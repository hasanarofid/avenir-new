import sys
sys.path.append('scripts/python/avenir_regime_engine_py')
import sector_rotation
import pandas as pd

ringkasan = '/home/hasanarofid/Documents/hasanarofid/avenir/excel_terbaru/storage/app/private/ringkasan_saham/xDtPemlaNovkerR1WR1eTqxE8jcBkvNwQthUtDkb.xlsx'
master_file = 'storage/app/sector_master.csv'

df = pd.read_excel(ringkasan)
df.columns = [c.lower().strip() for c in df.columns]
df.rename(columns={'ticker':'code'}, inplace=True)
master = pd.read_csv(master_file)

res = sector_rotation.calculate(df, master)
print("SECTOR ROTATION:")
for k,v in res['components'].items(): print(f"{k}: {v}")
print(f"Total Score: {res['sector_rotation_score']}")

import volatility
ohlc_file = '/home/hasanarofid/Documents/hasanarofid/avenir/avenir-new/storage/app/temp/ohlc_temp.csv'
# If that doesn't exist I'll generate it.
