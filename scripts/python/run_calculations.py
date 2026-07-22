from pathlib import Path
import os
import sys
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

BASE_DIR = Path(__file__).resolve().parent.parent.parent
DOWNLOADS = Path("/home/hasan/Downloads")

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
    if text.endswith("B") or text.endswith("b"):
        mult = 1e9
        text = text[:-1]
    elif text.endswith("T") or text.endswith("t"):
        mult = 1e12
        text = text[:-1]
    num = parse_id_number(text)
    return float(num) * mult if pd.notna(num) else np.nan

def find_flow_file():
    candidates = [
        DOWNLOADS / "Data Foreign Flow - Data.csv",
        BASE_DIR / "storage/app/foreign_flow/Data Foreign Flow - Data.csv",
        BASE_DIR / "Data Foreign Flow - Data.csv",
    ]
    for cand in candidates:
        if cand.exists():
            return cand

    search_dirs = [
        BASE_DIR / "storage/app/foreign_flow",
        BASE_DIR / "storage/app",
        BASE_DIR,
        DOWNLOADS
    ]
    for d in search_dirs:
        if not d.exists(): continue
        for f in d.glob("*"):
            if f.is_file() and f.suffix.lower() in [".csv", ".xlsx", ".xls"]:
                fname = f.name.lower()
                if "foreign" in fname or "flow" in fname:
                    return f
    return None

def load_flow_data():
    flow_file = find_flow_file()
    if not flow_file:
        return None

    if flow_file.suffix.lower() in ['.xlsx', '.xls']:
        try:
            df = pd.read_excel(flow_file, header=1)
        except Exception:
            df = pd.read_excel(flow_file, header=0)
    else:
        raw = pd.read_csv(flow_file, header=None)
        header_idx = 0
        for idx in range(min(20, len(raw))):
            row_vals = [str(x).strip() for x in raw.iloc[idx].tolist() if pd.notna(x)]
            if any("tanggal" in v.lower() or "foreign" in v.lower() for v in row_vals):
                header_idx = idx
                break
        df = pd.read_csv(flow_file, header=header_idx)

    df.columns = [str(c).strip() for c in df.columns]
    
    date_cols = [c for c in df.columns if "tanggal" in c.lower() or "date" in c.lower()]
    flow_cols = [c for c in df.columns if "foreign net" in c.lower() or "foreign" in c.lower()]
    val_cols = [c for c in df.columns if "market value" in c.lower() or "value" in c.lower()]

    if not date_cols or not flow_cols:
        return None

    date_col = date_cols[0]
    flow_col = flow_cols[0]
    val_col = val_cols[0] if val_cols else flow_cols[0]
    
    df["date"] = pd.to_datetime(df[date_col], format="%d/%m/%Y", errors="coerce")
    if df["date"].isna().all():
        df["date"] = pd.to_datetime(df[date_col], errors="coerce")
        
    df["foreign_net_1d"] = df[flow_col].apply(parse_unit_number)
    df["market_value_1d"] = df[val_col].apply(parse_unit_number).abs()
    
    res = df.dropna(subset=["date", "foreign_net_1d"]).sort_values("date").reset_index(drop=True)
    return res

