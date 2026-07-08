import sys
import json
import csv
from datetime import datetime

def calculate_price_trend(csv_path):
    try:
        data = []
        with open(csv_path, 'r', encoding='utf-8-sig') as f:
            reader = csv.DictReader(f)
            for row in reader:
                if 'Date' not in row and '\ufeffDate' not in row:
                    # try fallback to lower case
                    keys = [k.lower() for k in row.keys() if k]
                    if 'date' not in keys:
                        continue
                
                # find the correct key for Date, Price, High
                date_key = next((k for k in row.keys() if k and k.lower() == 'date'), None)
                price_key = next((k for k in row.keys() if k and k.lower() == 'price'), None)
                high_key = next((k for k in row.keys() if k and k.lower() == 'high'), None)
                
                if not date_key or not price_key:
                    continue
                    
                date_str = row[date_key].strip()
                try:
                    date_obj = datetime.strptime(date_str, '%m/%d/%Y')
                except ValueError:
                    try:
                        date_obj = datetime.strptime(date_str, '%Y-%m-%d')
                    except ValueError:
                        try:
                            date_obj = datetime.strptime(date_str, '%d/%m/%Y')
                        except ValueError:
                            continue # skip unparseable date
                
                price_str = str(row.get(price_key, '0')).replace(',', '')
                price = float(price_str) if price_str else 0.0
                
                high_str = str(row.get(high_key, price_str)).replace(',', '')
                high = float(high_str) if high_str else price
                
                data.append({
                    'Date': date_obj,
                    'Price': price,
                    'High': high
                })
                    
        if len(data) < 5:
            return {"error": "Not enough data points. Minimum 5 days required."}
            
        # Sort values by Date ascending
        data.sort(key=lambda x: x['Date'])
        
        prices = [row['Price'] for row in data]
        highs = [row['High'] for row in data]
        
        results = []
        for i in range(len(data)):
            current_date = data[i]['Date'].strftime('%Y-%m-%d')
            close = prices[i]
            
            # MA20
            start_20 = max(0, i - 19)
            ma20_slice = prices[start_20:i+1]
            ma20 = sum(ma20_slice) / len(ma20_slice)
            
            # MA60
            start_60 = max(0, i - 59)
            ma60_slice = prices[start_60:i+1]
            ma60 = sum(ma60_slice) / len(ma60_slice)
            
            # Sparkline (last 60 days)
            prices_60d = prices[start_60:i+1]
            
            # Change for current date
            if i >= 1:
                change_abs = close - prices[i-1]
                change_pct = (change_abs / prices[i-1]) * 100
            else:
                change_abs = 0
                change_pct = 0
                
            # Return 5D
            if i >= 5:
                ret_5d = (close - prices[i-5]) / prices[i-5]
            else:
                ret_5d = (close - prices[0]) / prices[0]
                
            # Return 20D
            if i >= 20:
                ret_20d = (close - prices[i-20]) / prices[i-20]
            else:
                ret_20d = (close - prices[0]) / prices[0]
                
            # Drawdown 20D
            high_20d_slice = highs[start_20:i+1]
            max_high_20d = max(high_20d_slice) if high_20d_slice else close
            drawdown_20d = (close - max_high_20d) / max_high_20d if max_high_20d > 0 else 0
            
            # Calculate Score
            score = 0
            if close > ma20: score += 30
            if ma20 > ma60: score += 25
            if ret_5d > 0: score += 20
            if ret_20d > 0: score += 15
            if drawdown_20d > -0.03: score += 10
            score = max(0, min(score, 100))
            
            results.append({
                "date": current_date,
                "close": round(close, 2),
                "ma20": round(ma20, 2),
                "ma60": round(ma60, 2),
                "ret_5d": round(ret_5d, 4),
                "ret_20d": round(ret_20d, 4),
                "score": score,
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
