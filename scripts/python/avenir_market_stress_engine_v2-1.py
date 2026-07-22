"""
Avenir Research — Market Stress Engine
======================================

Menghasilkan:
1. CSV lengkap komponen stress.
2. Chart institutional 16:9.

Komponen model:
- Macro Stress: USD/IDR dan Indonesia 10Y.
- Flow Stress: foreign-flow intensity, flow score, dan likuiditas.
- Internal Stress: drawdown dan realized volatility IHSG.
- Composite: 40% macro + 35% flow + 25% internal.

Dependencies:
    pip install pandas numpy matplotlib scipy openpyxl

Catatan:
- Model score adalah konstruksi Avenir Research, bukan indikator resmi BEI.
- Untuk tanggal terbaru yang belum ada di CSV historis, isi MARKET_OVERRIDES.
"""

from __future__ import annotations

from pathlib import Path
from typing import Dict

import matplotlib.dates as mdates
import matplotlib.pyplot as plt
import numpy as np
import pandas as pd
from scipy.stats import percentileofscore


# =========================================================
# 1. CONFIG
# =========================================================

IHSG_FILE = Path("historical_data-IDX_Composite-5.csv")
USD_FILE = Path("historical_data-USD_IDR.csv")
YIELD_FILE = Path("historical_data-Indonesia_10T.csv")
FOREIGN_FLOW_FILE = Path("Data_Foreign_Flow_20.xlsx")
FLOW_CALIBRATION_FILE = Path("flow_score_features.csv")

OUTPUT_CSV = Path("avenir_market_stress_engine_output.csv")
OUTPUT_CHART = Path("avenir_market_stress_engine_16x9.png")

# Isi atau ubah nilai ini ketika data terbaru belum masuk ke CSV historis.
# Key = YYYY-MM-DD.
MARKET_OVERRIDES: Dict[str, Dict[str, float]] = {
    "2026-07-14": {
        "ihsg": 6039.521,
        "usd_idr": 18099.0,
        "yield_10y": 7.259,
    },
    "2026-07-15": {
        "ihsg": 6041.970,
        "usd_idr": 18064.0,
        "yield_10y": 7.236,
    },
    "2026-07-16": {
        "ihsg": 6108.210,
        "usd_idr": 17985.0,
        "yield_10y": 7.237,
    },
    "2026-07-17": {
        "ihsg": 6175.540,
        "usd_idr": 17890.0,
        "yield_10y": 7.267,
    },
    "2026-07-20": {
        "ihsg": 6231.780,
        "usd_idr": 17948.4,
        "yield_10y": 7.267,
    },
}

# Model parameters
ROLLING_WINDOW = 60
MIN_PERIODS = 20
SMOOTHING_SPAN = 3
SIGMOID_SLOPE = 1.15

MACRO_WEIGHT = 0.40
FLOW_WEIGHT = 0.35
INTERNAL_WEIGHT = 0.25

# Chart style
CHART_TITLE = "Avenir Market Stress Engine"
CHART_SOURCE = (
    "Source: IHSG, USD/IDR, Indonesia 10Y, and foreign-flow historical data. "
    "Avenir Research calculation."
)


# =========================================================
# 2. HELPERS
# =========================================================


def parse_id_number(value: object) -> float:
    """Parse angka format Indonesia/US menjadi float."""
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
    """Parse angka dengan suffix B/T ke nilai rupiah penuh."""
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


def load_investing_csv(path: Path, value_name: str) -> pd.DataFrame:
    """Load CSV Investing-style dengan kolom Tanggal dan Harga."""
    if not path.exists():
        raise FileNotFoundError(f"File tidak ditemukan: {path}")

    frame = pd.read_csv(path)
    required = {"Tanggal", "Harga"}
    missing = required - set(frame.columns)
    if missing:
        raise ValueError(f"{path.name}: kolom wajib tidak ada: {sorted(missing)}")

    frame["date"] = pd.to_datetime(
        frame["Tanggal"],
        format="%d/%m/%y",
        errors="coerce",
    )
    frame[value_name] = frame["Harga"].apply(parse_id_number)

    result = (
        frame[["date", value_name]]
        .dropna()
        .sort_values("date")
        .drop_duplicates("date", keep="last")
        .reset_index(drop=True)
    )

    if result.empty:
        raise ValueError(f"{path.name}: data kosong setelah cleaning.")

    return result


