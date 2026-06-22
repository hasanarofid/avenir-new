<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';

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
    if (!props.newsList) return [];
    return props.newsList.filter(n => n.is_featured).slice(0, 3);
});

const selectedCategories = ref([]);
const selectedSources = ref([]);
const selectedYear = ref('Semua');
const searchQuery = ref('');
const sortOrder = ref('Terbaru');
const visibleCount = ref(10);

const uniqueCategories = computed(() => {
    const cats = props.newsList.map(n => n.category).filter(Boolean).map(s => s.trim());
    return [...new Set(cats)];
});
const uniqueSources = computed(() => {
    const sources = props.newsList.map(n => n.source ? n.source.trim() : 'Avenir Research');
    return [...new Set(sources)];
});
const uniqueYears = computed(() => {
    const years = props.newsList.map(n => {
        if(!n.published_at) return null;
        const match = String(n.published_at).match(/\d{4}/);
        return match ? match[0] : null;
    }).filter(Boolean);
    return ['Semua', ...new Set(years)].sort((a,b) => b-a);
});

const resetFilters = () => {
    searchQuery.value = '';
    selectedCategories.value = [];
    selectedSources.value = [];
    selectedYear.value = 'Semua';
    sortOrder.value = 'Terbaru';
};

const loadMore = () => {
    visibleCount.value += 10;
};

const filteredRecentNews = computed(() => {
    let list = props.newsList || [];
    
    // Hanya kecualikan berita utama dari daftar terbaru jika total berita cukup banyak (> 5)
    // agar bagian 'Berita Terbaru' tidak terlihat kosong saat jumlah berita masih sedikit.
    const featuredIds = featuredNews.value.map(f => f.id);
    const hasFilters = searchQuery.value || selectedCategories.value.length > 0 || selectedSources.value.length > 0 || selectedYear.value !== 'Semua';
    
    if (list.length > 5 && !hasFilters) {
        list = list.filter(n => !featuredIds.includes(n.id));
    }
    
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(n => 
            (n.title && n.title.toLowerCase().includes(q)) || 
            (n.category && n.category.toLowerCase().includes(q))
        );
    }
    if (selectedCategories.value.length > 0) {
        list = list.filter(n => selectedCategories.value.includes(n.category));
    }
    if (selectedSources.value.length > 0) {
        list = list.filter(n => {
            const src = n.source ? n.source.trim() : 'Avenir Research';
            return selectedSources.value.includes(src);
        });
    }
    if (selectedYear.value !== 'Semua') {
        list = list.filter(n => n.published_at && String(n.published_at).includes(selectedYear.value));
    }
    
    if (sortOrder.value === 'Terpopuler') {
        list = [...list].sort((a, b) => b.title.length - a.title.length); 
    }
    
    return list;
});

const recentNews = computed(() => {
    return filteredRecentNews.value.slice(0, visibleCount.value);
});

const hasMoreRecentNews = computed(() => {
    return filteredRecentNews.value.length > visibleCount.value;
});

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


const mostRead = computed(() => {
    return [...props.newsList].sort((a, b) => b.title.length - a.title.length).slice(0, 5); 
});

const watchlistNews = computed(() => {
    const list = props.marketConfig.watchlist || [];
    const result = [];
    
    list.forEach(sym => {
        const ticker = sym.replace('.JK', '');
        const newsItem = props.newsList.find(n => n.title.includes(ticker));
        if (newsItem) {
            result.push({
                ticker: ticker,
                title: newsItem.title,
                time: newsItem.published_at || 'Baru saja',
                slug: newsItem.slug,
                source_url: newsItem.source_url,
                author: newsItem.author
            });
        }
    });
    
    if (result.length < 3) {
        list.forEach(sym => {
            const ticker = sym.replace('.JK', '');
            if (!result.find(r => r.ticker === ticker)) {
                const quote = getQuote(sym, { symbol: sym, price: 0, changePercent: 0 });
                const name = quote.name || sym;
                const changeStr = (quote.changePercent > 0 ? '+' : '') + Number(quote.changePercent).toFixed(2) + '%';
                result.push({
                    ticker: ticker,
                    title: `${name} diperdagangkan di Rp ${formatPrice(quote.price)} (${changeStr})`,
                    time: 'Hari ini',
                    slug: null
                });
            }
        });
    }
    
    return result;
});

const trendingTickers = computed(() => {
    const list = props.marketConfig.trending || [];
    return list.map(sym => sym.replace('.JK', ''));
});

</script>

