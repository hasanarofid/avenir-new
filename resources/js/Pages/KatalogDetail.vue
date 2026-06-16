<script setup>
import { Head, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Paywall from '@/Components/Paywall.vue';
import { authStore } from '@/Stores/authStore';
import { computed, ref, onMounted } from 'vue';

const props = defineProps({
    research: {
        type: Object,
        required: true,
    }
});

const page = usePage();
const isLoggedIn = computed(() => !!page.props.auth?.user);

// Terkunci jika is_paid dan tidak unlocked
const isLocked = computed(() => props.research.is_paid && !props.research.is_unlocked);

// Konten mengandung HTML kustom dari file website jika ada class art-page
const hasCustomHtml = computed(() => {
    return props.research.content && props.research.content.includes('art-page');
});

// Deteksi apakah konten bawaan (migrasi) memiliki .hero sendiri
const hasCustomHero = computed(() => {
    if (!props.research.content) return false;
    return /class=['"][a-zA-Z0-9_-]*hero['"]/i.test(props.research.content);
});

const formattedPrice = computed(() => {
    if (!props.research.target_price) return null;
    if (isNaN(props.research.target_price)) return props.research.target_price;
    return Number(props.research.target_price).toLocaleString('id-ID');
});
</script>

<template>
    <Head>
        <title>KatalogDetail | AVENIR</title>
        <meta name="description" content="AVENIR - Platform riset dan direktori pasar modal Indonesia yang komprehensif." />
        <meta property="og:title" content="KatalogDetail | AVENIR" />
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

  <Head :title="`${research.ticker ? research.ticker + ' — ' : ''}${research.title} | Avenir Research`" />

  <AppLayout>
    <div :id="'page-' + research.slug" class="kdp-page" :class="{ 'has-custom-content': hasCustomHtml || hasCustomHero }">
      <!-- Background glows -->
      <div class="kdp-glow kdp-glow-tr hidden md:block"></div>
      <div class="kdp-glow kdp-glow-bl hidden md:block"></div>

      <!-- ─── MOBILE-FIRST HERO BANNER ─────────────────────────────────── -->
      <div v-if="!hasCustomHero" class="kdp-hero-modern">
        <div class="kdp-hero-bg">
          <img v-if="research.image" :src="research.image" :alt="research.title" class="w-full h-full object-cover" />
          <div v-else class="w-full h-full bg-slate-800"></div>
          <div class="kdp-hero-overlay"></div>
        </div>

        <!-- Top Nav Icons -->
        <div class="kdp-top-nav">
          <Link href="/katalog" class="kdp-icon-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
          </Link>
          <div class="flex gap-3">
            <button class="kdp-icon-btn">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
            </button>
            <button class="kdp-icon-btn">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            </button>
          </div>
        </div>

        <div class="kdp-hero-content">
          <div class="kdp-hero-info">
            <!-- Premium Badge -->
            <div v-if="research.is_premium" class="kdp-badge-premium-gold mb-4">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              PREMIUM
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="ml-1 opacity-70"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>

            <!-- Ticker & Company Name -->
            <div class="kdp-company-row">
              <span class="kdp-ticker" v-if="research.ticker">{{ research.ticker }}</span>
              <span class="kdp-company-name">{{ research.ticker ? 'Bank Central Asia Tbk' : '' }}</span>
            </div>

            <!-- Title -->
            <h1 class="kdp-h1-modern">{{ research.title }}</h1>

            <!-- Meta Row -->
            <div class="kdp-meta-modern">
              <span>{{ research.date }}</span>
              <span class="dot">•</span>
              <span class="flex items-center gap-1" v-if="research.is_premium">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Premium Report
              </span>
              <span class="flex items-center gap-1" v-else>
                Free Report
              </span>
            </div>

            <!-- Author Info -->
            <div class="kdp-author-row">
              <div class="kdp-author-avatar">
                <img v-if="research.author?.avatar" :src="research.author.avatar" alt="Author">
                <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              </div>
              <div class="kdp-author-details">
                <div class="flex items-center gap-2">
                  <span class="kdp-author-name">{{ research.author ? research.author.name : 'Tim Avenir Research' }}</span>
                  <span class="kdp-author-badge">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    Avenir Research
                  </span>
                </div>
                <div class="kdp-author-role">Senior Equity Analyst</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Bar -->
      <div v-if="!hasCustomHero" class="kdp-stats-bar-wrapper">
        <div class="kdp-container">
          <div class="kdp-stats-bar">
            <div class="kdp-stat-item">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              <span><strong>61</strong> Comments</span>
            </div>
            <div class="kdp-stat-sep"></div>
            <div class="kdp-stat-item">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <span><strong>128</strong> Views</span>
            </div>
            <div class="kdp-stat-sep"></div>
            <div class="kdp-stat-item">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
              <span>Bookmark</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ─── MAIN CONTENT AREA (2 COLUMNS) ──────────────────────────────── -->
      <div class="kdp-content-area" :class="{ 'is-custom': hasCustomHtml || hasCustomHero }">
        <div :class="{ 'kdp-container': !hasCustomHtml && !hasCustomHero }">
          
          <!-- Legacy Custom HTML Content -->
          <div v-if="hasCustomHtml || hasCustomHero" class="guest-lock-wrap" :class="{ 'is-guest': isLocked }">
            
            <!-- Custom Breadcrumb for Legacy Pages -->
            <div class="kdp-custom-breadcrumb">
              <a href="/katalog" class="kdp-bc-link-custom">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                Katalog Riset
              </a>
            </div>

            <div class="guest-lock-content" v-html="research.content" />
            <Paywall v-if="isLocked" />
          </div>

          <!-- New PDF-Centric Layout -->
          <div v-else class="kdp-split-layout">
            
            <!-- Left Column: Summary -->
            <div class="kdp-summary-col">
              <div class="summary-card">
                <h2 class="summary-title">Ringkasan Tesis</h2>
                
                <div class="guest-lock-wrap" :class="{ 'is-guest': isLocked }">
                  <div class="guest-lock-content db-content">
                    <div v-if="research.content" v-html="research.content"></div>
                    <div v-else-if="research.subtitle" v-html="research.subtitle"></div>
                    <div v-else class="empty-summary text-slate-400">
                      Ringkasan detail belum tersedia untuk laporan ini. Silakan unduh dokumen PDF untuk membaca analisis selengkapnya.
                    </div>
                  </div>
                  <!-- Paywall blocks the summary if locked -->
                  <Paywall v-if="isLocked" />
                </div>
              </div>

              <!-- Action Card (Mobile Inline) -->
              <div class="action-card-mobile block md:hidden mt-6 mb-8">
                <!-- Metrics Grid -->
                <div class="ac-metrics-grid">
                  <div class="ac-metric-box">
                    <div class="ac-metric-lbl">Target Price</div>
                    <div class="ac-metric-val">Rp {{ formattedPrice || '-' }}</div>
                  </div>
                  <div class="ac-metric-box text-center">
                    <div class="ac-metric-lbl">Upside</div>
                    <div class="ac-metric-val" :class="research.upside && research.upside.includes('-') ? 'text-red-400' : 'text-emerald-400'">{{ research.upside || '-' }}</div>
                  </div>
                  <div class="ac-metric-box items-end">
                    <div class="ac-metric-lbl">Rekomendasi</div>
                    <div class="ac-metric-badge" :class="research.recommendation === 'BUY' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : (research.recommendation === 'SELL' ? 'bg-red-500/10 text-red-400 border-red-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/20')">{{ research.recommendation || 'BUY' }}</div>
                  </div>
                </div>

                <div class="ac-info-grid mt-4">
                  <div class="ac-info-item">
                    <div class="ac-info-lbl">Sektor</div>
                    <div class="ac-info-val text-white">{{ research.sector || 'Perbankan' }}</div>
                  </div>
                  <div class="ac-info-item">
                    <div class="ac-info-lbl">Tipe Laporan</div>
                    <div class="ac-info-val text-white">Equity Research</div>
                  </div>
                  <div class="ac-info-item">
                    <div class="ac-info-lbl">Frekuensi Update</div>
                    <div class="ac-info-val text-white">Premium Report</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Column: Action Card (Desktop Sidebar) -->
            <div class="kdp-action-col hidden md:block">
              <div class="action-card sticky top-24">
                
                <!-- Metrics Grid -->
                <div class="ac-metrics-grid mb-5">
                  <div class="ac-metric-box">
                    <div class="ac-metric-lbl">Target Price</div>
                    <div class="ac-metric-val">Rp {{ formattedPrice || '-' }}</div>
                  </div>
                  <div class="ac-metric-box text-center">
                    <div class="ac-metric-lbl">Upside</div>
                    <div class="ac-metric-val" :class="research.upside && research.upside.includes('-') ? 'text-red-400' : 'text-emerald-400'">{{ research.upside || '-' }}</div>
                  </div>
                  <div class="ac-metric-box items-end">
                    <div class="ac-metric-lbl">Rekomendasi</div>
                    <div class="ac-metric-badge" :class="research.recommendation === 'BUY' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : (research.recommendation === 'SELL' ? 'bg-red-500/10 text-red-400 border-red-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/20')">{{ research.recommendation || 'BUY' }}</div>
                  </div>
                </div>

                <div class="ac-info-grid mb-6">
                  <div class="ac-info-item">
                    <div class="ac-info-lbl">Sektor</div>
                    <div class="ac-info-val text-white">{{ research.sector || 'Perbankan' }}</div>
                  </div>
                  <div class="ac-info-item">
                    <div class="ac-info-lbl">Tipe Laporan</div>
                    <div class="ac-info-val text-white">Equity Research</div>
                  </div>
                  <div class="ac-info-item">
                    <div class="ac-info-lbl">Frekuensi Update</div>
                    <div class="ac-info-val text-white">Premium Report</div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="ac-actions">
                  <template v-if="isLocked">
                    <button class="ac-btn-download" @click="authStore.open('login')">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                      Lihat Laporan Lengkap
                    </button>
                    <p class="text-[10px] text-center text-slate-500 mt-3 px-2">Anda memerlukan akses Premium untuk mengunduh laporan ini.</p>
                  </template>
                  <template v-else>
                    <a v-if="research.pdf_path" :href="research.pdf_path" target="_blank" download class="ac-btn-download shadow-lg shadow-emerald-500/20">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                      Unduh PDF Lengkap
                    </a>
                    <button v-else disabled class="ac-btn-disabled">
                      PDF Belum Tersedia
                    </button>
                  </template>
                </div>

              </div>
            </div>

          </div>

        </div>
      </div>

      <!-- Sticky Bottom Action Bar (Mobile Only) -->
      <div v-if="!hasCustomHtml && !hasCustomHero" class="kdp-sticky-bottom block md:hidden">
         <template v-if="isLocked">
            <button class="ac-btn-download w-full" @click="authStore.open('login')">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              Lihat Laporan Lengkap
            </button>
         </template>
         <template v-else>
            <a v-if="research.pdf_path" :href="research.pdf_path" target="_blank" download class="ac-btn-download w-full shadow-lg shadow-emerald-500/20">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
              Unduh PDF Lengkap
            </a>
            <button v-else disabled class="ac-btn-disabled w-full">
              PDF Belum Tersedia
            </button>
         </template>
      </div>

    </div>
  </AppLayout>
</template>

<style>

/* New Modern Mobile-First Hero */
.kdp-hero-modern {
  position: relative;
  width: 100%;
  min-height: 340px;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
}
@media (min-width: 900px) {
  .kdp-hero-modern {
    min-height: 480px;
    padding-bottom: 24px;
  }
}

.kdp-hero-bg {
  position: absolute;
  inset: 0;
  z-index: 0;
}
.kdp-hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(9,11,10,0.1) 0%, rgba(9,11,10,0.85) 60%, #090b0a 100%);
}

.kdp-hero-content {
  position: relative;
  z-index: 10;
  padding: 0 0;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

.kdp-top-nav {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  padding: 16px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
@media (min-width: 900px) {
  .kdp-top-nav {
    padding: 24px 32px;
  }
}

.kdp-icon-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(0,0,0,0.4);
  backdrop-filter: blur(8px);
  color: #fff;
  transition: all 0.2s;
  border: 1px solid rgba(255,255,255,0.1);
}
.kdp-icon-btn:hover {
  background: rgba(0,0,0,0.6);
  border-color: rgba(255,255,255,0.2);
}

.kdp-hero-info {
  padding: 80px 20px 20px;
}
@media (min-width: 900px) {
  .kdp-hero-info {
    padding: 100px 32px 0;
  }
}

.kdp-badge-premium-gold {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: transparent;
  border: 1px solid rgba(251, 191, 36, 0.4);
  color: #fbbf24;
  font-size: 10px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 6px;
  letter-spacing: 0.05em;
}

.kdp-company-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}
.kdp-ticker {
  font-size: 16px;
  font-weight: 700;
  color: #fff;
  letter-spacing: 0.05em;
}
.kdp-company-name {
  font-size: 14px;
  color: #94a3b8;
}

.kdp-h1-modern {
  font-size: 26px;
  font-weight: 700;
  color: #fff;
  line-height: 1.3;
  margin-bottom: 12px;
}
@media (min-width: 900px) {
  .kdp-h1-modern {
    font-size: 40px;
    max-width: 800px;
  }
}

.kdp-meta-modern {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: #94a3b8;
  margin-bottom: 24px;
}
.kdp-meta-modern .dot {
  font-size: 10px;
}

.kdp-author-row {
  display: flex;
  align-items: center;
  gap: 12px;
}
.kdp-author-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: #1e293b;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  border: 1px solid rgba(255,255,255,0.1);
}
.kdp-author-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.kdp-author-name {
  font-size: 14px;
  font-weight: 700;
  color: #fff;
}
.kdp-author-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 10px;
  font-weight: 600;
  color: #10B981;
  background: rgba(16,185,129,0.1);
  padding: 2px 6px;
  border-radius: 4px;
}
.kdp-author-role {
  font-size: 12px;
  color: #64748b;
  margin-top: 2px;
}

