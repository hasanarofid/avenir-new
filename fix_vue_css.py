import re

with open('resources/js/Pages/DeskBrief/Index.vue', 'r') as f:
    vue = f.read()

# 1. Rename .ring to .regime-ring in the CSS block of Index.vue
vue = vue.replace('.rings{', '.regime-rings{')
vue = vue.replace('.ring{', '.regime-ring{')
vue = vue.replace('.ring ', '.regime-ring ')

# 2. Revert the tampered .rv HTML to match original structure precisely
bad_rv = '<div class="rv" style="line-height:0.9"><div class="n" style="font-size:32px;font-weight:700">{{ todayStance ? todayStance.score : 0 }}</div><div class="o" style="font-size:10px;margin-top:2px">/100</div></div>'
good_rv = '<div class="rv"><div class="n">{{ todayStance ? todayStance.score : 0 }}</div><div class="o">/100</div></div>'
vue = vue.replace(bad_rv, good_rv)

# 3. Apply the bolding fixes that I did to deskbrief.css directly to the Index.vue style block!
if 'b{font-weight:700' not in vue:
    vue = vue.replace('</style>', 'b, strong { font-weight: 700 !important; }\n</style>')

vue = vue.replace('.regime-ring .rl{font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);font-weight:600;text-align:center}',
                  '.regime-ring .rl{font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);font-weight:700 !important;text-align:center}')

vue = vue.replace('.chd .t b{color:var(--muted);font-weight:700;margin-right:5px}',
                  '.chd .t b{color:#888888;font-weight:700;margin-right:5px}')

with open('resources/js/Pages/DeskBrief/Index.vue', 'w') as f:
    f.write(vue)

print("Fixed Index.vue style block!")
