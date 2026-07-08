import sys
import json
import traceback
import pandas as pd
from datetime import datetime

def parse_volume(vol_str):
    """Convert volume strings like '19.72B', '40.12M', '1.5K' to numeric."""
    if not isinstance(vol_str, str):
        return float(vol_str) if pd.notna(vol_str) else 0.0
        
    vol_str = vol_str.strip().upper().replace(',', '')
    if not vol_str:
        return 0.0
        
    try:
        if vol_str.endswith('B'):
            return float(vol_str[:-1]) * 1e9
        elif vol_str.endswith('M'):
            return float(vol_str[:-1]) * 1e6
        elif vol_str.endswith('K'):
            return float(vol_str[:-1]) * 1e3
        return float(vol_str)
    except ValueError:
        return 0.0

def calculate_price_trend(csv_path):
    try:
        df = pd.read_csv(csv_path, encoding='utf-8-sig')
        
        # Rename for consistency across EN/ID versions of investing.com
        col_map = {
            "Date": "Date",
            "Tanggal": "Date",
            "Price": "Close",
            "Terakhir": "Close",
            "Open": "Open",
            "Pembukaan": "Open",
            "High": "High",
            "Tertinggi": "High",
            "Low": "Low",
            "Terendah": "Low",
            "Vol.": "Volume",
            "Change %": "Change_Pct",
            "Perubahan%": "Change_Pct",
        }
        
        # Strip spaces from column names before mapping
        df.rename(columns=lambda x: col_map.get(x.strip(), x.strip()), inplace=True)
        
        if "Date" not in df.columns or "Close" not in df.columns:
            return {"error": f"Missing required columns. Found: {list(df.columns)}"}
            
        # Parse date gracefully
        df["Date"] = pd.to_datetime(df["Date"], format="mixed", dayfirst=True)
        df.sort_values("Date", inplace=True)
        df.reset_index(drop=True, inplace=True)
        
        if len(df) < 5:
            return {"error": "Not enough data points. Minimum 5 days required."}
            
        # Clean numeric columns
        for col in ["Close", "Open", "High", "Low"]:
            if col in df.columns:
                if df[col].dtype == object:
                    df[col] = df[col].astype(str).str.replace(",", "").astype(float)
        
        if "Volume" in df.columns:
            df["Volume"] = df["Volume"].apply(parse_volume)
        else:
            df["Volume"] = 0.0
            
        # Calculate features using rolling windows
        df["MA20"] = df["Close"].rolling(window=20).mean()
        df["MA60"] = df["Close"].rolling(window=60).mean()
        
        df["Return_5D"] = df["Close"].pct_change(periods=5)
        df["Return_20D"] = df["Close"].pct_change(periods=20)
        
        df["High_20D"] = df["High"].rolling(window=20).max()
        # Fallback to Close if High isn't available for Drawdown
        if df["High_20D"].isnull().all():
            df["High_20D"] = df["Close"].rolling(window=20).max()
            
        df["Drawdown_20D"] = (df["Close"] / df["High_20D"]) - 1.0
        
        # Volatility / Liquidity components
        df["Vol_MA20"] = df["Volume"].rolling(window=20).mean()
        df["Range"] = df["High"] - df["Low"]
        df["Range_MA20"] = df["Range"].rolling(window=20).mean()
        
        results = []
        
        for i, row in df.iterrows():
            close = row["Close"]
            ma20 = row["MA20"] if pd.notna(row["MA20"]) else close
            ma60 = row["MA60"] if pd.notna(row["MA60"]) else close
            
            # Calculate Price Trend Score (0-100)
            score = 0
            if close > ma20: score += 30
            if ma20 > ma60: score += 25
            if pd.notna(row["Return_5D"]) and row["Return_5D"] > 0: score += 20
            if pd.notna(row["Return_20D"]) and row["Return_20D"] > 0: score += 15
            if pd.notna(row["Drawdown_20D"]) and row["Drawdown_20D"] > -0.03: score += 10
            score = max(0, min(100, score))
            
            # Calculate Volatility & Liquidity Score (0-100)
            vol_score = 50
            if pd.notna(row["Volume"]) and pd.notna(row["Vol_MA20"]) and row["Vol_MA20"] > 0:
                if row["Volume"] > row["Vol_MA20"]:
                    vol_score += 30 # Good liquidity
                    
            if pd.notna(row["Range"]) and pd.notna(row["Range_MA20"]) and row["Range_MA20"] > 0:
                if row["Range"] < (1.5 * row["Range_MA20"]):
                    vol_score += 20 # Stable volatility
                else:
                    vol_score -= 20 # High volatility (bad)
                    
            vol_score = max(0, min(100, vol_score))
            
            # Calculate change
            change_abs = 0.0
            change_pct = 0.0
            if i > 0:
                prev_close = df.iloc[i-1]["Close"]
                change_abs = close - prev_close
                if prev_close > 0:
                    change_pct = (change_abs / prev_close) * 100
                    
            # Get last 60 days for sparkline
            start_idx = max(0, i - 59)
            prices_60d = df.iloc[start_idx:i+1]["Close"].tolist()
            
            results.append({
                "date": row["Date"].strftime("%Y-%m-%d"),
                "close": round(close, 2),
                "ma20": round(ma20, 2),
                "ma60": round(ma60, 2),
                "ret_5d": round(row["Return_5D"], 4) if pd.notna(row["Return_5D"]) else 0,
                "ret_20d": round(row["Return_20D"], 4) if pd.notna(row["Return_20D"]) else 0,
                "score": score,
                "volatility_score": vol_score,
                "prices_60d": prices_60d,
                "change_abs": round(change_abs, 4),
                "change_pct": round(change_pct, 4)
            })
            
        return {
            "latest": results[-1] if results else {},
            "history": results
        }

    except Exception as e:
        return {"error": str(e), "trace": traceback.format_exc()}

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No CSV path provided"}))
        sys.exit(1)
        
    csv_path = sys.argv[1]
    result = calculate_price_trend(csv_path)
    print(json.dumps(result))