/* Stats Bar */
.kdp-stats-bar-wrapper {
  border-bottom: 1px solid rgba(255,255,255,0.05);
}
.kdp-stats-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 0;
}
.kdp-stat-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  color: #64748b;
  font-size: 12px;
}
.kdp-stat-item svg {
  color: #94a3b8;
}
.kdp-stat-item strong {
  color: #fff;
  font-size: 13px;
}
.kdp-stat-sep {
  width: 1px;
  height: 32px;
  background: rgba(255,255,255,0.08);
}


/* New Layout CSS */
.kdp-split-layout {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 32px;
}
@media (max-width: 900px) {
  .kdp-split-layout {
    grid-template-columns: 1fr;
  }
}

.kdp-summary-col {
  min-width: 0;
}

/* Custom Breadcrumb for Legacy HTML */
.kdp-custom-breadcrumb {
  max-width: 1120px;
  margin: 0 auto;
  padding: 24px 28px 0;
}
.kdp-bc-link-custom {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #10b981; /* emerald-500 */
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s ease;
}
.kdp-bc-link-custom:hover {
  color: #059669; /* emerald-600 */
  transform: translateX(-2px);
}

.summary-card {
  padding: 0;
}
@media (min-width: 900px) {
  .summary-card {
    background: #111413;
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  }
}

