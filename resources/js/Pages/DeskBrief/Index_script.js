<script setup>
import { computed, ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
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
    { title: 'GROWTH (INDONESIA)', status: 'SOLID', value: '+5.61%', desc: 'GDP Q1 2026 YoY (BPS)', icon: '📈' },
    { title: 'INFLATION (INDONESIA)', status: 'NAIK', value: '3.34%', desc: 'IHK Jun 2026 YoY (BI)', icon: '📊' },
    { title: 'LIQUIDITY (M2)', status: 'EKSPANSIF', value: '+10.8%', desc: 'M2 growth May 2026 YoY (BI)', icon: '💧' },
    { title: 'FX & FLOW', status: 'TERJAGA', value: '$145.6B', desc: 'Cadangan Devisa Jun 2026 · Portfolio inflow +$0.70M (30d)', icon: '💵' },
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
    marketStance: (data && data.market_stance && data.market_stance.score !== undefined) 
      ? data.market_stance 
      : (props.todayStance ? {
          score: props.todayStance.score,
          label: props.todayStance.label,
          view: 'Constructive',
          horizon: '1-4 weeks',
        } : mockData.marketStance),
    drivers: data.drivers || mockData.drivers,
    lastUpdate: data.updated_at ? new Date(data.updated_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).replace('.', ':') + ' WIB' : mockData.lastUpdate,
    headline: data.title || mockData.headline,
    subHeadline: data.market_read || mockData.subHeadline,
    regimeText: data.so_what || mockData.regimeText,
    macroCards: props.macroCards?.length ? props.macroCards.map(mc => ({
      title: mc.title || mc.symbol_or_metric?.replace('_', ' ') || 'MACRO',
      status: mc.status || (mc.change_abs > 0 ? 'Rising' : (mc.change_abs < 0 ? 'Cooling' : 'Stable')),
      value: mc.value || '—',
      desc: mc.desc || mc.source || '',
      icon: mc.icon || (mc.symbol_or_metric === 'GLOBAL_GROWTH' ? '🌐' : (mc.symbol_or_metric === 'US_INFLATION' ? '📊' : '💧'))
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
