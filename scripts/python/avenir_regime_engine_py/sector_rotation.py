import argparse, numpy as np, pandas as pd
from pathlib import Path
from common import *

RISK_ON=['Energy','Basic Materials','Industrials','Consumer Cyclicals','Financials','Properties & Real Estate','Technology','Infrastructures','Transportation & Logistic']
DEF=['Consumer Non-Cyclicals','Healthcare']
WEIGHTS={'sector_return_breadth_score':.25,'risk_on_participation_score':.20,'sector_value_confirmation_score':.20,'sector_breadth_score':.15,'leadership_breadth_score':.10,'risk_on_vs_defensive_score':.10}

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

def s_spread(x): return score_piecewise(x,[(-.03,10),(-.02,20),(-.01,35),(0,50),(.005,65),(.01,75),(.02,90),(.03,100)])

def calculate(stock, master, index_df=None):
    merged=stock.merge(master,on='code',how='left'); missing=int(merged.sector.isna().sum()); merged=merged.dropna(subset=['sector']).copy()
    if merged.empty: raise ValueError('tidak ada saham match sector master')
    detail=merged.groupby('sector').apply(lambda g:pd.Series({'sector_return':wavg(g),'sector_value':g.trading_value.sum(),'sector_market_cap':g.market_cap.sum(skipna=True),'advancers':(g.stock_return>0).sum(),'decliners':(g.stock_return<0).sum(),'stable':(g.stock_return==0).sum(),'stock_count':len(g)})).reset_index()
    
    if index_df is not None:
        sector_map = {
            'IDXENERGY': 'Energy', 'IDXBASIC': 'Basic Materials', 'IDXINDUST': 'Industrials',
            'IDXCYCLIC': 'Consumer Cyclicals', 'IDXFINANCE': 'Financials', 'IDXPROPERT': 'Properties & Real Estate',
            'IDXTECHNO': 'Technology', 'IDXINFRA': 'Infrastructures', 'IDXTRANS': 'Transportation & Logistic',
            'IDXNONCYC': 'Consumer Non-Cyclicals', 'IDXHEALTH': 'Healthcare'
        }
        idx_returns = {}
        idx_values = {}
        # Columns might be 'index_code', 'change', 'previous', 'value'
        code_col = find_col(index_df, ['index_code', 'index', 'code', 'indeks'])
        prev_col = find_col(index_df, ['previous', 'prev', 'sebelumnya'])
        chg_col = find_col(index_df, ['change', 'perubahan'])
        val_col = find_col(index_df, ['value', 'nilai', 'trading_value'])
        
        if code_col and prev_col and chg_col and val_col:
            for _, row in index_df.iterrows():
                c = str(row[code_col]).upper().strip()
                if c in sector_map:
                    try:
                        p = float(str(row[prev_col]).replace(',', ''))
                        chg = float(str(row[chg_col]).replace(',', ''))
                        val = float(str(row[val_col]).replace(',', ''))
                        if p > 0:
                            idx_returns[sector_map[c]] = chg / p
                            idx_values[sector_map[c]] = val
                    except: pass
        
        if idx_returns:
            detail['sector_return'] = detail['sector'].apply(lambda s: idx_returns.get(s, detail.loc[detail.sector == s, 'sector_return'].values[0] if len(detail.loc[detail.sector == s, 'sector_return'].values) > 0 else 0))
        if idx_values:
            detail['sector_value'] = detail['sector'].apply(lambda s: idx_values.get(s, detail.loc[detail.sector == s, 'sector_value'].values[0] if len(detail.loc[detail.sector == s, 'sector_value'].values) > 0 else 0))

    detail['sector_breadth_ratio']=detail.apply(lambda r:safe_div(r.advancers,r.advancers+r.decliners),axis=1)
    total=len(detail); pos=int((detail.sector_return>0).sum()); risk=detail[detail.sector.isin(RISK_ON)]; defensive=detail[detail.sector.isin(DEF)]
    risk_pos=int((risk.sector_return>0).sum()); val_pos=detail.loc[detail.sector_return>0,'sector_value'].sum(); val_neg=detail.loc[detail.sector_return<0,'sector_value'].sum(); spread=risk.sector_return.mean()-defensive.sector_return.mean()
    comp={'sector_return_breadth_score':safe_div(pos,total)*100,'risk_on_participation_score':safe_div(risk_pos,len(risk))*100,'sector_value_confirmation_score':safe_div(val_pos,val_pos+val_neg)*100,'sector_breadth_score':detail.sector_breadth_ratio.mean()*100,'leadership_breadth_score':leader_score(detail.sector_return),'risk_on_vs_defensive_score':s_spread(spread)}
    raw=weighted_score(comp,WEIGHTS); score=round_score(raw); lead=detail.sort_values('sector_return',ascending=False).iloc[0]; lag=detail.sort_values('sector_return').iloc[0]
    p={'sector_rotation':score,'sector_rotation_score':score,'sector_rotation_score_raw':round(raw,1),'sector_rotation_label':label(score),'positive_sector_count':pos,'total_sector_count':total,'risk_on_positive_count':risk_pos,'risk_on_total':len(risk),'leader_sector':lead.sector,'leader_return':float(lead.sector_return),'laggard_sector':lag.sector,'laggard_return':float(lag.sector_return),'risk_on_avg_return':float(risk.sector_return.mean()),'defensive_avg_return':float(defensive.sector_return.mean()),'risk_on_vs_defensive_spread':float(spread),'missing_sector_mapping_count':missing}
    p.update({k:round(v,1) for k,v in comp.items()})
    return p,detail,merged

def run(stocks_path, sector_master_path, output_dir='output_sector_rotation', stock_sheet_name=0, sector_sheet_name=0, index_summary_path=None):
    out=Path(output_dir); out.mkdir(exist_ok=True,parents=True); 
    index_df = load_table(index_summary_path) if index_summary_path else None
    p,d,m=calculate(prep_stock(load_table(stocks_path,stock_sheet_name)),prep_master(load_table(sector_master_path,sector_sheet_name)), index_df)
    d.to_csv(out/'sector_rotation_detail.csv',index=False); m.to_csv(out/'sector_rotation_stock_merged.csv',index=False); save_json(p,out/'latest_sector_rotation_score.json'); print('Sector Rotation:',p['sector_rotation_score'],p['sector_rotation_label']); return p,d,m
if __name__=='__main__':
    ap=argparse.ArgumentParser(); ap.add_argument('--stocks',required=True); ap.add_argument('--sector-master',required=True); ap.add_argument('--output-dir',default='output_sector_rotation'); ap.add_argument('--stock-sheet-name',default=0); ap.add_argument('--sector-sheet-name',default=0); ap.add_argument('--index-summary', default=None); a=ap.parse_args(); run(a.stocks,a.sector_master,a.output_dir,a.stock_sheet_name,a.sector_sheet_name, a.index_summary)
