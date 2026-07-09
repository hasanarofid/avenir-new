import sys
import json
import csv
import math
from datetime import datetime

# =========================================================
# 1. HELPERS & PARSERS
# =========================================================

def clamp(value, min_value=0, max_value=100):
    return max(min_value, min(value, max_value))

def is_na(x):
    if x is None:
        return True
    if isinstance(x, float) and math.isnan(x):
        return True
    if isinstance(x, str):
        s = x.strip().lower()
        if s in ["", "-", "n/a", "nan", "none"]:
            return True
    return False

def parse_number(x):
    if is_na(x):
        return None

    s = str(x).strip()
    s = s.replace("%", "").replace(" ", "").replace('"', "")

    if s in ["", "-", "n/a", "nan", "none"]:
        return None

    multiplier = 1
    if len(s) > 0 and s[-1].upper() in ["K", "M", "B"]:
        suffix = s[-1].upper()
        s = s[:-1]
        if suffix == "K": multiplier = 1_000
        elif suffix == "M": multiplier = 1_000_000
        elif suffix == "B": multiplier = 1_000_000_000

    if "," in s and "." in s:
        if s.rfind(",") > s.rfind("."):
            s = s.replace(".", "").replace(",", ".")
        else:
            s = s.replace(",", "")
    elif "," in s:
        parts = s.split(",")
        if len(parts[-1]) <= 2:
            s = s.replace(",", ".")
        else:
            s = s.replace(",", "")

    try:
        return float(s) * multiplier
    except Exception:
        return None

# =========================================================
# 2. CONTINUOUS SCORING ENGINE (CLIENT LOGIC)
# =========================================================

def score_piecewise(value, points):
    if is_na(value):
        return None

    points = sorted(points, key=lambda x: x[0])

    if value <= points[0][0]: return points[0][1]
    if value >= points[-1][0]: return points[-1][1]

    for i in range(len(points) - 1):
        x0, y0 = points[i]
        x1, y1 = points[i + 1]
        if x0 <= value <= x1:
            ratio = (value - x0) / (x1 - x0)
            return y0 + ratio * (y1 - y0)
    return None

def score_close_vs_ma20(close, ma20):
    if is_na(close) or is_na(ma20) or ma20 == 0: return None
    distance = (close / ma20) - 1
    return score_piecewise(distance, [
        (-0.15, 5), (-0.10, 12), (-0.07, 22), (-0.03, 38),
        (0.00, 60), (0.02, 75), (0.05, 90), (0.08, 100),
    ])

def score_ma20_vs_ma60(ma20, ma60):
    if is_na(ma20) or is_na(ma60) or ma60 == 0: return None
    spread = (ma20 / ma60) - 1
    return score_piecewise(spread, [
        (-0.15, 5), (-0.10, 15), (-0.07, 25), (-0.03, 40),
        (0.00, 60), (0.02, 75), (0.05, 90), (0.08, 100),
    ])

def score_return_5d(ret_5d):
    if is_na(ret_5d): return None
    return score_piecewise(ret_5d, [
        (-0.12, 5), (-0.08, 12), (-0.05, 25), (-0.02, 40),
        (0.00, 52), (0.02, 65), (0.05, 85), (0.08, 100),
    ])

def score_return_20d(ret_20d):
    if is_na(ret_20d): return None
    return score_piecewise(ret_20d, [
        (-0.20, 5), (-0.12, 15), (-0.08, 25), (-0.05, 35),
        (0.00, 52), (0.03, 65), (0.08, 85), (0.12, 100),
    ])

def score_drawdown_20d(drawdown_20d):
    if is_na(drawdown_20d): return None
    return score_piecewise(drawdown_20d, [
        (-0.25, 5), (-0.18, 15), (-0.12, 30), (-0.08, 45),
        (-0.05, 60), (-0.03, 75), (-0.01, 90), (0.00, 100),
    ])

