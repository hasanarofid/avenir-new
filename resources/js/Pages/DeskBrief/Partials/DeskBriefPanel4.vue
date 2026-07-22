<template>
  <div class="card span12" style="padding:24px; margin-bottom:24px; display:block">
    <!-- DESKTOP HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-3 gap-4">
      <div>
        <div class="flex items-center gap-3 flex-wrap">
          <h2 class="text-xl font-bold text-white tracking-tight" style="margin:0">
            <span style="color:#888; font-weight:700; margin-right:6px">4.</span>REGIME &amp; FLOW
          </h2>

          <!-- Dropdown chart selector -->
          <div class="p4dd" :class="{ open: ddOpen }" @click.stop="ddOpen = !ddOpen">
            <button class="p4dd-btn">
              <span id="p4DdLabel">{{ selectedChartLabel }}</span>
              <span class="p4dd-arw">▾</span>
            </button>
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
                  <span class="p4dd-dot" style="background:#FFFFFF"></span>
                  <span>Foreign Momentum v2</span>
                </div>
                <span class="p4dd-check">✓</span>
              </div>
              <div class="p4dd-item" :class="{ on: selectedChart === 'stress' }" @click.stop="selectChart('stress')">
                <div style="display:flex;align-items:center;gap:10px">
                  <span class="p4dd-dot" style="background:#FFFFFF"></span>
                  <span>Market Stress Engine</span>
                </div>
                <span class="p4dd-check">✓</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Latest Value & Subtitle Info (Phase/Signal/Regime matching Python output) -->
        <div class="mt-2 flex items-baseline gap-3 flex-wrap">
          <span class="text-3xl font-bold text-white" style="font-variant-numeric:tabular-nums">
            {{ chartStats.current }}
          </span>
          <span class="text-sm font-semibold px-2.5 py-1 rounded-md" :style="{ color: chartColor, background: 'var(--inset)', border: '1px solid var(--line2)' }">
            {{ chartStats.lvl }}
          </span>
          <span class="text-xs font-medium" style="color:var(--muted)">
            {{ subTitleText }}
          </span>
        </div>
      </div>

      <!-- Timeframe selector buttons -->
      <div class="flex flex-wrap gap-1 p-1 rounded-lg" style="background:var(--bg2)">
        <button 
          v-for="tf in timeframes" 
          :key="tf"
          @click="activeTimeframe = tf"
          :class="['px-3 py-1 text-xs font-medium rounded-md transition-colors', activeTimeframe === tf ? 'text-white shadow-sm' : 'hover:text-white']"
          :style="activeTimeframe === tf ? 'background:var(--card)' : 'color:var(--muted)'"
        >
          {{ tf }}
        </button>
      </div>
    </div>

    <!-- END VALUES SUMMARY BADGES (matching image right-aligned labels) -->
    <div v-if="endValues.length" class="flex flex-wrap gap-4 mb-3 text-xs font-semibold px-1">
      <div v-for="(ev, idx) in endValues" :key="idx" class="flex items-center gap-1.5" :style="{ color: ev.color }">
        <span class="w-2.5 h-2.5 rounded-full" :style="{ background: ev.color }"></span>
        <span>{{ ev.label }}: {{ ev.value }}</span>
      </div>
    </div>

    <!-- CHART AREA -->
    <div class="h-80 w-full relative">
      <VueApexCharts
        v-if="chartSeries.length && filteredHistoricalScores.length"
        type="line"
        height="100%"
        :options="chartOptions"
        :series="chartSeries"
      />
      <div v-else class="h-full flex items-center justify-center text-gray-500">
        No historical data available
      </div>
    </div>

    <!-- BOTTOM STATS CARDS -->
    <div class="smstats grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
      <div class="sms">
        <div class="k">CURRENT {{ selectedChartLabel.toUpperCase() }}</div>
        <div class="v" :style="{ color: chartColor }">{{ chartStats.current }}</div>
        <div class="s">{{ chartStats.lvl }}</div>
      </div>
      <div class="sms">
        <div class="k">AVG ({{ activeTimeframe }})</div>
        <div class="v">{{ chartStats.avg }}</div>
        <div class="s">Rata-rata periode</div>
      </div>
      <div class="sms">
        <div class="k">RANGE ({{ activeTimeframe }})</div>
        <div class="v">{{ chartStats.min }}–{{ chartStats.max }}</div>
        <div class="s">Min–Max skor</div>
      </div>
    </div>

    <div class="csrc mt-3 text-xs" style="color:var(--faint)">
      Source: Avenir Research — {{ sourceDesc }} (0–100) — {{ activeTimeframe }} EOD
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
  historicalScores: { type: Array, default: () => [] },
});

