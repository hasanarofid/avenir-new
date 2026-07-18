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

const chartData = computed(() => {
  const cutoff = getCutoffDate(activeTimeframe.value);
  const filtered = props.history.filter(item => new Date(item.date) >= cutoff);
  
  if (filtered.length === 0) return [];
  
  return [{
    name: 'IHSG',
    data: filtered.map(item => ({
      x: new Date(item.date).getTime(),
      y: parseFloat(item.value)
    }))
  }];
});

const latestData = computed(() => {
  if (!props.history.length) return null;
  return props.history[props.history.length - 1];
});

const isUp = computed(() => {
  if (!latestData.value) return true;
  return parseFloat(latestData.value.change_pct) >= 0;
});

const chartOptions = computed(() => ({
  chart: {
    type: 'area',
    toolbar: { show: false },
    background: 'transparent',
    parentHeightOffset: 0,
    zoom: { enabled: false },
    animations: { enabled: false }
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
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  xaxis: {
    type: 'datetime',
    labels: { style: { colors: '#9ca3af' }, datetimeUTC: false },
    axisBorder: { show: false },
    axisTicks: { show: false },
    tooltip: { enabled: false }
  },
  yaxis: {
    labels: {
      formatter: (value) => value.toFixed(0),
      style: { colors: '#9ca3af' },
    }
  },
  grid: {
    borderColor: '#374151', // gray-700
    strokeDashArray: 4,
    xaxis: { lines: { show: true } },
    yaxis: { lines: { show: true } },
    padding: { top: 0, right: 0, bottom: 0, left: 10 }
  },
  tooltip: {
    theme: 'dark',
    x: { format: 'dd MMM yyyy' },
    y: { formatter: (val) => val.toFixed(2) }
  }
}));
</script>

<template>
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 mb-8 shadow-xl">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="text-xl font-bold text-white tracking-tight">IHSG</h2>
          <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-800 text-gray-400">INDEX</span>
        </div>
        <div class="mt-1 flex items-baseline gap-3">
          <span class="text-3xl font-bold text-white">{{ latestData ? parseFloat(latestData.value).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2}) : '0' }}</span>
          <span :class="['text-sm font-semibold flex items-center', isUp ? 'text-emerald-400' : 'text-red-400']">
            <svg v-if="isUp" class="w-4 h-4 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
            <svg v-else class="w-4 h-4 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" /></svg>
            {{ latestData && parseFloat(latestData.change_abs) > 0 ? '+' : '' }}{{ latestData ? parseFloat(latestData.change_abs).toFixed(2) : '0' }} 
            ({{ latestData && parseFloat(latestData.change_pct) > 0 ? '+' : '' }}{{ latestData ? parseFloat(latestData.change_pct).toFixed(2) : '0' }}%)
          </span>
        </div>
      </div>
      
      <div class="mt-4 md:mt-0 flex flex-wrap gap-1 bg-gray-800/50 p-1 rounded-lg">
        <button 
          v-for="tf in timeframes" 
          :key="tf"
          @click="activeTimeframe = tf"
          :class="['px-3 py-1 text-xs font-medium rounded-md transition-colors', activeTimeframe === tf ? 'bg-gray-700 text-white shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-700/50']"
        >
          {{ tf }}
        </button>
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
  </div>
</template>
