import pandas as pd
import json
import sys
import os
import math
import re
from collections import defaultdict

def sanitize_ticker(ticker):
    return ticker.replace('.JK', '').strip().upper()

def title_case(s):
    if not isinstance(s, str) or pd.isna(s): return ""
    return s.title()

def get_entity_key(kind, ticker_or_name):
    if kind == 'issuer':
        return f"E:{ticker_or_name}"
    else:
        return f"I:{str(ticker_or_name).strip().upper()}"

def main():
    if len(sys.argv) < 4:
        print("Usage: python3 build_ownership_data.py <full.csv> <movement.csv> <gov.csv>")
        sys.exit(1)

    full_path = sys.argv[1]
    mov_path = sys.argv[2]
    gov_path = sys.argv[3]

    try:
        df_full = pd.read_csv(full_path)
        df_mov = pd.read_csv(mov_path)
        df_gov = pd.read_csv(gov_path)
    except Exception as e:
        print(f'{{"status":"error","message":"Failed to read CSV: {e}"}}')
        sys.exit(1)
        
    df_full['symbol'] = df_full['symbol'].apply(sanitize_ticker)
    df_mov['symbol'] = df_mov['symbol'].apply(sanitize_ticker)
    df_gov['symbol'] = df_gov['symbol'].apply(sanitize_ticker)

    stats = {
        "latestDate": df_full['date'].max() if 'date' in df_full.columns else "2026-06-30",
        "prevDate": "",
        "dates": [],
        "rowsLatest": len(df_full),
        "issuersLatest": df_full['symbol'].nunique(),
        "entities": 0,
        "edges": len(df_full),
        "changes": len(df_mov),
        "defaultKey": f"E:{df_full['symbol'].iloc[0]}" if not df_full.empty else ""
    }

    entities = {}
    edges = []
    audits = {}
    groups = {}
    shadow = {"connectorCount": 0, "sharedHolders": []}
    institutions = {"top": [], "flow": {"buy": [], "sell": []}}
    govHoldings = []
    changes = []
    
    # 1. Process Edges and Entities
    investor_issuers = defaultdict(list)
    issuer_investors = defaultdict(list)
    
    for _, row in df_full.iterrows():
        ticker = row['symbol']
        issuer_name = title_case(row.get('company_name', ''))
        investor = title_case(row.get('investor_name', ''))
        pct = float(row.get('percentage', 0))
        shares = int(row.get('total_holding_shares', 0))
        lf = str(row.get('local_foreign', 'U')).upper()
        
        is_gov = str(row.get('is_government', 'false')).lower() == 'true'
        cls = title_case(row.get('investor_classification', ''))
        
        ik = get_entity_key('investor', investor)
        ek = get_entity_key('issuer', ticker)
        
        if ek not in entities:
            entities[ek] = {
                "key": ek,
                "label": issuer_name,
                "ticker": ticker,
                "kind": "issuer",
                "norm": issuer_name.replace(" Tbk", ""),
                "listed": True
            }
            
        if ik not in entities:
            entities[ik] = {
                "key": ik,
                "label": investor,
                "kind": "investor",
                "norm": investor,
                "listed": False
            }
            
        edge = {
            "from": ik,
            "to": ek,
            "pct": pct,
            "shares": shares,
            "investor": investor,
            "issuer": issuer_name,
            "ticker": ticker,
            "classification": cls,
            "local_foreign": lf,
            "is_government": is_gov
        }
        edges.append(edge)
        investor_issuers[ik].append(edge)
        issuer_investors[ek].append(edge)
        
    stats["entities"] = len(entities)

    # 2. Process Changes
    for _, row in df_mov.iterrows():
        ticker = row['symbol']
        issuer = title_case(row.get('company_name', ''))
        investor = title_case(row.get('investor_name', ''))
        ik = get_entity_key('investor', investor)
        ek = get_entity_key('issuer', ticker)
        
        c = {
            "from": ik,
            "to": ek,
            "investor": investor,
            "ticker": ticker,
            "issuer": issuer,
            "direction": str(row.get('change_type', '')).upper(),
            "latestPct": float(row.get('pct_after', 0)) if pd.notna(row.get('pct_after')) else 0,
            "deltaShares": int(row.get('shares', 0)) if pd.notna(row.get('shares')) else 0,
            "magnitude": float(row.get('magnitude', 0)) if pd.notna(row.get('magnitude')) else 0,
            "localForeign": str(row.get('local_foreign', 'U')).upper(),
            "isGovernment": str(row.get('is_government', 'false')).lower() == 'true',
            "classification": title_case(row.get('investor_classification', ''))
        }
        changes.append(c)

    # 3. Process Audits
    for ek, holds in issuer_investors.items():
        ticker = holds[0]['ticker']
        holds_sorted = sorted(holds, key=lambda x: x['pct'], reverse=True)
        coverage = sum(h['pct'] for h in holds_sorted)
        
        top1 = holds_sorted[0]['pct'] if len(holds_sorted) > 0 else 0
        top3 = sum(h['pct'] for h in holds_sorted[:3])
        top5 = sum(h['pct'] for h in holds_sorted[:5])
        
        hhi = sum((h['pct']**2) for h in holds_sorted)
        
        nakamoto = 0
        csum = 0
        for h in reversed(holds_sorted):
            csum += h['pct']
            nakamoto += 1
            if csum >= 50:
                break
                
        local_pct = sum(h['pct'] for h in holds_sorted if h['local_foreign'] == 'L')
        foreign_pct = sum(h['pct'] for h in holds_sorted if h['local_foreign'] == 'F')
        
        audits[ek] = {
            "key": ek,
            "ticker": ticker,
            "issuer": holds[0]['issuer'],
            "holders": len(holds),
            "coverage": coverage,
            "residual": max(0, 100 - coverage),
            "top1": top1,
            "top3": top3,
            "top5": top5,
            "hhi": round(hhi, 2),
            "nakamoto50": nakamoto,
            "govPct": sum(h['pct'] for h in holds_sorted if h['is_government']),
            "localPct": local_pct,
            "foreignPct": foreign_pct,
            "unknownPct": max(0, coverage - local_pct - foreign_pct),
            "scripRatio": 0,
            "floatProxy": max(0, 100 - top5),
            "controlLabel": "Concentrated" if top1 >= 20 else "Fragmented",
            "riskLabel": "High" if top1 >= 50 else "Medium",
            "riskScore": 75,
            "confidence": 90,
            "controller": {"holderKey": holds_sorted[0]['from'], "pct": top1, "label": holds_sorted[0]['investor'], "type": "Direct"} if top1 >= 20 else None
        }

    # 4. Shadow Network and Groups
    shared_holders = []
    for ik, holds in investor_issuers.items():
        is_bank_or_sec = "bank" in holds[0]['classification'].lower() or "securit" in holds[0]['classification'].lower()
        if len(holds) > 1 and not is_bank_or_sec:
            shared_holders.append({
                "holder": holds[0]['investor'],
                "count": len(holds),
                "cls": holds[0]['classification'],
                "isGov": holds[0]['is_government'],
                "issuers": [{"key": h['to'], "ticker": h['ticker'], "pct": h['pct']} for h in sorted(holds, key=lambda x: x['pct'], reverse=True)]
            })
            
        # Group Detection (Control >= 20%)
        if not is_bank_or_sec:
            controlled = [h for h in holds if h['pct'] >= 20]
            if len(controlled) >= 1:
                label = holds[0]["investor"]
                groups[ik] = {
                    "label": label,
                    "issuerCount": len(controlled),
                    "issuers": [{"key": h["to"], "ticker": h["ticker"], "issuer": h["issuer"], "controllerPct": h["pct"], "controlLabel": "Controlled"} for h in controlled],
                    "holdings": [{"holderKey": ik, "holder": label, "key": h["to"], "ticker": h["ticker"], "issuer": h["issuer"], "pct": h["pct"], "controlling": True, "suspected": False} for h in controlled],
                    "suspected": [],
                    "holdingCount": len(controlled),
                    "reachIssuers": len(controlled),
                    "anchors": [{"key": ik, "label": label, "kind": "investor", "ticker": ""}]
                }
    shared_holders.sort(key=lambda x: x['count'], reverse=True)
    shadow["sharedHolders"] = shared_holders
    shadow["connectorCount"] = len(shared_holders)
    
    # 5. Government Holdings
    for _, row in df_gov.iterrows():
        govHoldings.append({
            "ticker": row['symbol'],
            "issuer": title_case(row.get('company_name', '')),
            "investor": title_case(row.get('investor_name', '')),
            "pct": float(row.get('percentage', 0)) if pd.notna(row.get('percentage')) else 0,
            "shares": int(row.get('total_holding_shares', 0)) if pd.notna(row.get('total_holding_shares')) else 0,
            "classification": title_case(row.get('investor_classification', '')),
            "domicile": str(row.get('domicile', '')),
            "local_foreign": str(row.get('local_foreign', ''))
        })

    # Prepare Final DATA
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
        "investorSummaries": {}
    }
    
    print(json.dumps(DATA))

if __name__ == '__main__':
    main()
