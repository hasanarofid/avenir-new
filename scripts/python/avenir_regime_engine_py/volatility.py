import argparse, numpy as np, pandas as pd
from pathlib import Path
from common import *

def s_liq(r,ret):
    if pd.isna(r) or pd.isna(ret): return np.nan
    pts=[(.30,20),(.50,35),(.70,50),(.90,65),(1,75),(1.2,90),(1.5,100)] if ret>=0 else [(.30,55),(.50,50),(.70,42),(.90,35),(1.1,25),(1.3,15),(1.5,10)]
    return score_piecewise(r,pts)
def s_range(x): return score_piecewise(x,[(0,70),(.005,90),(.010,85),(.015,75),(.020,60),(.030,40),(.050,20),(.080,10)])
def s_ret(x): return score_piecewise(x,[(-.060,5),(-.040,15),(-.025,30),(-.015,45),(-.005,60),(0,70),(.010,80),(.020,90),(.040,100)])
def s_close(x): return score_piecewise(x,[(0,10),(.20,25),(.40,45),(.50,55),(.60,65),(.80,85),(1,100)])
def s_vol(x):
    if pd.isna(x): return np.nan
    return score_piecewise(clamp(x),[(0,100),(20,85),(40,65),(60,45),(80,25),(100,10)])
def label(x):
    if x>=80: return 'Very Stable Tape'
    if x>=65: return 'Stable Tape'
    if x>=50: return 'Mixed Stability'
    if x>=35: return 'Stress Building'
    return 'High Volatility / Sell Pressure'

def prepare(raw):
    dc=find_col(raw,['date','tanggal','time']); pc=find_col(raw,['previous_close','previous','prev_close','prev','sebelumnya']); hc=find_col(raw,['high','highest','tertinggi']); lc=find_col(raw,['low','lowest','terendah']); cc=find_col(raw,['close','closing','penutupan','ihsg_close']); vc=find_col(raw,['market_value','market value','trading value','value','nilai transaksi','total value']); ac=find_col(raw,['avg_market_value_20d','avg value 20d','avg_value_20d']); vol=find_col(raw,['volatility_percentile','realized_vol_percentile'])
    miss=[n for n,c in {'date':dc,'previous_close':pc,'high':hc,'low':lc,'close':cc,'market_value':vc}.items() if c is None]
    if miss: raise ValueError(f'Kolom wajib belum ada: {miss}')
    df=pd.DataFrame({'date':parse_date(raw[dc]),'previous_close':raw[pc].apply(parse_number),'high':raw[hc].apply(parse_number),'low':raw[lc].apply(parse_number),'close':raw[cc].apply(parse_number),'market_value_raw':raw[vc].apply(parse_number)})
    df['market_value_negative_flag']=df.market_value_raw<0; df['market_value']=df.market_value_raw.abs(); df['avg_market_value_20d_input']=raw[ac].apply(parse_number).abs() if ac else np.nan; df['volatility_percentile']=raw[vol].apply(parse_number) if vol else np.nan
    df=df.dropna(subset=['date','previous_close','high','low','close','market_value']); df=df[(df.previous_close>0)&(df.high>0)&(df.low>0)&(df.close>0)&(df.market_value>0)].sort_values('date').drop_duplicates('date',keep='last').reset_index(drop=True); return df

def add_vol_pct(df):
    df=df.sort_values('date').copy()
    if len(df)<2:
        df['daily_return_for_vol']=np.nan
        df['realized_vol_20d']=np.nan
        df['volatility_percentile_calc']=np.nan
        return df
    df['daily_return_for_vol']=df.close.pct_change()
    df['realized_vol_20d']=df.daily_return_for_vol.rolling(20,min_periods=1).std()*np.sqrt(252)
    def pct(win):
        s=pd.Series(win).dropna()
        if len(s)==0: return np.nan
        return (s<=s.iloc[-1]).mean()*100
    df['volatility_percentile_calc']=df.realized_vol_20d.rolling(252,min_periods=1).apply(pct,raw=False)
    return df

def add_features(df):
    if len(df)==0:
        raise ValueError("Dataframe volatility kosong")
    df=add_vol_pct(df)
    df['volatility_percentile']=df.volatility_percentile.fillna(df.volatility_percentile_calc).fillna(50)
    df['ihsg_return_1d']=(df.close/df.previous_close-1).fillna(0)
    df['intraday_range_pct']=((df.high-df.low)/df.previous_close).fillna(0)
    df['close_location']=df.apply(lambda r:safe_div(r.close-r.low,r.high-r.low,default=.5),axis=1)
    df['avg_market_value_20d_calc']=df.market_value.rolling(20,min_periods=1).mean()
    df['avg_market_value_20d']=df.avg_market_value_20d_input.fillna(df.avg_market_value_20d_calc)
    df['liquidity_ratio']=df.apply(lambda r:safe_div(r.market_value,r.avg_market_value_20d,default=1.0),axis=1)
    df['volatility_regime_score']=df.volatility_percentile.apply(s_vol)
    df['liquidity_quality_score']=df.apply(lambda r:s_liq(r.liquidity_ratio,r.ihsg_return_1d),axis=1)
    df['intraday_range_score']=df.intraday_range_pct.apply(s_range)
    df['return_shock_score']=df.ihsg_return_1d.apply(s_ret)
    df['close_location_score']=df.close_location.apply(s_close)
    wt={'volatility_regime_score':.30,'liquidity_quality_score':.25,'intraday_range_score':.20,'return_shock_score':.15,'close_location_score':.10}
    df['volatility_stability_score_raw']=df.apply(lambda r:weighted_score({k:r[k] for k in wt},wt),axis=1)
    df['volatility_stability_score']=df.volatility_stability_score_raw.apply(round_score).fillna(50)
    df['volatility_stability_label']=df.volatility_stability_score.apply(label)
    return df

def latest(df):
    valid=df.dropna(subset=['volatility_stability_score'])
    r=valid.iloc[-1] if len(valid)>0 else df.iloc[-1]
    keys=['previous_close','high','low','close','market_value_raw','market_value_negative_flag','market_value','avg_market_value_20d','liquidity_ratio','ihsg_return_1d','intraday_range_pct','close_location','volatility_percentile','volatility_regime_score','liquidity_quality_score','intraday_range_score','return_shock_score','close_location_score']
    v_val=int(r.volatility_stability_score) if pd.notna(r.get('volatility_stability_score')) else 50
    v_lbl=r.volatility_stability_label if r.get('volatility_stability_label') else label(v_val)
    p={'date':str(r.date.date()) if hasattr(r.date,'date') else str(r.date)[:10],'volatility_stability':v_val,'volatility_stability_score_raw':v_val,'volatility_stability_score':v_val,'volatility_stability_label':v_lbl}
    for k in keys: p[k]=r.get(k) if pd.notna(r.get(k)) else 0
    return p

def run(input_path, output_dir='output_volatility_stability', sheet_name=0):
    out=Path(output_dir); out.mkdir(exist_ok=True,parents=True); f=add_features(prepare(load_table(input_path,sheet_name))); f.to_csv(out/'volatility_stability_features.csv',index=False); p=latest(f); save_json(p,out/'latest_volatility_stability_score.json'); print('Volatility/Stability:',p['volatility_stability_score'],p['volatility_stability_label']); return p,f
if __name__=='__main__':
    ap=argparse.ArgumentParser(); ap.add_argument('--input',required=True); ap.add_argument('--output-dir',default='output_volatility_stability'); ap.add_argument('--sheet-name',default=0); a=ap.parse_args(); run(a.input,a.output_dir,a.sheet_name)