def find_excel_header(raw: pd.DataFrame) -> int:
    """Cari baris header workbook foreign flow."""
    for idx in range(min(20, len(raw))):
        values = {str(x).strip().lower() for x in raw.iloc[idx].tolist() if pd.notna(x)}
        if "tanggal" in values and "foreign net flow" in values:
            return idx
    raise ValueError("Header 'Tanggal' dan 'Foreign Net Flow' tidak ditemukan.")


def load_foreign_flow_workbook(path: Path) -> pd.DataFrame:
    """Load raw daily foreign flow workbook dan normalisasi nama kolom."""
    if not path.exists():
        raise FileNotFoundError(f"File tidak ditemukan: {path}")

    raw_no_header = pd.read_excel(path, sheet_name=0, header=None)
    header_row = find_excel_header(raw_no_header)
    frame = pd.read_excel(path, sheet_name=0, header=header_row)

    frame.columns = [str(c).strip() for c in frame.columns]
    required = {"Tanggal", "Foreign Net Flow", "Market Value"}
    missing = required - set(frame.columns)
    if missing:
        raise ValueError(f"{path.name}: kolom wajib tidak ada: {sorted(missing)}")

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


def rolling_z(
    series: pd.Series,
    window: int = ROLLING_WINDOW,
    min_periods: int = MIN_PERIODS,
) -> pd.Series:
    mean = series.rolling(window, min_periods=min_periods).mean()
    std = series.rolling(window, min_periods=min_periods).std(ddof=1)
    return ((series - mean) / std).replace([np.inf, -np.inf], np.nan)


def sigmoid_score(z: pd.Series, slope: float = SIGMOID_SLOPE) -> pd.Series:
    clipped = z.clip(-5, 5)
    return 100 / (1 + np.exp(-slope * clipped))


def apply_market_overrides(
    ihsg: pd.DataFrame,
    usd: pd.DataFrame,
    yield_10y: pd.DataFrame,
) -> tuple[pd.DataFrame, pd.DataFrame, pd.DataFrame]:
    """Append/update manual latest observations."""
    for date_text, values in MARKET_OVERRIDES.items():
        date = pd.Timestamp(date_text)

        if "ihsg" in values:
            ihsg = pd.concat(
                [ihsg, pd.DataFrame({"date": [date], "ihsg": [values["ihsg"]]})],
                ignore_index=True,
            )
        if "usd_idr" in values:
            usd = pd.concat(
                [usd, pd.DataFrame({"date": [date], "usd_idr": [values["usd_idr"]]})],
                ignore_index=True,
            )
        if "yield_10y" in values:
            yield_10y = pd.concat(
                [
                    yield_10y,
                    pd.DataFrame({"date": [date], "yield_10y": [values["yield_10y"]]}),
                ],
                ignore_index=True,
            )

    ihsg = ihsg.sort_values("date").drop_duplicates("date", keep="last").reset_index(drop=True)
    usd = usd.sort_values("date").drop_duplicates("date", keep="last").reset_index(drop=True)
    yield_10y = (
        yield_10y.sort_values("date").drop_duplicates("date", keep="last").reset_index(drop=True)
    )
    return ihsg, usd, yield_10y


# =========================================================
# 3. FOREIGN-FLOW FEATURE ENGINE
# =========================================================


def build_flow_features(raw_flow: pd.DataFrame) -> pd.DataFrame:
    flow = raw_flow.copy()

    flow["foreign_net_5d"] = flow["foreign_net_1d"].rolling(5).sum()
    flow["foreign_net_20d"] = flow["foreign_net_1d"].rolling(20).sum()
    flow["total_market_value_5d"] = flow["market_value_1d"].rolling(5).sum()
    flow["total_market_value_20d"] = flow["market_value_1d"].rolling(20).sum()
    flow["avg_market_value_5d"] = flow["market_value_1d"].rolling(5).mean()
    flow["avg_market_value_20d"] = flow["market_value_1d"].rolling(20).mean()

    flow["foreign_intensity_5d"] = (
        flow["foreign_net_5d"] / flow["total_market_value_5d"]
    )
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
        lambda x: np.nan
        if pd.isna(x)
        else percentileofscore(all_5d, x, kind="weak")
    )
    flow["p20"] = flow["foreign_intensity_20d"].apply(
        lambda x: np.nan
        if pd.isna(x)
        else percentileofscore(all_20d, x, kind="weak")
    )

    # Calibrated mapping from historical Avenir flow features.
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

    return flow


