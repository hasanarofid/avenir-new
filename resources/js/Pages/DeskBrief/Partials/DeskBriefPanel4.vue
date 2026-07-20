<template>
  <div class="card span3">
    <div class="chd">
      <div class="t"><b>4.</b>REGIME &amp; FLOW</div>
      <div class="p4dd" :class="{ open: ddOpen }" @click.stop="ddOpen = !ddOpen">
        <button class="p4dd-btn"><span id="p4DdLabel">{{ selectedChartLabel }}</span><span class="p4dd-arw">▾</span></button>
        <div class="p4dd-menu">
          <div class="p4dd-item" :class="{ on: selectedChart === 'regime' }" @click.stop="selectChart('regime')">
            <div style="display:flex;align-items:center;gap:10px">
              <span class="p4dd-dot" style="background:#5FA0D8"></span>
              <span>Regime Score</span>
            </div>
            <span class="p4dd-check">✓</span>
          </div>
          <div class="p4dd-item" :class="{ on: selectedChart === 'momentum' }" @click.stop="selectChart('momentum')">
            <div style="display:flex;align-items:center;gap:10px">
              <span class="p4dd-dot" style="background:#46C46E"></span>
              <span>Foreign Momentum v2</span>
            </div>
            <span class="p4dd-check">✓</span>
          </div>
          <div class="p4dd-item" :class="{ on: selectedChart === 'stress' }" @click.stop="selectChart('stress')">
            <div style="display:flex;align-items:center;gap:10px">
              <span class="p4dd-dot" style="background:#E2705C"></span>
              <span>Market Stress</span>
            </div>
            <span class="p4dd-check">✓</span>
          </div>
        </div>
      </div>
    </div>
    <div style="font-size:9px;color:var(--muted);margin-bottom:4px;display:flex;justify-content:space-between">
      <span>Historical {{ selectedChartLabel }}</span>
      <div class="toggles">
        <span v-for="tf in timeframes" :key="tf" :class="['tg', timeframe === tf ? 'on' : '']" @click.stop="timeframe = tf">{{ tf }}</span>
      </div>
    </div>
    
    <div style="position:relative" @mouseleave="hoveredPoint = null">
      <svg width="100%" viewBox="0 0 320 120" style="display:block; overflow:visible">
        <line x1="14" :y1="chartData.firstY" x2="314" :y2="chartData.firstY" stroke="#2E2E2E" stroke-dasharray="3 4"/>
        <polyline :points="chartData.points" fill="none" :stroke="chartColor" stroke-width="2.2" stroke-linejoin="round"/>
        <circle v-if="chartData.lastPoint" :cx="chartData.lastPoint.x" :cy="chartData.lastPoint.y" r="4" :fill="chartColor"/>
        <circle v-for="(pt, i) in chartData.raw" :key="'hover_'+i"
                :cx="pt.x" :cy="pt.y" r="8" fill="transparent"
                style="cursor:crosshair"
                @mouseenter="hoveredPoint = pt" />
        <g font-size="7.5" fill="#7C7C76">
          <text x="14" y="115">{{ chartData.raw.length ? new Date(chartData.raw[0].date).toLocaleDateString('id-ID', { month: 'short', year: '2-digit' }) : timeframe + ' Ago' }}</text>
          <text x="290" y="115">Now</text>
        </g>
      </svg>
      <div v-if="hoveredPoint" class="chart-tooltip" :style="{ left: hoveredPoint.x + 'px', top: (hoveredPoint.y - 20) + 'px' }">
        <div class="date">{{ new Date(hoveredPoint.date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) }}</div>
        <div class="val">{{ hoveredPoint.val }}</div>
      </div>
    </div>
    <div class="csrc">Source: Avenir Research · {{ timeframe }} · EOD</div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  historicalScores: { type: Array, default: () => [] },
});

const ddOpen = ref(false);
const selectedChart = ref('regime'); // 'regime', 'momentum', 'stress'
const timeframes = ['1M', '6M', '1Y'];
const timeframe = ref('1M');
const hoveredPoint = ref(null);

const selectedChartLabel = computed(() => {
  if (selectedChart.value === 'regime') return 'Regime Score';
  if (selectedChart.value === 'momentum') return 'Foreign Momentum';
  if (selectedChart.value === 'stress') return 'Market Stress';
  return '';
});

const chartColor = computed(() => {
  if (selectedChart.value === 'regime') return '#5FA0D8';
  if (selectedChart.value === 'momentum') return '#46C46E';
  if (selectedChart.value === 'stress') return '#E2705C';
  return '#46C46E';
});

