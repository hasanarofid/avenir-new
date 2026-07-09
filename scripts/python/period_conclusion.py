# =========================================================
# AVENIR PERIOD CONCLUSION ENGINE
# Input  : historical regime score table
# Output : narrative conclusion paragraph
# =========================================================

import pandas as pd
import numpy as np


# =========================================================
# 1. CONFIG
# =========================================================

INPUT_PATH = "regime_scores.csv"

# Required columns:
# date
# price_trend
# market_breadth
# flow
# sector_rotation
# volatility_stability
# regime_score
# regime_label


# =========================================================
# 2. LABEL MAP
# =========================================================

COMPONENT_LABELS = {
    "price_trend": "price trend",
    "market_breadth": "breadth",
    "flow": "foreign flow",
    "sector_rotation": "sector rotation",
    "volatility_stability": "market stability",
}


def clean_regime_label(label):
    if pd.isna(label):
        return "Unknown"

    label = str(label).strip()

    replace_map = {
        "Stress / Risk-Off Pressure": "Stress / Risk-Off Pressure",
        "Risk-Off": "Risk-Off",
        "Defensive Neutral": "Defensive Neutral",
        "Neutral Rotation": "Neutral Rotation",
        "Neutral Rotation / Tactical Rebound": "Neutral Rotation",
        "Constructive Rotation": "Constructive Rotation",
        "Risk-On Accumulation": "Risk-On Accumulation",
    }

    return replace_map.get(label, label)


def regime_state_from_score(score):
    if score < 35:
        return "stress conditions"
    if score < 45:
        return "risk-off pressure"
    if score < 55:
        return "a defensive neutral phase"
    if score < 65:
        return "a tactical recovery attempt"
    if score < 75:
        return "a constructive rotation phase"
    return "a risk-on accumulation phase"


def month_phase(date):
    day = date.day
    month = date.strftime("%B")

    if day <= 10:
        phase = "early"
    elif day <= 20:
        phase = "mid"
    else:
        phase = "late"

    return f"{phase} {month}"


def format_date(date):
    # 7 July, 8 July, dst
    return f"{date.day} {date.strftime('%B')}"


# =========================================================
# 3. COMPONENT HELPERS
# =========================================================

def get_component_columns(df):
    cols = [
        "price_trend",
        "market_breadth",
        "flow",
        "sector_rotation",
        "volatility_stability",
    ]

    return [col for col in cols if col in df.columns]


def top_supports(row, min_score=55, max_items=3):
    component_cols = get_component_columns(pd.DataFrame([row]))

    items = []

    for col in component_cols:
        score = row[col]
        if pd.notna(score) and score >= min_score:
            items.append((col, score))

    items = sorted(items, key=lambda x: x[1], reverse=True)

    if not items:
        # fallback: ambil top 3 komponen tertinggi
        items = [
            (col, row[col])
            for col in component_cols
            if pd.notna(row[col])
        ]
        items = sorted(items, key=lambda x: x[1], reverse=True)

    labels = [COMPONENT_LABELS[col] for col, _ in items[:max_items]]

    return labels


def breakdown_components(peak_row, last_row, max_items=3):
    component_cols = get_component_columns(pd.DataFrame([last_row]))

    broken = []

    for col in component_cols:
        peak_score = peak_row[col]
        last_score = last_row[col]

        if pd.isna(peak_score) or pd.isna(last_score):
            continue

        drop = peak_score - last_score

        # Komponen dianggap breakdown kalau:
        # 1. score akhir < 35, atau
        # 2. turun > 20 poin dari peak dan score akhir < 50
        if last_score < 35 or (drop >= 20 and last_score < 50):
            broken.append((col, drop, last_score))

    broken = sorted(broken, key=lambda x: (x[2], -x[1]))

    labels = [COMPONENT_LABELS[col] for col, _, _ in broken[:max_items]]

    return labels


def detect_persistent_drag(df):
    """
    Cari komponen yang tidak pernah benar-benar confirm selama periode.
    Priority: foreign flow, karena di market regime flow sering jadi validator.
    """

    if "flow" in df.columns:
        flow_avg = df["flow"].mean()
        flow_max = df["flow"].max()

        if flow_avg < 45 or flow_max < 50:
            return "foreign flow never confirmed the rebound"

    weak_components = []

    for col in get_component_columns(df):
        avg_score = df[col].mean()
        max_score = df[col].max()

        if avg_score < 45 and max_score < 55:
            weak_components.append((col, avg_score))

    if weak_components:
        weak_components = sorted(weak_components, key=lambda x: x[1])
        label = COMPONENT_LABELS[weak_components[0][0]]
        return f"{label} never fully confirmed the move"

    return None


