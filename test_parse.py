import re
import subprocess
result_layout = subprocess.run(['pdftotext', '-layout', '/home/hasanarofid/Downloads/ds_260707.pdf', '-'], stdout=subprocess.PIPE, text=True)
layout_text = result_layout.stdout

value_match = re.search(r'([0-9]{1,3}(?:,[0-9]{3})+)\s+([0-9]{1,3}(?:,[0-9]{3})*|\d+)\s+.*?\(billion IDR\)', layout_text, re.DOTALL)
print(f"Value Traded: {value_match.group(1)}" if value_match else "Value Traded: No match")

market_cap_match = re.search(r'IHSG Market Cap\^.*?\n\s+([0-9]{1,3}(?:,[0-9]{3})*)', layout_text, re.DOTALL)
print(f"Market Cap: {market_cap_match.group(1)}" if market_cap_match else "Market Cap: No match")
