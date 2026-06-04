<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { authStore } from '@/Stores/authStore';
import { computed } from 'vue';

const props = defineProps({
    article: {
        type: Object,
        required: true,
    }
});

const page = usePage();
const isLoggedIn = computed(() => !!page.props.auth?.user);

const isLocked = computed(() => {
    return props.article.is_paid && !isLoggedIn.value;
});

const hasCustomHtml = computed(() => {
    return props.article.content && props.article.content.includes('art-page');
});
</script>

<template>
  <Head :title="article.title" />

  <AppLayout>
    <div class="article-detail-page">
      <!-- Glow Backdrops -->
      <div class="radial-glow glow-top-right"></div>
      <div class="radial-glow glow-bottom-left"></div>

      <div 
        class="guest-lock-wrap" 
        :class="{ 'is-guest': isLocked }"
      >
        <!-- Dynamic Header for DB-seeded articles -->
        <div v-if="!hasCustomHtml" class="db-article-header">
          <div class="db-article-cat" v-if="article.category">{{ article.category }}</div>
          <h1 class="db-article-title">{{ article.title }}</h1>
          <div class="db-article-meta">
            <span class="author">Oleh <strong>{{ article.author || 'Tim Avenir Research' }}</strong></span>
            <span class="meta-sep">·</span>
            <span class="date">{{ article.published_at }}</span>
          </div>
          <img 
            v-if="article.cover_image" 
            :src="article.cover_image" 
            :alt="article.title" 
            class="db-article-hero"
          />
        </div>

        <!-- Article HTML Content -->
        <div 
          class="guest-lock-content" 
          :class="{ 'db-content': !hasCustomHtml }"
          v-html="article.content"
        />

        <!-- Lock Overlay Gate -->
        <div v-if="isLocked" class="guest-lock-overlay">
          <div class="gl-icon">🔒</div>
          <h2 class="gl-title">Daftar Gratis untuk Mulai Akses</h2>
          <p class="gl-sub">
            Daftar sekarang dan dapatkan 7 hari uji coba gratis ke seluruh katalog riset ekuitas, scenario analysis, dan investment thesis Avenir Research.
          </p>
          <div class="gl-price">
            Setelah trial: mulai <strong>Rp 149.000 / bulan</strong>
          </div>
          <div class="gl-actions">
            <button 
              class="gl-btn-primary" 
              @click="authStore.open('register')"
            >
              Mulai Trial 7 Hari
            </button>
            <button 
              class="gl-btn-secondary" 
              @click="authStore.open('login')"
            >
              Sudah Punya Akun
            </button>
          </div>
          <div class="gl-link">
            <a href="/langganan">Lihat detail paket di homepage</a>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap');

.article-detail-page {
  background-color: #090b0a;
  min-height: calc(100vh - 52px);
  padding: 40px 0;
  position: relative;
  overflow: hidden;
}

/* Glowing Background Vectors */
.article-detail-page .radial-glow {
  position: absolute;
  border-radius: 50%;
  filter: blur(140px);
  pointer-events: none;
  z-index: 1;
  opacity: 0.12;
}
.article-detail-page .glow-top-right {
  top: -10%;
  right: -5%;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, #10B981 0%, transparent 70%);
}
.article-detail-page .glow-bottom-left {
  bottom: 5%;
  left: -10%;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.8) 0%, transparent 70%);
}

/* Guest lock positioning and styles */
.guest-lock-wrap {
  position: relative;
  min-height: 600px;
  max-width: 1000px;
  margin: 0 auto;
  z-index: 2;
}

.guest-lock-content {
  filter: none;
  pointer-events: auto;
  user-select: auto;
}