<template>
  <Head>
    <title>News | Avenir</title>
    <meta name="description" content="Avenir - Platform riset dan direktori pasar modal Indonesia yang komprehensif." />
    <!-- ...other metas... -->
  </Head>

  <AppLayout>
    <div class="bg-[#090b0a] min-h-screen text-slate-200 font-sans relative overflow-x-hidden pt-8 pb-20">
      
      <!-- Container matching Katalog -->
      <div class="max-w-[1200px] mx-auto px-6 relative z-10">
        
      
        <!-- Header Section -->
        <div class="mb-10">
          <h1 class="text-4xl md:text-[42px] font-bold text-white mb-2 leading-tight">Market News</h1>
          <p class="text-slate-400 text-[15px] mb-8">Update pasar harian tepercaya untuk keputusan investasi yang lebih cerdas.</p>
        </div>

        <!-- Main Layout Split -->
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-8">
          
          <!-- ==================== LEFT COLUMN ==================== -->
          <div class="main-column min-w-0">
            
            <!-- Berita Utama -->
            <div class="mb-12">
              <h3 class="text-white font-bold text-[17px] mb-5">Berita Utama</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <component :is="(!news.author && news.source_url) ? 'a' : Link" v-for="news in featuredNews" :key="news.id" :href="(!news.author && news.source_url) ? news.source_url : ('/news/' + news.slug)" :target="(!news.author && news.source_url) ? '_blank' : null" class="group flex flex-col bg-[#111413] border border-white/5 rounded-[14px] overflow-hidden hover:border-emerald-500/30 transition-all duration-300 shadow-sm hover:shadow-[0_8px_25px_rgba(0,0,0,0.5)]">
                   <div class="aspect-[16/10] relative overflow-hidden bg-slate-800">
                     <img v-if="news.cover_image" :src="news.cover_image" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                     <div class="absolute inset-0 bg-gradient-to-t from-[#111413] via-[#111413]/20 to-transparent opacity-80"></div>
                     <div class="absolute bottom-3 left-3 bg-emerald-500 text-white text-[9px] font-bold px-2 py-0.5 rounded shadow-sm tracking-wider">FEATURED</div>
                     <div v-if="news.is_paid" class="absolute top-3 right-3 bg-[#090b0a]/80 backdrop-blur-md text-amber-400 px-2 py-1 flex items-center gap-1 text-[10px] font-bold rounded shadow-sm border border-amber-500/20" title="Premium News">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        PREMIUM
                     </div>
                     <div v-else class="absolute top-3 right-3 bg-[#090b0a]/80 backdrop-blur-md text-emerald-400 px-2 py-1 flex items-center gap-1 text-[10px] font-bold rounded shadow-sm border border-emerald-500/20" title="Berita Gratis">
                        GRATIS
                     </div>
                   </div>
                   <div class="p-5 flex flex-col flex-1">
                     <h4 class="text-slate-100 font-semibold text-[15px] mb-2 group-hover:text-emerald-400 transition-colors line-clamp-2 leading-[1.4]">{{ news.title }}</h4>
                     <p class="text-slate-400 text-[13px] mb-4 line-clamp-3 leading-relaxed flex-1">{{ news.excerpt }}</p>
                     
                     <div class="flex items-center gap-2 text-[11px] text-slate-500 mb-4">
                        <div v-if="news.author" class="flex items-center gap-1.5">
                            <div class="w-4 h-4 rounded-full overflow-hidden bg-slate-800 border border-white/10 shrink-0 flex items-center justify-center">
                                <img v-if="news.author.profile_photo_url" :src="news.author.profile_photo_url" loading="lazy" alt="Author" class="w-full h-full object-cover">
                                <span v-else class="text-[8px]">🧑‍💼</span>
                            </div>
                            <span class="text-slate-300 font-medium">{{ news.author.name }}</span>
                        </div>
                        <span v-if="news.source" class="bg-blue-500/10 border border-blue-500/20 text-blue-400 px-1.5 py-0.5 rounded font-medium">{{ news.source }}</span>
                        <span v-else-if="!news.author" class="bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 px-1.5 py-0.5 rounded font-medium">Avenir Research</span>
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
                </component>
                
                <div v-if="featuredNews.length === 0" class="col-span-3 text-center py-10 border border-dashed border-white/10 rounded-xl">
                  <p class="text-slate-500 text-sm">Belum ada berita utama</p>
                </div>
              </div>
            </div>

            <!-- Berita Terbaru -->
            <div>
              <div class="bg-[#111413] border border-white/5 rounded-xl p-4 mb-8">
                <div class="flex flex-col md:flex-row gap-4 items-end">
                  <div class="relative flex-1 w-full">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" v-model="searchQuery" placeholder="Cari berita, emiten, topik..." class="w-full bg-[#090b0a] border border-white/10 rounded-lg pl-10 pr-4 py-2 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 h-[36px] transition-all">
                  </div>
                  
                  <div class="flex flex-wrap gap-4 w-full md:w-auto">
                    <div class="filter-group flex-1 md:flex-none min-w-[140px]">
                      <label class="text-[10.5px] text-slate-500 font-semibold mb-1.5 block">Kategori</label>
                      <Multiselect
                        v-model="selectedCategories"
                        mode="tags"
                        :options="uniqueCategories"
                        :searchable="true"
                        placeholder="Semua"
                      />
                    </div>
                    <div class="filter-group flex-1 md:flex-none min-w-[140px]">
                      <label class="text-[10.5px] text-slate-500 font-semibold mb-1.5 block">Sumber Media</label>
                      <Multiselect
                        v-model="selectedSources"
                        mode="tags"
                        :options="uniqueSources"
                        :searchable="true"
                        placeholder="Semua"
                      />
                    </div>
                    <div class="filter-group flex-1 md:flex-none min-w-[120px]">
                      <label class="text-[10.5px] text-slate-500 font-semibold mb-1.5 block">Tahun</label>
                      <Multiselect
                        v-model="selectedYear"
                        :options="uniqueYears"
                        :searchable="true"
                        :can-clear="false"
                      />
                    </div>
                    
                    <button class="flex items-center gap-2 text-xs text-slate-400 bg-transparent border border-white/10 px-4 h-[36px] rounded-lg hover:bg-white/5 hover:text-white transition-colors ml-auto md:ml-0" @click="resetFilters">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                      Reset
                    </button>
                  </div>
                </div>
              </div>
              
              <div class="flex justify-between items-center border-b border-white/10 pb-4 mb-2">
                 <h3 class="text-white font-bold text-[17px]">Berita Terbaru</h3>
                 <div class="flex items-center gap-2 text-xs text-slate-400">
                   <span>Sort by:</span>
                   <select v-model="sortOrder" class="bg-transparent border-none text-white focus:outline-none cursor-pointer font-medium p-0">
                      <option value="Terbaru">Terbaru</option>
                      <option value="Terpopuler">Terpopuler</option>
                   </select>
                 </div>
              </div>

              <div class="flex flex-col">
                <component :is="(!news.author && news.source_url) ? 'a' : Link" v-for="(news, idx) in recentNews" :key="news.id" :href="(!news.author && news.source_url) ? news.source_url : ('/news/' + news.slug)" :target="(!news.author && news.source_url) ? '_blank' : null" class="group flex items-center gap-4 py-4 lg:py-5 border-b border-white/5 hover:bg-white/[0.02] px-2 -mx-2 rounded transition-colors">
                   
                   <!-- <div class="w-14 lg:w-16 flex-shrink-0 text-center">
                     <div class="text-[13px] text-slate-300 font-medium">{{ news.published_time || '09:28' }}</div>
                     <div class="text-[10px] text-slate-500 mt-0.5">WIB</div>
                   </div> -->
                   
                   <div class="w-24 lg:w-28 flex-shrink-0">
                      <img v-if="news.cover_image" :src="news.cover_image" alt="Cover" class="w-full h-16 object-cover rounded-lg border border-white/5" />
                      <div v-else class="w-full h-16 bg-[#1a1f1c] border border-white/5 rounded-lg flex items-center justify-center">
                        <span class="text-[9px] font-bold tracking-wider uppercase text-emerald-400 text-center px-1">{{ news.category || 'MARKET' }}</span>
                      </div>
                   </div>
                   
                   <div class="flex-1 pr-4">
                      <h4 class="text-[14px] lg:text-[15px] text-slate-200 font-medium group-hover:text-emerald-400 transition-colors leading-snug line-clamp-2 mb-1.5">{{ news.title }}</h4>
                      <div class="flex items-center flex-wrap gap-2 text-[10px] text-slate-500">
                         <span class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-1.5 py-0.5 rounded">{{ news.category || 'Pasar' }}</span>
                         <span v-if="news.is_paid" class="bg-amber-500/10 border border-amber-500/20 text-amber-400 px-1.5 py-0.5 rounded font-bold flex items-center gap-1" title="Premium News">
                           <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                           PREMIUM
                         </span>
                         <span v-else class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-1.5 py-0.5 rounded font-bold" title="Berita Gratis">
                           GRATIS
                         </span>
                         <span v-if="news.source" class="bg-blue-500/10 border border-blue-500/20 text-blue-400 px-1.5 py-0.5 rounded font-medium">{{ news.source }}</span>
                         <span v-else class="bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 px-1.5 py-0.5 rounded font-medium">Avenir Research</span>
                         <span>&bull;</span>
                         <span>{{ news.published_at ? news.published_at + ' ' + (news.published_time || '') + ' WIB' : 'Baru saja' }}</span>
                      </div>
                   </div>
                   
                   <button class="text-slate-500 hover:text-emerald-400 transition-colors" @click.prevent>
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                   </button>
                </component>
                
                <div v-if="recentNews.length === 0" class="text-center py-10 border border-dashed border-white/10 rounded-xl mt-4">
                  <p class="text-slate-500 text-sm">Belum ada berita terbaru</p>
                </div>
              </div>

              <button v-if="hasMoreRecentNews" @click="loadMore" class="w-full mt-6 py-3 bg-[#111413] border border-white/5 text-slate-300 text-[13px] font-semibold rounded-lg hover:bg-white/10 hover:text-white transition-all flex items-center justify-center gap-2 group">
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
                 <Link href="/market" class="text-[11px] text-emerald-400 cursor-pointer font-medium hover:text-emerald-300">Lihat Semua</Link>
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
                  <component :is="(!item.author && item.source_url) ? 'a' : Link" v-for="(item, idx) in mostRead" :key="item.id || idx" :href="(!item.author && item.source_url) ? item.source_url : (item.slug ? `/news/${item.slug}` : '#')" :target="(!item.author && item.source_url) ? '_blank' : null" class="group flex gap-3 items-start">
                   <span class="text-emerald-400 font-bold text-[13px] mt-0.5 w-3 text-right">{{ idx + 1 }}</span>
                   <p class="text-slate-300 text-[13px] leading-relaxed group-hover:text-emerald-400 transition-colors flex-1">{{ item.title }}</p>
                 </component>
               </div>
             </div>

             <!-- Watchlist News -->
             <div class="bg-[#111413] border border-white/5 rounded-[14px] p-5">
               <div class="flex justify-between items-center mb-5">
                 <h3 class="text-white font-bold text-[15px]">Watchlist News</h3>
                 <Link href="/market" class="text-[11px] text-emerald-400 cursor-pointer font-medium hover:text-emerald-300">Lihat Semua</Link>
               </div>
               <div class="flex flex-col gap-4">
                  <component :is="(!item.author && item.source_url) ? 'a' : Link" v-for="item in watchlistNews" :key="item.ticker" :href="(!item.author && item.source_url) ? item.source_url : (item.slug ? `/news/${item.slug}` : '#')" :target="(!item.author && item.source_url) ? '_blank' : null" class="group flex items-start gap-3">
                   <span class="text-emerald-400 font-bold text-[11px] w-9 flex-shrink-0 mt-1">{{ item.ticker }}</span>
                   <p class="text-slate-300 text-[13px] leading-relaxed group-hover:text-emerald-400 transition-colors line-clamp-2 flex-1">{{ item.title }}</p>
                   <span class="text-[10px] text-slate-500 flex-shrink-0 mt-1 w-8 text-right">{{ item.time }}</span>
                 </component>
               </div>
             </div>

             <!-- Trending Tickers -->
             <div class="bg-[#111413] border border-white/5 rounded-[14px] p-5">
               <div class="flex justify-between items-center mb-5">
                 <h3 class="text-white font-bold text-[15px]">Trending Tickers</h3>
                 <span class="text-[10px] text-slate-500">24 jam terakhir</span>
               </div>
               <div class="flex flex-wrap gap-2">
                 <Link v-for="ticker in trendingTickers" :key="ticker" :href="`/market?search=${ticker}`" class="px-3.5 py-1.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[11px] font-bold rounded-lg hover:bg-emerald-500 hover:text-white hover:border-emerald-500 transition-all">
                   {{ ticker }}
                 </Link>
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
.filter-group {
  --ms-bg: #090b0a;
  --ms-border-color: rgba(255, 255, 255, 0.1);
  --ms-dropdown-bg: #111413;
  --ms-dropdown-border-color: rgba(255, 255, 255, 0.1);
  --ms-option-color-pointed: #fff;
  --ms-option-bg-pointed: rgba(16, 185, 129, 0.1);
  --ms-option-color-selected: #fff;
  --ms-option-bg-selected: #10B981;
  --ms-option-color-selected-pointed: #fff;
  --ms-option-bg-selected-pointed: #059669;
  --ms-font-size: 12px;
  --ms-line-height: 1.2;
  --ms-radius: 6px;
  --ms-ring-color: rgba(16, 185, 129, 0.5);
  --ms-placeholder-color: #64748b;
  --ms-caret-color: #94a3b8;
  --ms-clear-color: #94a3b8;
  --ms-tag-bg: rgba(16, 185, 129, 0.2);
  --ms-tag-color: #10B981;
}
.filter-group .multiselect {
  min-height: 36px;
  color: #cbd5e1;
}
.filter-group .multiselect-dropdown {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
}
</style>
