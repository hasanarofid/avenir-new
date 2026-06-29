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
  sectorBias: { type: Array, default: () => [] },
  riskAlerts: { type: Array, default: () => [] },
  smartMoney: { type: Object, default: null },
  internals:  { type: Object, default: null },
  events:     { type: Array, default: () => [] },
  macroCards: { type: Array, default: () => [] },
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
  internals: {
    advances: 215,
    declines: 342,
    unchanged: 180,
    above200dma: '42%',
    newHighs: 12,
    newLows: 45,
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
const brief = computed(() => {
  const data = props.deskBrief || mockData;
  return {
    ...data,
    marketStance: data.market_stance || mockData.marketStance,
    drivers: data.drivers || mockData.drivers,
    lastUpdate: data.updated_at ? new Date(data.updated_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).replace('.', ':') + ' WIB' : mockData.lastUpdate,
    headline: data.title || mockData.headline,
    subHeadline: data.market_read || mockData.subHeadline,
    regimeText: data.so_what || mockData.regimeText,
    macroCards: props.macroCards?.length ? props.macroCards.map(mc => ({
      title: mc.symbol_or_metric.replace('_', ' '),
      status: mc.change_abs > 0 ? 'Rising' : (mc.change_abs < 0 ? 'Cooling' : 'Stable'),
      value: mc.value,
      desc: mc.source || '',
      icon: mc.symbol_or_metric === 'GLOBAL_GROWTH' ? '🌐' : (mc.symbol_or_metric === 'US_INFLATION' ? '📊' : '💧')
    })) : mockData.macroCards,
    sectors: props.sectorBias?.length ? props.sectorBias.map(sb => ({
      name: sb.sector,
      change: Number(sb.return_1d),
    })) : mockData.sectors,
    catalysts: props.events?.length ? props.events : mockData.catalysts,
    risks: props.riskAlerts?.length ? props.riskAlerts.map(r => ({
      risk: r.risk_type,
      level: r.severity,
    })) : mockData.risks,
    smartMoney: props.smartMoney ? {
      cumulativeNet: props.smartMoney.cumulative_net,
      cumulativeVs: props.smartMoney.cumulative_vs,
      costBasis: props.smartMoney.cost_basis,
      priceVsCost: props.smartMoney.price_vs_cost,
    } : mockData.smartMoney,
    internals: props.internals || mockData.internals,
    analyst: {
      quote: data.what_to_do || mockData.analyst.quote,
      author: data.analyst_id ? 'Analyst' : mockData.analyst.author,
      role: 'Research',
      topPicks: data.radar_stocks?.map(r => r.ticker) || mockData.analyst.topPicks,
    }
  };
});
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
    { symbol: 'IHSG',  value: '7,142.50', change: '+0.54%', isUp: true, sparkline: [7100, 7120, 7110, 7135, 7142], isLive: true },
    { symbol: 'LQ45',  value: '892.15',   change: '+0.82%', isUp: true, sparkline: [885, 888, 890, 889, 892], isLive: true },
    { symbol: 'IDX30', value: '451.80',   change: '+0.95%', isUp: true, sparkline: [448, 449, 451, 450, 451.8], isLive: true },
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

// Data min/max IHSG untuk chart Smart Money (Simulasi dinamis sesuai periode)
const simulatedChartStats = computed(() => {
  const period = smPeriod.value;
  let pointsCount = 20;
  let volatility = 0;
  let trend = 0; // positive is up, negative is down
  let basePrice = 7000;
  let labels = [];

  // Konfigurasi mock trend per periode
  switch(period) {
    case '1D':  pointsCount = 10; volatility = 20;  trend = 10;   labels = ['09:00', '11:00', '14:00', '16:00']; break;
    case '1W':  pointsCount = 5;  volatility = 50;  trend = -20;  labels = ['Mon', 'Wed', 'Fri']; break;
    case '1M':  pointsCount = 20; volatility = 80;  trend = 40;   labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4']; break;
    case '3M':  pointsCount = 30; volatility = 120; trend = -100; labels = ['Month 1', 'Month 2', 'Month 3']; break;
    case 'YTD': pointsCount = 40; volatility = 150; trend = 250;  labels = ['Jan', 'Mar', 'May', 'Now']; break;
    case '1Y':  pointsCount = 50; volatility = 250; trend = 400;  labels = ['Q3', 'Q4', 'Q1', 'Q2']; break;
    case '3Y':  pointsCount = 60; volatility = 500; trend = 800;  labels = ['2023', '2024', '2025', '2026']; break;
    case '5Y':  pointsCount = 80; volatility = 800; trend = 1200; labels = ['2021', '2023', '2025', '2026']; break;
    default:    pointsCount = 20; volatility = 100; trend = 0;    labels = ['Start', 'Mid', 'End'];
  }

  // Generate random walk
  let data = [basePrice];
  let currentPrice = basePrice;
  for (let i = 1; i < pointsCount; i++) {
    // Add trend component + random volatility
    const trendStep = trend / pointsCount;
    const noise = (Math.random() - 0.5) * volatility;
    currentPrice += trendStep + noise;
    data.push(currentPrice);
  }

  const min = Math.min(...data);
  const max = Math.max(...data);
  const minIdx = data.indexOf(min);
  const maxIdx = data.indexOf(max);
  
  // Calculate SVG points (0 to 100 on X, 90 to 10 on Y to leave padding)
  const range = max - min || 1;
  const svgPoints = data.map((v, i) => {
    const x = (i / (pointsCount - 1)) * 100;
    const y = 90 - ((v - min) / range) * 80; 
    return `${x.toFixed(1)},${y.toFixed(1)}`;
  }).join(' ');

  return {
    min, max,
    minX: (minIdx / (pointsCount - 1)) * 100,
    maxX: (maxIdx / (pointsCount - 1)) * 100,
    svgPoints,
    isUp: data[data.length - 1] >= data[0],
    labels
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
    <div class="bg-slate-950 min-h-screen text-slate-200 pb-16 font-sans relative overflow-hidden selection:bg-indigo-500 selection:text-white">
      
      <!-- Background Glow Blobs -->
      <div class="absolute top-[-20%] left-[-10%] w-[600px] h-[600px] rounded-full bg-emerald-900/10 blur-[120px] pointer-events-none"></div>
      <div class="absolute bottom-[-10%] right-[-10%] w-[600px] h-[600px] rounded-full bg-emerald-900/10 blur-[120px] pointer-events-none"></div>
      
      <div class="w-full max-w-[1536px] mx-auto px-4 lg:px-6 py-6 relative z-10">

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
              <span class="text-emerald-500/80 ml-2 border-l border-gray-700 pl-2">
                Auto-sync (07:00 & 17:00 WIB)
              </span>
            </span>
            <!-- Share -->
            <button
              @click="shareLink"
              class="px-3 py-1.5 border border-green-600/60 text-green-400 rounded-md hover:bg-green-500/10 flex items-center gap-1.5 transition-all print:hidden"
              :class="shared ? 'border-green-400 bg-green-500/10' : ''"
            >
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
              {{ shared ? 'Copied!' : 'Share Desk Brief' }}
            </button>
            <!-- Print -->
            <button
              @click="printPage"
              class="px-3 py-1.5 border border-[#3A403D] rounded-md hover:bg-[#2A302D] flex items-center gap-1.5 transition-colors print:hidden"
            >
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
              Print / PDF
            </button>
            <button class="p-1.5 border border-[#3A403D] rounded-md hover:bg-[#2A302D] transition-colors print:hidden">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
            </button>
          </div>
        </div>

        <!-- ── HEADLINE STRIP ─────────────────────────────────── -->
        <div class="bg-white/[0.02] border border-white/[0.05] rounded-2xl shadow-xl backdrop-blur-xl p-6 mb-6 flex flex-col lg:flex-row gap-6 lg:gap-8">
          <!-- Market Stance -->
          <div class="flex-shrink-0 lg:w-44">
            <p class="text-xs font-semibold text-slate-400 tracking-widest uppercase mb-3">Market Stance</p>
            <div class="flex items-center gap-2 mb-2">
              <span class="text-2xl font-bold leading-tight" :class="scoreColor.text">{{ brief.marketStance.label }}</span>
              <div class="w-7 h-7 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors"
                :class="brief.marketStance.score >= 10 ? 'border-emerald-400 text-emerald-400' : brief.marketStance.score >= -9 ? 'border-yellow-400 text-yellow-400' : 'border-rose-400 text-rose-400'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline :points="brief.marketStance.score >= 0 ? '18 15 12 9 6 15' : '6 9 12 15 18 9'"/>
                </svg>
              </div>
            </div>
            <div class="text-sm text-slate-400 space-y-1">
              <p>Tactical view: <span class="text-slate-200">{{ brief.marketStance.view }}</span></p>
              <p>Time horizon: <span class="text-slate-200">{{ brief.marketStance.horizon }}</span></p>
            </div>
          </div>

          <!-- Headline -->
          <div class="flex-1 lg:border-l border-white/5 lg:pl-8">
            <p class="text-xs font-semibold text-slate-400 tracking-widest uppercase mb-3">Desk Brief Headline</p>
            <h2 class="text-xl font-bold text-white mb-3 leading-snug">{{ brief.headline }}</h2>
            <p class="text-sm text-slate-300 leading-relaxed">{{ brief.subHeadline }}</p>
          </div>

          <!-- Macro Cards -->
          <div class="flex gap-6 lg:gap-8 lg:border-l border-white/5 lg:pl-8 flex-wrap">
            <div v-for="m in brief.macroCards" :key="m.title" class="flex flex-col min-w-[90px]">
              <p class="text-[10px] font-semibold text-slate-400 tracking-widest uppercase flex items-center gap-1.5 mb-1.5">
                <span>{{ m.icon }}</span> {{ m.title }}
              </p>
              <p class="text-xs font-medium text-slate-300 mb-0.5">{{ m.status }}</p>
              <p class="text-3xl font-bold text-white">{{ m.value }}</p>
              <p class="text-[10px] font-medium text-slate-500 mt-1">{{ m.desc }}</p>
            </div>
          </div>
        </div>

        <!-- ── ROW 1: Modules 1–4 ──────────────────────────────── -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-12 gap-5 mb-5">

          <!-- 1. Regime Summary -->
          <div class="bg-white/[0.02] border border-white/[0.05] rounded-2xl p-5 xl:col-span-3 flex flex-col justify-between backdrop-blur-xl shadow-lg">
            <div>
              <div class="flex items-center justify-between mb-5">
                <h3 class="text-[11px] font-semibold text-slate-400 tracking-widest uppercase">1. Regime Summary</h3>
                <span class="text-xs text-slate-500 cursor-help" title="Market regime score 0-100 berdasarkan 6 komponen">ⓘ</span>
              </div>
              <div class="flex gap-5 items-center">
                <!-- Animated SVG circle -->
                <div class="relative w-28 h-28 flex-shrink-0">
                  <svg class="-rotate-90 w-28 h-28" width="112" height="112" viewBox="0 0 112 112">
                    <circle cx="56" cy="56" r="45" stroke="rgba(255,255,255,0.05)" stroke-width="8" fill="none"/>
                    <circle
                      cx="56" cy="56" r="45"
                      :stroke="scoreColor.stroke"
                      stroke-width="8" fill="none"
                      stroke-linecap="round"
                      :stroke-dasharray="282.74"
                      :stroke-dashoffset="282.74 - (282.74 * Math.abs(animatedScore)) / 100"
                      style="transition: stroke-dashoffset 0.05s linear"
                    />
                  </svg>
                  <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                    <span class="text-[8px] text-slate-500 uppercase tracking-widest leading-none mt-1">Regime</span>
                    <div class="flex items-baseline mt-1">
                      <span class="text-[26px] font-bold leading-none" :class="scoreColor.text">{{ animatedScore }}</span>
                      <span class="text-[10px] text-slate-500 font-medium">/100</span>
                    </div>
                    <span class="text-[9px] font-bold mt-1 leading-tight text-center px-1" :class="scoreColor.text">{{ brief.marketStance.label }}</span>
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <h4 class="text-sm font-bold text-white mb-2 leading-tight">Disinflation + Growth Support</h4>
                  <p class="text-xs text-slate-400 leading-relaxed">{{ brief.regimeText }}</p>
                </div>
              </div>
            </div>
            <div class="mt-5 pt-3 border-t border-white/5 flex justify-between items-center text-xs text-slate-500 font-medium">
              <span>Prev. Score: {{ brief.marketStance.prevScore }} ({{ brief.marketStance.prevDate }})</span>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-500"><polyline points="18 15 12 9 6 15"/></svg>
            </div>
          </div>

          <!-- 2. Key Drivers -->
          <div class="bg-white/[0.02] border border-white/[0.05] rounded-2xl p-5 xl:col-span-2 backdrop-blur-xl shadow-lg">
            <h3 class="text-[11px] font-semibold text-slate-400 tracking-widest uppercase mb-5">
              2. Key Drivers <span class="normal-case text-slate-500 text-[10px] font-normal ml-1">(Ranked)</span>
            </h3>
            <div class="space-y-4">
              <div
                v-for="d in brief.drivers"
                :key="d.rank"
                class="flex items-start justify-between gap-3 group cursor-default"
                :title="d.explanation"
              >
                <div class="flex items-start gap-2.5">
                  <span class="w-6 h-6 rounded-full bg-white/5 text-slate-400 text-[10px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5 group-hover:bg-white/10 transition-colors shadow-sm">{{ d.rank }}</span>
                  <div>
                    <span class="text-xs text-slate-200 font-medium leading-tight block mb-0.5">{{ d.title }}</span>
                    <span class="text-[10px] text-slate-500">{{ d.category }}</span>
                  </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0 mt-1">
                  <span class="text-[10px] text-slate-500 font-medium hidden sm:block">{{ d.impact }}</span>
                  <div class="flex gap-1">
                    <span v-for="n in 3" :key="n" class="w-1.5 h-1.5 rounded-full transition-colors"
                      :class="n <= d.dots ? (d.dots === 3 ? 'bg-rose-400 shadow-[0_0_8px_rgba(251,113,133,0.6)]' : d.dots === 2 ? 'bg-yellow-400 shadow-[0_0_8px_rgba(250,204,21,0.6)]' : 'bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.6)]') : 'bg-slate-700'"/>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 3. Cross-Asset & Internals -->
          <div class="bg-white/[0.02] border border-white/[0.05] rounded-2xl p-5 xl:col-span-3 flex flex-col backdrop-blur-xl shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between mb-5 relative z-10">
              <h3 class="text-[11px] font-semibold text-slate-400 tracking-widest uppercase">3. Cross-Asset & Internals</h3>
            </div>

            <!-- IDX Index Snapshot -->
            <div class="grid grid-cols-3 gap-3 mb-4 relative z-10">
              <div
                v-for="snap in idxSnapshots"
                :key="snap.symbol"
                class="bg-white/[0.03] border border-white/5 rounded-xl p-3 flex flex-col items-center hover:bg-white/[0.06] transition-colors"
              >
                <div class="flex items-center gap-1 mb-1">
                  <span class="text-[10px] text-slate-400 tracking-wider font-semibold">{{ snap.symbol }}</span>
                  <span v-if="snap.isLive" class="text-[8px] text-emerald-400 animate-pulse">●</span>
                </div>
                <div class="text-sm font-bold text-white mb-1 tabular-nums">{{ snap.value }}</div>
                <div class="text-[11px] font-bold mb-2"
                  :class="snap.isUp === true ? 'text-emerald-400' : snap.isUp === false ? 'text-rose-400' : 'text-slate-500'">
                  {{ snap.change }}
                </div>
                <!-- Real sparkline (No Fill) -->
                <svg viewBox="0 0 100 20" class="w-full h-5" preserveAspectRatio="none">
                  <template v-if="buildSparklinePoints(snap.sparkline)">
                    <polyline
                      :points="buildSparklinePoints(snap.sparkline)"
                      fill="none"
                      :stroke="snap.isUp ? '#34d399' : '#fb7185'"
                      stroke-width="2"
                      stroke-linejoin="round"
                      stroke-linecap="round"
                      vector-effect="non-scaling-stroke"
                    />
                  </template>
                  <template v-else>
                    <line x1="0" y1="10" x2="100" y2="10" stroke="rgba(255,255,255,0.1)" stroke-width="1.5" stroke-dasharray="3,2"/>
                  </template>
                </svg>
              </div>
            </div>

            <!-- Other Assets -->
            <p class="text-[9px] text-slate-500 uppercase tracking-widest mb-2 font-medium">Other Assets</p>
            <div class="grid grid-cols-4 gap-2 mb-4">
              <div v-for="snap in otherSnapshots" :key="snap.symbol" class="text-center bg-white/[0.02] rounded-lg py-2 border border-white/5">
                <div class="text-[9px] text-slate-400 mb-1 truncate px-1 font-medium">{{ snap.symbol }}</div>
                <div class="text-xs font-bold text-white tabular-nums">{{ snap.value }}</div>
                <div class="text-[10px] font-semibold mt-0.5 tabular-nums" :class="snap.isUp === true ? 'text-emerald-400' : snap.isUp === false ? 'text-rose-400' : 'text-slate-500'">{{ snap.change }}</div>
              </div>
            </div>

            <!-- Market Internals -->
            <p class="text-[9px] text-slate-500 uppercase tracking-widest mb-2 font-medium">Market Internals (IDX)</p>
            <div class="grid grid-cols-3 gap-2 relative z-10">
              <div class="bg-white/[0.02] border border-white/5 rounded-lg p-2 text-center">
                <div class="text-[9px] text-slate-400 mb-1 font-medium">A/D Ratio</div>
                <div class="flex items-center justify-center gap-1 text-xs font-bold tabular-nums">
                  <span class="text-emerald-400" title="Advances">{{ brief.internals.advances }}</span>
                  <span class="text-slate-500">:</span>
                  <span class="text-rose-400" title="Declines">{{ brief.internals.declines }}</span>
                </div>
              </div>
              <div class="bg-white/[0.02] border border-white/5 rounded-lg p-2 text-center">
                <div class="text-[9px] text-slate-400 mb-1 font-medium">% > 200DMA</div>
                <div class="text-xs font-bold tabular-nums" :class="parseInt(brief.internals.above200dma) > 50 ? 'text-emerald-400' : 'text-rose-400'">
                  {{ brief.internals.above200dma }}
                </div>
              </div>
              <div class="bg-white/[0.02] border border-white/5 rounded-lg p-2 text-center">
                <div class="text-[9px] text-slate-400 mb-1 font-medium">New H/L (52W)</div>
                <div class="flex items-center justify-center gap-1 text-xs font-bold tabular-nums">
                  <span class="text-emerald-400" title="New Highs">{{ brief.internals.newHighs }}</span>
                  <span class="text-slate-500">/</span>
                  <span class="text-rose-400" title="New Lows">{{ brief.internals.newLows }}</span>
                </div>
              </div>
            </div>

            <!-- Source footer -->
            <div class="mt-auto pt-4 flex justify-between text-[9px] text-slate-500 border-t border-white/5 mt-4">
              <span>Source: Sectors.app (IDX) · FRED/BBG</span>
              <span>{{ apiStatus?.lastSync ?? brief.lastUpdate }}</span>
            </div>
          </div>

          <!-- 4. Smart Money / Broker Flow -->
          <div class="bg-white/[0.02] border border-white/[0.05] rounded-2xl p-5 xl:col-span-4 flex flex-col backdrop-blur-xl shadow-lg relative overflow-hidden">
            <!-- Glow background specific to chart -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-3/4 h-3/4 bg-blue-500/5 blur-[80px] pointer-events-none rounded-full"></div>

            <div class="flex items-center justify-between mb-5 relative z-10">
              <h3 class="text-[11px] font-semibold text-slate-400 tracking-widest uppercase">4. Smart Money / Broker Flow</h3>
              <!-- Period Toggle -->
              <div class="flex gap-2 bg-white/[0.05] p-1 rounded-lg text-[10px] font-medium">
                <button v-for="p in ['1D','1W','1M','3M','YTD','1Y','3Y','5Y']" :key="p"
                  @click="smPeriod = p"
                  class="px-2 py-1 rounded transition-colors"
                  :class="smPeriod === p ? 'bg-emerald-500 text-white shadow' : 'text-slate-400 hover:text-white hover:bg-white/5'"
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
                  <template v-if="simulatedChartStats">
                    <polyline
                      :points="simulatedChartStats.svgPoints"
                      fill="none" :stroke="simulatedChartStats.isUp ? '#4ade80' : '#f87171'" stroke-width="2" vector-effect="non-scaling-stroke" stroke-linejoin="round" stroke-linecap="round"
                    />
                  </template>
                </svg>
                
                <!-- Max / Min labels overlay -->
                <div v-if="simulatedChartStats" class="absolute inset-0 pointer-events-none">
                  <span class="absolute text-[10px] text-gray-400 font-medium -translate-x-1/2" 
                        :style="{ left: `${simulatedChartStats.maxX}%`, top: '0%' }">
                    {{ simulatedChartStats.max.toLocaleString(undefined, {maximumFractionDigits:0}) }}
                  </span>
                  <span class="absolute text-[10px] text-gray-400 font-medium -translate-x-1/2" 
                        :style="{ left: `${simulatedChartStats.minX}%`, bottom: '0%' }">
                    {{ simulatedChartStats.min.toLocaleString(undefined, {maximumFractionDigits:0}) }}
                  </span>
                </div>
              </div>
              
              <!-- Y-axis labels -->
              <div v-if="simulatedChartStats" class="w-10 flex flex-col justify-between items-end text-[9px] text-gray-500 py-2 border-l border-white/5 pl-1.5 font-medium">
                <span>{{ simulatedChartStats.max.toLocaleString(undefined, {maximumFractionDigits:0}) }}</span>
                <span>{{ ((simulatedChartStats.max + simulatedChartStats.min) / 2).toLocaleString(undefined, {maximumFractionDigits:0}) }}</span>
                <span>{{ simulatedChartStats.min.toLocaleString(undefined, {maximumFractionDigits:0}) }}</span>
              </div>
            </div>
            <!-- X-axis labels -->
            <div class="flex justify-between text-[10px] text-gray-500 font-medium mt-2 px-1">
              <span v-for="label in simulatedChartStats.labels" :key="label">{{ label }}</span>
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
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-12 gap-5 mb-5 relative z-10">

          <!-- 5. Sector Rotation Heatmap -->
          <div class="bg-white/[0.02] border border-white/[0.05] rounded-2xl p-5 xl:col-span-3 flex flex-col backdrop-blur-xl shadow-lg">
            <h3 class="text-[11px] font-semibold text-slate-400 tracking-widest uppercase mb-5">
              5. Sector Rotation <span class="normal-case text-slate-500 text-[10px] font-normal ml-1">(Heatmap – 1M Chg%)</span>
            </h3>
            <div class="grid grid-cols-4 gap-1.5 flex-1">
              <div
                v-for="sec in brief.sectors"
                :key="sec.name"
                class="rounded-lg p-2 flex flex-col items-center justify-center text-center cursor-pointer transition-all hover:scale-105 hover:shadow-xl hover:z-10 relative border border-white/10"
                :style="{ backgroundColor: sectorBg(sec.change) }"
                @mouseenter="onSectorHover(sec, $event)"
                @mousemove="tooltipPos = { x: $event.clientX, y: $event.clientY }"
                @mouseleave="onSectorLeave"
              >
                <span class="text-[9px] text-white font-medium leading-tight break-words text-center">{{ sec.name }}</span>
                <span class="text-[10px] font-bold text-white mt-1">{{ sec.change >= 0 ? '+' : '' }}{{ sec.change.toFixed(1) }}%</span>
              </div>
            </div>

            <!-- Gradient legend -->
            <div class="flex justify-between items-center mt-4 text-[10px] font-medium text-slate-500">
              <span>-5%</span>
              <div class="flex-1 mx-3 h-2 rounded-full" style="background: linear-gradient(to right, #e11d48, #f43f5e, rgba(255,255,255,0.1), #10b981, #059669)"></div>
              <span>+5%</span>
            </div>
            <div class="flex justify-between mt-2 text-[10px] text-slate-500">
              <span>Source: Avenir Research</span>
              <span>As of {{ brief.date }}</span>
            </div>
          </div>

          <!-- 6. Catalyst Calendar -->
          <div class="bg-white/[0.02] border border-white/[0.05] rounded-2xl p-5 xl:col-span-3 flex flex-col backdrop-blur-xl shadow-lg">
            <h3 class="text-[11px] font-semibold text-slate-400 tracking-widest uppercase mb-4">6. Catalyst Calendar</h3>

            <!-- Tab toggle -->
            <div class="flex border-b border-white/10 mb-4">
              <button
                v-for="tab in [{ key: 'upcoming', label: 'Upcoming' }, { key: 'past', label: 'Past Events' }]"
                :key="tab.key"
                @click="catalystTab = tab.key"
                class="px-3 py-2 text-xs font-medium transition-colors border-b-2 -mb-px"
                :class="catalystTab === tab.key
                  ? 'text-emerald-400 border-emerald-400'
                  : 'text-slate-500 border-transparent hover:text-slate-300'"
              >{{ tab.label }}</button>
            </div>

            <!-- Event table -->
            <div class="flex-1 overflow-hidden">
              <table class="w-full text-xs text-left">
                <thead>
                  <tr class="text-slate-500 border-b border-white/5 font-medium">
                    <th class="pb-3 font-medium">Date</th>
                    <th class="pb-3 font-medium">Event</th>
                    <th class="pb-3 font-medium text-right pr-2">Impact</th>
                    <th class="pb-3 font-medium text-center w-10">Rgn</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="cat in filteredCatalysts"
                    :key="cat.event + cat.date"
                    class="border-b border-white/[0.02] last:border-0 hover:bg-white/[0.03] transition-colors"
                  >
                    <td class="py-2.5 pr-3 whitespace-nowrap text-slate-400 font-medium">{{ cat.date }}</td>
                    <td class="py-2.5 pr-3 text-slate-200 leading-tight">{{ cat.event }}</td>
                    <td class="py-2.5 text-right pr-2 whitespace-nowrap">
                      <span :class="impactStyle(cat.impact).text" class="font-medium text-[11px]">{{ cat.impact }}</span>
                      <span class="inline-block w-1.5 h-1.5 rounded-full ml-1.5" :class="impactStyle(cat.impact).dot"></span>
                    </td>
                    <td class="py-2.5 text-center text-slate-500 font-medium text-[10px]">{{ cat.region }}</td>
                  </tr>
                  <tr v-if="filteredCatalysts.length === 0">
                    <td colspan="4" class="py-6 text-center text-slate-500 text-xs">Tidak ada event untuk periode ini.</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <button @click="showCalendarModal = true" class="mt-4 pt-4 border-t border-white/5 text-xs text-emerald-400 hover:text-emerald-300 transition-colors text-left font-semibold inline-flex items-center gap-1">
              View Full Calendar <span>→</span>
            </button>
          </div>

          <!-- 7. Risk Monitor -->
          <div class="bg-white/[0.02] border border-white/[0.05] rounded-2xl p-5 xl:col-span-2 flex flex-col backdrop-blur-xl shadow-lg">
            <h3 class="text-[11px] font-semibold text-slate-400 tracking-widest uppercase mb-5">7. Risk Monitor</h3>

            <div class="space-y-4 flex-1">
              <div
                v-for="risk in brief.risks"
                :key="risk.risk"
                class="flex justify-between items-center text-xs group"
              >
                <span class="text-slate-200 font-medium leading-tight pr-3 flex-1">{{ risk.risk }}</span>
                <div class="flex items-center gap-2 flex-shrink-0">
                  <span class="w-1.5 h-1.5 rounded-full" :class="riskLevelStyle(risk.level).dot"/>
                  <span :class="riskLevelStyle(risk.level).text" class="w-12 text-right font-semibold">{{ risk.level }}</span>
                </div>
              </div>
            </div>

            <!-- Composite Risk Index -->
            <div class="mt-6 pt-4 border-t border-white/5">
              <div class="flex justify-between items-center mb-2">
                <span class="text-[10px] font-medium text-slate-500 uppercase tracking-widest">Risk Index (Composite)</span>
                <span class="text-2xl font-bold flex items-baseline gap-0.5" :class="riskIndexColor">
                  {{ riskIndex }}<span class="text-[10px] text-slate-500 font-normal">/100</span>
                </span>
              </div>
              <!-- Progress bar -->
              <div class="h-1.5 bg-white/5 rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full transition-all duration-1000"
                  :class="riskIndex >= 70 ? 'bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.8)]' : riskIndex >= 40 ? 'bg-yellow-400 shadow-[0_0_8px_rgba(250,204,21,0.8)]' : 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]'"
                  :style="{ width: `${riskIndex}%` }"
                />
              </div>
            </div>
          </div>

          <!-- 8. Analyst Takeaway -->
          <div class="bg-white/[0.02] border border-white/[0.05] rounded-2xl p-5 xl:col-span-4 flex flex-col backdrop-blur-xl shadow-lg relative overflow-hidden">
            <!-- Analyst bg glow -->
            <div class="absolute -bottom-10 -right-10 w-48 h-48 bg-emerald-500/10 blur-[50px] rounded-full pointer-events-none"></div>

            <h3 class="text-[11px] font-semibold text-slate-400 tracking-widest uppercase mb-5 relative z-10">8. Analyst Takeaway</h3>

            <div class="relative flex-1 z-10">
              <span class="absolute -top-4 -left-2 text-6xl text-emerald-500/20 leading-none font-serif select-none">"</span>
              <p class="text-[15px] text-slate-200 italic leading-relaxed pl-5 relative z-10 font-medium">{{ brief.analyst.quote }}</p>
            </div>

            <div class="mt-6 flex items-center gap-3 border-t border-white/5 pt-4 relative z-10">
              <img src="/favicon.png" alt="AR" class="w-10 h-10 rounded-full border border-emerald-500/20 flex-shrink-0 object-cover bg-emerald-900/40 p-1" />
              <div>
                <p class="text-sm font-bold text-white">{{ brief.analyst.author }}</p>
                <p class="text-[10px] text-slate-400 font-medium uppercase tracking-widest mt-0.5">{{ brief.analyst.role }}</p>
              </div>
            </div>

            <!-- Top Picks -->
            <div class="mt-4 relative z-10">
              <p class="text-[10px] text-slate-500 uppercase tracking-widest mb-2 font-medium">Top Picks Radar</p>
              <div class="flex gap-2 flex-wrap">
                <span v-for="t in brief.analyst.topPicks" :key="t" class="px-2.5 py-1 text-xs font-bold bg-white/5 text-emerald-400 border border-emerald-500/20 rounded-md">
                  {{ t }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- ── TOP MOVERS STRIP ───────────────────────────────── -->
        <div v-if="hasMovers" class="bg-white/[0.02] border border-white/[0.05] rounded-2xl p-5 mb-6 backdrop-blur-xl shadow-lg relative overflow-hidden">
          <div class="flex items-center justify-between mb-3 relative z-10">
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
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(255, 255, 255, 0.3);
}

@media print {
  /* Sembunyikan header/nav/footer dari AppLayout (global class assumption) */
  header, nav, footer, aside, .app-header, .app-sidebar, #navbar, #footer {
    display: none !important;
  }
  
  @page {
    margin: 0.5cm;
    size: auto;
  }

  /* Force background to be dark and text to be light exactly as UI */
  body {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    background-color: #020617 !important; /* Tailwind slate-950 */
    color: #e2e8f0 !important;
  }

  /* Expand main container to use full space */
  main, #app, .min-h-screen {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  .print\:hidden {
    display: none !important;
  }
}
</style>
