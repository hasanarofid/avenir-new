import argparse, json, pandas as pd
from pathlib import Path

def run(input_path, output_path):
    df = pd.read_excel(input_path, header=1)
    df.columns = [str(c).strip().lower() for c in df.columns]

    date_col = [c for c in df.columns if 'tanggal' in c or 'date' in c][0]
    fnet_col = [c for c in df.columns if 'foreign net' in c][0]
    val_col  = [c for c in df.columns if 'market value' in c or 'value' in c][0]
    open_col = [c for c in df.columns if 'open' == c][0]
    high_col = [c for c in df.columns if 'high' == c][0]
    low_col  = [c for c in df.columns if 'low' == c][0]
    price_col = [c for c in df.columns if 'price' == c or 'close' == c][0]

    df[date_col] = pd.to_datetime(df[date_col], format='mixed', dayfirst=True).dt.strftime('%Y-%m-%d')
    
    from common import parse_number

    results = {}
    for _, row in df.iterrows():
        d = row[date_col]
        if pd.isna(d): continue

        fnet = parse_number(row[fnet_col])
        val  = parse_number(row[val_col])
        o    = parse_number(row[open_col])
        h    = parse_number(row[high_col])
        l    = parse_number(row[low_col])
        p    = parse_number(row[price_col])

        fnet = fnet if not pd.isna(fnet) else 0
        val = val if not pd.isna(val) else 0
        o = o if not pd.isna(o) else 0
        h = h if not pd.isna(h) else 0
        l = l if not pd.isna(l) else 0
        p = p if not pd.isna(p) else 0
        
        # Only include rows that have a valid price
        if p > 0:
            results[d] = {
                "FOREIGN_NET_TODAY": fnet / 1e9 if fnet != 0 else 0,
                "VALUE_TRADED_BN_IDR": val / 1e9,
                "OPEN": o,
                "HIGH": h,
                "LOW": l,
                "PRICE": p
            }

    with open(output_path, 'w') as f:
        json.dump(results, f)

if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('--input', required=True)
    parser.add_argument('--output', required=True)
    parser.add_argument('--date', required=False) # Keep for compatibility if needed
    args = parser.parse_args()
    run(args.input, args.output)
