import argparse, json, pandas as pd
from pathlib import Path

def run(input_path, date, output_path):
    df = pd.read_excel(input_path, header=1)
    df.columns = [str(c).strip().lower() for c in df.columns]
    
    date_col = [c for c in df.columns if 'tanggal' == c or 'date' == c][0]
    fnet_col = [c for c in df.columns if 'foreign net flow' == c or 'foreign net' == c][0]
    val_col  = [c for c in df.columns if 'market value' == c or 'value' == c][0]
    open_col = [c for c in df.columns if 'open' == c][0]
    high_col = [c for c in df.columns if 'high' == c][0]
    low_col  = [c for c in df.columns if 'low' == c][0]
    
    df[date_col] = pd.to_datetime(df[date_col], format='mixed', dayfirst=True).dt.strftime('%Y-%m-%d')
    row = df[df[date_col] == date]
    
    if row.empty:
        res = {"error": f"Date {date} not found in Excel"}
    else:
        row = row.iloc[0]
        def parse_value(val_str):
            if pd.isna(val_str): return 0
            s = str(val_str).upper().strip().replace(',', '')
            mult = 1
            if 'T' in s: mult = 1e12; s = s.replace('T', '')
            elif 'B' in s: mult = 1e9; s = s.replace('B', '')
            elif 'M' in s: mult = 1e6; s = s.replace('M', '')
            try: return float(s) * mult
            except: return 0

        fnet = parse_value(row[fnet_col])
        val  = parse_value(row[val_col])
        o    = float(row[open_col]) if not pd.isna(row[open_col]) else 0
        h    = float(row[high_col]) if not pd.isna(row[high_col]) else 0
        l    = float(row[low_col]) if not pd.isna(row[low_col]) else 0

        res = {
            "FOREIGN_NET_TODAY": fnet / 1e9 if fnet != 0 else 0,
            "VALUE_TRADED_BN_IDR": val / 1e9 if val > 0 else 0,
            "OPEN": o,
            "HIGH": h,
            "LOW": l
        }

    with open(output_path, 'w') as f:
        json.dump(res, f)

if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('--input', required=True)
    parser.add_argument('--date', required=True)
    parser.add_argument('--output', required=True)
    args = parser.parse_args()
    run(args.input, args.date, args.output)
