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
    
    <div style="font-size:9px;color:var(--muted);margin-bottom:8px;display:flex;justify-content:space-between;align-items:flex-end">
      <div>
        <span>Historical {{ selectedChartLabel }}</span>
        <!-- Legend for multiple lines -->
        <div v-if="chartData.lines.length > 1" style="display:flex;gap:10px;margin-top:6px">
          <div v-for="(line, idx) in chartData.lines" :key="idx" style="display:flex;align-items:center;gap:4px;font-size:8.5px">
            <span :style="{ width: '10px', height: '2px', background: line.color }"></span>
            <span style="color:var(--ink2)">{{ line.label }}</span>
          </div>
        </div>
      </div>
      <div class="toggles">
        <span v-for="tf in timeframes" :key="tf" :class="['tg', timeframe === tf ? 'on' : '']" @click.stop="timeframe = tf">{{ tf }}</span>
      </div>
    </div>
    
    <div style="position:relative" @mouseleave="hoveredPoint = null">
      <svg width="100%" viewBox="0 0 320 120" style="display:block; overflow:visible">
        <line x1="14" :y1="chartData.firstY" x2="314" :y2="chartData.firstY" stroke="#2E2E2E" stroke-dasharray="3 4"/>
        
        <polyline v-for="(line, idx) in chartData.lines" :key="'poly_'+idx" 
                  :points="line.points" fill="none" :stroke="line.color" 
                  :stroke-width="line.width || 2.2" 
                  :stroke-dasharray="line.dasharray || ''" 
                  stroke-linejoin="round"/>
                  
        <circle v-for="(line, idx) in chartData.lines" :key="'circ_'+idx" 
                v-show="line.lastPoint" :cx="line.lastPoint?.x" :cy="line.lastPoint?.y" r="4" :fill="line.color"/>
                
        <!-- Hover targets (invisible) -->
        <circle v-for="(pt, i) in chartData.raw" :key="'hover_'+i"
                :cx="pt.x" :cy="pt.y" r="10" fill="transparent"
                style="cursor:crosshair"
                @mouseenter="hoveredPoint = pt" />
                
        <g font-size="7.5" fill="#7C7C76">
          <text x="14" y="115">{{ chartData.raw.length ? new Date(chartData.raw[0].date).toLocaleDateString('id-ID', { month: 'short', year: '2-digit' }) : timeframe + ' Ago' }}</text>
          <text x="290" y="115">Now</text>
        </g>
      </svg>
      
      <div v-if="hoveredPoint" class="chart-tooltip" :style="{ left: hoveredPoint.x + 'px', top: (hoveredPoint.y - 20) + 'px' }">
        <div class="date">{{ new Date(hoveredPoint.date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) }}</div>
        <div v-for="(val, idx) in hoveredPoint.values" :key="idx" class="val" :style="{ color: val.color }">
          {{ val.label }}: {{ val.value }}
        </div>
      </div>
    </div>
    
    <div class="smstats">
      <div class="sms">
        <div class="k">CURRENT</div>
        <div class="v" :style="{ color: chartColor }">{{ chartStats.current }}</div>
        <div class="s">{{ chartStats.lvl }}</div>
      </div>
      <div class="sms">
        <div class="k">AVG {{ timeframe }}</div>
        <div class="v">{{ chartStats.avg }}</div>
        <div class="s">rata-rata</div>
      </div>
      <div class="sms">
        <div class="k">RANGE</div>
        <div class="v">{{ chartStats.min }}–{{ chartStats.max }}</div>
        <div class="s">min–max</div>
      </div>
    </div>
    
    <div class="csrc">Source: Avenir Research - {{ sourceDesc }} (0-100) - {{ timeframe }} EOD</div>
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

