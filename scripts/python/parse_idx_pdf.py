import sys
import re
import json
import subprocess

def parse_idx_pdf(pdf_path):
    try:
        # Extract without layout to get text linearly
        result_no_layout = subprocess.run(['pdftotext', pdf_path, '-'], stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
        # Extract with layout to get structured tables
        result_layout = subprocess.run(['pdftotext', '-layout', pdf_path, '-'], stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
        
        if result_no_layout.returncode != 0:
            return {"error": "Failed to read PDF"}
            
        text = result_no_layout.stdout
        layout_text = result_layout.stdout
        
        data = {}
        
        # 1. Date
        date_match = re.search(r'([A-Za-z]+, \d{2} [A-Za-z]+ \d{4})', text)
        if date_match:
            data['date'] = date_match.group(1)
            
        # 2. IHSG Close & Change
        ihsg_match = re.search(r'IDX Composite Index \(IHSG\).*?([\d,]+\.\d+)\s+([+-][\d,]+\.\d+)\s*\(([+-]?[\d,]+\.\d+)%\)', layout_text, re.DOTALL)
        if ihsg_match:
            data['ihsg_close'] = float(ihsg_match.group(1).replace(',', ''))
            data['ihsg_change_abs'] = float(ihsg_match.group(2).replace(',', ''))
            data['ihsg_change_pct'] = float(ihsg_match.group(3).replace(',', ''))
            
        # 3. Value Traded & Market Cap
        value_match = re.search(r'([0-9]{1,3}(?:,[0-9]{3})+)\s+([0-9]{1,3}(?:,[0-9]{3})*|\d+)\s+.*?\(billion IDR\)', layout_text, re.DOTALL)
        if value_match:
            data['value_traded_bn_idr'] = float(value_match.group(1).replace(',', ''))
            
        market_cap_match = re.search(r'IHSG Market Cap\^.*?\n\s+([0-9]{1,3}(?:,[0-9]{3})*)', layout_text, re.DOTALL)
        if market_cap_match:
            data['total_market_cap_tn_idr'] = float(market_cap_match.group(1).replace(',', ''))

        # 4. Net Foreign
        foreign_match = re.search(r'NET FOREIGN.*?(-?[\d,]+\.\d+)\s+(-?[\d,]+\.\d+).*?Today\s+YTD', layout_text, re.DOTALL)
        if foreign_match:
            data['foreign_net_today'] = float(foreign_match.group(1).replace(',', ''))
                 
        # 5. Market Breadth (Advancers / Decliners)
        breadth_match = re.search(r'By Number\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)', layout_text)
        if breadth_match:
            # >2%, 0-2%, stable, 0-(-2%), <-2%
            data['advancers'] = int(breadth_match.group(1)) + int(breadth_match.group(2))
            data['stable'] = int(breadth_match.group(3))
            data['decliners'] = int(breadth_match.group(4)) + int(breadth_match.group(5))
            
        # 6. Sector Indices
        sectors = ['Energy', 'Basic Materials', 'Industrials', 'Consumer Non-Cyclicals', 'Consumer Cyclicals', 'Healthcare', 'Financials', 'Properties & Real Estate', 'Technology', 'Infrastructures', 'Transportation & Logistic']
        data['sectors'] = {}
        for sector in sectors:
            sec_match = re.search(rf'\[[A-Z]\] {re.escape(sector)}\s+\d+\s+[\d,]+\.\d+\s+([+-]?\d+\.\d+)%\s+[+-]?\d+\.\d+%', text)
            if sec_match:
                data['sectors'][sector] = float(sec_match.group(1))

        return data

    except Exception as e:
        import traceback; traceback.print_exc(); return {"error": str(e)}

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No PDF path provided"}))
        sys.exit(1)
        
    pdf_path = sys.argv[1]
    result = parse_idx_pdf(pdf_path)
    print(json.dumps(result))
