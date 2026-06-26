<script setup>
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
  symbol: { type: String, default: 'ADMR' },
  price: { type: String, default: '1,330' },
  change: { type: String, default: '-230 (-14.74%) Year To Date' },
  isUp: { type: Boolean, default: false },
  tags: { type: Array, default: () => ['Minyak, Gas & Batu Bara', 'Syariah', 'Day Trade'] },
  seriesData: { type: Array, default: () => [] },
});

const color = computed(() => props.isUp ? '#10B981' : '#EF4444');

const chartOptions = computed(() => ({
  chart: {
    type: 'area',
    toolbar: { show: false },
    background: 'transparent',
    sparkline: { enabled: false },
    parentHeightOffset: 0,
    animations: {
      enabled: true,
      easing: 'easeinout',
      speed: 800,
      dynamicAnimation: {
        enabled: true,
        speed: 350
      }
    }
  },
  colors: [color.value],
  stroke: {
    curve: 'smooth',
    width: 2,
  },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.4,
      opacityTo: 0.0,
      stops: [0, 100]
    }
  },
  dataLabels: { enabled: false },
  grid: {
    show: true,
    borderColor: 'rgba(255,255,255,0.05)',
    strokeDashArray: 4,
    xaxis: { lines: { show: false } },
    yaxis: { lines: { show: true } },
    padding: { top: 10, right: 0, bottom: 0, left: 10 },
  },
  xaxis: {
    labels: { show: false },
    axisBorder: { show: false },
    axisTicks: { show: false },
    tooltip: { enabled: false },
  },
  yaxis: {
    show: true,
    opposite: true,
    labels: {
      style: { colors: '#6B7280', fontSize: '9px', fontWeight: 500 },
      formatter: (val) => val.toFixed(0),
      offsetX: -10,
    },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  annotations: {
    yaxis: [
      {
        y: props.seriesData.length ? props.seriesData[0] : 1500, // Base line
        borderColor: '#4B5563',
        strokeDashArray: 4,
        borderWidth: 1.5,
      }
    ]
  },
  tooltip: {
    theme: 'dark',
    x: { show: false },
    marker: { show: false },
  }
}));

const series = computed(() => [{
  name: props.symbol,
  data: props.seriesData.length ? props.seriesData : [1500, 1550, 1450, 1600, 1580, 1650, 1620, 1700, 1680, 1750, 1800, 2200, 1900, 1850, 1950, 1700, 1750, 1720, 1680, 1650, 1600, 1580, 1500, 1450, 1300, 1400, 1306, 1450, 1380, 1330]
}]);
</script>

