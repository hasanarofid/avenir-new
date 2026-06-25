<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  deskBrief:   { type: Object, default: null },
  snapshots:   { type: Array,  default: () => [] },
  topMovers:   { type: Object, default: () => ({ gainers: [], losers: [] }) },
  mostTraded:  { type: Array,  default: () => [] },
  apiStatus:   { type: Object, default: null },
});

// Mock data untuk komponen yang belum terhubung ke DB
const mockData = {
  date: '25 Jun 2026',
  lastUpdate: '25 Jun 2026 17:00 WIB',
  marketStance: {
    label: 'SELECTIVE RISK-ON',
    score: 42,
    prevScore: 38,
    prevDate: '24 Jun 2026',
    view: 'Constructive',
    horizon: '1-4 weeks',
  },
  headline: 'Waiting for data from Sectors.app — jalankan php artisan sectors:sync',
  subHeadline: 'Data market akan tampil setelah command sectors:sync dijalankan. Snapshot IHSG, LQ45, IDX30 akan diambil dari Sectors API dan disimpan ke database.',
  macroCards: [
    { title: 'GLOBAL GROWTH', status: 'Stable', value: '3.1%', desc: '2025E Global GDP' },
    { title: 'INFLATION (US)', status: 'Cooling', value: '2.3%', desc: 'Apr-25 PCE YoY' },
    { title: 'LIQUIDITY (G3)', status: 'Ample',   value: '$6.1T', desc: 'CB Balance Sheet' },
  ],
  regimeText: 'Selective risk-on: pasar konstruktif namun hanya untuk sektor dan saham terpilih. Likuiditas asing masih oke, yield manageable. Fokus pada quality earnings dengan domestic demand exposure.',
  drivers: [
    { rank: 1, title: 'US Rates & Dollar Direction',  impact: 'High',   dots: 3 },
    { rank: 2, title: 'Global Growth & China Demand', impact: 'High',   dots: 3 },
    { rank: 3, title: 'Indonesia Earnings Outlook',   impact: 'Medium', dots: 2 },
    { rank: 4, title: 'Liquidity & Foreign Flows',    impact: 'Medium', dots: 2 },
    { rank: 5, title: 'Commodity Prices',             impact: 'Low',    dots: 1 },
    { rank: 6, title: 'Domestic Politics & Policy',   impact: 'Low',    dots: 1 },
  ],
  sectors: [
    { name: 'Banking',                change: '+4.6%', color: 'bg-green-600' },
    { name: 'Telecom',                change: '+3.8%', color: 'bg-green-500' },
    { name: 'Consumer Staples',       change: '+3.1%', color: 'bg-green-500' },
    { name: 'Healthcare',             change: '+2.4%', color: 'bg-green-500' },
    { name: 'Energy',                 change: '+1.7%', color: 'bg-green-500' },
    { name: 'Transportation',         change: '+1.5%', color: 'bg-green-500' },
    { name: 'Property',               change: '+0.8%', color: 'bg-green-500' },
    { name: 'Infrastructure',         change: '+0.6%', color: 'bg-green-500' },
    { name: 'Industrial',             change: '-0.2%', color: 'bg-red-500' },
    { name: 'Retail',                 change: '-0.6%', color: 'bg-red-500' },
    { name: 'Basic Materials',        change: '-1.2%', color: 'bg-red-500' },
    { name: 'Technology',             change: '-1.8%', color: 'bg-red-500' },
    { name: 'Consumer Discretionary', change: '-2.1%', color: 'bg-red-500' },
    { name: 'Mining',                 change: '-3.4%', color: 'bg-red-600' },
  ],
  catalysts: [
    { date: '27 Jun', event: 'US Durable Goods Orders',          impact: 'Medium', region: 'US' },
    { date: '30 Jun', event: 'Indonesia Inflation Jun',           impact: 'High',   region: 'ID' },
    { date: '1 Jul',  event: 'China Caixin Manufacturing PMI',    impact: 'Medium', region: 'CN' },
    { date: '3 Jul',  event: 'Indonesia FX Reserves Jun',         impact: 'Low',    region: 'ID' },
    { date: '8 Jul',  event: 'BI Board of Governors Meeting',     impact: 'High',   region: 'ID' },
  ],
  risks: [
    { risk: 'Global Recession Risk',       level: 'Medium', color: 'text-yellow-400' },
    { risk: 'US Policy Error / Rates Shock', level: 'High', color: 'text-red-500' },
    { risk: 'Geopolitical Tension',        level: 'High',   color: 'text-red-500' },
    { risk: 'China Growth Slowdown',       level: 'Medium', color: 'text-yellow-400' },
    { risk: 'Commodity Price Shock',       level: 'Medium', color: 'text-yellow-400' },
    { risk: 'IDR Volatility',              level: 'Low',    color: 'text-green-500' },
    { risk: 'Domestic Policy Risk',        level: 'Low',    color: 'text-green-500' },
  ],
  analyst: {
    quote: 'Selective risk-on. Prioritaskan saham dengan domestic revenue exposure yang kuat, balance sheet bersih, dan dividend yield menarik. Banks dan Telcos tetap jadi pilihan utama di tengah kondisi pasar yang masih konstruktif.',
    author: 'Riset Avenir Research',
    role: 'Head of Market Intelligence',
    topPicks: ['BBCA', 'TLKM', 'UNVR', 'AMMN', 'PGAS'],
  },
};

