<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { authStore } from '@/Stores/authStore';
import { ref, computed } from 'vue';

const props = defineProps({
  risetUnggulan: {
    type: Array,
    default: () => []
  },
  insightTerbaru: {
    type: Array,
    default: () => []
  },
  headlinesPasar: {
    type: Array,
    default: () => []
  }
});

// Mock/Default fallback data for the 3 dashboard columns on the landing page
const defaultRiset = [
  {
    ticker: 'BBCA IJ',
    name: 'Bank Central Asia Tbk',
    sector: 'Perbankan',
    targetPrice: 'Rp 11,600',
    upside: '+18.7%',
    rating: 'BUY',
    date: '14 Mei 2025',
    slug: 'bbca'
  },
  {
    ticker: 'TLKM IJ',
    name: 'Telkom Indonesia Tbk',
    sector: 'Telekomunikasi',
    targetPrice: 'Rp 4,250',
    upside: '+16.2%',
    rating: 'BUY',
    date: '12 Mei 2025',
    slug: 'tlkm'
  },
  {
    ticker: 'ASII IJ',
    name: 'Astra International Tbk',
    sector: 'Otomotif',
    targetPrice: 'Rp 5,850',
    upside: '+8.3%',
    rating: 'HOLD',
    date: '9 Mei 2025',
    slug: 'asii'
  }
];

const defaultInsight = [
  {
    title: 'Outlook IHSG: Menguji Resistance 7,400, Apa Sinyalnya?',
    date: '15 Mei 2025',
    gradient: 'from-blue-950/60 to-emerald-950/60',
    image: '/images/articles/outlook_ihsg.png'
  },
  {
    title: 'BI Tahan Suku Bunga, Stabilitas Rupiah Jadi Kunci',
    date: '14 Mei 2025',
    gradient: 'from-cyan-950/60 to-blue-950/60',
    image: '/images/articles/rupiah_chart.png'
  },
  {
    title: 'Banking Sector: Kredit Tumbuh, NIM Masih Terjaga',
    date: '13 Mei 2025',
    gradient: 'from-teal-950/60 to-emerald-950/60',
    image: '/images/articles/banking_sector.png'
  }
];

const defaultHeadlines = [
  { ticker: 'BBRI', text: 'BBRI Catat Laba Q1 2025 Naik 12% YoY', time: '09:12' },
  { ticker: 'BMRI', text: 'BMRI Salurkan Kredit Rp 620 T di Q1 2025', time: '08:48' },
  { ticker: 'TLKM', text: 'TLKM Siapkan Spin-off Bisnis Data Center', time: '08:31' },
  { ticker: 'MDKA', text: 'MDKA Akuisisi Tambang Emas Baru', time: '08:05' },
  { ticker: 'IHSG', text: 'IHSG Menguat 1.35% ke Level 7,275', time: '07:45' }
];

const risetUnggulan = computed(() => {
  if (!props.risetUnggulan || props.risetUnggulan.length === 0) {
    return defaultRiset;
  }
  const merged = [...props.risetUnggulan];
  while (merged.length < 3) {
    merged.push(defaultRiset[merged.length % defaultRiset.length]);
  }
  return merged.slice(0, 3);
});

const insightTerbaru = computed(() => {
  if (!props.insightTerbaru || props.insightTerbaru.length === 0) {
    return defaultInsight;
  }
  const merged = [...props.insightTerbaru];
  while (merged.length < 3) {
    merged.push(defaultInsight[merged.length % defaultInsight.length]);
  }
  return merged.slice(0, 3);
});

const headlinesPasar = computed(() => {
  if (!props.headlinesPasar || props.headlinesPasar.length === 0) {
    return defaultHeadlines;
  }
  const merged = [...props.headlinesPasar];
  while (merged.length < 5) {
    merged.push(defaultHeadlines[merged.length % defaultHeadlines.length]);
  }
  return merged.slice(0, 5);
});
</script>

