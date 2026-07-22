"""
Avenir Research — Foreign Flow Momentum v2
==========================================

Menghasilkan:
1. Flow Momentum Score 0–100.
2. Flow Exhaustion Score 0–100.
3. Probabilitas foreign flow 5 hari berikutnya menjadi positif.
4. Behavior phase dan tactical signal.
5. CSV lengkap, summary CSV, dan chart institutional 16:9.

Dependencies:
    pip install pandas numpy matplotlib scipy scikit-learn openpyxl

Catatan:
- Probabilitas reversal menggunakan logistic regression.
- Model adalah konstruksi Avenir Research dan wajib diuji ulang sebelum dipakai live.
"""

from __future__ import annotations

from pathlib import Path

import matplotlib.dates as mdates
import matplotlib.pyplot as plt
import numpy as np
import pandas as pd
from scipy.stats import percentileofscore
from sklearn.linear_model import LogisticRegression
from sklearn.metrics import accuracy_score, brier_score_loss, roc_auc_score
from sklearn.model_selection import TimeSeriesSplit
from sklearn.pipeline import Pipeline
from sklearn.preprocessing import StandardScaler


# =========================================================
# 1. CONFIG
# =========================================================

FOREIGN_FLOW_FILE = Path("Data_Foreign_Flow_20.xlsx")
IHSG_FILE = Path("historical_data-IDX_Composite-5.csv")
FLOW_CALIBRATION_FILE = Path("flow_score_features.csv")

OUTPUT_CSV = Path("avenir_foreign_flow_momentum_v2_output.csv")
OUTPUT_SUMMARY = Path("avenir_foreign_flow_momentum_v2_summary.csv")
OUTPUT_CHART = Path("avenir_foreign_flow_momentum_v2_16x9.png")

# Official/latest IHSG values that may not exist in historical CSV.
IHSG_OVERRIDES = {
    "2026-07-14": 6039.521,
    "2026-07-15": 6041.970,
    "2026-07-16": 6108.210,
    "2026-07-17": 6175.540,
    "2026-07-20": 6231.780,
}

RANDOM_SEED = 42
ROLLING_WINDOW = 60
MIN_PERIODS = 20

CHART_TITLE = "Avenir Foreign Flow Momentum v2"
CHART_SUBTITLE = "Behavior phase and reversal probability"
CHART_SOURCE = (
    "Source: Avenir Research calculation using foreign-flow features "
    "and IHSG historical data."
)


# =========================================================
# 2. HELPERS
# =========================================================


def parse_id_number(value: object) -> float:
    if value is None or pd.isna(value):
        return np.nan

    text = str(value).strip().replace(" ", "")
    if text in {"", "-", "nan", "NaN"}:
        return np.nan

    if "," in text and "." in text and text.rfind(",") > text.rfind("."):
        text = text.replace(".", "").replace(",", ".")
    elif "," in text and "." in text and text.rfind(".") > text.rfind(","):
        text = text.replace(",", "")
    elif "," in text and "." not in text:
        text = text.replace(",", ".")

    return pd.to_numeric(text, errors="coerce")


def parse_unit_number(value: object) -> float:
    if value is None or pd.isna(value):
        return np.nan

    text = str(value).strip().replace(" ", "")
    multiplier = 1.0

    if text.endswith("B"):
        multiplier = 1e9
        text = text[:-1]
    elif text.endswith("T"):
        multiplier = 1e12
        text = text[:-1]

    parsed = parse_id_number(text)
    return float(parsed) * multiplier if pd.notna(parsed) else np.nan


def find_excel_header(raw: pd.DataFrame) -> int:
    for idx in range(min(20, len(raw))):
        values = {str(x).strip().lower() for x in raw.iloc[idx].tolist() if pd.notna(x)}
        if "tanggal" in values and "foreign net flow" in values:
            return idx
    raise ValueError("Header foreign-flow workbook tidak ditemukan.")