.summary-title {
  font-size: 18px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 16px;
}
@media (min-width: 900px) {
  .summary-title {
    font-size: 20px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  }
}

/* Action Card / Metrics Box */
.action-card, .action-card-mobile {
  background: #111413;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.ac-metrics-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}
.ac-metric-box {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.ac-metric-lbl {
  font-size: 10.5px;
  color: #64748b;
  font-weight: 500;
}
.ac-metric-val {
  font-size: 13.5px;
  font-weight: 700;
  color: #fff;
}
.ac-metric-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 4px 6px;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 700;
  border: 1px solid transparent;
  width: max-content;
}

.ac-info-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  border-top: 1px solid rgba(255,255,255,0.05);
  padding-top: 16px;
}
.ac-info-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.ac-info-lbl {
  font-size: 10px;
  color: #64748b;
}
.ac-info-val {
  font-size: 11px;
  color: #fff;
  font-weight: 500;
}

.ac-btn-download {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  background: #10B981;
  color: #fff;
  padding: 14px 20px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 14px;
  transition: all 0.2s;
  text-decoration: none;
}
.ac-btn-download:hover {
  background: #059669;
  transform: translateY(-2px);
}

.ac-btn-disabled {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  background: #1e293b;
  color: #64748b;
  padding: 14px 20px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  cursor: not-allowed;
  border: 1px dashed rgba(255, 255, 255, 0.1);
}