const ddOpen = ref(false);
const selectedChart = ref('momentum'); // default to 'momentum' or 'regime'
const timeframes = ['1M', '3M', '6M', 'YTD', '1Y', 'All'];
const activeTimeframe = ref('6M');

const closeDd = () => { ddOpen.value = false; };
onMounted(() => { document.addEventListener('click', closeDd); });
onUnmounted(() => { document.removeEventListener('click', closeDd); });

function selectChart(val) {
  selectedChart.value = val;
  ddOpen.value = false;
}

const selectedChartLabel = computed(() => {
  if (selectedChart.value === 'regime') return 'Regime Score';
  if (selectedChart.value === 'momentum') return 'Foreign Momentum v2';
  if (selectedChart.value === 'stress') return 'Market Stress Engine';
  return '';
});

const chartColor = computed(() => {
  if (selectedChart.value === 'regime') return '#5FA0D8';
  if (selectedChart.value === 'momentum') return '#FFFFFF';
  if (selectedChart.value === 'stress') return '#FFFFFF';
  return '#FFFFFF';
});

const sourceDesc = computed(() => {
  if (selectedChart.value === 'regime') return 'engine Avenir 5-komponen';
  if (selectedChart.value === 'momentum') return 'foreign flow momentum & exhaustion model';
  if (selectedChart.value === 'stress') return 'composite market stress engine';
  return '';
});

const getCutoffDate = (tf) => {
  if (tf === 'All') return new Date(0);
  const data = props.historicalScores;
  const now = data && data.length 
    ? new Date(data[data.length - 1].date)
    : new Date();
  const cutoff = new Date(now);
  if (tf === '1M') cutoff.setMonth(cutoff.getMonth() - 1);
  else if (tf === '3M') cutoff.setMonth(cutoff.getMonth() - 3);
  else if (tf === '6M') cutoff.setMonth(cutoff.getMonth() - 6);
  else if (tf === 'YTD') { cutoff.setMonth(0); cutoff.setDate(1); }
  else if (tf === '1Y') cutoff.setFullYear(cutoff.getFullYear() - 1);
  return cutoff;
};

const filteredHistoricalScores = computed(() => {
  if (!props.historicalScores || !props.historicalScores.length) return [];
  const sorted = [...props.historicalScores].sort((a, b) => new Date(a.date) - new Date(b.date));
  const cutoff = getCutoffDate(activeTimeframe.value);
  return sorted.filter(item => new Date(item.date) >= cutoff);
});

const latestRow = computed(() => {
  const data = filteredHistoricalScores.value;
  return data.length ? data[data.length - 1] : null;
});