def load_foreign_flow(path: Path) -> pd.DataFrame:
    if not path.exists():
        raise FileNotFoundError(f"File tidak ditemukan: {path}")

    no_header = pd.read_excel(path, sheet_name=0, header=None)
    header_row = find_excel_header(no_header)
    frame = pd.read_excel(path, sheet_name=0, header=header_row)
    frame.columns = [str(c).strip() for c in frame.columns]

    required = {"Tanggal", "Foreign Net Flow", "Market Value"}
    missing = required - set(frame.columns)
    if missing:
        raise ValueError(f"Kolom wajib tidak ada: {sorted(missing)}")

    result = pd.DataFrame(
        {
            "date": pd.to_datetime(frame["Tanggal"], errors="coerce"),
            "foreign_net_1d": frame["Foreign Net Flow"].apply(parse_unit_number),
            "market_value_1d": frame["Market Value"].apply(parse_unit_number).abs(),
        }
    )

    if "Price" in frame.columns:
        result["ihsg_from_flow"] = frame["Price"].apply(parse_id_number)

    return (
        result.dropna(subset=["date", "foreign_net_1d", "market_value_1d"])
        .sort_values("date")
        .drop_duplicates("date", keep="last")
        .reset_index(drop=True)
    )


def load_ihsg(path: Path) -> pd.DataFrame:
    if not path.exists():
        raise FileNotFoundError(f"File tidak ditemukan: {path}")

    frame = pd.read_csv(path)
    frame["date"] = pd.to_datetime(
        frame["Tanggal"],
        format="%d/%m/%y",
        errors="coerce",
    )
    frame["ihsg"] = frame["Harga"].apply(parse_id_number)

    result = (
        frame[["date", "ihsg"]]
        .dropna()
        .sort_values("date")
        .drop_duplicates("date", keep="last")
        .reset_index(drop=True)
    )

    for date_text, value in IHSG_OVERRIDES.items():
        result = pd.concat(
            [result, pd.DataFrame({"date": [pd.Timestamp(date_text)], "ihsg": [value]})],
            ignore_index=True,
        )

    return result.sort_values("date").drop_duplicates("date", keep="last").reset_index(drop=True)


def rolling_z(
    series: pd.Series,
    window: int = ROLLING_WINDOW,
    min_periods: int = MIN_PERIODS,
) -> pd.Series:
    mean = series.rolling(window, min_periods=min_periods).mean()
    std = series.rolling(window, min_periods=min_periods).std(ddof=1)
    return ((series - mean) / std).replace([np.inf, -np.inf], np.nan)


def consecutive_negative_days(series: pd.Series) -> pd.Series:
    output: list[float] = []
    count = 0
    for value in series:
        if pd.notna(value) and value < 0:
            count += 1
        else:
            count = 0
        output.append(float(count))
    return pd.Series(output, index=series.index)


# =========================================================
# 3. FEATURE ENGINE
# =========================================================


