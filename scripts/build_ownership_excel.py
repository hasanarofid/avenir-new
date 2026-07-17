import pandas as pd
import json
import sys
import math
import re

def title_case(s):
    if not isinstance(s, str) or pd.isna(s): return ""
    return str(s).title()

def get_entity_key(kind, ticker_or_name):
    if kind == 'issuer':
        return f"E:{ticker_or_name}"
    else:
        return f"I:{str(ticker_or_name).strip().upper()}"

def num(v):
    if v == '' or v is None: return None
    try:
        s = str(v).replace(',', '').strip()
        n = float(s)
        return n if not math.isnan(n) else None
    except ValueError:
        return None

def parse_ksei_workbook(file_path):
    rows = pd.read_excel(file_path, header=None).fillna('').values.tolist()
    hIdx = -1
    for i in range(min(len(rows), 12)):
        if any(str(c).strip() == 'Kode Efek' for c in rows[i]):
            hIdx = i
            break
    if hIdx < 0:
        raise ValueError('Format tidak dikenali: baris header "Kode Efek" tak ditemukan.')
    
    hdr = [str(c) for c in rows[hIdx]]
    datePrev = ''
    dateNow = ''
    dateCols = []
    for idx, c in enumerate(hdr):
        if 'Kepemilikan Per' in c:
            dateCols.append({'idx': idx, 'label': c.replace('Kepemilikan Per', '').strip()})
    
    if len(dateCols) >= 2:
        datePrev = dateCols[0]['label']
        dateNow = dateCols[1]['label']
    elif len(dateCols) == 1:
        dateNow = dateCols[0]['label']
        
    issuers = {}
    cur = None
    
    def flush():
        nonlocal cur
        if not cur: return
        totchg = 0
        moved = []
        for x in cur['rows']:
            if x['chg']:
                totchg += x['chg']
                if abs(x['chg']) > 0:
                    moved.append(x)
        
        broker = cur['broker']
        if moved:
            moved.sort(key=lambda a: abs(a['chg']), reverse=True)
            broker = moved[0]['broker'] or broker
            
        sharesPrev = cur['sharesPrev']
        if sharesPrev is None and cur['sharesNow'] is not None:
            sharesPrev = cur['sharesNow'] - totchg
            
        if cur['ticker'] not in issuers:
            issuers[cur['ticker']] = []
            
        issuers[cur['ticker']].append({
            'investor': cur['investor'],
            'broker': broker,
            'change': totchg,
            'pctNow': cur['pctNow'],
            'pctPrev': cur['pctPrev'],
            'sharesNow': cur['sharesNow'],
            'sharesPrev': sharesPrev,
            'type': cur['type'],
            'domicile': cur['domicile'],
            'brokerCount': len(cur['rows'])
        })
        
    for i in range(hIdx + 2, len(rows)):
        r = rows[i]
        no = r[0] if len(r) > 0 else None
        ticker = r[1] if len(r) > 1 else None
        if no == '' or ticker == '': continue
        
        t = str(ticker).strip()
        investor = str(r[4]).strip() if len(r) > 4 else ''
        broker = str(r[3]).strip() if len(r) > 3 else ''
        chg = num(r[17]) if len(r) > 17 else None
        
        if investor:
            flush()
            cur = {
                'ticker': t,
                'investor': investor,
                'broker': broker,
                'pctNow': num(r[16]) if len(r) > 16 else None,
                'pctPrev': num(r[13]) if len(r) > 13 else None,
                'sharesNow': num(r[15]) if len(r) > 15 else None,
                'sharesPrev': num(r[12]) if len(r) > 12 else None,
                'type': str(r[10]).strip() if len(r) > 10 else '',
                'domicile': str(r[9]).strip() if len(r) > 9 else '',
                'rows': [{'broker': broker, 'chg': chg}]
            }
        elif cur and cur['ticker'] == t:
            cur['rows'].append({'broker': broker, 'chg': chg})
            
    flush()
    return {'dateNow': dateNow, 'datePrev': datePrev, 'issuers': issuers, 'source': 'KSEI >=5%'}

def find_header_row(rows, token):
    for i in range(min(len(rows), 12)):
        if any(str(c).strip().upper() == token for c in rows[i]):
            return i
    return -1

