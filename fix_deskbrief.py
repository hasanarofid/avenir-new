import re

# Fix deskbrief.css
with open('resources/css/deskbrief.css', 'r') as f:
    css = f.read()

# Make sure b has font-weight 700 globally in deskbrief
if 'b{font-weight:700}' not in css:
    css = css + "\nb, strong { font-weight: 700 !important; }\n"

# Fix .regime-ring .rl to be bolder (700)
css = css.replace('.regime-ring .rl{font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);font-weight:600;text-align:center}',
                  '.regime-ring .rl{font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);font-weight:700 !important;text-align:center}')

# Make .chd .t b have the right color
css = css.replace('.chd .t b{color:var(--muted);font-weight:700;margin-right:5px}',
                  '.chd .t b{color:#888888;font-weight:700;margin-right:5px}')

with open('resources/css/deskbrief.css', 'w') as f:
    f.write(css)

# Fix Index.vue
with open('resources/js/Pages/DeskBrief/Index.vue', 'r') as f:
    vue = f.read()

# Fix the /100 so it aligns correctly next to or under 42 like in HTML
vue = vue.replace('<div class="rv"><div class="n">{{ todayStance ? todayStance.score : 0 }}</div><div class="o">/100</div></div>',
                  '<div class="rv" style="line-height:0.9"><div class="n" style="font-size:32px;font-weight:700">{{ todayStance ? todayStance.score : 0 }}</div><div class="o" style="font-size:10px;margin-top:2px">/100</div></div>')

with open('resources/js/Pages/DeskBrief/Index.vue', 'w') as f:
    f.write(vue)

print("CSS and Vue patched.")
