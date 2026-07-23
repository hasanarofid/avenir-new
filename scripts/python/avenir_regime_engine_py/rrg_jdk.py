import argparse
import json
import pandas as pd
import numpy as np
from pathlib import Path

# Color and short name mappings for Sectors
SECTOR_CONFIG = {
    "Basic Materials": {"short": "Basic Mat.", "color": "#C9A227"},
    "Consumer Cyclicals": {"short": "Cons. Cycl.", "color": "#E2705C"},
    "Consumer Non-Cyclicals": {"short": "Cons. Non-Cycl.", "color": "#46C46E"},
    "Energy": {"short": "Energy", "color": "#D99B3E"},
    "Financials": {"short": "Financials", "color": "#5FA0D8"},
    "Healthcare": {"short": "Healthcare", "color": "#8FD4A8"},
    "Industrials": {"short": "Industrials", "color": "#A98BD0"},
    "Infrastructures": {"short": "Infrastruct.", "color": "#6BC5C0"},
    "Properties & Real Estate": {"short": "Property", "color": "#D08BA8"},
    "Technology": {"short": "Technology", "color": "#7C9BE0"},
    "Transportation & Logistic": {"short": "Transport", "color": "#B0B0A8"}
}

def get_quadrant(x, y):
    if x >= 100:
        return "LEADING" if y >= 100 else "WEAKENING"
    else:
        return "IMPROVING" if y >= 100 else "LAGGING"

