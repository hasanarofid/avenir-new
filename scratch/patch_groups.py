import json

with open("prd-testing/new_concept/output_test.json", "r") as f:
    DATA = json.load(f)

# Add basic groups
groups = {}
edges = DATA["edges"]
entities = DATA["entities"]

anchor_issuers = {}
for e in edges:
    if e["pct"] >= 20 and "bank" not in str(e.get("classification", "")).lower() and "securit" not in str(e.get("classification", "")).lower():
        ik = e["from"]
        if ik not in anchor_issuers:
            anchor_issuers[ik] = []
        anchor_issuers[ik].append(e)

for ik, holds in anchor_issuers.items():
    if len(holds) >= 1: # Group if they control at least 1? Or >= 2? Let's say >= 1
        label = holds[0]["investor"]
        grp = {
            "label": label,
            "issuerCount": len(holds),
            "issuers": [{"key": h["to"], "ticker": h["ticker"], "issuer": h["issuer"], "controllerPct": h["pct"], "controlLabel": "Controlled"} for h in holds],
            "holdings": [{"holderKey": ik, "holder": label, "key": h["to"], "ticker": h["ticker"], "issuer": h["issuer"], "pct": h["pct"], "controlling": True, "suspected": False} for h in holds],
            "suspected": [],
            "holdingCount": len(holds),
            "reachIssuers": len(holds),
            "anchors": [{"key": ik, "label": label, "kind": "investor", "ticker": ""}]
        }
        groups[ik] = grp

DATA["groups"] = groups
DATA["stats"]["groupsCount"] = len(groups)

with open("prd-testing/new_concept/output_test.json", "w") as f:
    json.dump(DATA, f)
