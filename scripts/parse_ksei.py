import sys
import subprocess
import re
import json

def parse_ksei_pdf(pdf_path):
    result = subprocess.run(['pdftotext', '-layout', pdf_path, '-'], stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
    if result.returncode != 0:
        return {"error": f"Failed to run pdftotext: {result.stderr}"}
        
    lines = result.stdout.split('\n')
    
    records = []
    current_record = None
    
    for line in lines:
        if 'Kepemilikan Per' in line or 'Nama Emiten' in line or 'Nama Rekening Efek' in line or 'Alamat' in line or 'Page' in line or 'Hal' in line:
            continue
            
        if not line.strip(): continue
        
        # New record starts with spaces and Ticker
        match = re.match(r'^\s{0,35}([A-Z0-9]{4,5})\s', line)
        if match:
            if current_record:
                records.append(current_record)
            current_record = {'ticker': match.group(1), 'lines': [line]}
        elif current_record:
            current_record['lines'].append(line)
            
    if current_record:
        records.append(current_record)
        
    parsed_records = []
    
    for r in records:
        ticker = r['ticker']
        lines = r['lines']
        text_block = '\n'.join(lines)
        
        # Extract L/F
        lf_match = re.search(r'\s([LF])\s*\n', text_block)
        lf = lf_match.group(1) if lf_match else 'L'
        
        shares, pct = 0, 0.0
        
        # Extract numbers (sum row)
        for line in reversed(lines):
            num_match = re.search(r'([\d,]{5,})\s+([\d,]{5,})?\s*([\d\.]+)', line)
            if num_match:
                shares = int(num_match.group(1).replace(',', ''))
                pct = float(num_match.group(3))
                break
                
        # If pct is weirdly large (e.g., misparsed), attempt secondary fallback
        if pct == 0.0:
            for line in reversed(lines):
                alt_match = re.search(r'([\d,]{5,})\s+([\d\.]+)', line)
                if alt_match:
                    shares = int(alt_match.group(1).replace(',', ''))
                    pct = float(alt_match.group(2))
                    break
        
        # Extract investor name
        right_column_parts = []
        left_column_parts = []
        
        for line in lines:
            # Replace numbers and L/F at the end with spaces to preserve offsets
            clean_line = re.sub(r'(\s+[LF]?\s*[\d,]+\s*[\d\.]+\s*)$', lambda m: ' ' * len(m.group(1)), line)
            clean_line = re.sub(r'(\s+[LF]?\s*[\d,]+\s*)$', lambda m: ' ' * len(m.group(1)), clean_line)
            clean_line = re.sub(r'(\s+[LF]\s*)$', lambda m: ' ' * len(m.group(1)), clean_line)
                
            # Replace Ticker at the start with spaces
            clean_line = re.sub(r'^(\s{0,35}' + ticker + r'\b)', lambda m: ' ' * len(m.group(1)), clean_line)
            
            # Find blocks of text separated by >= 2 spaces
            for match in re.finditer(r'(?:(?!\s{2}).)+', clean_line):
                chunk = match.group(0).strip()
                if not chunk: continue
                
                # Filter out standalone numbers or garbage
                if re.match(r'^[\d,\.\-]+$', chunk) or chunk == 'INDONESIA': 
                    continue
                
                start_idx = match.start()
                # The right column (Pemegang Saham) usually starts after offset 45
                if start_idx >= 45:
                    right_column_parts.append(chunk)
                else:
                    left_column_parts.append(chunk)
                    
        if right_column_parts:
            investor_name = " ".join(right_column_parts)
        elif left_column_parts:
            investor_name = " ".join(left_column_parts)
        else:
            investor_name = f"INVESTOR OF {ticker}"
            
        investor_name = re.sub(r'[^a-zA-Z0-9\s,\.\-&/()]+$', '', investor_name).strip()
        
        parsed_records.append({
            'ticker': ticker,
            'investor_name': investor_name,
            'shares': shares,
            'pct': pct,
            'local_foreign': lf
        })
        
    return {"status": "success", "data": parsed_records}

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No file"}))
        sys.exit(1)
    res = parse_ksei_pdf(sys.argv[1])
    print(json.dumps(res))
