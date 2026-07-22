from pathlib import Path
import pandas as pd
import numpy as np

def percentileofscore(arr_like, score, kind='weak'):
    arr = np.asarray(arr_like)
    arr = arr[~np.isnan(arr)]
    if len(arr) == 0:
        return 50.0
    if kind == 'weak':
        return (np.sum(arr <= score) / float(len(arr))) * 100.0
    return (np.sum(arr < score) / float(len(arr))) * 100.0


# Paths
BASE_DIR = Path(__file__).resolve().parent.parent.parent
DOWNLOADS = Path("/home/hasan/Downloads")

FLOW_CSV = DOWNLOADS / "Data Foreign Flow - Data.csv"
flow_candidates = [
    DOWNLOADS / "Data Foreign Flow - Data.csv",
    BASE_DIR / "storage/app/foreign_flow/Data Foreign Flow - Data.csv",
    BASE_DIR / "Data Foreign Flow - Data.csv"
]
for cand in flow_candidates:
    if cand.exists():
        FLOW_CSV = cand
        break

IHSG_CSV = DOWNLOADS / "Jakarta Stock Exchange Composite Index Historical Data.csv"

MOMENTUM_OUTPUT = BASE_DIR / "avenir_foreign_flow_momentum_v2_output.csv"
STRESS_OUTPUT = BASE_DIR / "avenir_market_stress_engine_output.csv"

def parse_id_number(val):
    if pd.isna(val) or val is None:
        return np.nan
    text = str(val).strip().replace('"', '').replace(" ", "")
    if text in {"", "-", "nan", "NaN"}:
        return np.nan
    if "," in text and "." in text and text.rfind(",") > text.rfind("."):
        text = text.replace(".", "").replace(",", ".")
    elif "," in text and "." in text and text.rfind(".") > text.rfind(","):
        text = text.replace(",", "")
    elif "," in text and "." not in text:
        text = text.replace(",", ".")
    return pd.to_numeric(text, errors="coerce")

def parse_unit_number(val):
    if pd.isna(val) or val is None:
        return np.nan
    text = str(val).strip().replace('"', '').replace(" ", "")
    mult = 1.0
    if text.endswith("B"):
        mult = 1e9
        text = text[:-1]
    elif text.endswith("T"):
        mult = 1e12
        text = text[:-1]
    num = parse_id_number(text)
    return float(num) * mult if pd.notna(num) else np.nan

def load_flow_data():
    raw = pd.read_csv(FLOW_CSV, header=None)
    header_idx = 0
    for idx in range(min(20, len(raw))):
        row_vals = [str(x).strip() for x in raw.iloc[idx].tolist() if pd.notna(x)]
        if "Tanggal" in row_vals or "Foreign Net Flow" in row_vals:
            header_idx = idx
            break
            
    df = pd.read_csv(FLOW_CSV, header=header_idx)
    df.columns = [str(c).strip() for c in df.columns]
    
    date_col = [c for c in df.columns if "tanggal" in c.lower() or "date" in c.lower()][0]
    flow_col = [c for c in df.columns if "foreign net flow" in c.lower() or "foreign" in c.lower()][0]
    val_col = [c for c in df.columns if "market value" in c.lower() or "value" in c.lower()][0]
    
    df["date"] = pd.to_datetime(df[date_col], format="%d/%m/%Y", errors="coerce")
    if df["date"].isna().all():
        df["date"] = pd.to_datetime(df[date_col], errors="coerce")
        
    df["foreign_net_1d"] = df[flow_col].apply(parse_unit_number)
    df["market_value_1d"] = df[val_col].apply(parse_unit_number).abs()
    
    res = df.dropna(subset=["date", "foreign_net_1d", "market_value_1d"]).sort_values("date").reset_index(drop=True)
    return res


