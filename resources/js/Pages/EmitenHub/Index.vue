<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    Search, Download, ChevronDown, RefreshCw, 
    TrendingUp, TrendingDown, Star, ChevronLeft, ChevronRight,
    ArrowRight, ArrowUpRight
} from 'lucide-vue-next';

const props = defineProps({
    tickers: Object,
    filters: Object
});

const searchQuery = ref(props.filters.search || '');

const submitSearch = () => {
    router.get(route('emiten.index'), { search: searchQuery.value }, { preserveState: true });
};

// Mock data for UI elements that are not dynamic yet
const topSectors = [
    { name: 'Energi', d1: '+2.25%', ytd: '+18.72%', isUp: true },
    { name: 'Perbankan', d1: '+1.31%', ytd: '+9.84%', isUp: true },
    { name: 'Infrastruktur', d1: '+0.98%', ytd: '+7.12%', isUp: true },
    { name: 'Konsumer Primer', d1: '+0.76%', ytd: '+6.05%', isUp: true },
    { name: 'Kesehatan', d1: '-0.12%', ytd: '-1.23%', isUp: false },
];

const topGainers = [
    { ticker: 'CUAN', price: '7,650', d1: '+24.59%', vol: '1,234.5' },
    { ticker: 'PSAB', price: '422', d1: '+18.02%', vol: '523.1' },
    { ticker: 'ENRG', price: '292', d1: '+15.84%', vol: '312.6' },
    { ticker: 'BRMS', price: '202', d1: '+13.48%', vol: '865.3' },
    { ticker: 'GOTO', price: '82', d1: '+9.33%', vol: '1,023.7' },
];

const watchlist = [
    { ticker: 'BBRI', price: '4,820', d1: '+1.48%', isUp: true },
    { ticker: 'BBCA', price: '9,775', d1: '+0.77%', isUp: true },
    { ticker: 'TLKM', price: '2,620', d1: '-0.38%', isUp: false },
    { ticker: 'ASII', price: '4,950', d1: '+0.10%', isUp: true },
    { ticker: 'PANI', price: '17,625', d1: '+1.01%', isUp: true },
];

const featuredCards = [
    { ticker: 'BBRI', name: 'Bank Rakyat Indonesia (Persero) Tbk', price: '4,820', d1: '+1.48%', per: '10.41x', roe: '16.9%', yield: '4.15%', rec: 'BUY', recColor: 'text-emerald-500' },
    { ticker: 'BBCA', name: 'Bank Central Asia Tbk', price: '9,775', d1: '+0.77%', per: '19.21x', roe: '20.6%', yield: '2.01%', rec: 'BUY', recColor: 'text-emerald-500' },
    { ticker: 'BMRI', name: 'Bank Mandiri (Persero) Tbk', price: '5,700', d1: '+1.42%', per: '9.02x', roe: '15.2%', yield: '3.80%', rec: 'BUY', recColor: 'text-emerald-500' },
    { ticker: 'TLKM', name: 'Telkom Indonesia (Persero) Tbk', price: '2,620', d1: '-0.38%', per: '12.34x', roe: '16.7%', yield: '4.27%', rec: 'HOLD', recColor: 'text-yellow-500' },
];

const formatNumber = (num) => {
    return Number(num).toLocaleString('id-ID');
};

const getDummyPrice = (id) => {
    // Generate deterministic dummy price based on ID for MVP
    const base = 1000 + (id * 15);
    return base;
};

const getDummyChange = (id) => {
    const isUp = id % 3 !== 0;
    const value = ((id % 5) + (id % 10) / 10).toFixed(2);
    return { value: `${isUp ? '+' : '-'}${value}%`, isUp };
};
</script>

