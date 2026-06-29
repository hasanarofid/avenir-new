import re

with open('resources/js/Pages/DeskBrief/Index.vue', 'r') as f:
    content = f.read()

# Fix Last Update
content = content.replace(
    '<div class="lastupd">Last Update: <b>{{ lastUpdate }}</b></div>',
    '<div class="lastupd">Last Update: <b>{{ brief.lastUpdate }}</b><br><span style="font-size:8.5px;font-weight:normal;color:#999;letter-spacing:0">(Updated daily at EOD)</span></div>'
)

# Fix What Changed ribbon condition
content = content.replace(
    '<div class="changed" v-if="delta && delta.stance_changed !== undefined">',
    '<div class="changed" v-if="delta && Object.keys(delta).length > 0">'
)

# Fix What Changed - Stance
content = content.replace(
    '{{ todayStance ? todayStance.label : brief.marketStance.label }}',
    '{{ todayStance ? todayStance.label.toUpperCase() : brief.marketStance.label }}'
)

# Fix What Changed - Regime Score
content = content.replace(
    '{{ yesterdayStance ? yesterdayStance.score : \'-\' }} &rarr; {{ todayStance ? todayStance.score : \'-\' }}',
    '{{ yesterdayStance ? yesterdayStance.score : \'-\' }} &rarr; {{ todayStance ? todayStance.score : \'-\' }}'
) # Do nothing

with open('resources/js/Pages/DeskBrief/Index.vue', 'w') as f:
    f.write(content)
