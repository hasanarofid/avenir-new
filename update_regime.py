import re

with open('resources/js/Pages/DeskBrief/Index.vue', 'r') as f:
    content = f.read()

target = """      <!-- 1. REGIME SUMMARY -->
      <div class="card span3">
        <div class="chd"><div class="t"><b>1.</b>REGIME SUMMARY</div></div>
        <div class="ring" style="align-self:center">
          <div class="rw"><svg width="92" height="92" viewBox="0 0 96 96"><circle cx="48" cy="48" r="40" fill="none" stroke="#222" stroke-width="8"/><circle cx="48" cy="48" r="40" fill="none" stroke="#46C46E" stroke-width="8" stroke-linecap="round" stroke-dasharray="251.3" stroke-dashoffset="145.8" transform="rotate(-90 48 48)"/></svg><div class="rv"><div class="n">42</div><div class="o">/100</div></div></div>
          <div class="rl pos" style="color:var(--green)">Selective Risk-On</div>
        </div>
        <div style="font-size:8.5px;color:var(--faint);text-transform:uppercase;letter-spacing:.08em;margin-top:14px;font-weight:600">Komponen skor</div>
        <div class="rcomp">
          <span class="rc up">Flow ▲</span><span class="rc up">Breadth ▲</span><span class="rc up">Momentum ▲</span>
          <span class="rc dn">Rupiah ▼</span><span class="rc dn">Yield ▼</span><span class="rc up">Rotasi ▲</span>
        </div>
        <div class="traj">Trajectory: <b>warming</b> · 38 → 42 (4 dari 6 komponen positif)</div>
        <div class="csrc">Prev. Score: 38 (26 Jun 2026)</div>
      </div>"""

replacement = """      <!-- 1. REGIME SUMMARY -->
      <div class="card span3">
        <div class="chd"><div class="t"><b>1.</b>REGIME SUMMARY</div></div>
        <div class="ring" style="align-self:center">
          <div class="rw">
            <svg width="92" height="92" viewBox="0 0 96 96">
              <circle cx="48" cy="48" r="40" fill="none" stroke="#222" stroke-width="8"/>
              <circle cx="48" cy="48" r="40" fill="none" stroke="#46C46E" stroke-width="8" stroke-linecap="round" stroke-dasharray="251.3" :stroke-dashoffset="251.3 - ((todayStance ? todayStance.score : 0) / 100 * 251.3)" transform="rotate(-90 48 48)"/>
            </svg>
            <div class="rv"><div class="n">{{ todayStance ? todayStance.score : 0 }}</div><div class="o">/100</div></div>
          </div>
          <div class="rl pos" style="color:var(--green)">{{ todayStance ? todayStance.label.toUpperCase() : 'UNKNOWN' }}</div>
        </div>
        <div style="font-size:8.5px;color:var(--faint);text-transform:uppercase;letter-spacing:.08em;margin-top:14px;font-weight:600">Komponen skor</div>
        <div class="rcomp">
          <span class="rc up">Flow ▲</span><span class="rc up">Breadth ▲</span><span class="rc up">Momentum ▲</span>
          <span class="rc dn">Rupiah ▼</span><span class="rc dn">Yield ▼</span><span class="rc up">Rotasi ▲</span>
        </div>
        <div class="traj">
          Trajectory: <span :class="delta && delta.regime > 0 ? 'pos' : (delta && delta.regime < 0 ? 'neg' : '')"><b>{{ delta && delta.regime_trend ? delta.regime_trend : 'neutral' }}</b></span> · {{ yesterdayStance ? yesterdayStance.score : '-' }} → {{ todayStance ? todayStance.score : '-' }} (4 dari 6 komponen positif)
        </div>
        <div class="csrc" v-if="yesterdayStance">Prev. Score: {{ yesterdayStance.score }} ({{ new Date(yesterdayStance.date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }})</div>
      </div>"""

content = content.replace(target, replacement)

with open('resources/js/Pages/DeskBrief/Index.vue', 'w') as f:
    f.write(content)
