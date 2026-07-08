import sys
import json
import numpy as np
import pandas as pd
from pathlib import Path

def calculate_price_trend(csv_path):
    try:
        # Read CSV
        df = pd.read_csv(csv_path)
        
        # Clean 'Price' and 'High' columns
        if 'Price' in df.columns:
            df['Price'] = df['Price'].astype(str).str.replace(',', '').astype(float)
        else:
            return {"error": "Column 'Price' not found in CSV."}
            
        if 'High' in df.columns:
            df['High'] = df['High'].astype(str).str.replace(',', '').astype(float)
        else:
            df['High'] = df['Price']
            
        # Parse Date
        df['Date'] = pd.to_datetime(df['Date'], format='%m/%d/%Y', errors='coerce')
        
        # Sort values by Date ascending
        df = df.sort_values('Date').reset_index(drop=True)
        
        if len(df) < 5:
            return {"error": "Not enough data points. Minimum 5 days required."}
            
        # Calculations
        latest_date = df['Date'].iloc[-1].strftime('%Y-%m-%d')
        close = df['Price'].iloc[-1]
        
        # MA20
        ma20 = df['Price'].rolling(window=20, min_periods=1).mean().iloc[-1]
        
        # MA60
        ma60 = df['Price'].rolling(window=60, min_periods=1).mean().iloc[-1]
        
        # Sparkline (last 60 days)
        prices_60d = df['Price'].tail(60).tolist()
        
        # Change for latest date
        if len(df) >= 2:
            change_abs = close - df['Price'].iloc[-2]
            change_pct = (change_abs / df['Price'].iloc[-2]) * 100
        else:
            change_abs = 0
            change_pct = 0
        
        # Return 5D
        if len(df) >= 6:
            ret_5d = (close - df['Price'].iloc[-6]) / df['Price'].iloc[-6]
        else:
            ret_5d = (close - df['Price'].iloc[0]) / df['Price'].iloc[0]
            
        # Return 20D
        if len(df) >= 21:
            ret_20d = (close - df['Price'].iloc[-21]) / df['Price'].iloc[-21]
        else:
            ret_20d = (close - df['Price'].iloc[0]) / df['Price'].iloc[0]
            
        # Drawdown 20D
        if len(df) >= 20:
            max_high_20d = df['High'].tail(20).max()
        else:
            max_high_20d = df['High'].max()
            
        drawdown_20d = (close - max_high_20d) / max_high_20d if max_high_20d > 0 else 0
        
        # Calculate Score
        score = 0
        if close > ma20:
            score += 30
        if ma20 > ma60:
            score += 25
        if ret_5d > 0:
            score += 20
        if ret_20d > 0:
            score += 15
        if drawdown_20d > -0.03:
            score += 10
            
        score = max(0, min(score, 100))
        
        result = {
            "date": latest_date,
            "close": round(close, 2),
            "ma20": round(ma20, 2),
            "ma60": round(ma60, 2),
            "ret_5d": round(ret_5d, 4),
            "ret_20d": round(ret_20d, 4),
            "score": score,
            "prices_60d": prices_60d,
            "change_abs": round(change_abs, 4),
            "change_pct": round(change_pct, 4)
        }
        
        return result

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
