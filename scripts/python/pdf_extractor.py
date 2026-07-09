import sys
import json
import re
import subprocess
from pathlib import Path

def clamp(value, min_value=0, max_value=100):
    return max(min_value, min(value, max_value))

def calculate_flow_score(net_foreign_value):
    # Rule dari klien:
    # +500B -> 100
    # +100B -> 75
    # 0 -> 50
    # -250B -> 25
    # -500B -> 0
    if net_foreign_value >= 500: return 100
    if net_foreign_value >= 100: return 75
    if net_foreign_value >= 0: return 50
    if net_foreign_value >= -250: return 25
    return 0

def calculate_sector_score(sector_returns):
    # Rule dari klien (Poin 4):
    # % sektor positif * 100
    # (Diabaikan pembobotan market cap sementara sesuai instruksi awal jika data tidak tersedia, 
    # namun klien bilang: Hitung persentase sektor yang positif)
    
    if not sector_returns:
        return 50
        
    positive_count = sum(1 for val in sector_returns.values() if val > 0)
    total_count = len(sector_returns)
    
    score = (positive_count / total_count) * 100
    return int(round(score))

def extract_pdf_data(pdf_path):
    try:
        # Run pdftotext
        result = subprocess.run(
            ['pdftotext', '-layout', '-f', '1', '-l', '10', str(pdf_path), '-'],
            capture_output=True, text=True, check=True
        )
        text = result.stdout
    except Exception as e:
        raise Exception(f"Gagal mengekstrak PDF: {str(e)}")

    lines = text.split('\n')
    
    # 1. Extract Net Foreign (Flow)
    # Target format di PDF: NET FOREIGN FUNDAMENTAL ... 480.20 ...
    # Kita cari angka net foreign.
    net_foreign_value = 0
    
    for line in lines:
        if 'NET FOREIGN' in line.upper():
            # Contoh line: NET FOREIGN FUNDAMENTAL      480.20       -30.50
            # Ambil angka pertama setelah string text
            matches = re.findall(r'-?\d+[,.]\d+', line)
            if matches:
                # Menghapus koma ribuan jika ada, mengubah ke float
                val_str = matches[0].replace(',', '')
                net_foreign_value = float(val_str)
                break

    # 2. Extract Sector Returns
    # Target sektor: Energy, Basic Materials, Industrials, dll.
    sectors = [
        "Energy", "Basic Materials", "Industrials", "Consumer Non-Cyclicals", 
        "Consumer Cyclicals", "Healthcare", "Financials", "Properties & Real Estate",
        "Technology", "Infrastructures", "Transportation & Logistic"
    ]
    
    sector_returns = {}
    
    for i, line in enumerate(lines):
        for sector in sectors:
            if sector in line:
                # Cari pola angka return (%), contoh: 2,123.45   1.23%   -0.5%
                matches = re.findall(r'-?\d+[,.]\d+', line.replace(sector, ''))
                if len(matches) >= 2:
                    # Ambil angka kedua terakhir atau terakhir yang merupakan persentase perubahan harian
                    # Asumsi kolom perubahan harian ada di indeks ke-1 atau ke-2 dari angka yang ditemukan
                    try:
                        # Kita ambil angka index 1 (sebagai chg)
                        val_str = matches[1].replace(',', '')
                        sector_returns[sector] = float(val_str)
                    except:
                        pass

    flow_score = calculate_flow_score(net_foreign_value)
    sector_score = calculate_sector_score(sector_returns)

    return {
        "net_foreign": net_foreign_value,
        "flow_score": flow_score,
        "sector_returns": sector_returns,
        "sector_score": sector_score
    }

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Path file PDF diperlukan"}))
        sys.exit(1)

    pdf_path = sys.argv[1]
    
    try:
        result = extract_pdf_data(pdf_path)
        print(json.dumps(result))
    except Exception as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()
