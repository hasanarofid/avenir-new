import argparse, numpy as np, pandas as pd
from pathlib import Path
from common import *

WEIGHTS={'close_vs_ma20':.30,'ma20_vs_ma60':.25,'return_5d':.20,'return_20d':.15,'drawdown_20d':.10}

def s_close(x): return score_piecewise(x,[(-.15,5),(-.10,12),(-.07,22),(-.03,38),(0,60),(.02,75),(.05,90),(.08,100)])
def s_ma(x): return score_piecewise(x,[(-.15,5),(-.10,15),(-.07,25),(-.03,40),(0,60),(.02,75),(.05,90),(.08,100)])
def s_ret5(x): return score_piecewise(x,[(-.12,5),(-.08,12),(-.05,25),(-.02,40),(0,52),(.02,65),(.05,85),(.08,100)])
def s_ret20(x): return score_piecewise(x,[(-.20,5),(-.12,15),(-.08,25),(-.05,35),(0,52),(.03,65),(.08,85),(.12,100)])
def s_dd(x): return score_piecewise(x,[(-.25,5),(-.18,15),(-.12,30),(-.08,45),(-.05,60),(-.03,75),(-.01,90),(0,100)])
def label(x):
    if x>=86: return 'Strong Uptrend / Momentum Expansion'
    if x>=71: return 'Healthy Uptrend'
    if x>=56: return 'Recovery / Constructive Trend'
    if x>=41: return 'Repair Phase'
    if x>=21: return 'Bearish / Weak Trend'
    return 'Breakdown'

def prepare(raw):
    dc=find_col(raw,['date','tanggal','time']); cc=find_col(raw,['close','harga','penutupan','ihsg_close'])
    oc=find_col(raw,['open','pembukaan']); hc=find_col(raw,['high','tinggi','highest','tertinggi']); lc=find_col(raw,['low','rendah','lowest','terendah'])
    if dc is None or cc is None: raise ValueError('butuh date/tanggal dan close/harga')
    df=pd.DataFrame({'date':parse_date(raw[dc]),'close':raw[cc].apply(parse_number)})
    df['open']=raw[oc].apply(parse_number) if oc else np.nan
    df['high']=raw[hc].apply(parse_number) if hc else df['close']
    df['low']=raw[lc].apply(parse_number) if lc else df['close']
    df=df.dropna(subset=['date','close']).query('close>0').sort_values('date').drop_duplicates('date',keep='last').reset_index(drop=True)
    return df

def add_features(df):
    df=df.sort_values('date').copy()
    df['ma20']=df.close.rolling(20,min_periods=1).mean(); df['ma60']=df.close.rolling(60,min_periods=1).mean()
    df['ret_1d']=df.close.pct_change(1); df['ret_5d']=df.close.pct_change(5); df['ret_20d']=df.close.pct_change(20)
    df['rolling_high_20d']=df.high.rolling(20,min_periods=1).max(); df['drawdown_20d']=df.close/df.rolling_high_20d-1
    
    # Binary Scoring based on Client's Rules
    df['score_close_ma20'] = np.where(df.close > df.ma20, 30, 0)
    df['score_ma20_ma60'] = np.where(df.ma20 > df.ma60, 25, 0)
    df['score_ret_5d'] = np.where(df.ret_5d > 0, 20, 0)
    df['score_ret_20d'] = np.where(df.ret_20d > 0, 15, 0)
    df['score_dd_20d'] = np.where(df.drawdown_20d > -0.03, 10, 0)
    
    df['price_trend_score_raw'] = df['score_close_ma20'] + df['score_ma20_ma60'] + df['score_ret_5d'] + df['score_ret_20d'] + df['score_dd_20d']
    df['price_trend_score'] = df['price_trend_score_raw'].fillna(0).astype(int)
    df['price_trend_label'] = df['price_trend_score'].apply(label)
    return df

def latest_payload(df):
    r=df.dropna(subset=['price_trend_score']).iloc[-1]
    return {'date':str(r.date.date()),'price_trend':r.price_trend_score,'price_trend_score':r.price_trend_score,'price_trend_score_raw':float(r.price_trend_score_raw),'price_trend_label':r.price_trend_label,'close':r.close,'ma20':r.ma20,'ma60':r.ma60,'ret_5d':r.ret_5d,'ret_20d':r.ret_20d,'drawdown_20d':r.drawdown_20d}

def run(input_path, output_dir='output_price_trend', sheet_name=0):
    out=Path(output_dir); out.mkdir(exist_ok=True,parents=True)
    f=add_features(prepare(load_table(input_path,sheet_name)))
    f.to_csv(out/'price_trend_features.csv',index=False); p=latest_payload(f); save_json(p,out/'latest_price_trend_score.json')
    print('Price Trend:',p['price_trend_score'],p['price_trend_label']); return p,f
if __name__=='__main__':
    ap=argparse.ArgumentParser(); ap.add_argument('--input',required=True); ap.add_argument('--output-dir',default='output_price_trend'); ap.add_argument('--sheet-name',default=0); a=ap.parse_args(); run(a.input,a.output_dir,a.sheet_name)