const subTitleText = computed(() => {
  const row = latestRow.value;
  if (!row) return '';

  if (selectedChart.value === 'momentum') {
    const mom = parseFloat(row.flow_momentum_v2_score ?? row.score ?? 50);
    const exh = parseFloat(row.flow_exhaustion_score ?? 20);
    const rev = parseFloat(row.reversal_probability ?? 50);

    let phase = 'Neutral / Transition';
    if (exh >= 70 && mom < 45) phase = 'Capitulation / Exhaustion';
    else if (exh >= 60 && mom >= 45 && mom < 60) phase = 'Absorption';
    else if (mom >= 60 && rev >= 60) phase = 'Accumulation';
    else if (mom >= 70) phase = 'Positive Momentum';
    else if (mom < 35) phase = 'Selling Acceleration';

    let signal = 'HOLD / WAIT';
    if (rev >= 65 && mom >= 55) signal = 'BUY / ACCUMULATION';
    else if (rev <= 35 && mom <= 40) signal = 'SELL / DISTRIBUTION';

    return `Latest phase: ${phase} | Signal: ${signal} | ▲ Momentum (p85>59) · return IHSG 15 hari bursa`;
  }

  if (selectedChart.value === 'stress') {
    const comp = parseFloat(row.market_stress_composite ?? 50);
    let regime = 'Normal';
    if (comp > 65) regime = 'Extreme Stress';
    else if (comp >= 45) regime = 'Elevated Stress';
    else regime = 'Low Stress / Normal';

    return `Latest regime: ${regime} | Composite stress: ${comp.toFixed(1)} | ▼ Low (p15<44) ▲ High (p85>76)`;
  }

  return `Latest stance: ${(row.label || 'Neutral').toUpperCase()} | Score: ${Math.round(row.score || 50)} / 100 · 5-komponen Avenir`;
});

const endValues = computed(() => {
  const row = latestRow.value;
  if (!row) return [];

  if (selectedChart.value === 'momentum') {
    return [
      { label: 'Flow Momentum V2', value: parseFloat(row.flow_momentum_v2_score ?? row.score ?? 50).toFixed(1), color: '#FFFFFF' },
      { label: 'Flow Exhaustion', value: parseFloat(row.flow_exhaustion_score ?? 20).toFixed(1), color: '#46C46E' },
      { label: 'Reversal Probability', value: parseFloat(row.reversal_probability ?? 50).toFixed(1) + '%', color: '#E2705C' }
    ];
  }

  if (selectedChart.value === 'stress') {
    return [
      { label: 'Market Stress Composite', value: parseFloat(row.market_stress_composite ?? 50).toFixed(1), color: '#FFFFFF' },
      { label: 'Macro Stress', value: parseFloat(row.macro_stress ?? 50).toFixed(1), color: '#46C46E' },
      { label: 'Flow & Internal Stress', value: parseFloat(row.flow_internal_stress ?? 50).toFixed(1), color: '#E2705C' }
    ];
  }

  return [
    { label: 'Regime Score', value: Math.round(row.score || 50).toString(), color: '#5FA0D8' }
  ];
});

const chartSeries = computed(() => {
  const data = filteredHistoricalScores.value;
  if (!data.length) return [];

  if (selectedChart.value === 'regime') {
    return [{
      name: 'Regime Score',
      data: data.map(d => ({
        x: new Date(d.date).getTime(),
        y: Math.round(parseFloat(d.score || 50))
      }))
    }];
  }

  if (selectedChart.value === 'momentum') {
    return [
      {
        name: 'Flow Momentum V2',
        data: data.map(d => ({ x: new Date(d.date).getTime(), y: parseFloat(d.flow_momentum_v2_score ?? d.score ?? 50) }))
      },
      {
        name: 'Flow Exhaustion',
        data: data.map(d => ({ x: new Date(d.date).getTime(), y: parseFloat(d.flow_exhaustion_score ?? 20) }))
      },
      {
        name: 'Reversal Probability',
        data: data.map(d => ({ x: new Date(d.date).getTime(), y: parseFloat(d.reversal_probability ?? 50) }))
      }
    ];
  }

  if (selectedChart.value === 'stress') {
    return [
      {
        name: 'Market Stress Composite',
        data: data.map(d => ({ x: new Date(d.date).getTime(), y: parseFloat(d.market_stress_composite ?? 50) }))
      },
      {
        name: 'Macro Stress',
        data: data.map(d => ({ x: new Date(d.date).getTime(), y: parseFloat(d.macro_stress ?? 50) }))
      },
      {
        name: 'Flow & Internal Stress',
        data: data.map(d => ({ x: new Date(d.date).getTime(), y: parseFloat(d.flow_internal_stress ?? 50) }))
      }
    ];
  }
  return [];
});

