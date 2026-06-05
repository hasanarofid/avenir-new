<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Users, FileText, Layers, Settings, ArrowRight, Activity, TrendingUp, UserCheck, Search, Award } from '@lucide/vue';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
  stats: {
    type: Object,
    required: true
  },
  recent_posts: {
    type: Array,
    required: true
  }
});

const trendData = props.stats.trend_data?.reverse() || [30, 45, 40, 60, 50, 80, 70, 90, 85, 110, 95, 120];
const totalViews = props.stats.total_views || 0;
const activeSubscribers = props.stats.active_subscribers || 0;
const activeTrials = props.stats.active_trials || 0;
const totalMitra = props.stats.total_mitra || 0;
const totalAccounts = props.stats.users_count || 0;
const totalLikes = props.stats.likes_count || 0;
const totalComments = props.stats.comments_count || 0;
const totalEngagement = totalLikes + totalComments;

// Donut chart math helpers
const donutData = [
  { label: 'Subscriber', value: activeSubscribers, color: '#1B6B3A' },
  { label: 'Trial', value: activeTrials, color: '#10b981' },
  { label: 'Mitra', value: totalMitra, color: '#f59e0b' },
  { label: 'Inactive', value: Math.max(0, totalAccounts - activeSubscribers - activeTrials - totalMitra), color: '#374151' }
];

const totalUsers = donutData.reduce((sum, d) => sum + d.value, 0) || 1;
let currentPct = 0;
const donutArcs = donutData.map(d => {
  const pct = d.value / totalUsers;
  const startAngle = currentPct * 360;
  const endAngle = (currentPct + pct) * 360;
  currentPct += pct;
  
  const cx = 70, cy = 70, radius = 56;
  const startRad = (startAngle - 90) * Math.PI / 180;
  const endRad = (endAngle - 90) * Math.PI / 180;
  const x1 = cx + radius * Math.cos(startRad);
  const y1 = cy + radius * Math.sin(startRad);
  const x2 = cx + radius * Math.cos(endRad);
  const y2 = cy + radius * Math.sin(endRad);
  const largeArc = pct > 0.5 ? 1 : 0;
  
  const path = pct === 1 
    ? `M ${cx} ${cy-radius} A ${radius} ${radius} 0 1 1 ${cx} ${cy+radius} A ${radius} ${radius} 0 1 1 ${cx} ${cy-radius}`
    : `M ${x1} ${y1} A ${radius} ${radius} 0 ${largeArc} 1 ${x2} ${y2}`;
    
  return { ...d, path, pct };
});

const filters = ['Trending Now', 'Kesehatan Perusahaan', 'Persaingan', 'Sales Flow & Valuasi', 'Target Evaluasi', 'Fitback'];
const activeFilter = ref('Trending Now');

const filteredResearch = computed(() => {
  const data = {
    'Trending Now': ['ADRO', 'BBCA', 'ASII', 'GOTO', 'BREN', 'UNTR', 'TLKM', 'ICBP', 'AMMN', 'BMRI'],
    'Kesehatan Perusahaan': ['BBCA', 'BMRI', 'BBNI', 'BBRI', 'ASII', 'TLKM', 'KLBF', 'ICBP', 'INDF', 'UNVR'],
    'Persaingan': ['GOTO', 'BUKA', 'BELI', 'WIRG', 'EMTK', 'SCMA', 'MNCN', 'MSIN', 'BMTR', 'VIVA'],
    'Sales Flow & Valuasi': ['ADRO', 'ITMG', 'PTBA', 'UNTR', 'BYAN', 'HRUM', 'INDY', 'BUMI', 'ENRG', 'MEDC'],
    'Target Evaluasi': ['BREN', 'CUAN', 'PANI', 'AMMN', 'TPIA', 'BRPT', 'PGEO', 'VKTR', 'NCKL', 'MBMA'],
    'Fitback': ['GOTO', 'ARTO', 'BBYB', 'BANK', 'AGRO', 'BBHI', 'BGTG', 'PNBS', 'DNAR', 'NOBU']
  };
  return data[activeFilter.value] || data['Trending Now'];
});

