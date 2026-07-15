# =========================================================
# AVENIR RESEARCH
# SECTOR ROTATION SCORE ENGINE
# Manual input: daily IDX sector returns
# =========================================================

import json
import numpy as np
import pandas as pd
from pathlib import Path


# =========================================================
# 1. CONFIG
# =========================================================

SECTOR_CSV_PATH = None
OUTPUT_DIR = None
OUTPUT_FEATURES_CSV = None
OUTPUT_LATEST_JSON = None


# =========================================================
# 2. SECTOR CONFIG
# =========================================================

SECTOR_COLUMNS = [
    "Energy",
    "Basic Materials",
    "Industrials",
    "Consumer Non-Cyclicals",
    "Consumer Cyclicals",
    "Healthcare",
    "Financials",
    "Properties & Real Estate",
    "Technology",
    "Infrastructures",
    "Transportation & Logistic"
]

RISK_ON_SECTORS = [
    "Energy",
    "Basic Materials",
    "Industrials",
    "Consumer Cyclicals",
    "Financials",
    "Properties & Real Estate",
    "Technology",
    "Infrastructures",
    "Transportation & Logistic"
]

DEFENSIVE_SECTORS = [
    "Consumer Non-Cyclicals",
    "Healthcare"
]


# =========================================================
# 3. HELPERS
# =========================================================

def clamp(value, min_value=0, max_value=100):
    return max(min_value, min(value, max_value))


def normalize_column_name(x):
    return str(x).lower().strip().replace(".", "").replace("_", "").replace(" ", "")


def find_column(df, candidates):
    normalized = {
        normalize_column_name(c): c
        for c in df.columns
    }

    for cand in candidates:
        key = normalize_column_name(cand)
        if key in normalized:
            return normalized[key]

    return None


def parse_number(x):
    """
    Support:
    - -1.55
    - -1.55%
    - -1,55
    """

    if pd.isna(x):
        return np.nan

    s = str(x).strip()
    s = s.replace("%", "").replace(" ", "").replace('"', "")

    if s in ["", "-", "N/A", "nan", "None"]:
        return np.nan

    if "," in s and "." in s:
        if s.rfind(",") > s.rfind("."):
            s = s.replace(".", "").replace(",", ".")
        else:
            s = s.replace(",", "")

    elif "," in s:
        s = s.replace(",", ".")

    try:
        return float(s)
    except Exception:
        return np.nan


def parse_date_column(series):
    return pd.to_datetime(series, errors="coerce", format="%Y-%m-%d")


def score_piecewise(value, points):
    if value is None or pd.isna(value):
        return np.nan

    points = sorted(points, key=lambda x: x[0])

    if value <= points[0][0]:
        return points[0][1]

    if value >= points[-1][0]:
        return points[-1][1]

    for i in range(len(points) - 1):
        x0, y0 = points[i]
        x1, y1 = points[i + 1]

        if x0 <= value <= x1:
            ratio = (value - x0) / (x1 - x0)
            return y0 + ratio * (y1 - y0)

    return np.nan


# =========================================================
# 4. LOAD DATA
# =========================================================

def load_sector_file(file_path):
    file_path = Path(file_path)

    if file_path.suffix.lower() in [".xlsx", ".xls"]:
        df = pd.read_excel(file_path)
    else:
        df = pd.read_csv(file_path)

    df = df.loc[:, ~df.columns.astype(str).str.startswith("Unnamed")]

    date_col = find_column(df, ["date", "tanggal", "time"])

    if date_col is None:
        raise ValueError("Kolom tanggal tidak ditemukan.")

    out = pd.DataFrame()
    out["date"] = parse_date_column(df[date_col])

    for sector in SECTOR_COLUMNS:
        col = find_column(df, [sector])
        if col is None:
            out[sector] = np.nan
        else:
            out[sector] = df[col].apply(parse_number)

    out = out.dropna(subset=["date"])
    out = out.sort_values("date")
    out = out.drop_duplicates(subset=["date"], keep="last")
    out = out.reset_index(drop=True)

    return out


