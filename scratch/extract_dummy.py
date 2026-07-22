import re

with open('/home/hasan/Documents/hasanarofid/avenir/default-avenir/prd-testing/ownership/new/Avenir-OwnershipIntelligence-6-31.html', 'r') as f:
    content = f.read()

match = re.search(r'const DATA = (\{.*?\});', content, re.DOTALL)
if match:
    with open('/home/hasan/Documents/hasanarofid/avenir/default-avenir/public/dummyOwnership.json', 'w') as out:
        out.write(match.group(1))
    print("Extracted successfully.")
else:
    print("Not found.")