<template>
    <Head title="Emiten Hub" />
    <AppLayout>
        <div class="min-h-screen bg-[#090b0a] pt-24 pb-12 font-sans text-slate-300">
            <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Page Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Emiten Hub</h1>
                        <p class="text-sm text-slate-400">Jelajahi dan analisis kinerja emiten tercatat di BEI secara komprehensif.</p>
                    </div>
                    <button class="flex items-center gap-2 px-4 py-2 bg-[#121614] border border-slate-800 rounded-lg text-sm font-medium hover:bg-slate-800 transition-colors">
                        <Download class="w-4 h-4" /> Download Data
                    </button>
                </div>

                <!-- Filters Bar -->
                <div class="flex flex-wrap gap-3 mb-8 items-center bg-[#121614] p-3 rounded-xl border border-slate-800/50">
                    <form @submit.prevent="submitSearch" class="relative flex-1 min-w-[250px]">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            placeholder="Cari emiten, sektor, atau kata kunci (contoh: BBRI, perbankan)" 
                            class="w-full bg-[#0a0c0b] border border-slate-800 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:border-emerald-500/50 text-slate-200"
                        />
                    </form>
                    
                    <div class="flex gap-2 flex-wrap">
                        <select class="bg-[#0a0c0b] border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-300 appearance-none pr-8 relative min-w-[120px]">
                            <option>Sektor</option>
                        </select>
                        <select class="bg-[#0a0c0b] border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-300 appearance-none pr-8 min-w-[120px]">
                            <option>Market Cap</option>
                        </select>
                        <select class="bg-[#0a0c0b] border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-300 appearance-none pr-8 min-w-[120px]">
                            <option>Valuasi (PER)</option>
                        </select>
                        <select class="bg-[#0a0c0b] border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-300 appearance-none pr-8 min-w-[120px]">
                            <option>Growth (YoY)</option>
                        </select>
                        <select class="bg-[#0a0c0b] border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-300 appearance-none pr-8 min-w-[120px]">
                            <option>Dividend Yield</option>
                        </select>
                        <select class="bg-[#0a0c0b] border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-300 appearance-none pr-8 min-w-[120px]">
                            <option>Papan</option>
                        </select>
                    </div>

                    <button class="flex items-center gap-2 px-3 py-2 text-sm text-slate-400 hover:text-white transition-colors ml-auto">
                        <RefreshCw class="w-4 h-4" /> Reset
                    </button>
                </div>

                <!-- Main Grid Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
                    
                    <!-- Left Column (Span 9) -->
                    <div class="lg:col-span-9 space-y-6">
                        
                        <!-- Featured Top Card -->
                        <div class="bg-[#121614] border border-slate-800/50 rounded-xl p-6 flex flex-col md:flex-row gap-8">
                            <div class="flex-1">
                                <div class="flex items-start gap-4 mb-4">
                                    <div class="w-14 h-14 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shrink-0">
                                        B
                                    </div>
                                    <div>
                                        <h2 class="text-2xl font-bold text-white flex items-center gap-2">BBRI</h2>
                                        <p class="text-sm text-slate-400">Bank Rakyat Indonesia (Persero) Tbk</p>
                                        <div class="flex gap-2 mt-2">
                                            <span class="text-[10px] uppercase font-bold bg-slate-800 px-2 py-0.5 rounded text-slate-300">Perbankan</span>
                                            <span class="text-[10px] uppercase font-bold bg-slate-800 px-2 py-0.5 rounded text-slate-300">Bank BUMN</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm text-slate-400 leading-relaxed mb-4">
                                    BBRI merupakan bank komersial milik negara terbesar di Indonesia dengan fokus pada segmen UMKM. Memiliki jaringan terluas dengan lebih dari 10.000 unit kerja di seluruh Indonesia.
                                </p>
                                <Link href="#" class="text-emerald-500 text-sm font-medium hover:text-emerald-400 flex items-center gap-1">
                                    Lihat Profil Lengkap <ArrowRight class="w-4 h-4" />
                                </Link>
                            </div>
                            
                            <!-- Middle: Price & Chart -->
                            <div class="flex-1 flex flex-col justify-center border-l border-r border-slate-800/50 px-6">
                                <p class="text-xs text-slate-500 mb-1">Harga</p>
                                <div class="flex items-baseline gap-1">
                                    <h3 class="text-3xl font-bold text-white">4,820</h3>
                                    <span class="text-sm text-slate-400">IDR</span>
                                </div>
                                <p class="text-emerald-500 font-medium text-sm mb-4">+70 (+1.48%)</p>
                                
                                <!-- Mock Chart SVG -->
                                <div class="h-16 w-full mb-4">
                                    <svg viewBox="0 0 200 50" class="w-full h-full stroke-emerald-500 stroke-[2] fill-none" preserveAspectRatio="none">
                                        <path d="M0,40 Q10,35 20,45 T40,30 T60,20 T80,35 T100,10 T120,25 T140,15 T160,30 T180,5 T200,20"></path>
                                    </svg>
                                </div>
                                
                                <div class="flex justify-between text-xs font-medium text-slate-500">
                                    <button class="hover:text-white bg-slate-800/50 rounded px-2 py-1 text-emerald-500">1D</button>
                                    <button class="hover:text-white px-2 py-1">1M</button>
                                    <button class="hover:text-white px-2 py-1">3M</button>
                                    <button class="hover:text-white px-2 py-1">6M</button>
                                    <button class="hover:text-white px-2 py-1">1Y</button>
                                    <button class="hover:text-white px-2 py-1">5Y</button>
                                </div>
                            </div>
                            
                            <!-- Right: Stats -->
                            <div class="flex-1 pl-2">
                                <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                                    <div>
                                        <p class="text-xs text-slate-500 mb-1">Target Price</p>
                                        <p class="text-lg font-bold text-white">5,400 <span class="text-xs font-normal text-slate-400">IDR</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500 mb-1">Rekomendasi</p>
                                        <p class="text-lg font-bold text-emerald-500">BUY <span class="text-xs font-normal text-slate-400 block -mt-1">(Overweight)</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500 mb-1">Market Cap</p>
                                        <p class="font-bold text-white text-sm">728.5 T</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500 mb-1">PER (TTM)</p>
                                        <p class="font-bold text-white text-sm">10.41x</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500 mb-1">ROE (TTM)</p>
                                        <p class="font-bold text-white text-sm">16.9%</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500 mb-1">Dividend Yield</p>
                                        <p class="font-bold text-white text-sm">4.15%</p>
                                    </div>
                                </div>
                                <p class="text-[10px] text-slate-600 mt-4">Data per 14 Mei 2025</p>
                            </div>
                        </div>

                        <!-- Main Table -->
                        <div class="bg-[#121614] border border-slate-800/50 rounded-xl overflow-hidden">
                            <div class="flex flex-wrap items-center justify-between p-4 border-b border-slate-800/50 gap-4">
                                <div class="flex space-x-1">
                                    <button class="px-4 py-1.5 bg-emerald-500/10 text-emerald-500 rounded-lg text-sm font-medium">Semua Emiten <span class="ml-1 opacity-70">{{ tickers.total }}</span></button>
                                    <button class="px-4 py-1.5 text-slate-400 hover:text-white rounded-lg text-sm font-medium transition-colors">IDX30</button>
                                    <button class="px-4 py-1.5 text-slate-400 hover:text-white rounded-lg text-sm font-medium transition-colors">LQ45</button>
                                    <button class="px-4 py-1.5 text-slate-400 hover:text-white rounded-lg text-sm font-medium transition-colors">IDX80</button>
                                    <button class="px-4 py-1.5 text-slate-400 hover:text-white rounded-lg text-sm font-medium transition-colors">Kompas100</button>
                                </div>
                                <button class="flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
                                    <ArrowUpRight class="w-4 h-4" /> Export
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-slate-400 uppercase bg-[#0a0c0b] border-b border-slate-800">
                                        <tr>
                                            <th class="px-4 py-3 font-medium">#</th>
                                            <th class="px-4 py-3 font-medium">Ticker</th>
                                            <th class="px-4 py-3 font-medium">Nama Emiten</th>
                                            <th class="px-4 py-3 font-medium">Sektor</th>
                                            <th class="px-4 py-3 font-medium text-right">Harga (IDR)</th>
                                            <th class="px-4 py-3 font-medium text-right">Market Cap (T)</th>
                                            <th class="px-4 py-3 font-medium text-right">PER (TTM)</th>
                                            <th class="px-4 py-3 font-medium text-right">PBV (TTM)</th>
                                            <th class="px-4 py-3 font-medium text-right">ROE (TTM)</th>
                                            <th class="px-4 py-3 font-medium text-center">Rekomendasi</th>
                                            <th class="px-4 py-3 font-medium text-center">1D Chart</th>
                                            <th class="px-4 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(ticker, index) in tickers.data" :key="ticker.id" class="border-b border-slate-800/50 hover:bg-[#1a1f1c] transition-colors group">
                                            <td class="px-4 py-3 text-slate-500">{{ (tickers.current_page - 1) * tickers.per_page + index + 1 }}</td>
                                            <td class="px-4 py-3">
                                                <Link :href="route('emiten.show', ticker.symbol)" class="font-bold text-emerald-500 hover:underline">{{ ticker.symbol }}</Link>
                                            </td>
                                            <td class="px-4 py-3 font-medium text-slate-300 truncate max-w-[200px]">{{ ticker.company_name }}</td>
                                            <td class="px-4 py-3 text-slate-400">{{ ticker.sector || '-' }}</td>
                                            <td class="px-4 py-3 text-right">
                                                <div class="font-medium text-white">{{ formatNumber(getDummyPrice(ticker.id)) }}</div>
                                                <div :class="[getDummyChange(ticker.id).isUp ? 'text-emerald-500' : 'text-red-500', 'text-xs']">
                                                    {{ getDummyChange(ticker.id).value }}
                                                </div>
                                            </td>
                                            <!-- Mock metrics -->
                                            <td class="px-4 py-3 text-right text-slate-300">{{ (getDummyPrice(ticker.id) * 0.15).toFixed(1) }}</td>
                                            <td class="px-4 py-3 text-right text-slate-300">{{ (10 + (ticker.id % 20)).toFixed(2) }}x</td>
                                            <td class="px-4 py-3 text-right text-slate-300">{{ (1 + (ticker.id % 5)).toFixed(2) }}x</td>
                                            <td class="px-4 py-3 text-right text-slate-300">{{ (5 + (ticker.id % 25)).toFixed(1) }}%</td>
                                            <td class="px-4 py-3 text-center">
                                                <span :class="[ticker.id % 3 === 0 ? 'text-yellow-500' : (ticker.id % 2 === 0 ? 'text-emerald-500' : 'text-slate-400'), 'font-bold text-xs']">
                                                    {{ ticker.id % 3 === 0 ? 'HOLD' : (ticker.id % 2 === 0 ? 'BUY' : 'N/A') }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 w-24">
                                                <svg viewBox="0 0 100 30" class="w-full h-8 stroke-[2] fill-none" :class="getDummyChange(ticker.id).isUp ? 'stroke-emerald-500' : 'stroke-red-500'" preserveAspectRatio="none">
                                                    <path d="M0,15 Q5,10 10,20 T20,15 T30,25 T40,5 T50,20 T60,10 T70,15 T80,5 T90,20 T100,10"></path>
                                                </svg>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <button class="text-slate-500 hover:text-yellow-400 transition-colors">
                                                    <Star class="w-4 h-4" />
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="tickers.data.length === 0">
                                            <td colspan="12" class="px-4 py-8 text-center text-slate-500">
                                                Tidak ada emiten yang sesuai dengan kriteria pencarian.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="flex items-center justify-between p-4 bg-[#0a0c0b] border-t border-slate-800" v-if="tickers.total > 0">
                                <button class="px-4 py-2 text-sm font-medium text-slate-300 bg-[#121614] border border-slate-700 rounded-lg hover:bg-slate-800 transition-colors">
                                    Lihat Semua Emiten <ArrowRight class="w-4 h-4 inline ml-1" />
                                </button>
                                <div class="flex items-center gap-4">
                                    <span class="text-sm text-slate-500">
                                        {{ (tickers.current_page - 1) * tickers.per_page + 1 }}-{{ Math.min(tickers.current_page * tickers.per_page, tickers.total) }} dari {{ tickers.total }}
                                    </span>
                                    <div class="flex gap-1">
                                        <Link :href="tickers.prev_page_url || '#'" class="p-1 rounded bg-[#121614] border border-slate-800 text-slate-400 hover:text-white" :class="{'opacity-50 pointer-events-none': !tickers.prev_page_url}">
                                            <ChevronLeft class="w-5 h-5" />
                                        </Link>
                                        <div class="flex items-center px-2">
                                            <span v-for="n in Math.min(3, tickers.last_page)" :key="n" class="w-8 h-8 flex items-center justify-center text-sm font-medium rounded" :class="n === tickers.current_page ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:bg-slate-800'">
                                                {{ n }}
                                            </span>
                                            <span v-if="tickers.last_page > 3" class="px-1 text-slate-500">...</span>
                                            <span v-if="tickers.last_page > 3" class="w-8 h-8 flex items-center justify-center text-sm font-medium rounded text-slate-400 hover:bg-slate-800">
                                                {{ tickers.last_page }}
                                            </span>
                                        </div>
                                        <Link :href="tickers.next_page_url || '#'" class="p-1 rounded bg-[#121614] border border-slate-800 text-slate-400 hover:text-white" :class="{'opacity-50 pointer-events-none': !tickers.next_page_url}">
                                            <ChevronRight class="w-5 h-5" />
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <!-- Right Sidebar (Span 3) -->
                    <div class="lg:col-span-3 space-y-6">
                        
                        <!-- Top Sectors -->
                        <div class="bg-[#121614] border border-slate-800/50 rounded-xl p-5">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-bold text-white text-lg">Top Sectors</h3>
                                <Link href="#" class="text-xs text-emerald-500 hover:text-emerald-400">Lihat Semua</Link>
                            </div>
                            <div class="grid grid-cols-[auto_1fr_auto_auto] gap-x-4 gap-y-3 text-sm">
                                <div class="text-slate-500 text-xs"></div>
                                <div class="text-slate-500 text-xs">Sektor</div>
                                <div class="text-slate-500 text-xs text-right">1D %</div>
                                <div class="text-slate-500 text-xs text-right">YTD %</div>

                                <template v-for="(sector, idx) in topSectors" :key="idx">
                                    <div class="text-slate-500">{{ idx + 1 }}</div>
                                    <div class="text-slate-300 truncate font-medium">{{ sector.name }}</div>
                                    <div :class="[sector.isUp ? 'text-emerald-500' : 'text-red-500', 'text-right']">{{ sector.d1 }}</div>
                                    <div :class="[sector.isUp ? 'text-emerald-500' : 'text-red-500', 'text-right']">{{ sector.ytd }}</div>
                                </template>
                            </div>
                        </div>

                        <!-- Top Movers -->
                        <div class="bg-[#121614] border border-slate-800/50 rounded-xl p-5">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-bold text-white text-lg">Top Movers</h3>
                                <Link href="#" class="text-xs text-emerald-500 hover:text-emerald-400">Lihat Semua</Link>
                            </div>
                            
                            <div class="flex mb-4 bg-slate-800/30 rounded-lg p-1">
                                <button class="flex-1 text-xs font-medium py-1.5 rounded-md bg-emerald-900/40 text-emerald-400 border border-emerald-800/50">Top Gainers</button>
                                <button class="flex-1 text-xs font-medium py-1.5 text-slate-400 hover:text-slate-300">Top Losers</button>
                            </div>

                            <div class="grid grid-cols-[1fr_auto_auto_auto] gap-x-4 gap-y-3 text-sm">
                                <div class="text-slate-500 text-xs">Ticker</div>
                                <div class="text-slate-500 text-xs text-right">Harga</div>
                                <div class="text-slate-500 text-xs text-right">1D %</div>
                                <div class="text-slate-500 text-xs text-right">Vol (T)</div>

                                <template v-for="gainer in topGainers" :key="gainer.ticker">
                                    <div class="font-bold text-white">{{ gainer.ticker }}</div>
                                    <div class="text-slate-300 text-right">{{ gainer.price }}</div>
                                    <div class="text-emerald-500 text-right">{{ gainer.d1 }}</div>
                                    <div class="text-slate-400 text-right">{{ gainer.vol }}</div>
                                </template>
                            </div>
                        </div>

                        <!-- Watchlist Saya -->
                        <div class="bg-[#121614] border border-slate-800/50 rounded-xl p-5">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-bold text-white text-lg">Watchlist Saya</h3>
                                <Link href="#" class="text-xs text-emerald-500 hover:text-emerald-400">Lihat Semua</Link>
                            </div>

                            <div class="grid grid-cols-[1fr_auto_auto_1fr_auto] gap-x-3 gap-y-3 text-sm items-center mb-4">
                                <div class="text-slate-500 text-xs">Ticker</div>
                                <div class="text-slate-500 text-xs text-right">Harga</div>
                                <div class="text-slate-500 text-xs text-right">1D %</div>
                                <div class="text-slate-500 text-xs text-center">Chart</div>
                                <div></div>

                                <template v-for="item in watchlist" :key="item.ticker">
                                    <div class="font-bold" :class="item.ticker === 'BBRI' ? 'text-emerald-500' : 'text-white'">{{ item.ticker }}</div>
                                    <div class="text-slate-300 text-right">{{ item.price }}</div>
                                    <div :class="[item.isUp ? 'text-emerald-500' : 'text-red-500', 'text-right']">{{ item.d1 }}</div>
                                    <div class="w-16 mx-auto">
                                        <svg viewBox="0 0 100 30" class="w-full h-5 stroke-[2] fill-none" :class="item.isUp ? 'stroke-emerald-500' : 'stroke-red-500'" preserveAspectRatio="none">
                                            <path d="M0,15 Q5,10 10,20 T20,15 T30,25 T40,5 T50,20 T60,10 T70,15 T80,5 T90,20 T100,10"></path>
                                        </svg>
                                    </div>
                                    <div class="text-right">
                                        <Star class="w-4 h-4" :class="item.ticker === 'BBRI' ? 'text-yellow-400 fill-yellow-400' : 'text-slate-500'" />
                                    </div>
                                </template>
                            </div>
                            
                            <button class="w-full py-2 border border-slate-700 rounded-lg text-sm text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                                Kelola Watchlist
                            </button>
                        </div>

                    </div>
                </div>

                <!-- Featured Emiten Bottom Row -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-white">Featured Emiten</h2>
                        <Link href="#" class="text-sm text-emerald-500 hover:text-emerald-400">Lihat Semua <ChevronRight class="w-4 h-4 inline" /></Link>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div v-for="card in featuredCards" :key="card.ticker" class="bg-[#121614] border border-slate-800/50 rounded-xl p-4 hover:border-emerald-500/30 transition-colors">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-slate-800 rounded flex items-center justify-center font-bold text-white">
                                        <span v-if="card.ticker === 'BBRI' || card.ticker === 'BBCA' || card.ticker === 'BMRI'" class="text-blue-500 text-xl font-serif tracking-tighter">B</span>
                                        <span v-else class="text-red-500 text-xl font-serif">T</span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-white leading-tight">{{ card.ticker }}</h3>
                                        <p class="text-[10px] text-slate-400 truncate w-32">{{ card.name }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-end mb-4">
                                <div>
                                    <h4 class="text-lg font-bold text-white">{{ card.price }}</h4>
                                    <p class="text-xs" :class="card.d1.startsWith('+') ? 'text-emerald-500' : 'text-red-500'">{{ card.d1 }}</p>
                                </div>
                                <div class="w-20">
                                    <svg viewBox="0 0 100 30" class="w-full h-8 stroke-[2] fill-none" :class="card.d1.startsWith('+') ? 'stroke-emerald-500' : 'stroke-red-500'" preserveAspectRatio="none">
                                        <path d="M0,15 Q5,10 10,20 T20,15 T30,25 T40,5 T50,20 T60,10 T70,15 T80,5 T90,20 T100,10"></path>
                                    </svg>
                                </div>
                            </div>

                            <div class="grid grid-cols-4 gap-2 text-center text-xs">
                                <div>
                                    <p class="text-slate-500 mb-0.5">PER</p>
                                    <p class="font-bold text-slate-200">{{ card.per }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500 mb-0.5">ROE</p>
                                    <p class="font-bold text-slate-200">{{ card.roe }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500 mb-0.5">Div Yield</p>
                                    <p class="font-bold text-slate-200">{{ card.yield }}</p>
                                </div>
                                <div class="flex items-center justify-center">
                                    <span :class="[card.recColor, 'font-bold px-2 py-0.5 border border-current rounded/20']">{{ card.rec }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center text-xs text-slate-500 pt-4 border-t border-slate-800">
                    <p>Data disediakan oleh AVENIR Research • Terakhir diperbarui 14 Mei 2025 15:30 WIB</p>
                    <div class="flex gap-4">
                        <Link href="#" class="hover:text-slate-300">Disclaimer</Link>
                        <Link href="#" class="hover:text-slate-300">Metodologi</Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

