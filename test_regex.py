import re
layout_text = """By Number          82         140              289             207             243
of Stocks         (9%)       (15%)            (30%)           (22%)           (25%)"""
match = re.search(r'By Number\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)', layout_text)
print(match.groups() if match else "No match")
