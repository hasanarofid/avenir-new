import sys
import json
import csv
import traceback
from datetime import datetime

def parse_volume(vol_str):
    """Convert volume strings like '19.72B', '40.12M', '1.5K' to numeric."""
    if not isinstance(vol_str, str):
        try:
            return float(vol_str) if vol_str is not None else 0.0
        except:
            return 0.0
            
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
        data = []
        with open(csv_path, 'r', encoding='utf-8-sig') as f:
            reader = csv.DictReader(f)
            
            for row in reader:
                # Rename for consistency across EN/ID versions of investing.com
                # We normalize the keys first
                normalized_row = {}
                for k, v in row.items():
                    if k:
                        normalized_row[k.strip().lower()] = v
                
                date_str = normalized_row.get('date') or normalized_row.get('tanggal')
                close_str = normalized_row.get('price') or normalized_row.get('terakhir')
                open_str = normalized_row.get('open') or normalized_row.get('pembukaan')
                high_str = normalized_row.get('high') or normalized_row.get('tertinggi')
                low_str = normalized_row.get('low') or normalized_row.get('terendah')
                vol_str = normalized_row.get('vol.') or normalized_row.get('volume')
                
                if not date_str or not close_str:
                    continue
                    
                # Parse date gracefully
                date_str = date_str.strip()
                date_obj = None
                formats_to_try = ['%m/%d/%Y', '%Y-%m-%d', '%d/%m/%Y']
                for fmt in formats_to_try:
                    try:
                        date_obj = datetime.strptime(date_str, fmt)
                        break
                    except ValueError:
                        pass
                
                if not date_obj:
                    continue # skip unparseable date
                    
                def to_float(val_str):
                    if not val_str: return 0.0
                    try:
                        return float(val_str.replace(',', ''))
                    except:
                        return 0.0
                        
                close = to_float(close_str)
                high = to_float(high_str) if high_str else close
                low = to_float(low_str) if low_str else close
                vol = parse_volume(vol_str)
                
                data.append({
                    'Date': date_obj,
                    'Close': close,
                    'High': high,
                    'Low': low,
                    'Volume': vol
                })
                
        if len(data) < 5:
            return {"error": "Not enough data points. Minimum 5 days required."}
            
        # Sort values by Date ascending
        data.sort(key=lambda x: x['Date'])
        
        results = []
        
        for i in range(len(data)):
            current_date = data[i]['Date'].strftime('%Y-%m-%d')
            close = data[i]['Close']
            high = data[i]['High']
            low = data[i]['Low']
            volume = data[i]['Volume']
            
            start_20 = max(0, i - 19)
            start_60 = max(0, i - 59)
            
            # MAs
            closes_20d = [row['Close'] for row in data[start_20:i+1]]
            ma20 = sum(closes_20d) / len(closes_20d) if closes_20d else close
            
            closes_60d = [row['Close'] for row in data[start_60:i+1]]
            ma60 = sum(closes_60d) / len(closes_60d) if closes_60d else close
            
            prices_60d = closes_60d
            
            # Returns
            if i >= 5:
                ret_5d = (close - data[i-5]['Close']) / data[i-5]['Close']
            else:
                ret_5d = (close - data[0]['Close']) / data[0]['Close']
                
            if i >= 20:
                ret_20d = (close - data[i-20]['Close']) / data[i-20]['Close']
            else:
                ret_20d = (close - data[0]['Close']) / data[0]['Close']
                
            # Drawdown
            highs_20d = [row['High'] for row in data[start_20:i+1]]
            max_high_20d = max(highs_20d) if highs_20d else close
            drawdown_20d = (close - max_high_20d) / max_high_20d if max_high_20d > 0 else 0
            
            # Volatility features
            vols_20d = [row['Volume'] for row in data[start_20:i+1]]
            vol_ma20 = sum(vols_20d) / len(vols_20d) if vols_20d else volume
            
            range_val = high - low
            ranges = [row['High'] - row['Low'] for row in data[start_20:i+1]]
            range_ma20 = sum(ranges) / len(ranges) if ranges else range_val
            
            # Calculate Price Trend Score (0-100)
            score = 0
            if close > ma20: score += 30
            if ma20 > ma60: score += 25
            if ret_5d > 0: score += 20
            if ret_20d > 0: score += 15
            if drawdown_20d > -0.03: score += 10
            score = max(0, min(100, score))
            
            # Calculate Volatility & Liquidity Score (0-100)
            vol_score = 50
            if vol_ma20 > 0:
                if volume > vol_ma20:
                    vol_score += 30 # Good liquidity
                    
            if range_ma20 > 0:
                if range_val < (1.5 * range_ma20):
                    vol_score += 20 # Stable volatility
                else:
                    vol_score -= 20 # High volatility (bad)
                    
            vol_score = max(0, min(100, int(vol_score)))
            
            # Calculate change
            change_abs = 0.0
            change_pct = 0.0
            if i > 0:
                prev_close = data[i-1]['Close']
                change_abs = close - prev_close
                if prev_close > 0:
                    change_pct = (change_abs / prev_close) * 100
            
            results.append({
                "date": current_date,
                "close": round(close, 2),
                "ma20": round(ma20, 2),
                "ma60": round(ma60, 2),
                "ret_5d": round(ret_5d, 4),
                "ret_20d": round(ret_20d, 4),
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
        import traceback
        return {"error": str(e), "trace": traceback.format_exc()}

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No CSV path provided"}))
        sys.exit(1)
        
    csv_path = sys.argv[1]
    result = calculate_price_trend(csv_path)
    print(json.dumps(result))