def load_ihsg_data():
    if IHSG_CSV.exists():
        df = pd.read_csv(IHSG_CSV)
        df.columns = [str(c).strip().replace('"', '') for c in df.columns]
        date_col = [c for c in df.columns if "date" in c.lower() or "tanggal" in c.lower()][0]
        price_col = [c for c in df.columns if "price" in c.lower() or "harga" in c.lower()][0]
        df["date"] = pd.to_datetime(df[date_col], errors="coerce")
        df["ihsg"] = df[price_col].apply(parse_id_number)
        res = df.dropna(subset=["date", "ihsg"]).sort_values("date").reset_index(drop=True)
        if len(res) > 50:
            return res

    # Fallback to flow CSV price column which has 355 daily rows from Jan 2025
    flow_raw = pd.read_csv(FLOW_CSV, header=1)
    flow_raw.columns = [str(c).strip() for c in flow_raw.columns]
    date_c = [c for c in flow_raw.columns if "tanggal" in c.lower() or "date" in c.lower()][0]
    price_c = [c for c in flow_raw.columns if "price" in c.lower() or "harga" in c.lower()][0]
    
    flow_raw["date"] = pd.to_datetime(flow_raw[date_c], format="%d/%m/%Y", errors="coerce")
    if flow_raw["date"].isna().all():
        flow_raw["date"] = pd.to_datetime(flow_raw[date_c], errors="coerce")
    flow_raw["ihsg"] = flow_raw[price_c].apply(parse_id_number)
    return flow_raw.dropna(subset=["date", "ihsg"]).sort_values("date").reset_index(drop=True)


def rolling_z(series, window=60, min_periods=1):
    mean = series.rolling(window, min_periods=min_periods).mean()
    std = series.rolling(window, min_periods=min_periods).std(ddof=1).replace(0, 1e-6)
    return ((series - mean) / std).replace([np.inf, -np.inf], np.nan).fillna(0)

def consecutive_negative_days(series):
    output = []
    count = 0
    for value in series:
        if pd.notna(value) and value < 0:
            count += 1
        else:
            count = 0
        output.append(float(count))
    return pd.Series(output, index=series.index)

def sigmoid_score(z, slope=1.15):
    clipped = z.clip(-5, 5)
    return 100 / (1 + np.exp(-slope * clipped))

