<script setup>
import { computed, ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  deskBrief:  { type: Object, default: null },
  snapshots:  { type: Array,  default: () => [] },
  topMovers:  { type: Object, default: () => ({ gainers: [], losers: [] }) },
  mostTraded: { type: Array,  default: () => [] },
  apiStatus:  { type: Object, default: null },
});

// ──────────────────────────────────────────────
// Mock / fallback data (dipakai jika belum ada DeskBrief record published di DB)
// ──────────────────────────────────────────────
const mockData = {
  date: '27 Jun 2026',
  lastUpdate: '27 Jun 2026 17:00 WIB',
  marketStance: {
    label: 'SELECTIVE RISK-ON',
    score: 42,
    prevScore: 38,
    prevDate: '26 Jun 2026',
    view: 'Constructive',
    horizon: '1-4 weeks',
  },
  headline: 'Pasar konstruktif di tengah perbaikan global; Indonesia masih menarik bagi asing',
  subHeadline: 'Perbaikan sentimen global dan tekanan inflasi yang melandai mendukung risk appetite. IHSG berpotensi menguat selektif, didorong sektor Banking, Telco, dan Consumer Staples dengan fundamental solid.',
  macroCards: [
    { title: 'GLOBAL GROWTH', status: 'Stable',  value: '3.1%',  desc: '2025E Global GDP',   icon: '🌐' },
    { title: 'INFLATION (US)', status: 'Cooling', value: '2.3%',  desc: 'Apr-25 PCE YoY',    icon: '📊' },
    { title: 'LIQUIDITY (G3)', status: 'Ample',   value: '$6.1T', desc: 'CB Balance Sheet',   icon: '💧' },
  ],
  regimeText: 'Selective risk-on: pasar konstruktif namun hanya untuk sektor dan saham terpilih. Likuiditas asing masih oke, yield manageable. Fokus pada quality earnings dengan domestic demand exposure.',
  drivers: [
    { rank: 1, title: 'US Rates & Dollar Direction',   impact: 'High',   dots: 3, category: 'Macro/Rate',    explanation: 'Fed hawkish pause menekan DXY; positive for EM flows.' },
    { rank: 2, title: 'Global Growth & China Demand',  impact: 'High',   dots: 3, category: 'Flow',          explanation: 'China stimulus incremental mendukung komoditas & risk sentiment.' },
    { rank: 3, title: 'Indonesia Earnings Outlook',    impact: 'Medium', dots: 2, category: 'Earnings',      explanation: 'Q1 results mixed; Banks & Telco beat, Consumer miss.' },
    { rank: 4, title: 'Liquidity & Foreign Flows',     impact: 'Medium', dots: 2, category: 'Flow',          explanation: 'Foreign net buy di SBN; ekuitas masih volatile.' },
    { rank: 5, title: 'Commodity Prices',              impact: 'Low',    dots: 1, category: 'Commodity',     explanation: 'CPO & coal sideways; neutral untuk mining/plantation.' },
    { rank: 6, title: 'Domestic Politics & Policy',   impact: 'Low',    dots: 1, category: 'Policy',        explanation: 'Kabinet stabil; tidak ada kejutan kebijakan signifikan.' },
  ],
  sectors: [
    { name: 'Banking',                change: 4.6,  color: '#16a34a' },
    { name: 'Telecom',                change: 3.8,  color: '#22c55e' },
    { name: 'Consumer Staples',       change: 3.1,  color: '#22c55e' },
    { name: 'Healthcare',             change: 2.4,  color: '#4ade80' },
    { name: 'Energy',                 change: 1.7,  color: '#86efac' },
    { name: 'Transportation',         change: 1.5,  color: '#86efac' },
    { name: 'Property',               change: 0.8,  color: '#bbf7d0' },
    { name: 'Infrastructure',         change: 0.6,  color: '#bbf7d0' },
    { name: 'Industrial',             change: -0.2, color: '#fecaca' },
    { name: 'Retail',                 change: -0.6, color: '#fca5a5' },
    { name: 'Basic Materials',        change: -1.2, color: '#f87171' },
    { name: 'Technology',             change: -1.8, color: '#ef4444' },
    { name: 'Consumer Discretionary', change: -2.1, color: '#dc2626' },
    { name: 'Mining',                 change: -3.4, color: '#b91c1c' },
  ],
  catalysts: [
    { date: '27 Jun', event: 'US Durable Goods Orders',       impact: 'Medium', region: 'US', isPast: false },
    { date: '30 Jun', event: 'Indonesia Inflation Jun',         impact: 'High',   region: 'ID', isPast: false },
    { date: '1 Jul',  event: 'China Caixin Manufacturing PMI', impact: 'Medium', region: 'CN', isPast: false },
    { date: '3 Jul',  event: 'Indonesia FX Reserves Jun',      impact: 'Low',    region: 'ID', isPast: false },
    { date: '8 Jul',  event: 'BI Board of Governors Meeting',  impact: 'High',   region: 'ID', isPast: false },
    { date: '23 Mei', event: 'Indonesia GDP 1Q25 (Final)',     impact: 'High',   region: 'ID', isPast: true },
    { date: '27 Mei', event: 'US Durable Goods Orders Apr',   impact: 'Medium', region: 'US', isPast: true },
    { date: '28 Mei', event: 'FOMC Minutes (Mei)',             impact: 'Medium', region: 'US', isPast: true },
  ],
  risks: [
    { risk: 'Global Recession Risk',        level: 'Medium' },
    { risk: 'US Policy Error / Rates Shock', level: 'High'   },
    { risk: 'Geopolitical Tension',         level: 'High'   },
    { risk: 'China Growth Slowdown',        level: 'Medium' },
    { risk: 'Commodity Price Shock',        level: 'Medium' },
    { risk: 'IDR Volatility',              level: 'Low'    },
    { risk: 'Domestic Policy Risk',        level: 'Low'    },
  ],
  riskIndex: 55,
  smartMoney: {
    cumulativeNet: '+28.4 Tn',
    cumulativeVs: '+12.6 Tn',
    costBasis: '6,812',
    priceVsCost: '+5.9%',
  },
  analyst: {
    quote: 'Selective risk-on. Prioritaskan saham dengan domestic revenue exposure yang kuat, balance sheet bersih, dan dividend yield menarik. Banks dan Telcos tetap jadi pilihan utama di tengah kondisi pasar yang masih konstruktif.',
    author: 'Riset Avenir Research',
    role: 'Head of Market Intelligence',
    topPicks: ['BBCA', 'TLKM', 'UNVR', 'AMMN', 'PGAS'],
  },
};