<template>
    <Head>
        <title>Home | AVENIR</title>
        <meta name="description" content="AVENIR - Platform riset dan direktori pasar modal Indonesia yang komprehensif." />
        <meta property="og:title" content="Home | AVENIR" />
        <meta property="og:description" content="AVENIR - Platform riset dan direktori pasar modal Indonesia yang komprehensif." />
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

  <Head title="Riset Saham Kelas Institusi" />
  <AppLayout>
    <div class="landing-dark-wrapper">
      
      <!-- HERO SECTION -->
      <section class="hero-section">
        <!-- Glow Backdrops -->
        <div class="radial-glow glow-top-right"></div>
        <div class="radial-glow glow-bottom-left"></div>

        <div class="hero-container">
          <!-- Left Column: Copy & Actions -->
          <div class="hero-content">
            <h1 class="hero-title">
              Riset Saham Kelas <br />
              <span class="text-green-glow">Institusi</span> untuk <br />
              <span class="text-green-glow">Investor Cerdas</span>
            </h1>
            
            <p class="hero-lead">
              Akses riset ekuitas mendalam dengan data audited, analisis bersertifikasi, dan insight pasar yang relevan untuk keputusan investasi yang lebih cerdas.
            </p>
            
            <div class="hero-cta-group">
              <button class="btn-primary-green" @click="authStore.open('register')">
                Berlangganan Sekarang
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="inline-block ml-1">
                  <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
              </button>
              <button class="btn-secondary-outline" @click="router.visit('/katalog')">
                <span class="play-icon-circle">
                  <svg width="8" height="8" viewBox="0 0 24 24" fill="currentColor" class="play-icon-svg">
                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                  </svg>
                </span>
                Coba Demo Gratis
              </button>
            </div>
          </div>

          <!-- Right Column: Premium Mockup Graphic -->
          <div class="hero-graphics">
            <div class="laptop-glow"></div>
            <div class="mockup-container">
              <img src="/images/hero_laptop.png" alt="Avenir Terminal Mockup" class="laptop-img" />
              <!-- Background stock chart animation decorative element -->
              <div class="chart-glow-vector">
                <svg viewBox="0 0 400 200" fill="none" class="chart-svg">
                  <path d="M10,180 L80,140 L150,160 L220,90 L290,110 L360,40" stroke="#22c55e" stroke-width="3" opacity="0.3" stroke-linecap="round"/>
                  <path d="M10,180 L80,140 L150,160 L220,90 L290,110 L360,40 L360,200 L10,200 Z" fill="url(#chart-grad)" opacity="0.05" />
                  <defs>
                    <linearGradient id="chart-grad" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stop-color="#22c55e" />
                      <stop offset="100%" stop-color="#22c55e" stop-opacity="0" />
                    </linearGradient>
                  </defs>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </section>

   

      <!-- FEATURE GRID (6 CARDS) -->
      <section class="features-grid-section">
        <div class="section-container">
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
            
            <!-- Card 1: Riset Premium -->
            <Link href="/katalog" class="feature-card-glass text-left">
              <div class="feature-icon-wrapper">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-500">
                  <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
              </div>
              <div class="feature-text-wrapper">
                <h3 class="feature-title">Riset Premium</h3>
                <p class="feature-desc">Laporan mendalam berbasis analisis fundamental.</p>
              </div>
            </Link>
            
            <!-- Card 2: Disclosure Radar -->
            <Link href="/disclosure-radar" class="feature-card-glass text-left">
              <div class="feature-icon-wrapper">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-500">
                  <circle cx="12" cy="12" r="10"></circle>
                  <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                  <path d="M2 12h20"></path>
                </svg>
              </div>
              <div class="feature-text-wrapper">
                <h3 class="feature-title">Disclosure Radar</h3>
                <p class="feature-desc">Pemantauan keterbukaan informasi emiten.</p>
              </div>
            </Link>
            
            <!-- Card 3: KI Brief -->
            <Link href="/ki-brief" class="feature-card-glass text-left">
              <div class="feature-icon-wrapper">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-500">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                  <polyline points="14 2 14 8 20 8"></polyline>
                  <line x1="16" y1="13" x2="8" y2="13"></line>
                  <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
              </div>
              <div class="feature-text-wrapper">
                <h3 class="feature-title">KI Brief</h3>
                <p class="feature-desc">Ringkasan kebijakan &amp; isu ekonomi terkini.</p>
              </div>
            </Link>
            
            <!-- Card 4: Emiten Hub -->
            <Link href="/emiten" class="feature-card-glass text-left">
              <div class="feature-icon-wrapper">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-500">
                  <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                  <line x1="9" y1="22" x2="9" y2="16"></line>
                  <line x1="8" y1="6" x2="16" y2="6"></line>
                  <line x1="16" y1="22" x2="16" y2="16"></line>
                  <line x1="12" y1="22" x2="12" y2="16"></line>
                </svg>
              </div>
              <div class="feature-text-wrapper">
                <h3 class="feature-title">Emiten Hub</h3>
                <p class="feature-desc">Profil emiten, kinerja, dan key financials.</p>
              </div>
            </Link>
            
            <!-- Card 5: Market News -->
            <Link href="/news" class="feature-card-glass text-left">
              <div class="feature-icon-wrapper">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-500">
                  <path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v11"></path>
                  <rect x="14" y="14" width="7" height="6" rx="1"></rect>
                  <line x1="7" y1="8" x2="13" y2="8"></line>
                  <line x1="7" y1="12" x2="11" y2="12"></line>
                </svg>
              </div>
              <div class="feature-text-wrapper">
                <h3 class="feature-title">Market News</h3>
                <p class="feature-desc">Berita pasar &amp; update terpercaya setiap hari.</p>
              </div>
            </Link>
            
            <!-- Card 6: Katalog Riset -->
            <Link href="/katalog" class="feature-card-glass text-left">
              <div class="feature-icon-wrapper">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-500">
                  <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                  <path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5v-15z"></path>
                </svg>
              </div>
              <div class="feature-text-wrapper">
                <h3 class="feature-title">Katalog Riset</h3>
                <p class="feature-desc">Akses lengkap seluruh laporan riset kami.</p>
              </div>
            </Link>

          </div>
        </div>
      </section>

      <!-- 3-COLUMN DASHBOARD SECTION -->
      <section class="dashboard-columns-section">
        <div class="section-container">
          <div class="grid grid-cols-1 lg:grid-cols-7 gap-8">
            
            <!-- Column 1: Riset Unggulan -->
            <div class="dashboard-col text-left lg:col-span-3">
              <div class="col-header flex justify-between items-center mb-6">
                <h2 class="col-title text-xl font-extrabold font-heading text-white">Research Desk</h2>
                <Link href="/katalog" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 flex items-center gap-1">
                  Lihat Semua
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </Link>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div v-for="(riset, idx) in risetUnggulan" :key="idx" class="dashboard-card-glass flex flex-col justify-between p-4 rounded-xl border border-slate-900 bg-slate-950/20 hover:border-emerald-500/30 transition-all duration-300">
                  <div class="flex justify-between items-start mb-1.5">
                    <div>
                      <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-white tracking-wide font-mono">{{ riset.ticker }}</span>
                        <span :class="riset.rating === 'BUY' ? 'bg-emerald-950/50 text-emerald-400 border border-emerald-500/30' : 'bg-yellow-950/50 text-yellow-500 border border-yellow-500/30'" class="text-[10px] font-extrabold px-1.5 py-0.5 rounded uppercase tracking-wider">
                          {{ riset.rating }}
                        </span>
                      </div>
                      <h4 class="text-[13px] font-extrabold text-slate-200 mt-1 leading-snug">{{ riset.name }}</h4>
                      <p class="text-[11px] text-slate-400 mt-0.5 font-semibold">Sektor: {{ riset.sector }}</p>
                    </div>
                  </div>
                  
                  <div class="flex items-center gap-6 mt-3 pt-2.5 border-t border-slate-900/60">
                    <div>
                      <div class="text-[11px] text-slate-500 font-medium">Target Price</div>
                      <div class="text-sm font-bold text-slate-200 mt-0.5 font-mono">{{ riset.targetPrice }}</div>
                    </div>
                    <div>
                      <div class="text-[11px] text-slate-500 font-medium">Upside</div>
                      <div class="text-sm font-bold text-emerald-400 mt-0.5 font-mono">{{ riset.upside }}</div>
                    </div>
                  </div>
                  
                  <div class="flex justify-between items-center mt-3 pt-2">
                    <span class="text-[11px] text-slate-500 font-mono">{{ riset.date }}</span>
                    <Link :href="`/katalog/${riset.slug}`" class="text-[11px] font-bold text-emerald-400 hover:underline flex items-center gap-1">
                      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="inline-block"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                      Premium Report
                    </Link>
                  </div>
                </div>
              </div>
              
              <!-- Slider Indicator Dots -->
              <div class="flex justify-center gap-1.5 mt-6">
                <span class="w-5 h-1.5 rounded-full bg-emerald-500"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-slate-800"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-slate-800"></span>
              </div>
            </div>
            
            <!-- Column 2: Insight Terbaru -->
            <div class="dashboard-col text-left lg:col-span-2">
              <div class="col-header mb-6">
                <h2 class="col-title text-xl font-extrabold font-heading text-white">Insight Terbaru</h2>
              </div>
              
              <div class="space-y-4">
                <div v-for="(insight, idx) in insightTerbaru" :key="idx" class="dashboard-card-glass flex gap-3.5 p-3 rounded-xl border border-slate-900 bg-slate-950/20 hover:border-emerald-500/30 transition-all duration-300">
                  <!-- Custom Thumbnail -->
                  <div class="w-20 h-14 rounded-lg overflow-hidden flex-shrink-0 border border-slate-850 bg-slate-900">
                    <img v-if="insight.image" :src="insight.image" loading="lazy" class="w-full h-full object-cover" :alt="insight.title" />
                    <div v-else class="w-full h-full bg-gradient-to-br flex items-center justify-center" :class="insight.gradient || 'from-blue-950/60 to-emerald-950/60'">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-white/30">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="21" y1="12" x2="3" y2="12"></line>
                      </svg>
                    </div>
                  </div>
                  <div class="flex flex-col justify-between py-0.5">
                    <h3 class="text-xs font-bold text-slate-200 hover:text-emerald-400 transition-colors leading-snug cursor-pointer line-clamp-2">
                      <Link href="/artikel">{{ insight.title }}</Link>
                    </h3>
                    <span class="text-[11px] text-slate-500 font-mono">{{ insight.date }}</span>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Column 3: Headlines Pasar -->
            <div class="dashboard-col text-left lg:col-span-2">
              <div class="col-header mb-6">
                <h2 class="col-title text-xl font-extrabold font-heading text-white">Headlines Pasar</h2>
              </div>
              
              <div class="space-y-2.5">
                <div v-for="(headline, idx) in headlinesPasar" :key="idx" class="flex items-center justify-between p-2.5 rounded-xl border border-slate-900 bg-slate-950/10 hover:bg-slate-950/30 transition-colors">
                  <div class="flex items-center gap-3">
                    <span class="bg-emerald-950/30 text-emerald-400 text-[10px] font-bold px-2 py-0.5 rounded border border-emerald-500/20 font-mono tracking-wider">
                      {{ headline.ticker }}
                    </span>
                    <span class="text-xs font-semibold text-slate-300 line-clamp-1 hover:text-emerald-400 cursor-pointer">
                      <Link href="/news">{{ headline.text }}</Link>
                    </span>
                  </div>
                  <span class="text-[11px] font-mono text-slate-500 pl-2">{{ headline.time }}</span>
                </div>
              </div>
            </div>

          </div>
        </div>
      </section>

      <!-- PREMIUM UPGRADE CALL TO ACTION BANNER -->
      <section class="upgrade-banner-section pb-20">
        <div class="section-container">
          <div class="upgrade-banner-card flex flex-col lg:flex-row justify-between items-center p-8 lg:p-10 rounded-2xl border border-emerald-500/30 bg-gradient-to-r from-emerald-950/10 via-slate-950/40 to-slate-950/30 backdrop-blur-md relative overflow-hidden">
            <!-- Glow background overlay inside CTA -->
            <div class="absolute inset-0 bg-radial-gradient from-emerald-500/5 to-transparent pointer-events-none"></div>
            
            <div class="flex items-start gap-4 lg:gap-6 relative z-10 text-left">
              <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center flex-shrink-0 mt-1">
                <!-- Golden Crown Icon -->
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7z"></path>
                  <path d="M3 20h18v2H3z"></path>
                </svg>
              </div>
              <div>
                <h2 class="text-xl lg:text-2xl font-extrabold font-heading text-white tracking-wide">Tingkatkan Keputusan Investasi Anda</h2>
                <p class="text-xs text-slate-400 mt-1 max-w-xl">Bergabunglah dengan ribuan investor &amp; profesional yang mengandalkan riset AVENIR setiap hari.</p>
                
                <!-- Horizontal list with custom green ticks -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2.5 mt-5">
                  <div class="flex items-center gap-2 text-xs text-slate-300">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Akses penuh seluruh riset premium
                  </div>
                  <div class="flex items-center gap-2 text-xs text-slate-300">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Data audited &amp; metodologi terverifikasi
                  </div>
                  <div class="flex items-center gap-2 text-xs text-slate-300">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Update harian &amp; insight eksklusif
                  </div>
                  <div class="flex items-center gap-2 text-xs text-slate-300">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Dukungan analis profesional
                  </div>
                </div>
              </div>
            </div>
            
            <div class="mt-6 lg:mt-0 relative z-10 flex-shrink-0">
              <Link href="/langganan" class="bg-[#22c55e] text-[#090b0a] hover:bg-[#16a34a] flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-sm tracking-wide transition-all duration-300 shadow-lg shadow-emerald-500/10">
                Lihat Paket Langganan
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
              </Link>
            </div>
          </div>
        </div>
      </section>

    </div>
  </AppLayout>
