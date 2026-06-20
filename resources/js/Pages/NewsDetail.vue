<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Paywall from '@/Components/Paywall.vue';
import { computed } from 'vue';

const props = defineProps({
    news: {
        type: Object,
        required: true,
    }
});

const page = usePage();
const isLoggedIn = computed(() => !!page.props.auth?.user);

const isLocked = computed(() => {
    return props.news.is_paid && !props.news.is_unlocked;
});
</script>

<template>
    <Head>
        <title>NewsDetail | AVENIR</title>
        <meta name="description" content="AVENIR - Platform riset dan direktori pasar modal Indonesia yang komprehensif." />
        <meta property="og:title" content="NewsDetail | AVENIR" />
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

  <Head :title="news.title" />

  <AppLayout>
    <div class="news-detail-page">
      <!-- Glow Backdrops -->
      <div class="radial-glow glow-top-right"></div>
      <div class="radial-glow glow-bottom-left"></div>

      <div class="news-container">
        <!-- Back Link -->
        <a href="/news" class="back-link">
          <span class="arrow">←</span> Kembali ke Market News
        </a>

        <div class="guest-lock-wrap" :class="{ 'is-guest': isLocked }">
          <!-- News Header -->
          <div class="news-header">
            <div class="flex items-center gap-3 mb-5">
              <div class="news-cat !mb-0" v-if="news.category">{{ news.category }}</div>
              
              <div v-if="news.is_paid" class="px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center gap-1">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                PREMIUM
              </div>
              <div v-else class="px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1">
                GRATIS
              </div>

              <div v-if="news.sentiment" :class="[
                'px-3 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase border',
                news.sentiment.toLowerCase() === 'positif' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 
                (news.sentiment.toLowerCase() === 'negatif' ? 'bg-red-500/10 text-red-400 border-red-500/20' : 'bg-slate-500/10 text-slate-400 border-slate-500/20')
              ]">{{ news.sentiment }}</div>
            </div>
            <h1 class="news-title">{{ news.title }}</h1>
            <div class="news-meta flex items-center">
              <span v-if="news.source" class="text-slate-400">Sumber: <span class="text-emerald-400/90 font-medium">{{ news.source }}</span></span>
              <span v-if="news.source && news.published_at" class="mx-2 text-slate-600">•</span>
              <span class="date">{{ news.published_at }}</span>
            </div>
            
            <!-- Author Info -->
            <div class="flex items-center gap-3 mt-6">
              <div class="w-10 h-10 rounded-full bg-[#1e293b] overflow-hidden border border-white/10 flex items-center justify-center text-slate-500">
                <img v-if="news.author?.profile_photo_url" :src="news.author.profile_photo_url" alt="Author" class="w-full h-full object-cover">
                <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              </div>
              <div>
                <div class="text-sm font-bold text-white">{{ news.author ? news.author.name : 'Tim Avenir Research' }}</div>
                <div class="text-xs text-slate-500">Avenir News Editor</div>
              </div>
            </div>
          </div>

          <!-- Cover Image -->
          <div v-if="news.cover_image" class="w-full mb-10 rounded-2xl overflow-hidden border border-white/5 shadow-2xl">
            <img :src="news.cover_image" :alt="news.title" class="w-full h-auto max-h-[500px] object-cover" />
          </div>

          <!-- News Content -->
          <div 
            class="guest-lock-content news-content" 
            v-html="news.content"
          />

          <!-- Lock Overlay Gate -->
          <Paywall 
            v-if="isLocked"
            :price="'Setelah trial: mulai <strong>Rp 149.000 / bulan</strong>'"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Content styles */
.guest-lock-wrap {
  position: relative;
  min-height: 400px;
  z-index: 2;
}

.guest-lock-content {
  filter: none;
  pointer-events: auto;
  user-select: auto;
}

