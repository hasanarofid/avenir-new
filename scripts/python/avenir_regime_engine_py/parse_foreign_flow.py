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
    price_col = [c for c in df.columns if 'price' == c or 'close' == c][0]
    
    df[date_col] = pd.to_datetime(df[date_col], format='mixed', dayfirst=True).dt.strftime('%Y-%m-%d')
    row = df[df[date_col] == date]
    
    if row.empty:
        res = {"error": f"Date {date} not found in Excel"}
    else:
        row = row.iloc[0]
        from common import parse_number

        fnet = parse_number(row[fnet_col])
        val  = parse_number(row[val_col])
        
        o    = parse_number(row[open_col])
        h    = parse_number(row[high_col])
        l    = parse_number(row[low_col])
        p    = parse_number(row[price_col])

        # Fill NaNs with 0
        fnet = fnet if not pd.isna(fnet) else 0
        val = val if not pd.isna(val) else 0
        o = o if not pd.isna(o) else 0
        h = h if not pd.isna(h) else 0
        l = l if not pd.isna(l) else 0
        p = p if not pd.isna(p) else 0

        res = {
            "FOREIGN_NET_TODAY": fnet / 1e9 if fnet != 0 else 0,
            "VALUE_TRADED_BN_IDR": val / 1e9 if val > 0 else 0,
            "OPEN": o,
            "HIGH": h,
            "LOW": l,
            "PRICE": p
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