</template>

<style scoped>
/* GENERAL RESET & THEME STYLINGS */
.landing-dark-wrapper {
  background-color: #090b0a;
  color: #d1d5db;
  font-family: 'Roboto', sans-serif;
  min-height: 100vh;
  overflow-x: hidden;
  position: relative;
}

/* Typography styles */
h1, h2, h3, h4 {
  font-family: 'Sora', 'Roboto', sans-serif;
  color: #ffffff;
}

/* Glowing Background Vectors */
.radial-glow {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  filter: blur(140px);
  z-index: 0;
  opacity: 0.7;
}
.glow-top-right {
  top: -100px;
  right: -100px;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(34, 197, 94, 0.15) 0%, rgba(9, 11, 10, 0) 70%);
}
.glow-bottom-left {
  bottom: 10%;
  left: -200px;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(34, 197, 94, 0.08) 0%, rgba(9, 11, 10, 0) 70%);
}

.text-green-glow {
  color: #22c55e;
  text-shadow: 0 0 20px rgba(34, 197, 94, 0.25);
}

.section-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  position: relative;
  z-index: 10;
}

/* HERO SECTION */
.hero-section {
  position: relative;
  padding: 60px 0 30px 0;
}
.hero-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  display: grid;
  grid-template-columns: 1fr;
  gap: 60px;
  align-items: center;
  position: relative;
  z-index: 10;
}
@media (min-width: 900px) {
  .hero-container {
    grid-template-columns: 1.1fr 0.9fr;
  }
}