<template>
  <div class="bg-[#111312] border border-[#2A302D] rounded-xl overflow-hidden flex flex-col font-sans relative">
    <div class="p-5 pb-0">
      <div class="flex justify-between items-start">
        <div>
          <h2 class="text-3xl font-bold text-white mb-1">{{ price }}</h2>
          <div class="text-[11px] font-medium flex items-center gap-1 mb-4" :class="isUp ? 'text-green-500' : 'text-red-500'">
            <svg v-if="!isUp" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="19 14 12 21 5 14"></polyline><line x1="12" y1="3" x2="12" y2="21"></line></svg>
            <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="5 10 12 3 19 10"></polyline><line x1="12" y1="21" x2="12" y2="3"></line></svg>
            {{ change }}
          </div>
          <div class="flex gap-2 flex-wrap mb-2">
            <span v-for="tag in tags" :key="tag" class="px-2 py-1 rounded border border-[#2A302D] text-[9px] text-gray-400 font-medium">
              {{ tag }}
            </span>
          </div>
        </div>
        <div class="w-10 h-10 rounded-full bg-[#1A1F1C] border border-[#2A302D] flex items-center justify-center p-2 flex-shrink-0">
          <svg viewBox="0 0 24 24" class="w-full h-full text-blue-400 opacity-80" fill="currentColor">
            <rect x="3" y="3" width="8" height="8" rx="2" />
            <rect x="13" y="3" width="8" height="8" rx="2" fill="#10B981" />
            <rect x="3" y="13" width="8" height="8" rx="2" fill="#EF4444" />
            <rect x="13" y="13" width="8" height="8" rx="2" fill="#F59E0B" />
          </svg>
        </div>
      </div>
    </div>
    
    <div class="relative h-[220px] w-full mt-2">
      <!-- High/Low Labels (absolute positioning over chart) -->
      <div class="absolute left-6 top-4 text-[9px] text-red-500 font-medium z-10">2,280</div>
      <div class="absolute right-12 bottom-8 text-[9px] text-red-500 font-medium z-10">1,306</div>
      
      <VueApexCharts type="area" height="100%" :options="chartOptions" :series="series" />
      
      <!-- Expand Icon -->
      <button class="absolute bottom-4 right-4 w-6 h-6 bg-[#1A1F1C] border border-[#2A302D] rounded flex items-center justify-center text-gray-400 hover:text-white z-10">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
      </button>
    </div>

    <div class="px-5 pb-5">
      <!-- Time periods -->
      <div class="flex justify-between items-center text-[10px] font-bold text-gray-500 mb-5 border-b border-[#2A302D]/50 pb-3">
        <button class="hover:text-white transition-colors">1D</button>
        <button class="hover:text-white transition-colors">1W</button>
        <button class="hover:text-white transition-colors">1M</button>
        <button class="hover:text-white transition-colors">3M</button>
        <button class="text-green-500 border-b-2 border-green-500 pb-2 -mb-[13px]">YTD</button>
        <button class="hover:text-white transition-colors">1Y</button>
        <button class="hover:text-white transition-colors">3Y</button>
        <button class="hover:text-white transition-colors">5Y</button>
        <div class="flex gap-2 ml-2">
          <button class="text-gray-400 hover:text-white"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg></button>
          <button class="text-green-400"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg></button>
        </div>
      </div>
      
      <!-- CTA Button -->
      <button class="w-full py-3.5 bg-[#4ADE80] hover:bg-[#34d399] text-[#064E3B] font-bold rounded-lg text-sm transition-colors shadow-[0_0_15px_rgba(74,222,128,0.2)]">
        Lihat Detail Saham
      </button>

      <!-- Tabs -->
      <div class="flex justify-between items-center text-[9px] font-bold text-gray-500 mt-5 uppercase tracking-wider">
        <button class="text-green-500 border-b-2 border-green-500 pb-2 -mb-0.5">Stream</button>
        <button class="hover:text-white pb-2">Keystats</button>
        <button class="hover:text-white pb-2">Orderbook</button>
        <button class="hover:text-white pb-2">Analisis</button>
      </div>
      <div class="border-t border-[#2A302D] -mt-0.5 mb-3"></div>

      <!-- Sub Tabs -->
      <div class="flex gap-2 text-[10px] overflow-x-auto no-scrollbar pb-1">
        <button class="px-3 py-1.5 rounded-[10px] border border-[#2A302D] text-gray-400 hover:text-white whitespace-nowrap">All</button>
        <button class="px-3 py-1.5 rounded-[10px] border border-[#2A302D] text-gray-400 hover:text-white whitespace-nowrap">Notes</button>
        <button class="px-3 py-1.5 rounded-[10px] border border-[#2A302D] text-gray-400 hover:text-white whitespace-nowrap">Berita</button>
        <button class="px-3 py-1.5 rounded-[10px] border border-green-500/50 text-green-400 bg-green-500/10 whitespace-nowrap">Laporan</button>
        <button class="px-3 py-1.5 rounded-[10px] border border-[#2A302D] text-gray-400 hover:text-white whitespace-nowrap">Riset</button>
        <button class="px-2 py-1.5 rounded-[10px] border border-[#2A302D] text-gray-400 hover:text-white ml-auto">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
