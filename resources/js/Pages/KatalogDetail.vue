<script setup>
import { Head, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Paywall from '@/Components/Paywall.vue';
import { authStore } from '@/Stores/authStore';
import { computed } from 'vue';

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
    if (!props.research.price) return null;
    return Number(props.research.price).toLocaleString('id-ID');
});
</script>

<template>
  <Head :title="`${research.ticker ? research.ticker + ' — ' : ''}${research.title} | Avenir Research`" />

  <AppLayout>
    <div :id="'page-' + research.slug" class="kdp-page" :class="{ 'has-custom-content': hasCustomHtml || hasCustomHero }">
      <!-- Background glows -->
      <div class="kdp-glow kdp-glow-tr"></div>
      <div class="kdp-glow kdp-glow-bl"></div>

      <!-- ─── HERO BANNER ─────────────────────────────────── -->
      <div v-if="!hasCustomHero" class="kdp-hero-banner hero">
        <div class="kdp-container">

          <!-- Breadcrumb -->
          <div class="kdp-breadcrumb">
            <Link href="/katalog" class="kdp-bc-link">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
              Katalog Riset
            </Link>
          </div>

          <!-- Badges -->
          <div class="kdp-badges">
            <span v-if="research.ticker" class="kdp-badge-ticker hbadge">{{ research.ticker }}</span>
            <span v-if="research.sector" class="kdp-badge-sector hbadge" v-html="research.sector"></span>
          </div>

          <!-- Title -->
          <h1 class="kdp-h1">{{ research.title }}</h1>

          <!-- Subtitle / tagline -->
          <p v-if="research.subtitle" class="kdp-subtitle hsub" v-html="research.subtitle"></p>

          <!-- Meta row -->
          <div class="kdp-meta-row hmeta">
            <span class="kdp-meta-author">Tim Avenir Research</span>
            <span class="kdp-meta-sep">·</span>
            <span class="kdp-meta-date">{{ research.date }}</span>
            <span v-if="formattedPrice" class="kdp-meta-sep">·</span>
            <span v-if="formattedPrice" class="kdp-meta-price">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              Target: Rp {{ formattedPrice }}
            </span>
          </div>

          <!-- Key Metrics Bar -->
          <div v-if="research.revenue || research.patmi || research.sales" class="kdp-metrics">
            <div v-if="research.revenue" class="kdp-metric">
              <div class="kdp-metric-val">{{ research.revenue }}</div>
              <div class="kdp-metric-lbl">Revenue</div>
            </div>
            <div class="kdp-metric-sep" v-if="research.revenue && research.patmi"></div>
            <div v-if="research.patmi" class="kdp-metric">
              <div class="kdp-metric-val">{{ research.patmi }}</div>
              <div class="kdp-metric-lbl">PATMI</div>
            </div>
            <div class="kdp-metric-sep" v-if="research.patmi && research.sales"></div>
            <div v-if="research.sales" class="kdp-metric">
              <div class="kdp-metric-val">{{ research.sales }}</div>
              <div class="kdp-metric-lbl">Sales</div>
            </div>
          </div>

        </div>
      </div>

      <!-- ─── CONTENT AREA ──────────────────────────────── -->
      <div class="kdp-content-area" :class="{ 'is-custom': hasCustomHtml || hasCustomHero }">
        <div :class="{ 'kdp-container': !hasCustomHtml && !hasCustomHero }">
          <div class="guest-lock-wrap" :class="{ 'is-guest': isLocked }">

            <!-- Article HTML Content -->
            <div
              class="guest-lock-content"
              :class="{ 'db-content': !hasCustomHtml }"
              v-html="research.content"
            />

            <!-- Lock Overlay Gate -->
            <Paywall v-if="isLocked" />
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<style>


/* Guest lock positioning and styles */
.guest-lock-wrap {
  position: relative;
  min-height: 600px;
}

.guest-lock-content {
  filter: none;
  pointer-events: auto;
  user-select: auto;
}

/* When article is locked */
.guest-lock-wrap.is-guest .guest-lock-content {
  filter: blur(8px);
  pointer-events: none;
  user-select: none;
  max-height: 650px;
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
  max-width: 880px;
  margin: 0 auto;
  padding: 0 24px;
  position: relative;
  z-index: 2;
}

