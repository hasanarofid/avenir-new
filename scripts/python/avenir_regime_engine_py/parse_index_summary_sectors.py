import argparse, json, pandas as pd
from pathlib import Path
from common import find_col

SECTOR_MAP = {
    'IDXENERGY': 'Energy', 'IDXBASIC': 'Basic Materials', 'IDXINDUST': 'Industrials',
    'IDXCYCLIC': 'Consumer Cyclicals', 'IDXFINANCE': 'Financials', 'IDXPROPERT': 'Properties & Real Estate',
    'IDXTECHNO': 'Technology', 'IDXINFRA': 'Infrastructures', 'IDXTRANS': 'Transportation & Logistic',
    'IDXNONCYC': 'Consumer Non-Cyclicals', 'IDXHEALTH': 'Healthcare'
}

def parse_number(x):
    try:
        if pd.isna(x): return None
        s = str(x).strip().replace(',', '')
        return float(s)
    except:
        return None

def run(input_path, output_path, date_str):
    df = pd.read_excel(input_path)
    df.columns = [str(c).strip().lower() for c in df.columns]

    code_col = find_col(df, ['index_code', 'index', 'code', 'indeks'])
    prev_col = find_col(df, ['previous', 'prev', 'sebelumnya'])
    chg_col = find_col(df, ['change', 'perubahan'])
    
    if not code_col or not prev_col or not chg_col:
        raise ValueError("Cannot find required columns in Index Summary")

    results = {}
    for _, row in df.iterrows():
        c = str(row[code_col]).upper().strip()
        if c in SECTOR_MAP:
            p = parse_number(row[prev_col])
            chg = parse_number(row[chg_col])
            if p is not None and chg is not None and p > 0:
                mapped_name = SECTOR_MAP[c]
                results[mapped_name] = (chg / p) * 100

    with open(output_path, 'w') as f:
        json.dump(results, f)

if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('--input', required=True)
    parser.add_argument('--output', required=True)
    parser.add_argument('--date', required=True)
    args = parser.parse_args()
    run(args.input, args.output, args.date)