def load_ihsg_data():
    flow_file = find_flow_file()
    ihsg_file = DOWNLOADS / "Jakarta Stock Exchange Composite Index Historical Data.csv"
    if ihsg_file.exists():
        df = pd.read_csv(ihsg_file)
        df.columns = [str(c).strip().replace('"', '') for c in df.columns]
        date_cols = [c for c in df.columns if "date" in c.lower() or "tanggal" in c.lower()]
        price_cols = [c for c in df.columns if "price" in c.lower() or "harga" in c.lower()]
        if date_cols and price_cols:
            df["date"] = pd.to_datetime(df[date_cols[0]], errors="coerce")
            df["ihsg"] = df[price_cols[0]].apply(parse_id_number)
            res = df.dropna(subset=["date", "ihsg"]).sort_values("date").reset_index(drop=True)
            if len(res) > 50:
                return res

    if not flow_file:
        return None

    if flow_file.suffix.lower() in ['.xlsx', '.xls']:
        try:
            flow_raw = pd.read_excel(flow_file, header=1)
        except Exception:
            flow_raw = pd.read_excel(flow_file, header=0)
    else:
        flow_raw = pd.read_csv(flow_file, header=1)

    flow_raw.columns = [str(c).strip() for c in flow_raw.columns]
    date_cols = [c for c in flow_raw.columns if "tanggal" in c.lower() or "date" in c.lower()]
    price_cols = [c for c in flow_raw.columns if "price" in c.lower() or "harga" in c.lower() or "close" in c.lower()]
    
    if not date_cols or not price_cols:
        return None

    flow_raw["date"] = pd.to_datetime(flow_raw[date_cols[0]], format="%d/%m/%Y", errors="coerce")
    if flow_raw["date"].isna().all():
        flow_raw["date"] = pd.to_datetime(flow_raw[date_cols[0]], errors="coerce")
    flow_raw["ihsg"] = flow_raw[price_cols[0]].apply(parse_id_number)
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
    
    if flow is None or ihsg is None or len(flow) == 0 or len(ihsg) == 0:
        if MOMENTUM_OUTPUT.exists() and STRESS_OUTPUT.exists():
            print(f"Using existing outputs: {MOMENTUM_OUTPUT.name}, {STRESS_OUTPUT.name}")
            return
        else:
            print("Warning: Input data files not found and output files do not exist.")
            return

    # Merge flow and ihsg
    df = pd.merge(flow, ihsg, on="date", how="inner").sort_values("date").reset_index(drop=True)
    
    if len(df) == 0:
        print("Warning: Merged dataset is empty.")
        return

    # 1. Flow Momentum v2 Calculations
    df["foreign_net_5d"] = df["foreign_net_1d"].rolling(5, min_periods=1).sum()
    df["foreign_net_20d"] = df["foreign_net_1d"].rolling(20, min_periods=1).sum()
    df["total_market_value_5d"] = df["market_value_1d"].rolling(5, min_periods=1).sum()
    df["total_market_value_20d"] = df["market_value_1d"].rolling(20, min_periods=1).sum()
    df["avg_market_value_5d"] = df["market_value_1d"].rolling(5, min_periods=1).mean()
    df["avg_market_value_20d"] = df["market_value_1d"].rolling(20, min_periods=1).mean()

    df["foreign_intensity_5d"] = df["foreign_net_5d"] / (df["total_market_value_5d"].replace(0, 1e-6))
    df["foreign_intensity_20d"] = df["foreign_net_20d"] / (df["total_market_value_20d"].replace(0, 1e-6))
    df["positive_foreign_flow_days_5d"] = (df["foreign_net_1d"] > 0).rolling(5, min_periods=1).sum()
    df["liquidity_ratio_5d_vs_20d_avg"] = df["avg_market_value_5d"] / (df["avg_market_value_20d"].replace(0, 1e-6))

    consistency_map = {0: 10, 1: 25, 2: 45, 3: 65, 4: 85, 5: 100}
    df["flow_consistency_score"] = df["positive_foreign_flow_days_5d"].round().map(lambda x: consistency_map.get(int(x), 50))

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
    df["flow_velocity"] = df["flow_5d_bn"].diff(5).fillna(0) / 5
    df["flow_acceleration"] = df["flow_velocity"].diff(5).fillna(0) / 5
    df["ihsg_return_5d"] = df["ihsg"].pct_change(5).fillna(0)
    df["ihsg_return_20d"] = df["ihsg"].pct_change(20).fillna(0)

    df["velocity_z"] = rolling_z(df["flow_velocity"])
    df["acceleration_z"] = rolling_z(df["flow_acceleration"])
    df["intensity_5d_z"] = rolling_z(df["foreign_intensity_5d"])
    df["intensity_20d_z"] = rolling_z(df["foreign_intensity_20d"])
    df["liquidity_z"] = rolling_z(df["liquidity_ratio_5d_vs_20d_avg"])
    df["price_5d_z"] = rolling_z(df["ihsg_return_5d"])
    df["price_20d_z"] = rolling_z(df["ihsg_return_20d"])

    df["negative_flow_streak"] = consecutive_negative_days(df["foreign_net_1d"])
    df["outflow_20d_z"] = -rolling_z(df["flow_20d_bn"], window=120, min_periods=1)
    df["outflow_5d_z"] = -rolling_z(df["flow_5d_bn"])
    df["streak_score"] = (df["negative_flow_streak"] / 15).clip(0, 1)
    acceleration_recovery = ((df["acceleration_z"] + 2) / 4).clip(0, 1)

    df["flow_exhaustion_score"] = (
        100
        * (
            0.45 * (df["outflow_20d_z"] / 3).clip(0, 1).fillna(0)
            + 0.25 * (df["outflow_5d_z"] / 3).clip(0, 1).fillna(0)
            + 0.15 * df["streak_score"].fillna(0)
            + 0.15 * (1 - acceleration_recovery).fillna(0)
        )
    ).clip(0, 100)

    raw_reversal_z = (
        0.35 * df["intensity_5d_z"]
        + 0.25 * df["velocity_z"]
        + 0.20 * df["acceleration_z"]
        + 0.10 * df["liquidity_z"]
        + 0.10 * df["price_5d_z"]
    )
    df["reversal_probability"] = sigmoid_score(raw_reversal_z, slope=1.15)
    df["flow_momentum_v2_score"] = df["flow_score"]

    # Export Momentum Output
    mom_out = df[["date", "flow_momentum_v2_score", "flow_exhaustion_score", "reversal_probability"]].copy()
    mom_out["date"] = mom_out["date"].dt.strftime("%Y-%m-%d")
    mom_out.to_csv(MOMENTUM_OUTPUT, index=False)
    print(f"Exported {len(mom_out)} rows to {MOMENTUM_OUTPUT}")

    # 2. Market Stress Engine Calculations
    macro_stress = sigmoid_score(-df["price_20d_z"], slope=1.0)
    flow_internal_stress = sigmoid_score(-df["velocity_z"], slope=1.0)

    df["macro_stress"] = macro_stress
    df["flow_internal_stress"] = flow_internal_stress
    df["market_stress_composite"] = (0.5 * macro_stress + 0.5 * flow_internal_stress).round(1)

    stress_out = df[["date", "market_stress_composite", "macro_stress", "flow_internal_stress"]].copy()
    stress_out["date"] = stress_out["date"].dt.strftime("%Y-%m-%d")
    stress_out.to_csv(STRESS_OUTPUT, index=False)
    print(f"Exported {len(stress_out)} rows to {STRESS_OUTPUT}")

if __name__ == "__main__":
    compute_all()