/* When article is locked (is_guest status) */
.guest-lock-wrap.is-guest .guest-lock-content {
  filter: blur(8px);
  pointer-events: none;
  user-select: none;
  max-height: 650px;
  overflow: hidden;
  -webkit-mask-image: linear-gradient(180deg, #000 0%, #000 50%, transparent 100%);
  mask-image: linear-gradient(180deg, #000 0%, #000 50%, transparent 100%);
}

.guest-lock-wrap.is-guest .guest-lock-overlay {
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

.gl-icon {
  font-size: 40px;
  margin-bottom: 20px;
  opacity: 0.8;
  color: #10B981;
  text-shadow: 0 0 10px rgba(16, 185, 129, 0.3);
}

.gl-title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 32px;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 16px;
  line-height: 1.25;
  max-width: 600px;
}

.gl-sub {
  font-size: 15px;
  color: #94a3b8;
  line-height: 1.65;
  margin-bottom: 20px;
  max-width: 560px;
}

.gl-price {
  display: inline-block;
  padding: 10px 24px;
  background: rgba(16, 185, 129, 0.08);
  border: 1px solid rgba(16, 185, 129, 0.2);
  border-radius: 100px;
  font-size: 13.5px;
  color: #10B981;
  margin-bottom: 28px;
}

.gl-price strong {
  color: #ffffff;
  font-weight: 700;
}

.gl-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  justify-content: center;
  margin-bottom: 20px;
}

.gl-btn-primary,
.gl-btn-secondary {
  padding: 14px 28px;
  border-radius: 100px;
  font-weight: 700;
  font-size: 13.5px;
  cursor: pointer;
  letter-spacing: .02em;
  transition: all .25s cubic-bezier(0.16, 1, 0.3, 1);
  border: none;
  font-family: inherit;
}

.gl-btn-primary {
  background: #1B6B3A;
  color: #fff;
  box-shadow: 0 4px 14px rgba(27, 107, 58, 0.3);
}

.gl-btn-primary:hover {
  background: #10b981;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.gl-btn-secondary {
  background: transparent;
  color: #cbd5e1;
  border: 1.5px solid rgba(255, 255, 255, 0.1);
}

.gl-btn-secondary:hover {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.2);
  color: #fff;
}

.gl-link {
  font-size: 12.5px;
  color: #64748b;
}

.gl-link a {
  color: #10B981;
  text-decoration: none;
  font-weight: 600;
}

.gl-link a:hover {
  text-decoration: underline;
}

/* ── Dynamic Content Styles Overrides ── */
.article-detail-page .art-page {
  max-width: 800px;
  margin: 0 auto;
  padding: 40px 24px 80px;
  font-family: 'Inter', sans-serif !important;
}

.article-detail-page .art-label {
  display: inline-flex;
  align-items: center;
  background: rgba(16, 185, 129, 0.1) !important;
  color: #10B981 !important;
  border: 1px solid rgba(16, 185, 129, 0.25) !important;
  font-size: 10px !important;
  font-weight: 700 !important;
  letter-spacing: 0.15em !important;
  text-transform: uppercase !important;
  padding: 6px 14px !important;
  border-radius: 100px !important;
  margin-bottom: 20px !important;
}

.article-detail-page .art-title {
  font-family: 'Space Grotesk', sans-serif !important;
  font-size: clamp(30px, 5vw, 48px) !important;
  font-weight: 700 !important;
  line-height: 1.2 !important;
  color: #ffffff !important;
  margin: 0 0 20px !important;
  letter-spacing: -0.02em !important;
}

.article-detail-page .art-deck {
  font-size: 17px !important;
  line-height: 1.7 !important;
  color: #94a3b8 !important;
  margin: 0 0 28px !important;
  font-weight: 400 !important;
  border-left: 3px solid #10B981 !important;
  padding-left: 20px !important;
}

.article-detail-page .art-meta {
  display: flex !important;
  flex-wrap: wrap !important;
  gap: 8px 24px !important;
  font-size: 12px !important;
  color: #64748b !important;
  padding-bottom: 24px !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
  margin-bottom: 36px !important;
}

.article-detail-page .art-meta strong {
  color: #cbd5e1 !important;
  font-weight: 600 !important;
}

.article-detail-page .art-hero {
  border: 1px solid rgba(255, 255, 255, 0.05) !important;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5) !important;
}

.article-detail-page .art-body {
  font-size: 16px !important;
  line-height: 1.85 !important;
  color: #cbd5e1 !important;
}

.article-detail-page .art-body p {
  margin: 0 0 24px !important;
}

.article-detail-page .art-body h2 {
  font-family: 'Space Grotesk', sans-serif !important;
  font-size: 28px !important;
  font-weight: 600 !important;
  color: #ffffff !important;
  margin: 48px 0 20px !important;
  padding-top: 24px !important;
  border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
}

.article-detail-page .art-body h3 {
  font-family: 'Space Grotesk', sans-serif !important;
  font-size: 20px !important;
  font-weight: 600 !important;
  color: #ffffff !important;
  margin: 36px 0 14px !important;
}

.article-detail-page .art-body strong {
  color: #ffffff !important;
  font-weight: 700 !important;
}