def calculate_price_trend_score(row):
    components = {
        "close_vs_ma20": score_close_vs_ma20(row.get("close"), row.get("ma20")),
        "ma20_vs_ma60": score_ma20_vs_ma60(row.get("ma20"), row.get("ma60")),
        "return_5d": score_return_5d(row.get("ret_5d")),
        "return_20d": score_return_20d(row.get("ret_20d")),
        "drawdown_20d": score_drawdown_20d(row.get("drawdown_20d")),
    }

    weights = {
        "close_vs_ma20": 0.30,
        "ma20_vs_ma60": 0.25,
        "return_5d": 0.20,
        "return_20d": 0.15,
        "drawdown_20d": 0.10,
    }

    valid_score = 0
    valid_weight = 0

    for key, score in components.items():
        if not is_na(score):
            valid_score += score * weights[key]
            valid_weight += weights[key]

    if valid_weight == 0:
        return None

    return round(clamp(valid_score / valid_weight), 0)

# =========================================================
# 3. MAIN PIPELINE (WITHOUT PANDAS)
# =========================================================

def calculate_price_trend(csv_path):
    try:
        data = []
        with open(csv_path, 'r', encoding='utf-8-sig') as f:
            reader = csv.DictReader(f)
            for row in reader:
                normalized_row = {k.strip().lower(): v for k, v in row.items() if k}
                
                date_str = normalized_row.get('date') or normalized_row.get('tanggal') or normalized_row.get('time')
                close_str = normalized_row.get('close') or normalized_row.get('price') or normalized_row.get('last') or normalized_row.get('terakhir')
                open_str = normalized_row.get('open') or normalized_row.get('pembukaan')
                high_str = normalized_row.get('high') or normalized_row.get('tertinggi')
                low_str = normalized_row.get('low') or normalized_row.get('terendah')
                vol_str = normalized_row.get('vol.') or normalized_row.get('volume')
                
                if not date_str or not close_str:
                    continue
                    
                date_str = date_str.strip()
                date_obj = None
                for fmt in ['%m/%d/%Y', '%Y-%m-%d', '%d/%m/%Y', '%d/%m/%y']:
                    try:
                        date_obj = datetime.strptime(date_str, fmt)
                        break
                    except ValueError:
                        pass
                
                if not date_obj:
                    continue
                    
                close = parse_number(close_str)
                if is_na(close):
                    continue
                    
                open_val = parse_number(open_str)
                if is_na(open_val): open_val = close
                high = parse_number(high_str)
                if is_na(high): high = close
                low = parse_number(low_str)
                if is_na(low): low = close
                vol = parse_number(vol_str) or 0.0
                
                data.append({
                    'Date': date_obj,
                    'Open': open_val,
                    'Close': close,
                    'High': high,
                    'Low': low,
                    'Volume': vol
                })
                
        if len(data) < 5:
            return {"error": "Not enough data points. Minimum 5 days required."}
            
        data.sort(key=lambda x: x['Date'])
        
        # We need to drop duplicates by Date, keeping the last (similar to pandas drop_duplicates keep='last')
        unique_data = {}
        for d in data:
            unique_data[d['Date']] = d
        data = list(unique_data.values())
        data.sort(key=lambda x: x['Date'])
        
        # Pre-calculate daily returns
        for i in range(len(data)):
            if i == 0:
                data[i]['ret_1d'] = 0.0
            else:
                data[i]['ret_1d'] = (data[i]['Close'] - data[i-1]['Close']) / data[i-1]['Close']
        
        # Pre-calculate 20d realized volatility
        import math
        for i in range(len(data)):
            start_20 = max(0, i - 19)
            if (i - start_20 + 1) >= 20:
                returns_20d = [row['ret_1d'] for row in data[start_20:i+1]]
                mean_ret = sum(returns_20d) / len(returns_20d)
                variance = sum((r - mean_ret) ** 2 for r in returns_20d) / len(returns_20d)
                data[i]['vol_20d'] = math.sqrt(variance)
            else:
                data[i]['vol_20d'] = None
        
        results = []
        
        for i in range(len(data)):
            current_date = data[i]['Date'].strftime('%Y-%m-%d')
            close = data[i]['Close']
            high = data[i]['High']
            low = data[i]['Low']
            volume = data[i]['Volume']
            
            # Moving Average 20
            start_20 = max(0, i - 19)
            if (i - start_20 + 1) >= 20: # min_periods=20
                closes_20d = [row['Close'] for row in data[start_20:i+1]]
                ma20 = sum(closes_20d) / len(closes_20d)
            else:
                ma20 = None
                
            # Moving Average 60
            start_60 = max(0, i - 59)
            if (i - start_60 + 1) >= 60: # min_periods=60
                closes_60d = [row['Close'] for row in data[start_60:i+1]]
                ma60 = sum(closes_60d) / len(closes_60d)
            else:
                ma60 = None
                
            # Sparkline prices
            prices_60d = [row['Close'] for row in data[max(0, i-59):i+1]]
            
            # Returns
            ret_1d = (close - data[i-1]['Close']) / data[i-1]['Close'] if i >= 1 else None
            ret_5d = (close - data[i-5]['Close']) / data[i-5]['Close'] if i >= 5 else None
            ret_20d = (close - data[i-20]['Close']) / data[i-20]['Close'] if i >= 20 else None
            
            # Drawdown
            if (i - start_20 + 1) >= 20:
                highs_20d = [row['High'] for row in data[start_20:i+1]]
                max_high_20d = max(highs_20d)
                drawdown_20d = (close / max_high_20d) - 1 if max_high_20d > 0 else 0
            else:
                drawdown_20d = None
                
            # Volatility Percentile
            current_vol = data[i]['vol_20d']
            start_252 = max(0, i - 251)
            vols_252 = [row['vol_20d'] for row in data[start_252:i] if row['vol_20d'] is not None]
            
            if current_vol is not None and len(vols_252) > 0:
                # Calculate percentile rank
                count_below = sum(1 for v in vols_252 if v <= current_vol)
                volatility_percentile = count_below / len(vols_252)
            else:
                volatility_percentile = None
                
            # Component scores & Final Score
            row_dict = {
                "close": close,
                "ma20": ma20,
                "ma60": ma60,
                "ret_5d": ret_5d,
                "ret_20d": ret_20d,
                "drawdown_20d": drawdown_20d
            }
            score = calculate_price_trend_score(row_dict)
            
            # Volatility feature (keeping our volume logic so Laravel gets the rupiah_score)
            vols_20d_list = [row['Volume'] for row in data[start_20:i+1]]
            vol_ma20 = sum(vols_20d_list) / len(vols_20d_list) if vols_20d_list else volume
            range_val = high - low
            ranges = [row['High'] - row['Low'] for row in data[start_20:i+1]]
            range_ma20 = sum(ranges) / len(ranges) if ranges else range_val
            
            vol_score = 50
            if vol_ma20 > 0 and volume > vol_ma20:
                vol_score += 30
            if range_ma20 > 0:
                if range_val < (1.5 * range_ma20):
                    vol_score += 20
                else:
                    vol_score -= 20
            vol_score = max(0, min(100, int(vol_score)))
            
            # Laravel wants change_abs and change_pct
            change_abs = (close - data[i-1]['Close']) if i >= 1 else 0.0
            change_pct = ret_1d * 100 if ret_1d is not None else 0.0
            
            results.append({
                "date": current_date,
                "open": data[i]['Open'],
                "high": high,
                "low": low,
                "close": close,
                "ma20": ma20,
                "ma60": ma60,
                "ret_1d": ret_1d,
                "ret_5d": ret_5d,
                "ret_20d": ret_20d,
                "drawdown_20d": drawdown_20d,
                "volatility_percentile": volatility_percentile,
                "ret_5d_pct": ret_5d * 100 if ret_5d is not None else None,
                "ret_20d_pct": ret_20d * 100 if ret_20d is not None else None,
                "drawdown_20d_pct": drawdown_20d * 100 if drawdown_20d is not None else None,
                "score": score if score is not None else 0, # Mapped to momentum in Laravel
                "volatility_score": vol_score,              # Mapped to rupiah_score in Laravel
                "prices_60d": prices_60d,
                "change_abs": change_abs,
                "change_pct": change_pct
            })
            
        return {
            "latest": results[-1] if results else {},
            "history": results
        }

    except Exception as e:
        import traceback
        return {"error": str(e), "trace": traceback.format_exc()}

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No CSV path provided"}))
        sys.exit(1)
        
    csv_path = sys.argv[1]
    result = calculate_price_trend(csv_path)
    print(json.dumps(result))