const brief = computed(() => props.deskBrief || mockData);

// Snapshot: gunakan data real dari DB, fallback ke placeholder
const snapshots = computed(() => {
  if (props.snapshots && props.snapshots.length > 0) return props.snapshots;
  return [
    { symbol: 'IHSG',         value: '—', change: '—', isUp: null, ytd: '—', sparkline: [], isLive: false, lastSync: null },
    { symbol: 'LQ45',         value: '—', change: '—', isUp: null, ytd: '—', sparkline: [], isLive: false, lastSync: null },
    { symbol: 'IDX30',        value: '—', change: '—', isUp: null, ytd: '—', sparkline: [], isLive: false, lastSync: null },
    { symbol: 'USD/IDR',      value: '—', change: '—', isUp: null, ytd: '—', sparkline: [], isLive: false, lastSync: null },
    { symbol: '10Y IND YIELD', value: '—', change: '—', isUp: null, ytd: '—', sparkline: [], isLive: false, lastSync: null },
    { symbol: 'BRENT',        value: '—', change: '—', isUp: null, ytd: '—', sparkline: [], isLive: false, lastSync: null },
    { symbol: 'GOLD',         value: '—', change: '—', isUp: null, ytd: '—', sparkline: [], isLive: false, lastSync: null },
  ];
});

/** Build SVG polyline points string dari array harga. */
function buildSparklinePoints(data) {
  if (!data || data.length < 2) return null;
  const min = Math.min(...data);
  const max = Math.max(...data);
  const range = max - min || 1;
  const w = 100;
  const h = 20;
  return data
    .map((v, i) => {
      const x = (i / (data.length - 1)) * w;
      const y = h - ((v - min) / range) * h;
      return `${x.toFixed(1)},${y.toFixed(1)}`;
    })
    .join(' ');
}

const apiStatusColor = computed(() => {
  const s = props.apiStatus?.status;
  if (s === 'fresh')   return 'text-green-400';
  if (s === 'stale')   return 'text-orange-400';
  return 'text-gray-500';
});

const apiStatusLabel = computed(() => {
  const s = props.apiStatus?.status;
  if (s === 'fresh')   return '● Live EOD';
  if (s === 'stale')   return '⚠ Data Stale';
  return '○ No Sync Yet';
});
</script>