// ──────────────────────────────────────────────
// State / Reactive
// ──────────────────────────────────────────────
const brief        = computed(() => props.deskBrief || mockData);
const catalystTab  = ref('upcoming');   // 'upcoming' | 'past'
const smPeriod     = ref('6M');         // '6M' | '1Y' | '3Y'
const hoveredSector = ref(null);
const tooltipPos   = ref({ x: 0, y: 0 });
const animatedScore = ref(0);
const shared       = ref(false);

const showCalendarModal = ref(false);

// Animated score on mount
onMounted(() => {
  const target = brief.value.marketStance.score;
  const duration = 1200;
  const step = 16;
  const increment = target / (duration / step);
  let current = 0;
  const timer = setInterval(() => {
    current = Math.min(current + increment, target);
    animatedScore.value = Math.round(current);
    if (current >= target) clearInterval(timer);
  }, step);
});

// ──────────────────────────────────────────────
// Snapshots: real data dari DB, fallback placeholder
// ──────────────────────────────────────────────
const idxSnapshots = computed(() => {
  const fromDb = props.snapshots.filter(s => ['IHSG', 'LQ45', 'IDX30'].includes(s.symbol));
  if (fromDb.length > 0) return fromDb;
  return [
    { symbol: 'IHSG',  value: '—', change: '—', isUp: null, sparkline: [], isLive: false },
    { symbol: 'LQ45',  value: '—', change: '—', isUp: null, sparkline: [], isLive: false },
    { symbol: 'IDX30', value: '—', change: '—', isUp: null, sparkline: [], isLive: false },
  ];
});

const otherSnapshots = computed(() =>
  props.snapshots.filter(s => !['IHSG', 'LQ45', 'IDX30'].includes(s.symbol))
);

// Buat sparkline SVG points dari array harga
function buildSparklinePoints(data) {
  if (!Array.isArray(data) || data.length < 2) return null;
  const min = Math.min(...data);
  const max = Math.max(...data);
  const range = max - min || 1;
  return data.map((v, i) => {
    const x = (i / (data.length - 1)) * 100;
    const y = 20 - ((v - min) / range) * 18; // 18px height, 1px padding
    return `${x.toFixed(1)},${y.toFixed(1)}`;
  }).join(' ');
}

// Data min/max IHSG untuk chart Smart Money
const ihsgStats = computed(() => {
  const ihsg = idxSnapshots.value.find(s => s.symbol === 'IHSG');
  const data = ihsg?.sparkline ?? [];
  if (data.length < 2) return null;
  const min = Math.min(...data);
  const max = Math.max(...data);
  const minIdx = data.indexOf(min);
  const maxIdx = data.indexOf(max);
  return {
    min, max,
    minX: (minIdx / (data.length - 1)) * 100,
    maxX: (maxIdx / (data.length - 1)) * 100,
  };
});

// ──────────────────────────────────────────────
// API Status
// ──────────────────────────────────────────────
const apiStatus = computed(() => props.apiStatus);

const apiStatusDot = computed(() => ({
  fresh:   'bg-green-400',
  stale:   'bg-orange-400',
  no_data: 'bg-gray-500',
}[apiStatus.value?.status] ?? 'bg-gray-600'));

const apiStatusText = computed(() => ({
  fresh:   '● Live EOD',
  stale:   '⚠ Data Stale',
  no_data: '○ No Sync Yet',
}[apiStatus.value?.status] ?? '○ Offline'));

const apiStatusColor = computed(() => ({
  fresh:   'text-green-400',
  stale:   'text-orange-400',
  no_data: 'text-gray-500',
}[apiStatus.value?.status] ?? 'text-gray-600'));

// ──────────────────────────────────────────────
// Market Stance helpers
// ──────────────────────────────────────────────
const scoreColor = computed(() => {
  const s = brief.value.marketStance.score;
  if (s >= 40) return { text: 'text-green-400', stroke: '#4ade80', label: 'text-green-400' };
  if (s >= 10) return { text: 'text-emerald-400', stroke: '#34d399', label: 'text-emerald-400' };
  if (s >= -9) return { text: 'text-yellow-400', stroke: '#facc15', label: 'text-yellow-400' };
  if (s >= -39) return { text: 'text-orange-400', stroke: '#fb923c', label: 'text-orange-400' };
  return { text: 'text-red-400', stroke: '#f87171', label: 'text-red-400' };
});

// Circle progress: r=38 → circumference ≈ 238.76
const circumference = 238.76;
const dashOffset = computed(() =>
  circumference - (circumference * Math.abs(animatedScore.value)) / 100
);

// ──────────────────────────────────────────────
// Catalyst Calendar
// ──────────────────────────────────────────────
const filteredCatalysts = computed(() =>
  (brief.value.catalysts || []).filter(c =>
    catalystTab.value === 'upcoming' ? !c.isPast : c.isPast
  )
);

const impactStyle = (impact) => ({
  High:   { text: 'text-red-400',    dot: 'bg-red-400' },
  Medium: { text: 'text-yellow-400', dot: 'bg-yellow-400' },
  Low:    { text: 'text-green-400',  dot: 'bg-green-400' },
}[impact] ?? { text: 'text-gray-400', dot: 'bg-gray-400' });

