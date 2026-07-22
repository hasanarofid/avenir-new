import sys
sys.path.append('scripts/python/avenir_regime_engine_py')
import pandas as pd

ringkasan = '/home/hasanarofid/Documents/hasanarofid/avenir/excel_terbaru/storage/app/private/ringkasan_saham/xDtPemlaNovkerR1WR1eTqxE8jcBkvNwQthUtDkb.xlsx'
master_file = 'storage/app/sector_master.csv'

df = pd.read_excel(ringkasan)
df.columns = [c.lower().strip() for c in df.columns]
df.rename(columns={'ticker':'code'}, inplace=True)
master = pd.read_csv(master_file)

merged = df.merge(master, on='code', how='left')
missing = merged[merged.sector.isna()]
print("Missing sectors for:")
print(missing[['code', 'name', 'close']])