/* ── Hero Banner ── */
.kdp-hero-banner {
  padding: 40px 0 48px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

/* Breadcrumb */
.kdp-breadcrumb { margin-bottom: 28px; }
.kdp-bc-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  font-weight: 600;
  color: #10B981;
  text-decoration: none;
  letter-spacing: 0.02em;
  transition: color 0.2s ease;
}
.kdp-bc-link:hover { color: #34d399; }
.kdp-bc-link svg { flex-shrink: 0; }

/* Badges */
.kdp-badges {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 18px;
}
.kdp-badge-ticker {
  font-family: 'Roboto', sans-serif;
  font-size: 11px;
  font-weight: 700;
  color: #10B981;
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.3);
  padding: 4px 12px;
  border-radius: 6px;
  letter-spacing: 0.08em;
}
.kdp-badge-sector {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: #64748b;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  padding: 4px 12px;
  border-radius: 6px;
}

/* Title */
.kdp-h1 {
  font-family: 'Roboto', sans-serif;
  font-size: clamp(26px, 4.5vw, 42px) !important;
  font-weight: 700 !important;
  line-height: 1.2 !important;
  color: #ffffff !important;
  margin: 0 0 16px !important;
  letter-spacing: -0.02em !important;
}

/* Subtitle */
.kdp-subtitle {
  font-size: 15px;
  line-height: 1.7;
  color: #94a3b8;
  margin: 0 0 20px;
  border-left: 3px solid #10B981;
  padding-left: 16px;
  max-width: 720px;
}