<template>
  <Head title="Desk Brief | Market Intelligence" />
  <AppLayout>
    <div class="bg-[#090b0a] min-h-screen text-gray-200 pb-16 font-sans">
      <div class="w-full max-w-[1536px] mx-auto px-6 py-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
          <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
              Desk Brief 
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            </h1>
            <p class="text-sm text-gray-400">Avenir Research Market Intelligence</p>
          </div>
          <div class="flex items-center gap-4 text-sm">
            <span class="text-gray-400">Last Update: {{ brief.lastUpdate }} <span class="cursor-pointer hover:text-white">↻</span></span>
            <button class="px-4 py-2 border border-green-500 text-green-400 rounded-md hover:bg-green-500/10 flex items-center gap-2">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg>
              Share Desk Brief
            </button>
            <button class="px-4 py-2 border border-[#3A403D] rounded-md hover:bg-[#2A302D] flex items-center gap-2">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
              Print / PDF
            </button>
            <button class="p-2 border border-[#3A403D] rounded-md hover:bg-[#2A302D]">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
            </button>
          </div>
        </div>

        <!-- Headline Strip -->
        <div class="bg-[#151917] border border-[#2A302D] rounded-xl p-6 mb-4 flex flex-col lg:flex-row gap-8">
          <!-- Market Stance -->
          <div class="flex-shrink-0 w-48">
            <h3 class="text-[10px] font-semibold text-gray-500 tracking-widest uppercase mb-2">Market Stance</h3>
            <div class="flex items-center gap-3 mb-3">
              <span class="text-3xl font-bold text-green-400">{{ brief.marketStance.label }}</span>
              <div class="w-8 h-8 rounded-full border-2 border-green-400 flex items-center justify-center text-green-400">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"></polyline></svg>
              </div>
            </div>
            <div class="text-xs text-gray-400 space-y-1">
              <p>Tactical view: <span class="text-white">{{ brief.marketStance.view }}</span></p>
              <p>Time horizon: <span class="text-white">{{ brief.marketStance.horizon }}</span></p>
            </div>
          </div>
          
          <!-- Headline Details -->
          <div class="flex-1 border-l border-[#2A302D] pl-8">
            <h3 class="text-[10px] font-semibold text-gray-500 tracking-widest uppercase mb-2">Desk Brief Headline</h3>
            <h2 class="text-xl font-bold text-white mb-2">{{ brief.headline }}</h2>
            <p class="text-sm text-gray-400 leading-relaxed">{{ brief.subHeadline }}</p>
          </div>

          <!-- Macro Cards -->
          <div class="flex gap-8 border-l border-[#2A302D] pl-8">
            <div v-for="macro in brief.macroCards" :key="macro.title" class="flex flex-col justify-between">
              <h3 class="text-[10px] font-semibold text-gray-500 tracking-widest uppercase flex items-center gap-1">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                {{ macro.title }}
              </h3>
              <p class="text-xs text-gray-300 mt-2">{{ macro.status }}</p>
              <p class="text-2xl font-bold text-white mt-1">{{ macro.value }}</p>
              <p class="text-[10px] text-gray-500 mt-1">{{ macro.desc }}</p>
            </div>
          </div>
        </div>

        <!-- Grid Row 1 (Modules 1-4) -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-12 gap-4 mb-4">
          <!-- 1. Regime Summary -->
          <div class="bg-[#151917] border border-[#2A302D] rounded-xl p-5 xl:col-span-3 flex flex-col justify-between">
            <div>
              <div class="flex justify-between items-center mb-5">
                <h3 class="text-[11px] font-semibold text-gray-300 tracking-wider uppercase">1. Regime Summary <span class="text-gray-500 ml-1">ⓘ</span></h3>
              </div>
              <div class="flex gap-4 items-center">
                <!-- Circular Progress SVG -->
                <div class="relative w-24 h-24 flex-shrink-0 flex items-center justify-center">
                  <svg class="transform -rotate-90 w-24 h-24">
                    <circle cx="48" cy="48" r="40" stroke="currentColor" stroke-width="6" fill="transparent" class="text-gray-700" />
                    <circle cx="48" cy="48" r="40" stroke="currentColor" stroke-width="6" fill="transparent" :stroke-dasharray="251.2" :stroke-dashoffset="251.2 - (251.2 * brief.marketStance.score) / 100" class="text-green-500" stroke-linecap="round" />
                  </svg>
                  <div class="absolute flex flex-col items-center justify-center">
                    <span class="text-[8px] text-gray-400 uppercase tracking-widest mb-0.5">Regime Score</span>
                    <div class="flex items-baseline">
                      <span class="text-2xl font-bold text-green-400">{{ brief.marketStance.score }}</span>
                      <span class="text-xs text-gray-500">/100</span>
                    </div>
                    <span class="text-[10px] text-green-400 font-medium mt-0.5">{{ brief.marketStance.label }}</span>
                  </div>
                </div>
                <div>
                  <h4 class="text-xs font-bold text-white mb-1.5 leading-snug">Disinflation + Growth Support</h4>
                  <p class="text-[10px] text-gray-400 leading-relaxed">{{ brief.regimeText }}</p>
                </div>
              </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#2A302D] text-[10px] text-gray-500 flex justify-between items-center">
              <span>Prev. Score: {{ brief.marketStance.prevScore }} ({{ brief.marketStance.prevDate }})</span>
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-green-400"><polyline points="18 15 12 9 6 15"></polyline></svg>
            </div>
          </div>

          <!-- 2. Key Drivers -->
          <div class="bg-[#151917] border border-[#2A302D] rounded-xl p-5 xl:col-span-2">
            <div class="flex justify-between items-center mb-5">
              <h3 class="text-[11px] font-semibold text-gray-300 tracking-wider uppercase">2. Key Drivers <span class="normal-case text-gray-500 font-normal text-[10px] ml-1">(Ranked by Impact)</span></h3>
            </div>
            <div class="space-y-3.5">
              <div v-for="driver in brief.drivers" :key="driver.rank" class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <span class="w-5 h-5 rounded-full bg-[#2A302D] text-gray-400 text-[10px] flex items-center justify-center flex-shrink-0">{{ driver.rank }}</span>
                  <span class="text-[11px] text-gray-300 leading-tight">{{ driver.title }}</span>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                  <span class="text-[10px] text-gray-500">{{ driver.impact }}</span>
                  <div class="flex gap-0.5">
                    <span v-for="n in 3" :key="n" class="w-1 h-1 rounded-full" :class="n <= driver.dots ? 'bg-green-500' : 'bg-gray-700'"></span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 3. Cross-Asset Snapshot -->
          <div class="bg-[#151917] border border-[#2A302D] rounded-xl p-5 xl:col-span-3 flex flex-col justify-between">
            <div>
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-[11px] font-semibold text-gray-300 tracking-wider uppercase">3. Cross-Asset Snapshot</h3>
                <span :class="apiStatusColor" class="text-[9px] font-medium tracking-wide">{{ apiStatusLabel }}</span>
              </div>

              <!-- IDX Indices (data real dari Sectors API) -->
              <div class="mb-3">
                <p class="text-[9px] text-gray-600 uppercase tracking-widest mb-2">IDX Indices — Sectors.app EOD</p>
                <div class="grid grid-cols-3 gap-2 mb-3">
                  <div
                    v-for="snap in snapshots.filter(s => ['IHSG','LQ45','IDX30'].includes(s.symbol))"
                    :key="snap.symbol"
                    class="bg-[#0f1210] border border-[#2A302D] rounded-lg p-2.5 flex flex-col items-center"
                  >
                    <!-- Symbol + LIVE badge -->
                    <div class="flex items-center gap-1 mb-1">
                      <span class="text-[9px] text-gray-400 tracking-wider">{{ snap.symbol }}</span>
                      <span v-if="snap.isLive" class="text-[7px] text-green-400 font-semibold">● EOD</span>
                    </div>
                    <!-- Value -->
                    <div class="text-sm font-bold text-white mb-0.5">
                      {{ snap.value }}
                    </div>
                    <!-- Change -->
                    <div
                      class="text-[10px] font-semibold mb-1.5"
                      :class="snap.isUp === true ? 'text-green-400' : snap.isUp === false ? 'text-red-400' : 'text-gray-500'"
                    >
                      {{ snap.change }}
                    </div>
                    <!-- Sparkline real -->
                    <svg viewBox="0 0 100 20" class="w-full h-5" preserveAspectRatio="none">
                      <template v-if="snap.sparkline && snap.sparkline.length >= 2">
                        <polyline
                          :points="buildSparklinePoints(snap.sparkline)"
                          fill="none"
                          :stroke="snap.isUp ? '#4ade80' : '#f87171'"
                          stroke-width="1.5"
                          vector-effect="non-scaling-stroke"
                        />
                      </template>
                      <template v-else>
                        <!-- Placeholder dashed line jika belum ada data -->
                        <line x1="0" y1="10" x2="100" y2="10" stroke="#374151" stroke-width="1" stroke-dasharray="3,2" />
                      </template>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Other Assets (static / manual) -->
              <p class="text-[9px] text-gray-600 uppercase tracking-widest mb-2">Other Assets</p>
              <div class="grid grid-cols-4 gap-1.5">
                <div
                  v-for="snap in snapshots.filter(s => !['IHSG','LQ45','IDX30'].includes(s.symbol))"
                  :key="snap.symbol"
                  class="text-center"
                >
                  <div class="text-[9px] text-gray-500 mb-0.5 truncate">{{ snap.symbol }}</div>
                  <div class="text-[11px] font-bold text-white">{{ snap.value }}</div>
                  <div
                    class="text-[9px]"
                    :class="snap.isUp === true ? 'text-green-400' : snap.isUp === false ? 'text-red-400' : 'text-gray-600'"
                  >{{ snap.change }}</div>
                </div>
              </div>
            </div>

            <div class="mt-4 pt-3 flex justify-between text-[9px] text-gray-600">
              <span>Source: Sectors.app (IDX) · Manual (others)</span>
              <span>{{ props.apiStatus?.lastSync ?? brief.lastUpdate }}</span>
            </div>
          </div>
          
          <!-- 4. Smart Money / Broker Flow -->
          <div class="bg-[#151917] border border-[#2A302D] rounded-xl p-5 xl:col-span-4 flex flex-col justify-between">
            <div class="flex justify-between items-center mb-5">
              <h3 class="text-[11px] font-semibold text-gray-300 tracking-wider uppercase">4. Smart Money / Broker Flow <span class="text-gray-500 ml-1">ⓘ</span></h3>
              <div class="flex text-[9px] bg-[#0A0D0B] rounded-sm overflow-hidden border border-[#2A302D]">
                <button class="px-2 py-1 bg-[#2A302D] text-white">6M</button>
                <button class="px-2 py-1 text-gray-400 hover:text-white">1Y</button>
                <button class="px-2 py-1 text-gray-400 hover:text-white">3Y</button>
              </div>
            </div>
            <div class="flex-1 relative">
              <div class="text-center text-[10px] text-gray-400 mb-2">IHSG Smart Money: Accumulation & Cost Basis</div>
              <div class="flex justify-center gap-4 text-[9px] text-gray-400 mb-3">
                <span class="flex items-center gap-1"><span class="w-2 h-0.5 bg-white"></span> IHSG Price (LHS)</span>
                <span class="flex items-center gap-1"><span class="w-2 h-0.5 bg-red-500 border-b border-dashed border-red-500"></span> Running Cost Basis (LHS)</span>
                <span class="flex items-center gap-1"><span class="w-2 h-0.5 bg-blue-400"></span> Cumulative Net Inventory (RHS)</span>
              </div>
              
              <!-- Chart placeholder area -->
              <div class="h-32 w-full border-b border-l border-[#2A302D] relative flex items-end">
                <!-- Grid lines -->
                <div class="absolute inset-0 flex flex-col justify-between pointer-events-none">
                  <div class="border-t border-[#2A302D]/50 w-full h-0"></div>
                  <div class="border-t border-[#2A302D]/50 w-full h-0"></div>
                  <div class="border-t border-[#2A302D]/50 w-full h-0"></div>
                  <div class="border-t border-[#2A302D]/50 w-full h-0"></div>
                </div>
                <!-- Mock Chart Lines -->
                <svg viewBox="0 0 100 40" class="w-full h-full absolute inset-0 overflow-visible" preserveAspectRatio="none">
                  <!-- Area -->
                  <path d="M0,35 L20,30 L40,32 L60,15 L80,20 L100,5 L100,40 L0,40 Z" fill="rgba(96,165,250,0.1)"></path>
                  <!-- Net Inventory (Blue) -->
                  <polyline points="0,35 20,30 40,32 60,15 80,20 100,5" fill="none" stroke="#60a5fa" stroke-width="1.5" vector-effect="non-scaling-stroke"></polyline>
                  <!-- Price (White) -->
                  <polyline points="0,15 20,25 40,10 60,5 80,20 100,2" fill="none" stroke="#ffffff" stroke-width="1.5" vector-effect="non-scaling-stroke"></polyline>
                  <!-- Cost Basis (Red Dashed) -->
                  <polyline points="0,20 20,22 40,18 60,12 80,15 100,8" fill="none" stroke="#ef4444" stroke-dasharray="2" stroke-width="1.5" vector-effect="non-scaling-stroke"></polyline>
                </svg>
              </div>
              <div class="flex justify-between text-[8px] text-gray-500 mt-1">
                <span>Dec '24</span><span>Jan '25</span><span>Feb '25</span><span>Mar '25</span><span>Apr '25</span><span>May '25</span>
              </div>
              
              <!-- Stats Row -->
              <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-[#2A302D]">
                <div>
                  <div class="text-[9px] text-gray-500">Cumulative Net Inventory (6M)</div>
                  <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-sm font-bold text-blue-400">+28.4 Tn</span>
                    <span class="text-[9px] text-green-400">vs 1M ago +12.6 Tn</span>
                  </div>
                </div>
                <div>
                  <div class="text-[9px] text-gray-500">Running Cost Basis</div>
                  <div class="text-sm font-bold text-red-400 mt-0.5">6,812</div>
                </div>
                <div>
                  <div class="text-[9px] text-gray-500">Price vs Cost Basis</div>
                  <div class="text-sm font-bold text-green-400 mt-0.5">+5.9%</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Grid Row 2 (Modules 5-8) -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-12 gap-4">
          <!-- 5. Sector Rotation Heatmap -->
          <div class="bg-[#151917] border border-[#2A302D] rounded-xl p-5 xl:col-span-3 flex flex-col justify-between">
            <h3 class="text-[11px] font-semibold text-gray-300 tracking-wider uppercase mb-4">5. Sector Rotation <span class="normal-case text-gray-500 font-normal text-[10px] ml-1">(Heatmap - 1M Chg%)</span></h3>
            <div class="grid grid-cols-4 gap-1">
              <div v-for="sec in brief.sectors" :key="sec.name" 
                   :class="`p-2 flex flex-col items-center justify-center text-center ${sec.color} rounded-sm opacity-90 hover:opacity-100 cursor-pointer transition-opacity`" 
                   :style="(sec.name === 'Banking' || sec.name === 'Consumer Discretionary') ? 'grid-column: span 1; grid-row: span 2;' : ''">
                <span class="text-[9px] text-white/90 font-medium leading-tight mb-1" :class="{'break-words whitespace-normal' : true}">{{ sec.name }}</span>
                <span class="text-[10px] font-bold text-white">{{ sec.change }}</span>
              </div>
            </div>
            <div class="flex justify-between items-center mt-4 text-[9px] text-gray-500">
              <span>-5%</span>
              <div class="flex-1 mx-2 h-1 bg-gradient-to-r from-red-600 via-gray-700 to-green-600 rounded"></div>
              <span>+5%</span>
            </div>
            <div class="mt-2 flex justify-between text-[9px] text-gray-600">
              <span>Source: Avenir Research</span>
              <span>As of {{ brief.date }}</span>
            </div>
          </div>

          <!-- 6. Catalyst Calendar -->
          <div class="bg-[#151917] border border-[#2A302D] rounded-xl p-5 xl:col-span-3 flex flex-col justify-between">
            <div>
              <h3 class="text-[11px] font-semibold text-gray-300 tracking-wider uppercase mb-4">6. Catalyst Calendar</h3>
              <div class="flex border-b border-[#2A302D] mb-3">
                <button class="px-3 py-1.5 text-[10px] font-medium text-green-400 border-b-2 border-green-400">Upcoming</button>
                <button class="px-3 py-1.5 text-[10px] font-medium text-gray-500 hover:text-gray-300">Past Events</button>
              </div>
              <table class="w-full text-[10px] text-left text-gray-300">
                <thead>
                  <tr class="text-gray-500 border-b border-[#2A302D]/30">
                    <th class="pb-2 font-normal">Date</th>
                    <th class="pb-2 font-normal">Event</th>
                    <th class="pb-2 font-normal text-right">Impact</th>
                    <th class="pb-2 font-normal text-center">Region</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="cat in brief.catalysts" :key="cat.event" class="border-b border-[#2A302D]/30 last:border-0">
                    <td class="py-2.5 whitespace-nowrap">{{ cat.date }}</td>
                    <td class="py-2.5 pr-2 leading-tight">{{ cat.event }}</td>
                    <td class="py-2.5 text-right whitespace-nowrap">
                      <span :class="cat.impact === 'High' ? 'text-red-400' : (cat.impact === 'Medium' ? 'text-yellow-400' : 'text-green-400')">{{ cat.impact }}</span>
                      <span class="inline-block w-1.5 h-1.5 rounded-full ml-1" :class="cat.impact === 'High' ? 'bg-red-400' : (cat.impact === 'Medium' ? 'bg-yellow-400' : 'bg-green-400')"></span>
                    </td>
                    <td class="py-2.5 text-center text-gray-500">{{ cat.region }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <button class="mt-4 text-[10px] text-green-400 hover:text-green-300 text-left font-medium">View Full Calendar →</button>
          </div>

          <!-- 7. Risk Monitor -->
          <div class="bg-[#151917] border border-[#2A302D] rounded-xl p-5 xl:col-span-2 flex flex-col justify-between">
            <div>
              <h3 class="text-[11px] font-semibold text-gray-300 tracking-wider uppercase mb-5">7. Risk Monitor</h3>
              <div class="space-y-3.5">
                <div v-for="risk in brief.risks" :key="risk.risk" class="flex justify-between items-center text-[10px]">
                  <span class="text-gray-300 leading-tight pr-2">{{ risk.risk }}</span>
                  <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full" :class="risk.level === 'High' ? 'bg-red-500' : (risk.level === 'Medium' ? 'bg-yellow-400' : 'bg-green-500')"></span>
                    <span :class="risk.color" class="w-10">{{ risk.level }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="mt-5 pt-3 border-t border-[#2A302D] flex justify-between items-center text-sm">
              <span class="text-[10px] text-gray-400">Risk Index (Composite)</span>
              <div class="text-xl font-bold text-yellow-400 flex items-baseline gap-0.5">55<span class="text-[10px] text-gray-500 font-normal">/100</span></div>
            </div>
          </div>

          <!-- 8. Analyst Takeaway -->
          <div class="bg-[#151917] border border-[#2A302D] rounded-xl p-5 xl:col-span-4 flex flex-col justify-between">
            <div>
              <h3 class="text-[11px] font-semibold text-gray-300 tracking-wider uppercase mb-4">8. Analyst Takeaway</h3>
              <div class="relative mt-2">
                <span class="absolute -top-4 -left-2 text-5xl text-green-500/20 leading-none font-serif">"</span>
                <p class="text-[13px] text-gray-300 italic leading-relaxed z-10 relative pl-4">{{ brief.analyst.quote }}</p>
              </div>
              <div class="flex items-center gap-3 mt-6 pl-4">
                <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center overflow-hidden border border-[#2A302D]">
                  <img src="/images/avatar-placeholder.png" alt="AR" class="w-full h-full object-cover opacity-50" onerror="this.src=''; this.className='hidden'" />
                  <span class="text-xs absolute font-medium">AR</span>
                </div>
                <div>
                  <div class="text-[11px] font-semibold text-white">{{ brief.analyst.author }}</div>
                  <div class="text-[10px] text-gray-500">{{ brief.analyst.role }}</div>
                </div>
              </div>
            </div>
            <div class="mt-6 flex flex-wrap items-center gap-3 text-[10px] border-t border-[#2A302D] pt-4">
              <span class="text-green-400 font-semibold tracking-widest uppercase">Top Picks</span>
              <div class="flex gap-2 flex-wrap">
                <span v-for="pick in brief.analyst.topPicks" :key="pick" class="px-2 py-1 bg-[#2A302D] text-white rounded font-medium">{{ pick }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Top Movers Strip (data real dari Sectors API) -->
        <div v-if="topMovers.gainers?.length || topMovers.losers?.length" class="mt-4 bg-[#151917] border border-[#2A302D] rounded-xl p-4">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-[11px] font-semibold text-gray-300 tracking-wider uppercase">
              Top Movers IDX
              <span class="normal-case text-gray-500 font-normal text-[10px] ml-1">(1D — Sectors.app EOD)</span>
            </h3>
            <span :class="apiStatusColor" class="text-[9px]">{{ apiStatusLabel }}</span>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <!-- Gainers -->
            <div>
              <p class="text-[9px] text-green-500 uppercase tracking-widest font-semibold mb-2">▲ Top Gainers</p>
              <div class="space-y-1.5">
                <div
                  v-for="(mover, i) in (topMovers.gainers || []).slice(0, 5)"
                  :key="i"
                  class="flex items-center justify-between text-[10px]"
                >
                  <span class="font-semibold text-white">{{ mover.symbol }}</span>
                  <span class="text-green-400 font-medium">+{{ Number(mover.price_pct ?? 0).toFixed(2) }}%</span>
                </div>
              </div>
            </div>
            <!-- Losers -->
            <div>
              <p class="text-[9px] text-red-500 uppercase tracking-widest font-semibold mb-2">▼ Top Losers</p>
              <div class="space-y-1.5">
                <div
                  v-for="(mover, i) in (topMovers.losers || []).slice(0, 5)"
                  :key="i"
                  class="flex items-center justify-between text-[10px]"
                >
                  <span class="font-semibold text-white">{{ mover.symbol }}</span>
                  <span class="text-red-400 font-medium">{{ Number(mover.price_pct ?? 0).toFixed(2) }}%</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="mt-6 pt-4 border-t border-[#2A302D] flex flex-col sm:flex-row justify-between gap-3 text-[10px] text-gray-500">
          <p>Informasi ini bersifat umum dan tidak merupakan rekomendasi investasi. Data bersumber dari Sectors.app dan diolah oleh Avenir Research.</p>
          <div class="flex items-center gap-4 flex-shrink-0">
            <!-- API Status indicator -->
            <div v-if="props.apiStatus" class="flex items-center gap-1.5">
              <span
                class="w-1.5 h-1.5 rounded-full"
                :class="{
                  'bg-green-400': props.apiStatus.status === 'fresh',
                  'bg-orange-400': props.apiStatus.status === 'stale',
                  'bg-gray-600': props.apiStatus.status === 'no_data',
                }"
              ></span>
              <span class="text-gray-500">
                {{ props.apiStatus.provider }}
                <template v-if="props.apiStatus.lastSync"> · {{ props.apiStatus.lastSync }}</template>
                <template v-if="props.apiStatus.creditsToday"> · {{ props.apiStatus.creditsToday }} credits today</template>
              </span>
            </div>
            <span class="text-green-400 font-medium">Avenir Research</span>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
