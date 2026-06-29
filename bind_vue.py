import re

with open('resources/js/Pages/DeskBrief/Index.vue', 'r') as f:
    content = f.read()

# Header / Last Update
content = content.replace(
    'Last Update: <b>27 Jun 2026 17:00 WIB</b> ↻',
    'Last Update: <b>{{ lastUpdate }}</b>'
)
# Print btn
content = content.replace(
    '<div class="btn ghost">⎙ Print / PDF</div>',
    '<div class="btn ghost" @click="printReport">⎙ Print / PDF</div>'
)

# Hero Stance
content = content.replace(
    '<div class="big">SELECTIVE<br>RISK-ON</div>',
    '<div class="big" :class="scoreColor.text">{{ brief.marketStance.label }}</div>'
)
content = content.replace(
    '<span class="v">Constructive</span>',
    '<span class="v">{{ brief.marketStance.view }}</span>'
)
content = content.replace(
    '<span class="v">1–4 minggu</span>',
    '<span class="v">{{ brief.marketStance.horizon }}</span>'
)
content = content.replace(
    '<span class="v pos">42 / 100</span>',
    '<span class="v pos" :class="scoreColor.text">{{ brief.marketStance.score }} / 100</span>'
)
content = content.replace(
    '<h2>Pasar konstruktif di tengah perbaikan global; Indonesia masih menarik bagi asing.</h2>',
    '<h2>{{ brief.headline }}</h2>'
)
content = content.replace(
    '<p>Perbaikan sentimen global dan tekanan inflasi yang melandai mendukung risk appetite. IHSG berpotensi menguat selektif, didorong sektor Banking, Telco, dan Consumer Staples dengan fundamental solid.</p>',
    '<p>{{ brief.subHeadline }}</p>'
)

# Macro Cards - replace static macro blocks with v-for
macro_static = """<div class="macro"><div class="l"><div class="k"><span class="ic"></span>Global Growth · Stable</div><div class="s">2025E Global GDP</div></div><div class="v">3.1%</div></div>
      <div class="macro"><div class="l"><div class="k"><span class="ic"></span>Inflation (US) · Cooling</div><div class="s">Apr-25 PCE YoY</div></div><div class="v">2.3%</div></div>
      <div class="macro"><div class="l"><div class="k"><span class="ic"></span>Liquidity (G3) · Ample</div><div class="s">CB Balance Sheet</div></div><div class="v">$6.1T</div></div>"""
macro_vue = """<div class="macro" v-for="m in brief.macroCards" :key="m.title">
        <div class="l">
          <div class="k"><span class="ic"></span>{{ m.title }} &middot; {{ m.status }}</div>
          <div class="s">{{ m.desc }}</div>
        </div>
        <div class="v">{{ m.value }}</div>
      </div>"""
content = content.replace(macro_static, macro_vue)

# What Changed Ribbon - wait, we already had the delta logic in the previous file.
# Let's replace the whole changed div
changed_static = """<div class="changed">
        <div class="lab"><span class="pulse"></span>What Changed<br>vs 26 Jun close</div>
        <div class="chg"><div class="k">Regime score</div><div class="v">38 → 42 <span class="ar pos">▲ warming</span></div></div>
        <div class="chg"><div class="k">Stance</div><div class="v">Selective Risk-On <span style="color:var(--muted);font-weight:500">· unchanged</span></div></div>
        <div class="chg"><div class="k">Driver escalated</div><div class="v">US Rates <span class="ar neg">▲ to High</span></div></div>
        <div class="chg"><div class="k">Foreign flow</div><div class="v">Outflow → <span class="pos">Net Buy ↺</span></div></div>
        <div class="chg"><div class="k">New risk flag</div><div class="v amb">Yield · SBN 6.85%</div></div>
        <div class="chg"><div class="k">Breadth</div><div class="v">Mild negatif <span style="color:var(--muted);font-weight:500">(198▲/312▼)</span></div></div>
        <div class="chg"><div class="k">Confluence</div><div class="v pos">Banks +1 ▲</div></div>
      </div>"""
changed_vue = """<div class="changed" v-if="delta && delta.stance_changed !== undefined">
        <div class="lab"><span class="pulse"></span>What Changed<br>vs {{ yesterdayStance ? new Date(yesterdayStance.date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) + ' close' : 'prev close' }}</div>
        
        <div class="chg">
          <div class="k">Regime score</div>
          <div class="v">
            {{ yesterdayStance ? yesterdayStance.score : '-' }} &rarr; {{ todayStance ? todayStance.score : '-' }}
            <span v-if="delta.regime > 0" class="ar pos">&#9650; warming</span>
            <span v-else-if="delta.regime < 0" class="ar neg">&#9660; cooling</span>
            <span v-else class="ar" style="color:var(--muted)">· neutral</span>
          </div>
        </div>

        <div class="chg">
          <div class="k">Stance</div>
          <div class="v">
            {{ todayStance ? todayStance.label : brief.marketStance.label }}
            <span v-if="delta.stance_changed" style="color:var(--muted);font-weight:500;margin-left:4px">· changed</span>
            <span v-else style="color:var(--muted);font-weight:500;margin-left:4px">· unchanged</span>
          </div>
        </div>

        <div class="chg"><div class="k">Driver escalated</div><div class="v">US Rates <span class="ar neg">&#9650; to High</span></div></div>
        <div class="chg"><div class="k">Foreign flow</div><div class="v">Outflow &rarr; <span class="pos">Net Buy &#8634;</span></div></div>
        <div class="chg"><div class="k">New risk flag</div><div class="v amb">Yield · SBN 6.85%</div></div>
        <div class="chg"><div class="k">Breadth</div><div class="v">Mild negatif <span style="color:var(--muted);font-weight:500">(198&#9650;/312&#9660;)</span></div></div>
        <div class="chg"><div class="k">Confluence</div><div class="v pos">Banks +1 &#9650;</div></div>
      </div>"""
content = content.replace(changed_static, changed_vue)

with open('resources/js/Pages/DeskBrief/Index.vue', 'w') as f:
    f.write(content)
