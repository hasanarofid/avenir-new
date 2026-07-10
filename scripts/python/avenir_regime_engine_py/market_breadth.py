import argparse, numpy as np, pandas as pd
from pathlib import Path
from common import *

WEIGHTS={'ad_score':.30,'strong_movers_score':.20,'mcap_breadth_score':.20,'value_breadth_score':.20,'active_participation_score':.10}

def mb_label(x):
    if x>=80: return 'Strong Positive Breadth'
    if x>=65: return 'Positive Breadth'
    if x>=50: return 'Neutral / Improving'
    if x>=35: return 'Weak Breadth'
    return 'Broad Weakness'

def calculate(raw):
    code=find_col(raw,['kode_saham','kode','code','symbol']); prev=find_col(raw,['sebelumnya','previous','prev','prev_close']); close=find_col(raw,['penutupan','close','closing_price','last']); shares=find_col(raw,['listed_shares','listed_share','saham_tercatat']); val=find_col(raw,['nilai','value','trading_value'])
    miss=[n for n,c in {'code':code,'previous':prev,'close':close,'listed_shares':shares,'value':val}.items() if c is None]
    if miss: raise ValueError(f'Kolom wajib tidak ditemukan: {miss}')
    df=pd.DataFrame(); df['symbol']=raw[code].astype(str).str.strip().str.upper(); df['previous']=raw[prev].apply(parse_number); df['close']=raw[close].apply(parse_number); df['listed_shares']=raw[shares].apply(parse_number); df['trading_value_raw']=raw[val].apply(parse_number); df['trading_value_negative_flag']=df.trading_value_raw<0; df['trading_value']=df.trading_value_raw.abs()
    df=df[df.symbol.notna() & ~df.symbol.str.lower().isin(['nan','total','']) & df.previous.notna() & df.close.notna() & df.listed_shares.notna() & df.trading_value.notna() & (df.previous>0) & (df.close>0) & (df.listed_shares>0) & (df.trading_value>=0)].copy()
    df['return_pct']=df.close/df.previous-1; df['market_cap']=df.close*df.listed_shares; eps=1e-12
    df['bucket']=np.select([df.return_pct < -0.02,(df.return_pct>=-0.02)&(df.return_pct < -eps),df.return_pct.abs()<=eps,(df.return_pct>eps)&(df.return_pct<=0.02),df.return_pct>0.02],['down_gt2','down_0_2','stable','up_0_2','up_gt2'],'unknown')
    c=df.bucket.value_counts().to_dict(); m=df.groupby('bucket').market_cap.sum().to_dict(); v=df.groupby('bucket').trading_value.sum().to_dict()
    up_gt2,up_0_2,stable,down_0_2,down_gt2=[c.get(k,0) for k in ['up_gt2','up_0_2','stable','down_0_2','down_gt2']]
    adv=up_gt2+up_0_2; dec=down_gt2+down_0_2; total=adv+dec+stable
    m_up=m.get('up_gt2',0)+m.get('up_0_2',0); m_down=m.get('down_gt2',0)+m.get('down_0_2',0); m_stable=m.get('stable',0)
    v_up=v.get('up_gt2',0)+v.get('up_0_2',0); v_down=v.get('down_gt2',0)+v.get('down_0_2',0); v_stable=v.get('stable',0)
    metrics={'ad_score':safe_div(adv,adv+dec)*100,'strong_movers_score':safe_div(up_gt2,up_gt2+down_gt2)*100,'mcap_breadth_score':safe_div(m_up,m_up+m_down)*100,'value_breadth_score':safe_div(v_up,v_up+v_down)*100,'active_participation_score':safe_div(adv+dec,total)*100}
    raw_score=weighted_score(metrics,WEIGHTS); score=round_score(raw_score)
    s={'total_stocks':int(total),'advancers':int(adv),'decliners':int(dec),'stable':int(stable),'up_gt2_n':int(up_gt2),'up_0_2_n':int(up_0_2),'down_0_2_n':int(down_0_2),'down_gt2_n':int(down_gt2),'mcap_up':float(m_up),'mcap_down':float(m_down),'mcap_stable':float(m_stable),'value_up':float(v_up),'value_down':float(v_down),'value_stable':float(v_stable),'ad_score':round(metrics['ad_score'],1),'strong_movers_score':round(metrics['strong_movers_score'],1),'mcap_breadth_score':round(metrics['mcap_breadth_score'],1),'value_breadth_score':round(metrics['value_breadth_score'],1),'active_participation_score':round(metrics['active_participation_score'],1),'market_breadth_score_raw':round(raw_score,1),'market_breadth':score,'market_breadth_score':score,'market_breadth_label':mb_label(score),'negative_value_corrected_count':int(df.trading_value_negative_flag.sum())}
    return s,df

def run(input_path, output_dir='output_market_breadth', sheet_name=0):
    out=Path(output_dir); out.mkdir(exist_ok=True,parents=True)
    s,d=calculate(load_table(input_path,sheet_name)); pd.DataFrame([s]).to_csv(out/'market_breadth_summary.csv',index=False); d.to_csv(out/'market_breadth_stock_detail.csv',index=False); save_json(s,out/'latest_market_breadth_score.json')
    print('Market Breadth:',s['market_breadth_score'],s['market_breadth_label']); return s,d
if __name__=='__main__':
    ap=argparse.ArgumentParser(); ap.add_argument('--input',required=True); ap.add_argument('--output-dir',default='output_market_breadth'); ap.add_argument('--sheet-name',default=0); a=ap.parse_args(); run(a.input,a.output_dir,a.sheet_name)