const closeDd = (e) => {
  ddOpen.value = false;
};

onMounted(() => {
  document.addEventListener('click', closeDd);
});
onUnmounted(() => {
  document.removeEventListener('click', closeDd);
});

function selectChart(val) {
  selectedChart.value = val;
  ddOpen.value = false;
}

const chartData = computed(() => {
  const all = [...props.historicalScores].sort((a, b) => new Date(a.date) - new Date(b.date));
  let filtered = all;
  
  if (all.length > 0) {
    const lastDate = new Date(all[all.length - 1].date);
    const msPerDay = 24 * 60 * 60 * 1000;
    let days = 30;
    if (timeframe.value === '6M') days = 180;
    if (timeframe.value === '1Y') days = 365;
    
    const cutoff = new Date(lastDate.getTime() - days * msPerDay);
    filtered = all.filter(d => new Date(d.date) >= cutoff);
  }
  
  if (!filtered.length) return { points: '', raw: [], firstY: 0, lastPoint: null, isUp: true };
  
  // extract value based on selectedChart
  const getVal = (d) => {
    if (selectedChart.value === 'regime') return d.score || 50;
    if (selectedChart.value === 'momentum') return d.flow_momentum_v2_score || 50;
    if (selectedChart.value === 'stress') return d.market_stress_composite || 50;
    return 50;
  };

  const values = filtered.map(d => getVal(d));
  const minVal = Math.min(...values);
  const maxVal = Math.max(...values);
  const range = maxVal - minVal || 1;
  
  const width = 300;
  const height = 80;
  const paddingX = 14;
  const paddingY = 10;
  
  const raw = filtered.map((d, i) => {
    const val = getVal(d);
    const x = paddingX + (i / (Math.max(1, filtered.length - 1))) * width;
    const y = paddingY + height - ((val - minVal) / range) * height;
    return { x, y, val: val.toFixed(1), date: d.date, rawData: d };
  });
  
  const points = raw.map(p => `${p.x},${p.y}`).join(' ');
  const firstY = raw[0].y;
  const lastPoint = raw[raw.length - 1];
  const isUp = raw.length > 1 ? raw[raw.length - 1].val >= raw[0].val : true;
  
  return { points, raw, firstY, lastPoint, isUp };
});
</script>

<style scoped>
.chd{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.chd .t{font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--ink2);font-weight:700}
.chd .t b{color:#888888;font-weight:700;margin-right:5px}
.chd .meta{font-size:10px;color:var(--muted);display:flex;align-items:center;gap:6px}
.csrc{font-size:9px;color:var(--faint);margin-top:auto;padding-top:11px}
.toggles{display:flex;gap:4px}
.tg{font-size:9.5px;font-weight:600;color:var(--muted);padding:3px 7px;border-radius:4px;border:1px solid transparent;cursor:pointer}
.tg.on{background:#222;color:var(--ink);border-color:var(--line3)}
.p4dd{position:relative}
.p4dd-btn{display:flex;align-items:center;gap:8px;background:transparent;border:1px solid var(--green2);color:var(--green);font-size:11px;font-weight:600;padding:5px 12px;border-radius:20px;cursor:pointer;transition:background .12s}
.p4dd-btn:hover{background:rgba(70,196,110,.08)}
.p4dd-arw{font-size:9px;transition:transform .18s}
.p4dd.open .p4dd-arw{transform:rotate(180deg)}
.p4dd-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:170px;background:#161616;border:1px solid var(--line3);border-radius:10px;padding:5px;z-index:40;opacity:0;visibility:hidden;transform:translateY(-6px);transition:opacity .14s,transform .14s;box-shadow:0 14px 40px rgba(0,0,0,.6)}
.p4dd.open .p4dd-menu{opacity:1;visibility:visible;transform:translateY(0)}
.p4dd-item{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 11px;border-radius:7px;font-size:11.5px;color:var(--ink2);cursor:pointer}
.p4dd-item:hover{background:#1F1F1F;color:var(--ink)}
.p4dd-item.on{color:var(--green)}
.p4dd-item .p4dd-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.p4dd-item .p4dd-check{font-size:11px;opacity:0}
.p4dd-item.on .p4dd-check{opacity:1}
.chart-tooltip {position:absolute;background:#161616;border:1px solid var(--line2);padding:4px 8px;border-radius:4px;font-size:10px;color:var(--ink);pointer-events:none;transform:translate(-50%, -100%);white-space:nowrap;z-index:10;}
.chart-tooltip .date {color:var(--muted);font-size:9px;margin-bottom:2px;}
.chart-tooltip .val {font-weight:700;}
</style>