.article-detail-page .art-callout {
  background: rgba(16, 185, 129, 0.04) !important;
  border-left: 4px solid #10B981 !important;
  padding: 18px 24px !important;
  border-radius: 0 12px 12px 0 !important;
  margin: 28px 0 !important;
  font-size: 15px !important;
  line-height: 1.75 !important;
  color: #34d399 !important;
}

.article-detail-page .art-callout-amber {
  background: rgba(245, 158, 11, 0.04) !important;
  border-left-color: #fbbf24 !important;
  color: #fbbf24 !important;
}

.article-detail-page .art-callout-blue {
  background: rgba(59, 130, 246, 0.04) !important;
  border-left-color: #60a5fa !important;
  color: #60a5fa !important;
}

.article-detail-page .art-table-wrap {
  border: 1px solid rgba(255, 255, 255, 0.05) !important;
  border-radius: 12px !important;
  background: #111413 !important;
  margin: 28px 0 !important;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
}

.article-detail-page .art-table {
  border: 0 !important;
}

.article-detail-page .art-table th {
  background: #090b0a !important;
  color: #ffffff !important;
  padding: 12px 16px !important;
  font-size: 11px !important;
  font-weight: 700 !important;
  letter-spacing: 0.08em !important;
  text-transform: uppercase !important;
  border: 1px solid rgba(255, 255, 255, 0.05) !important;
}

.article-detail-page .art-table td {
  padding: 12px 16px !important;
  border: 1px solid rgba(255, 255, 255, 0.03) !important;
  color: #cbd5e1 !important;
  font-size: 13.5px !important;
}

.article-detail-page .art-table tr:nth-child(even) td {
  background: rgba(255, 255, 255, 0.01) !important;
}

.article-detail-page .art-table tr.art-hl td {
  background: rgba(16, 185, 129, 0.08) !important;
  font-weight: 600 !important;
  color: #10B981 !important;
}

.article-detail-page .art-stats {
  display: grid !important;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)) !important;
  gap: 18px !important;
  margin: 32px 0 !important;
}

.article-detail-page .art-stat {
  background: #111413 !important;
  border: 1px solid rgba(255, 255, 255, 0.05) !important;
  color: #ffffff !important;
  border-radius: 12px !important;
  padding: 20px !important;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2) !important;
}

.article-detail-page .art-stat .sv {
  font-family: 'JetBrains Mono', monospace !important;
  font-size: 24px !important;
  font-weight: 700 !important;
  color: #10B981 !important;
  margin-bottom: 6px !important;
}

.article-detail-page .art-stat .sl {
  font-size: 10px !important;
  color: #94a3b8 !important;
  font-weight: 600 !important;
  letter-spacing: .08em !important;
}

.article-detail-page .art-toc {
  background: #111413 !important;
  border: 1px solid rgba(255, 255, 255, 0.05) !important;
  border-radius: 12px !important;
  padding: 24px !important;
  margin: 32px 0 !important;
}

.article-detail-page .art-toc h4 {
  font-family: 'Space Grotesk', sans-serif !important;
  font-size: 11px !important;
  font-weight: 700 !important;
  letter-spacing: .12em !important;
  color: #64748b !important;
  text-transform: uppercase !important;
  margin: 0 0 16px !important;
}

.article-detail-page .art-toc ol {
  font-size: 14.5px !important;
  line-height: 2.0 !important;
  color: #cbd5e1 !important;
}

.article-detail-page .art-toc ol li a {
  color: #10B981 !important;
  transition: color 0.2s ease !important;
}

.article-detail-page .art-toc ol li a:hover {
  color: #34d399 !important;
  text-decoration: underline !important;
}

.article-detail-page .art-sources {
  font-size: 12.5px !important;
  line-height: 1.8 !important;
  color: #64748b !important;
  margin-top: 56px !important;
  padding-top: 28px !important;
  border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
}

.article-detail-page .art-sources strong {
  color: #cbd5e1 !important;
}

.article-detail-page .art-poin {
  display: flex !important;
  gap: 18px !important;
  padding: 20px !important;
  background: #111413 !important;
  border: 1px solid rgba(255, 255, 255, 0.03) !important;
  border-radius: 12px !important;
  margin: 16px 0 !important;
}

