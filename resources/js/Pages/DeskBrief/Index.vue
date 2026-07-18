<script setup>
import { computed, ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import IhsgChart from './Partials/IhsgChart.vue';



const props = defineProps({
  date:        { type: String, default: null },
  isPreview:   { type: Boolean, default: false },
  deskBrief:   { type: Object, default: null },
  snapshots:   { type: Array,  default: () => [] },
  topMovers:   { type: Object, default: () => ({ gainers: [], losers: [] }) },
  mostTraded:  { type: Array,  default: () => [] },
  apiStatus:   { type: Object, default: null },
  sectorBias:  { type: Array, default: () => [] },
  riskAlerts:  { type: Array,  default: () => [] },
  smartMoney:  { type: Object, default: null },
  internals:   { type: Object, default: null },
  events:      { type: Array,  default: () => [] },
  macroCards:  { type: Array,  default: () => [] },
  delta:       { type: Object, default: () => ({}) },
  todayStance: { type: Object, default: null },
  yesterdayStance: { type: Object, default: null },
  periodConclusion: { type: String, default: null },
  historicalScores: { type: Array, default: () => [] },
  ihsgHistory: { type: Array, default: () => [] },
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
    { rank: 1, title: 'IDX Liquidity & Foreign Flow',   impact: 'High',   dots: 3, category: 'Flow/Liquidity', explanation: 'Foreign net inflow mendukung stabilitas.' },
    { rank: 2, title: 'Rupiah, BI & SBN Yield',         impact: 'High',   dots: 3, category: 'Macro/Rate',     explanation: 'Yield dan nilai tukar jadi penentu utama risk appetite.' },
    { rank: 3, title: 'Market Breadth & Internals',     impact: 'Medium', dots: 2, category: 'Market Internals', explanation: 'Partisipasi saham penggerak indeks.' },
    { rank: 4, title: 'Sector Rotation & Leadership',   impact: 'Medium', dots: 2, category: 'Sector Rotation',  explanation: 'Rotasi sektoral ke area defensif atau pro-growth.' },
    { rank: 5, title: 'Indonesia Earnings Outlook',     impact: 'Low',    dots: 1, category: 'Earnings',         explanation: 'Proyeksi earning ke depan masih solid.' },
    { rank: 6, title: 'Domestic Policy & Fiscal Risk',  impact: 'Low',    dots: 1, category: 'Policy/Risk',      explanation: 'Kebijakan fiskal domestik terkendali.' },
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
    lastUpdate: data.updated_at 
        ? new Date(data.updated_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).replace('.', ':') + ' WIB' 
        : (props.date ? new Date(props.date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' 17:00 WIB' : mockData.lastUpdate),
    headline: data.title || mockData.headline,
    subHeadline: props.periodConclusion || data.market_read || mockData.subHeadline,
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
    sectorBiases: props.sectorBias?.length ? props.sectorBias : [],
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

const regimeComponents = computed(() => {
  const ts = props.todayStance;
  if (!ts) return [
    { name: 'Price Trend', score: 50 },
    { name: 'Breadth', score: 50 },
    { name: 'Flow', score: 50 },
    { name: 'Rotasi', score: 50 },
    { name: 'Vol/Liq', score: 50 },
  ];
  return [
    { name: 'Price Trend', score: ts.momentum_score },
    { name: 'Breadth', score: ts.breadth_score },
    { name: 'Flow', score: ts.foreign_score },
    { name: 'Rotasi', score: ts.sector_score },
    { name: 'Vol/Liq', score: ts.rupiah_score },
  ];
});

const positiveComponentsCount = computed(() => {
  return regimeComponents.value.filter(c => c.score >= 50).length;
});

const showCalendarModal = ref(false);
const showRegimeModal = ref(false);

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
const expandMovers = ref(false);
const gainers = computed(() => {
  const all = props.topMovers?.gainers || [];
  return expandMovers.value ? all.slice(0, 20) : all.slice(0, 10);
});
const losers  = computed(() => {
  const all = props.topMovers?.losers || [];
  return expandMovers.value ? all.slice(0, 20) : all.slice(0, 10);
});
const hasMovers = computed(() => gainers.value.length > 0 || losers.value.length > 0);

// ──────────────────────────────────────────────
// Historical Regime Chart
// ──────────────────────────────────────────────
const hoveredScore = ref(null);
const regimeTimeframe = ref('6M');
const regimeTimeframes = ['1M', '6M', '1Y'];

const filteredHistoricalScores = computed(() => {
  const scores = props.historicalScores;
  if (!scores || scores.length === 0) return [];
  
  const tf = regimeTimeframe.value;
  const cutoff = new Date(scores[scores.length - 1].date);
  if (tf === '1M') cutoff.setMonth(cutoff.getMonth() - 1);
  else if (tf === '6M') cutoff.setMonth(cutoff.getMonth() - 6);
  else if (tf === '1Y') cutoff.setFullYear(cutoff.getFullYear() - 1);
  
  return scores.filter(s => new Date(s.date) >= cutoff);
});

const activeScore = computed(() => {
  if (hoveredScore.value) return hoveredScore.value;
  return props.todayStance || { score: 67, label: 'Constructive', date: '2026-06-27' };
});

const chartData = computed(() => {
  const scores = filteredHistoricalScores.value;
  if (scores.length === 0) {
    return { 
      raw: [], 
      points: '14,80 50,75 86,50 122,55 158,90 194,60 230,40 266,45 300,35', 
      lastPoint: {x: 300, y: 35},
      isUp: true,
      firstY: 80
    };
  }
  
  const stepX = (300 - 14) / Math.max(1, scores.length - 1);
  
  const pts = scores.map((s, i) => {
    const x = 14 + (i * stepX);
    const y = 100 - (s.score / 100) * 80;
    return { x, y, data: s };
  });
  
  const isUp = scores[scores.length - 1].score >= scores[0].score;
  const firstY = pts[0].y;
  
  return {
    raw: pts,
    points: pts.map(p => `${p.x.toFixed(1)},${p.y.toFixed(1)}`).join(' '),
    lastPoint: pts[pts.length - 1],
    isUp,
    firstY
  };
});

const avgScore = computed(() => {
  const scores = filteredHistoricalScores.value;
  if (scores.length === 0) return 58;
  const sum = scores.reduce((acc, s) => acc + parseFloat(s.score), 0);
  return Math.round(sum / scores.length);
});

const trendLabel = computed(() => {
  const scores = filteredHistoricalScores.value;
  if (scores.length < 2) return 'Warming';
  const recent = scores[scores.length - 1].score;
  const old = scores[0].score;
  return recent >= old ? 'Warming' : 'Cooling';
});

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

function getSigClass(val) {
  if (val > 0) return 'sig-up';
  if (val < 0) return 'sig-dn';
  return 'sig-neu';
}
function getSigIcon(val) {
  if (val > 0) return '▲';
  if (val < 0) return '▼';
  return '●';
}
function getConfClass(label) {
  if (label === 'Strong') return 'cs-strong';
  if (label === 'Building') return 'cs-strong';
  if (label === 'Watch') return 'cs-mod';
  return 'cs-avoid';
}
</script>


<template>
    <Head>
        <title>Desk Brief | Avenir</title>
        <meta name="description" content="Desk Brief - Avenir Research Market Intelligence." />
        <meta property="og:title" content="Desk Brief | Avenir" />
        <meta property="og:description" content="Desk Brief - Avenir Research Market Intelligence." />
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
    <Head title="Desk Brief - Avenir" />
    <div class="deskbrief-page ">

<div v-if="isPreview" class="preview-banner">
  ⚠️ PREVIEW MODE: You are viewing an unpublished draft.
</div>

<!-- PAGE HEADER -->
<div class="phead">
  <div>
    <div class="ptitle">Desk Brief <span class="star">☆</span></div>
    <div class="psub">Avenir Research Market Intelligence</div>
  </div>
  <div class="right">
    <div class="lastupd">Last Update: <b>{{ brief.lastUpdate }}</b><br><span style="font-size:8.5px;font-weight:normal;color:#999;letter-spacing:0">(Updated daily at EOD)</span></div>
    <div class="pbtns">
      <div class="btn green">⤴ Share Desk Brief</div>
      <div class="btn ghost" @click="printPage">⎙ Print / PDF</div>
      <div class="btn dots">⋮</div>
    </div>
  </div>
</div>

<div class="wrap">
  <IhsgChart :history="ihsgHistory" />

  <!-- HERO -->
  <div class="hero dh">
    <div class="mstance">
      <div class="lab">Market Stance</div>
      <div class="big" :class="scoreColor.text">{{ brief.marketStance.label }}</div>
      <div class="meta">
        <div class="m"><span class="k">Tactical view</span><span class="v">{{ brief.marketStance.view }}</span></div>
        <div class="m"><span class="k">Time horizon</span><span class="v">{{ brief.marketStance.horizon }}</span></div>
        <div class="m"><span class="k">Regime score</span><span class="v pos" :class="scoreColor.text">{{ brief.marketStance.score }} / 100</span></div>
      </div>
    </div>
    <div class="hero-head">
      <div class="eye">Desk Brief Headline</div>
      <h2>{{ brief.headline }}</h2>
      <p>{{ brief.subHeadline }}</p>
      <div class="chips">
        <span class="kchip">Disinflation + Growth</span>
        <span class="kchip">Foreign inflow</span>
        <span class="kchip">Domestic-revenue tilt</span>
        <span class="kchip">Quality + dividend</span>
      </div>
    </div>
    <div class="macros">
      <div class="macro" v-for="m in brief.macroCards" :key="m.title">
        <div class="l">
          <div class="k"><span class="ic"></span>{{ m.title }} &middot; {{ m.status }}</div>
          <div class="s">{{ m.desc }}</div>
        </div>
        <div class="v">{{ m.value }}</div>
      </div>
    </div>
  </div>

  <div class="grid">
    <!-- WHAT CHANGED RIBBON -->
    <div class="span12">
      <div class="changed" v-if="delta && Object.keys(delta).length > 0">
        <div class="lab"><span class="pulse"></span>What Changed<br>vs {{ yesterdayStance ? new Date(yesterdayStance.date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) + ' close' : 'prev close' }}</div>
        
        <div class="chg">
          <div class="k">Regime score</div>
          <div class="v">
            {{ yesterdayStance ? yesterdayStance.score : '-' }} &rarr; {{ todayStance ? todayStance.score : '-' }}
            <span v-if="delta.regime > 0" class="ar pos">&#9650; warming</span>
            <span v-else-if="delta.regime < 0" class="ar neg">&#9660; cooling</span>
            <span v-else class="ar" style="color:var(--muted)">· neutral</span>
          </div>
        </div>

        <div class="chg">
          <div class="k">Stance</div>
          <div class="v">
            {{ todayStance ? todayStance.label.toUpperCase() : brief.marketStance.label }}
            <span v-if="delta.stance_changed" style="color:var(--muted);font-weight:500;margin-left:4px">· changed</span>
            <span v-else style="color:var(--muted);font-weight:500;margin-left:4px">· unchanged</span>
          </div>
        </div>

        <div class="chg" v-if="delta.driver_escalated">
          <div class="k">Driver escalated</div>
          <div class="v">{{ delta.driver_escalated.title }} <span class="ar neg">&#9650; to High</span></div>
        </div>
        <div class="chg" v-if="delta.foreign_flow_text">
          <div class="k">Foreign flow</div>
          <div class="v" v-html="delta.foreign_flow_text"></div>
        </div>
        <div class="chg" v-if="delta.new_risk_flag">
          <div class="k">New risk flag</div>
          <div class="v" :class="delta.new_risk_flag.badge === 'ESCALATED' ? 'neg' : 'amb'">
            {{ delta.new_risk_flag.badge }} &middot; {{ delta.new_risk_flag.title }}
          </div>
        </div>
        <div class="chg" v-if="delta.breadth">
          <div class="k">Breadth</div>
          <div class="v"><span :class="delta.breadth.state === 'Positif' ? 'pos' : (delta.breadth.state === 'Negatif' ? 'neg' : '')">{{ delta.breadth.state }}</span> <span style="color:var(--muted);font-weight:500">({{ delta.breadth.advancers }}&#9650;/{{ delta.breadth.decliners }}&#9660;)</span></div>
        </div>
        <div class="chg" v-if="delta.confluence_sectors && delta.confluence_sectors.length > 0">
          <div class="k">Confluence</div>
          <div class="v pos">{{ delta.confluence_sectors.join(', ') }} &#9650;</div>
        </div>
      </div>
    </div>

    <!-- 1. REGIME SUMMARY -->
    <div class="card span3">
      <div class="chd"><div class="t"><b>1.</b>REGIME SUMMARY</div></div>
      <div class="regime-ring" style="align-self:center; display:flex; flex-direction:column; align-items:center; position:relative; margin-top:8px">
        <div class="rw" style="position:relative; width:120px; height:70px; display:flex; justify-content:center">
          <svg width="120" height="70" viewBox="0 0 96 60" style="overflow:visible">
            <circle cx="48" cy="48" r="40" fill="none" stroke="#222" stroke-width="8" stroke-dasharray="125.66 251.32" transform="rotate(180 48 48)"/>
            <circle cx="48" cy="48" r="40" fill="none" :stroke="todayStance && todayStance.score < 40 ? '#E2705C' : (todayStance && todayStance.score < 60 ? '#D99B3E' : '#46C46E')" stroke-width="8" stroke-linecap="round" stroke-dasharray="125.66 251.32" :stroke-dashoffset="125.66 - ((todayStance ? todayStance.score : 0) / 100 * 125.66)" transform="rotate(180 48 48)"/>
          </svg>
          <div class="rv" style="position:absolute; bottom:0; left:0; right:0; text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; padding-bottom:4px">
            <div class="n" style="font-size:28px; font-weight:700; color:#fff; line-height:1">{{ todayStance ? todayStance.score : 0 }}</div>
            <div class="o" style="font-size:10px; color:#888; font-weight:600">/100</div>
          </div>
        </div>
        <div class="rl pos" :style="{ color: todayStance && todayStance.score < 40 ? '#E2705C' : (todayStance && todayStance.score < 60 ? '#D99B3E' : '#46C46E'), marginTop: '4px', fontSize: '13px', fontWeight: '700', letterSpacing: '0.5px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '6px' }">
          {{ todayStance ? todayStance.label.toUpperCase() : 'UNKNOWN' }}
          <svg @click="showRegimeModal = true" style="cursor:pointer; width:16px; height:16px; fill:currentColor" viewBox="0 0 24 24"><path d="M11 7h2v2h-2zm0 4h2v6h-2zm1-9C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
        </div>
      </div>
      <div style="font-size:8.5px;color:var(--faint);text-transform:uppercase;letter-spacing:.08em;margin-top:14px;font-weight:600">Komponen skor</div>
      <div class="rcomp">
        <span v-for="c in regimeComponents" :key="c.name" :class="['rc', c.score >= 50 ? 'up' : 'dn']">
          {{ c.name }} {{ c.score >= 50 ? '▲' : '▼' }}
        </span>
      </div>
      <div class="traj" style="font-size:11px;color:#aaa;line-height:1.4">
        Trajectory: <span :class="delta && delta.regime > 0 ? 'pos' : (delta && delta.regime < 0 ? 'neg' : '')"><b>{{ delta && delta.regime_trend ? delta.regime_trend : 'neutral' }}</b></span> · {{ yesterdayStance ? yesterdayStance.score : '-' }} → {{ todayStance ? todayStance.score : '-' }} ({{ positiveComponentsCount }} dari 5 komponen positif)
      </div>
      <div class="csrc" v-if="yesterdayStance" style="font-size:10px;color:#666;margin-top:12px">Prev. Score: {{ yesterdayStance.score }} ({{ new Date(yesterdayStance.date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }})</div>
    </div>

    <!-- 2. KEY DRIVERS -->
    <div class="card span3">
      <div class="chd"><div class="t"><b>2.</b>KEY DRIVERS</div><div class="meta">Ranked by impact</div></div>
      <div class="kd">
        <div class="kdr" v-for="driver in deskBrief.drivers" :key="driver.id">
          <span class="rk">{{ driver.rank }}</span>
          <div class="bd">
            <div class="t">
              {{ driver.title }}
              <span v-if="driver.explanation" :class="['ctag', driver.explanation.includes('▼') ? 'ct-dn' : 'ct-up']">
                {{ driver.explanation }}
              </span>
            </div>
            <div class="c">{{ driver.category }}</div>
          </div>
          <div class="imp">
            <span class="idots3">
              <i v-for="n in 3" :key="n" :style="{ background: n <= (driver.impact_level === 'HIGH' ? 3 : (driver.impact_level === 'MEDIUM' ? 2 : 1)) ? (driver.impact_level === 'HIGH' ? '#E2705C' : (driver.impact_level === 'MEDIUM' ? '#D99B3E' : '#46C46E')) : '#333' }"></i>
            </span>
            <span :class="['lvl', driver.impact_level === 'HIGH' ? 'lvl-high' : (driver.impact_level === 'MEDIUM' ? 'lvl-med' : 'lvl-low')]">
              {{ driver.impact_level === 'HIGH' ? 'High' : (driver.impact_level === 'MEDIUM' ? 'Medium' : 'Low') }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. CROSS-ASSET SNAPSHOT + INTERNALS -->
    <div class="card span3">
      <div class="chd"><div class="t"><b>3.</b>CROSS-ASSET &amp; INTERNALS</div><div class="meta"><span class="live"><span class="d"></span>Live EOD</span></div></div>
      <div class="idx3">
        <div class="idxc" v-for="s in idxSnapshots" :key="s.symbol">
          <div class="k">{{ s.symbol }}</div>
          <div class="v">{{ s.price }}</div>
          <div class="c" :class="parseFloat(s.change) > 0 ? 'pos' : (parseFloat(s.change) < 0 ? 'neg' : 'amb')">{{ s.change }}</div>
        </div>
      </div>
      <div class="oalbl">Other Assets</div>
      <div class="oa">
        <div v-for="s in otherSnapshots" :key="s.symbol">
          <div class="k">{{ s.symbol }}</div>
          <div class="v">{{ s.price }}</div>
          <div class="c" :class="parseFloat(s.change) > 0 ? 'pos' : (parseFloat(s.change) < 0 ? 'neg' : 'amb')">{{ s.change }}</div>
        </div>
      </div>
      <div class="oalbl">Market Internals</div>
      <div class="intern">
        <div class="it"><div class="k">Breadth A/D</div><div class="v">{{ brief.internals.advances ?? brief.internals.advancers ?? 520 }} / {{ brief.internals.declines ?? brief.internals.decliners ?? 159 }}</div></div>
        <div class="it"><div class="k">% &gt; 200DMA</div><div class="v">{{ brief.internals.above200dma ?? '58%' }}</div></div>
        <div class="it"><div class="k">New H / L</div><div class="v">{{ brief.internals.newHighs ?? brief.internals.new_highs ?? 12 }} / {{ brief.internals.newLows ?? brief.internals.new_lows ?? 9 }}</div></div>
      </div>
      <div class="csrc">Index −1.7% tapi breadth hanya mild negatif &amp; 58% di atas 200DMA → koreksi dangkal, bukan distribusi luas.</div>
    </div>

    <!-- 4. REGIME TREND CHART -->
    <div class="card span3">
      <div class="chd">
        <div class="t"><b>4.</b>REGIME SCORE TREND</div>
        <div class="toggles">
          <span v-for="tf in regimeTimeframes" :key="tf" :class="['tg', regimeTimeframe === tf ? 'on' : '']" @click="regimeTimeframe = tf">{{ tf }}</span>
        </div>
      </div>
      <div style="font-size:9px;color:var(--muted);margin-bottom:4px">Historical Regime Score ({{ regimeTimeframe }})</div>
      <svg width="100%" viewBox="0 0 320 120" style="display:block; overflow:visible" @mouseleave="hoveredScore = null">
        <line x1="14" :y1="chartData.firstY" x2="314" :y2="chartData.firstY" stroke="#2E2E2E" stroke-dasharray="3 4"/>
        <polyline :points="chartData.points" fill="none" :stroke="chartData.isUp ? '#46C46E' : '#E2705C'" stroke-width="2.2" stroke-linejoin="round"/>
        <circle v-if="chartData.lastPoint" :cx="chartData.lastPoint.x" :cy="chartData.lastPoint.y" r="4" :fill="chartData.isUp ? '#46C46E' : '#E2705C'"/>
        <circle v-for="(pt, i) in chartData.raw" :key="'hover_'+i"
                :cx="pt.x" :cy="pt.y" r="8" fill="transparent"
                style="cursor:crosshair"
                @mouseenter="hoveredScore = pt.data" />
        <g font-size="7.5" fill="#7C7C76">
          <text x="14" y="115">{{ filteredHistoricalScores.length ? new Date(filteredHistoricalScores[0].date).toLocaleDateString('id-ID', { month: 'short', year: '2-digit' }) : regimeTimeframe + ' Ago' }}</text>
          <text x="300" y="115" text-anchor="end">{{ filteredHistoricalScores.length ? new Date(filteredHistoricalScores[filteredHistoricalScores.length - 1].date).toLocaleDateString('id-ID', { month: 'short', year: '2-digit' }) : 'Now' }}</text>
        </g>
      </svg>
      <div class="smstats">
        <div class="sms"><div class="k">Selected Score</div><div class="v pos">{{ activeScore.score }}</div><div class="s">{{ activeScore.label }}</div></div>
        <div class="sms"><div class="k">Avg ({{ regimeTimeframe }})</div><div class="v">{{ avgScore }}</div><div class="s">moderate</div></div>
        <div class="sms"><div class="k">Trend</div><div class="v pos" :class="trendLabel === 'Cooling' ? 'neg' : ''">{{ trendLabel }}</div><div class="s">vs {{ regimeTimeframe }} ago</div></div>
      </div>
      <div class="csrc">Source: Avenir Regime Calculation · {{ activeScore.date ? new Date(activeScore.date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '27 Jun 2026' }}</div>
    </div>

    <!-- 5. SIGNAL CONFLUENCE -->
    <div class="card span6">
      <div class="chd"><div class="t"><b>5.</b>SIGNAL CONFLUENCE <span style="color:var(--faint);font-weight:400;letter-spacing:0;text-transform:none">— di mana sinyal sepakat</span></div><div class="meta">Rotation × Flow × Valuation × Event</div></div>
      <table class="conf">
        <thead><tr><th>Sektor</th><th>Rotasi<br>1M</th><th>Smart<br>Money</th><th>Valuasi</th><th>Event</th><th>Confluence</th></tr></thead>
        <tbody>
          <tr v-for="sec in brief.sectorBiases" :key="sec.sector">
            <td class="sct">{{ sec.sector }}</td>
            <td :class="['sig', getSigClass(sec.rotation_score)]">{{ getSigIcon(sec.rotation_score) }}</td>
            <td :class="['sig', getSigClass(sec.smart_money_score)]">{{ getSigIcon(sec.smart_money_score) }}</td>
            <td :class="['sig', getSigClass(sec.valuation_score)]">{{ getSigIcon(sec.valuation_score) }}</td>
            <td :class="['sig', getSigClass(sec.event_score)]">{{ getSigIcon(sec.event_score) }}</td>
            <td><span :class="['confscore', getConfClass(sec.confluence_label)]">{{ sec.confluence_label }}</span></td>
          </tr>
        </tbody>
      </table>
      <div class="note">Confluence tertinggi di <b>Banking &amp; Telco</b> — rotasi, akumulasi asing, valuasi, dan katalis searah. Plantation menguat (flow + valuasi via Valuation Lab + CPO). <b>Property &amp; Tech</b>: keempat sinyal negatif → hindari.</div>
    </div>

    <!-- 6. SMART MONEY LENS -->
    <div class="card span6">
      <div class="chd"><div class="t"><b>6.</b>SMART MONEY LENS <span style="color:var(--faint);font-weight:400;letter-spacing:0;text-transform:none">— yang retail tak lihat</span></div><div class="meta">Badarmologi · 5D net</div></div>
      <div class="smlens">
        <div class="smlblock">
          <div class="h pos">▲ Stealth Accumulation <span style="color:var(--muted);font-weight:400;text-transform:none;letter-spacing:0">· harga diam, asing/bandar masuk</span></div>
          <div class="smlrow"><span class="tk">BMRI</span><span class="px neg">−0.4%</span><span class="fl pos">+Rp 210 bn</span><span class="nt">foreign 5D</span></div>
          <div class="smlrow"><span class="tk">ICBP</span><span class="px pos">+0.2%</span><span class="fl pos">+Rp 88 bn</span><span class="nt">broker net buy</span></div>
          <div class="smlrow"><span class="tk">ASII</span><span class="px neg">−0.6%</span><span class="fl pos">+Rp 95 bn</span><span class="nt">foreign 5D</span></div>
        </div>
        <div class="smlblock">
          <div class="h" style="color:var(--amber)">⚠ Distribution into Strength <span style="color:var(--muted);font-weight:400;text-transform:none;letter-spacing:0">· harga naik, bandar keluar</span></div>
          <div class="smlrow"><span class="tk">BHAT</span><span class="px pos">+20.2%</span><span class="fl" style="color:var(--amber)">retail spike</span><span class="nt">foreign absen</span></div>
          <div class="smlrow"><span class="tk">SURE</span><span class="px pos">+9.8%</span><span class="fl" style="color:var(--amber)">−Rp 41 bn</span><span class="nt">broker net sell</span></div>
          <div class="smlrow"><span class="tk">MGLV</span><span class="px neg">−9.9%</span><span class="fl" style="color:var(--amber)">no support</span><span class="nt">likuiditas tipis</span></div>
        </div>
      </div>
      <div class="note warn">Top gainer hari ini (<b>BHAT +20%</b>) <b>tidak terkonfirmasi smart money</b> — pola retail-driven, rawan reversal. Sebaliknya BMRI &amp; ASII diakumulasi diam-diam meski harga datar.</div>
    </div>

    <!-- 7. SECTOR ROTATION -->
    <div class="card span3">
      <div class="chd"><div class="t"><b>7.</b>SECTOR ROTATION</div><div class="meta">Heatmap · 1M Chg%</div></div>
      <div class="sheat">
        <div class="sh" v-for="sec in brief.sectors" :key="sec.name" :style="{ background: sectorBg(sec.change) }">
          <div class="n">{{ sec.name }}</div>
          <div class="p">{{ sec.change > 0 ? '+' : '' }}{{ sec.change }}%</div>
        </div>
      </div>
      <div class="scale"><span>−5%</span><span>Avenir · {{ todayStance ? new Date(todayStance.date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) : '27 Jun' }}</span><span>+5%</span></div>
    </div>

    <!-- 8. CATALYST CALENDAR -->
    <div class="card span3">
      <div class="chd"><div class="t"><b>8.</b>CATALYST CALENDAR</div></div>
      <div class="caltabs"><span class="caltab on">Upcoming</span><span class="caltab">Past Events</span></div>
      <div class="cal2-wrap">
        <table class="cal2">
          <thead><tr><th>Date</th><th>Event</th><th>Imp</th></tr></thead>
          <tbody>
            <tr v-for="(c, idx) in filteredCatalysts" :key="idx">
              <td class="dt">{{ c.date }}</td>
              <td class="ev" v-html="c.event"></td>
              <td><span :class="'lvl-' + (c.impact === 'High' ? 'high' : (c.impact === 'Medium' ? 'med' : 'low'))" style="font-weight:700;font-size:9px">● {{ c.impact }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="viewall">View Full Calendar →</div>
    </div>

    <!-- 9. RISK MONITOR -->
    <div class="card span3">
      <div class="chd"><div class="t"><b>9.</b>RISK MONITOR</div><div class="meta amb">↑ rising</div></div>
      <div class="risk">
        <div class="rkr"><span class="n">US Policy / Rates Shock</span><span class="lvl lvl-high"><span class="dt"></span>High ▲</span></div>
        <div class="rkr"><span class="n">Geopolitical Tension</span><span class="lvl lvl-high"><span class="dt"></span>High</span></div>
        <div class="rkr"><span class="n">Global Recession Risk</span><span class="lvl lvl-med"><span class="dt"></span>Medium</span></div>
        <div class="rkr"><span class="n">China Growth Slowdown</span><span class="lvl lvl-med"><span class="dt"></span>Medium</span></div>
        <div class="rkr"><span class="n">Commodity Price Shock</span><span class="lvl lvl-med"><span class="dt"></span>Medium</span></div>
        <div class="rkr"><span class="n">IDR Volatility</span><span class="lvl lvl-low"><span class="dt"></span>Low ▲</span></div>
        <div class="rkr"><span class="n">Domestic Policy Risk</span><span class="lvl lvl-low"><span class="dt"></span>Low</span></div>
      </div>
      <div class="rkidx"><div><div class="lab">Risk Index (Composite)</div><div class="rkbar" style="width:148px"><i style="width:55%"></i></div></div><div class="v">55<span style="font-size:11px;color:var(--muted)">/100</span></div></div>
      <div class="csrc">Transition watch: yield SBN &gt; 7.0% atau DXY break → naikkan ke Defensive.</div>
    </div>

    <!-- 10. ANALYST TAKEAWAY -->
    <div class="card span3">
      <div class="chd"><div class="t"><b>10.</b>ANALYST TAKEAWAY</div></div>
      <div style="font-size:12px;line-height:1.6;color:var(--ink2);font-style:italic">Selective risk-on. Lean ke <b style="color:var(--ink);font-style:normal">confluence tertinggi (Banks, Telco)</b> dengan domestic revenue &amp; dividend. Ikuti akumulasi diam-diam (BMRI/ASII), hindari mengejar spike retail. Avoid Property &amp; Tech sampai yield mereda.</div>
      <div class="byline" style="margin-top:13px"><div class="av">AR</div><div><div class="nm">Riset Avenir Research</div><div class="rl">Head of Market Intelligence · reviewed</div></div></div>
      <div class="picks"><span class="pl">Top Picks</span><span class="pk">BBCA</span><span class="pk">TLKM</span><span class="pk">UNVR</span><span class="pk">AMMN</span><span class="pk">PGAS</span></div>
    </div>

    <!-- TOP MOVERS -->
    <div class="card span12">
      <div class="chd"><div class="t">TOP MOVERS IDX <span style="color:var(--faint);font-weight:400;text-transform:none;letter-spacing:0">(1D — Sectors.app EOD · ⚑ = flow-confirmed)</span></div><div class="meta"><span class="live"><span class="d"></span>Live EOD</span></div></div>
      <div class="movers">
        <div>
          <div class="movhd pos">▲ Top Gainers</div>
          <div class="mvr" v-for="(g, idx) in gainers" :key="g.symbol">
            <span class="rk">{{ idx + 1 }}</span>
            <span :class="['ff', g.flow_confirmed !== false ? 'ff-ok' : 'ff-warn']">{{ g.flow_confirmed !== false ? '⚑' : '⚠' }}</span>
            <img v-if="g.logo_url" :src="g.logo_url" :alt="g.symbol" style="width:16px; height:16px; border-radius:50%; object-fit:cover; flex-shrink:0" />
            <span v-else style="width:16px; height:16px; border-radius:50%; background:#222; display:flex; align-items:center; justify-content:center; font-size:7px; color:#666; font-weight:700; flex-shrink:0">{{ g.symbol.substring(0,2) }}</span>
            <span class="tk">{{ g.symbol }}</span>
            <span class="nm">{{ g.name || g.symbol }}</span>
            <span class="pr">{{ g.last_close || '-' }}</span>
            <span class="ch pos">+{{ g.price_pct }}%</span>
          </div>
          <div v-if="!gainers.length" style="padding:10px;font-size:11px;color:var(--muted)">No gainers data available.</div>
        </div>
        <div>
          <div class="movhd neg">▼ Top Losers</div>
          <div class="mvr" v-for="(l, idx) in losers" :key="l.symbol">
            <span class="rk">{{ idx + 1 }}</span>
            <span :class="['ff', l.flow_confirmed !== false ? 'ff-warn' : 'ff-ok']">{{ l.flow_confirmed !== false ? '⚠' : '⚑' }}</span>
            <img v-if="l.logo_url" :src="l.logo_url" :alt="l.symbol" style="width:16px; height:16px; border-radius:50%; object-fit:cover; flex-shrink:0" />
            <span v-else style="width:16px; height:16px; border-radius:50%; background:#222; display:flex; align-items:center; justify-content:center; font-size:7px; color:#666; font-weight:700; flex-shrink:0">{{ l.symbol.substring(0,2) }}</span>
            <span class="tk">{{ l.symbol }}</span>
            <span class="nm">{{ l.name || l.symbol }}</span>
            <span class="pr">{{ l.last_close || '-' }}</span>
            <span class="ch neg">{{ l.price_pct }}%</span>
          </div>
          <div v-if="!losers.length" style="padding:10px;font-size:11px;color:var(--muted)">No losers data available.</div>
        </div>
      </div>
      <div v-if="hasMovers" style="text-align:center; padding: 10px; margin-top: 10px; grid-column: span 12;">
        <button @click="expandMovers = !expandMovers" class="btn ghost" style="font-size:11px; cursor:pointer; padding: 6px 12px">
          {{ expandMovers ? 'Lebih Sedikit ∧' : 'Selengkapnya (Top 20) ∨' }}
        </button>
      </div>
    </div>
  </div>
</div>




  </div>

  <!-- Regime Modal Popup -->
  <div v-if="showRegimeModal" class="db-modal-overlay" @click.self="showRegimeModal = false">
    <div class="db-modal">
      <h3 style="text-align:center;font-size:16px;margin-bottom:20px;font-weight:700">Breakdown Perhitungan Regime</h3>
      
      <div class="db-modal-item">
        <b>1. Price Trend (Momentum):</b> Skor <span :class="todayStance && todayStance.momentum_score >= 50 ? 'su' : 'sd'">{{ todayStance ? todayStance.momentum_score : 50 }}</span> Bobot 30% &rarr; {{ todayStance ? todayStance.momentum_score : 50 }} &times; 30% = <b>{{ ((todayStance ? todayStance.momentum_score : 50) * 0.3).toFixed(2) }}</b><br>
        <small>(Skor moderat, tren harga IHSG berada dalam fase konsolidasi atau transisi).</small>
      </div>
      
      <div class="db-modal-item">
        <b>2. Market Breadth:</b> Skor <span :class="todayStance && todayStance.breadth_score >= 50 ? 'su' : 'sd'">{{ todayStance ? todayStance.breadth_score : 50 }}</span> Bobot 25% &rarr; {{ todayStance ? todayStance.breadth_score : 50 }} &times; 25% = <b>{{ ((todayStance ? todayStance.breadth_score : 50) * 0.25).toFixed(2) }}</b><br>
        <small>(Perbandingan saham naik dan turun relatif seimbang di pasar).</small>
      </div>
      
      <div class="db-modal-item">
        <b>3. Flow (Foreign):</b> Skor <span :class="todayStance && todayStance.foreign_score >= 50 ? 'su' : 'sd'">{{ todayStance ? todayStance.foreign_score : 50 }}</span> Bobot 20% &rarr; {{ todayStance ? todayStance.foreign_score : 50 }} &times; 20% = <b>{{ ((todayStance ? todayStance.foreign_score : 50) * 0.2).toFixed(2) }}</b><br>
        <small>(Aliran dana asing cenderung netral atau mixed).</small>
      </div>
      
      <div class="db-modal-item">
        <b>4. Sector Rotation:</b> Skor <span :class="todayStance && todayStance.sector_score >= 50 ? 'su' : 'sd'">{{ todayStance ? todayStance.sector_score : 50 }}</span> Bobot 15% &rarr; {{ todayStance ? todayStance.sector_score : 50 }} &times; 15% = <b>{{ ((todayStance ? todayStance.sector_score : 50) * 0.15).toFixed(2) }}</b><br>
        <small>(Rotasi sektor berjalan mulus dengan partisipasi merata dari berbagai sektor).</small>
      </div>
      
      <div class="db-modal-item">
        <b>5. Volatility &amp; Liquidity:</b> Skor <span :class="todayStance && todayStance.rupiah_score >= 50 ? 'su' : 'sd'">{{ todayStance ? todayStance.rupiah_score : 50 }}</span> Bobot 10% &rarr; {{ todayStance ? todayStance.rupiah_score : 50 }} &times; 10% = <b>{{ ((todayStance ? todayStance.rupiah_score : 50) * 0.1).toFixed(2) }}</b><br>
        <small>(Kondisi likuiditas pasar dan volatilitas pada batas wajar).</small>
      </div>
      
      <div style="border-top:1px solid var(--line2); margin-top:15px; padding-top:15px; font-size:15px">
        <b>Total Final Score: <span style="color:#3B82F6">{{ todayStance ? todayStance.score.toFixed(2) : '50.00' }}</span></b>
      </div>
      
      <div style="text-align:right; margin-top:20px">
        <button @click="showRegimeModal = false" style="background:#3B82F6; color:#fff; border:none; padding:8px 16px; border-radius:4px; font-weight:600; cursor:pointer">Tutup</button>
      </div>
    </div>
  </div>

  </AppLayout>
</template>

<style scoped>
.deskbrief-page {
  --bg:#090B0A; --bg2:#0E0E0E; --card:#151515; --card2:#1A1A1A; --inset:#101010;
  --line:#242424; --line2:#2E2E2E; --line3:#383838;
  --ink:#EAEAE7; --ink2:#B6B6B0; --muted:#7C7C76; --faint:#565651;
  --green:#46C46E; --green2:#2E9E55; --greend:#1C6B3C; --greensoft:rgba(70,196,110,.12);
  --red:#E2705C; --redsoft:rgba(226,112,92,.12); --reddim:#9A4438;
  --amber:#D99B3E; --gold:#C9A227; --blue:#5FA0D8;
  --sans:'Liberation Sans',Arial,'DejaVu Sans',sans-serif;
  --mono:'Liberation Mono','DejaVu Sans Mono',monospace;
}
*{margin:0;padding:0;box-sizing:border-box}
.deskbrief-page{background:var(--bg);font-family:var(--sans);color:var(--ink);-webkit-font-smoothing:antialiased;width:100%;min-height:100vh;overflow-x:hidden;}
.num{font-variant-numeric:tabular-nums}
.pos{color:var(--green)}.neg{color:var(--red)}.amb{color:var(--amber)}
.dotr{width:6px;height:6px;border-radius:50%;display:inline-block}

.preview-banner {
  background: #facc15;
  color: #000;
  text-align: center;
  padding: 8px;
  font-weight: 700;
  font-size: 13px;
  letter-spacing: 0.05em;
}

/* ---------------- top nav ---------------- */
.nav{height:58px;background:#0C0C0C;border-bottom:1px solid var(--line);display:flex;align-items:center;padding:0 26px;gap:30px}
.logo{display:flex;align-items:center;gap:9px;font-weight:700;font-size:18px;letter-spacing:.06em;color:var(--ink)}
.logo .mk{width:0;height:0;border-left:7px solid transparent;border-right:7px solid transparent;border-bottom:13px solid var(--green)}
.logo b{color:var(--green);font-weight:700}
.navlinks{display:flex;gap:22px;align-items:center}
.navlinks a{font-size:13px;color:var(--ink2);text-decoration:none;display:flex;align-items:center;gap:5px}
.navlinks a.active{color:var(--green);font-weight:600;position:relative}
.navlinks a.active::after{content:"";position:absolute;left:0;right:0;bottom:-19px;height:2px;background:var(--green)}
.navlinks .car{font-size:9px;color:var(--muted)}
.navright{margin-left:auto;display:flex;align-items:center;gap:14px}
.search{background:#161616;border:1px solid var(--line2);border-radius:18px;height:32px;width:128px;display:flex;align-items:center;gap:8px;padding:0 13px;font-size:12px;color:var(--muted)}
.userpill{background:#161616;border:1px solid var(--line2);border-radius:18px;height:32px;display:flex;align-items:center;gap:8px;padding:0 12px;font-size:12px;color:var(--ink2)}
.userpill .av{width:16px;height:16px;border-radius:50%;background:var(--blue)}
.bell{width:18px;height:18px;border:1.6px solid var(--muted);border-radius:6px 6px 0 0}

/* ---------------- page header ---------------- */
.phead{max-width:1200px;margin:0 auto;padding:20px 26px 16px;display:flex;align-items:flex-start;justify-content:space-between;border-bottom:1px solid var(--line)}
.crumb{font-size:11px;color:var(--muted);margin-bottom:7px}
.crumb b{color:var(--green)}
.ptitle{font-size:26px;font-weight:700;letter-spacing:-.01em;display:flex;align-items:center;gap:10px}
.ptitle .star{color:var(--faint);font-size:18px}
.ptitle .tk{font-size:12px;font-weight:700;color:var(--green);background:var(--greensoft);border:1px solid rgba(70,196,110,.3);padding:3px 9px;border-radius:5px;letter-spacing:.04em}
.psub{font-size:12.5px;color:var(--muted);margin-top:6px}
.phead .right{text-align:right;display:flex;flex-direction:column;align-items:flex-end;gap:11px}
.lastupd{font-size:11.5px;color:var(--muted)}.lastupd b{color:var(--ink2)}
.pbtns{display:flex;gap:9px}
.btn{font-size:12px;font-weight:600;border-radius:7px;padding:8px 13px;display:inline-flex;align-items:center;gap:7px;cursor:default}
.btn.green{background:transparent;border:1px solid var(--green2);color:var(--green)}
.btn.ghost{background:#161616;border:1px solid var(--line2);color:var(--ink2)}
.btn.dots{background:#161616;border:1px solid var(--line2);color:var(--muted);padding:8px 11px}

/* ---------------- hero band ---------------- */
.wrap{max-width:1200px;margin:0 auto;padding:18px 26px 30px}
.hero{background:linear-gradient(180deg,#141414,#111);border:1px solid var(--line2);border-radius:12px;padding:20px 24px;display:grid;grid-template-columns:248px 1fr 396px;gap:26px;position:relative;overflow:hidden}
.hero::after{content:"";position:absolute;right:-60px;top:-60px;width:260px;height:260px;border:1px solid rgba(70,196,110,.10);border-radius:50%}
.vlab-verdict .lab{font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:var(--muted);font-weight:600}
.vlab-verdict .big{font-size:34px;font-weight:700;color:var(--green);letter-spacing:-.01em;line-height:1.05;margin-top:8px}
.vlab-verdict .rev{display:inline-flex;align-items:center;gap:6px;margin-top:11px;font-size:10px;font-weight:600;color:var(--green);background:var(--greensoft);border:1px solid rgba(70,196,110,.3);padding:4px 9px;border-radius:20px}
.vlab-verdict .meta{margin-top:14px;display:flex;flex-direction:column;gap:7px}
.vlab-verdict .meta .m{display:flex;justify-content:space-between;font-size:11.5px;border-bottom:1px solid var(--line);padding-bottom:6px}
.vlab-verdict .meta .m:last-child{border-bottom:none}
.vlab-verdict .meta .k{color:var(--muted)}.vlab-verdict .meta .v{color:var(--ink);font-weight:600}
.hero-head .eye{font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:var(--muted);font-weight:600}
.hero-head h2{font-size:22px;font-weight:700;color:var(--ink);margin-top:9px;line-height:1.28;letter-spacing:-.01em;max-width:560px}
.hero-head p{font-size:13px;color:var(--ink2);line-height:1.6;margin-top:12px;max-width:580px}
.hero-head .chips{display:flex;gap:7px;margin-top:14px;flex-wrap:wrap}
.kchip{font-size:10.5px;font-weight:600;color:var(--ink2);background:#1B1B1B;border:1px solid var(--line2);padding:5px 10px;border-radius:5px}
.hero-stats{display:flex;flex-direction:column;gap:11px;border-left:1px solid var(--line);padding-left:24px}
.hstat{display:flex;align-items:center;justify-content:space-between}
.hstat .l{display:flex;flex-direction:column}
.hstat .l .k{font-size:9.5px;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);font-weight:600}
.hstat .l .s{font-size:10px;color:var(--faint);margin-top:2px}
.hstat .v{font-size:26px;font-weight:700;letter-spacing:-.01em}

/* ---------------- grid + cards ---------------- */
.grid{display:grid;grid-template-columns:repeat(12,1fr);gap:14px;margin-top:14px}
.card{background:var(--card);border:1px solid var(--line);border-radius:10px;padding:15px 16px;min-width:0;display:flex;flex-direction:column}
.span3{grid-column:span 3}.span4{grid-column:span 4}.span5{grid-column:span 5}
.span6{grid-column:span 6}.span8{grid-column:span 8}.span12{grid-column:span 12}
.chd{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.chd .t{font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--ink2);font-weight:700}
.chd .t b{color:#888888;font-weight:700;margin-right:5px}
.chd .meta{font-size:10px;color:var(--muted);display:flex;align-items:center;gap:6px}
.live{display:inline-flex;align-items:center;gap:5px;font-size:10px;color:var(--green);font-weight:600}
.live .d{width:6px;height:6px;border-radius:50%;background:var(--green)}
.toggles{display:flex;gap:4px}
.tg{font-size:9.5px;font-weight:600;color:var(--muted);padding:3px 7px;border-radius:4px;border:1px solid transparent}
.tg.on{background:#222;color:var(--ink);border-color:var(--line3)}
.csrc{font-size:9px;color:var(--faint);margin-top:auto;padding-top:11px}

/* twin rings */
.regime-rings{display:flex;gap:10px;justify-content:space-between}
.regime-ring{flex:1;display:flex;flex-direction:column;align-items:center;gap:7px}
.regime-ring .rw{position:relative;width:96px;height:96px}
.regime-ring .rv{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center}
.regime-ring .rv .n{font-size:24px;font-weight:700;line-height:1}
.regime-ring .rv .o{font-size:9px;color:var(--muted)}
.regime-ring .rl{font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);font-weight:700 !important;text-align:center}
.compbars{display:flex;flex-direction:column;gap:7px;margin-top:14px}
.cb{display:grid;grid-template-columns:96px 1fr 30px;align-items:center;gap:9px;font-size:10px}
.cb .n{color:var(--ink2)}
.cb .tr{height:5px;background:#222;border-radius:3px;overflow:hidden}
.cb .tr i{display:block;height:100%;border-radius:3px}
.cb .vv{text-align:right;color:var(--ink);font-weight:600}

/* snapshot multiples tiles */
.mtiles{display:grid;grid-template-columns:repeat(4,1fr);gap:9px}
.mt{background:var(--inset);border:1px solid var(--line);border-radius:8px;padding:11px 12px;position:relative}
.mt .k{font-size:9.5px;letter-spacing:.04em;text-transform:uppercase;color:var(--muted);font-weight:600}
.mt .v{font-size:19px;font-weight:700;margin-top:6px;color:var(--ink)}
.mt .s{font-size:9.5px;color:var(--faint);margin-top:3px}
.mt .dots{position:absolute;top:11px;right:11px;display:flex;gap:2px}
.mt .dots i{width:5px;height:5px;border-radius:50%}
.mt .tag{font-size:8px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;margin-top:6px;display:inline-block}
.t-cheap{color:var(--green)}.t-fair{color:var(--amber)}.t-rich{color:var(--red)}

/* matrix scatter */
.scatterwrap{flex:1;display:flex;flex-direction:column}
.qlegend{display:flex;flex-wrap:wrap;gap:6px 12px;margin-top:8px;font-size:10px;color:var(--ink2)}
.qlegend span{display:flex;align-items:center;gap:5px}
.qlegend i{width:8px;height:8px;border-radius:50%}

/* tables (peer / movers style) */
.tbl{width:100%;border-collapse:collapse;font-size:12px}
.tbl th{text-align:right;font-size:9px;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);font-weight:600;padding:7px 8px;border-bottom:1px solid var(--line2)}
.tbl th:first-child,.tbl td:first-child{text-align:left}
.tbl td{text-align:right;padding:8px 8px;border-bottom:1px solid var(--line);color:var(--ink2)}
.tbl tr:last-child td{border-bottom:none}
.tbl .tk{font-weight:700;color:var(--ink)}
.tbl tr.me td{background:rgba(70,196,110,.06)}
.tbl tr.me .tk{color:var(--green)}
.heat{border-radius:4px;padding:3px 7px;font-weight:600;color:#0A0A0A;display:inline-block;min-width:40px}

/* dupont */
.dupont{display:flex;align-items:stretch;gap:6px}
.dp{flex:1;background:var(--inset);border:1px solid var(--line);border-radius:7px;padding:9px 6px;text-align:center}
.dp .k{font-size:8px;letter-spacing:.03em;text-transform:uppercase;color:var(--muted);font-weight:600;line-height:1.2}
.dp .v{font-size:17px;font-weight:700;margin-top:5px}
.dp .nn{font-size:8px;color:var(--faint);margin-top:2px}
.dpop{display:flex;align-items:center;color:var(--muted);font-size:14px;font-weight:700}

/* ledger / rows */
.rows{display:flex;flex-direction:column}
.rw2{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:7px 0;border-bottom:1px solid var(--line);font-size:11.5px}
.rw2:last-child{border-bottom:none}
.rw2 .k{color:var(--ink2)}.rw2 .k small{display:block;color:var(--faint);font-size:9px;margin-top:1px}
.rw2 .v{font-weight:600;color:var(--ink);white-space:nowrap}
.flagchip{font-size:8.5px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;padding:3px 8px;border-radius:4px}
.fc-ok{background:var(--greensoft);color:var(--green)}.fc-watch{background:rgba(217,155,62,.14);color:var(--amber)}.fc-risk{background:var(--redsoft);color:var(--red)}

/* impact dots */
.idots{display:inline-flex;gap:3px;align-items:center}
.idots i{width:5px;height:5px;border-radius:50%}

/* note strip */
.note{background:var(--greensoft);border:1px solid rgba(70,196,110,.2);border-radius:8px;padding:10px 13px;font-size:11.5px;line-height:1.5;color:var(--ink2);margin-top:12px}
.note b{color:var(--green)}
.note.warn{background:var(--redsoft);border-color:rgba(226,112,92,.2)}
.note.warn b{color:var(--red)}

/* mini bars */
.minibars{display:flex;align-items:flex-end;gap:8px;height:74px;margin-top:4px}
.minibars .b{flex:1;display:flex;flex-direction:column;align-items:center;gap:5px;justify-content:flex-end;height:100%}
.minibars .b .bar{width:62%;border-radius:3px 3px 0 0}
.minibars .b .lb{font-size:8.5px;color:var(--muted)}
.minibars .b .vv{font-size:8.5px;color:var(--ink2);font-weight:600}

/* heat grid (reverse dcf) */
.hgrid{display:grid;grid-template-columns:auto repeat(4,1fr);gap:4px;font-size:10px;margin-top:4px}
.hgrid .hh{text-align:center;color:var(--muted);font-weight:600;font-size:9px;padding:3px 0}
.hgrid .hl{display:flex;align-items:center;color:var(--muted);font-weight:600;font-size:9px}
.hgrid .hc{text-align:center;padding:7px 2px;border-radius:4px;font-weight:700;color:#0A0A0A}

/* implied rows */
.imp{display:flex;flex-direction:column}
.impr{display:grid;grid-template-columns:1fr auto;gap:6px;align-items:baseline;padding:8px 0;border-bottom:1px solid var(--line)}
.impr:last-child{border-bottom:none}
.impr .il{font-size:11px;color:var(--ink2)}.impr .il small{display:block;color:var(--faint);font-size:9px;margin-top:1px}
.impr .iv{text-align:right;font-size:15px;font-weight:700;color:var(--ink)}
.impr .iv span{display:block;font-size:9px;font-weight:600;margin-top:1px}

/* scenario */
.scen{display:flex;flex-direction:column;gap:10px}
.sc{display:grid;grid-template-columns:54px 1fr 84px;align-items:center;gap:10px}
.sc .nm{font-size:10px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
.sc .track{height:26px;background:var(--inset);border-radius:6px;position:relative;overflow:hidden}
.sc .fill{position:absolute;top:0;bottom:0;border-radius:6px;display:flex;align-items:center;padding:0 9px;font-size:10px;font-weight:600;color:#0A0A0A}
.sc .tp{text-align:right;font-size:13px;font-weight:700;color:var(--ink)}
.sc .tp small{display:block;font-size:9px;font-weight:600;color:var(--muted);margin-top:1px}

/* verdict full */
.vfull{display:grid;grid-template-columns:1.5fr 1fr 1fr;gap:22px}
.vfull .lead{font-size:15px;line-height:1.6;color:var(--ink2)}
.vfull .lead b{color:var(--ink)}
.vfull h4{font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);font-weight:700;margin-bottom:11px}
.vlist{display:flex;flex-direction:column;gap:9px}
.vli{display:flex;gap:9px;font-size:11.5px;color:var(--ink2);line-height:1.4}
.vli .mk{width:5px;height:5px;border-radius:50%;background:var(--green);margin-top:6px;flex-shrink:0}
.vli.w .mk{background:var(--red)}
.byline{display:flex;align-items:center;gap:10px;margin-top:16px;padding-top:14px;border-top:1px solid var(--line)}
.byline .av{width:32px;height:32px;border-radius:50%;background:var(--green2);color:#0A0A0A;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700}
.byline .nm{font-size:12px;font-weight:600;color:var(--ink)}.byline .rl{font-size:10px;color:var(--muted)}
.picks{display:flex;align-items:center;gap:7px;margin-top:14px;flex-wrap:wrap}
.picks .pl{font-size:9px;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);font-weight:700;margin-right:4px}
.picks .pk{font-size:11px;font-weight:700;color:var(--green);background:var(--greensoft);border:1px solid rgba(70,196,110,.25);padding:4px 10px;border-radius:5px}

/* footer */
.foot{padding:18px 26px 30px;border-top:1px solid var(--line);display:flex;justify-content:space-between;align-items:flex-end;margin-top:8px}
.foot .dis{font-size:10px;color:var(--faint);line-height:1.6;max-width:620px}
.foot .src{font-size:10.5px;color:var(--muted);display:flex;align-items:center;gap:9px}
.foot .src b{color:var(--green);font-weight:700}
.foot .src .d{width:6px;height:6px;border-radius:50%;background:var(--green)}

/* ===== nav dropdown (Market Intelligence) ===== */
.dropdown{position:absolute;top:56px;left:150px;width:278px;background:#121212;border:1px solid var(--line2);border-radius:10px;padding:7px;box-shadow:0 24px 60px rgba(0,0,0,.65);z-index:60}
.dditem{padding:10px 12px;border-radius:8px}
.dditem.active{background:#1A1A1A}
.dditem .t{font-size:13px;font-weight:600;color:var(--ink);display:flex;align-items:center}
.dditem.active .t{color:var(--green)}
.dditem .d{font-size:10.5px;color:var(--muted);margin-top:2px}
.soon{font-size:8px;color:var(--amber);font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-left:auto;border:1px solid rgba(217,155,62,.35);padding:2px 6px;border-radius:4px}

/* ===== hero (stance / headline / macro) ===== */
.dh{grid-template-columns:236px 1fr 372px !important}
.mstance .lab{font-size:9.5px;letter-spacing:.16em;text-transform:uppercase;color:var(--muted);font-weight:600}
.mstance .big{font-size:28px;font-weight:700;color:var(--green);margin-top:9px;line-height:1.06;letter-spacing:-.01em}
.mstance .meta{margin-top:15px;display:flex;flex-direction:column;gap:8px}
.mstance .m{display:flex;justify-content:space-between;font-size:11.5px;border-bottom:1px solid var(--line);padding-bottom:8px}
.mstance .m:last-child{border-bottom:none}.mstance .k{color:var(--muted)}.mstance .v{color:var(--ink);font-weight:600}
.macros{display:flex;flex-direction:column;border-left:1px solid var(--line);padding-left:22px;justify-content:center}
.macro{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--line)}
.macro:last-child{border-bottom:none}
.macro .l .k{font-size:9.5px;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);font-weight:600;display:flex;align-items:center;gap:7px}
.macro .l .k .ic{width:13px;height:13px;border:1.4px solid var(--muted);border-radius:3px}
.macro .l .s{font-size:10px;color:var(--faint);margin-top:3px}
.macro .v{font-size:21px;font-weight:700}

/* ===== key drivers ===== */
.kd{display:flex;flex-direction:column}
.kdr{display:flex;align-items:center;gap:11px;padding:8.5px 0;border-bottom:1px solid var(--line)}
.kdr:last-child{border-bottom:none}
.kdr .rk{font-size:11px;color:var(--faint);font-weight:700;width:13px;flex-shrink:0}
.kdr .bd{flex:1;min-width:0}
.kdr .t{font-size:12px;font-weight:600;color:var(--ink);line-height:1.2}
.kdr .c{font-size:8.5px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-top:3px}
.kdr .imp{display:flex;align-items:center;gap:8px;flex-shrink:0}
.kdr .lvl{font-size:9px;font-weight:700;text-transform:uppercase;width:50px;text-align:right}
.idots3{display:inline-flex;gap:2px}.idots3 i{width:6px;height:6px;border-radius:50%}

/* ===== cross-asset ===== */
.idx3{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.idxc{background:var(--inset);border:1px solid var(--line);border-radius:8px;padding:10px 11px}
.idxc .k{font-size:9.5px;color:var(--muted);font-weight:600;display:flex;align-items:center;gap:5px}
.idxc .v{font-size:15px;font-weight:700;margin-top:5px}
.idxc .c{font-size:9.5px;font-weight:600;margin-top:1px}
.oalbl{font-size:8.5px;color:var(--faint);text-transform:uppercase;letter-spacing:.1em;margin:13px 0 8px;font-weight:600}
.oa{display:grid;grid-template-columns:repeat(4,1fr);gap:8px}
.oa .k{font-size:8px;color:var(--muted);text-transform:uppercase;letter-spacing:.03em;font-weight:600}
.oa .v{font-size:12.5px;font-weight:700;margin-top:4px}
.oa .c{font-size:8.5px;font-weight:600;margin-top:1px}

/* ===== smart money ===== */
.smstats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:11px}
.sms{background:var(--inset);border:1px solid var(--line);border-radius:8px;padding:9px 10px}
.sms .k{font-size:8px;color:var(--muted);text-transform:uppercase;letter-spacing:.03em;font-weight:600}
.sms .v{font-size:14px;font-weight:700;margin-top:4px}
.sms .s{font-size:8.5px;color:var(--faint);margin-top:1px}

/* ===== sector heatmap ===== */
.sheat{display:grid;grid-template-columns:repeat(auto-fit,minmax(70px,1fr));gap:6px}
.sh{border-radius:6px;padding:9px 9px}
.sh .n{font-size:9.5px;font-weight:600;color:#0A0A0A;line-height:1.15;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.sh .p{font-size:12px;font-weight:700;color:#0A0A0A;margin-top:3px}
.scale{display:flex;justify-content:space-between;font-size:9px;color:var(--muted);margin-top:11px}

/* ===== calendar ===== */
.caltabs{display:flex;gap:16px;border-bottom:1px solid var(--line);margin-bottom:6px}
.caltab{font-size:11px;font-weight:600;color:var(--muted);padding-bottom:8px}
.caltab.on{color:var(--green);border-bottom:2px solid var(--green)}
.cal2{width:100%;border-collapse:collapse;font-size:11px}
.cal2-wrap{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch}
.cal2 th{text-align:left;font-size:8px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;font-weight:600;padding:6px 5px}
.cal2 th:last-child,.cal2 td:last-child{text-align:right}
.cal2 td{padding:8px 5px;border-top:1px solid var(--line);color:var(--ink2)}
.cal2 .dt{color:var(--muted);white-space:nowrap;font-size:10px}
.cal2 .ev{color:var(--ink);font-weight:500}
.viewall{font-size:11px;color:var(--green);font-weight:600;margin-top:11px}

/* ===== risk monitor ===== */
.risk{display:flex;flex-direction:column}
.rkr{display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--line);font-size:11.5px}
.rkr:last-child{border-bottom:none}
.rkr .n{color:var(--ink2)}
.rkr .lvl{display:flex;align-items:center;gap:7px;font-size:10px;font-weight:700;text-transform:uppercase}
.lvl-low .dt,.lvl-med .dt,.lvl-high .dt{width:7px;height:7px;border-radius:50%;display:inline-block}
.lvl-low{color:var(--green)}.lvl-low .dt{background:var(--green)}
.lvl-med{color:var(--amber)}.lvl-med .dt{background:var(--amber)}
.lvl-high{color:var(--red)}.lvl-high .dt{background:var(--red)}
.rkidx{display:flex;align-items:flex-end;justify-content:space-between;margin-top:13px;padding-top:12px;border-top:1px solid var(--line2)}
.rkidx .lab{font-size:9.5px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;font-weight:600}
.rkidx .v{font-size:24px;font-weight:700;color:var(--gold)}
.rkbar{height:6px;background:var(--inset);border-radius:4px;margin-top:9px;overflow:hidden}
.rkbar i{display:block;height:100%;background:var(--gold);border-radius:4px}

/* ===== top movers ===== */
.movers{display:grid;grid-template-columns:1fr 1fr;gap:28px}
.movers > div {min-width: 0;}
.movhd{font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:8px;display:flex;align-items:center;gap:7px}
.mvr{display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--line);font-size:11.5px}
.mvr:last-child{border-bottom:none}
.mvr .rk{font-size:10px;color:var(--faint);width:12px;flex-shrink:0}
.mvr .tk{font-weight:700;color:var(--ink);width:46px;flex-shrink:0}
.mvr .nm{color:var(--muted);font-size:10px;flex:1;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;min-width:0}
.mvr .pr{color:var(--ink2);font-variant-numeric:tabular-nums;width:54px;text-align:right;flex-shrink:0}
.mvr .ch{font-weight:700;width:62px;text-align:right;flex-shrink:0}

/* what changed ribbon */
.changed{display:flex;align-items:stretch;background:linear-gradient(90deg,#16201A,#121212);border:1px solid var(--line2);border-radius:10px;padding:11px 4px;overflow:hidden}
.changed .lab{font-size:9px;letter-spacing:.13em;text-transform:uppercase;color:var(--green);font-weight:700;padding:0 16px;border-right:1px solid var(--line);white-space:nowrap;display:flex;align-items:center;gap:7px}
.changed .lab .pulse{width:7px;height:7px;border-radius:50%;background:var(--green)}
.chg{display:flex;flex-direction:column;gap:3px;padding:0 17px;border-right:1px solid var(--line);justify-content:center}
.chg:last-child{border-right:none}
.chg .k{font-size:8.5px;color:var(--muted);text-transform:uppercase;letter-spacing:.04em}
.chg .v{font-size:12px;font-weight:700;color:var(--ink);display:flex;align-items:center;gap:6px;white-space:nowrap}
.chg .v .ar{font-size:12px}

/* regime components */
.rcomp{display:flex;flex-wrap:wrap;gap:5px;margin-top:12px}
.rc{font-size:9px;font-weight:700;padding:3px 7px;border-radius:4px;display:flex;align-items:center;gap:4px;background:var(--inset);border:1px solid var(--line)}
.rc.up{color:var(--green)}.rc.dn{color:var(--amber)}
.traj{font-size:10px;color:var(--muted);margin-top:10px}.traj b{color:var(--green)}

/* change tag on drivers */
.ctag{font-size:7.5px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;padding:2px 5px;border-radius:3px;margin-left:7px;vertical-align:middle}
.ct-up{background:var(--redsoft);color:var(--red)}
.ct-flip{background:var(--greensoft);color:var(--green)}

/* internals */
.intern{display:grid;grid-template-columns:repeat(3,1fr);gap:7px;margin-top:10px}
.intern .it{background:var(--inset);border:1px solid var(--line);border-radius:7px;padding:8px 9px}
.intern .k{font-size:7.5px;color:var(--muted);text-transform:uppercase;letter-spacing:.03em;font-weight:600}
.intern .v{font-size:12.5px;font-weight:700;margin-top:3px}

/* confluence matrix */
.conf{width:100%;border-collapse:collapse;font-size:11.5px}
.conf th{text-align:center;font-size:8.5px;letter-spacing:.03em;text-transform:uppercase;color:var(--muted);font-weight:600;padding:7px 5px;border-bottom:1px solid var(--line2)}
.conf th:first-child{text-align:left}
.conf td{text-align:center;padding:9px 5px;border-bottom:1px solid var(--line)}
.conf tr:last-child td{border-bottom:none}
.conf .sct{text-align:left;font-weight:600;color:var(--ink)}
.conf .sct small{display:block;color:var(--faint);font-size:9px;font-weight:400}
.conf .sig{font-size:12px;font-weight:700}
.sig-up{color:var(--green)}.sig-dn{color:var(--red)}.sig-neu{color:var(--muted)}
.confscore{font-size:8.5px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;padding:4px 8px;border-radius:5px;white-space:nowrap}
.cs-strong{background:var(--greensoft);color:var(--green)}
.cs-mod{background:rgba(217,155,62,.14);color:var(--amber)}
.cs-avoid{background:var(--redsoft);color:var(--red)}

/* smart money lens */
.smlens{display:flex;flex-direction:column;gap:13px}
.smlblock .h{font-size:9.5px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;margin-bottom:7px;display:flex;align-items:center;gap:7px}
.smlrow{display:flex;align-items:center;gap:9px;padding:7px 0;border-bottom:1px solid var(--line);font-size:11.5px}
.smlrow:last-child{border-bottom:none}
.smlrow .tk{font-weight:700;color:var(--ink);width:46px}
.smlrow .px{width:56px;text-align:right;font-variant-numeric:tabular-nums;font-size:11px}
.smlrow .fl{flex:1;text-align:right;font-size:10.5px;font-weight:600}
.smlrow .nt{font-size:9px;color:var(--muted);width:84px;text-align:right}

/* mover flow flag */
.mvr .ff{font-size:10px;font-weight:700;width:14px;text-align:center;flex-shrink:0}
.ff-ok{color:var(--green)}.ff-warn{color:var(--amber)}

/* calendar impact subline */
.imp-sub{font-size:9px;color:var(--muted);margin-top:3px;line-height:1.35}
.imp-sub b{color:#7FB98C;font-weight:600}



@media screen and (max-width: 1024px) {
  .hero, .dh {
    grid-template-columns: 1fr !important;
    gap: 20px;
  }
  .grid {
    display: flex;
    flex-direction: column;
  }
  .span3, .span4, .span5, .span6, .span8, .span12 {
    grid-column: span 12;
    width: 100%;
  }
  .idx3, .oa, .intern, .smstats, .sheat {
    grid-template-columns: repeat(2, 1fr);
  }
  .movers {
    grid-template-columns: 1fr;
  }
  .phead {
    flex-direction: column;
    gap: 16px;
  }
  .phead .right {
    align-items: flex-start;
    text-align: left;
  }
  .macros {
    border-left: none;
    border-top: 1px solid var(--line);
    padding-left: 0;
    padding-top: 20px;
  }
  .hero-stats {
    border-left: none;
    padding-left: 0;
  }
  .conf, .cal2 {
    display: block;
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    white-space: nowrap;
  }
}

@media screen and (max-width: 640px) {
  .idx3, .oa, .intern, .smstats, .sheat {
    grid-template-columns: 1fr;
  }
  .changed {
    flex-direction: column;
    gap: 10px;
    padding: 15px;
  }
  .changed .lab, .chg {
    border-right: none;
    padding: 5px 0;
  }
}

/* Regime Modal CSS */
.db-modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.7);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}
.db-modal {
  background: #111;
  border: 1px solid var(--line2);
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  padding: 24px;
  color: #eee;
  box-shadow: 0 10px 25px rgba(0,0,0,0.5);
}
.db-modal-item {
  margin-bottom: 12px;
  font-size: 13px;
  line-height: 1.5;
}
.db-modal-item b {
  color: #fff;
}
.db-modal-item small {
  color: #888;
  display: block;
  margin-top: 2px;
}
.su { color: var(--green); font-weight: 700; }
.sd { color: var(--amber); font-weight: 700; }
</style>

<style>
@media print {
  nav, header, footer, [class*="footer"], .nav, .foot, .pbtns, .dropdown, .phead { display: none !important; }
  .deskbrief-page { 
    width: 100% !important; 
    background: var(--bg) !important; 
    color: var(--ink) !important;
  }
  .wrap { padding: 0 !important; }
  
  /* Force dark theme printing */
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }
  
  /* Prevent awkward page breaks */
  .hero, .card, .phead { 
    page-break-inside: avoid !important; 
    break-inside: avoid !important; 
  }
}
b, strong { font-weight: 700 !important; }
</style>