/* Guest lock positioning and styles */
.guest-lock-wrap {
  position: relative;
  min-height: 200px;
}

.guest-lock-content {
  filter: none;
  pointer-events: auto;
  user-select: auto;
  color: #cbd5e1;
  line-height: 1.7;
}

/* When article is locked */
.guest-lock-wrap.is-guest .guest-lock-content {
  filter: blur(8px);
  pointer-events: none;
  user-select: none;
  max-height: 400px;
  overflow: hidden;
  -webkit-mask-image: linear-gradient(180deg, #000 0%, #000 50%, transparent 100%);
  mask-image: linear-gradient(180deg, #000 0%, #000 50%, transparent 100%);
}

/* ── Page wrapper ── */
.kdp-page {
  background-color: var(--bg, #090b0a);
  color: var(--t, #cbd5e1);
  min-height: calc(100vh - 52px);
  position: relative;
  overflow: hidden;
  font-family: 'Roboto', sans-serif;
}

.kdp-glow {
  position: absolute;
  border-radius: 50%;
  filter: blur(140px);
  pointer-events: none;
  z-index: 0;
  opacity: 0.12;
}
.kdp-glow-tr { top: -10%; right: -5%; width: 500px; height: 500px; background: radial-gradient(circle, #10B981 0%, transparent 70%); }
.kdp-glow-bl { bottom: 5%; left: -10%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(16, 185, 129, 0.8) 0%, transparent 70%); }

/* ── Shared container ── */
.kdp-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  position: relative;
  z-index: 2;
}
@media (min-width: 900px) {
  .kdp-container {
    padding: 0 32px;
  }
}

/* Sticky Bottom for Mobile */
.kdp-sticky-bottom {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 16px 20px;
  background: rgba(9, 11, 10, 0.9);
  backdrop-filter: blur(10px);
  border-top: 1px solid rgba(255,255,255,0.05);
  z-index: 50;
}

/* ── Content area ── */
.kdp-content-area {
  padding: 24px 0 100px;
  position: relative;
  z-index: 2;
}
@media (min-width: 900px) {
  .kdp-content-area {
    padding: 48px 0 80px;
  }
}

/* Fallback content - Typography Improvements */
.kdp-page:not(.has-custom-content) .guest-lock-content,
.kdp-page:not(.has-custom-content) .db-content {
  font-size: 15px !important;
  line-height: 1.85 !important;
  color: #cbd5e1 !important;
  text-align: justify;
}

/* Remove huge gaps caused by empty paragraphs */
.kdp-page:not(.has-custom-content) .guest-lock-content p:empty,
.kdp-page:not(.has-custom-content) .db-content p:empty {
  display: none !important;
}

.kdp-page:not(.has-custom-content) .guest-lock-content p,
.kdp-page:not(.has-custom-content) .db-content p {
  margin: 0 0 1.25rem !important;
}

/* Fix huge spaces from consecutive <br> tags */
.kdp-page:not(.has-custom-content) .db-content br {
  display: block;
  content: "";
  margin-top: 1.25rem;
}
.kdp-page:not(.has-custom-content) .db-content br + br {
  display: none;
}

/* Drop Cap for the first letter of the container */
.kdp-page:not(.has-custom-content) .db-content::first-letter {
  float: left;
  font-size: 3.5rem;
  line-height: 0.8;
  margin-right: 0.15em;
  margin-bottom: -0.05em;
  font-weight: 700;
  color: #10B981;
}

/* Styling for lists */
.kdp-page:not(.has-custom-content) .db-content ul {
  list-style-type: disc;
  padding-left: 1.5rem;
  margin-bottom: 1.25rem !important;
  color: #cbd5e1;
}
.kdp-page:not(.has-custom-content) .db-content li {
  margin-bottom: 0.5rem;
}

/* Highlight strong text */
.kdp-page:not(.has-custom-content) .db-content strong,
.kdp-page:not(.has-custom-content) .db-content b {
  color: #fff;
  font-weight: 600;
}

</style>