/* Meta row */
.kdp-meta-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 4px 12px;
  font-size: 12px;
  color: #64748b;
  margin-bottom: 28px;
}
.kdp-meta-author { color: #94a3b8; font-weight: 600; }
.kdp-meta-sep { color: #334155; }
.kdp-meta-date { font-family: 'Roboto', sans-serif; font-size: 11.5px; }
.kdp-meta-price {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  color: #10B981;
  font-weight: 600;
  font-size: 12px;
}

/* Metrics bar */
.kdp-metrics {
  display: inline-flex;
  align-items: stretch;
  gap: 0;
  background: rgba(16, 185, 129, 0.04);
  border: 1px solid rgba(16, 185, 129, 0.15);
  border-radius: 12px;
  overflow: hidden;
}
.kdp-metric {
  padding: 14px 24px;
  text-align: center;
}
.kdp-metric-sep {
  width: 1px;
  background: rgba(16, 185, 129, 0.15);
  flex-shrink: 0;
}
.kdp-metric-val {
  font-family: 'Roboto', sans-serif;
  font-size: 16px;
  font-weight: 700;
  color: #10B981;
  margin-bottom: 4px;
  white-space: nowrap;
}
.kdp-metric-lbl {
  font-size: 9px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

/* ── Content area ── */
.kdp-content-area {
  padding: 48px 0 80px;
  position: relative;
  z-index: 2;
}
.kdp-content-area.is-custom {
  padding-top: 0;
  background-color: var(--bg, #f8fafc); /* Menggunakan --bg kustom dari HTML/CSS jika ada, fallback ke Slate 50 */
}

/* Guest lock */
.guest-lock-wrap {
  position: relative;
  min-height: 500px;
}
.guest-lock-content {
  filter: none;
  pointer-events: auto;
  user-select: auto;
}
.guest-lock-wrap.is-guest .guest-lock-content {
  filter: blur(8px);
  pointer-events: none;
  user-select: none;
  max-height: 600px;
  overflow: hidden;
  -webkit-mask-image: linear-gradient(180deg, #000 0%, #000 45%, transparent 100%);
  mask-image: linear-gradient(180deg, #000 0%, #000 45%, transparent 100%);
}
.guest-lock-wrap.is-guest .guest-lock-overlay {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  padding: 60px 24px;
  text-align: center;
  background: linear-gradient(180deg, rgba(9,11,10,0) 0%, rgba(9,11,10,0.96) 20%, #090b0a 50%);
  z-index: 100;
}
.gl-icon { font-size: 36px; margin-bottom: 20px; }
.gl-title {
  font-family: 'Roboto', sans-serif;
  font-size: 30px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 14px;
  line-height: 1.25;
  max-width: 560px;
}
.gl-sub {
  font-size: 14.5px;
  color: #94a3b8;
  line-height: 1.65;
  margin-bottom: 20px;
  max-width: 520px;
}
.gl-price {
  display: inline-block;
  padding: 9px 22px;
  background: rgba(16, 185, 129, 0.08);
  border: 1px solid rgba(16, 185, 129, 0.2);
  border-radius: 100px;
  font-size: 13px;
  color: #10B981;
  margin-bottom: 24px;
}
.gl-price strong { color: #fff; font-weight: 700; }
.gl-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; margin-bottom: 18px; }
.gl-btn-primary, .gl-btn-secondary {
  padding: 13px 26px;
  border-radius: 100px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  letter-spacing: .02em;
  transition: all .25s cubic-bezier(0.16, 1, 0.3, 1);
  border: none;
  font-family: inherit;
}
.gl-btn-primary { background: #1B6B3A; color: #fff; box-shadow: 0 4px 14px rgba(27,107,58,.3); }
.gl-btn-primary:hover { background: #10b981; transform: translateY(-1px); }
.gl-btn-secondary { background: transparent; color: #cbd5e1; border: 1.5px solid rgba(255,255,255,.12); }
.gl-btn-secondary:hover { background: rgba(255,255,255,.05); color: #fff; }
.gl-link { font-size: 12px; color: #64748b; }
.gl-link a { color: #10B981; text-decoration: none; font-weight: 600; }
.gl-link a:hover { text-decoration: underline; }

/* ── Fallback content (non-art-page) ── */
.kdp-page:not(.has-custom-content) .guest-lock-content,
.kdp-page:not(.has-custom-content) .db-content {
  font-size: 16px !important; line-height: 1.85 !important;
  color: #cbd5e1 !important;
}
.kdp-page:not(.has-custom-content) .guest-lock-content p,
.kdp-page:not(.has-custom-content) .db-content p {
  margin: 0 0 24px !important;
}
.kdp-page:not(.has-custom-content) .guest-lock-content h2 {
  font-family: 'Roboto', sans-serif !important;
  font-size: 26px !important; font-weight: 600 !important;
  color: #fff; margin: 44px 0 18px !important;
  padding-top: 24px !important;
  border-top: 1px solid rgba(255,255,255,0.05) !important;
}
.kdp-page:not(.has-custom-content) .guest-lock-content strong { color: #fff; }

/* ── HTML art-page content overrides ── */
.kdp-page .art-page {
  font-family: 'Roboto', sans-serif !important;
  padding: 0 !important;
  margin: 0 !important;
  max-width: 100% !important;
}
.kdp-page .art-label {
  display: inline-flex; align-items: center;
  background: rgba(16,185,129,0.1) !important; color: #10B981 !important;
  border: 1px solid rgba(16,185,129,0.25) !important;
  font-size: 10px !important; font-weight: 700 !important;
  letter-spacing: 0.15em !important; text-transform: uppercase !important;
  padding: 5px 13px !important; border-radius: 100px !important;
  margin-bottom: 18px !important;
}
.kdp-page .art-title {
  font-family: 'Roboto', sans-serif;
  font-size: clamp(26px, 4vw, 40px) !important; font-weight: 700 !important;
  color: #fff !important; margin: 0 0 16px !important; letter-spacing: -0.02em !important;
}
.kdp-page .art-deck {
  font-size: 16px !important; line-height: 1.7 !important; color: #94a3b8 !important;
  margin: 0 0 24px !important; border-left: 3px solid #10B981 !important; padding-left: 18px !important;
}
.kdp-page .art-meta {
  display: flex !important; flex-wrap: wrap !important; gap: 8px 20px !important;
  font-size: 12px !important; color: #64748b !important;
  padding-bottom: 24px !important; border-bottom: 1px solid rgba(255,255,255,0.05) !important;
  margin-bottom: 32px !important;
}
.kdp-page .art-meta strong { color: #cbd5e1 !important; font-weight: 600 !important; }
.kdp-page .art-body {
  font-size: 16px !important; line-height: 1.85 !important; color: #cbd5e1 !important;
}
.kdp-page .art-body p { margin: 0 0 22px !important; }
.kdp-page .art-body h2 {
  font-family: 'Roboto', sans-serif;
  font-size: 26px !important; font-weight: 600 !important; color: #fff !important;
  margin: 48px 0 18px !important; padding-top: 24px !important;
  border-top: 1px solid rgba(255,255,255,0.05) !important;
}
.kdp-page .art-body h3 {
  font-family: 'Roboto', sans-serif;
  font-size: 19px !important; font-weight: 600 !important;
  color: #fff !important; margin: 36px 0 14px !important;
}
.kdp-page .art-body strong { color: #fff !important; font-weight: 700 !important; }
.kdp-page .art-callout {
  background: rgba(16,185,129,0.04) !important; border-left: 4px solid #10B981 !important;
  padding: 16px 22px !important; border-radius: 0 12px 12px 0 !important;
  margin: 26px 0 !important; font-size: 14.5px !important;
  line-height: 1.75 !important; color: #34d399 !important;
}
.kdp-page .art-callout-amber { background: rgba(245,158,11,0.04) !important; border-left-color: #fbbf24 !important; color: #fbbf24 !important; }
.kdp-page .art-callout-blue  { background: rgba(59,130,246,0.04) !important;  border-left-color: #60a5fa !important; color: #60a5fa !important; }
.kdp-page .art-table-wrap {
  border: 1px solid rgba(255,255,255,0.05) !important; border-radius: 12px !important;
  background: #111413 !important; margin: 26px 0 !important;
  box-shadow: 0 4px 20px rgba(0,0,0,.3) !important; overflow: auto;
}
.kdp-page .art-table { border: 0 !important; width: 100%; }
.kdp-page .art-table th {
  background: #090b0a !important; color: #fff !important; padding: 11px 16px !important;
  font-size: 10.5px !important; font-weight: 700 !important; letter-spacing: .08em !important;
  text-transform: uppercase !important; border: 1px solid rgba(255,255,255,.05) !important;
}
.kdp-page .art-table td {
  padding: 11px 16px !important; border: 1px solid rgba(255,255,255,.03) !important;
  color: #cbd5e1 !important; font-size: 13.5px !important;
}
.kdp-page .art-table tr:nth-child(even) td { background: rgba(255,255,255,.01) !important; }
.kdp-page .art-table tr.art-hl td { background: rgba(16,185,129,0.08) !important; font-weight: 600 !important; color: #10B981 !important; }
.kdp-page .art-stats {
  display: grid !important; grid-template-columns: repeat(auto-fit, minmax(130px,1fr)) !important;
  gap: 16px !important; margin: 28px 0 !important;
}
.kdp-page .art-stat {
  background: #111413 !important; border: 1px solid rgba(255,255,255,.05) !important;
  color: #fff !important; border-radius: 12px !important;
  padding: 18px !important; box-shadow: 0 4px 15px rgba(0,0,0,.2) !important;
}
.kdp-page .art-stat .sv { font-family: 'Roboto', sans-serif; font-size: 22px !important; font-weight: 700 !important; color: #10B981 !important; margin-bottom: 5px !important; }
.kdp-page .art-stat .sl { font-size: 10px !important; color: #94a3b8 !important; font-weight: 600 !important; letter-spacing: .08em !important; }
.kdp-page .art-toc {
  background: #111413 !important; border: 1px solid rgba(255,255,255,.05) !important;
  border-radius: 12px !important; padding: 22px !important; margin: 28px 0 !important;
}
.kdp-page .art-toc h4 { font-family: 'Roboto', sans-serif; font-size: 11px !important; font-weight: 700 !important; letter-spacing: .12em !important; color: #64748b !important; text-transform: uppercase !important; margin: 0 0 14px !important; }
.kdp-page .art-toc ol { font-size: 14px !important; line-height: 2 !important; color: #cbd5e1 !important; }
.kdp-page .art-toc ol li a { color: #10B981 !important; }
.kdp-page .art-toc ol li a:hover { color: #34d399 !important; text-decoration: underline !important; }
.kdp-page .art-sources { font-size: 12px !important; line-height: 1.8 !important; color: #64748b !important; margin-top: 52px !important; padding-top: 26px !important; border-top: 1px solid rgba(255,255,255,.05) !important; }
.kdp-page .art-sources strong { color: #cbd5e1 !important; }
.kdp-page .art-poin { display: flex !important; gap: 16px !important; padding: 18px !important; background: #111413 !important; border: 1px solid rgba(255,255,255,.03) !important; border-radius: 12px !important; margin: 14px 0 !important; }
.kdp-page .art-poin-num { background: #090b0a !important; color: #10B981 !important; border: 1px solid rgba(16,185,129,.15) !important; font-family: 'Roboto', sans-serif; font-size: 13px !important; font-weight: 700 !important; width: 34px !important; height: 34px !important; border-radius: 50% !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important; }
.kdp-page .art-poin-body { font-size: 14px !important; line-height: 1.75 !important; color: #cbd5e1 !important; }
.kdp-page .photo-band { margin: 36px 0 !important; border-radius: 12px !important; border: 1px solid rgba(255,255,255,.05) !important; width: 100%; }

/* Hero & header in HTML content — hide, since we render it in Vue */
.kdp-page .art-hero { display: none !important; }
.kdp-page .art-title { display: none !important; }
.kdp-page .art-label { display: none !important; }
.kdp-page .art-deck { display: none !important; }
.kdp-page .art-meta { display: none !important; }

/* Responsive */
@media (max-width: 640px) {
  .kdp-hero-banner { padding: 28px 0 36px; }
  .kdp-h1 { font-size: 24px !important; }
  .kdp-metrics { flex-wrap: wrap; }
  .kdp-metric { padding: 12px 16px; }
  .kdp-metric-val { font-size: 14px; }
  .gl-title { font-size: 24px; }
  .gl-btn-primary, .gl-btn-secondary { padding: 11px 20px; font-size: 12.5px; width: 100%; }
  .gl-actions { flex-direction: column; }
}
</style>