def calculate_rrg_metrics(prices_df, benchmark_df, target_cols, id_col, value_col):
    """
    Computes RS-Ratio (EMA-8 smoothed) and RS-Momentum (lag-8 ROC)
    according to Avenir Research Panel 7 PRD v1.0.
    """
    # 1. Create a wide format price dataframe: index = date, columns = security/sector identifiers
    price_pivot = prices_df.pivot(index='date', columns=id_col, values=value_col)
    
    # Merge with benchmark
    merged = price_pivot.join(benchmark_df.set_index('date')['value'].rename('benchmark'), how='inner')
    merged = merged.sort_index()
    
    dates = merged.index.tolist()
    if len(dates) < 16:
        raise ValueError("Price history must contain at least 16 sessions for lag-8 RRG calculation.")
        
    bench = merged['benchmark']
    
    # 2. Base Relative Strength (RS)
    rs_df = merged[target_cols].div(bench, axis=0) * 100
    
    # 3. RS-Ratio_raw: Cross-sectional z-score * 2 + 100
    rs_ratio_raw = pd.DataFrame(index=dates, columns=target_cols, dtype=float)
    for date in dates:
        row = rs_df.loc[date].astype(float)
        mean_val = row.mean()
        std_val = row.std(ddof=0)
        if pd.isna(std_val) or std_val == 0:
            rs_ratio_raw.loc[date] = 100.0
        else:
            rs_ratio_raw.loc[date] = 100.0 + ((row - mean_val) / std_val) * 2.0
            
    # 4. RS-Ratio: EMA-8 Smoothing
    rs_ratio_df = rs_ratio_raw.ewm(span=8, adjust=False).mean()
    
    # 5. RS-Momentum: Lag-8 ROC Cross-sectional z-score * 2 + 100
    roc_8 = (rs_ratio_df / rs_ratio_df.shift(8) - 1.0) * 100.0
    rs_mom_df = pd.DataFrame(index=dates, columns=target_cols, dtype=float)
    for date in dates:
        row = roc_8.loc[date].astype(float)
        if row.isna().any():
            rs_mom_df.loc[date] = np.nan
            continue
        mean_val = row.mean()
        std_val = row.std(ddof=0)
        if pd.isna(std_val) or std_val == 0:
            rs_mom_df.loc[date] = 100.0
        else:
            rs_mom_df.loc[date] = 100.0 + ((row - mean_val) / std_val) * 2.0
            
    return rs_ratio_df, rs_mom_df, dates

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--prices', required=True, help='Path to stock price history CSV')
    parser.add_argument('--benchmark', required=True, help='Path to benchmark index history CSV')
    parser.add_argument('--sector-master', required=True, help='Path to sector master mapping CSV')
    parser.add_argument('--output-dir', required=True, help='Output directory')
    args = parser.parse_args()
    
    output_dir = Path(args.output_dir)
    output_dir.mkdir(parents=True, exist_ok=True)
    
    # Load data
    prices = pd.read_csv(args.prices)
    benchmark = pd.read_csv(args.benchmark)
    sectors = pd.read_csv(args.sector_master)
    
    # Clean prices & join with sectors
    prices['date'] = pd.to_datetime(prices['date']).dt.strftime('%Y-%m-%d')
    benchmark['date'] = pd.to_datetime(benchmark['date']).dt.strftime('%Y-%m-%d')
    
    # standardise column names
    sectors.columns = [c.lower() for c in sectors.columns]
    prices.columns = [c.lower() for c in prices.columns]
    
    # Drop sector from prices if already present to avoid duplicate column suffix suffix_x / suffix_y
    if 'sector' in prices.columns:
        prices = prices.drop(columns=['sector'])
        
    # Merge
    merged_prices = prices.merge(sectors, on='code', how='inner')
    merged_prices = merged_prices.dropna(subset=['sector', 'close'])
    
    # Calculate Cap-Weighted Sector Index Prices
    # Sector_Index = Sum(close * listed_shares)
    merged_prices['listed_shares'] = pd.to_numeric(merged_prices['listed_shares'], errors='coerce').fillna(1e6)
    merged_prices['cap_value'] = merged_prices['close'] * merged_prices['listed_shares']
    
    sector_index_series = merged_prices.groupby(['date', 'sector'])['cap_value'].sum().reset_index()
    
    available_sectors = [s for s in SECTOR_CONFIG.keys() if s in sector_index_series['sector'].unique()]
    
    # ----------------------------------------------------
    # Sector level RRG Calculation (Benchmark = IHSG)
    # ----------------------------------------------------
    rs_ratio_sec, rs_mom_sec, dates = calculate_rrg_metrics(
        sector_index_series, benchmark, available_sectors, 'sector', 'cap_value'
    )
    
    # Filter for valid dates where momentum is defined
    valid_dates = [d for d in dates if not pd.isna(rs_mom_sec.loc[d].iloc[0])]
    vis_dates = valid_dates[-15:] if len(valid_dates) >= 15 else valid_dates
    
    sector_series = []
    rotev_sector = {}
    for sec in available_sectors:
        pts = []
        for d in vis_dates:
            pts.append({
                "d": d,
                "x": round(float(rs_ratio_sec.loc[d, sec]), 3),
                "y": round(float(rs_mom_sec.loc[d, sec]), 3)
            })
        
        cfg = SECTOR_CONFIG[sec]
        sector_series.append({
            "name": sec,
            "short": cfg["short"],
            "color": cfg["color"],
            "pts": pts
        })
        
        # Calculate rotation event (rotev)
        current_q = get_quadrant(pts[-1]["x"], pts[-1]["y"])
        dwell = 1
        frm = None
        for p in reversed(pts[:-1]):
            q = get_quadrant(p["x"], p["y"])
            if q == current_q:
                dwell += 1
            else:
                frm = q
                break
        if frm is not None and frm != current_q:
            rotev_sector[sec] = {
                "frm": frm,
                "to": current_q,
                "dwell": dwell,
                "status": "fresh" if dwell == 1 else "confirmed"
            }
        
    rrg_sector = {
        "dates": vis_dates,
        "lag": 8,
        "series": sector_series,
        "rotev": rotev_sector,
        "meta": {
            "universe": 900,
            "sessions": len(dates),
            "valid": len(vis_dates),
            "from": dates[0] if dates else "",
            "to": dates[-1] if dates else ""
        }
    }
    
    # Save rrg_sector.json
    with open(output_dir / "rrg_sector.json", "w") as f:
        json.dump(rrg_sector, f, indent=2)
        
    # ----------------------------------------------------
    # Stock level RRG Calculation (Benchmark = Respective Sector Index)
    # ----------------------------------------------------
    rrg_stocks = {
        "short": {s: cfg["short"] for s, cfg in SECTOR_CONFIG.items()},
        "sectors": {}
    }
    
    for sec in available_sectors:
        sec_prices = merged_prices[merged_prices['sector'] == sec].copy()
        
        # Get top 20 stocks by average trading value
        avg_values = sec_prices.groupby('code')['value'].mean().sort_values(ascending=False)
        top_stocks = avg_values.head(20).index.tolist()
        
        if len(top_stocks) < 3:
            continue
            
        try:
            # Benchmark is respective Sector Index
            sector_bench = sector_index_series[sector_index_series['sector'] == sec][['date', 'cap_value']].rename(columns={'cap_value': 'value'})
            
            rs_ratio_stk, rs_mom_stk, stk_dates = calculate_rrg_metrics(
                sec_prices[sec_prices['code'].isin(top_stocks)], sector_bench, top_stocks, 'code', 'close'
            )
            
            stk_valid_dates = [d for d in stk_dates if not pd.isna(rs_mom_stk.loc[d].iloc[0])]
            stk_vis_dates = stk_valid_dates[-15:] if len(stk_valid_dates) >= 15 else stk_valid_dates
            
            stock_series = []
            rotev_stock = {}
            for stk in top_stocks:
                pts = []
                for d in stk_vis_dates:
                    if d in rs_ratio_stk.index and stk in rs_ratio_stk.columns:
                        pts.append({
                            "d": d,
                            "x": round(float(rs_ratio_stk.loc[d, stk]), 3),
                            "y": round(float(rs_mom_stk.loc[d, stk]), 3)
                        })
                
                # Find company name
                stk_rows = sec_prices[sec_prices['code'] == stk]
                name = stk_rows.iloc[0]['name'] if 'name' in sec_prices.columns and not stk_rows.empty else stk
                
                stock_series.append({
                    "code": stk,
                    "name": name,
                    "pts": pts,
                    "chg": 0.0,
                    "val": round(float(sec_prices[(sec_prices['code'] == stk) & (sec_prices['date'] == stk_vis_dates[-1])]['value'].iloc[0]) / 1e9, 2) if not sec_prices[(sec_prices['code'] == stk) & (sec_prices['date'] == stk_vis_dates[-1])].empty else 0.0
                })
                
                # Calculate stock rotation event
                if len(pts) > 1:
                    current_q = get_quadrant(pts[-1]["x"], pts[-1]["y"])
                    dwell = 1
                    frm = None
                    for p in reversed(pts[:-1]):
                        q = get_quadrant(p["x"], p["y"])
                        if q == current_q:
                            dwell += 1
                        else:
                            frm = q
                            break
                    if frm is not None and frm != current_q:
                        rotev_stock[stk] = {
                            "frm": frm,
                            "to": current_q,
                            "dwell": dwell,
                            "status": "fresh" if dwell == 1 else "confirmed"
                        }
                
            rrg_stocks["sectors"][sec] = {
                "series": stock_series,
                "rotev": rotev_stock
            }
        except Exception as e:
            print(f"Skipping stocks in sector {sec}: {e}")
            
    # Save rrg_stocks.json
    with open(output_dir / "rrg_stocks.json", "w") as f:
        json.dump(rrg_stocks, f, indent=2)
        
    print("RRG standard JdK calculation complete.")

if __name__ == '__main__':
    main()
