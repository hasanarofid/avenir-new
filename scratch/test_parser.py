import sys
import subprocess
import re
import json

def parse(pdf_path):
    result = subprocess.run(['pdftotext', '-layout', pdf_path, '-'], stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
    lines = result.stdout.split('\n')
    
    records = []
    current_record = None
    
    for i, line in enumerate(lines):
        if not line.strip(): continue
        
        # A new record starts when we see a ticker at index 4-7
        match = re.match(r'^ {4,8}([A-Z0-9]{4,5})\s', line)
        if match:
            if current_record:
                records.append(current_record)
            current_record = {'ticker': match.group(1), 'lines': [line]}
        elif current_record:
            current_record['lines'].append(line)
            
    if current_record:
        records.append(current_record)
        
    out = []
    for r in records[:5]: # just test first 5
        block = '\n'.join(r['lines'])
        print("BLOCK:")
        print(block)
        print("---")
        
if __name__ == "__main__":
    parse(sys.argv[1])