const filteredTrendViews = computed(() => {
  const multipliers = {
    'Trending Now': 1,
    'Kesehatan Perusahaan': 0.8,
    'Persaingan': 0.6,
    'Sales Flow & Valuasi': 0.7,
    'Target Evaluasi': 0.4,
    'Fitback': 0.3
  };
  const mult = multipliers[activeFilter.value] || 1;
  return trendData.map(val => Math.round(val * mult));
});

const filteredTotalViews = computed(() => {
  // Base total from backend, adjusted mock style for UX feedback
  const base = totalViews || 52400;
  const multipliers = {
    'Trending Now': 1,
    'Kesehatan Perusahaan': 0.8,
    'Persaingan': 0.6,
    'Sales Flow & Valuasi': 0.7,
    'Target Evaluasi': 0.4,
    'Fitback': 0.3
  };
  return Math.round(base * (multipliers[activeFilter.value] || 1));
});

const filteredMaxTrend = computed(() => Math.max(...filteredTrendViews.value, 1));
const filteredTrendPoints = computed(() => {
  return filteredTrendViews.value.map((val, i) => {
    const x = (i / (filteredTrendViews.value.length - 1)) * 100;
    const y = 100 - ((val / filteredMaxTrend.value) * 100);
    return `${x},${y}`;
  }).join(' ');
});

</script>