const sourceDesc = computed(() => {
  if (selectedChart.value === 'regime') return 'engine Avenir - 5-komponen';
  if (selectedChart.value === 'momentum') return 'momentum asing';
  if (selectedChart.value === 'stress') return 'tekanan pasar';
  return '';
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
  
  if (!filtered.length) return { lines: [], raw: [], firstY: 0 };
  
  const width = 300;
  const height = 80;
  const paddingX = 14;
  const paddingY = 10;
  
  // Base scales based on primary value
  const getPrimaryVal = (d) => {
    if (selectedChart.value === 'regime') return d.score || 50;
    if (selectedChart.value === 'momentum') return d.flow_momentum_v2_score || 50;
    if (selectedChart.value === 'stress') return d.market_stress_composite || 50;
    return 50;
  };

  const primaryValues = filtered.map(d => getPrimaryVal(d));
  // Use fixed scale 0-100 for proper multi-line alignment as in Python scripts
  const minVal = 0;
  const maxVal = 100;
  const range = 100;
  
  const linesConfig = [];
  
  if (selectedChart.value === 'regime') {
    linesConfig.push({ key: 'score', label: 'Regime Score', color: '#5FA0D8', width: 2.2 });
  } else if (selectedChart.value === 'momentum') {
    linesConfig.push({ key: 'flow_momentum_v2_score', label: 'Flow Momentum', color: '#888888', width: 2.2 });
    linesConfig.push({ key: 'flow_exhaustion_score', label: 'Flow Exhaustion', color: '#4E7D52', width: 2, dasharray: '4 3' });
    linesConfig.push({ key: 'reversal_probability', label: 'Reversal Prob.', color: '#8A5A5A', width: 2, dasharray: '2 2' });
  } else if (selectedChart.value === 'stress') {
    linesConfig.push({ key: 'market_stress_composite', label: 'Composite Stress', color: '#888888', width: 2.2 });
    linesConfig.push({ key: 'macro_stress', label: 'Macro Stress', color: '#4E7D52', width: 2, dasharray: '4 3' });
    linesConfig.push({ key: 'flow_internal_stress', label: 'Flow/Internal', color: '#8A5A5A', width: 2, dasharray: '2 2' });
  }

  const lines = linesConfig.map(cfg => {
    const rawPoints = filtered.map((d, i) => {
      const val = d[cfg.key] ?? 50;
      const x = paddingX + (i / (Math.max(1, filtered.length - 1))) * width;
      const y = paddingY + height - ((val - minVal) / range) * height;
      return { x, y, val: parseFloat(val).toFixed(1), date: d.date };
    });
    
    return {
      label: cfg.label,
      color: cfg.color,
      width: cfg.width,
      dasharray: cfg.dasharray,
      points: rawPoints.map(p => `${p.x},${p.y}`).join(' '),
      lastPoint: rawPoints[rawPoints.length - 1],
      rawPoints
    };
  });
  
  // For hover interactions, we need a single array of X points that contain all values
  const raw = filtered.map((d, i) => {
    const x = paddingX + (i / (Math.max(1, filtered.length - 1))) * width;
    const pVal = getPrimaryVal(d);
    const y = paddingY + height - ((pVal - minVal) / range) * height; // Main Y for tooltip position
    
    const values = linesConfig.map(cfg => {
      return {
        label: cfg.label,
        value: parseFloat(d[cfg.key] ?? 50).toFixed(1),
        color: cfg.color
      };
    });
    
    return { x, y, date: d.date, values };
  });

  // Calculate firstY based on primary line to draw the dashed horizontal line
  const firstY = lines[0].rawPoints[0].y;
  
  return { lines, raw, firstY };
});

const chartStats = computed(() => {
  const { lines } = chartData.value;
  if (!lines || !lines.length) return { current: '-', avg: '-', min: '-', max: '-', lvl: '-' };
  
  const primaryLine = lines[0]; // First line is always the main score
  const values = primaryLine.rawPoints.map(d => parseFloat(d.val));
  const current = values[values.length - 1];
  const avg = (values.reduce((a, b) => a + b, 0) / values.length).toFixed(1);
  const min = Math.min(...values).toFixed(1);
  const max = Math.max(...values).toFixed(1);
  
  let lvl = 'Neutral';
  if (selectedChart.value === 'stress') {
    if (current < 40) lvl = 'Benign';
    else if (current > 70) lvl = 'Extreme';
    else lvl = 'Elevated';
  } else if (selectedChart.value === 'momentum') {
    // Phase logic from momentum python script approx
    if (current > 60) lvl = 'Accumulation';
    else if (current < 40) lvl = 'Distribution';
    else lvl = 'Neutral';
  } else {
    if (current > 60) lvl = 'Bullish';
    else if (current < 40) lvl = 'Bearish';
    else lvl = 'Neutral';
  }
  
  const currentFormatted = selectedChart.value === 'regime' ? Math.round(current).toString() : current.toFixed(1);
  
  return {
    current: currentFormatted,
    avg,
    min,
    max,
    lvl
  };
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
.chart-tooltip {position:absolute;background:#161616;border:1px solid var(--line2);padding:6px 10px;border-radius:6px;font-size:10.5px;color:var(--ink);pointer-events:none;transform:translate(-50%, -100%);white-space:nowrap;z-index:10;box-shadow:0 8px 24px rgba(0,0,0,0.5);}
.chart-tooltip .date {color:var(--muted);font-size:9.5px;margin-bottom:4px;border-bottom:1px solid var(--line3);padding-bottom:3px;}
.chart-tooltip .val {font-weight:600;margin-top:3px;display:flex;justify-content:space-between;gap:12px;}
.smstats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:11px}
.sms{background:var(--inset);border:1px solid var(--line);border-radius:8px;padding:9px 10px}
.sms .k{font-size:8px;color:var(--muted);text-transform:uppercase;letter-spacing:.03em;font-weight:600}
.sms .v{font-size:14px;font-weight:700;margin-top:4px}
.sms .s{font-size:8.5px;color:var(--faint);margin-top:1px}
</style>

