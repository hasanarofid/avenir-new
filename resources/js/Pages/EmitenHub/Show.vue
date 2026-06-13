<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    ArrowLeft, TrendingUp, Building2, Newspaper, BookmarkPlus, BookmarkMinus, 
    Zap, AlertTriangle, Share2, Wallet, CreditCard, PiggyBank, Percent, Activity, Shield, Download
} from 'lucide-vue-next';
import Swal from 'sweetalert2';

const props = defineProps({
    ticker: Object,
    articles: Array,
    disclosures: Array,
    realtimePrice: [Number, String],
    isWatchlisted: Boolean
});

const page = usePage();
const form = useForm({});

// Mapping for dynamic icons from JSON
const iconMap = {
    Wallet, TrendingUp, Building2, CreditCard, PiggyBank, Percent, AlertTriangle, Activity, Shield
};

// Use data from database with fallback
const companyProfile = props.ticker.company_profile || {
    fullName: props.ticker.company_name || 'N/A',
    code: props.ticker.symbol || 'N/A',
    sector: props.ticker.sector || 'N/A',
    industry: 'N/A',
    board: 'N/A',
    listingDate: 'N/A',
    website: '#',
    business: 'N/A',
    marketCap: 'N/A',
    outstandingShares: 'N/A',
    address: 'N/A',
    phone: 'N/A',
    email: 'N/A',
    tags: []
};

const financialHighlights = props.ticker.financial_highlights || [];
const financialRatios = props.ticker.financial_ratios || [];
const mainRisks = props.ticker.main_risks || [];

