<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    news: {
        type: Object,
        required: true,
    }
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

        <div class="guest-lock-wrap">
          <!-- News Header -->
          <div class="news-header">
            <div class="flex items-center gap-3 mb-5">
              <div class="news-cat !mb-0" v-if="news.category">{{ news.category }}</div>
              <div v-if="news.sentiment" :class="[
                'px-3 py-1 rounded-full text-xs font-bold tracking-wider uppercase',
                news.sentiment.toLowerCase() === 'positif' ? 'bg-emerald-500/10 text-emerald-400' : 
                (news.sentiment.toLowerCase() === 'negatif' ? 'bg-red-500/10 text-red-400' : 'bg-slate-500/10 text-slate-400')
              ]">{{ news.sentiment }}</div>
            </div>
            <h1 class="news-title">{{ news.title }}</h1>
            <div class="news-meta flex items-center">
              <span v-if="news.source" class="text-slate-400">Sumber: <span class="text-emerald-400/90 font-medium">{{ news.source }}</span></span>
              <span v-if="news.source && news.published_at" class="mx-2 text-slate-600">•</span>
              <span class="date">{{ news.published_at }}</span>
            </div>
          </div>

          <!-- Cover Image -->
          <div v-if="news.cover_image" class="w-full mb-10 rounded-2xl overflow-hidden border border-white/5 shadow-2xl">
            <img :src="news.cover_image" :alt="news.title" class="w-full h-auto max-h-[500px] object-cover" />
          </div>

          <!-- News Content -->
          <div 
            class="news-content" 
            v-html="news.content"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Content styles */
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
  max-width: 800px;
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
