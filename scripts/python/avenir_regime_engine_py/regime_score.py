import argparse, pandas as pd
from common import *

WEIGHTS={'price_trend':.30,'market_breadth':.25,'flow':.20,'sector_rotation':.15,'volatility_stability':.10}

def comp_label(k,x):
    maps={
    'price_trend':[(70,'healthy price trend'),(55,'recovering price trend'),(40,'fragile price trend'),(0,'weak price trend')],
    'market_breadth':[(80,'strong positive breadth'),(65,'positive breadth'),(50,'neutral breadth'),(35,'weak breadth'),(0,'broad weakness')],
    'flow':[(70,'strong accumulation'),(50,'mixed flow'),(35,'distribution pressure'),(0,'heavy outflow')],
    'sector_rotation':[(70,'broad sector rotation'),(50,'selective sector rotation'),(35,'weak sector rotation'),(0,'sector-wide pressure')],
    'volatility_stability':[(70,'stable tape'),(50,'mixed stability'),(35,'stress building'),(0,'high volatility / sell pressure')]}
    for th,l in maps[k]:
        if x>=th: return l
    return 'neutral'

def supports_drags(cs):
    sup=[]; drag=[]
    for k,v in cs.items():
        item={'component':k,'score':v,'label':comp_label(k,v)}
        if v>=60: sup.append(item)
        if v<40: drag.append(item)
    return sorted(sup,key=lambda x:x['score'],reverse=True), sorted(drag,key=lambda x:x['score'])

def headline(score,cs):
    if score<35:
        if cs['price_trend']>=45 and cs['market_breadth']<35 and cs['sector_rotation']<35: return 'Rebound failed to confirm.'
        return 'Risk-off pressure dominates.'
    if score<45: return 'Market remains in risk-off mode.'
    if score<55: return 'Market is stabilizing, but confirmation remains incomplete.'
    if score<65: return 'Tactical recovery is forming.'
    if score<75: return 'Constructive rotation is building.'
    return 'Risk appetite is expanding.'

def bias(score,cs):
    if score>=75 and cs['market_breadth']>=60 and cs['flow']>=60: return 'Risk-on. Favor leaders with confirmed liquidity and institutional flow.'
    if score>=65: return 'Selective overweight. Focus on sectors with strong rotation and improving flow.'
    if score>=55: return 'Selective trading. Follow leadership, but avoid aggressive exposure until flow confirms.'
    if score>=45: return 'Defensive selective. Keep exposure moderate and avoid weak-volume rallies.'
    if score>=35: return 'Defensive. Preserve cash and wait for breadth, flow, and sector participation to stabilize.'
    return 'Defensive. Avoid chasing rebound until breadth and flow recover.'

def summary(score,cs):
    h=headline(score,cs); sup,drag=supports_drags(cs); text=h+' '
    if sup: text+='Key support came from '+', '.join([x['label'] for x in sup[:3]])+'. '
    if drag: text+='Main drag came from '+', '.join([x['label'] for x in drag[:3]])+'. '
    if score<35: text+='The regime remains in stress condition.'
    elif score<45: text+='The regime remains risk-off.'
    elif score<55: text+='The regime is defensive neutral.'
    elif score<65: text+='The regime is in tactical recovery / neutral rotation.'
    elif score<75: text+='The regime is constructive but not yet full risk-on.'
    else: text+='The regime supports risk-on positioning.'
    return text

def build_payload(date, cs):
    miss=[k for k,v in cs.items() if v is None or pd.isna(v)]
    if miss: raise ValueError(f'Score komponen belum lengkap: {miss}')
    raw=weighted_score(cs,WEIGHTS); score=round_score(raw); sup,drag=supports_drags(cs)
    return {'date':date,'regime_score':score,'regime_score_raw':round(raw,1),'regime_label':regime_label(score),'component_scores':cs,'market_pulse':{'headline':headline(score,cs),'summary':summary(score,cs),'bias':bias(score,cs),'key_support':sup,'key_drag':drag}}

def run(components_csv, output_json='market_regime_payload.json'):
    df=pd.read_csv(components_csv).sort_values('date'); r=df.iloc[-1]
    cs={k:float(r[k]) for k in WEIGHTS}
    p=build_payload(str(pd.to_datetime(r['date']).date()),cs); save_json(p,output_json); print('Regime:',p['regime_score'],p['regime_label']); return p
if __name__=='__main__':
    ap=argparse.ArgumentParser(); ap.add_argument('--components',required=True); ap.add_argument('--output-json',default='market_regime_payload.json'); a=ap.parse_args(); run(a.components,a.output_json)
