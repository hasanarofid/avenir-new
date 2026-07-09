# =========================================================
# AVENIR MARKET BREADTH ENGINE — STOCK LEVEL + VALUE
# Input  : Ringkasan Saham harian IDX
# Output : Market Breadth Score
# =========================================================

import sys
import json
import pandas as pd
import numpy as np
from pathlib import Path


def clean_column_name(col):
    return (
        str(col)
        .strip()
        .lower()
        .replace(" ", "_")
        .replace(".", "")
        .replace("/", "_")
        .replace("-", "_")
    )


def to_number(series):
    return (
        series.astype(str)
        .str.replace(",", "", regex=False)
        .str.replace(" ", "", regex=False)
        .replace(["-", "", "nan", "None"], np.nan)
        .astype(float)
    )


def find_col(df, candidates):
    for col in candidates:
        if col in df.columns:
            return col
    raise ValueError(f"Kolom tidak ditemukan. Coba salah satu: {candidates}")


def safe_div(a, b):
    if b == 0 or pd.isna(b):
        return np.nan
    return a / b


def round_score(score):
    if pd.isna(score):
        return np.nan
    return int(np.floor(round(float(score), 1) + 0.5))


def market_breadth_label(score):
    if pd.isna(score):
        return "No Data"
    if score >= 80:
        return "Strong Positive Breadth"
    if score >= 65:
        return "Positive Breadth"
    if score >= 50:
        return "Neutral / Improving"
    if score >= 35:
        return "Weak Breadth"
    return "Broad Weakness"


def load_ringkasan_saham(path, sheet_name=0):
    path = Path(path)

    if not path.exists():
        raise FileNotFoundError(f"File tidak ditemukan: {path}")

    if path.suffix.lower() == ".csv":
        df = pd.read_csv(path)
    else:
        df = pd.read_excel(path, sheet_name=sheet_name)

    df.columns = [clean_column_name(c) for c in df.columns]
    return df


def calculate_market_breadth_from_stocks(df):
    df = df.copy()

    code_col = find_col(df, ["kode_saham", "kode", "code", "symbol"])
    prev_col = find_col(df, ["sebelumnya", "previous", "prev", "prev_close"])
    close_col = find_col(df, ["penutupan", "close", "closing_price", "last"])
    shares_col = find_col(df, ["listed_shares", "listed_share", "saham_tercatat"])
    value_col = find_col(df, ["nilai", "value", "trading_value"])

    df[prev_col] = to_number(df[prev_col])
    df[close_col] = to_number(df[close_col])
    df[shares_col] = to_number(df[shares_col])
    df[value_col] = to_number(df[value_col])

    df = df[
        df[code_col].notna() &
        df[prev_col].notna() &
        df[close_col].notna() &
        df[shares_col].notna() &
        df[value_col].notna() &
        (df[prev_col] > 0) &
        (df[close_col] > 0) &
        (df[shares_col] > 0) &
        (df[value_col] >= 0)
    ].copy()

    df["return_pct"] = (df[close_col] / df[prev_col]) - 1
    df["market_cap"] = df[close_col] * df[shares_col]
    df["trading_value"] = df[value_col]

    df["bucket"] = np.select(
        [
            df["return_pct"] < -0.02,
            (df["return_pct"] < 0) & (df["return_pct"] >= -0.02),
            df["return_pct"] == 0,
            (df["return_pct"] > 0) & (df["return_pct"] <= 0.02),
            df["return_pct"] > 0.02,
        ],
        [
            "down_gt2",
            "down_0_2",
            "stable",
            "up_0_2",
            "up_gt2",
        ],
        default="unknown"
    )

    count_bucket = df["bucket"].value_counts().to_dict()
    mcap_bucket = df.groupby("bucket")["market_cap"].sum().to_dict()
    value_bucket = df.groupby("bucket")["trading_value"].sum().to_dict()

    down_gt2_n = count_bucket.get("down_gt2", 0)
    down_0_2_n = count_bucket.get("down_0_2", 0)
    stable_n = count_bucket.get("stable", 0)
    up_0_2_n = count_bucket.get("up_0_2", 0)
    up_gt2_n = count_bucket.get("up_gt2", 0)

    down_gt2_mcap = mcap_bucket.get("down_gt2", 0)
    down_0_2_mcap = mcap_bucket.get("down_0_2", 0)
    stable_mcap = mcap_bucket.get("stable", 0)
    up_0_2_mcap = mcap_bucket.get("up_0_2", 0)
    up_gt2_mcap = mcap_bucket.get("up_gt2", 0)

    down_gt2_value = value_bucket.get("down_gt2", 0)
    down_0_2_value = value_bucket.get("down_0_2", 0)
    stable_value = value_bucket.get("stable", 0)
    up_0_2_value = value_bucket.get("up_0_2", 0)
    up_gt2_value = value_bucket.get("up_gt2", 0)

    advancers = up_0_2_n + up_gt2_n
    decliners = down_0_2_n + down_gt2_n
    total_active = advancers + decliners
    total_stocks = advancers + decliners + stable_n

    mcap_up = up_0_2_mcap + up_gt2_mcap
    mcap_down = down_0_2_mcap + down_gt2_mcap
    mcap_stable = stable_mcap

    value_up = up_0_2_value + up_gt2_value
    value_down = down_0_2_value + down_gt2_value
    value_stable = stable_value

    ad_score = safe_div(advancers, advancers + decliners) * 100
    strong_movers_score = safe_div(up_gt2_n, up_gt2_n + down_gt2_n) * 100
    mcap_breadth_score = safe_div(mcap_up, mcap_up + mcap_down) * 100
    value_breadth_score = safe_div(value_up, value_up + value_down) * 100
    active_participation_score = safe_div(total_active, total_stocks) * 100

    market_breadth_score_raw = (
        0.30 * ad_score +
        0.20 * strong_movers_score +
        0.20 * mcap_breadth_score +
        0.20 * value_breadth_score +
        0.10 * active_participation_score
    )

    market_breadth_score = round_score(market_breadth_score_raw)

    result = {
        "total_stocks": total_stocks,
        "advancers": advancers,
        "decliners": decliners,
        "stable": stable_n,

        "up_gt2_n": up_gt2_n,
        "up_0_2_n": up_0_2_n,
        "down_0_2_n": down_0_2_n,
        "down_gt2_n": down_gt2_n,

        "mcap_up": mcap_up,
        "mcap_down": mcap_down,
        "mcap_stable": mcap_stable,

        "value_up": value_up,
        "value_down": value_down,
        "value_stable": value_stable,

        "ad_score": round(ad_score, 1),
        "strong_movers_score": round(strong_movers_score, 1),
        "mcap_breadth_score": round(mcap_breadth_score, 1),
        "value_breadth_score": round(value_breadth_score, 1),
        "active_participation_score": round(active_participation_score, 1),

        "market_breadth_score_raw": round(market_breadth_score_raw, 1),
        "market_breadth_score": market_breadth_score,
        "market_breadth_label": market_breadth_label(market_breadth_score),
    }

    return result, df


def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Path file Ringkasan Saham diperlukan"}))
        sys.exit(1)

    input_path = sys.argv[1]
    
    try:
        df = load_ringkasan_saham(input_path, sheet_name=0)
        result, stock_detail = calculate_market_breadth_from_stocks(df)
        
        # We output JSON directly for PHP to consume
        print(json.dumps(result))
    except Exception as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)


if __name__ == "__main__":
    main()
