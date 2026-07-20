<script setup>
import { computed, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
  history: { type: Array, required: true },
});

const timeframes = ['1M', '3M', '6M', 'YTD', '1Y', 'All'];
const activeTimeframe = ref('1Y');

// Helper to get cutoff date based on timeframe
const getCutoffDate = (tf) => {
  if (tf === 'All') return new Date(0);
  const now = new Date();
  if (tf === '1M') now.setMonth(now.getMonth() - 1);
  else if (tf === '3M') now.setMonth(now.getMonth() - 3);
  else if (tf === '6M') now.setMonth(now.getMonth() - 6);
  else if (tf === 'YTD') { now.setMonth(0); now.setDate(1); }
  else if (tf === '1Y') now.setFullYear(now.getFullYear() - 1);
  return now;
};

const chartDataRaw = computed(() => {
  const cutoff = getCutoffDate(activeTimeframe.value);
  return props.history.filter(item => new Date(item.date) >= cutoff);
});

const chartData = computed(() => {
  if (chartDataRaw.value.length === 0) return [];
  
  return [{
    name: 'IHSG',
    data: chartDataRaw.value.map(item => ({
      x: new Date(item.date).getTime(),
      y: parseFloat(item.value)
    }))
  }];
});

const latestData = computed(() => {
  if (chartDataRaw.value.length === 0) return null;
  return chartDataRaw.value[chartDataRaw.value.length - 1];
});

const startData = computed(() => {
  if (chartDataRaw.value.length === 0) return null;
  return chartDataRaw.value[0];
});

const periodChangeAbs = computed(() => {
  if (!latestData.value || !startData.value) return 0;
  return parseFloat(latestData.value.value) - parseFloat(startData.value.value);
});

const periodChangePct = computed(() => {
  if (!latestData.value || !startData.value || parseFloat(startData.value.value) === 0) return 0;
  return (periodChangeAbs.value / parseFloat(startData.value.value)) * 100;
});

const isUp = computed(() => {
  return periodChangeAbs.value >= 0;
});

const hoveredData = ref(null);

const displayData = computed(() => hoveredData.value || latestData.value);

const displayChangeAbs = computed(() => {
  if (!displayData.value || !startData.value) return 0;
  return parseFloat(displayData.value.value) - parseFloat(startData.value.value);
});

const displayChangePct = computed(() => {
  if (!displayData.value || !startData.value || parseFloat(startData.value.value) === 0) return 0;
  return (displayChangeAbs.value / parseFloat(startData.value.value)) * 100;
});

const displayIsUp = computed(() => displayChangeAbs.value >= 0);

const displayDateLabel = computed(() => {
  if (hoveredData.value) {
    const d = new Date(hoveredData.value.date);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
  }
  return periodLabel.value;
});

const periodLabel = computed(() => {
  const map = {
    '1M': '1 Bulan Terakhir',
    '3M': '3 Bulan Terakhir',
    '6M': '6 Bulan Terakhir',
    'YTD': 'Tahun Ini (YTD)',
    '1Y': '1 Tahun Terakhir',
    'All': 'Keseluruhan',
  };
  return map[activeTimeframe.value] || activeTimeframe.value;
});

const chartOptions = computed(() => ({
  chart: {
    type: 'area',
    toolbar: { show: false },
    background: 'transparent',
    parentHeightOffset: 0,
    zoom: { enabled: false },
    animations: { enabled: false },
    events: {
      mouseMove: function(event, chartContext, config) {
        if (config.dataPointIndex !== -1 && chartDataRaw.value[config.dataPointIndex]) {
          hoveredData.value = chartDataRaw.value[config.dataPointIndex];
        }
      },
      mouseLeave: function() {
        hoveredData.value = null;
      }
    }
  },
  colors: [isUp.value ? '#10b981' : '#ef4444'], // emerald-500 or red-500
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.4,
      opacityTo: 0,
      stops: [0, 90, 100]
    }
  },
  annotations: {
    yaxes: startData.value ? [
      {
        y: parseFloat(startData.value.value),
        borderColor: '#6b7280', // gray-500
        strokeDashArray: 5,
        opacity: 0.8,
      }
    ] : []
  },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  xaxis: {
    type: 'datetime',
    labels: { style: { colors: '#9ca3af' }, datetimeUTC: false },
    axisBorder: { show: false },
    axisTicks: { show: false },
    tooltip: { enabled: false }
  },
  responsive: [
    {
      breakpoint: 640,
      options: {
        xaxis: {
          labels: { show: false }
        }
      }
    }
  ],
  yaxis: {
    labels: {
      align: 'left',
      offsetX: -15,
      formatter: (value) => value.toLocaleString('id-ID', { maximumFractionDigits: 0 }),
      style: { colors: '#9ca3af' },
    }
  },
  grid: {
    borderColor: '#374151', // gray-700
    strokeDashArray: 4,
    xaxis: { lines: { show: false } },
    yaxis: { lines: { show: false } },
    padding: { top: 0, right: 0, bottom: 0, left: 0 }
  },
  tooltip: {
    theme: 'dark',
    x: { format: 'dd MMM yyyy' },
    y: { formatter: (val) => val.toFixed(2) }
  }
}));
</script>