const toggleWatchlist = () => {
    if (!page.props.auth.user) {
        Swal.fire({
            icon: 'info',
            title: 'Perlu Login',
            text: 'Silakan login terlebih dahulu untuk menggunakan fitur Watchlist.',
            background: '#121614',
            color: '#f8fafc',
            confirmButtonColor: '#059669'
        });
        return;
    }

    form.post(route('watchlist.toggle', props.ticker.id), {
        preserveScroll: true,
        onSuccess: () => {
            const flash = usePage().props.flash;
            if (flash.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: flash.success,
                    background: '#121614',
                    color: '#f8fafc',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        }
    });
};
</script>

<template>
    <Head>
        <title>{{ ticker.symbol }} - {{ ticker.company_name }} | AVENIR Emiten Hub</title>
        <meta name="description" :content="`Analisis, profil perusahaan, laporan keuangan, dan riset saham ${ticker.symbol} - ${ticker.company_name}.`" />
        <meta property="og:title" :content="`${ticker.symbol} - ${ticker.company_name} | AVENIR Emiten Hub`" />
        <meta property="og:description" :content="`Data lengkap fundamental dan valuasi saham ${ticker.symbol}.`" />
        <meta property="og:type" content="website" />
        <meta name="twitter:card" content="summary_large_image" />
        
        <!-- GEO Tags untuk SEO Indonesia -->
        <meta name="geo.region" content="ID" />
        <meta name="geo.placename" content="Indonesia" />
        <meta name="geo.position" content="-0.789275;113.921327" />
        <meta name="ICBM" content="-0.789275, 113.921327" />
        <meta name="language" content="id-ID" />
        <meta name="view-transition" content="same-origin" />
    </Head>
    <AppLayout>
        <div class="min-h-screen bg-[#090b0a] pt-24 pb-12 font-['Inter',sans-serif]">
            <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Back Link -->
                <Link :href="route('emiten.index')" class="inline-flex items-center text-sm font-medium text-emerald-500 hover:text-emerald-400 mb-6 transition-colors">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Kembali ke Emiten Hub
                </Link>

                <!-- Header / Profile -->
                <div class="bg-[#121614]/70 backdrop-blur-md border border-white/5 rounded-3xl p-6 lg:p-8 mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 shadow-xl hover:border-emerald-500/20 transition-all duration-300">
                    <div class="flex items-center gap-6">
                        <div class="w-20 h-20 bg-gradient-to-br from-[#0d47a1] to-[#1565c0] rounded-2xl flex items-center justify-center text-white font-black text-2xl shadow-inner shrink-0">
                            {{ ticker.symbol }}
                        </div>
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <h1 class="text-2xl lg:text-3xl font-black text-white tracking-tight">{{ ticker.symbol }}</h1>
                            </div>
                            <p class="text-base lg:text-lg text-slate-300 font-medium mb-1.5">{{ ticker.company_name }}</p>
                            <p class="text-xs text-slate-500">{{ companyProfile.sector }} • {{ companyProfile.industry }} • Papan {{ companyProfile.board }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col w-full md:w-auto mt-4 md:mt-0">
                        <div class="flex justify-between md:justify-end items-end gap-6 mb-4">
                            <div>
                                <p class="text-[11px] text-slate-500 font-medium uppercase tracking-wider mb-1">Harga Terakhir (IDR)</p>
                                <div class="flex items-baseline gap-3">
                                    <h2 class="text-2xl lg:text-3xl font-bold text-white">Rp {{ Number(realtimePrice).toLocaleString('id-ID') }}</h2>
                                    <span class="text-emerald-400 text-sm font-bold">
                                        +60 (+1,35%)
                                    </span>
                                </div>
                                <p class="text-[10px] text-slate-500 mt-1">11 Jun 2026 15:14 WIB</p>
                            </div>
                        </div>
                        
                        <div class="flex gap-2">
                            <button 
                                @click="toggleWatchlist"
                                :disabled="form.processing"
                                :class="[
                                    'w-full md:w-auto flex justify-center items-center gap-2 px-5 py-2.5 font-semibold rounded-xl transition-all text-sm',
                                    isWatchlisted 
                                        ? 'bg-red-500/10 text-red-400 hover:bg-red-500/20 border border-red-500/20 hover:border-red-500/50 hover:-translate-y-0.5' 
                                        : 'bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 hover:border-emerald-500/50 hover:-translate-y-0.5'
                                ]"
                            >
                                <BookmarkMinus v-if="isWatchlisted" class="w-4 h-4" />
                                <BookmarkPlus v-else class="w-4 h-4" />
                                {{ isWatchlisted ? 'Hapus Watchlist' : '+ Tambah ke Watchlist' }}
                            </button>
                            <button class="p-2.5 bg-[#121614]/50 hover:bg-[#1a1f1c] rounded-xl text-slate-300 border border-white/5 hover:border-emerald-500/30 transition-all hover:-translate-y-0.5">
                                <Share2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabs (Visual only for MVP) -->
                <div class="flex overflow-x-auto gap-8 mb-8 border-b border-emerald-950/30 pb-0 scrollbar-hide">
                    <button class="text-emerald-400 text-sm font-bold border-b-2 border-emerald-500 pb-3 whitespace-nowrap">Overview</button>
                    <button class="text-slate-400 text-sm hover:text-slate-200 font-medium pb-3 border-b-2 border-transparent hover:border-slate-700 whitespace-nowrap transition-all">Financials</button>
                    <button class="text-slate-400 text-sm hover:text-slate-200 font-medium pb-3 border-b-2 border-transparent hover:border-slate-700 whitespace-nowrap transition-all">Valuation</button>
                    <button class="text-slate-400 text-sm hover:text-slate-200 font-medium pb-3 border-b-2 border-transparent hover:border-slate-700 whitespace-nowrap transition-all">Research</button>
                    <button class="text-slate-400 text-sm hover:text-slate-200 font-medium pb-3 border-b-2 border-transparent hover:border-slate-700 whitespace-nowrap transition-all">Disclosures</button>
                </div>

                <!-- MAIN GRID -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- ROW 1: Ticker Brief & Risiko -->
                    <div class="lg:col-span-2 bg-[#121614]/70 backdrop-blur-md border border-white/5 rounded-3xl p-6 relative overflow-hidden group hover:border-emerald-500/30 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                        <div class="absolute -right-8 -top-8 opacity-[0.03] group-hover:opacity-5 group-hover:scale-110 transition-all duration-700">
                            <Zap class="w-48 h-48 text-emerald-400" />
                        </div>
                        <h3 class="text-[13px] font-bold text-emerald-500 uppercase tracking-wider flex items-center gap-2 mb-4">
                            <Zap class="w-4 h-4" />
                            Ticker Brief
                        </h3>
                        <p class="text-slate-300 text-[15px] leading-relaxed mb-6 relative z-10">
                            {{ companyProfile.code }} merupakan bank BUMN terbesar di Indonesia dengan fokus utama pada segmen mikro dan UMKM. Kekuatan utama perseroan berasal dari jaringan luas, basis nasabah besar, dan profitabilitas yang stabil.
                        </p>
                        <div class="flex flex-wrap gap-2 relative z-10">
                            <span class="px-3 py-1 bg-[#1a1f1c]/50 backdrop-blur border border-white/5 text-slate-300 text-[11px] font-bold tracking-wide rounded-md uppercase">SOE</span>
                            <span class="px-3 py-1 bg-[#1a1f1c]/50 backdrop-blur border border-white/5 text-slate-300 text-[11px] font-bold tracking-wide rounded-md uppercase">Banking</span>
                            <span class="px-3 py-1 bg-[#1a1f1c]/50 backdrop-blur border border-white/5 text-slate-300 text-[11px] font-bold tracking-wide rounded-md uppercase">UMKM</span>
                            <span class="px-3 py-1 bg-[#1a1f1c]/50 backdrop-blur border border-white/5 text-slate-300 text-[11px] font-bold tracking-wide rounded-md uppercase">Bluechip</span>
                        </div>
                    </div>
                    
                    <div class="lg:col-span-1 bg-[#121614]/70 backdrop-blur-md border border-white/5 rounded-3xl p-6 hover:border-red-900/30 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                        <h3 class="text-[13px] font-bold text-red-500 uppercase tracking-wider flex items-center gap-2 mb-4">
                            <AlertTriangle class="w-4 h-4" />
                            Risiko Utama
                        </h3>
                        <ul class="text-slate-400 text-sm leading-relaxed space-y-2.5 list-none">
                            <li v-for="(risk, idx) in mainRisks" :key="idx" class="flex gap-3">
                                <span class="text-red-500 mt-1">•</span>
                                <span>{{ risk }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- ROW 2: Profil Perusahaan -->
                    <div class="lg:col-span-3 bg-[#121614]/70 backdrop-blur-md border border-white/5 rounded-3xl p-6 lg:p-8 hover:shadow-lg transition-all duration-300">
                        <h3 class="text-[13px] font-bold text-emerald-500 uppercase tracking-wider flex items-center gap-2 mb-6">
                            <Building2 class="w-4 h-4" />
                            Profil Perusahaan
                        </h3>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-16 gap-y-6">
                            <div class="space-y-4">
                                <div class="grid grid-cols-[1fr_1.5fr] gap-4 text-sm">
                                    <span class="text-slate-500">Nama Lengkap</span>
                                    <span class="text-slate-200 font-medium">{{ companyProfile.fullName }}</span>
                                </div>
                                <div class="grid grid-cols-[1fr_1.5fr] gap-4 text-sm">
                                    <span class="text-slate-500">Kode Saham</span>
                                    <span class="text-slate-200 font-medium">{{ companyProfile.code }}</span>
                                </div>
                                <div class="grid grid-cols-[1fr_1.5fr] gap-4 text-sm">
                                    <span class="text-slate-500">Sektor</span>
                                    <span class="text-slate-200 font-medium">{{ companyProfile.sector }}</span>
                                </div>
                                <div class="grid grid-cols-[1fr_1.5fr] gap-4 text-sm">
                                    <span class="text-slate-500">Industri</span>
                                    <span class="text-slate-200 font-medium">{{ companyProfile.industry }}</span>
                                </div>
                                <div class="grid grid-cols-[1fr_1.5fr] gap-4 text-sm">
                                    <span class="text-slate-500">Papan Pencatatan</span>
                                    <span class="text-slate-200 font-medium">{{ companyProfile.board }}</span>
                                </div>
                                <div class="grid grid-cols-[1fr_1.5fr] gap-4 text-sm">
                                    <span class="text-slate-500">Tanggal Listing</span>
                                    <span class="text-slate-200 font-medium">{{ companyProfile.listingDate }}</span>
                                </div>
                                <div class="grid grid-cols-[1fr_1.5fr] gap-4 text-sm">
                                    <span class="text-slate-500">Website</span>
                                    <a :href="'https://' + companyProfile.website" target="_blank" class="text-emerald-400 hover:text-emerald-300 font-medium">{{ companyProfile.website }} ↗</a>
                                </div>
                                <div class="grid grid-cols-[1fr_1.5fr] gap-4 text-sm">
                                    <span class="text-slate-500">Bidang Usaha</span>
                                    <span class="text-slate-200 leading-relaxed">{{ companyProfile.business }}</span>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="grid grid-cols-[1fr_1.5fr] gap-4 text-sm">
                                    <span class="text-slate-500">Market Cap</span>
                                    <span class="text-slate-200 font-medium">{{ companyProfile.marketCap }}</span>
                                </div>
                                <div class="grid grid-cols-[1fr_1.5fr] gap-4 text-sm">
                                    <span class="text-slate-500">Jumlah Saham Beredar</span>
                                    <span class="text-slate-200 font-medium">{{ companyProfile.outstandingShares }}</span>
                                </div>
                                <div class="grid grid-cols-[1fr_1.5fr] gap-4 text-sm">
                                    <span class="text-slate-500">Alamat</span>
                                    <span class="text-slate-200 leading-relaxed">{{ companyProfile.address }}</span>
                                </div>
                                <div class="grid grid-cols-[1fr_1.5fr] gap-4 text-sm">
                                    <span class="text-slate-500">Telepon</span>
                                    <span class="text-slate-200 font-medium">{{ companyProfile.phone }}</span>
                                </div>
                                <div class="grid grid-cols-[1fr_1.5fr] gap-4 text-sm">
                                    <span class="text-slate-500">Email</span>
                                    <span class="text-slate-200 font-medium">{{ companyProfile.email }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ROW 3: Financial Highlight & Rasio -->
                    <div class="lg:col-span-2 bg-[#121614]/70 backdrop-blur-md border border-white/5 rounded-3xl p-6 lg:p-8 hover:shadow-lg transition-all duration-300">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-sm font-bold text-white">Financial Highlight <span class="text-xs text-slate-500 font-normal ml-2">(Kuartal Terakhir)</span></h3>
                            <a href="#" class="text-xs font-semibold text-emerald-500 hover:text-emerald-400 transition-colors">Lihat Semua &rarr;</a>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="(item, idx) in financialHighlights" :key="idx" class="bg-[#1a1f1c] border border-emerald-950/20 rounded-2xl p-4 hover:border-emerald-500/30 hover:bg-[#1e2421] transition-all">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="p-2 bg-[#121614] rounded-lg text-emerald-500 border border-emerald-900/30">
                                        <component :is="iconMap[item.icon]" class="w-4 h-4" />
                                    </div>
                                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ item.title }}</span>
                                </div>
                                <div class="text-xl font-bold text-white mb-1.5 tracking-tight">{{ item.value }}</div>
                                <div :class="['text-[11px] font-bold px-2 py-0.5 rounded-md inline-block', item.type === 'up' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400']">
                                    {{ item.change }} YoY
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1 bg-[#121614]/70 backdrop-blur-md border border-white/5 rounded-3xl p-6 lg:p-8 hover:shadow-lg transition-all duration-300">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-sm font-bold text-white">Rasio Keuangan</h3>
                            <a href="#" class="text-xs font-semibold text-emerald-500 hover:text-emerald-400 transition-colors">Lihat Semua &rarr;</a>
                        </div>
                        <div class="grid grid-cols-4 text-[11px] uppercase tracking-wider font-bold text-slate-500 mb-2 px-2">
                            <div class="col-span-2">Rasio</div>
                            <div class="text-right">Nilai</div>
                            <div class="text-right">YoY</div>
                        </div>
                        <div class="space-y-0.5">
                            <div v-for="(ratio, idx) in financialRatios" :key="idx" class="grid grid-cols-4 text-sm px-2 py-2.5 rounded-xl hover:bg-[#1a1f1c] transition-colors items-center">
                                <div class="col-span-2 text-slate-300 font-semibold">{{ ratio.name }}</div>
                                <div class="text-right text-white font-bold">{{ ratio.value }}</div>
                                <div :class="['text-right text-xs font-bold', ratio.change.startsWith('+') ? 'text-emerald-400' : 'text-red-400']">
                                    {{ ratio.change }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ROW 4: Tren Kinerja Chart -->
                    <div class="lg:col-span-3 bg-[#121614]/70 backdrop-blur-md border border-white/5 rounded-3xl p-6 lg:p-8 hover:shadow-lg transition-all duration-300">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-8">
                            <h3 class="text-[13px] font-bold text-emerald-500 uppercase tracking-wider flex items-center gap-2">
                                <Activity class="w-4 h-4" />
                                Tren Kinerja
                            </h3>
                            <div class="flex gap-2">
                                <select class="bg-[#1a1f1c] border border-emerald-950/50 text-slate-300 text-xs font-semibold rounded-lg px-3 py-2 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                                    <option>Net Profit</option>
                                    <option>Revenue</option>
                                    <option>Assets</option>
                                </select>
                                <select class="bg-[#1a1f1c] border border-emerald-950/50 text-slate-300 text-xs font-semibold rounded-lg px-3 py-2 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                                    <option>Tahunan</option>
                                    <option>Kuartalan</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="text-xs font-medium text-slate-500 mb-6">Dalam Triliun Rupiah</div>
                        
                        <!-- Dummy Chart Container -->
                        <div class="h-64 w-full relative flex items-end justify-between px-2 pb-6 border-b border-slate-800/50">
                            <!-- Y-Axis labels -->
                            <div class="absolute left-0 top-0 bottom-6 w-8 flex flex-col justify-between text-[10px] text-slate-500 font-mono text-right pr-2 border-r border-slate-800/50">
                                <span>20T</span>
                                <span>15T</span>
                                <span>10T</span>
                                <span>5T</span>
                                <span>0</span>
                            </div>
                            
                            <!-- Dummy Bars -->
                            <div class="w-full h-full ml-10 flex items-end justify-between gap-3 md:gap-6 px-4">
                                <div class="w-full bg-emerald-500/80 hover:bg-emerald-400 rounded-t-sm transition-all relative group cursor-pointer" style="height: 30%;">
                                    <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] font-bold py-1 px-2 rounded whitespace-nowrap transition-opacity">Rp 6,0 T</div>
                                </div>
                                <div class="w-full bg-emerald-500/80 hover:bg-emerald-400 rounded-t-sm transition-all relative group cursor-pointer" style="height: 35%;">
                                    <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] font-bold py-1 px-2 rounded whitespace-nowrap transition-opacity">Rp 7,0 T</div>
                                </div>
                                <div class="w-full bg-emerald-500/80 hover:bg-emerald-400 rounded-t-sm transition-all relative group cursor-pointer" style="height: 45%;">
                                    <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] font-bold py-1 px-2 rounded whitespace-nowrap transition-opacity">Rp 9,0 T</div>
                                </div>
                                <div class="w-full bg-emerald-500/80 hover:bg-emerald-400 rounded-t-sm transition-all relative group cursor-pointer" style="height: 50%;">
                                    <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] font-bold py-1 px-2 rounded whitespace-nowrap transition-opacity">Rp 10,0 T</div>
                                </div>
                                <div class="w-full bg-emerald-500/80 hover:bg-emerald-400 rounded-t-sm transition-all relative group cursor-pointer" style="height: 60%;">
                                    <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] font-bold py-1 px-2 rounded whitespace-nowrap transition-opacity">Rp 12,0 T</div>
                                </div>
                                <div class="w-full bg-emerald-500/80 hover:bg-emerald-400 rounded-t-sm transition-all relative group cursor-pointer" style="height: 65%;">
                                    <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] font-bold py-1 px-2 rounded whitespace-nowrap transition-opacity">Rp 13,0 T</div>
                                </div>
                                <div class="w-full bg-emerald-500/80 hover:bg-emerald-400 rounded-t-sm transition-all relative group cursor-pointer" style="height: 75%;">
                                    <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] font-bold py-1 px-2 rounded whitespace-nowrap transition-opacity">Rp 15,0 T</div>
                                </div>
                            </div>
                            
                            <!-- X-Axis labels -->
                            <div class="absolute left-10 right-0 -bottom-2 h-6 flex justify-between px-4 text-[10px] text-slate-500 font-mono">
                                <span>2017</span>
                                <span>2018</span>
                                <span>2019</span>
                                <span>2020</span>
                                <span>2021</span>
                                <span>2022</span>
                                <span>2023</span>
                            </div>
                        </div>
                    </div>

                    <!-- ROW 5: Berita & Keterbukaan -->
                    <div class="lg:col-span-2">
                        <div class="flex justify-between items-center mb-6 px-2">
                            <h3 class="text-sm font-bold text-white">Berita & Riset Terkait</h3>
                            <a href="#" class="text-xs font-semibold text-emerald-500 hover:text-emerald-400 transition-colors">Lihat Semua &rarr;</a>
                        </div>
                        
                        <div v-if="articles.length > 0" class="space-y-4">
                            <Link 
                                v-for="article in articles" 
                                :key="article.id" 
                                :href="route('news.detail', article.slug)"
                                class="flex gap-5 items-center bg-[#121614] border border-emerald-950/20 hover:border-emerald-800/50 hover:bg-[#161a18] rounded-3xl p-5 transition-all group"
                            >
                                <img 
                                    :src="article.cover_image" 
                                    :alt="article.title" 
                                    class="w-20 h-20 md:w-24 md:h-24 rounded-2xl object-cover shrink-0"
                                />
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-md uppercase tracking-wider">{{ article.badge || 'Berita' }}</span>
                                        <span class="text-xs font-medium text-slate-500">{{ article.published_at || '10 Jun 2026' }}</span>
                                    </div>
                                    <h4 class="text-sm md:text-base font-bold text-slate-200 group-hover:text-emerald-400 transition-colors line-clamp-2 leading-snug">
                                        {{ article.title }}
                                    </h4>
                                </div>
                            </Link>
                        </div>
                        <div v-else class="bg-[#121614] border border-emerald-950/20 rounded-3xl p-8 text-center text-sm text-slate-500">
                            Belum ada berita atau riset terkait.
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="flex justify-between items-center mb-6 px-2">
                            <h3 class="text-sm font-bold text-white">Keterbukaan Informasi</h3>
                            <a href="#" class="text-xs font-semibold text-emerald-500 hover:text-emerald-400 transition-colors">Lihat Semua &rarr;</a>
                        </div>
                        
                        <div v-if="disclosures && disclosures.length > 0" class="space-y-4">
                            <div 
                                v-for="disclosure in disclosures" 
                                :key="disclosure.id" 
                                class="flex gap-4 bg-[#121614] border border-emerald-950/20 hover:border-emerald-800/30 rounded-3xl p-5 transition-all"
                            >
                                <div class="mt-0.5">
                                    <div class="w-10 h-10 rounded-xl bg-red-500/10 flex items-center justify-center text-red-400">
                                        <Newspaper class="w-5 h-5" />
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="text-[11px] font-bold text-slate-500 mb-1.5">{{ disclosure.date || '11 Jun 2026' }}</div>
                                    <h4 class="text-sm font-bold text-slate-200 mb-2 leading-snug">
                                        {{ disclosure.title }}
                                    </h4>
                                    <div class="flex justify-between items-center mt-3 pt-3 border-t border-emerald-950/30">
                                        <span class="text-[10px] text-slate-500 font-medium">Kategori: {{ disclosure.category || 'Laporan Bulanan' }}</span>
                                        <a v-if="disclosure.source_url" :href="disclosure.source_url" target="_blank" class="text-emerald-500 hover:text-emerald-400 bg-emerald-500/10 p-1.5 rounded-lg">
                                            <Download class="w-3 h-3" />
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="bg-[#121614] border border-emerald-950/20 rounded-3xl p-8 text-center text-sm text-slate-500">
                            Belum ada Keterbukaan Informasi.
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AppLayout>
</template>