def build_features() -> pd.DataFrame:
    flow = load_foreign_flow(FOREIGN_FLOW_FILE)
    ihsg = load_ihsg(IHSG_FILE)

    flow["foreign_net_5d"] = flow["foreign_net_1d"].rolling(5).sum()
    flow["foreign_net_20d"] = flow["foreign_net_1d"].rolling(20).sum()
    flow["total_market_value_5d"] = flow["market_value_1d"].rolling(5).sum()
    flow["total_market_value_20d"] = flow["market_value_1d"].rolling(20).sum()
    flow["avg_market_value_5d"] = flow["market_value_1d"].rolling(5).mean()
    flow["avg_market_value_20d"] = flow["market_value_1d"].rolling(20).mean()

    flow["foreign_intensity_5d"] = flow["foreign_net_5d"] / flow["total_market_value_5d"]
    flow["foreign_intensity_20d"] = (
        flow["foreign_net_20d"] / flow["total_market_value_20d"]
    )
    flow["positive_foreign_flow_days_5d"] = (
        (flow["foreign_net_1d"] > 0).rolling(5).sum()
    )
    flow["liquidity_ratio_5d_vs_20d_avg"] = (
        flow["avg_market_value_5d"] / flow["avg_market_value_20d"]
    )

    consistency_map = {0: 10, 1: 25, 2: 45, 3: 65, 4: 85, 5: 100}
    flow["flow_consistency_score"] = (
        flow["positive_foreign_flow_days_5d"].round().map(consistency_map)
    )

    if FLOW_CALIBRATION_FILE.exists():
        historical = pd.read_csv(FLOW_CALIBRATION_FILE)
        all_5d = pd.concat(
            [historical["foreign_intensity_5d"], flow["foreign_intensity_5d"]],
            ignore_index=True,
        ).dropna()
        all_20d = pd.concat(
            [historical["foreign_intensity_20d"], flow["foreign_intensity_20d"]],
            ignore_index=True,
        ).dropna()
    else:
        all_5d = flow["foreign_intensity_5d"].dropna()
        all_20d = flow["foreign_intensity_20d"].dropna()

    flow["p5"] = flow["foreign_intensity_5d"].apply(
        lambda x: np.nan if pd.isna(x) else percentileofscore(all_5d, x, kind="weak")
    )
    flow["p20"] = flow["foreign_intensity_20d"].apply(
        lambda x: np.nan if pd.isna(x) else percentileofscore(all_20d, x, kind="weak")
    )

    flow["foreign_flow_trend_score"] = (
        -11.30216285 + 0.61716879 * flow["p5"] + 0.28582957 * flow["p20"]
    ).clip(5, 100)
    flow["liquidity_confirmation_score"] = (
        79.0470822 * flow["liquidity_ratio_5d_vs_20d_avg"] - 10.096074
    ).clip(5, 100)
    flow["flow_score"] = (
        0.50 * flow["foreign_flow_trend_score"]
        + 0.20 * flow["flow_consistency_score"]
        + 0.30 * flow["liquidity_confirmation_score"]
    ).round()

    df = pd.merge(flow, ihsg, on="date", how="inner").sort_values("date").reset_index(drop=True)

    # Core momentum features
    df["flow_5d_bn"] = df["foreign_net_5d"] / 1e9
    df["flow_20d_bn"] = df["foreign_net_20d"] / 1e9
    df["flow_velocity"] = df["flow_5d_bn"].diff(5) / 5
    df["flow_acceleration"] = df["flow_velocity"].diff(5) / 5
    df["ihsg_return_5d"] = df["ihsg"].pct_change(5)
    df["ihsg_return_20d"] = df["ihsg"].pct_change(20)

    df["velocity_z"] = rolling_z(df["flow_velocity"])
    df["acceleration_z"] = rolling_z(df["flow_acceleration"])
    df["intensity_5d_z"] = rolling_z(df["foreign_intensity_5d"])
    df["intensity_20d_z"] = rolling_z(df["foreign_intensity_20d"])
    df["liquidity_z"] = rolling_z(df["liquidity_ratio_5d_vs_20d_avg"])
    df["price_5d_z"] = rolling_z(df["ihsg_return_5d"])
    df["price_20d_z"] = rolling_z(df["ihsg_return_20d"])

    # Exhaustion
    df["negative_flow_streak"] = consecutive_negative_days(df["foreign_net_1d"])
    df["outflow_20d_z"] = -rolling_z(df["flow_20d_bn"], window=120)
    df["outflow_5d_z"] = -rolling_z(df["flow_5d_bn"])
    df["streak_score"] = (df["negative_flow_streak"] / 15).clip(0, 1)
    acceleration_recovery = ((df["acceleration_z"] + 2) / 4).clip(0, 1)

    df["flow_exhaustion_score"] = (
        100
        * (
            0.45 * (df["outflow_20d_z"] / 3).clip(0, 1).fillna(0)
            + 0.25 * (df["outflow_5d_z"] / 3).clip(0, 1).fillna(0)
            + 0.20 * df["streak_score"].fillna(0)
            + 0.10 * acceleration_recovery.fillna(0)
        )
    ).clip(0, 100)

    # Composite momentum score
    trend_signal = ((df["foreign_flow_trend_score"] - 50) / 25).clip(-2, 2)
    consistency_signal = ((df["flow_consistency_score"] - 50) / 25).clip(-2, 2)
    liquidity_signal = ((df["liquidity_confirmation_score"] - 50) / 25).clip(-2, 2)

    composite_raw = (
        0.25 * df["velocity_z"].fillna(0)
        + 0.20 * df["acceleration_z"].fillna(0)
        + 0.20 * df["intensity_20d_z"].fillna(0)
        + 0.15 * liquidity_signal.fillna(0)
        + 0.10 * consistency_signal.fillna(0)
        + 0.10 * df["price_5d_z"].fillna(0)
    )

    df["flow_momentum_v2_score"] = (50 + 15 * composite_raw).clip(0, 100)

    return df