// ──────────────────────────────────────────────
// Risk Monitor
// ──────────────────────────────────────────────
const riskLevelStyle = (level) => ({
  High:   { text: 'text-red-400',    dot: 'bg-red-500' },
  Medium: { text: 'text-yellow-400', dot: 'bg-yellow-400' },
  Low:    { text: 'text-green-400',  dot: 'bg-green-500' },
}[level] ?? { text: 'text-gray-400', dot: 'bg-gray-500' });

const riskIndex = computed(() => brief.value.riskIndex ?? 55);

const riskIndexColor = computed(() => {
  const r = riskIndex.value;
  if (r >= 70) return 'text-red-400';
  if (r >= 40) return 'text-yellow-400';
  return 'text-green-400';
});

// ──────────────────────────────────────────────
// Top Movers
// ──────────────────────────────────────────────
const gainers = computed(() => (props.topMovers?.gainers || []).slice(0, 5));
const losers  = computed(() => (props.topMovers?.losers  || []).slice(0, 5));
const hasMovers = computed(() => gainers.value.length > 0 || losers.value.length > 0);

// ──────────────────────────────────────────────
// Actions

// ──────────────────────────────────────────────
function shareLink() {
  navigator.clipboard?.writeText(window.location.href).then(() => {
    shared.value = true;
    setTimeout(() => (shared.value = false), 2000);
  });
}

function printPage() {
  window.print();
}

// Sector heatmap hover
function onSectorHover(sec, e) {
  hoveredSector.value = sec;
  tooltipPos.value = { x: e.clientX, y: e.clientY };
}
function onSectorLeave() {
  hoveredSector.value = null;
}

// Sector heatmap color dari change value
function sectorBg(change) {
  if (change >= 4)   return '#15803d';
  if (change >= 2)   return '#16a34a';
  if (change >= 0.5) return '#22c55e';
  if (change >= 0)   return '#4ade80';
  if (change >= -0.5) return '#fca5a5';
  if (change >= -2)  return '#f87171';
  if (change >= -4)  return '#ef4444';
  return '#dc2626';
}
</script>

