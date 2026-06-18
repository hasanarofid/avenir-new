<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    newsList: {
        type: Array,
        default: () => []
    },
    marketData: {
        type: Object,
        default: () => ({})
    },
    marketConfig: {
        type: Object,
        default: () => ({
            top: [],
            watchlist: [],
            trending: []
        })
    }
});

// Computed split for Featured and Recent
const featuredNews = computed(() => {
    return props.newsList && props.newsList.length > 0 ? props.newsList.slice(0, 3) : [];
});

const recentNews = computed(() => {
    return props.newsList && props.newsList.length > 3 ? props.newsList.slice(3) : [];
});

// Filter states
const categories = ['Semua', 'Market', 'Corporate', 'Macro', 'Global', 'Commodity', 'Fixed Income'];
const selectedCategory = ref('Semua');
const searchQuery = ref('');

let pollInterval = null;

onMounted(() => {
    // Polling data market setiap 60 detik secara background (tanpa loading screen)
    pollInterval = setInterval(() => {
        router.reload({
            only: ['marketData', 'marketConfig'],
            preserveState: true,
            preserveScroll: true
        });
    }, 60000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});

// --- Dynamic Data ---
const getQuote = (symbol, fallback) => {
    return props.marketData[symbol] || fallback;
};

const formatPrice = (val) => {
    if (typeof val === 'number') {
        return new Intl.NumberFormat('id-ID').format(val);
    }
    return val;
};

const marketSummary = computed(() => {
    const ihsg = getQuote('^JKSE', { price: 7275.64, change: 25.5, changePercent: 0.35, name: 'IHSG' });
    const usd = getQuote('IDR=X', { price: 16255, change: -50, changePercent: -0.21, name: 'USD/IDR' });
    const gold = getQuote('GC=F', { price: 2343.89, change: 15.2, changePercent: 0.68, name: 'Gold' });
    const oil = getQuote('BZ=F', { price: 82.47, change: -1.1, changePercent: -1.31, name: 'Oil' });

    // Format top movers dynamically from all available real-time market data
    const excludeSymbols = ['^JKSE', 'IDR=X', 'GC=F', 'BZ=F'];
    const allMovers = Object.values(props.marketData || {})
        .filter(q => q && !excludeSymbols.includes(q.symbol) && q.price > 0)
        .sort((a, b) => Math.abs(b.changePercent) - Math.abs(a.changePercent))
        .map(quote => ({
            ticker: quote.symbol.replace('.JK', ''),
            change: (quote.changePercent > 0 ? '+' : '') + Number(quote.changePercent).toFixed(2) + '%',
            isUp: quote.changePercent >= 0,
            price: formatPrice(quote.price)
        }));

    // Ensure even number to keep the 2-column grid balanced (max 10)
    let topMoversList = allMovers.slice(0, 10);
    if (topMoversList.length % 2 !== 0 && topMoversList.length > 0) {
        topMoversList.pop();
    }

    return {
        time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB',
        ihsg: { 
            value: formatPrice(ihsg.price), 
            change: ihsg.change, 
            changePercent: (ihsg.changePercent >= 0 ? '+' : '') + Number(ihsg.changePercent).toFixed(2), 
            isUp: ihsg.change >= 0 
        },
        usd: { 
            value: formatPrice(usd.price), 
            change: usd.change, 
            changePercent: (usd.changePercent >= 0 ? '+' : '') + Number(usd.changePercent).toFixed(2), 
            isUp: usd.change >= 0 
        },
        gold: { 
            value: formatPrice(gold.price), 
            change: gold.change, 
            changePercent: (gold.changePercent >= 0 ? '+' : '') + Number(gold.changePercent).toFixed(2), 
            isUp: gold.change >= 0 
        },
        oil: { 
            value: formatPrice(oil.price), 
            change: oil.change, 
            changePercent: (oil.changePercent >= 0 ? '+' : '') + Number(oil.changePercent).toFixed(2), 
            isUp: oil.change >= 0 
        },
        topMovers: topMoversList.length > 0 ? topMoversList : [
            { ticker: 'BBRI', change: '+2.41%', isUp: true },
            { ticker: 'TLKM', change: '+1.87%', isUp: true }
        ]
    };
});

const activeMarketIndex = ref(0);
const timeframes = ['1D', '1W', '1M', '3M', 'YTD', '1Y', '3Y', '5Y'];
const activeTimeframe = ref('1D');
const isFullscreen = ref(false);
const chartContainer = ref(null);
const chartType = ref('line');

const carouselItems = computed(() => [
    { ...marketSummary.value.ihsg, id: 'ihsg', name: 'IHSG', fullName: 'Indeks Harga Saham Gabungan', prevClose: getQuote('^JKSE', {}).prevClose || getQuote('^JKSE', {}).price || 0, chartData: getQuote('^JKSE', {}).chartData || [] },
    { ...marketSummary.value.usd, id: 'usd', name: 'USD/IDR', fullName: 'Nilai Tukar Dolar AS terhadap Rupiah', prevClose: getQuote('IDR=X', {}).prevClose || getQuote('IDR=X', {}).price || 0, chartData: getQuote('IDR=X', {}).chartData || [] },
    { ...marketSummary.value.gold, id: 'gold', name: 'Gold (XAU/USD)', fullName: 'Harga Emas Global', prevClose: getQuote('GC=F', {}).prevClose || getQuote('GC=F', {}).price || 0, chartData: getQuote('GC=F', {}).chartData || [] },
    { ...marketSummary.value.oil, id: 'oil', name: 'Oil (Brent)', fullName: 'Harga Minyak Mentah Global', prevClose: getQuote('BZ=F', {}).prevClose || getQuote('BZ=F', {}).price || 0, chartData: getQuote('BZ=F', {}).chartData || [] }
]);

const activeMarket = computed(() => carouselItems.value[activeMarketIndex.value] || carouselItems.value[0]);

const historicalDataMap = ref({});
const isLoadingChart = ref(false);

watch([activeTimeframe, () => activeMarket.value.id], async ([newTf, newId]) => {
    if (!newId) return;
    
    let sym = '';
    if (newId === 'ihsg') sym = '^JKSE';
    else if (newId === 'usd') sym = 'IDR=X';
    else if (newId === 'gold') sym = 'GC=F';
    else if (newId === 'oil') sym = 'BZ=F';
    
    if (!sym) return;

    if (historicalDataMap.value[`${newId}-${newTf}`]) {
        return; // Already loaded
    }

    isLoadingChart.value = true;
    try {
        const response = await axios.get(`/api/market-chart/${encodeURIComponent(sym)}?range=${newTf}`);
        historicalDataMap.value[`${newId}-${newTf}`] = response.data;
    } catch (e) {
        console.error("Failed to fetch historical data", e);
    } finally {
        isLoadingChart.value = false;
    }
}, { immediate: true });

const nextMarket = () => {
    activeMarketIndex.value = (activeMarketIndex.value + 1) % carouselItems.value.length;
};

const prevMarket = () => {
    activeMarketIndex.value = (activeMarketIndex.value - 1 + carouselItems.value.length) % carouselItems.value.length;
};

const toggleFullscreen = () => {
    if (!chartContainer.value) return;
    if (!document.fullscreenElement) {
        chartContainer.value.requestFullscreen().catch(err => {
            console.error(`Error attempting to enable fullscreen: ${err.message}`);
        });
    } else {
        document.exitFullscreen();
    }
};

// Handle fullscreen change events to sync state
onMounted(() => {
    document.addEventListener('fullscreenchange', () => {
        isFullscreen.value = !!document.fullscreenElement;
    });
});

const chartContext = computed(() => {
    const item = activeMarket.value;
    const width = 800;
    const height = 300;
    const hist = historicalDataMap.value[`${item.id}-${activeTimeframe.value}`];
    let points = [];
    let prevClose = Number(item.prevClose);

    if (hist && hist.points && hist.points.length > 0) {
        points = hist.points;
        if (hist.prevClose) prevClose = Number(hist.prevClose);

        // Sesuaikan titik chart jika data Yahoo Finance usang atau berbeda jauh (lebih dari 5%)
        let actualPrice = Number(String(item.value).replace(/[^0-9,-]+/g,"").replace(",","."));
        let lastPointClose = points[points.length - 1].close;
        if (actualPrice && lastPointClose && Math.abs(lastPointClose - actualPrice) > (actualPrice * 0.05)) {
            let ratio = actualPrice / lastPointClose;
            points = points.map(p => ({
                ...p,
                open: p.open * ratio,
                high: p.high * ratio,
                low: p.low * ratio,
                close: p.close * ratio
            }));
            prevClose = prevClose * ratio;
        }
    } else {
        // Fallback to 1D data passed from props if available
        let data = item.chartData || [];
        if (data.length > 0) {
            points = data.map((c, i) => ({
                close: c,
                open: i === 0 ? prevClose : data[i-1],
                high: c,
                low: c
            }));
        }
    }

    if (points.length === 0) {
        return { path: '', fillPath: '', labels: [], minFormatted: '0', maxFormatted: '0', prevCloseY: height / 2, candles: [] };
    }

    const prices = points.map(p => p.close);
    
    let min = Math.min(...prices, prevClose);
    let max = Math.max(...prices, prevClose);
    const range = max - min || 1;
    
    const paddedMin = min - (range * 0.15);
    const paddedMax = max + (range * 0.15);
    const paddedRange = paddedMax - paddedMin;
    
    let path = '';
    let maxPointX = 0, maxPointY = 0;
    let minPointX = 0, minPointY = 0;
    const candles = [];

    points.forEach((pt, i) => {
        const x = (i / (points.length - 1)) * width;
        const y = height - ((pt.close - paddedMin) / paddedRange) * height;
        if (i === 0) path += `M${x},${y} `;
        else path += `L${x},${y} `;

        if (pt.close === max) { maxPointX = x; maxPointY = y; }
        if (pt.close === min) { minPointX = x; minPointY = y; }
        
        // Use real OHLC from the API
        const open = pt.open;
        const close = pt.close;
        const high = pt.high;
        const low = pt.low;
        const isUp = close >= open;
        
        const yOpen = height - ((open - paddedMin) / paddedRange) * height;
        const yClose = height - ((close - paddedMin) / paddedRange) * height;
        const yHigh = height - ((high - paddedMin) / paddedRange) * height;
        const yLow = height - ((low - paddedMin) / paddedRange) * height;
        
        candles.push({
            x,
            y: Math.min(yOpen, yClose),
            width: Math.max(2, (width / points.length) * 0.6),
            height: Math.max(1, Math.abs(yOpen - yClose)),
            yHigh,
            yLow,
            isUp
        });
    });
    
    const fillPath = path + ` L${width},${height} L0,${height} Z`;
    const prevCloseY = height - ((prevClose - paddedMin) / paddedRange) * height;
    
    const labels = [];
    for(let i=0; i<5; i++) {
        const val = paddedMax - (i * (paddedRange / 4));
        let formatted = new Intl.NumberFormat('id-ID', { maximumFractionDigits: val < 1000 ? 2 : 0 }).format(val);
        labels.push(formatted);
    }
    
    return { 
        path, 
        fillPath, 
        labels, 
        prevCloseY,
        maxPointPctX: (maxPointX / width) * 100,
        maxPointPctY: (maxPointY / height) * 100,
        minPointPctX: (minPointX / width) * 100,
        minPointPctY: (minPointY / height) * 100,
        maxFormatted: new Intl.NumberFormat('id-ID', { maximumFractionDigits: max < 1000 ? 2 : 0 }).format(max),
        minFormatted: new Intl.NumberFormat('id-ID', { maximumFractionDigits: min < 1000 ? 2 : 0 }).format(min),
        candles
    };
});

const mostRead = [
  { id: 1, title: 'IHSG Menguat ke 7.275, Investor Asing Net Buy Rp1,02 Triliun' },
  { id: 2, title: 'BI Tahan Suku Bunga di 6,25%, Rupiah Menguat ke 16.255' },
  { id: 3, title: 'BBRI Catat Laba Bersih Q1 2025 Naik 12% YoY Jadi Rp15,06 Triliun' },
  { id: 4, title: 'Inflasi April 2025 Terkendali di 2,72% YoY, Sesuai Ekspektasi' },
  { id: 5, title: 'Harga Minyak Turun di Tengah Data Inventori AS yang Meningkat' },
];

const watchlistNews = computed(() => {
    const list = props.marketConfig.watchlist || [];
    if (list.length === 0) {
        return [
            { ticker: 'BBRI', title: 'BBRI Catat Laba Bersih Q1 2025 Naik 12% YoY', time: '09:15' },
            { ticker: 'TLKM', title: 'TLKM Jajaki Kerja Sama Strategis dengan AWS', time: '08:33' }
        ];
    }
    return list.map(sym => {
        const quote = getQuote(sym, { symbol: sym, price: 0, changePercent: 0 });
        const name = quote.name || sym;
        const changeStr = (quote.changePercent > 0 ? '+' : '') + Number(quote.changePercent).toFixed(2) + '%';
        
        return {
            ticker: sym.replace('.JK', ''),
            title: `${name} diperdagangkan di Rp ${formatPrice(quote.price)} (${changeStr})`,
            time: 'Baru saja'
        };
    });
});

const trendingTickers = computed(() => {
    const list = props.marketConfig.trending || [];
    if (list.length === 0) {
        return ['BBRI', 'TLKM', 'ASII', 'MDKA', 'AMMN', 'GOTO', 'ANTM', 'UNVR', 'ICBP', 'SIDO'];
    }
    return list.map(sym => sym.replace('.JK', ''));
});

const economicCalendar = [
  { time: '09:00', flag: '🇮🇩', country: 'ID', event: 'Inflasi (YoY) - Apr 2025', act: '2.72%', est: '2.70%' },
  { time: '19:30', flag: '🇺🇸', country: 'US', event: 'CPI (YoY) - Apr 2025', act: '3.4%', est: '3.5%' },
  { time: '21:00', flag: '🇺🇸', country: 'US', event: 'FOMC Meeting Minutes', act: '-', est: '-' },
];
</script>

<template>
  <Head>
    <title>News | AVENIR</title>
    <meta name="description" content="AVENIR - Platform riset dan direktori pasar modal Indonesia yang komprehensif." />
    <!-- ...other metas... -->
  </Head>

  <AppLayout>
    <div class="bg-[#090b0a] min-h-screen text-slate-200 font-sans relative overflow-x-hidden pt-8 pb-20">
      
      <!-- Container matching Katalog -->
      <div class="max-w-[1200px] mx-auto px-6 relative z-10">
        
        <!-- Top Market Summary -->
        <div class="border-b border-white/5 pb-6 mb-8">
          <div class="flex items-center gap-2 text-emerald-400 text-xs font-bold mb-4">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            LIVE Market Update <span class="text-slate-400 font-normal ml-2">{{ marketSummary.time }}</span>
          </div>
          
          <div class="flex flex-col lg:flex-row gap-6 items-stretch">
            
            <div ref="chartContainer" :class="['relative flex-1 bg-[#111413] border border-white/10 p-6 shadow-2xl transition-all duration-300', isFullscreen ? 'fixed inset-0 z-50 rounded-none w-full h-full flex flex-col' : 'rounded-xl lg:mr-6 overflow-hidden']">
                <!-- Header -->
                <div class="relative z-10 mb-6 flex justify-between items-start">
                    <div>
                        <div class="flex items-center gap-1.5 cursor-pointer group w-fit" @click="nextMarket">
                            <h2 class="text-[20px] font-bold text-white tracking-tight">{{ activeMarket.name }}</h2>
                            <svg class="w-4 h-4 text-slate-400 group-hover:text-white transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="text-[14px] text-slate-400 mt-0.5 mb-5">{{ activeMarket.fullName }}</div>
                        
                        <div class="text-[42px] leading-none font-extrabold text-white mb-3">{{ activeMarket.value }}</div>
                        <div class="flex items-center gap-2 text-[15px]">
                            <div :class="['flex items-center gap-1 font-medium', activeMarket.isUp ? 'text-emerald-400' : 'text-red-400']">
                                <svg v-if="activeMarket.isUp" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="17" y1="7" x2="7" y2="17"/><polyline points="17 17 7 17 7 7"/></svg>
                                {{ activeMarket.isUp ? '+' : '' }}{{ Number(activeMarket.change || 0).toFixed(2) }} ({{ activeMarket.changePercent }}%)
                            </div>
                            <span class="text-slate-400">Hari Ini</span>
                        </div>
                    </div>
                </div>
                
                <!-- Chart Area -->
                <div :class="['relative w-full mb-8', isFullscreen ? 'flex-1' : 'h-[240px]']">
                    <!-- SVG Chart -->
                    <div class="absolute inset-0 right-14 overflow-visible">
                        <!-- Max/Min Labels -->
                        <div 
                           class="absolute text-[12px] font-medium -translate-y-full pb-1 -translate-x-1/2 whitespace-nowrap z-10"
                           :class="activeMarket.isUp ? 'text-emerald-400' : 'text-red-400'"
                           :style="{ left: `${chartContext.maxPointPctX}%`, top: `${chartContext.maxPointPctY}%` }"
                        >
                           {{ chartContext.maxFormatted }}
                        </div>
                        <div 
                           class="absolute text-[12px] font-medium pt-1 -translate-x-1/2 whitespace-nowrap z-10"
                           :class="activeMarket.isUp ? 'text-emerald-400' : 'text-red-400'"
                           :style="{ left: `${chartContext.minPointPctX}%`, top: `${chartContext.minPointPctY}%` }"
                        >
                           {{ chartContext.minFormatted }}
                        </div>

                        <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 800 300">
                            <!-- Dashed Previous Close Line -->
                            <line x1="0" :y1="chartContext.prevCloseY" x2="800" :y2="chartContext.prevCloseY" stroke="#64748b" stroke-width="2" stroke-dasharray="6,6" opacity="0.4" />
                            
                            <!-- Main Line -->
                            <path v-if="chartType === 'line' && chartContext.path" :d="chartContext.path" fill="none" :stroke="activeMarket.isUp ? '#10b981' : '#ef4444'" stroke-width="2.5" vector-effect="non-scaling-stroke"/>
                            
                            <!-- Candlesticks -->
                            <g v-if="chartType === 'candle'">
                                <g v-for="(candle, i) in chartContext.candles" :key="i">
                                    <line :x1="candle.x" :y1="candle.yHigh" :x2="candle.x" :y2="candle.yLow" :stroke="candle.isUp ? '#10b981' : '#ef4444'" stroke-width="1.5" />
                                    <rect :x="candle.x - candle.width/2" :y="candle.y" :width="candle.width" :height="candle.height" :fill="candle.isUp ? '#10b981' : '#ef4444'" />
                                </g>
                            </g>
                        </svg>
                    </div>

                    <!-- Y Axis Labels -->
                    <div class="absolute right-0 top-0 bottom-0 w-12 flex flex-col justify-between text-right text-[12px] text-slate-500 py-1">
                        <span v-for="(label, i) in chartContext.labels" :key="i">{{ label }}</span>
                    </div>

                    <!-- Expand button -->
                    <button @click="toggleFullscreen" class="absolute -bottom-8 right-14 w-8 h-8 flex items-center justify-center bg-white/5 hover:bg-white/10 rounded-lg text-slate-400 transition-colors">
                        <svg v-if="!isFullscreen" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                        <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 14 10 14 10 20"/><polyline points="20 10 14 10 14 4"/><line x1="14" y1="10" x2="21" y2="3"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                    </button>
                </div>

                <!-- Bottom Controls -->
                <div class="flex items-center justify-between border-t border-white/5 pt-4 mt-auto">
                    <div class="flex items-center gap-1 sm:gap-2 text-[12px] font-bold text-slate-500">
                        <button v-for="tf in timeframes" :key="tf" 
                            @click="activeTimeframe = tf"
                            :class="['px-2 sm:px-3 py-1.5 transition-all', activeTimeframe === tf ? 'text-emerald-400 border-b-2 border-emerald-400' : 'hover:text-slate-300 hover:bg-white/5 rounded-md']">
                            {{ tf }}
                        </button>
                    </div>
                    <div class="flex items-center gap-2 bg-[#1a1f1d] p-1 rounded-lg border border-white/5">
                        <button @click="chartType = 'candle'" :class="['p-1.5 rounded-md transition-colors', chartType === 'candle' ? 'bg-emerald-500/20 text-emerald-400' : 'text-slate-400 hover:text-white']"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></button>
                        <button @click="chartType = 'line'" :class="['p-1.5 rounded-md transition-colors', chartType === 'line' ? 'bg-emerald-500/20 text-emerald-400' : 'text-slate-400 hover:text-white']"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></button>
                    </div>
                </div>
            </div>

            <!-- Top Movers -->
            <div class="w-full lg:w-[280px] flex-shrink-0 flex flex-col justify-between pt-4 lg:pt-0 border-t lg:border-t-0 border-white/10">
               <div class="flex justify-between items-center mb-3">
                 <div class="text-slate-400 text-xs font-medium">Top Movers (IHSG)</div>
               </div>
               <div class="grid grid-cols-2 gap-x-3 gap-y-2 text-[11px] mb-4 flex-1 content-start">
                  <div v-for="mover in marketSummary.topMovers" :key="mover.ticker" class="flex flex-col bg-[#1a1f1d] border border-white/5 p-2 rounded-lg hover:bg-white/5 transition-colors cursor-default">
                     <div class="flex justify-between items-center mb-1">
                         <span class="text-slate-300 font-bold">{{ mover.ticker }}</span>
                         <span :class="mover.isUp ? 'text-emerald-400' : 'text-red-400'" class="font-medium flex items-center text-[10px]">
                           <svg v-if="mover.isUp" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                           <svg v-else width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"/></svg>
                           {{ mover.change }}
                         </span>
                     </div>
                     <div class="text-slate-500 font-medium">Rp {{ mover.price }}</div>
                  </div>
               </div>
               <Link href="/market" class="block w-full text-center bg-[#111413] border border-white/5 hover:border-white/20 hover:bg-white/5 text-slate-300 text-[11px] font-medium py-2 rounded-lg transition-colors flex items-center justify-center gap-1.5">
                  Lihat Market 
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
               </Link>
            </div>

          </div>
        </div>

        <!-- Header Section -->
        <div class="mb-10">
          <h1 class="text-4xl md:text-[42px] font-bold text-white mb-2 leading-tight">Market News</h1>
          <p class="text-slate-400 text-[15px] mb-8">Update pasar harian tepercaya untuk keputusan investasi yang lebih cerdas.</p>

          <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-5">
            <div class="flex flex-wrap gap-2">
               <button v-for="cat in categories" :key="cat" 
                 @click="selectedCategory = cat"
                 :class="['px-5 py-1.5 rounded-full text-xs font-semibold transition-all duration-200 border', 
                 selectedCategory === cat ? 'bg-emerald-500 text-white border-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.3)]' : 'bg-transparent text-slate-400 border-white/10 hover:border-white/20 hover:text-slate-200']">
                 {{ cat }}
               </button>
            </div>
            
            <div class="flex gap-3 w-full xl:w-auto">
               <button class="flex items-center justify-center gap-2 bg-[#111413] border border-white/10 px-4 py-2 rounded-lg text-slate-300 text-xs font-medium hover:bg-white/5 transition-colors">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>
                  Filter
               </button>
               <div class="relative flex-1 xl:w-72">
                  <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                  <input type="text" v-model="searchQuery" placeholder="Cari berita, emiten, topik..." class="w-full bg-[#111413] border border-white/10 rounded-lg pl-10 pr-4 py-2 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 h-[36px] transition-all">
               </div>
            </div>
          </div>
        </div>

        <!-- Main Layout Split -->
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-8">
          
          <!-- ==================== LEFT COLUMN ==================== -->
          <div class="main-column min-w-0">
            
            <!-- Berita Utama -->
            <div class="mb-12">
              <h3 class="text-white font-bold text-[17px] mb-5">Berita Utama</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <Link v-for="news in featuredNews" :key="news.id" :href="'/news/' + news.slug" class="group flex flex-col bg-[#111413] border border-white/5 rounded-[14px] overflow-hidden hover:border-emerald-500/30 transition-all duration-300 shadow-sm hover:shadow-[0_8px_25px_rgba(0,0,0,0.5)]">
                   <div class="aspect-[16/10] relative overflow-hidden bg-slate-800">
                     <img v-if="news.cover_image" :src="news.cover_image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                     <div class="absolute inset-0 bg-gradient-to-t from-[#111413] via-[#111413]/20 to-transparent opacity-80"></div>
                     <div class="absolute bottom-3 left-3 bg-emerald-500 text-white text-[9px] font-bold px-2 py-0.5 rounded shadow-sm tracking-wider">FEATURED</div>
                   </div>
                   <div class="p-5 flex flex-col flex-1">
                     <h4 class="text-slate-100 font-semibold text-[15px] mb-2 group-hover:text-emerald-400 transition-colors line-clamp-2 leading-[1.4]">{{ news.title }}</h4>
                     <p class="text-slate-400 text-[13px] mb-4 line-clamp-3 leading-relaxed flex-1">{{ news.excerpt }}</p>
                     
                     <div class="flex items-center gap-2 text-[11px] text-slate-500 mb-4">
                        <div v-if="news.author" class="flex items-center gap-1.5">
                            <div class="w-4 h-4 rounded-full overflow-hidden bg-slate-800 border border-white/10 shrink-0 flex items-center justify-center">
                                <img v-if="news.author.profile_photo_url" :src="news.author.profile_photo_url" alt="Author" class="w-full h-full object-cover">
                                <span v-else class="text-[8px]">🧑‍💼</span>
                            </div>
                            <span class="text-slate-300 font-medium">{{ news.author.name }}</span>
                        </div>
                        <span v-else class="text-slate-300">{{ news.source || 'AVENIR Research' }}</span>
                        <span>&bull;</span>
                        <span>{{ news.published_at || 'Baru saja' }}</span>
                     </div>
                     
                     <div class="flex items-center justify-between border-t border-white/5 pt-4 mt-auto">
                        <div class="flex gap-1.5 flex-wrap">
                           <span class="bg-white/5 border border-white/10 text-slate-400 text-[9px] font-semibold px-2 py-0.5 rounded tracking-wider">{{ news.category || 'IHSG' }}</span>
                           <span v-if="news.title && news.title.includes('BBRI')" class="bg-white/5 border border-white/10 text-slate-400 text-[9px] font-semibold px-2 py-0.5 rounded tracking-wider">BBRI</span>
                        </div>
                        <button class="text-slate-500 hover:text-emerald-400 transition-colors">
                           <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                        </button>
                     </div>
                   </div>
                </Link>
                
                <div v-if="featuredNews.length === 0" class="col-span-3 text-center py-10 border border-dashed border-white/10 rounded-xl">
                  <p class="text-slate-500 text-sm">Belum ada berita utama</p>
                </div>
              </div>
            </div>

            <!-- Berita Terbaru -->
            <div>
              <div class="flex justify-between items-center border-b border-white/10 pb-4 mb-2">
                 <h3 class="text-white font-bold text-[17px]">Berita Terbaru</h3>
                 <div class="flex items-center gap-2 text-xs text-slate-400">
                   <span>Sort by:</span>
                   <select class="bg-transparent border-none text-white focus:outline-none cursor-pointer font-medium p-0">
                      <option>Terbaru</option>
                      <option>Terpopuler</option>
                   </select>
                 </div>
              </div>

              <div class="flex flex-col">
                <Link v-for="(news, idx) in recentNews" :key="news.id" :href="'/news/' + news.slug" class="group flex items-center gap-4 py-4 lg:py-5 border-b border-white/5 hover:bg-white/[0.02] px-2 -mx-2 rounded transition-colors">
                   
                   <div class="w-14 lg:w-16 flex-shrink-0 text-center">
                     <div class="text-[13px] text-slate-300 font-medium">{{ news.published_at ? news.published_at.split(' ')[0] : '09:28' }}</div>
                     <div class="text-[10px] text-slate-500 mt-0.5">WIB</div>
                   </div>
                   
                   <div class="w-20 lg:w-24 flex-shrink-0">
                      <span class="text-[9px] font-bold tracking-wider uppercase text-emerald-400">{{ news.category || 'MARKET' }}</span>
                   </div>
                   
                   <div class="flex-1 pr-4">
                      <h4 class="text-[14px] lg:text-[15px] text-slate-200 font-medium group-hover:text-emerald-400 transition-colors leading-snug line-clamp-2">{{ news.title }}</h4>
                   </div>
                   
                   <div class="flex items-center gap-5">
                      <div class="hidden sm:flex gap-1.5 min-w-[60px] justify-end">
                         <!-- Auto generate tag based on title -->
                         <span v-if="news.title && news.title.includes('Inflasi')" class="bg-[#111413] border border-white/10 text-slate-400 text-[9px] font-semibold px-2 py-0.5 rounded tracking-wider">INFLASI</span>
                         <span v-else-if="news.title && news.title.includes('BBRI')" class="bg-[#111413] border border-white/10 text-slate-400 text-[9px] font-semibold px-2 py-0.5 rounded tracking-wider">BBRI</span>
                         <span v-else class="bg-[#111413] border border-white/10 text-slate-400 text-[9px] font-semibold px-2 py-0.5 rounded tracking-wider">NEWS</span>
                      </div>
                      <button class="text-slate-500 hover:text-emerald-400 transition-colors" @click.prevent>
                         <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                      </button>
                   </div>
                </Link>
                
                <div v-if="recentNews.length === 0" class="text-center py-10 border border-dashed border-white/10 rounded-xl mt-4">
                  <p class="text-slate-500 text-sm">Belum ada berita terbaru</p>
                </div>
              </div>

              <button v-if="recentNews.length > 0" class="w-full mt-6 py-3 bg-[#111413] border border-white/5 text-slate-300 text-[13px] font-semibold rounded-lg hover:bg-white/10 hover:text-white transition-all flex items-center justify-center gap-2 group">
                 Muat lebih banyak
                 <svg class="group-hover:translate-y-0.5 transition-transform" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
              </button>
            </div>
            
          </div>

          <!-- ==================== RIGHT COLUMN (SIDEBAR) ==================== -->
          <div class="sidebar-column flex flex-col gap-5">
             
             <!-- News Sentiment -->
             <div class="bg-[#111413] border border-white/5 rounded-[14px] p-5">
               <div class="flex justify-between items-center mb-5">
                 <h3 class="text-white font-bold text-[15px] flex items-center gap-2">
                   News Sentiment
                   <svg class="text-slate-500" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                 </h3>
                 <span class="text-[11px] text-emerald-400 cursor-pointer font-medium hover:text-emerald-300">Lihat Semua</span>
               </div>
               
               <div class="text-[11px] text-slate-400 mb-3 flex justify-between border-b border-white/5 pb-2">
                 <span>Pasar Indonesia</span>
                 <span>(24 jam terakhir)</span>
               </div>
               
               <div class="flex items-end gap-2 mb-2 mt-4">
                 <div class="text-2xl font-bold text-emerald-400 leading-none">58%</div>
                 <div class="text-sm font-bold text-slate-300 mb-0.5 ml-auto leading-none">28%</div>
                 <div class="text-sm font-bold text-red-400 mb-0.5 leading-none">14%</div>
               </div>
               <div class="flex w-full h-2 rounded-full overflow-hidden mb-4">
                 <div class="bg-emerald-500" style="width: 58%"></div>
                 <div class="bg-slate-600 border-x border-[#111413]" style="width: 28%"></div>
                 <div class="bg-red-500" style="width: 14%"></div>
               </div>
               <div class="flex justify-between text-[10px] text-slate-400 font-medium">
                 <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Positif</div>
                 <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span>Netral</div>
                 <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Negatif</div>
               </div>
             </div>

             <!-- Most Read -->
             <div class="bg-[#111413] border border-white/5 rounded-[14px] p-5">
               <div class="flex justify-between items-center mb-5">
                 <h3 class="text-white font-bold text-[15px]">Most Read</h3>
                 <span class="text-[10px] text-slate-500">24 jam terakhir</span>
               </div>
               <div class="flex flex-col gap-4">
                 <Link v-for="(item, idx) in mostRead" :key="item.id" href="#" class="group flex gap-3 items-start">
                   <span class="text-emerald-400 font-bold text-[13px] mt-0.5 w-3 text-right">{{ idx + 1 }}</span>
                   <p class="text-slate-300 text-[13px] leading-relaxed group-hover:text-emerald-400 transition-colors flex-1">{{ item.title }}</p>
                 </Link>
               </div>
             </div>

             <!-- Watchlist News -->
             <div class="bg-[#111413] border border-white/5 rounded-[14px] p-5">
               <div class="flex justify-between items-center mb-5">
                 <h3 class="text-white font-bold text-[15px]">Watchlist News</h3>
                 <span class="text-[11px] text-emerald-400 cursor-pointer font-medium hover:text-emerald-300">Lihat Semua</span>
               </div>
               <div class="flex flex-col gap-4">
                 <Link v-for="item in watchlistNews" :key="item.ticker" href="#" class="group flex items-start gap-3">
                   <span class="text-emerald-400 font-bold text-[11px] w-9 flex-shrink-0 mt-1">{{ item.ticker }}</span>
                   <p class="text-slate-300 text-[13px] leading-relaxed group-hover:text-emerald-400 transition-colors line-clamp-2 flex-1">{{ item.title }}</p>
                   <span class="text-[10px] text-slate-500 flex-shrink-0 mt-1 w-8 text-right">{{ item.time }}</span>
                 </Link>
               </div>
             </div>

             <!-- Trending Tickers -->
             <div class="bg-[#111413] border border-white/5 rounded-[14px] p-5">
               <div class="flex justify-between items-center mb-5">
                 <h3 class="text-white font-bold text-[15px]">Trending Tickers</h3>
                 <span class="text-[10px] text-slate-500">24 jam terakhir</span>
               </div>
               <div class="flex flex-wrap gap-2">
                 <Link v-for="ticker in trendingTickers" :key="ticker" href="#" class="px-3.5 py-1.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[11px] font-bold rounded-lg hover:bg-emerald-500 hover:text-white hover:border-emerald-500 transition-all">
                   {{ ticker }}
                 </Link>
               </div>
             </div>

             <!-- Economic Calendar -->
             <div class="bg-[#111413] border border-white/5 rounded-[14px] p-5">
               <div class="flex justify-between items-center mb-5">
                 <h3 class="text-white font-bold text-[15px]">Economic Calendar</h3>
                 <span class="text-[11px] text-emerald-400 cursor-pointer font-medium hover:text-emerald-300">Lihat Kalendar</span>
               </div>
               <div class="flex flex-col gap-4 mb-5">
                 <div v-for="evt in economicCalendar" :key="evt.event" class="flex items-start gap-3">
                    <span class="text-[11px] text-slate-400 w-8 mt-0.5 font-medium">{{ evt.time }}</span>
                    <span class="text-base mt-0">{{ evt.flag }}</span>
                    <div class="flex-1">
                      <div class="flex items-center gap-1.5 mb-1.5">
                        <span class="bg-[#1e293b] text-slate-300 text-[9px] font-bold px-1.5 py-0.5 rounded tracking-wider">{{ evt.country }}</span>
                        <span class="text-[13px] text-slate-200">{{ evt.event }}</span>
                      </div>
                      <div class="flex gap-4 text-[11px] text-slate-500">
                         <span>Act: <strong class="text-slate-300 font-medium">{{ evt.act }}</strong></span>
                         <span>Est: <strong class="text-slate-300 font-medium">{{ evt.est }}</strong></span>
                      </div>
                    </div>
                 </div>
               </div>
               <div class="flex gap-5 text-[10px] text-slate-400 items-center pt-4 border-t border-white/5 font-medium">
                 <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Previous</span>
                 <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span>Forecast</span>
               </div>
             </div>
             
          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Optional scoped styles can be placed here, but most of the design is driven by Tailwind classes */
select {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394a3b8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
  background-repeat: no-repeat;
  background-position: right 0.2rem top 50%;
  background-size: 0.65rem auto;
  padding-right: 1rem;
}
</style>