.laptop-glow {
  position: absolute;
  width: 90%;
  height: 90%;
  background: radial-gradient(circle, rgba(34, 197, 94, 0.3) 0%, rgba(9, 11, 10, 0) 70%);
  filter: blur(80px);
  z-index: 1;
  pointer-events: none;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.hero-title {
  font-size: 38px;
  line-height: 1.15;
  font-weight: 800;
  letter-spacing: -0.02em;
  margin-bottom: 24px;
  text-align: left;
}
@media (min-width: 600px) {
  .hero-title {
    font-size: 50px;
  }
}
@media (min-width: 1024px) {
  .hero-title {
    font-size: 64px;
    line-height: 1.1;
  }
}
.hero-lead {
  font-size: 15px;
  line-height: 1.65;
  color: #9ca3af;
  max-width: 580px;
  margin-bottom: 36px;
  text-align: left;
}
@media (min-width: 1024px) {
  .hero-lead {
    font-size: 17px;
  }
}
.hero-cta-group {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 20px;
}
.btn-primary-green {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 12px 28px;
  border-radius: 50px;
  background-color: #22c55e;
  color: #ffffff;
  font-weight: 700;
  font-size: 14px;
  border: none;
  cursor: pointer;
  box-shadow: 0 0 20px rgba(34, 197, 94, 0.4);
  transition: all 0.2s ease-in-out;
  text-decoration: none;
}
.btn-primary-green:hover {
  background-color: #16a34a;
  transform: translateY(-2px);
  box-shadow: 0 0 30px rgba(34, 197, 94, 0.6);
}
.btn-secondary-outline {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 12px 28px;
  border-radius: 50px;
  background-color: transparent;
  color: #ffffff;
  font-weight: 600;
  font-size: 14px;
  border: 1px solid rgba(255, 255, 255, 0.15);
  cursor: pointer;
  text-decoration: none;
  text-align: center;
  transition: all 0.2s ease-in-out;
}
.btn-secondary-outline:hover {
  border-color: rgba(255, 255, 255, 0.4);
  background-color: rgba(255, 255, 255, 0.05);
}
@media (min-width: 1024px) {
  .btn-primary-green, .btn-secondary-outline {
    padding: 14px 32px;
    font-size: 15px;
  }
}

.play-icon-circle {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 1.5px solid #ffffff;
  margin-right: 8px;
}
.play-icon-svg {
  transform: translateX(1px);
}

/* Laptop mockup styles */
.hero-graphics {
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
}
.mockup-container {
  width: 100%;
  position: relative;
  z-index: 10;
}
.laptop-img {
  width: 100%;
  height: auto;
  display: block;
  object-fit: contain;
  filter: drop-shadow(0 20px 50px rgba(0,0,0,0.6));
}
.chart-glow-vector {
  position: absolute;
  bottom: -15px;
  right: -30px;
  width: 80%;
  z-index: -1;
  pointer-events: none;
}
.chart-svg {
  width: 100%;
  height: auto;
}

/* STATS ROW BANNER */
.stats-row-section {
  padding: 0 0 40px 0;
}
.stats-banner-card {
  background-color: rgba(17, 20, 19, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 16px;
  padding: 24px 32px;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
}
.stats-grid-row {
  display: grid;
  grid-template-columns: 1fr;
  gap: 28px;
}
@media (min-width: 640px) {
  .stats-grid-row {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (min-width: 1024px) {
  .stats-grid-row {
    grid-template-columns: repeat(4, 1fr);
  }
}
.stat-row-item {
  display: flex;
  align-items: center;
  gap: 16px;
}
.stat-icon-box {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: rgba(34, 197, 94, 0.08);
  border: 1px solid rgba(34, 197, 94, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #22c55e;
  flex-shrink: 0;
  box-shadow: 0 0 10px rgba(34, 197, 94, 0.15);
}
.stat-text-box {
  text-align: left;
}
.stat-num-val {
  font-size: 20px;
  font-weight: 700;
  color: #ffffff;
  font-family: 'Sora', sans-serif;
  letter-spacing: -0.01em;
}
.stat-num-lbl {
  font-size: 12px;
  color: #9ca3af;
  margin-top: 2px;
  white-space: nowrap;
}

/* FEATURE GRID SECTION */
.features-grid-section {
  padding: 20px 0;
}
.feature-card-glass {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  background-color: rgba(17, 20, 19, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.03);
  border-radius: 12px;
  backdrop-filter: blur(6px);
  transition: all 0.3s ease;
  text-decoration: none;
}
.feature-card-glass:hover {
  border-color: rgba(34, 197, 94, 0.25);
  background-color: rgba(34, 197, 94, 0.03);
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4), 0 0 15px rgba(34, 197, 94, 0.04);
}
.feature-icon-wrapper {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: rgba(34, 197, 94, 0.06);
  border: 1px solid rgba(34, 197, 94, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all 0.3s ease;
}
.feature-card-glass:hover .feature-icon-wrapper {
  background: rgba(34, 197, 94, 0.15);
  border-color: rgba(34, 197, 94, 0.35);
  transform: scale(1.05);
}
.feature-text-wrapper {
  display: flex;
  flex-direction: column;
  text-align: left;
}
.feature-title {
  font-size: 14.5px;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 3px;
  letter-spacing: -0.01em;
  line-height: 1.2;
}
.feature-desc {
  font-size: 11.5px;
  line-height: 1.4;
  color: #9ca3af;
}
/* 3-COLUMN DASHBOARD SECTION */
.dashboard-columns-section {
  padding: 40px 0 60px 0;
}
.dashboard-col {
  display: flex;
  flex-direction: column;
}
.col-title {
  letter-spacing: -0.015em;
}
.dashboard-card-glass {
  background-color: rgba(17, 20, 19, 0.2);
  border-color: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(4px);
}
.dashboard-card-glass:hover {
  background-color: rgba(17, 20, 19, 0.4);
  box-shadow: 0 10px 25px rgba(0,0,0,0.4);
}

/* UPGRADE BANNER SECTION */
.upgrade-banner-section {
  position: relative;
  z-index: 10;
}
.upgrade-banner-card {
  background-color: rgba(17, 20, 19, 0.3);
  backdrop-filter: blur(8px);
}
</style>