def join_items(items):
    if not items:
        return ""

    if len(items) == 1:
        return items[0]

    if len(items) == 2:
        return f"{items[0]} and {items[1]}"

    return ", ".join(items[:-1]) + f", and {items[-1]}"


# =========================================================
# 4. PERIOD CONCLUSION ENGINE
# =========================================================

def build_period_conclusion(df):
    df = df.copy()

    df["date"] = pd.to_datetime(df["date"])
    df = df.sort_values("date").reset_index(drop=True)

    df["regime_label"] = df["regime_label"].apply(clean_regime_label)

    start_row = df.iloc[0]
    last_row = df.iloc[-1]
    peak_row = df.loc[df["regime_score"].idxmax()]
    trough_row = df.loc[df["regime_score"].idxmin()]

    start_date = start_row["date"]
    last_date = last_row["date"]
    peak_date = peak_row["date"]
    trough_date = trough_row["date"]

    start_score = start_row["regime_score"]
    last_score = last_row["regime_score"]
    peak_score = peak_row["regime_score"]
    trough_score = trough_row["regime_score"]

    # Cari kondisi awal utama:
    # Kalau ada stress/risk-off sebelum peak, pakai itu sebagai starting condition.
    pre_peak = df[df["date"] <= peak_date].copy()
    pre_peak_low = pre_peak.loc[pre_peak["regime_score"].idxmin()]

    initial_state = regime_state_from_score(pre_peak_low["regime_score"])
    initial_period = month_phase(pre_peak_low["date"])

    recovery_state = regime_state_from_score(peak_score)
    recovery_period = month_phase(peak_date)

    supports = top_supports(peak_row)
    support_text = join_items(supports)

    persistent_drag = detect_persistent_drag(df)

    broken = breakdown_components(peak_row, last_row)
    broken_text = join_items(broken)

    last_label = clean_regime_label(last_row["regime_label"])

    # =========================
    # Sentence 1
    # =========================

    sentence_1 = (
        f"The market moved from {initial_state} in {initial_period} "
        f"into {recovery_state} in {recovery_period}."
    )

    # =========================
    # Sentence 2
    # =========================

    if support_text:
        sentence_2 = (
            f"The strongest confirmation appeared on {format_date(peak_date)}, "
            f"supported by {support_text}."
        )
    else:
        sentence_2 = (
            f"The strongest confirmation appeared on {format_date(peak_date)}."
        )

    # =========================
    # Sentence 3
    # =========================

    if persistent_drag:
        sentence_3 = f"However, {persistent_drag}."
    else:
        sentence_3 = (
            "However, confirmation was not strong enough to sustain a full regime upgrade."
        )

    # =========================
    # Sentence 4
    # =========================

    if last_score <= peak_score - 15 and last_score < 45:
        if broken_text:
            sentence_4 = (
                f"On {format_date(last_date)}, {broken_text} broke down sharply, "
                f"pushing the regime back into {last_label}."
            )
        else:
            sentence_4 = (
                f"On {format_date(last_date)}, the regime deteriorated sharply, "
                f"pushing the market back into {last_label}."
            )

    elif last_score < peak_score - 10:
        sentence_4 = (
            f"By {format_date(last_date)}, the regime had weakened from its peak, "
            f"ending the period in {last_label}."
        )

    else:
        sentence_4 = (
            f"By {format_date(last_date)}, the regime remained in {last_label}."
        )

    conclusion = " ".join([
        sentence_1,
        sentence_2,
        sentence_3,
        sentence_4,
    ])

    payload = {
        "start_date": str(start_date.date()),
        "end_date": str(last_date.date()),
        "peak_date": str(peak_date.date()),
        "peak_score": int(peak_score),
        "trough_date": str(trough_date.date()),
        "trough_score": int(trough_score),
        "final_score": int(last_score),
        "final_label": last_label,
        "period_conclusion": conclusion,
    }

    return payload


# =========================================================
# 5. MAIN
# =========================================================

def main():
    df = pd.read_csv(INPUT_PATH)

    payload = build_period_conclusion(df)

    print("\n=== PERIOD CONCLUSION ===")
    print(payload["period_conclusion"])

    print("\n=== PAYLOAD ===")
    print(payload)


if __name__ == "__main__":
    main()
