import re

with open('resources/js/Pages/DeskBrief/Index.vue', 'r') as f:
    content = f.read()

# Replace tags
tags_pattern = r'<div class="kchip">.*?<span>Disinflation \+ Growth</span>.*?</div>.*?<div class="kchip">.*?<span>Domestic-revenue tilt</span>.*?</div>.*?<div class="kchip">.*?<span>Foreign inflow</span>.*?</div>'
replacement_tags = """<div v-for="driver in deskBrief.drivers" :key="driver.id" class="kchip">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
        </svg>
        <span>{{ driver.title }}</span>
      </div>"""

content = re.sub(tags_pattern, replacement_tags, content, flags=re.DOTALL)

# Replace "WHAT CHANGED"
what_changed_pattern = r'<div class="box wc">.*?<b>WHAT CHANGED</b>.*?<div class="wc_v".*?>.*?</div>.*?</div>'

replacement_wc = """<div class="box wc">
      <b>WHAT CHANGED</b>
      <div class="wc_v">
        <div class="wcv_i" v-if="delta.regime">
          <span>Regime :</span>
          <span><b>{{ Math.abs(delta.regime) }}</b> <span :style="{ color: delta.regime > 0 ? '#10b981' : '#ef4444' }">{{ delta.regime > 0 ? '↗' : '↘' }}</span></span>
        </div>
        <div class="wcv_i" v-if="delta.stance_changed">
          <span>Stance :</span>
          <span><b>Changed</b> ⚠</span>
        </div>
        <div class="wcv_i" v-if="delta.flow_flipped">
          <span>Flow :</span>
          <span><b>Flipped</b> <span>{{ delta.foreign_flow_today === 'Buy' ? '↗' : '↘' }}</span></span>
        </div>
      </div>
    </div>"""

content = re.sub(what_changed_pattern, replacement_wc, content, flags=re.DOTALL)

with open('resources/js/Pages/DeskBrief/Index.vue', 'w') as f:
    f.write(content)
