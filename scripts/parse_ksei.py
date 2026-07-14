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
        match = re.match(r'^ {4,8}([A-Z0-9]{4,5})\s', line)
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
        potential_names = []
        for line in lines:
            if len(line) > 50:
                chunk = line[50:].strip()
                if chunk and not re.search(r'^[\d,\.]+$', chunk):
                    clean_chunk = re.sub(r'[^a-zA-Z0-9\s,\.\-&/]+$', '', chunk).strip()
                    if len(clean_chunk) > 4 and clean_chunk != 'INDONESIA' and not re.search(r'\d{6,}', clean_chunk):
                        potential_names.append(clean_chunk)
        
        investor_name = potential_names[-1] if potential_names else f"INVESTOR OF {ticker}"
        
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