# =========================================================
# 5. COMPONENT SCORING
# =========================================================

def score_sector_participation(positive_sector_ratio):
    """
    Berapa banyak sektor positif.
    """

    return score_piecewise(positive_sector_ratio, [
        (0.00, 10),
        (0.20, 25),
        (0.35, 40),
        (0.50, 55),
        (0.60, 65),
        (0.75, 80),
        (0.90, 95),
        (1.00, 100),
    ])


def score_risk_on_participation(cyclical_positive_ratio):
    """
    Apakah sektor risk-on/cyclical ikut naik.
    """

    return score_piecewise(cyclical_positive_ratio, [
        (0.00, 10),
        (0.20, 25),
        (0.35, 40),
        (0.50, 55),
        (0.65, 70),
        (0.80, 88),
        (1.00, 100),
    ])


def calculate_leadership_concentration(sector_returns):
    """
    Mengukur apakah kenaikan terkonsentrasi di 1-2 sektor.
    Pakai HHI dari positive sector returns.

    HHI tinggi = leadership terkonsentrasi = kurang bagus.
    HHI rendah = leadership luas = bagus.
    """

    positive_returns = [
        max(0, r)
        for r in sector_returns
        if pd.notna(r) and r > 0
    ]

    if len(positive_returns) == 0:
        return np.nan

    total_positive = sum(positive_returns)

    if total_positive <= 0:
        return np.nan

    shares = [r / total_positive for r in positive_returns]
    hhi = sum(s ** 2 for s in shares)

    return hhi


def score_leadership_breadth(hhi, positive_sector_count):
    """
    HHI rendah lebih bagus.
    Kalau cuma 1 sektor positif, score harus rendah.
    """

    if pd.isna(hhi):
        return 10

    if positive_sector_count <= 1:
        return 15

    # HHI:
    # 1.00 = satu sektor dominan
    # 0.20-0.30 = leadership cukup luas
    score = score_piecewise(hhi, [
        (0.15, 100),
        (0.25, 85),
        (0.35, 70),
        (0.50, 50),
        (0.70, 30),
        (1.00, 10),
    ])

    return round(clamp(score), 0)


def score_risk_on_vs_defensive_spread(spread):
    """
    spread = avg risk-on return - avg defensive return.
    Dalam percentage point.
    Contoh:
    risk-on avg = +1.2
    defensive avg = +0.3
    spread = +0.9
    """

    return score_piecewise(spread, [
        (-4.00, 10),
        (-2.00, 25),
        (-1.00, 40),
        (0.00, 55),
        (1.00, 70),
        (2.00, 85),
        (4.00, 100),
    ])


def score_leadership_persistence(days):
    """
    Optional.
    Berapa hari terakhir sektor/bucket pemimpin masih konsisten.
    """

    if days is None or pd.isna(days):
        return np.nan

    days = int(days)

    if days >= 5:
        return 100
    if days == 4:
        return 85
    if days == 3:
        return 70
    if days == 2:
        return 50
    if days == 1:
        return 35

    return 20


# =========================================================
# 6. FEATURE BUILDER
# =========================================================

def get_leading_sector(row):
    valid = {
        sector: row[sector]
        for sector in SECTOR_COLUMNS
        if pd.notna(row.get(sector))
    }

    if not valid:
        return None

    return max(valid, key=valid.get)


def get_leading_bucket(sector):
    if sector in RISK_ON_SECTORS:
        return "risk_on"
    if sector in DEFENSIVE_SECTORS:
        return "defensive"
    return "other"


def calculate_persistence(series, window=5):
    """
    Hitung berapa hari dalam window terakhir leading_bucket sama dengan hari ini.
    """

    result = []

    for i in range(len(series)):
        current = series.iloc[i]

        if pd.isna(current):
            result.append(np.nan)
            continue

        start = max(0, i - window + 1)
        window_values = series.iloc[start:i + 1]

        count_same = (window_values == current).sum()
        result.append(count_same)

    return result