# =========================================================
# 4. REVERSAL PROBABILITY MODEL
# =========================================================


def add_reversal_probability(df: pd.DataFrame) -> tuple[pd.DataFrame, dict[str, float]]:
    future_5d_flow = df["foreign_net_1d"].shift(-1).rolling(5).sum().shift(-4)
    df["target_positive_flow_next_5d"] = (future_5d_flow > 0).astype(float)

    feature_columns = [
        "velocity_z",
        "acceleration_z",
        "intensity_5d_z",
        "intensity_20d_z",
        "liquidity_z",
        "price_5d_z",
        "price_20d_z",
        "flow_exhaustion_score",
        "foreign_flow_trend_score",
        "flow_consistency_score",
        "liquidity_confirmation_score",
    ]

    training_mask = (
        df[feature_columns].notna().all(axis=1)
        & df.index.to_series().le(len(df) - 6)
    )
    train = df.loc[training_mask, feature_columns + ["target_positive_flow_next_5d"]].copy()

    if len(train) < 50 or train["target_positive_flow_next_5d"].nunique() < 2:
        raise ValueError("Data training tidak cukup untuk reversal probability model.")

    X = train[feature_columns]
    y = train["target_positive_flow_next_5d"].astype(int)

    n_splits = min(5, max(2, len(train) // 60))
    splitter = TimeSeriesSplit(n_splits=n_splits)
    oos_probability = pd.Series(index=train.index, dtype=float)

    for train_idx, test_idx in splitter.split(X):
        y_train = y.iloc[train_idx]
        if y_train.nunique() < 2:
            continue

        fold_model = Pipeline(
            [
                ("scale", StandardScaler()),
                (
                    "logit",
                    LogisticRegression(
                        C=0.5,
                        max_iter=2000,
                        class_weight="balanced",
                        random_state=RANDOM_SEED,
                    ),
                ),
            ]
        )
        fold_model.fit(X.iloc[train_idx], y_train)
        oos_probability.loc[X.iloc[test_idx].index] = fold_model.predict_proba(
            X.iloc[test_idx]
        )[:, 1]

    model = Pipeline(
        [
            ("scale", StandardScaler()),
            (
                "logit",
                LogisticRegression(
                    C=0.5,
                    max_iter=2000,
                    class_weight="balanced",
                    random_state=RANDOM_SEED,
                ),
            ),
        ]
    )
    model.fit(X, y)

    all_features = df[feature_columns].notna().all(axis=1)
    df.loc[all_features, "reversal_probability"] = (
        model.predict_proba(df.loc[all_features, feature_columns])[:, 1] * 100
    )
    df["reversal_probability_oos"] = np.nan
    df.loc[oos_probability.index, "reversal_probability_oos"] = oos_probability * 100

    valid_oos = oos_probability.notna()
    metrics = {"auc": np.nan, "accuracy": np.nan, "brier": np.nan, "rows": len(train)}

    if valid_oos.sum() >= 20 and y.loc[valid_oos].nunique() == 2:
        actual = y.loc[valid_oos]
        probability = oos_probability.loc[valid_oos]
        metrics["auc"] = roc_auc_score(actual, probability)
        metrics["accuracy"] = accuracy_score(actual, (probability >= 0.5).astype(int))
        metrics["brier"] = brier_score_loss(actual, probability)

    return df, metrics


# =========================================================
# 5. PHASE AND SIGNAL
# =========================================================


def add_phase_and_signal(df: pd.DataFrame) -> pd.DataFrame:
    conditions = [
        (df["flow_exhaustion_score"] >= 70) & (df["flow_momentum_v2_score"] < 45),
        (df["flow_exhaustion_score"] >= 60)
        & (df["flow_momentum_v2_score"] >= 45)
        & (df["flow_momentum_v2_score"] < 60),
        (df["flow_momentum_v2_score"] >= 60) & (df["reversal_probability"] >= 60),
        (df["flow_momentum_v2_score"] >= 70) & (df["foreign_net_5d"] > 0),
        (df["flow_momentum_v2_score"] < 35) & (df["foreign_net_5d"] < 0),
        (df["flow_momentum_v2_score"] < 50) & (df["ihsg_return_5d"] > 0),
    ]
    choices = [
        "Capitulation / Exhaustion",
        "Absorption",
        "Accumulation",
        "Positive Momentum",
        "Selling Acceleration",
        "Distribution Risk",
    ]
    df["foreign_behavior_phase"] = np.select(
        conditions,
        choices,
        default="Neutral / Transition",
    )

    df["v2_signal"] = np.select(
        [
            (df["reversal_probability"] >= 65) & (df["flow_momentum_v2_score"] >= 55),
            (df["reversal_probability"] <= 35) & (df["flow_momentum_v2_score"] <= 40),
        ],
        ["BUY / ACCUMULATION", "SELL / DISTRIBUTION"],
        default="HOLD / WAIT",
    )

    return df


# =========================================================
# 6. CHART
# =========================================================


def plot_chart(df: pd.DataFrame) -> None:
    chart_df = df.dropna(
        subset=[
            "flow_momentum_v2_score",
            "flow_exhaustion_score",
            "reversal_probability",
        ]
    ).copy()
    last = chart_df.iloc[-1]

    plt.rcParams.update(
        {
            "font.family": "DejaVu Sans",
            "font.size": 10,
            "axes.labelsize": 11,
            "xtick.labelsize": 10,
            "ytick.labelsize": 10,
            "legend.fontsize": 10,
        }
    )

    fig, (ax, ax2) = plt.subplots(
        2, 1, figsize=(16, 9), dpi=180,
        sharex=True, gridspec_kw={"height_ratios": [2.1, 1], "hspace": 0.08},
    )

    # Ambang titik ekstrem momentum berbasis persentil data aktual (transparan & adaptif)
    EXTREMA_WIN, FWD = 10, 15
    mom = chart_df["flow_momentum_v2_score"].to_numpy()
    ihsg_arr = chart_df["ihsg"].to_numpy()
    n_pts = len(chart_df)
    LOW_TH = float(np.nanpercentile(mom, 15))
    HIGH_TH = float(np.nanpercentile(mom, 85))

    ax.plot(
        chart_df["date"],
        chart_df["flow_momentum_v2_score"],
        color="black",
        linewidth=2.4,
        label="Flow Momentum V2",
    )
    ax.plot(
        chart_df["date"],
        chart_df["flow_exhaustion_score"],
        color="#4E7D52",
        linewidth=2.2,
        linestyle="--",
        label="Flow Exhaustion",
    )
    ax.plot(
        chart_df["date"],
        chart_df["reversal_probability"],
        color="#8A5A5A",
        linewidth=2.1,
        linestyle="-.",
        label="Reversal Probability",
    )

    last_date = last["date"]
    label_offset = pd.Timedelta(days=5)
    for column, label, color, suffix in [
        ("flow_momentum_v2_score", "Momentum", "black", ""),
        ("flow_exhaustion_score", "Exhaustion", "#4E7D52", ""),
        ("reversal_probability", "Reversal", "#8A5A5A", "%"),
    ]:
        value = float(last[column])
        ax.scatter([last_date], [value], color=color, s=40, zorder=5)
        ax.text(
            last_date + label_offset,
            value,
            f"{label} {value:.1f}{suffix}",
            va="center",
            fontsize=10,
            fontweight="bold",
            color=color,
        )

    fig.suptitle(
        CHART_TITLE,
        x=0.5,
        y=0.965,
        ha="center",
        fontsize=19,
        fontweight="bold",
    )
    fig.text(
        0.5,
        0.915,
        f"Latest phase: {last['foreign_behavior_phase']} | Signal: {last['v2_signal']}"
        f"  |  \u25bc Momentum lemah (p15<{LOW_TH:.0f})  \u25b2 Momentum kuat (p85>{HIGH_TH:.0f})"
        f"  \u00b7 return IHSG {FWD} hari bursa",
        ha="center",
        fontsize=11,
        color="#555555",
    )

    ax.set_ylabel("Score / Probability", fontsize=11, fontweight="bold", labelpad=12)
    ax.set_ylim(0, 100)
    ax.grid(axis="y", linestyle=":", linewidth=0.8, alpha=0.32)
    ax.grid(axis="x", visible=False)

    ax.spines["left"].set_linewidth(1.5)
    ax.spines["bottom"].set_linewidth(1.5)
    ax.spines["top"].set_visible(False)
    ax.spines["right"].set_visible(False)
    ax.tick_params(axis="both", width=1.3, length=6, color="black")

    # ===== DETEKSI TITIK MOMENTUM EKSTREM LOKAL =====
    low_pts, high_pts = [], []
    for i in range(EXTREMA_WIN, n_pts - EXTREMA_WIN):
        win = mom[i - EXTREMA_WIN:i + EXTREMA_WIN + 1]
        if mom[i] == win.min() and mom[i] < LOW_TH:
            low_pts.append(i)
        elif mom[i] == win.max() and mom[i] > HIGH_TH:
            high_pts.append(i)

    def dedup(idxs, gap=EXTREMA_WIN):
        out = []
        for i in idxs:
            if not out or i - out[-1] > gap:
                out.append(i)
        return out
    low_pts, high_pts = dedup(low_pts), dedup(high_pts)

    def fwd_ret(i):
        j = min(i + FWD, n_pts - 1)
        if pd.isna(ihsg_arr[i]) or pd.isna(ihsg_arr[j]) or ihsg_arr[i] == 0:
            return None
        return (ihsg_arr[j] / ihsg_arr[i] - 1) * 100

    for i in low_pts:
        ax.scatter(chart_df["date"].iloc[i], mom[i], marker="v", s=80,
                   color="#B0413E", zorder=6, edgecolor="white", linewidth=0.6)
        ax.text(chart_df["date"].iloc[i], mom[i] - 5,
                f"Low {mom[i]:.0f}", ha="center", va="top", fontsize=8, color="#B0413E")
    for i in high_pts:
        ax.scatter(chart_df["date"].iloc[i], mom[i], marker="^", s=80,
                   color="#4E7D52", zorder=6, edgecolor="white", linewidth=0.6)
        ax.text(chart_df["date"].iloc[i], mom[i] + 4,
                f"High {mom[i]:.0f}", ha="center", va="bottom", fontsize=8, color="#4E7D52")

    # ===== PANEL BAWAH: IHSG =====
    ax2.plot(chart_df["date"], chart_df["ihsg"], color="#2C5F8A", linewidth=2.0, label="IHSG")
    ax2.fill_between(chart_df["date"], chart_df["ihsg"], chart_df["ihsg"].min(),
                     color="#2C5F8A", alpha=0.07)
    for i in low_pts:
        r = fwd_ret(i)
        ax2.scatter(chart_df["date"].iloc[i], ihsg_arr[i], marker="v", s=70,
                    color="#B0413E", zorder=6, edgecolor="white", linewidth=0.6)
        if r is not None:
            ax2.annotate(f"{r:+.1f}% ({FWD}d)",
                         (chart_df["date"].iloc[i], ihsg_arr[i]),
                         textcoords="offset points", xytext=(0, 12),
                         ha="center", fontsize=8,
                         color=("#4E7D52" if r >= 0 else "#B0413E"), fontweight="bold")
    for i in high_pts:
        r = fwd_ret(i)
        ax2.scatter(chart_df["date"].iloc[i], ihsg_arr[i], marker="^", s=70,
                    color="#4E7D52", zorder=6, edgecolor="white", linewidth=0.6)
        if r is not None:
            ax2.annotate(f"{r:+.1f}% ({FWD}d)",
                         (chart_df["date"].iloc[i], ihsg_arr[i]),
                         textcoords="offset points", xytext=(0, -16),
                         ha="center", fontsize=8,
                         color=("#4E7D52" if r >= 0 else "#B0413E"), fontweight="bold")
    ax2.scatter([last_date], [last["ihsg"]], color="#2C5F8A", s=40, zorder=5)
    ax2.text(last_date + label_offset, last["ihsg"],
             f"IHSG {last['ihsg']:,.0f}", va="center", fontsize=10,
             fontweight="bold", color="#2C5F8A")
    ax2.set_ylabel("IHSG", fontsize=11, fontweight="bold", labelpad=12)
    ax2.grid(axis="y", linestyle=":", linewidth=0.8, alpha=0.32)
    ax2.spines["left"].set_linewidth(1.5)
    ax2.spines["bottom"].set_linewidth(1.5)
    ax2.spines["top"].set_visible(False)
    ax2.spines["right"].set_visible(False)
    ax2.tick_params(axis="both", width=1.3, length=6, color="black")
    ax2.legend(loc="upper left", frameon=False)

    ax.xaxis.set_major_locator(mdates.MonthLocator(interval=1))
    ax2.xaxis.set_major_formatter(mdates.DateFormatter("%b\n%Y"))
    ax2.set_xlim(chart_df["date"].min(), last_date + pd.Timedelta(days=42))

    ax.legend(loc="upper left", frameon=False, ncol=3, bbox_to_anchor=(0, 0.985))
    fig.text(0.01, 0.018, CHART_SOURCE, fontsize=10, color="#666666")

    plt.tight_layout(rect=[0, 0.055, 1, 0.885])
    plt.savefig(OUTPUT_CHART, dpi=180, bbox_inches="tight", facecolor="white")
    plt.show()


# =========================================================
# 7. MAIN
# =========================================================


def main() -> None:
    df = build_features()
    df, metrics = add_reversal_probability(df)
    df = add_phase_and_signal(df)
    df.to_csv(OUTPUT_CSV, index=False)

    last = df.dropna(subset=["reversal_probability"]).iloc[-1]
    summary = pd.DataFrame(
        {
            "metric": [
                "date",
                "ihsg",
                "foreign_net_5d_bn",
                "foreign_net_20d_bn",
                "flow_velocity",
                "flow_acceleration",
                "flow_momentum_v2_score",
                "flow_exhaustion_score",
                "reversal_probability",
                "foreign_behavior_phase",
                "v2_signal",
                "oos_auc",
                "oos_accuracy",
                "oos_brier",
                "training_rows",
            ],
            "value": [
                str(last["date"].date()),
                f"{last['ihsg']:.2f}",
                f"{last['flow_5d_bn']:.2f}",
                f"{last['flow_20d_bn']:.2f}",
                f"{last['flow_velocity']:.4f}",
                f"{last['flow_acceleration']:.4f}",
                f"{last['flow_momentum_v2_score']:.2f}",
                f"{last['flow_exhaustion_score']:.2f}",
                f"{last['reversal_probability']:.2f}",
                str(last["foreign_behavior_phase"]),
                str(last["v2_signal"]),
                f"{metrics['auc']:.4f}" if pd.notna(metrics["auc"]) else "NA",
                f"{metrics['accuracy']:.4f}" if pd.notna(metrics["accuracy"]) else "NA",
                f"{metrics['brier']:.4f}" if pd.notna(metrics["brier"]) else "NA",
                str(metrics["rows"]),
            ],
        }
    )
    summary.to_csv(OUTPUT_SUMMARY, index=False)

    plot_chart(df)

    print("DONE")
    print(f"Date                 : {last['date'].date()}")
    print(f"Momentum V2          : {last['flow_momentum_v2_score']:.2f}")
    print(f"Flow Exhaustion      : {last['flow_exhaustion_score']:.2f}")
    print(f"Reversal Probability : {last['reversal_probability']:.2f}%")
    print(f"Behavior Phase       : {last['foreign_behavior_phase']}")
    print(f"Signal               : {last['v2_signal']}")
    print(f"Chart saved          : {OUTPUT_CHART}")
    print(f"CSV saved            : {OUTPUT_CSV}")
    print(f"Summary saved        : {OUTPUT_SUMMARY}")


if __name__ == "__main__":
    main()
