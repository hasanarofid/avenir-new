import json
from pathlib import Path
import numpy as np
import pandas as pd


def clamp(x, lo=0, hi=100):
    if pd.isna(x): return np.nan
    return max(lo, min(hi, x))

def safe_div(a,b,default=np.nan):
    if b is None or pd.isna(b) or b == 0: return default
    return a/b

def clean_col(c):
    return str(c).strip().lower().replace(' ','_').replace('.','').replace('/','_').replace('-','_').replace('(','').replace(')','')

def find_col(df, names):
    norm={str(c).lower().strip().replace('.','').replace('_','').replace(' ',''):c for c in df.columns}
    for n in names:
        k=n.lower().strip().replace('.','').replace('_','').replace(' ','')
        if k in norm: return norm[k]
    return None

def parse_number(x):
    if pd.isna(x): return np.nan
    if isinstance(x,(int,float,np.integer,np.floating)): return float(x)
    s=str(x).strip().replace('Rp','').replace('IDR','').replace('%','').replace(' ','').replace('"','')
    if s in ['', '-', 'N/A', 'nan', 'None', 'NaN']: return np.nan
    mult=1
    if s and s[-1].upper() in ['K','M','B','T']:
        suf=s[-1].upper(); s=s[:-1]
        mult={'K':1e3,'M':1e6,'B':1e9,'T':1e12}[suf]
    if ',' in s and '.' in s:
        if s.rfind(',') > s.rfind('.'):
            s=s.replace('.','').replace(',','.')
        else:
            s=s.replace(',','')
    elif ',' in s:
        parts=s.split(',')
        s=s.replace(',','.') if len(parts[-1])<=2 else s.replace(',','')
    elif '.' in s:
        parts=s.split('.')
        if len(parts)>2 and all(len(p)==3 for p in parts[1:]): s=s.replace('.','')
    try: return float(s)*mult
    except Exception: return np.nan

def parse_date(s): return pd.to_datetime(s, errors='coerce', dayfirst=False)

def load_table(path, sheet_name=0):
    p=Path(path)
    if not p.exists(): raise FileNotFoundError(p)
    if p.suffix.lower()=='.csv': df=pd.read_csv(p)
    elif p.suffix.lower() in ['.xlsx','.xls']: df=pd.read_excel(p, sheet_name=sheet_name)
    else: raise ValueError('format harus csv/xlsx/xls')
    df=df.loc[:, ~df.columns.astype(str).str.startswith('Unnamed')]
    df.columns=[clean_col(c) for c in df.columns]
    return df

def score_piecewise(v, pts):
    if v is None or pd.isna(v): return np.nan
    pts=sorted(pts,key=lambda x:x[0])
    if v<=pts[0][0]: return pts[0][1]
    if v>=pts[-1][0]: return pts[-1][1]
    for (x0,y0),(x1,y1) in zip(pts[:-1],pts[1:]):
        if x0<=v<=x1:
            r=(v-x0)/(x1-x0)
            return y0+r*(y1-y0)
    return np.nan

def weighted_score(values, weights):
    s=w=0
    for k,wt in weights.items():
        v=values.get(k)
        if pd.notna(v):
            s+=v*wt; w+=wt
    return np.nan if w==0 else clamp(s/w)

def round_score(x):
    if pd.isna(x): return np.nan
    return int(np.floor(round(float(x),1)+0.5))

def save_json(payload,path):
    Path(path).parent.mkdir(parents=True, exist_ok=True)
    def clean(v):
        if isinstance(v, (bool, np.bool_)): return int(v)
        if pd.isna(v): return None
        if isinstance(v,(np.integer,np.floating)): return float(v)
        return v
    def rec(o):
        if isinstance(o,dict): return {k:rec(v) for k,v in o.items()}
        if isinstance(o,list): return [rec(v) for v in o]
        return clean(o)
    with open(path,'w',encoding='utf-8') as f: json.dump(rec(payload),f,indent=2,ensure_ascii=False)

def regime_label(score):
    if score>=75: return 'Risk-On Accumulation'
    if score>=65: return 'Constructive Rotation'
    if score>=55: return 'Neutral Rotation'
    if score>=45: return 'Defensive Neutral'
    if score>=35: return 'Risk-Off'
    return 'Stress / Risk-Off Pressure'
