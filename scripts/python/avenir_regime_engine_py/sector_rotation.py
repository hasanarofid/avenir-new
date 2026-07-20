import argparse, numpy as np, pandas as pd
from pathlib import Path
from common import *

RISK_ON=['Energy','Basic Materials','Industrials','Consumer Cyclicals','Financials','Properties & Real Estate','Technology','Infrastructures','Transportation & Logistic']
DEF=['Consumer Non-Cyclicals','Healthcare']
WEIGHTS={'sector_return_breadth_score':.25,'risk_on_participation_score':.25,'sector_value_confirmation_score':.15,'sector_breadth_score':.15,'leadership_breadth_score':.10,'risk_on_vs_defensive_score':.10}

def label(x):
    if x>=80: return 'Strong Sector Rotation'
    if x>=65: return 'Constructive Sector Rotation'
    if x>=50: return 'Selective Sector Rotation'
    if x>=35: return 'Weak Sector Rotation'
    return 'Sector-Wide Pressure'

def prep_stock(raw):
    code=find_col(raw,['kode_saham','kode','code','symbol']); prev=find_col(raw,['sebelumnya','previous','prev','prev_close']); close=find_col(raw,['penutupan','close','last','closing_price']); val=find_col(raw,['nilai','value','trading_value']); shares=find_col(raw,['listed_shares','listed_share','saham_tercatat'])
    miss=[n for n,c in {'code':code,'previous':prev,'close':close,'value':val}.items() if c is None]
    if miss: raise ValueError(f'Kolom wajib Ringkasan Saham belum ada: {miss}')
    df=pd.DataFrame({'code':raw[code].astype(str).str.strip().str.upper(),'previous':raw[prev].apply(parse_number),'close':raw[close].apply(parse_number),'trading_value':raw[val].apply(parse_number).abs()})
    df['listed_shares']=raw[shares].apply(parse_number) if shares else np.nan; df['market_cap']=df.close*df.listed_shares
    df=df[df.code.notna() & ~df.code.str.lower().isin(['nan','']) & df.previous.notna() & df.close.notna() & df.trading_value.notna() & (df.previous>0)&(df.close>0)&(df.trading_value>=0)].copy(); df['stock_return']=df.close/df.previous-1; return df

def prep_master(raw):
    code=find_col(raw,['code','kode','kode_saham','symbol']); sec=find_col(raw,['sector','sektor'])
    if code is None or sec is None: raise ValueError('sector master butuh code dan sector')
    m=pd.DataFrame({'code':raw[code].astype(str).str.strip().str.upper(),'sector':raw[sec].astype(str).str.strip()}).drop_duplicates('code',keep='last')
    return m

def wavg(g):
    if g.market_cap.notna().sum()>0 and g.market_cap.sum()>0: return np.average(g.stock_return,weights=g.market_cap)
    if g.trading_value.sum()>0: return np.average(g.stock_return,weights=g.trading_value)
    return g.stock_return.mean()

def leader_score(sec_ret):
    pos=sec_ret[sec_ret>0]
    if len(pos)==0: return 10
    if len(pos)==1: return 25
    w=pos/pos.sum(); hhi=np.sum(w**2); return max(10,min((1-hhi)/(1-1/len(pos))*100,100))

def s_spread(x): return score_piecewise(x,[(-.02,10),(-.01,30),(-.005,45),(0,55),(.005,70),(.01,85),(.02,100)])
def s_breadth(x): return score_piecewise(x, [(0, 10), (0.25, 35), (0.40, 45), (0.50, 55), (0.60, 65), (0.75, 85), (1.00, 100)])
def s_val_conf(x): return score_piecewise(x, [(0, 20), (0.30, 40), (0.50, 60), (0.70, 80), (1.00, 100)])
def s_sec_breadth(x): return score_piecewise(x, [(0, 10), (0.30, 35), (0.50, 55), (0.70, 75), (1.00, 100)])