<template>
  <div class="card" style="padding:24px; margin-bottom:24px; display:block">
    <!-- DESKTOP HEADER (hidden on mobile) -->
    <div class="hidden md:flex flex-row justify-between items-center mb-6">
      <div>
        <div class="flex items-center gap-3">
          <h2 class="text-xl font-bold text-white tracking-tight" style="margin:0">IHSG</h2>
          <span class="text-xs font-semibold" style="color:var(--muted); background:var(--bg2); padding:3px 8px; border-radius:4px">Indeks Harga Saham Gabungan</span>
        </div>
        <div class="mt-2 flex items-baseline gap-3">
          <span class="text-3xl font-bold text-white" style="font-variant-numeric:tabular-nums">{{ displayData ? parseFloat(displayData.value).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2}) : '0' }}</span>
          <span :class="['text-sm font-semibold flex items-center']" :style="{ color: displayIsUp ? '#46C46E' : '#E2705C' }">
            <svg v-if="displayIsUp" class="w-4 h-4 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
            <svg v-else class="w-4 h-4 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" /></svg>
            {{ displayChangeAbs > 0 ? '+' : '' }}{{ displayChangeAbs.toFixed(2) }} 
            ({{ displayChangePct > 0 ? '+' : '' }}{{ displayChangePct.toFixed(2) }}%)
            <span style="color:var(--muted); font-weight:normal; margin-left:8px">{{ displayDateLabel }}</span>
          </span>
        </div>
      </div>
      
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

    <!-- MOBILE HEADER (hidden on desktop) -->
    <div class="flex md:hidden flex-col mb-4">
      <div class="text-xs font-medium" style="color:var(--muted); margin-bottom:4px">Indeks Harga Saham Gabungan</div>
      <div class="flex justify-between items-center">
        <div class="text-4xl font-bold text-white tracking-tight" style="font-variant-numeric:tabular-nums">{{ displayData ? parseFloat(displayData.value).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2}) : '0' }}</div>
        <img src="/favicon.png" alt="Logo" class="w-10 h-10 rounded-full object-contain bg-white p-1" />
      </div>
      <div class="mt-1 flex items-center text-sm font-semibold" :style="{ color: displayIsUp ? '#46C46E' : '#E2705C' }">
        <svg v-if="displayIsUp" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7M17 7V17" /></svg>
        <svg v-else class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 7L7 17M7 17H17M7 17V7" /></svg>
        <span>{{ displayChangeAbs > 0 ? '+' : '' }}{{ displayChangeAbs.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2}) }} ({{ displayChangePct > 0 ? '+' : '' }}{{ displayChangePct.toFixed(2) }}%) <span style="color:var(--muted); font-weight:normal; margin-left:4px">{{ periodLabel }}</span></span>
      </div>
      
      <!-- Tags for mobile -->
      <div class="flex flex-wrap gap-2 mt-4">
        <span class="px-2 py-1 text-xs rounded border" :style="{ borderColor: displayIsUp ? 'rgba(70,196,110,0.4)' : 'rgba(226,112,92,0.4)', color: displayIsUp ? '#46C46E' : '#E2705C' }">Composite</span>
        <span class="px-2 py-1 text-xs rounded border" :style="{ borderColor: displayIsUp ? 'rgba(70,196,110,0.4)' : 'rgba(226,112,92,0.4)', color: displayIsUp ? '#46C46E' : '#E2705C' }">Market Index</span>
      </div>
    </div>
    
    <div class="h-72 w-full">
      <VueApexCharts
        v-if="chartData.length"
        type="area"
        height="100%"
        :options="chartOptions"
        :series="chartData"
      />
      <div v-else class="h-full flex items-center justify-center text-gray-500">
        No data available
      </div>
    </div>

    <!-- MOBILE TIMEFRAMES (hidden on desktop) -->
    <div class="flex md:hidden mt-4 flex-wrap justify-between items-center px-1">
      <button 
        v-for="tf in timeframes" 
        :key="tf"
        @click="activeTimeframe = tf"
        class="text-xs font-semibold px-2 py-1 transition-colors"
        :style="activeTimeframe === tf ? 'color:#fff; border-bottom:2px solid ' + (displayIsUp ? '#46C46E' : '#E2705C') : 'color:var(--muted); border-bottom:2px solid transparent'"
      >
        {{ tf }}
      </button>
    </div>
  </div>
</template>