def compute_all():
    flow = load_flow_data()
    ihsg = load_ihsg_data()
    
    # Merge flow and ihsg
    df = pd.merge(flow, ihsg, on="date", how="inner").sort_values("date").reset_index(drop=True)
    
    # 1. Flow Momentum v2 Calculations
    df["foreign_net_5d"] = df["foreign_net_1d"].rolling(5).sum()
    df["foreign_net_20d"] = df["foreign_net_1d"].rolling(20).sum()
    df["total_market_value_5d"] = df["market_value_1d"].rolling(5).sum()
    df["total_market_value_20d"] = df["market_value_1d"].rolling(20).sum()
    df["avg_market_value_5d"] = df["market_value_1d"].rolling(5).mean()
    df["avg_market_value_20d"] = df["market_value_1d"].rolling(20).mean()

    df["foreign_intensity_5d"] = df["foreign_net_5d"] / df["total_market_value_5d"]
    df["foreign_intensity_20d"] = df["foreign_net_20d"] / df["total_market_value_20d"]
    df["positive_foreign_flow_days_5d"] = (df["foreign_net_1d"] > 0).rolling(5).sum()
    df["liquidity_ratio_5d_vs_20d_avg"] = df["avg_market_value_5d"] / df["avg_market_value_20d"]

    consistency_map = {0: 10, 1: 25, 2: 45, 3: 65, 4: 85, 5: 100}
    df["flow_consistency_score"] = df["positive_foreign_flow_days_5d"].round().map(consistency_map)

    all_5d = df["foreign_intensity_5d"].dropna()
    all_20d = df["foreign_intensity_20d"].dropna()

    df["p5"] = df["foreign_intensity_5d"].apply(
        lambda x: np.nan if pd.isna(x) else percentileofscore(all_5d, x, kind="weak")
    )
    df["p20"] = df["foreign_intensity_20d"].apply(
        lambda x: np.nan if pd.isna(x) else percentileofscore(all_20d, x, kind="weak")
    )

    df["foreign_flow_trend_score"] = (-11.30216285 + 0.61716879 * df["p5"] + 0.28582957 * df["p20"]).clip(5, 100)
    df["liquidity_confirmation_score"] = (79.0470822 * df["liquidity_ratio_5d_vs_20d_avg"] - 10.096074).clip(5, 100)
    df["flow_score"] = (
        0.50 * df["foreign_flow_trend_score"]
        + 0.20 * df["flow_consistency_score"]
        + 0.30 * df["liquidity_confirmation_score"]
    ).round()

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

    composite_raw = (
        0.25 * df["velocity_z"].fillna(0)
        + 0.20 * df["acceleration_z"].fillna(0)
        + 0.20 * df["intensity_20d_z"].fillna(0)
        + 0.15 * ((df["liquidity_confirmation_score"] - 50) / 25).clip(-2, 2).fillna(0)
        + 0.10 * ((df["flow_consistency_score"] - 50) / 25).clip(-2, 2).fillna(0)
        + 0.10 * df["price_5d_z"].fillna(0)
    )

    df["flow_momentum_v2_score"] = (50 + 15 * composite_raw).clip(0, 100)
    
    # Reversal probability approximation
    df["reversal_probability"] = (
        0.4 * df["flow_momentum_v2_score"] + 0.6 * (100 - df["flow_exhaustion_score"])
    ).clip(0, 100)

    # 2. Market Stress Engine Calculations
    df["ihsg_return_1d"] = df["ihsg"].pct_change()
    df["realized_vol_20d"] = df["ihsg_return_1d"].rolling(20).std(ddof=1) * np.sqrt(252)
    df["rolling_peak_60d"] = df["ihsg"].rolling(60, min_periods=20).max()
    df["drawdown_60d"] = df["ihsg"] / df["rolling_peak_60d"] - 1
    df["volatility_z"] = rolling_z(df["realized_vol_20d"])
    df["drawdown_stress_z"] = rolling_z(-df["drawdown_60d"])

    df["flow_score_stress"] = (50 - df["flow_score"]) / 15
    df["flow_stress_raw"] = (
        0.30 * (-df["intensity_5d_z"]).fillna(0)
        + 0.25 * (-df["intensity_20d_z"]).fillna(0)
        + 0.30 * df["flow_score_stress"].fillna(0)
        + 0.15 * (-df["liquidity_z"]).fillna(0)
    )
    df["flow_stress"] = sigmoid_score(df["flow_stress_raw"])

    df["internal_stress_raw"] = (
        0.55 * df["drawdown_stress_z"].fillna(0)
        + 0.45 * df["volatility_z"].fillna(0)
    )
    df["internal_stress"] = sigmoid_score(df["internal_stress_raw"])

    # Macro stress approximation (from price/flow dynamics if USD/Yield historical missing)
    df["macro_stress_raw"] = 0.5 * df["drawdown_stress_z"].fillna(0) + 0.5 * (-df["intensity_5d_z"]).fillna(0)
    df["macro_stress"] = sigmoid_score(df["macro_stress_raw"])

    df["flow_internal_stress"] = 0.60 * df["flow_stress"] + 0.40 * df["internal_stress"]
    df["market_stress_composite"] = (
        0.40 * df["macro_stress"]
        + 0.35 * df["flow_stress"]
        + 0.25 * df["internal_stress"]
    )

    for col in ["macro_stress", "flow_internal_stress", "market_stress_composite"]:
        df[col] = df[col].ewm(span=3, adjust=False).mean()

    # Format date string for export
    df["date_str"] = df["date"].dt.strftime("%Y-%m-%d")

    # Export Momentum CSV to root project directory where artisan command looks
    root_dir = Path("/home/hasan/Documents/hasanarofid/avenir/default-avenir")
    mom_out = root_dir / MOMENTUM_OUTPUT
    stress_out = root_dir / STRESS_OUTPUT

    mom_df = df[["date_str", "flow_momentum_v2_score", "flow_exhaustion_score", "reversal_probability"]].copy()
    mom_df.columns = ["date", "flow_momentum_v2_score", "flow_exhaustion_score", "reversal_probability"]
    mom_df.to_csv(mom_out, index=False)
    print(f"Exported {len(mom_df)} rows to {mom_out}")

    stress_df = df[["date_str", "market_stress_composite", "macro_stress", "flow_internal_stress"]].copy()
    stress_df.columns = ["date", "market_stress_composite", "macro_stress", "flow_internal_stress"]
    stress_df.to_csv(stress_out, index=False)
    print(f"Exported {len(stress_df)} rows to {stress_out}")

if __name__ == "__main__":
    compute_all()