const highLowAnnotations = computed(() => {
  const data = filteredHistoricalScores.value;
  if (!data || data.length < 5) return [];

  const key = selectedChart.value === 'momentum' 
    ? 'flow_momentum_v2_score' 
    : (selectedChart.value === 'stress' ? 'market_stress_composite' : 'score');

  const points = [];
  const len = data.length;

  for (let i = 2; i < len - 2; i++) {
    const val = parseFloat(data[i][key] ?? data[i].score ?? 50);
    const prev1 = parseFloat(data[i-1][key] ?? data[i-1].score ?? 50);
    const prev2 = parseFloat(data[i-2][key] ?? data[i-2].score ?? 50);
    const next1 = parseFloat(data[i+1][key] ?? data[i+1].score ?? 50);
    const next2 = parseFloat(data[i+2][key] ?? data[i+2].score ?? 50);

    // Peak High
    if (val > prev1 && val > prev2 && val > next1 && val > next2 && val > 55) {
      points.push({
        x: new Date(data[i].date).getTime(),
        y: val,
        marker: { size: 4, fillColor: '#46C46E', strokeColor: '#FFFFFF', strokeWidth: 1.5 },
        label: {
          text: `High ${Math.round(val)}`,
          borderColor: 'transparent',
          style: { color: '#46C46E', background: '#161616', fontSize: '9px', fontWeight: 'bold' }
        }
      });
    }
    // Trough Low
    else if (val < prev1 && val < prev2 && val < next1 && val < next2 && val < 45) {
      points.push({
        x: new Date(data[i].date).getTime(),
        y: val,
        marker: { size: 4, fillColor: '#E2705C', strokeColor: '#FFFFFF', strokeWidth: 1.5 },
        label: {
          text: `Low ${Math.round(val)}`,
          borderColor: 'transparent',
          style: { color: '#E2705C', background: '#161616', fontSize: '9px', fontWeight: 'bold' }
        }
      });
    }
  }
  return points;
});

const chartOptions = computed(() => ({
  chart: {
    type: 'line',
    toolbar: { show: false },
    background: 'transparent',
    parentHeightOffset: 0,
    zoom: { enabled: false },
    animations: { enabled: false },
  },
  colors: selectedChart.value === 'regime' 
    ? ['#5FA0D8']
    : (selectedChart.value === 'momentum' 
      ? ['#FFFFFF', '#46C46E', '#E2705C'] 
      : ['#FFFFFF', '#46C46E', '#E2705C']),
  annotations: {
    yaxes: [
      {
        y: 50,
        borderColor: '#4b5563',
        strokeDashArray: 4,
        label: {
          text: 'Neutral (50)',
          style: { color: '#9ca3af', background: '#1f2937' }
        }
      }
    ],
    points: highLowAnnotations.value
  },
  dataLabels: { enabled: false },
  stroke: {
    curve: 'smooth',
    width: selectedChart.value === 'regime' ? 3 : [3, 2, 2],
    dashArray: selectedChart.value === 'regime' ? 0 : [0, 6, 3]
  },
  xaxis: {
    type: 'datetime',
    labels: { style: { colors: '#9ca3af' }, datetimeUTC: false },
    axisBorder: { show: false },
    axisTicks: { show: false },
    tooltip: { enabled: false }
  },
  yaxis: {
    min: 0,
    max: 100,
    labels: {
      align: 'left',
      offsetX: -10,
      formatter: (val) => Math.round(val),
      style: { colors: '#9ca3af' }
    }
  },
  grid: {
    borderColor: '#2E2E2E',
    strokeDashArray: 4,
    xaxis: { lines: { show: false } },
    yaxis: { lines: { show: true } },
    padding: { top: 10, right: 10, bottom: 0, left: 10 }
  },
  legend: {
    show: selectedChart.value !== 'regime',
    position: 'top',
    horizontalAlign: 'right',
    labels: { colors: '#d1d5db' }
  },
  tooltip: {
    theme: 'dark',
    x: { format: 'dd MMM yyyy' },
    y: { formatter: (val) => typeof val === 'number' ? val.toFixed(1) : val }
  }
}));

