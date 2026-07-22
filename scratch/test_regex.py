import re
import time

clean_line = "PT ASURANSI JIWA SINARMAS MSIG Tbk. - SYARIAH            123456789     0.5"
start = time.time()
for _ in range(1000):
    matches = list(re.finditer(r'(?:(?!\s{2}).)+', clean_line))
end = time.time()
print(f"Lookahead time: {end - start}")

start = time.time()
for _ in range(1000):
    matches = list(re.finditer(r'\S+(?:\s\S+)*', clean_line))
end = time.time()
print(f"Better regex time: {end - start}")