/* When news is locked (is_guest status) */
.guest-lock-wrap.is-guest .guest-lock-content {
  filter: blur(8px);
  pointer-events: none;
  user-select: none;
  max-height: 650px;
  overflow: hidden;
  -webkit-mask-image: linear-gradient(180deg, #000 0%, #000 50%, transparent 100%);
  mask-image: linear-gradient(180deg, #000 0%, #000 50%, transparent 100%);
}

.guest-lock-wrap.is-guest :deep(.guest-lock-overlay) {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  padding: 60px 24px;
  text-align: center;
  background: linear-gradient(180deg, rgba(9, 11, 10, 0) 0%, rgba(9, 11, 10, 0.96) 25%, #090b0a 55%);
  z-index: 100;
}

.news-content {
  filter: none;
  pointer-events: auto;
  user-select: auto;
}

.news-detail-page {
  background-color: #090b0a;
  color: #cbd5e1;
  font-family: 'Roboto', sans-serif;
  min-height: 100vh;
  position: relative;
  overflow-x: hidden;
  padding-top: 40px;
}

/* Glowing Background Vectors */
.radial-glow {
  position: absolute;
  border-radius: 50%;
  filter: blur(140px);
  pointer-events: none;
  z-index: 1;
}
.glow-top-right {
  top: -10%;
  right: -10%;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, rgba(9, 11, 10, 0) 70%);
}
.glow-bottom-left {
  bottom: -10%;
  left: -10%;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(52, 211, 153, 0.06) 0%, rgba(9, 11, 10, 0) 70%);
}

.news-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 24px 100px;
  position: relative;
  z-index: 2;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 600;
  color: #10b981;
  text-decoration: none;
  margin-bottom: 32px;
  transition: transform 0.2s, color 0.2s;
}

.back-link:hover {
  color: #34d399;
  transform: translateX(-4px);
}

.news-header {
  margin-bottom: 40px;
}

.news-cat {
  display: inline-flex;
  align-items: center;
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.25);
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  padding: 6px 14px;
  border-radius: 100px;
}

.news-title {
  font-family: 'Roboto', sans-serif;
  font-size: clamp(26px, 4vw, 36px);
  font-weight: 700;
  line-height: 1.25;
  color: #ffffff;
  margin: 0 0 20px;
  letter-spacing: -0.01em;
}

.news-meta {
  font-size: 12px;
  color: #64748b;
  padding-bottom: 24px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.news-meta .date {
  font-family: 'Roboto', sans-serif;
}

/* Deep Styles for News Html Content */
:deep(.art-body),
:deep(.news-content) {
  font-size: 16px;
  line-height: 1.85;
  color: #cbd5e1;
}

/* Force inner wrappers from CMS to span full width to match image width */
:deep(.news-content div),
:deep(.news-content p),
:deep(.art-body div),
:deep(.art-body p) {
  max-width: 100% !important;
}

:deep(.art-body p) {
  margin-bottom: 24px;
}

:deep(.art-body h2) {
  font-family: 'Roboto', sans-serif;
  font-size: 24px;
  font-weight: 600;
  color: #ffffff;
  margin: 40px 0 20px;
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

:deep(.art-body h3) {
  font-family: 'Roboto', sans-serif;
  font-size: 18px;
  font-weight: 600;
  color: #ffffff;
  margin: 30px 0 12px;
}

:deep(.art-body strong) {
  color: #ffffff;
}

:deep(.art-body em) {
  font-style: italic;
}

:deep(.art-body ul),
:deep(.art-body ol) {
  margin: 0 0 24px 24px;
  color: #cbd5e1;
}

:deep(.art-body li) {
  margin-bottom: 8px;
}

/* Custom styles for tables, blockquotes, etc. inside legacy files */
:deep(.art-table) {
  width: 100%;
  border-collapse: collapse;
  margin: 24px 0;
  background: #111413;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

:deep(.art-table th) {
  background: #090b0a;
  color: #ffffff;
  padding: 12px 16px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  text-align: left;
}

:deep(.art-table td) {
  padding: 12px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.03);
  color: #cbd5e1;
  font-size: 13.5px;
}

:deep(.art-table tr:last-child td) {
  border-bottom: none;
}

:deep(.art-table tr:nth-child(even) td) {
  background: rgba(255, 255, 255, 0.01);
}

:deep(.art-blockquote) {
  margin: 32px 0;
  padding: 20px 24px;
  background: rgba(16, 185, 129, 0.04);
  border-left: 4px solid #10b981;
  border-radius: 0 12px 12px 0;
  font-style: italic;
  color: #e2e8f0;
}

:deep(.art-timeline) {
  margin: 32px 0;
}

:deep(.art-timeline-item) {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
}

:deep(.art-timeline-date) {
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  color: #10b981;
  font-weight: 700;
  min-width: 100px;
}

:deep(.art-timeline-desc) {
  font-size: 14.5px;
  color: #cbd5e1;
}
</style>