<template>
  <Head>
    <title>Desk Brief | Market Intelligence</title>
    <meta name="description" content="Desk Brief Avenir Research - Rangkuman intelijen pasar, rotasi sektor, smart money flow, dan katalis harian (real-time)." />
    <meta property="og:title" content="Desk Brief | Market Intelligence" />
    <meta property="og:description" content="Desk Brief Avenir Research - Rangkuman intelijen pasar, rotasi sektor, smart money flow, dan katalis harian (real-time)." />
    <meta property="og:type" content="website" />
    <meta name="twitter:card" content="summary_large_image" />
    
    <!-- GEO Tags -->
    <meta name="geo.region" content="ID" />
    <meta name="geo.placename" content="Indonesia" />
    <meta name="geo.position" content="-0.789275;113.921327" />
    <meta name="ICBM" content="-0.789275, 113.921327" />
    <meta name="language" content="id-ID" />
    <meta name="view-transition" content="same-origin" />
  </Head>

  <AppLayout>
    <div class="bg-[#090b0a] min-h-screen text-gray-200 pb-16 font-sans">
      <div class="w-full max-w-[1536px] mx-auto px-4 lg:px-6 py-5">

        <!-- ── HEADER ─────────────────────────────────────────── -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-5 gap-3">
          <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
              Desk Brief
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-500 cursor-pointer hover:text-yellow-400 transition-colors"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </h1>
            <p class="text-xs text-gray-500">Avenir Research Market Intelligence</p>
          </div>
          <div class="flex items-center gap-2 sm:gap-3 text-xs flex-wrap">
            <span class="text-gray-500 flex items-center gap-1">
              Last Update: <span class="text-gray-300 ml-1">{{ brief.lastUpdate }}</span>
              <button class="hover:text-white transition-colors ml-1 cursor-pointer" title="Refresh data">↻</button>
            </span>
            <!-- Share -->
            <button
              @click="shareLink"
              class="px-3 py-1.5 border border-green-600/60 text-green-400 rounded-md hover:bg-green-500/10 flex items-center gap-1.5 transition-all"
              :class="shared ? 'border-green-400 bg-green-500/10' : ''"
            >
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
              {{ shared ? 'Copied!' : 'Share Desk Brief' }}
            </button>
            <!-- Print -->
            <button
              @click="printPage"
              class="px-3 py-1.5 border border-[#3A403D] rounded-md hover:bg-[#2A302D] flex items-center gap-1.5 transition-colors"
            >
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
              Print / PDF
            </button>
            <button class="p-1.5 border border-[#3A403D] rounded-md hover:bg-[#2A302D] transition-colors">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
            </button>
          </div>
        </div>

        <!-- ── HEADLINE STRIP ─────────────────────────────────── -->
        <div class="bg-[#131714] border border-[#252b27] rounded-xl p-5 mb-4 flex flex-col lg:flex-row gap-6 lg:gap-8">
          <!-- Market Stance -->
          <div class="flex-shrink-0 lg:w-44">
            <p class="text-[9px] font-semibold text-gray-600 tracking-widest uppercase mb-2">Market Stance</p>
            <div class="flex items-center gap-2 mb-2">
              <span class="text-2xl font-bold leading-tight" :class="scoreColor.text">{{ brief.marketStance.label }}</span>
              <div class="w-7 h-7 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors"
                :class="brief.marketStance.score >= 10 ? 'border-green-400 text-green-400' : brief.marketStance.score >= -9 ? 'border-yellow-400 text-yellow-400' : 'border-red-400 text-red-400'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline :points="brief.marketStance.score >= 0 ? '18 15 12 9 6 15' : '6 9 12 15 18 9'"/>
                </svg>
              </div>
            </div>
            <div class="text-[11px] text-gray-500 space-y-0.5">
              <p>Tactical view: <span class="text-gray-300">{{ brief.marketStance.view }}</span></p>
              <p>Time horizon: <span class="text-gray-300">{{ brief.marketStance.horizon }}</span></p>
            </div>
          </div>

          <!-- Headline -->
          <div class="flex-1 lg:border-l border-[#252b27] lg:pl-8">
            <p class="text-[9px] font-semibold text-gray-600 tracking-widest uppercase mb-2">Desk Brief Headline</p>
            <h2 class="text-lg font-bold text-white mb-2 leading-snug">{{ brief.headline }}</h2>
            <p class="text-xs text-gray-400 leading-relaxed">{{ brief.subHeadline }}</p>
          </div>

          <!-- Macro Cards -->
          <div class="flex gap-6 lg:gap-8 lg:border-l border-[#252b27] lg:pl-8 flex-wrap">
            <div v-for="m in brief.macroCards" :key="m.title" class="flex flex-col min-w-[80px]">
              <p class="text-[9px] font-semibold text-gray-600 tracking-widest uppercase flex items-center gap-1 mb-1">
                <span>{{ m.icon }}</span> {{ m.title }}
              </p>
              <p class="text-[10px] text-gray-400 mb-0.5">{{ m.status }}</p>
              <p class="text-2xl font-bold text-white">{{ m.value }}</p>
              <p class="text-[10px] text-gray-600 mt-0.5">{{ m.desc }}</p>
            </div>
          </div>
        </div>

        <!-- ── ROW 1: Modules 1–4 ──────────────────────────────── -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-12 gap-4 mb-4">

          <!-- 1. Regime Summary -->
          <div class="bg-[#131714] border border-[#252b27] rounded-xl p-4 xl:col-span-3 flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-[10px] font-semibold text-gray-400 tracking-widest uppercase">1. Regime Summary</h3>
                <span class="text-[10px] text-gray-600 cursor-help" title="Market regime score 0-100 berdasarkan 6 komponen">ⓘ</span>
              </div>
              <div class="flex gap-4 items-center">
                <!-- Animated SVG circle -->
                <div class="relative w-24 h-24 flex-shrink-0">
                  <svg class="-rotate-90 w-24 h-24" width="96" height="96" viewBox="0 0 96 96">
                    <circle cx="48" cy="48" r="38" stroke="#1f2421" stroke-width="7" fill="none"/>
                    <circle
                      cx="48" cy="48" r="38"
                      :stroke="scoreColor.stroke"
                      stroke-width="7" fill="none"
                      stroke-linecap="round"
                      :stroke-dasharray="circumference"
                      :stroke-dashoffset="dashOffset"
                      style="transition: stroke-dashoffset 0.05s linear"
                    />
                  </svg>
                  <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                    <span class="text-[7px] text-gray-500 uppercase tracking-wider leading-none">Regime</span>
                    <span class="text-[7px] text-gray-500 uppercase tracking-wider leading-none">Score</span>
                    <div class="flex items-baseline mt-0.5">
                      <span class="text-[22px] font-bold leading-none" :class="scoreColor.text">{{ animatedScore }}</span>
                      <span class="text-[9px] text-gray-600">/100</span>
                    </div>
                    <span class="text-[7px] font-medium mt-0.5 leading-tight text-center px-1" :class="scoreColor.text">{{ brief.marketStance.label }}</span>
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <h4 class="text-[11px] font-bold text-white mb-1.5">Disinflation + Growth Support</h4>
                  <p class="text-[10px] text-gray-400 leading-relaxed">{{ brief.regimeText }}</p>
                </div>
              </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#252b27] flex justify-between items-center text-[10px] text-gray-500">
              <span>Prev. Score: {{ brief.marketStance.prevScore }} ({{ brief.marketStance.prevDate }})</span>
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-green-500"><polyline points="18 15 12 9 6 15"/></svg>
            </div>
          </div>

          <!-- 2. Key Drivers -->
          <div class="bg-[#131714] border border-[#252b27] rounded-xl p-4 xl:col-span-2">
            <h3 class="text-[10px] font-semibold text-gray-400 tracking-widest uppercase mb-4">
              2. Key Drivers <span class="normal-case text-gray-600 text-[9px] font-normal">(Ranked by impact)</span>
            </h3>
            <div class="space-y-3">
              <div
                v-for="d in brief.drivers"
                :key="d.rank"
                class="flex items-start justify-between gap-2 group cursor-default"
                :title="d.explanation"
              >
                <div class="flex items-start gap-2">
                  <span class="w-5 h-5 rounded-full bg-[#1f2421] text-gray-500 text-[9px] flex items-center justify-center flex-shrink-0 mt-0.5 group-hover:bg-[#2a302d] transition-colors">{{ d.rank }}</span>
                  <div>
                    <span class="text-[11px] text-gray-300 leading-tight block">{{ d.title }}</span>
                    <span class="text-[9px] text-gray-600">{{ d.category }}</span>
                  </div>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0 mt-0.5">
                  <span class="text-[9px] text-gray-500 hidden sm:block">{{ d.impact }}</span>
                  <div class="flex gap-0.5">
                    <span v-for="n in 3" :key="n" class="w-1.5 h-1.5 rounded-full transition-colors"
                      :class="n <= d.dots ? (d.dots === 3 ? 'bg-red-400' : d.dots === 2 ? 'bg-yellow-400' : 'bg-green-400') : 'bg-gray-700'"/>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 3. Cross-Asset Snapshot -->
          <div class="bg-[#131714] border border-[#252b27] rounded-xl p-4 xl:col-span-3 flex flex-col">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-[10px] font-semibold text-gray-400 tracking-widest uppercase">3. Cross-Asset Snapshot</h3>
              <span :class="apiStatusColor" class="text-[9px] font-medium">{{ apiStatusText }}</span>
            </div>

            <!-- IDX Indices -->
            <p class="text-[8px] text-gray-600 uppercase tracking-widest mb-2">IDX Indices — Sectors.app EOD</p>
            <div class="grid grid-cols-3 gap-2 mb-4">
              <div
                v-for="snap in idxSnapshots"
                :key="snap.symbol"
                class="bg-[#0c0f0d] border border-[#1e2420] rounded-lg p-2.5 flex flex-col items-center hover:border-[#2a302d] transition-colors"
              >
                <div class="flex items-center gap-1 mb-0.5">
                  <span class="text-[9px] text-gray-500 tracking-wider font-medium">{{ snap.symbol }}</span>
                  <span v-if="snap.isLive" class="text-[7px] text-green-400">●</span>
                </div>
                <div class="text-[13px] font-bold text-white mb-0.5 tabular-nums">{{ snap.value }}</div>
                <div class="text-[10px] font-semibold mb-1.5"
                  :class="snap.isUp === true ? 'text-green-400' : snap.isUp === false ? 'text-red-400' : 'text-gray-600'">
                  {{ snap.change }}
                </div>
                <!-- Real sparkline (No Fill) -->
                <svg viewBox="0 0 100 20" class="w-full h-4" preserveAspectRatio="none">
                  <template v-if="buildSparklinePoints(snap.sparkline)">
                    <polyline
                      :points="buildSparklinePoints(snap.sparkline)"
                      fill="none"
                      :stroke="snap.isUp ? '#4ade80' : '#f87171'"
                      stroke-width="1.5"
                      stroke-linejoin="round"
                      stroke-linecap="round"
                      vector-effect="non-scaling-stroke"
                    />
                  </template>
                  <template v-else>
                    <line x1="0" y1="10" x2="100" y2="10" stroke="#2a302d" stroke-width="1" stroke-dasharray="3,2"/>
                  </template>
                </svg>
              </div>
            </div>

            <!-- Other Assets -->
            <p class="text-[8px] text-gray-600 uppercase tracking-widest mb-2">Other Assets</p>
            <div class="grid grid-cols-4 gap-1.5">
              <div v-for="snap in (otherSnapshots.length ? otherSnapshots : [
                { symbol: 'USD/IDR', value: '—', change: '—', isUp: null },
                { symbol: '10Y Yield', value: '—', change: '—', isUp: null },
                { symbol: 'BRENT', value: '—', change: '—', isUp: null },
                { symbol: 'GOLD', value: '—', change: '—', isUp: null },
              ])" :key="snap.symbol" class="text-center">
                <div class="text-[8px] text-gray-600 mb-0.5 truncate">{{ snap.symbol }}</div>
                <div class="text-[11px] font-bold text-white">{{ snap.value }}</div>
                <div class="text-[9px]" :class="snap.isUp === true ? 'text-green-400' : snap.isUp === false ? 'text-red-400' : 'text-gray-600'">{{ snap.change }}</div>
              </div>
            </div>

            <!-- Source footer -->
            <div class="mt-auto pt-3 flex justify-between text-[8px] text-gray-600 border-t border-[#1e2420] mt-3">
              <span>Source: Sectors.app (IDX) · Manual (others)</span>
              <span>{{ apiStatus?.lastSync ?? brief.lastUpdate }}</span>
            </div>
          </div>

          <!-- 4. Smart Money / Broker Flow -->
          <div class="bg-[#131714] border border-[#252b27] rounded-xl p-4 xl:col-span-4 flex flex-col">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-[10px] font-semibold text-gray-400 tracking-widest uppercase">4. Smart Money / Broker Flow</h3>
              <!-- Period Toggle -->
              <div class="flex gap-3 text-[9px]">
                <button v-for="p in ['1D','1W','1M','3M','YTD','1Y','3Y','5Y']" :key="p"
                  @click="smPeriod = p"
                  class="pb-1 transition-colors font-medium border-b-2"
                  :class="smPeriod === p ? 'border-green-500 text-green-400' : 'border-transparent text-gray-500 hover:text-gray-300'"
                >{{ p }}</button>
              </div>
            </div>

            <p class="text-[9px] text-gray-500 mb-2">IHSG Trend & Cumulative Accumulation ({{ smPeriod }})</p>

            <!-- Chart area -->
            <div class="flex-1 relative min-h-[140px] flex mt-2">
              <!-- Y-axis background -->
              <div class="flex-1 relative mr-2">
                <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                  <!-- Dashed Baseline -->
                  <line x1="0" y1="50" x2="100" y2="50" stroke="#4ade80" stroke-width="1" stroke-dasharray="2,3" opacity="0.3"/>

                  <!-- Real sparkline (colored based on trend) -->
                  <template v-if="ihsgStats">
                    <polyline
                      :points="idxSnapshots.find(s=>s.symbol==='IHSG').sparkline.map((v,i,arr) => {
                          const range = ihsgStats.max - ihsgStats.min || 1;
                          return `${(i/(arr.length-1)*100).toFixed(1)},${(90-((v-ihsgStats.min)/range)*80).toFixed(1)}`;
                        }).join(' ')"
                      fill="none" :stroke="idxSnapshots.find(s=>s.symbol==='IHSG').isUp === false ? '#f87171' : '#4ade80'" stroke-width="1.5" vector-effect="non-scaling-stroke" stroke-linejoin="round" stroke-linecap="round"
                    />
                  </template>
                  <template v-else>
                    <polyline points="0,55 40,45 80,48 120,25 160,30 200,10" fill="none" stroke="#4ade80" stroke-width="1.5" vector-effect="non-scaling-stroke" stroke-linejoin="round" stroke-linecap="round"/>
                  </template>
                </svg>
                
                <!-- Max / Min labels overlay -->
                <div v-if="ihsgStats" class="absolute inset-0 pointer-events-none">
                  <span class="absolute text-[8px] text-gray-400 -translate-x-1/2" 
                        :style="{ left: `${ihsgStats.maxX}%`, top: '0%' }">
                    {{ ihsgStats.max.toLocaleString(undefined, {maximumFractionDigits:0}) }}
                  </span>
                  <span class="absolute text-[8px] text-gray-400 -translate-x-1/2" 
                        :style="{ left: `${ihsgStats.minX}%`, bottom: '0%' }">
                    {{ ihsgStats.min.toLocaleString(undefined, {maximumFractionDigits:0}) }}
                  </span>
                </div>
              </div>
              
              <!-- Y-axis labels -->
              <div v-if="ihsgStats" class="w-8 flex flex-col justify-between items-end text-[7px] text-gray-500 py-2 border-l border-[#1e2420] pl-1">
                <span>{{ ihsgStats.max.toLocaleString(undefined, {maximumFractionDigits:0}) }}</span>
                <span>{{ ((ihsgStats.max + ihsgStats.min) / 2).toLocaleString(undefined, {maximumFractionDigits:0}) }}</span>
                <span>{{ ihsgStats.min.toLocaleString(undefined, {maximumFractionDigits:0}) }}</span>
              </div>
            </div>
            <!-- X-axis labels -->
            <div class="flex justify-between text-[8px] text-gray-600 mt-1 px-0.5">
              <span>{{ smPeriod === '6M' ? "Jan '26" : smPeriod === '1Y' ? "Jun '25" : "Jun '23" }}</span>
              <span>{{ smPeriod === '6M' ? "Feb '26" : smPeriod === '1Y' ? "Sep '25" : "Dec '23" }}</span>
              <span>{{ smPeriod === '6M' ? "Apr '26" : smPeriod === '1Y' ? "Dec '25" : "Jun '24" }}</span>
              <span>Jun '26</span>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-[#1e2420]">
              <div>
                <p class="text-[8px] text-gray-600">Cumulative Net Inventory ({{ smPeriod }})</p>
                <div class="flex items-center gap-1 mt-0.5 flex-wrap">
                  <span class="text-[13px] font-bold text-blue-400">{{ brief.smartMoney?.cumulativeNet ?? '+28.4 Tn' }}</span>
                  <span class="text-[8px] text-green-400">vs 1M ago {{ brief.smartMoney?.cumulativeVs ?? '+12.6 Tn' }}</span>
                </div>
              </div>
              <div>
                <p class="text-[8px] text-gray-600">Running Cost Basis</p>
                <p class="text-[13px] font-bold text-red-400 mt-0.5">{{ brief.smartMoney?.costBasis ?? '6,812' }}</p>
              </div>
              <div>
                <p class="text-[8px] text-gray-600">Price vs Cost Basis</p>
                <p class="text-[13px] font-bold text-green-400 mt-0.5">{{ brief.smartMoney?.priceVsCost ?? '+5.9%' }}</p>
              </div>
            </div>
            <p class="text-[8px] text-gray-600 mt-2">Source: Avenir Smart Money, Broker Data &nbsp;· As of {{ brief.date }}</p>
          </div>
        </div>

        <!-- ── ROW 2: Modules 5–8 ──────────────────────────────── -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-12 gap-4 mb-4">

          <!-- 5. Sector Rotation Heatmap -->
          <div class="bg-[#131714] border border-[#252b27] rounded-xl p-4 xl:col-span-3 flex flex-col">
            <h3 class="text-[10px] font-semibold text-gray-400 tracking-widest uppercase mb-4">
              5. Sector Rotation <span class="normal-case text-gray-600 text-[9px] font-normal">(Heatmap – 1M Chg%)</span>
            </h3>
            <div class="grid grid-cols-4 gap-1 flex-1">
              <div
                v-for="sec in brief.sectors"
                :key="sec.name"
                class="rounded p-1.5 flex flex-col items-center justify-center text-center cursor-pointer transition-all hover:scale-[1.03] hover:shadow-lg hover:z-10 relative"
                :style="{ backgroundColor: sectorBg(sec.change) }"
                @mouseenter="onSectorHover(sec, $event)"
                @mousemove="tooltipPos = { x: $event.clientX, y: $event.clientY }"
                @mouseleave="onSectorLeave"
              >
                <span class="text-[8px] text-white/90 font-medium leading-tight break-words text-center">{{ sec.name }}</span>
                <span class="text-[9px] font-bold text-white mt-0.5">{{ sec.change >= 0 ? '+' : '' }}{{ sec.change.toFixed(1) }}%</span>
              </div>
            </div>

            <!-- Gradient legend -->
            <div class="flex justify-between items-center mt-3 text-[8px] text-gray-500">
              <span>-5%</span>
              <div class="flex-1 mx-2 h-1.5 rounded" style="background: linear-gradient(to right, #dc2626, #ef4444, #2a302d, #22c55e, #15803d)"></div>
              <span>+5%</span>
            </div>
            <div class="flex justify-between mt-1.5 text-[8px] text-gray-600">
              <span>Source: Avenir Research</span>
              <span>As of {{ brief.date }}</span>
            </div>
          </div>

          <!-- 6. Catalyst Calendar -->
          <div class="bg-[#131714] border border-[#252b27] rounded-xl p-4 xl:col-span-3 flex flex-col">
            <h3 class="text-[10px] font-semibold text-gray-400 tracking-widest uppercase mb-3">6. Catalyst Calendar</h3>

            <!-- Tab toggle -->
            <div class="flex border-b border-[#252b27] mb-3">
              <button
                v-for="tab in [{ key: 'upcoming', label: 'Upcoming' }, { key: 'past', label: 'Past Events' }]"
                :key="tab.key"
                @click="catalystTab = tab.key"
                class="px-3 py-1.5 text-[10px] font-medium transition-colors border-b-2 -mb-px"
                :class="catalystTab === tab.key
                  ? 'text-green-400 border-green-400'
                  : 'text-gray-500 border-transparent hover:text-gray-300'"
              >{{ tab.label }}</button>
            </div>

            <!-- Event table -->
            <div class="flex-1 overflow-hidden">
              <table class="w-full text-[10px] text-left">
                <thead>
                  <tr class="text-gray-600 border-b border-[#1e2420]">
                    <th class="pb-2 font-normal">Date</th>
                    <th class="pb-2 font-normal">Event</th>
                    <th class="pb-2 font-normal text-right pr-2">Impact</th>
                    <th class="pb-2 font-normal text-center w-8">Rgn</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="cat in filteredCatalysts"
                    :key="cat.event + cat.date"
                    class="border-b border-[#1a1f1c] last:border-0 hover:bg-[#1a1f1c] transition-colors"
                  >
                    <td class="py-2 pr-2 whitespace-nowrap text-gray-400">{{ cat.date }}</td>
                    <td class="py-2 pr-2 text-gray-300 leading-tight">{{ cat.event }}</td>
                    <td class="py-2 text-right pr-2 whitespace-nowrap">
                      <span :class="impactStyle(cat.impact).text">{{ cat.impact }}</span>
                      <span class="inline-block w-1.5 h-1.5 rounded-full ml-1" :class="impactStyle(cat.impact).dot"></span>
                    </td>
                    <td class="py-2 text-center text-gray-600 text-[8px]">{{ cat.region }}</td>
                  </tr>
                  <tr v-if="filteredCatalysts.length === 0">
                    <td colspan="4" class="py-4 text-center text-gray-600 text-[10px]">Tidak ada event untuk periode ini.</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <button @click="showCalendarModal = true" class="mt-3 text-[10px] text-green-400 hover:text-green-300 transition-colors text-left font-medium">
              View Full Calendar →
            </button>
          </div>

          <!-- 7. Risk Monitor -->
          <div class="bg-[#131714] border border-[#252b27] rounded-xl p-4 xl:col-span-2 flex flex-col">
            <h3 class="text-[10px] font-semibold text-gray-400 tracking-widest uppercase mb-4">7. Risk Monitor</h3>

            <div class="space-y-3 flex-1">
              <div
                v-for="risk in brief.risks"
                :key="risk.risk"
                class="flex justify-between items-center text-[10px] group"
              >
                <span class="text-gray-300 leading-tight pr-2 flex-1">{{ risk.risk }}</span>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                  <span class="w-1.5 h-1.5 rounded-full" :class="riskLevelStyle(risk.level).dot"/>
                  <span :class="riskLevelStyle(risk.level).text" class="w-12 text-right">{{ risk.level }}</span>
                </div>
              </div>
            </div>

            <!-- Composite Risk Index -->
            <div class="mt-4 pt-3 border-t border-[#252b27]">
              <div class="flex justify-between items-center mb-1.5">
                <span class="text-[9px] text-gray-500">Risk Index (Composite)</span>
                <span class="text-xl font-bold flex items-baseline gap-0.5" :class="riskIndexColor">
                  {{ riskIndex }}<span class="text-[9px] text-gray-600 font-normal">/100</span>
                </span>
              </div>
              <!-- Progress bar -->
              <div class="h-1 bg-[#1e2420] rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full transition-all duration-1000"
                  :class="riskIndex >= 70 ? 'bg-red-500' : riskIndex >= 40 ? 'bg-yellow-400' : 'bg-green-500'"
                  :style="{ width: `${riskIndex}%` }"
                />
              </div>
            </div>
          </div>

          <!-- 8. Analyst Takeaway -->
          <div class="bg-[#131714] border border-[#252b27] rounded-xl p-4 xl:col-span-4 flex flex-col">
            <h3 class="text-[10px] font-semibold text-gray-400 tracking-widest uppercase mb-4">8. Analyst Takeaway</h3>

            <div class="relative flex-1">
              <span class="absolute -top-3 -left-1 text-5xl text-green-500/15 leading-none font-serif select-none">"</span>
              <p class="text-[13px] text-gray-300 italic leading-relaxed pl-4 relative z-10">{{ brief.analyst.quote }}</p>
            </div>

            <div class="flex items-center gap-3 mt-5 pl-4">
              <div class="w-9 h-9 rounded-full bg-[#252b27] flex items-center justify-center text-[11px] font-bold text-green-400 border border-[#3a403d] flex-shrink-0">
                AR
              </div>
              <div>
                <div class="text-[11px] font-semibold text-white">{{ brief.analyst.author }}</div>
                <div class="text-[9px] text-gray-500">{{ brief.analyst.role }}</div>
              </div>
            </div>

            <!-- Top Picks -->
            <div class="mt-4 pt-4 border-t border-[#252b27] flex flex-wrap items-center gap-2">
              <span class="text-[9px] text-green-500 font-semibold tracking-widest uppercase">Top Picks</span>
              <div class="flex gap-1.5 flex-wrap">
                <span
                  v-for="pick in brief.analyst.topPicks"
                  :key="pick"
                  class="px-2 py-0.5 border border-green-800/80 text-green-400 text-[10px] rounded-full font-medium hover:border-green-500/80 hover:bg-green-900/20 cursor-pointer transition-colors"
                >{{ pick }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- ── TOP MOVERS STRIP ───────────────────────────────── -->
        <div v-if="hasMovers" class="bg-[#131714] border border-[#252b27] rounded-xl p-4 mb-4">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-[10px] font-semibold text-gray-400 tracking-widest uppercase">
              Top Movers IDX <span class="normal-case text-gray-600 text-[9px] font-normal">(1D — Sectors.app EOD)</span>
            </h3>
            <span :class="apiStatusColor" class="text-[9px]">{{ apiStatusText }}</span>
          </div>
          <div class="grid grid-cols-2 gap-6">
            <!-- Gainers -->
            <div>
              <p class="text-[9px] text-green-500 uppercase tracking-widest font-semibold mb-2">▲ Top Gainers</p>
              <div class="space-y-2">
                <div
                  v-for="(m, i) in gainers"
                  :key="m.symbol"
                  class="flex items-center justify-between text-[10px] py-1 border-b border-[#1a1f1c] last:border-0"
                >
                  <div class="flex items-center gap-2">
                    <span class="text-[8px] text-gray-600 w-4 text-right">{{ i + 1 }}</span>
                    <span class="font-semibold text-white">{{ m.symbol }}</span>
                    <span class="text-gray-600 text-[9px] hidden sm:block truncate max-w-[120px]">{{ m.name }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-gray-500 text-[9px] tabular-nums">{{ m.last_close?.toLocaleString() }}</span>
                    <span class="text-green-400 font-semibold tabular-nums">+{{ Number(m.price_pct).toFixed(2) }}%</span>
                  </div>
                </div>
              </div>
            </div>
            <!-- Losers -->
            <div>
              <p class="text-[9px] text-red-500 uppercase tracking-widest font-semibold mb-2">▼ Top Losers</p>
              <div class="space-y-2">
                <div
                  v-for="(m, i) in losers"
                  :key="m.symbol"
                  class="flex items-center justify-between text-[10px] py-1 border-b border-[#1a1f1c] last:border-0"
                >
                  <div class="flex items-center gap-2">
                    <span class="text-[8px] text-gray-600 w-4 text-right">{{ i + 1 }}</span>
                    <span class="font-semibold text-white">{{ m.symbol }}</span>
                    <span class="text-gray-600 text-[9px] hidden sm:block truncate max-w-[120px]">{{ m.name }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-gray-500 text-[9px] tabular-nums">{{ m.last_close?.toLocaleString() }}</span>
                    <span class="text-red-400 font-semibold tabular-nums">{{ Number(m.price_pct).toFixed(2) }}%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── FOOTER ─────────────────────────────────────────── -->
        <div class="mt-4 pt-4 border-t border-[#252b27] flex flex-col sm:flex-row justify-between gap-3 text-[9px] text-gray-600">
          <p class="leading-relaxed max-w-xl">
            Informasi ini bersifat umum dan tidak merupakan rekomendasi investasi. Data bersumber dari Sectors.app dan diolah oleh Avenir Research.
            Investor wajib melakukan riset sendiri sebelum mengambil keputusan investasi.
          </p>
          <div class="flex items-center gap-3 flex-shrink-0">
            <div v-if="apiStatus" class="flex items-center gap-1.5">
              <span class="w-1.5 h-1.5 rounded-full" :class="apiStatusDot"/>
              <span>
                {{ apiStatus.provider }}
                <template v-if="apiStatus.lastSync"> · {{ apiStatus.lastSync }}</template>
                <template v-if="apiStatus.creditsToday"> · {{ apiStatus.creditsToday }} credits today</template>
              </span>
            </div>
            <span class="text-green-500 font-semibold">Avenir Research</span>
          </div>
        </div>

      </div>
    </div>

    <!-- ── SECTOR TOOLTIP (fixed positioned) ─────────────────── -->
    <Teleport to="body">
      <div
        v-if="hoveredSector"
        class="fixed z-50 pointer-events-none bg-[#1a1f1c] border border-[#3a403d] rounded-lg px-3 py-2 shadow-xl text-[11px] text-white min-w-[120px]"
        :style="{ left: `${tooltipPos.x + 12}px`, top: `${tooltipPos.y - 10}px` }"
      >
        <div class="font-semibold">{{ hoveredSector.name }}</div>
        <div class="font-bold mt-0.5" :class="hoveredSector.change >= 0 ? 'text-green-400' : 'text-red-400'">
          {{ hoveredSector.change >= 0 ? '+' : '' }}{{ hoveredSector.change.toFixed(1) }}% (1M)
        </div>
      </div>
    </Teleport>

    <!-- ── CALENDAR MODAL ────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showCalendarModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="showCalendarModal = false">
        <div class="bg-[#131714] border border-[#252b27] rounded-xl w-full max-w-2xl max-h-[85vh] flex flex-col shadow-2xl overflow-hidden">
          <div class="flex justify-between items-center p-5 border-b border-[#252b27]">
            <h2 class="text-sm font-bold text-white tracking-widest uppercase">Full Catalyst Calendar</h2>
            <button @click="showCalendarModal = false" class="text-gray-500 hover:text-white transition-colors">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <div class="p-5 overflow-y-auto custom-scrollbar">
            <table class="w-full text-xs text-left">
              <thead>
                <tr class="text-gray-600 border-b border-[#1e2420]">
                  <th class="pb-3 font-normal">Date</th>
                  <th class="pb-3 font-normal">Event</th>
                  <th class="pb-3 font-normal text-right pr-4">Impact</th>
                  <th class="pb-3 font-normal text-center">Region</th>
                  <th class="pb-3 font-normal text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="cat in (brief.catalysts || [])" :key="cat.event + cat.date" class="border-b border-[#1a1f1c] last:border-0 hover:bg-[#1a1f1c] transition-colors">
                  <td class="py-3 pr-3 whitespace-nowrap text-gray-400">{{ cat.date }}</td>
                  <td class="py-3 pr-3 text-gray-200 font-medium">{{ cat.event }}</td>
                  <td class="py-3 text-right pr-4 whitespace-nowrap">
                    <span :class="impactStyle(cat.impact).text">{{ cat.impact }}</span>
                    <span class="inline-block w-2 h-2 rounded-full ml-1" :class="impactStyle(cat.impact).dot"></span>
                  </td>
                  <td class="py-3 text-center text-gray-500">{{ cat.region }}</td>
                  <td class="py-3 text-center">
                    <span v-if="cat.isPast" class="text-[9px] px-2 py-0.5 border border-gray-700 text-gray-500 rounded">Past</span>
                    <span v-else class="text-[9px] px-2 py-0.5 border border-green-800 text-green-500 rounded">Upcoming</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<style>
/* Optional: styling scrollbar for the modal */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #2a302d;
  border-radius: 4px;
}
</style>
