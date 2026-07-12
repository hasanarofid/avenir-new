import argparse, numpy as np, pandas as pd
from pathlib import Path
from common import *

def load_flow(path):
    raw=pd.read_csv(path); raw=raw.loc[:,~raw.columns.astype(str).str.startswith('Unnamed')]
    dc=find_col(raw,['date','tanggal','time']); fc=find_col(raw,['foreign_net','foreign net','net foreign','foreign flow','foreign_net_value','foreign net buy','net buy asing','net asing']); vc=find_col(raw,['market_value','market value','trading value','value','nilai transaksi','total value']); bc=find_col(raw,['broker_pulse_score','broker_score','broker pulse'])
    if dc is None or fc is None or vc is None: raise ValueError('butuh date, foreign_net, market_value')
    df=pd.DataFrame({'date':parse_date(raw[dc]),'foreign_net':raw[fc].apply(parse_number),'market_value_raw':raw[vc].apply(parse_number)})
    df['market_value_negative_flag']=df.market_value_raw<0; df['market_value']=df.market_value_raw.abs(); df['broker_pulse_score']=raw[bc].apply(parse_number) if bc else np.nan
    df=df.dropna(subset=['date','foreign_net','market_value']); df=df[df.market_value>0].sort_values('date').drop_duplicates('date',keep='last').reset_index(drop=True)
    n=int(df.market_value_negative_flag.sum())
    if n: print(f'Warning: {n} market_value negatif dikoreksi menjadi absolut.')
    return df

def s_int(x): return score_piecewise(x,[(-.050,5),(-.030,15),(-.015,30),(-.005,42),(0,50),(.005,60),(.015,75),(.030,90),(.050,100)])
def s_cons(d):
    if pd.isna(d): return np.nan
    d=int(d); return 100 if d>=5 else 85 if d==4 else 65 if d==3 else 45 if d==2 else 25 if d==1 else 10
def s_liq(r): return score_piecewise(r,[(.30,15),(.50,30),(.70,45),(.90,60),(1.00,70),(1.20,85),(1.50,100)])
def label(x):
    if x>=80: return 'Strong Accumulation'
    if x>=65: return 'Accumulation'
    if x>=50: return 'Mixed / Improving'
    if x>=35: return 'Distribution Pressure'
    return 'Heavy Outflow'

def calc_row(r):
    comp={'trend':r.foreign_flow_trend_score,'consistency':r.flow_consistency_score,'liquidity':r.liquidity_confirmation_score,'broker':r.broker_pulse_score_clean}
    wt={'trend':.40,'consistency':.25,'liquidity':.25,'broker':.10}; return round(weighted_score(comp,wt),0)

def add_features(df):
    df=df.sort_values('date').copy(); df['foreign_net_1d']=df.foreign_net; df['foreign_net_5d']=df.foreign_net.rolling(5,min_periods=1).sum(); df['foreign_net_20d']=df.foreign_net.rolling(20,min_periods=1).sum(); df['market_value_1d']=df.market_value; df['total_market_value_5d']=df.market_value.rolling(5,min_periods=1).sum(); df['total_market_value_20d']=df.market_value.rolling(20,min_periods=1).sum(); df['avg_market_value_20d']=df.market_value.rolling(20,min_periods=1).mean(); df['positive_foreign_flow_days_5d']=(df.foreign_net>0).astype(int).rolling(5,min_periods=1).sum()
    df['foreign_intensity_1d']=df.apply(lambda r:safe_div(r.foreign_net_1d,r.market_value_1d),axis=1); df['foreign_intensity_5d']=df.apply(lambda r:safe_div(r.foreign_net_5d,r.total_market_value_5d),axis=1); df['foreign_intensity_20d']=df.apply(lambda r:safe_div(r.foreign_net_20d,r.total_market_value_20d),axis=1); df['liquidity_ratio_5d_vs_20d_avg']=df.apply(lambda r:safe_div(r.total_market_value_5d,r.avg_market_value_20d*5 if pd.notna(r.avg_market_value_20d) else np.nan),axis=1)
    df['foreign_flow_1d_score']=df.foreign_intensity_1d.apply(s_int); df['foreign_flow_5d_score']=df.foreign_intensity_5d.apply(s_int); df['foreign_flow_20d_score']=df.foreign_intensity_20d.apply(s_int); df['foreign_flow_trend_score']=.20*df.foreign_flow_1d_score+.50*df.foreign_flow_5d_score+.30*df.foreign_flow_20d_score; df['flow_consistency_score']=df.positive_foreign_flow_days_5d.apply(s_cons); df['liquidity_confirmation_score']=df.liquidity_ratio_5d_vs_20d_avg.apply(s_liq); df['broker_pulse_score_clean']=df.broker_pulse_score.apply(lambda x:np.nan if pd.isna(x) else clamp(x)); df['flow_score']=df.apply(calc_row,axis=1); df['flow_label']=df.flow_score.apply(lambda x:None if pd.isna(x) else label(x)); return df

def latest(df):
    r=df.dropna(subset=['flow_score']).iloc[-1]
    keys=['foreign_net_1d','foreign_net_5d','foreign_net_20d','market_value_raw','market_value_negative_flag','market_value_1d','total_market_value_5d','total_market_value_20d','avg_market_value_20d','foreign_intensity_1d','foreign_intensity_5d','foreign_intensity_20d','positive_foreign_flow_days_5d','liquidity_ratio_5d_vs_20d_avg','foreign_flow_trend_score','flow_consistency_score','liquidity_confirmation_score']
    p={'date':str(r.date.date()),'flow':r.flow_score,'flow_score':r.flow_score,'flow_label':r.flow_label}
    for k in keys: p[k]=r.get(k)
    return p

def run(input_path, output_dir='output_flow_score'):
    out=Path(output_dir); out.mkdir(exist_ok=True,parents=True); f=add_features(load_flow(input_path)); f.to_csv(out/'flow_score_features.csv',index=False); p=latest(f); save_json(p,out/'latest_flow_score.json'); print('Flow:',p['flow_score'],p['flow_label']); return p,f
if __name__=='__main__':
    ap=argparse.ArgumentParser(); ap.add_argument('--input',required=True); ap.add_argument('--output-dir',default='output_flow_score'); a=ap.parse_args(); run(a.input,a.output_dir)