const chartStats = computed(() => {
  const data = filteredHistoricalScores.value;
  if (!data || !data.length) return { current: '-', avg: '-', min: '-', max: '-', lvl: 'No Data' };

  const getVal = (d) => {
    if (selectedChart.value === 'regime') return d.score || 50;
    if (selectedChart.value === 'momentum') return d.flow_momentum_v2_score ?? d.score ?? 50;
    if (selectedChart.value === 'stress') return d.market_stress_composite ?? 50;
    return 50;
  };

  const values = data.map(d => parseFloat(getVal(d)));
  const current = values[values.length - 1];
  const avg = (values.reduce((a, b) => a + b, 0) / values.length).toFixed(1);
  const min = Math.min(...values).toFixed(1);
  const max = Math.max(...values).toFixed(1);

  let lvl = 'Neutral';
  if (selectedChart.value === 'stress') {
    if (current < 40) lvl = 'Low Stress / Normal';
    else if (current > 60) lvl = 'Extreme Stress';
    else lvl = 'Elevated Stress';
  } else if (selectedChart.value === 'momentum') {
    if (current > 60) lvl = 'Accumulation Phase';
    else if (current < 40) lvl = 'Distribution Phase';
    else lvl = 'Neutral / Transition';
  } else {
    if (current > 60) lvl = 'Bullish Regime';
    else if (current < 40) lvl = 'Bearish Regime';
    else lvl = 'Neutral Regime';
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
.chd { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.p4dd { position: relative; display: inline-block; }
.p4dd-btn { display: flex; align-items: center; gap: 8px; background: transparent; border: 1px solid var(--green2); color: var(--green); font-size: 11px; font-weight: 600; padding: 5px 12px; border-radius: 20px; cursor: pointer; transition: background .12s; }
.p4dd-btn:hover { background: rgba(70,196,110,.08); }
.p4dd-arw { font-size: 9px; transition: transform .18s; }
.p4dd.open .p4dd-arw { transform: rotate(180deg); }
.p4dd-menu { position: absolute; top: calc(100% + 6px); left: 0; min-width: 190px; background: #161616; border: 1px solid var(--line3); border-radius: 10px; padding: 5px; z-index: 40; opacity: 0; visibility: hidden; transform: translateY(-6px); transition: opacity .14s, transform .14s; box-shadow: 0 14px 40px rgba(0,0,0,.6); }
.p4dd.open .p4dd-menu { opacity: 1; visibility: visible; transform: translateY(0); }
.p4dd-item { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 11px; border-radius: 7px; font-size: 11.5px; color: var(--ink2); cursor: pointer; }
.p4dd-item:hover { background: #1F1F1F; color: var(--ink); }
.p4dd-item.on { color: var(--green); }
.p4dd-item .p4dd-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.p4dd-item .p4dd-check { font-size: 11px; opacity: 0; }
.p4dd-item.on .p4dd-check { opacity: 1; }

.smstats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 14px; }
.sms { background: var(--inset); border: 1px solid var(--line); border-radius: 8px; padding: 12px 14px; }
.sms .k { font-size: 8.5px; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; font-weight: 600; }
.sms .v { font-size: 16px; font-weight: 700; margin-top: 4px; }
.sms .s { font-size: 9.5px; color: var(--faint); margin-top: 2px; }
</style>
