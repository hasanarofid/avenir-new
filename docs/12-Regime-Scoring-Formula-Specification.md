# 12 - Regime Scoring Formula Specification

Dokumen ini mencatat **seluruh rumus, bobot, dan logika piecewise** yang digunakan dalam perhitungan Regime Score. Formula ini merefleksikan versi original (Piecewise & Stock-based) yang digunakan dalam perhitungan manual client.

Tujuan dokumen ini adalah sebagai *Single Source of Truth* agar di masa depan logika perhitungan Regime Score tetap konsisten dan tidak tertimpa oleh versi lain (misalnya versi Binary / Index-based).

---

## 1. Price Trend (Momentum) - Bobot: 30%
Script: `price_trend.py`

**Komponen & Bobot:**
- `close_vs_ma20_score` (30%): Posisi harga Close IHSG relatif terhadap MA20.
- `ma20_vs_ma60_score` (25%): Posisi MA20 relatif terhadap MA60.
- `return_5d_score` (20%): Return IHSG dalam 5 hari perdagangan.
- `return_20d_score` (15%): Return IHSG dalam 20 hari perdagangan.
- `drawdown_20d_score` (10%): Jarak antara Close saat ini dengan High tertinggi dalam 20 hari.

**Mapping Piecewise (Interpolasi):**
- **s_close:** (-15%: 5), (-10%: 12), (-7%: 22), (-3%: 38), (0%: 60), (2%: 75), (5%: 90), (8%: 100)
- **s_ma:** (-15%: 5), (-10%: 15), (-7%: 25), (-3%: 40), (0%: 60), (2%: 75), (5%: 90), (8%: 100)
- **s_ret5:** (-12%: 5), (-8%: 12), (-5%: 25), (-2%: 40), (0%: 52), (2%: 65), (5%: 85), (8%: 100)
- **s_ret20:** (-20%: 5), (-12%: 15), (-8%: 25), (-5%: 35), (0%: 52), (3%: 65), (8%: 85), (12%: 100)
- **s_dd:** (-25%: 5), (-18%: 15), (-12%: 30), (-8%: 45), (-5%: 60), (-3%: 75), (-1%: 90), (0%: 100)

---

## 2. Market Breadth - Bobot: 25%
Script: `market_breadth.py` (Sumber data: Excel Ringkasan Saham)

**Komponen & Bobot:**
- `ad_score` (30%): $Advancers \div (Advancers + Decliners)$
- `strong_movers_score` (20%): (Saham Naik >2%) / ((Saham Naik >2%) + (Saham Turun >2%))
- `mcap_breadth_score` (20%): $MarketCap_{Up} \div (MarketCap_{Up} + MarketCap_{Down})$
- `value_breadth_score` (20%): $TradingValue_{Up} \div (TradingValue_{Up} + TradingValue_{Down})$
- `active_participation_score` (10%): $(Advancers + Decliners) \div Total Saham$

*Catatan: Semua nilai di-scale ke persentase 0-100.*

---

## 3. Flow (Foreign) - Bobot: 20%
Script: `flow.py` (Sumber data: Data Foreign Flow/Net)

**Komponen & Bobot:**
- `foreign_flow_trend_score` (40%): Agregat berbobot dari intensitas foreign flow 1 hari (20%), 5 hari (50%), dan 20 hari (30%).
- `flow_consistency_score` (25%): Jumlah hari dengan foreign net flow positif dalam 5 hari terakhir.
- `liquidity_confirmation_score` (25%): Rasio perbandingan likuiditas 5 hari terakhir terhadap rata-rata 20 hari.
- `broker_pulse_score` (10%): Nilai input langsung dari client (jika ada).

**Mapping Piecewise:**
- **s_int (Intensity):** (-5%: 5), (-3%: 15), (-1.5%: 30), (-0.5%: 42), (0%: 50), (0.5%: 60), (1.5%: 75), (3%: 90), (5%: 100)
- **s_cons (Consistency):** 5 hari: 100 | 4 hari: 85 | 3 hari: 65 | 2 hari: 45 | 1 hari: 25 | 0 hari: 10
- **s_liq (Liquidity):** (0.3: 15), (0.5: 30), (0.7: 45), (0.9: 60), (1.0: 70), (1.2: 85), (1.5: 100)

---

## 4. Sector Rotation - Bobot: 15%
Script: `sector_rotation.py` (Sumber data: Excel Ringkasan Saham & sector_master.csv)

Sektor dibagi menjadi **Risk-On** (Energy, Basic Materials, Industrials, dll) dan **Defensive** (Consumer Non-Cyclicals, Healthcare).

**Komponen & Bobot:**
- `sector_return_breadth_score` (25%): Persentase sektor yang mencetak return positif.
- `risk_on_participation_score` (20%): Persentase sektor Risk-On yang mencetak return positif.
- `sector_value_confirmation_score` (20%): Rasio Trading Value pada sektor yang positif terhadap total Trading Value.
- `sector_breadth_score` (15%): Rata-rata dari (Saham Naik / Total Saham Bergerak) di dalam masing-masing sektor.
- `leadership_breadth_score` (10%): Perhitungan HHI (Herfindahl-Hirschman Index) untuk melihat distribusi konsentrasi bobot return sektor yang positif.
- `risk_on_vs_defensive_score` (10%): Spread (selisih) rata-rata return sektor Risk-On dibandingkan sektor Defensive.

**Mapping Piecewise:**
- **s_spread:** (-3%: 10), (-2%: 20), (-1%: 35), (0%: 50), (0.5%: 65), (1%: 75), (2%: 90), (3%: 100)

---

## 5. Volatility & Liquidity - Bobot: 10%
Script: `volatility.py`

**Komponen & Bobot:**
- `volatility_regime_score` (30%): Percentile dari volatilitas harian historis dalam jendela 252 hari.
- `liquidity_quality_score` (25%): Likuiditas berbanding lurus dengan arah return IHSG (menggunakan matriks asimetris).
- `intraday_range_score` (20%): Rasio rentang harga (High - Low) terhadap Previous Close.
- `return_shock_score` (15%): Besar kejutan return 1 hari.
- `close_location_score` (10%): Seberapa dekat posisi Close dengan High pada hari tersebut ($Close-Low \div High-Low$).

**Mapping Piecewise:**
- **s_vol:** (0%: 100), (20%: 85), (40%: 65), (60%: 45), (80%: 25), (100%: 10)
- **s_range:** (0: 70), (0.5%: 90), (1%: 85), (1.5%: 75), (2%: 60), (3%: 40), (5%: 20), (8%: 10)
- **s_ret:** (-6%: 5), (-4%: 15), (-2.5%: 30), (-1.5%: 45), (-0.5%: 60), (0%: 70), (1%: 80), (2%: 90), (4%: 100)
- **s_close:** (0: 10), (0.2: 25), (0.4: 45), (0.5: 55), (0.6: 65), (0.8: 85), (1.0: 100)
- **s_liq:** 
  - Jika return $\ge$ 0: (0.3: 20), (0.5: 35), (0.7: 50), (0.9: 65), (1.0: 75), (1.2: 90), (1.5: 100)
  - Jika return $<$ 0: (0.3: 55), (0.5: 50), (0.7: 42), (0.9: 35), (1.1: 25), (1.3: 15), (1.5: 10)