def add_sector_rotation_features(df):
    df = df.copy()
    df = df.sort_values("date").reset_index(drop=True)

    sector_data = df[SECTOR_COLUMNS]

    # Positive sectors
    df["positive_sector_count"] = (sector_data > 0).sum(axis=1)
    df["total_sector_count"] = sector_data.notna().sum(axis=1)
    df["sector_positive_ratio"] = df["positive_sector_count"] / df["total_sector_count"]

    # Risk-on participation
    risk_on_data = df[RISK_ON_SECTORS]
    df["risk_on_positive_count"] = (risk_on_data > 0).sum(axis=1)
    df["risk_on_total_count"] = risk_on_data.notna().sum(axis=1)
    df["cyclical_positive_ratio"] = df["risk_on_positive_count"] / df["risk_on_total_count"]

    # Defensive
    defensive_data = df[DEFENSIVE_SECTORS]
    df["defensive_positive_count"] = (defensive_data > 0).sum(axis=1)

    # Average returns
    df["avg_sector_return"] = sector_data.mean(axis=1)
    df["avg_risk_on_return"] = risk_on_data.mean(axis=1)
    df["avg_defensive_return"] = defensive_data.mean(axis=1)
    df["risk_on_vs_defensive_spread"] = (
        df["avg_risk_on_return"] - df["avg_defensive_return"]
    )

    # Leadership
    df["leading_sector"] = df.apply(get_leading_sector, axis=1)
    df["leading_bucket"] = df["leading_sector"].apply(get_leading_bucket)

    # Leadership concentration
    df["leadership_concentration_hhi"] = df.apply(
        lambda row: calculate_leadership_concentration([row[s] for s in SECTOR_COLUMNS]),
        axis=1
    )

    # Leadership persistence
    df["leadership_persistence_days"] = calculate_persistence(
        df["leading_bucket"],
        window=5
    )

    # Component scores
    df["sector_participation_score"] = df["sector_positive_ratio"].apply(
        score_sector_participation
    )

    df["risk_on_participation_score"] = df["cyclical_positive_ratio"].apply(
        score_risk_on_participation
    )

    df["leadership_breadth_score"] = df.apply(
        lambda row: score_leadership_breadth(
            row["leadership_concentration_hhi"],
            row["positive_sector_count"]
        ),
        axis=1
    )

    df["risk_on_vs_defensive_score"] = df["risk_on_vs_defensive_spread"].apply(
        score_risk_on_vs_defensive_spread
    )

    df["leadership_persistence_score"] = df["leadership_persistence_days"].apply(
        score_leadership_persistence
    )

    # Final score
    df["sector_rotation_score"] = df.apply(calculate_sector_rotation_score_row, axis=1)
    df["sector_rotation_label"] = df["sector_rotation_score"].apply(
        lambda x: None if pd.isna(x) else get_sector_rotation_label(x)
    )

    return df


def calculate_sector_rotation_score_row(row):
    components = {
        "sector_participation": row.get("sector_participation_score"),
        "risk_on_participation": row.get("risk_on_participation_score"),
        "leadership_breadth": row.get("leadership_breadth_score"),
        "risk_on_vs_defensive": row.get("risk_on_vs_defensive_score"),
        "leadership_persistence": row.get("leadership_persistence_score"),
    }

    weights = {
        "sector_participation": 0.30,
        "risk_on_participation": 0.25,
        "leadership_breadth": 0.20,
        "risk_on_vs_defensive": 0.15,
        "leadership_persistence": 0.10,
    }

    valid_score = 0
    valid_weight = 0

    for key, score in components.items():
        if pd.notna(score):
            valid_score += score * weights[key]
            valid_weight += weights[key]

    if valid_weight == 0:
        return np.nan

    return round(clamp(valid_score / valid_weight), 0)