.article-detail-page .art-poin-num {
  background: #090b0a !important;
  color: #10B981 !important;
  border: 1px solid rgba(16, 185, 129, 0.15) !important;
  font-family: 'JetBrains Mono', monospace !important;
  font-size: 14px !important;
  font-weight: 700 !important;
  width: 36px !important;
  height: 36px !important;
  border-radius: 50% !important;
}

.article-detail-page .art-poin-body {
  font-size: 14.5px !important;
  line-height: 1.75 !important;
  color: #cbd5e1 !important;
}

.article-detail-page .photo-band {
  margin: 40px 0 !important;
  border-radius: 12px !important;
  border: 1px solid rgba(255, 255, 255, 0.05) !important;
}

/* ── Dynamic Header for DB-seeded articles ── */
.db-article-header {
  max-width: 800px;
  margin: 0 auto;
  padding: 40px 24px 0;
  font-family: 'Inter', sans-serif;
  position: relative;
  z-index: 5;
}

.db-article-cat {
  display: inline-flex;
  align-items: center;
  background: rgba(16, 185, 129, 0.1) !important;
  color: #10B981 !important;
  border: 1px solid rgba(16, 185, 129, 0.25) !important;
  font-size: 10px !important;
  font-weight: 700 !important;
  letter-spacing: 0.15em !important;
  text-transform: uppercase !important;
  padding: 6px 14px !important;
  border-radius: 100px !important;
  margin-bottom: 20px !important;
}

.db-article-title {
  font-family: 'Space Grotesk', sans-serif !important;
  font-size: clamp(30px, 5vw, 48px) !important;
  font-weight: 700 !important;
  line-height: 1.2 !important;
  color: #ffffff !important;
  margin: 0 0 20px !important;
  letter-spacing: -0.02em !important;
}

.db-article-meta {
  display: flex !important;
  flex-wrap: wrap !important;
  gap: 8px 24px !important;
  font-size: 12px !important;
  color: #64748b !important;
  padding-bottom: 24px !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
  margin-bottom: 36px !important;
}

.db-article-meta .author strong {
  color: #cbd5e1 !important;
  font-weight: 600 !important;
}

.db-article-meta .date {
  font-family: 'JetBrains Mono', monospace !important;
}

.db-article-meta .meta-sep {
  color: #475569 !important;
  margin: 0 4px !important;
}

.db-article-hero {
  width: 100% !important;
  aspect-ratio: 16/7 !important;
  object-fit: cover !important;
  border-radius: 12px !important;
  margin-bottom: 36px !important;
  background: #090b0a !important;
  border: 1px solid rgba(255, 255, 255, 0.05) !important;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5) !important;
}

/* Fallback styles for database-seeded articles that don't have .art-page wrapper */
.article-detail-page .guest-lock-content.db-content {
  max-width: 800px;
  margin: 0 auto;
  padding: 40px 24px 80px;
}

.article-detail-page .guest-lock-content.db-content p {
  font-size: 16px !important;
  line-height: 1.85 !important;
  color: #cbd5e1 !important;
  margin: 0 0 24px !important;
}

.article-detail-page .guest-lock-content.db-content h2 {
  font-family: 'Space Grotesk', sans-serif !important;
  font-size: 28px !important;
  font-weight: 600 !important;
  color: #ffffff !important;
  margin: 48px 0 20px !important;
  padding-top: 24px !important;
  border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
}

.article-detail-page .guest-lock-content.db-content h3 {
  font-family: 'Space Grotesk', sans-serif !important;
  font-size: 20px !important;
  font-weight: 600 !important;
  color: #ffffff !important;
  margin: 36px 0 14px !important;
}

.article-detail-page .guest-lock-content.db-content strong {
  color: #ffffff !important;
}

.article-detail-page .guest-lock-content.db-content em {
  font-style: italic !important;
}

.article-detail-page .guest-lock-content.db-content ul,
.article-detail-page .guest-lock-content.db-content ol {
  margin: 0 0 24px 24px !important;
  color: #cbd5e1 !important;
}

.article-detail-page .guest-lock-content.db-content li {
  font-size: 16px !important;
  line-height: 1.85 !important;
  margin-bottom: 8px !important;
}

@media (max-width: 600px) {
  .gl-title { font-size: 26px; }
  .gl-sub { font-size: 14px; }
  .gl-price { font-size: 12.5px; padding: 8px 14px; }
  .gl-btn-primary, .gl-btn-secondary { padding: 12px 20px; font-size: 13px; width: 100%; }
  .gl-actions { flex-direction: column; }
}
</style>