# =========================================================
# 4. MARKET STRESS MODEL
# =========================================================


def calculate_market_stress() -> pd.DataFrame:
    ihsg = load_investing_csv(IHSG_FILE, "ihsg")
    usd = load_investing_csv(USD_FILE, "usd_idr")
    yield_10y = load_investing_csv(YIELD_FILE, "yield_10y")
    ihsg, usd, yield_10y = apply_market_overrides(ihsg, usd, yield_10y)

    raw_flow = load_foreign_flow_workbook(FOREIGN_FLOW_FILE)
    flow = build_flow_features(raw_flow)

    df = ihsg.sort_values("date").copy()
    df = pd.merge_asof(df, usd.sort_values("date"), on="date", direction="backward")
    df = pd.merge_asof(
        df,
        yield_10y.sort_values("date"),
        on="date",
        direction="backward",
    )
    df = pd.merge_asof(
        df,
        flow[
            [
                "date",
                "foreign_intensity_5d",
                "foreign_intensity_20d",
                "flow_score",
                "liquidity_ratio_5d_vs_20d_avg",
            ]
        ].sort_values("date"),
        on="date",
        direction="backward",
    )

    df = (
        df.dropna(
            subset=[
                "ihsg",
                "usd_idr",
                "yield_10y",
                "foreign_intensity_5d",
                "foreign_intensity_20d",
                "flow_score",
            ]
        )
        .sort_values("date")
        .reset_index(drop=True)
    )

    # Macro features
    df["usd_return_5d"] = df["usd_idr"].pct_change(5)
    df["yield_change_5d"] = df["yield_10y"].diff(5)
    df["usd_level_z"] = rolling_z(df["usd_idr"])
    df["usd_return_5d_z"] = rolling_z(df["usd_return_5d"])
    df["yield_level_z"] = rolling_z(df["yield_10y"])
    df["yield_change_5d_z"] = rolling_z(df["yield_change_5d"])

    # Flow and liquidity features
    df["foreign_intensity_5d_z"] = rolling_z(df["foreign_intensity_5d"])
    df["foreign_intensity_20d_z"] = rolling_z(df["foreign_intensity_20d"])
    df["flow_score_stress"] = (50 - df["flow_score"]) / 15
    df["liquidity_z"] = rolling_z(df["liquidity_ratio_5d_vs_20d_avg"])

    # Internal market features
    df["ihsg_return_1d"] = df["ihsg"].pct_change()
    df["realized_vol_20d"] = (
        df["ihsg_return_1d"].rolling(20).std(ddof=1) * np.sqrt(252)
    )
    df["rolling_peak_60d"] = df["ihsg"].rolling(60, min_periods=20).max()
    df["drawdown_60d"] = df["ihsg"] / df["rolling_peak_60d"] - 1
    df["volatility_z"] = rolling_z(df["realized_vol_20d"])
    df["drawdown_stress_z"] = rolling_z(-df["drawdown_60d"])

    # Stress components
    df["macro_stress_raw"] = (
        0.35 * df["usd_level_z"].fillna(0)
        + 0.25 * df["usd_return_5d_z"].fillna(0)
        + 0.25 * df["yield_level_z"].fillna(0)
        + 0.15 * df["yield_change_5d_z"].fillna(0)
    )
    df["macro_stress"] = sigmoid_score(df["macro_stress_raw"])

    df["flow_stress_raw"] = (
        0.30 * (-df["foreign_intensity_5d_z"]).fillna(0)
        + 0.25 * (-df["foreign_intensity_20d_z"]).fillna(0)
        + 0.30 * df["flow_score_stress"].fillna(0)
        + 0.15 * (-df["liquidity_z"]).fillna(0)
    )
    df["flow_stress"] = sigmoid_score(df["flow_stress_raw"])

    df["internal_stress_raw"] = (
        0.55 * df["drawdown_stress_z"].fillna(0)
        + 0.45 * df["volatility_z"].fillna(0)
    )
    df["internal_stress"] = sigmoid_score(df["internal_stress_raw"])

    df["flow_internal_stress"] = (
        0.60 * df["flow_stress"] + 0.40 * df["internal_stress"]
    )
    df["market_stress_composite"] = (
        MACRO_WEIGHT * df["macro_stress"]
        + FLOW_WEIGHT * df["flow_stress"]
        + INTERNAL_WEIGHT * df["internal_stress"]
    )

    for column in ["macro_stress", "flow_internal_stress", "market_stress_composite"]:
        df[column] = df[column].ewm(span=SMOOTHING_SPAN, adjust=False).mean()

    df["stress_regime"] = pd.cut(
        df["market_stress_composite"],
        bins=[-np.inf, 20, 40, 60, 80, np.inf],
        labels=["Low Stress", "Normal", "Elevated", "High Stress", "Extreme Stress"],
    )

    return df


