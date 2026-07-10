import argparse, pandas as pd
from common import save_json

LABELS={'price_trend':'price trend','market_breadth':'breadth','flow':'foreign flow','sector_rotation':'sector rotation','volatility_stability':'market stability'}

def state(score):
    if score<35: return 'stress conditions'
    if score<45: return 'risk-off pressure'
    if score<55: return 'a defensive neutral phase'
    if score<65: return 'a tactical recovery attempt'
    if score<75: return 'a constructive rotation phase'
    return 'a risk-on accumulation phase'
def phase(d):
    return ('early' if d.day<=10 else 'mid' if d.day<=20 else 'late')+' '+d.strftime('%B')
def fmt(d): return f"{d.day} {d.strftime('%B')}"
def join(items):
    if not items: return ''
    if len(items)==1: return items[0]
    if len(items)==2: return items[0]+' and '+items[1]
    return ', '.join(items[:-1])+', and '+items[-1]
def top(row):
    arr=[(lab,row[col]) for col,lab in LABELS.items() if col in row and pd.notna(row[col]) and row[col]>=55]
    if not arr: arr=[(lab,row[col]) for col,lab in LABELS.items() if col in row and pd.notna(row[col])]
    return [x[0] for x in sorted(arr,key=lambda x:x[1],reverse=True)[:3]]
def broken(peak,last):
    arr=[]
    for col,lab in LABELS.items():
        if col in peak and col in last and pd.notna(peak[col]) and pd.notna(last[col]):
            drop=peak[col]-last[col]
            if last[col]<35 or (drop>=20 and last[col]<50): arr.append((lab,drop,last[col]))
    return [x[0] for x in sorted(arr,key=lambda x:(x[2],-x[1]))[:3]]
def drag(df):
    if 'flow' in df and (df.flow.mean()<45 or df.flow.max()<50): return 'foreign flow never confirmed the rebound'
    weak=[]
    for c,l in LABELS.items():
        if c in df and df[c].mean()<45 and df[c].max()<55: weak.append((l,df[c].mean()))
    return f"{sorted(weak,key=lambda x:x[1])[0][0]} never fully confirmed the move" if weak else None

def build(history,start_date=None,end_date=None):
    df=history.copy(); df.date=pd.to_datetime(df.date); df=df.sort_values('date')
    if start_date: df=df[df.date>=pd.to_datetime(start_date)]
    if end_date: df=df[df.date<=pd.to_datetime(end_date)]
    if 'regime_score' not in df and 'final_score' in df: df['regime_score']=df['final_score']
    if 'regime_label' not in df and 'label' in df: df['regime_label']=df['label']
    if df.empty: raise ValueError('history kosong untuk periode ini')
    last=df.iloc[-1]; peak=df.loc[df.regime_score.idxmax()]; trough=df.loc[df.regime_score.idxmin()]; pre=df[df.date<=peak.date]; low=pre.loc[pre.regime_score.idxmin()]
    s1=f"The market moved from {state(low.regime_score)} in {phase(low.date)} into {state(peak.regime_score)} in {phase(peak.date)}."
    t=join(top(peak)); s2=f"The strongest confirmation appeared on {fmt(peak.date)}, supported by {t}." if t else f"The strongest confirmation appeared on {fmt(peak.date)}."
    d=drag(df); s3=f"However, {d}." if d else 'However, confirmation was not strong enough to sustain a full regime upgrade.'
    br=join(broken(peak,last));
    if last.regime_score<=peak.regime_score-15 and last.regime_score<45: s4=f"On {fmt(last.date)}, {br} broke down sharply, pushing the regime back into {last.regime_label}." if br else f"On {fmt(last.date)}, the regime deteriorated sharply, pushing the market back into {last.regime_label}."
    else: s4=f"By {fmt(last.date)}, the regime ended the period in {last.regime_label}."
    return {'start_date':df.date.min().strftime('%Y-%m-%d'),'end_date':df.date.max().strftime('%Y-%m-%d'),'peak_date':peak.date.strftime('%Y-%m-%d'),'peak_score':int(peak.regime_score),'trough_date':trough.date.strftime('%Y-%m-%d'),'trough_score':int(trough.regime_score),'final_date':last.date.strftime('%Y-%m-%d'),'final_score':int(last.regime_score),'final_label':last.regime_label,'period_conclusion':' '.join([s1,s2,s3,s4])}

def run(history_csv, output_json='period_conclusion_payload.json', start_date=None, end_date=None):
    p=build(pd.read_csv(history_csv),start_date,end_date); save_json(p,output_json); print(p['period_conclusion']); return p
if __name__=='__main__':
    ap=argparse.ArgumentParser(); ap.add_argument('--history',required=True); ap.add_argument('--output-json',default='period_conclusion_payload.json'); ap.add_argument('--start-date'); ap.add_argument('--end-date'); a=ap.parse_args(); run(a.history,a.output_json,a.start_date,a.end_date)