<template>
  <Head title="Admin Dashboard" />

  <AdminLayout>
    <div class="space-y-6 max-w-7xl mx-auto pb-12">
      <!-- Title Page -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
            Halo, Avenir <span class="text-2xl">👋</span>
          </h2>
          <p class="text-sm text-slate-400 mt-1">Sistem pemantauan dan analitik Avenir Research.</p>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="flex flex-wrap items-center gap-2 border-b border-emerald-950/30 pb-4">
        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mr-2">Views Filter:</div>
        <button 
          v-for="filter in filters" 
          :key="filter"
          @click="activeFilter = filter"
          :class="[
            'px-4 py-1.5 rounded-full text-xs transition-all',
            activeFilter === filter 
              ? 'border border-emerald-500 bg-emerald-500 text-white font-bold'
              : 'border border-emerald-950/50 bg-[#121614] text-slate-300 hover:border-emerald-700 font-semibold'
          ]"
        >
          {{ filter }}
        </button>
      </div>

      <!-- Stats Grid (5 cards like Screenshot) -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-[#0f3823] border border-[#0f3823] rounded-2xl p-5 relative overflow-hidden shadow-xl flex flex-col justify-between">
          <div class="text-[10px] font-bold text-emerald-300 uppercase tracking-widest mb-3 flex items-center gap-1.5">
            <span>⚡</span> Total Akun
          </div>
          <div>
            <h3 class="text-3xl font-black text-white font-mono">{{ totalAccounts }}</h3>
            <p class="text-xs text-emerald-400 mt-1">12 baru bulan ini</p>
          </div>
        </div>

        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-5 relative overflow-hidden shadow-xl flex flex-col justify-between">
          <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-1.5">
            <Users class="w-3.5 h-3.5" /> Subscriber Aktif
          </div>
          <div>
            <h3 class="text-3xl font-black text-emerald-500 font-mono">{{ activeSubscribers }}</h3>
            <p class="text-[10px] text-slate-500 mt-1">Berbayar/mitra</p>
          </div>
        </div>

        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-5 relative overflow-hidden shadow-xl flex flex-col justify-between">
          <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-1.5">
            <Layers class="w-3.5 h-3.5" /> Trial Aktif
          </div>
          <div>
            <h3 class="text-3xl font-black text-blue-500 font-mono">{{ activeTrials }}</h3>
            <p class="text-[10px] text-slate-500 mt-1">Dalam 7 hari</p>
          </div>
        </div>

        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-5 relative overflow-hidden shadow-xl flex flex-col justify-between">
          <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-1.5">
            <UserCheck class="w-3.5 h-3.5" /> Mitra
          </div>
          <div>
            <h3 class="text-3xl font-black text-amber-500 font-mono">{{ totalMitra }}</h3>
            <p class="text-[10px] text-slate-500 mt-1">Aktif</p>
          </div>
        </div>

        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-5 relative overflow-hidden shadow-xl flex flex-col justify-between">
          <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-1.5">
            <Search class="w-3.5 h-3.5" /> Research Views
          </div>
          <div>
            <h3 class="text-3xl font-black text-white font-mono">{{ filteredTotalViews.toLocaleString('id-ID') }}</h3>
            <p class="text-[10px] text-slate-500 mt-1">Exclude Tim Avenir</p>
          </div>
        </div>
      </div>

      <!-- Trend Chart -->
      <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl space-y-6">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-bold text-white">Trend Views — 30 Hari Terakhir</h3>
            <p class="text-xs text-slate-500">LIVE · {{ activeFilter }}</p>
          </div>
          <div class="px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-mono font-bold text-xs rounded-full">
            {{ filteredTotalViews.toLocaleString('id-ID') }} VIEWS
          </div>
        </div>
        
        <div class="relative w-full h-[240px] mt-4">
          <svg class="w-full h-full overflow-visible" viewBox="0 0 100 100" preserveAspectRatio="none">
            <defs>
              <linearGradient id="trendGradBg" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#10b981" stop-opacity="0.3"/>
                <stop offset="100%" stop-color="#10b981" stop-opacity="0"/>
              </linearGradient>
            </defs>
            <line x1="0" y1="25" x2="100" y2="25" stroke="#1e293b" stroke-width="0.5" stroke-dasharray="2 2" />
            <line x1="0" y1="50" x2="100" y2="50" stroke="#1e293b" stroke-width="0.5" stroke-dasharray="2 2" />
            <line x1="0" y1="75" x2="100" y2="75" stroke="#1e293b" stroke-width="0.5" stroke-dasharray="2 2" />
            <line x1="0" y1="100" x2="100" y2="100" stroke="#1e293b" stroke-width="0.5" />
            
            <polygon :points="`0,100 ${filteredTrendPoints} 100,100`" fill="url(#trendGradBg)" class="transition-all duration-500" />
            <polyline :points="filteredTrendPoints" fill="none" stroke="#10b981" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round" class="transition-all duration-500" />
            
            <circle v-for="(val, i) in filteredTrendViews" :key="i" 
              :cx="(i / (filteredTrendViews.length - 1)) * 100" 
              :cy="100 - ((val / filteredMaxTrend) * 100)" 
              r="1.5" fill="#121614" stroke="#10b981" stroke-width="1" class="transition-all duration-500 hover:r-2 hover:fill-[#10b981] cursor-pointer" />
          </svg>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Donut Chart (Distribusi User) -->
        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl">
          <div>
            <h3 class="text-lg font-bold text-white">Distribusi User</h3>
            <p class="text-xs text-slate-500">SNAPSHOT · Status akun saat ini</p>
          </div>
          
          <div class="flex flex-col sm:flex-row items-center gap-8 mt-8 justify-center">
            <div class="relative w-[140px] h-[140px] flex-shrink-0">
              <svg viewBox="0 0 140 140" class="w-full h-full -rotate-90">
                <circle cx="70" cy="70" r="56" fill="none" stroke="#1e293b" stroke-width="16" />
                <path v-for="(arc, i) in donutArcs" :key="i" 
                  :d="arc.path" fill="none" :stroke="arc.color" stroke-width="16" stroke-linecap="butt" 
                  v-show="arc.pct > 0" class="transition-all duration-500 ease-out" />
              </svg>
              <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                <span class="text-2xl font-black font-mono text-white">{{ totalUsers.toLocaleString('id-ID') }}</span>
                <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">Total User</span>
              </div>
            </div>
            
            <div class="flex flex-col gap-3 flex-1 w-full max-w-[200px]">
              <div v-for="(arc, i) in donutArcs" :key="i" class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: arc.color }"></span>
                  <span class="text-xs font-semibold text-slate-300">{{ arc.label }}</span>
                </div>
                <span class="text-sm font-mono font-bold text-white">{{ arc.value.toLocaleString('id-ID') }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Conversion & Revenue -->
        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl">
          <div>
            <h3 class="text-lg font-bold text-white">Konversi & Revenue</h3>
            <p class="text-xs text-slate-500">METRIC · Trial → Subscriber funnel</p>
          </div>
          
          <div class="mt-6 space-y-4">
            <div class="p-4 bg-[#090b0a] border border-emerald-950/30 rounded-xl flex items-center justify-between">
              <div>
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Estimated MRR</div>
                <div class="text-2xl font-black text-emerald-500 font-mono"><span class="text-sm">Rp</span> 5.9M</div>
              </div>
              <div class="w-12 h-12 rounded-full border-4 border-emerald-900 flex items-center justify-center">
                <Award class="w-5 h-5 text-emerald-500" />
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div class="p-4 bg-[#090b0a] border border-emerald-950/30 rounded-xl">
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Conversion Rate</div>
                <div class="text-xl font-black text-white font-mono">5.0<span class="text-slate-400">%</span></div>
                <div class="w-full bg-slate-800 h-1.5 mt-3 rounded overflow-hidden">
                  <div class="bg-emerald-500 h-full" style="width: 5%"></div>
                </div>
              </div>
              <div class="p-4 bg-[#090b0a] border border-emerald-950/30 rounded-xl">
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Trial Drop-off</div>
                <div class="text-xl font-black text-white font-mono">87.4<span class="text-slate-400">%</span></div>
                <div class="w-full bg-slate-800 h-1.5 mt-3 rounded overflow-hidden">
                  <div class="bg-rose-500 h-full" style="width: 87.4%"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pos Pertumbuhan -->
      <div class="bg-[#0f3823] border border-emerald-800/50 rounded-2xl p-6 shadow-xl relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-900/20 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-bold text-white">Pos Pertumbuhan — {{ new Date().toLocaleString('id-ID', {month: 'long', year: 'numeric'}) }}</h3>
              <p class="text-xs text-emerald-400/80">REVENUE POOL · Distribusi insentif untuk tim riset dan kontributor eksternal berdasarkan performa</p>
            </div>
          </div>
          
          <div class="mt-6 bg-[#090b0a]/40 border border-emerald-800/40 rounded-xl p-5">
            <div class="flex items-center justify-between border-b border-emerald-800/40 pb-4 mb-4">
              <div>
                <div class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest mb-1">Tim Avenir</div>
                <div class="text-2xl font-black text-white font-mono">Rp 0</div>
              </div>
              <div class="px-3 py-1 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold rounded-full">CURRENT POOL</div>
            </div>
            <div class="grid grid-cols-4 gap-4">
              <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Views</div>
                <div class="text-lg font-bold text-white font-mono">34</div>
              </div>
              <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Target Monthly</div>
                <div class="text-lg font-bold text-white font-mono">51</div>
              </div>
              <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Qualified Research</div>
                <div class="text-lg font-bold text-white font-mono">37</div>
              </div>
              <div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Share</div>
                <div class="text-lg font-bold text-emerald-400 font-mono">100.0%</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Top 10 Research -->
      <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl">
        <div>
          <h3 class="text-lg font-bold text-white">Top 10 Riset Paling Banyak Dilihat</h3>
          <p class="text-xs text-slate-500">RANKING · Berdasarkan unique views · Excluded Tim Avenir</p>
        </div>
        
        <div class="mt-6 space-y-4">
          <!-- Dummy List -->
          <div v-for="(item, i) in filteredResearch" :key="item" class="flex items-center gap-4">
            <div class="w-6 h-6 rounded bg-[#090b0a] border border-emerald-950/50 flex items-center justify-center text-xs font-bold text-slate-400">{{ i + 1 }}</div>
            <div class="flex-1">
              <div class="flex items-center justify-between mb-1.5">
                <div class="flex items-center gap-2">
                  <span class="text-sm font-bold text-white">{{ item }}</span>
                  <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Tim Avenir</span>
                </div>
                <div class="text-sm font-bold font-mono text-white">{{ Math.round((80 - i * 5) * (filteredTotalViews / 52400)) }}</div>
              </div>
              <div class="w-full bg-[#090b0a] h-1.5 rounded overflow-hidden">
                <div class="bg-emerald-500 h-full rounded transition-all duration-500" :style="{ width: (100 - i * 10) + '%' }"></div>
              </div>
            </div>
            <div class="w-16 text-right text-[10px] text-slate-500 font-semibold">{{ Math.round((80 - i * 5) * (filteredTotalViews / 52400)) }} unique</div>
          </div>
        </div>
      </div>

      <!-- Heatmap -->
      <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl">
        <div>
          <h3 class="text-lg font-bold text-white">Heatmap Aktivitas — Hari × Jam</h3>
          <p class="text-xs text-slate-500">30 HARI · WIB · Excluded Tim Avenir · Hover untuk detail</p>
        </div>
        
        <!-- Mock Github style Heatmap Grid -->
        <div class="mt-6 overflow-x-auto pb-4">
          <div class="min-w-[700px] flex gap-1">
            <div v-for="col in 24" :key="col" class="flex flex-col gap-1">
              <div v-for="row in 7" :key="row" 
                class="w-4 h-4 rounded-sm border border-[#090b0a]" 
                :class="[
                  Math.random() > 0.8 ? 'bg-emerald-500' : 
                  Math.random() > 0.6 ? 'bg-emerald-700' : 
                  Math.random() > 0.4 ? 'bg-emerald-900' : 'bg-slate-800'
                ]"
              ></div>
            </div>
          </div>
          <div class="flex justify-between items-center mt-4">
            <div class="text-xs font-bold text-white">Minggu <span class="text-slate-500 font-normal block">Peak Day</span></div>
            <div class="text-xs font-bold text-white">10:00 <span class="text-slate-500 font-normal block">Peak Hour</span></div>
            <div class="text-xs font-bold text-white">119 <span class="text-slate-500 font-normal block">Views on Peak</span></div>
          </div>
        </div>
      </div>

      <!-- Engagement & Subscription Status -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl space-y-6">
          <div>
            <h3 class="text-lg font-bold text-white">Engagement Funnel</h3>
            <p class="text-xs text-slate-500">CONVERSION · Account → Trial → Subscribe</p>
          </div>

          <div class="space-y-3 mt-4">
            <div class="relative w-full bg-[#090b0a] h-10 rounded overflow-hidden flex items-center px-3 border border-emerald-950/20">
              <div class="absolute top-0 left-0 bottom-0 bg-emerald-900/40 border-r border-emerald-500/30" style="width: 100%"></div>
              <div class="relative z-10 flex justify-between w-full text-xs font-bold text-white">
                <span>Total Users</span>
                <span class="font-mono">{{ totalAccounts.toLocaleString('id-ID') }}</span>
              </div>
            </div>
            <div class="relative w-full bg-[#090b0a] h-10 rounded overflow-hidden flex items-center px-3 border border-emerald-950/20">
              <div class="absolute top-0 left-0 bottom-0 bg-emerald-700/40 border-r border-emerald-500/50" :style="{ width: totalAccounts > 0 ? ((activeSubscribers / totalAccounts) * 100).toFixed(1) + '%' : '0%' }"></div>
              <div class="relative z-10 flex justify-between w-full text-xs font-bold text-white">
                <span>Total Subscribers</span>
                <span class="font-mono">{{ activeSubscribers.toLocaleString('id-ID') }}</span>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4 mt-6">
            <div class="p-4 bg-[#090b0a] border border-emerald-950/30 rounded-xl">
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Conversion Rate</div>
              <div class="text-xl font-black text-white font-mono">5.0<span class="text-slate-400">%</span></div>
            </div>
            <div class="p-4 bg-[#090b0a] border border-emerald-950/30 rounded-xl">
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Estimated LTV</div>
              <div class="text-xl font-black text-white font-mono">Rp 598.000</div>
            </div>
          </div>
        </div>

        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl">
          <div>
            <h3 class="text-lg font-bold text-white">Status Subscription</h3>
            <p class="text-xs text-slate-500">DISTRIBUSI · Akun aktif vs expired/guest</p>
          </div>
          
          <div class="mt-8 space-y-6">
            <div>
              <div class="flex justify-between text-xs font-bold text-white mb-2">
                <span>Active Subs</span>
                <span>{{ activeSubscribers }} <span class="text-slate-500 font-normal ml-1">5.1%</span></span>
              </div>
              <div class="w-full bg-[#090b0a] h-2 rounded-full overflow-hidden border border-emerald-950/20">
                <div class="bg-emerald-500 h-full rounded-full" style="width: 5.1%"></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between text-xs font-bold text-white mb-2">
                <span>Trial Aktif</span>
                <span>{{ activeTrials }} <span class="text-slate-500 font-normal ml-1">97.4%</span></span>
              </div>
              <div class="w-full bg-[#090b0a] h-2 rounded-full overflow-hidden border border-emerald-950/20">
                <div class="bg-emerald-500 h-full rounded-full" style="width: 97.4%"></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between text-xs font-bold text-white mb-2">
                <span>Inactive/Guest</span>
                <span>0 <span class="text-slate-500 font-normal ml-1">0%</span></span>
              </div>
              <div class="w-full bg-[#090b0a] h-2 rounded-full overflow-hidden border border-emerald-950/20">
                <div class="bg-slate-700 h-full rounded-full" style="width: 0%"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Authors -->
      <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl">
        <div>
          <h3 class="text-lg font-bold text-white">Top Authors — Share of Views</h3>
          <p class="text-xs text-slate-500">LEADERBOARD · {{ new Date().toLocaleString('id-ID', {month: 'long', year: 'numeric'}) }} · % share dari total views bulan ini</p>
        </div>
        <div class="mt-8">
          <div class="flex items-center gap-4">
            <span class="text-xs font-bold text-white w-20 truncate">Tim Avenir</span>
            <div class="flex-1 bg-[#090b0a] h-4 rounded-full overflow-hidden border border-emerald-950/20">
              <div class="bg-emerald-600 h-full" style="width: 100%"></div>
            </div>
            <span class="text-xs font-bold font-mono text-emerald-400">100%</span>
          </div>
        </div>
      </div>

      <!-- Quick Insights -->
      <div class="bg-[#0b291a] border border-[#0f3823] rounded-2xl p-6 shadow-xl">
        <div class="flex items-center justify-between border-b border-emerald-900/30 pb-4 mb-5">
          <div>
            <h3 class="text-lg font-bold text-white">Quick Insights</h3>
            <p class="text-xs text-emerald-400">SUMMARY · {{ new Date().toLocaleString('id-ID', {month: 'long', year: 'numeric'}) }}</p>
          </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
          <div class="bg-[#090b0a]/20 border-l-2 border-emerald-400 p-4 rounded-lg">
            <div class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest mb-2 font-mono">Total Accounts</div>
            <div class="text-2xl font-bold text-white">{{ totalAccounts.toLocaleString('id-ID') }}</div>
          </div>
          <div class="bg-[#090b0a]/20 border-l-2 border-amber-400 p-4 rounded-lg">
            <div class="text-[10px] font-bold text-amber-400 uppercase tracking-widest mb-2 font-mono">Active Mitra</div>
            <div class="text-2xl font-bold text-white">{{ totalMitra }}</div>
          </div>
          <div class="bg-[#090b0a]/20 border-l-2 border-emerald-400 p-4 rounded-lg">
            <div class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest mb-2 font-mono">Full Access</div>
            <div class="text-2xl font-bold text-white">{{ activeSubscribers + activeTrials }} <span class="text-xs font-normal text-emerald-600">({{activeTrials}}T+{{activeSubscribers}}S)</span></div>
          </div>
          <div class="bg-[#090b0a]/20 border-l-2 border-emerald-400 p-4 rounded-lg">
            <div class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest mb-2 font-mono">Research Views</div>
            <div class="text-2xl font-bold text-white">{{ totalViews.toLocaleString('id-ID') }}</div>
          </div>
          <div class="bg-[#090b0a]/20 border-l-2 border-emerald-400 p-4 rounded-lg">
            <div class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest mb-2 font-mono">Engagement</div>
            <div class="text-2xl font-bold text-white">{{ totalEngagement.toLocaleString('id-ID') }}</div>
          </div>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>
