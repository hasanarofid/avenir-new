import re

with open('prd-testing/new_concept/Avenir-OwnershipIntelligence-6-5.html', 'r') as f:
    content = f.read()

# The data is between 'const DATA =' and ';\nconst entities = DATA.entities'
# Wait, let's just remove the large DATA string
content = re.sub(r'const DATA = \{.*?\};\n', 'const DATA = {}; // Replaced\n', content, flags=re.DOTALL)

with open('prd-testing/new_concept/Avenir-OwnershipIntelligence-logic.html', 'w') as f:
    f.write(content)

