# Avenir Market Regime Engine

Folder ini berisi script Python modular agar hitungan tidak terpotong:

- `common.py` — helper parsing, scoring, save JSON.
- `price_trend.py` — Price Trend Score dari historical IHSG.
- `market_breadth.py` — Market Breadth Score dari Ringkasan Saham, sudah pakai value-based breadth.
- `flow.py` — Flow & Liquidity Score dari foreign flow + market value.
- `volatility.py` — Volatility / Market Stability Score dari IHSG OHLC + market value.
- `sector_rotation.py` — Sector Rotation Score dari Ringkasan Saham + sector master.
- `regime_score.py` — gabungkan komponen menjadi Final Regime Score.
- `period_conclusion.py` — membuat period conclusion otomatis dari history regime score.

## Formula final

```text
Final Regime Score =
30% Price Trend
25% Market Breadth
20% Flow & Liquidity
15% Sector Rotation
10% Volatility / Market Stability
```

## Catatan data

- `foreign_net` boleh negatif.
- `market_value` / `nilai transaksi` tidak boleh negatif. Kalau negatif, engine otomatis mengubah menjadi absolut.
- `volatility_percentile` bukan dari IDX Daily Statistics. Itu optional dan dihitung dari historical realized volatility IHSG. Kalau kosong, komponen itu diskip dan bobot dinormalisasi otomatis.

## Contoh run

```bash
python price_trend.py --input historical_ihsg.csv
python market_breadth.py --input "Ringkasan Saham-20260709.xlsx"
python flow.py --input foreign_flow.csv
python volatility.py --input ihsg_ohlc_value.csv
python sector_rotation.py --stocks "Ringkasan Saham-20260709.xlsx" --sector-master sector_master.xlsx
python regime_score.py --components example_component_scores.csv
python period_conclusion.py --history regime_history.csv
```
