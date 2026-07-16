#!/usr/bin/env python3
"""
Import/Update master_stocks dari Financial Data Excel.
Kolom yang dipakai (A-H+):
  col1 = No.
  col2 = Sector
  col3 = Sub Industry Code
  col4 = Sub Industry
  col5 = Code (Ticker)
  col6 = Stock Name
  col7 = Sharia (S/-)
  col8 = FS Date
  col9 = Fiscal Year End

Logo: TIDAK ada di file Excel (gambar overlay, bukan embedded cell).
      Akan di-resolve dari IDX/Bursa Efek Indonesia API otomatis.

Usage:
  php artisan tinker --execute="require base_path('scripts/import_master_stocks.php');"
  atau langsung:
  python3 scripts/import_master_stocks.py [path_to_excel]
"""

import pandas as pd
import json
import sys
import re
import math
import os

DEFAULT_EXCEL = os.path.join(os.path.dirname(__file__), '../prd-testing/prd/Financial Data and Ratio - May 2026 (1).xlsx')

def safe_str(v):
    if v is None or (isinstance(v, float) and math.isnan(v)):
        return None
    s = str(v).strip()
    return s if s and s != 'nan' else None

def parse_financial_data(excel_path):
    df = pd.read_excel(excel_path, header=None, skiprows=5)

    # Column mapping (0-indexed, after skiprows=5):
    # 0=empty, 1=No., 2=Sector, 3=SubIndustryCodel, 4=SubIndustry,
    # 5=Code/Ticker, 6=StockName, 7=Sharia, 8=FSDate, 9=FiscalYearEnd

    records = []
    for _, row in df.iterrows():
        ticker = safe_str(row.iloc[5])
        # Ticker harus string 3-6 karakter (IDX format)
        if not ticker or not re.match(r'^[A-Z]{3,6}$', ticker):
            continue

        record = {
            'code':               ticker,
            'name':               safe_str(row.iloc[6]),
            'sector':             safe_str(row.iloc[2]),
            'sub_industry_code':  safe_str(row.iloc[3]),
            'sub_industry':       safe_str(row.iloc[4]),
            'is_sharia':          1 if safe_str(row.iloc[7]) == 'S' else 0,
            'fs_date':            safe_str(row.iloc[8]),
            'fiscal_year_end':    safe_str(row.iloc[9]),
        }
        records.append(record)

    return records

def main():
    excel_path = sys.argv[1] if len(sys.argv) > 1 else DEFAULT_EXCEL

    if not os.path.exists(excel_path):
        print(json.dumps({'status': 'error', 'message': f'File not found: {excel_path}'}))
        sys.exit(1)

    try:
        records = parse_financial_data(excel_path)
    except Exception as e:
        print(json.dumps({'status': 'error', 'message': str(e)}))
        sys.exit(1)

    # Summary per sector
    sectors = {}
    for r in records:
        s = r['sector'] or 'Unknown'
        sectors[s] = sectors.get(s, 0) + 1

    print(json.dumps({
        'status': 'success',
        'total': len(records),
        'sectors': sectors,
        'records': records,
    }))

if __name__ == '__main__':
    main()