def date_from_rows(rows, startIdx, col):
    for i in range(startIdx, min(len(rows), startIdx + 6)):
        v = rows[i][col] if col < len(rows[i]) else ''
        if v:
            m = re.search(r'(\d{4})-(\d{2})-(\d{2})', str(v))
            if m: return m.group(0)
            # Try parsing date format if available
            try:
                d = pd.to_datetime(v)
                return d.strftime('%Y-%m-%d')
            except:
                pass
    return None

def parse_monthly_workbook(file_path):
    rows = pd.read_excel(file_path, header=None).fillna('').values.tolist()
    flat = "||".join(["|".join([str(c) for c in r]) for r in rows[:8]]).upper()
    
    # SATU PERSEN
    if 'INVESTOR_NAME' in flat or 'INVESTOR NAME' in flat:
        h = find_header_row(rows, 'SHARE_CODE')
        if h < 0: h = find_header_row(rows, 'SHARE CODE')
        hi = h if h >= 0 else 5
        date = date_from_rows(rows, hi+1, 0)
        issuers = {}
        for i in range(hi+1, len(rows)):
            r = rows[i]
            if len(r) < 2 or r[1] == '': continue
            t = str(r[1]).strip()
            if t not in issuers: issuers[t] = []
            issuers[t].append({
                'investor': str(r[3]).strip() if len(r) > 3 else '',
                'cls': str(r[4]).strip() if len(r) > 4 else '',
                'lf': str(r[5]).strip() if len(r) > 5 else '',
                'domicile': str(r[7]).strip() if len(r) > 7 else '',
                'shares': num(r[10]) if len(r) > 10 else None,
                'pct': num(r[11]) if len(r) > 11 else None
            })
        return {'kind': 'satu', 'date': date, 'issuers': issuers}
        
    # KLASIFIKASI
    if 'TOTAL SCRIPLESS' in flat and 'GOVERNMENT' in flat and 'INDIVIDUAL' in flat:
        hi = find_header_row(rows, 'SHARE CODE')
        if hi < 0: hi = find_header_row(rows, 'SHARE_CODE')
        hr = rows[hi] if hi >= 0 else []
        cats = []
        totalCol = -1
        for c in range(3, len(hr)):
            nm = str(hr[c]).strip()
            if nm == 'TOTAL SCRIPLESS':
                totalCol = c
            elif nm:
                cats.append((c, nm))
        date = date_from_rows(rows, hi+1, 0)
        issuers = {}
        for i in range(hi+1, len(rows)):
            r = rows[i]
            if len(r) < 2 or r[1] == '': continue
            comp = {}
            for c, nm in cats:
                v = num(r[c]) if c < len(r) else None
                if v and v > 0: comp[nm] = v
            t = str(r[1]).strip()
            issuers[t] = {
                'total': num(r[totalCol]) if totalCol >= 0 and totalCol < len(r) else None,
                'comp': comp
            }
        return {'kind': 'klas', 'date': date, 'issuers': issuers}
        
    # TIPE
    if 'DOMESTIC' in flat and 'FOREIGN' in flat:
        hi = find_header_row(rows, 'STOCK_CODE')
        if hi < 0: hi = 1
        hr = rows[hi] if hi >= 0 else []
        totalCol = next((i for i, c in enumerate(hr) if str(c).strip().upper() == 'TOTAL SCRIPLESS'), -1)
        foreignStart = next((i for i, c in enumerate(hr) if str(c).strip().upper() == 'FOREIGN'), -1)
        date = date_from_rows(rows, hi+3, 0)
        issuers = {}
        for i in range(hi+3, len(rows)):
            r = rows[i]
            if len(r) < 2 or r[1] == '': continue
            dom = 0
            foreign = 0
            tot = num(r[totalCol]) if totalCol >= 0 and totalCol < len(r) else 0
            end_col = totalCol if totalCol > 0 else len(r)
            for c in range(3, end_col):
                v = num(r[c]) if c < len(r) else 0
                if not v: v = 0
                if foreignStart > 0 and c >= foreignStart:
                    foreign += v
                else:
                    dom += v
            t = str(r[1]).strip()
            issuers[t] = {'domestic': dom, 'foreign': foreign, 'total': tot}
        return {'kind': 'tipe', 'date': date, 'issuers': issuers}
        
    raise ValueError('Jenis file bulanan tidak dikenali (bukan satu-persen/klasifikasi/tipe).')