# =========================================================
# 5. CHART
# =========================================================


def plot_market_stress(df: pd.DataFrame) -> None:
    last = df.iloc[-1]

    # Ambang low/high stress berbasis persentil data aktual (transparan & adaptif)
    EXTREMA_WIN, FWD = 10, 15
    comp = df["market_stress_composite"].to_numpy()
    ihsg_arr = df["ihsg"].to_numpy()
    LOW_TH = float(np.nanpercentile(comp, 15))
    HIGH_TH = float(np.nanpercentile(comp, 85))
    n = len(df)

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

    ax.plot(
        df["date"],
        df["market_stress_composite"],
        color="black",
        linewidth=2.4,
        label="Market Stress Composite",
    )
    ax.plot(
        df["date"],
        df["macro_stress"],
        color="#4E7D52",
        linewidth=2.2,
        linestyle="--",
        label="Macro Stress",
    )
    ax.plot(
        df["date"],
        df["flow_internal_stress"],
        color="#8A5A5A",
        linewidth=2.1,
        linestyle="-.",
        label="Flow & Internal Stress",
    )

    ax.axhline(50, color="#777777", linewidth=1, linestyle=":", alpha=0.7)

    last_date = last["date"]
    label_offset = pd.Timedelta(days=5)
    for column, label, color in [
        ("market_stress_composite", "Composite", "black"),
        ("macro_stress", "Macro", "#4E7D52"),
        ("flow_internal_stress", "Flow/Internal", "#8A5A5A"),
    ]:
        value = float(last[column])
        ax.scatter([last_date], [value], color=color, s=40, zorder=5)
        ax.text(
            last_date + label_offset,
            value,
            f"{label} {value:.1f}",
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
        f"Latest regime: {last['stress_regime']} | Composite stress: "
        f"{last['market_stress_composite']:.1f}  |  \u25bc Low (p15<{LOW_TH:.0f})  "
        f"\u25b2 High (p85>{HIGH_TH:.0f})  \u00b7 return IHSG {FWD} hari bursa",
        ha="center",
        fontsize=11,
        color="#555555",
    )

    ax.set_ylabel("Stress Score", fontsize=11, fontweight="bold", labelpad=12)
    ax.set_ylim(0, 100)
    ax.grid(axis="y", linestyle=":", linewidth=0.8, alpha=0.32)
    ax.grid(axis="x", visible=False)

    ax.spines["left"].set_linewidth(1.5)
    ax.spines["bottom"].set_linewidth(1.5)
    ax.spines["top"].set_visible(False)
    ax.spines["right"].set_visible(False)
    ax.tick_params(axis="both", width=1.3, length=6, color="black")

    # ===== DETEKSI TITIK LOW/HIGH STRESS LOKAL =====
    # low-stress = trough lokal < p15, high-stress = peak lokal > p85, jendela +/-10 hari bursa.
    low_pts, high_pts = [], []
    for i in range(EXTREMA_WIN, n - EXTREMA_WIN):
        win = comp[i - EXTREMA_WIN:i + EXTREMA_WIN + 1]
        if comp[i] == win.min() and comp[i] < LOW_TH:
            low_pts.append(i)
        elif comp[i] == win.max() and comp[i] > HIGH_TH:
            high_pts.append(i)

    def dedup(idxs, gap=EXTREMA_WIN):
        out = []
        for i in idxs:
            if not out or i - out[-1] > gap:
                out.append(i)
        return out
    low_pts, high_pts = dedup(low_pts), dedup(high_pts)

    def fwd_ret(i):
        j = min(i + FWD, n - 1)
        if pd.isna(ihsg_arr[i]) or pd.isna(ihsg_arr[j]) or ihsg_arr[i] == 0:
            return None
        return (ihsg_arr[j] / ihsg_arr[i] - 1) * 100

    # marker di panel stress
    for i in low_pts:
        ax.scatter(df["date"].iloc[i], comp[i], marker="v", s=80,
                   color="#B0413E", zorder=6, edgecolor="white", linewidth=0.6)
        ax.text(df["date"].iloc[i], comp[i] - 5,
                f"Low {comp[i]:.0f}", ha="center", va="top", fontsize=8, color="#B0413E")
    for i in high_pts:
        ax.scatter(df["date"].iloc[i], comp[i], marker="^", s=80,
                   color="#4E7D52", zorder=6, edgecolor="white", linewidth=0.6)
        ax.text(df["date"].iloc[i], comp[i] + 4,
                f"High {comp[i]:.0f}", ha="center", va="bottom", fontsize=8, color="#4E7D52")

    # ===== PANEL BAWAH: IHSG =====
    ax2.plot(df["date"], df["ihsg"], color="#2C5F8A", linewidth=2.0, label="IHSG")
    ax2.fill_between(df["date"], df["ihsg"], df["ihsg"].min(),
                     color="#2C5F8A", alpha=0.07)
    # penanda + anotasi return ke depan pada IHSG
    for i in low_pts:
        r = fwd_ret(i)
        ax2.scatter(df["date"].iloc[i], ihsg_arr[i], marker="v", s=70,
                    color="#B0413E", zorder=6, edgecolor="white", linewidth=0.6)
        if r is not None:
            ax2.annotate(f"{r:+.1f}% ({FWD}d)",
                         (df["date"].iloc[i], ihsg_arr[i]),
                         textcoords="offset points", xytext=(0, 12),
                         ha="center", fontsize=8,
                         color=("#4E7D52" if r >= 0 else "#B0413E"), fontweight="bold")
    for i in high_pts:
        r = fwd_ret(i)
        ax2.scatter(df["date"].iloc[i], ihsg_arr[i], marker="^", s=70,
                    color="#4E7D52", zorder=6, edgecolor="white", linewidth=0.6)
        if r is not None:
            ax2.annotate(f"{r:+.1f}% ({FWD}d)",
                         (df["date"].iloc[i], ihsg_arr[i]),
                         textcoords="offset points", xytext=(0, -16),
                         ha="center", fontsize=8,
                         color=("#4E7D52" if r >= 0 else "#B0413E"), fontweight="bold")
    # titik IHSG terakhir
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
    ax2.set_xlim(df["date"].min(), last_date + pd.Timedelta(days=42))

    ax.legend(loc="upper left", frameon=False, ncol=3, bbox_to_anchor=(0, 0.985))
    fig.text(0.01, 0.018, CHART_SOURCE, fontsize=10, color="#666666")

    plt.tight_layout(rect=[0, 0.055, 1, 0.885])
    plt.savefig(OUTPUT_CHART, dpi=180, bbox_inches="tight", facecolor="white")
    plt.show()


# =========================================================
# 6. MAIN
# =========================================================


def main() -> None:
    df = calculate_market_stress()
    df.to_csv(OUTPUT_CSV, index=False)
    plot_market_stress(df)

    last = df.iloc[-1]
    print("DONE")
    print(f"Date                 : {last['date'].date()}")
    print(f"Market Stress        : {last['market_stress_composite']:.2f}")
    print(f"Macro Stress         : {last['macro_stress']:.2f}")
    print(f"Flow/Internal Stress : {last['flow_internal_stress']:.2f}")
    print(f"Regime               : {last['stress_regime']}")
    print(f"Chart saved          : {OUTPUT_CHART}")
    print(f"CSV saved            : {OUTPUT_CSV}")


if __name__ == "__main__":
    main()