# =========================================================
# 7. LABEL
# =========================================================

def get_sector_rotation_label(score):
    if score >= 80:
        return "Broad Risk-On Rotation"
    if score >= 65:
        return "Constructive Sector Rotation"
    if score >= 50:
        return "Mixed / Selective Rotation"
    if score >= 35:
        return "Defensive / Weak Rotation"
    return "Sector-Wide Pressure"


# =========================================================
# 8. LATEST PAYLOAD
# =========================================================

def clean_value(value):
    if pd.isna(value):
        return None
    if isinstance(value, (np.integer, np.floating)):
        return float(value)
    return value


def build_latest_payload(feature_df):
    latest = feature_df.dropna(subset=["sector_rotation_score"]).iloc[-1].to_dict()

    payload = {
        "date": str(pd.to_datetime(latest["date"]).date()),

        "sector_rotation_score": latest.get("sector_rotation_score"),
        "sector_rotation_label": latest.get("sector_rotation_label"),

        "positive_sector_count": latest.get("positive_sector_count"),
        "total_sector_count": latest.get("total_sector_count"),
        "sector_positive_ratio": latest.get("sector_positive_ratio"),

        "risk_on_positive_count": latest.get("risk_on_positive_count"),
        "risk_on_total_count": latest.get("risk_on_total_count"),
        "cyclical_positive_ratio": latest.get("cyclical_positive_ratio"),

        "avg_sector_return": latest.get("avg_sector_return"),
        "avg_risk_on_return": latest.get("avg_risk_on_return"),
        "avg_defensive_return": latest.get("avg_defensive_return"),
        "risk_on_vs_defensive_spread": latest.get("risk_on_vs_defensive_spread"),

        "leading_sector": latest.get("leading_sector"),
        "leading_bucket": latest.get("leading_bucket"),
        "leadership_concentration_hhi": latest.get("leadership_concentration_hhi"),
        "leadership_persistence_days": latest.get("leadership_persistence_days"),

        "component_scores": {
            "sector_participation": latest.get("sector_participation_score"),
            "risk_on_participation": latest.get("risk_on_participation_score"),
            "leadership_breadth": latest.get("leadership_breadth_score"),
            "risk_on_vs_defensive": latest.get("risk_on_vs_defensive_score"),
            "leadership_persistence": latest.get("leadership_persistence_score"),
        },

        "sector_returns": {
            sector: latest.get(sector)
            for sector in SECTOR_COLUMNS
        }
    }

    return {key: clean_value(value) for key, value in payload.items()}


# =========================================================
# 9. MAIN PIPELINE
# =========================================================

def run_pipeline(input_file, output_dir):
    out = Path(output_dir)
    out.mkdir(parents=True, exist_ok=True)
    
    df = load_sector_file(Path(input_file))
    feature_df = add_sector_rotation_features(df)
    feature_df.to_csv(out / "sector_rotation_features.csv", index=False)
    
    latest_payload = build_latest_payload(feature_df)
    
    with open(out / "latest_sector_rotation_score.json", "w", encoding="utf-8") as f:
        json.dump(latest_payload, f, indent=2, ensure_ascii=False)
        
    return latest_payload, feature_df


# =========================================================
# 10. RUN
# =========================================================

if __name__ == "__main__":
    import argparse
    parser = argparse.ArgumentParser()
    parser.add_argument('--input', required=True)
    parser.add_argument('--output-dir', required=True)
    # ignore other old arguments that breadthservice might send
    parser.add_argument('--stocks', required=False)
    parser.add_argument('--sector-master', required=False)
    parser.add_argument('--stock-sheet-name', required=False)
    parser.add_argument('--sector-sheet-name', required=False)
    args = parser.parse_args()
    
    latest_payload, feature_df = run_pipeline(args.input, args.output_dir)