def calculate(stock, master):
    merged=stock.merge(master,on='code',how='left'); missing=int(merged.sector.isna().sum()); merged=merged.dropna(subset=['sector']).copy()
    if merged.empty: raise ValueError('tidak ada saham match sector master')
    detail=merged.groupby('sector').apply(lambda g:pd.Series({'sector_return':wavg(g),'sector_value':g.trading_value.sum(),'sector_market_cap':g.market_cap.sum(skipna=True),'advancers':(g.stock_return>0).sum(),'decliners':(g.stock_return<0).sum(),'stable':(g.stock_return==0).sum(),'stock_count':len(g)})).reset_index()
    detail['sector_breadth_ratio']=detail.apply(lambda r:safe_div(r.advancers,r.advancers+r.decliners),axis=1)
    total=len(detail); pos=int((detail.sector_return>0).sum()); risk=detail[detail.sector.isin(RISK_ON)]; defensive=detail[detail.sector.isin(DEF)]
    risk_pos=int((risk.sector_return>0).sum()); val_pos=detail.loc[detail.sector_return>0,'sector_value'].sum(); val_neg=detail.loc[detail.sector_return<0,'sector_value'].sum(); spread=risk.sector_return.mean()-defensive.sector_return.mean()
    comp={'sector_return_breadth_score':s_breadth(safe_div(pos,total)),'risk_on_participation_score':s_breadth(safe_div(risk_pos,len(risk))),'sector_value_confirmation_score':s_val_conf(safe_div(val_pos,val_pos+val_neg)),'sector_breadth_score':s_sec_breadth(detail.sector_breadth_ratio.mean()),'leadership_breadth_score':s_sec_breadth(leader_score(detail.sector_return)/100),'risk_on_vs_defensive_score':s_spread(spread)}
    raw=weighted_score(comp,WEIGHTS); score=round_score(raw); lead=detail.sort_values('sector_return',ascending=False).iloc[0]; lag=detail.sort_values('sector_return').iloc[0]
    p={'sector_rotation':score,'sector_rotation_score':score,'sector_rotation_score_raw':round(raw,1),'sector_rotation_label':label(score),'positive_sector_count':pos,'total_sector_count':total,'risk_on_positive_count':risk_pos,'risk_on_total':len(risk),'leader_sector':lead.sector,'leader_return':float(lead.sector_return),'laggard_sector':lag.sector,'laggard_return':float(lag.sector_return),'risk_on_avg_return':float(risk.sector_return.mean()),'defensive_avg_return':float(defensive.sector_return.mean()),'risk_on_vs_defensive_spread':float(spread),'missing_sector_mapping_count':missing}
    p.update({k:round(v,1) for k,v in comp.items()})
    return p,detail,merged

def run(stocks_path, sector_master_path, output_dir='output_sector_rotation', stock_sheet_name=0, sector_sheet_name=0):
    out=Path(output_dir); out.mkdir(exist_ok=True,parents=True); p,d,m=calculate(prep_stock(load_table(stocks_path,stock_sheet_name)),prep_master(load_table(sector_master_path,sector_sheet_name))); d.to_csv(out/'sector_rotation_detail.csv',index=False); m.to_csv(out/'sector_rotation_stock_merged.csv',index=False); save_json(p,out/'latest_sector_rotation_score.json'); print('Sector Rotation:',p['sector_rotation_score'],p['sector_rotation_label']); return p,d,m
if __name__=='__main__':
    ap=argparse.ArgumentParser(); ap.add_argument('--stocks',required=True); ap.add_argument('--sector-master',required=True); ap.add_argument('--output-dir',default='output_sector_rotation'); ap.add_argument('--stock-sheet-name',default=0); ap.add_argument('--sector-sheet-name',default=0); a=ap.parse_args(); run(a.stocks,a.sector_master,a.output_dir,a.stock_sheet_name,a.sector_sheet_name)