def main():
    if len(sys.argv) < 7:
        print("Usage: python3 build_ownership_excel.py <daily5pct.xlsx> <type.xlsx> <klasifikasi.xlsx> <1pct.xlsx> <brokers.json> <master_stocks.json>")
        sys.exit(1)

    daily_path = sys.argv[1]
    type_path = sys.argv[2]
    klas_path = sys.argv[3]
    satu_path = sys.argv[4]
    brokers_path = sys.argv[5]
    master_stocks_path = sys.argv[6]

    try:
        daily_res = parse_ksei_workbook(daily_path)
        type_res = parse_monthly_workbook(type_path)
        klas_res = parse_monthly_workbook(klas_path)
        satu_res = parse_monthly_workbook(satu_path)
        
        with open(brokers_path, 'r') as f:
            brokers = json.load(f)
            
        with open(master_stocks_path, 'r') as f:
            master_stocks_data = json.load(f)
            master_stocks = master_stocks_data.get('byCode', {})
            
    except Exception as e:
        print(json.dumps({"status": "error", "message": f"Failed to parse files: {str(e)}"}))
        sys.exit(1)

    # Convert to the DATA format required by Avenir Ownership Graph
    # -----------------------------------------------------------
    stats = {
        "latestDate": daily_res['dateNow'],
        "prevDate": daily_res['datePrev'],
        "dates": [daily_res['dateNow'], daily_res['datePrev']],
        "issuersLatest": len(daily_res['issuers']),
        "entities": 0,
        "edges": 0,
        "changes": 0,
        "defaultKey": f"E:{list(daily_res['issuers'].keys())[0]}" if daily_res['issuers'] else ""
    }

    entities = {}
    edges = []
    changes = []
    audits = {}
    groups = {}
    shadow = {"connectorCount": 0, "sharedHolders": []}
    institutions = {"top": [], "flow": {"buy": [], "sell": []}}
    govHoldings = []
    
    # Generate entities & edges from daily 5% (to emulate the graph logic)
    for ticker, holds in daily_res['issuers'].items():
        ek = get_entity_key('issuer', ticker)
        if ek not in entities:
            ms = master_stocks.get(ticker, {})
            entities[ek] = {
                "key": ek, "label": ticker, "ticker": ticker,
                "kind": "issuer", "norm": ticker, "listed": True,
                "logo_url": ms.get("logo_url", ""),
                "sector": ms.get("sector", ""),
                "sub_industry": ms.get("sub_industry", ""),
                "is_sharia": ms.get("is_sharia", False)
            }
        
        for h in holds:
            ik = get_entity_key('investor', h['investor'])
            if ik not in entities:
                entities[ik] = {
                    "key": ik, "label": h['investor'], "kind": "investor", "norm": h['investor'], "listed": False
                }
            
            # Map Edge
            if h['pctNow']:
                edges.append({
                    "from": ik, "to": ek, "pct": h['pctNow'], "shares": h['sharesNow'],
                    "investor": h['investor'], "issuer": ticker, "ticker": ticker,
                    "classification": h['type'], "local_foreign": "L" if "LOCAL" in str(h['domicile']).upper() else "F",
                    "is_government": "PEMERINTAH" in h['investor'].upper()
                })
                
            # Map Change
            if h['change']:
                changes.append({
                    "from": ik, "to": ek, "investor": h['investor'], "ticker": ticker, "issuer": ticker,
                    "direction": "BUY" if h['change'] > 0 else "SELL",
                    "latestPct": h['pctNow'] or 0, "deltaShares": h['change'],
                    "magnitude": abs(h['change']),
                    "localForeign": "L" if "LOCAL" in str(h['domicile']).upper() else "F",
                    "classification": h['type'],
                    "isGovernment": "PEMERINTAH" in h['investor'].upper()
                })
                
    stats['entities'] = len(entities)
    stats['edges'] = len(edges)
    stats['changes'] = len(changes)

    # -----------------------------------------------------------
    # Prepare the Insider & Monthly Data for Vue Client rendering
    # -----------------------------------------------------------
    
    DATA = {
        "status": "success",
        "stats": stats,
        "entities": entities,
        "edges": edges,
        "changes": changes,
        "audits": audits,
        "groups": groups,
        "shadow": shadow,
        "institutions": institutions,
        "govHoldings": govHoldings,
        "investorSummaries": {},
        # Provide raw outputs for Vue client
        "insiderData": {
            "snapshots": [daily_res]
        },
        "monthlyData": {
            "satu": [satu_res],
            "klas": [klas_res],
            "tipe": [type_res]
        },
        "brokerDict": brokers
    }

    print(json.dumps(DATA))

if __name__ == '__main__':
    main()
