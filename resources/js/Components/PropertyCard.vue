<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    data: Object
});
</script>

<template>
  <div class="card">
    <!-- Cover Image -->
    <div class="card-cover" v-if="data.image">
      <img :src="data.image" :alt="data.title" loading="lazy" />
      <div class="card-cover-overlay" />
    </div>
    <div class="card-cover card-cover-placeholder" v-else>
      <div class="placeholder-icon">📊</div>
    </div>

    <!-- Card Body -->
    <div class="card-body">
      <div class="card-hd">
        <div class="card-meta">
          <span class="sector" v-if="data.sector" v-html="data.sector"></span>
          <span class="ticker" v-if="data.ticker">{{ data.ticker }}</span>
        </div>
        <h2>{{ data.title }}</h2>
        <p class="card-sub" v-html="data.subtitle"></p>
      </div>
      <div class="cdiv"></div>
      <div class="card-m">
        <div v-if="data.revenue">
          <div class="cmv">{{ data.revenue }}</div>
          <div class="cml">Revenue</div>
        </div>
        <div v-if="data.patmi">
          <div class="cmv">{{ data.patmi }}</div>
          <div class="cml">PATMI</div>
        </div>
        <div v-if="data.sales">
          <div class="cmv">{{ data.sales }}</div>
          <div class="cml">Sales</div>
        </div>
        <div v-if="!data.revenue && data.price">
          <div class="cmv">{{ data.price ? `Rp ${data.price}` : 'TBA' }}</div>
          <div class="cml">Harga</div>
        </div>
      </div>
      <div class="cdiv"></div>
      <div class="card-ft">
        <Link :href="data.slug ? `/katalog/${data.slug}` : `/katalog/${data.id}`" class="cta">
          Lihat Detail
        </Link>
      </div>
    </div>
  </div>
</template>

<style scoped>
.card {
  position: relative;
  background-color: #111413;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  z-index: 1;
}

.card:hover {
  border-color: rgba(16, 185, 129, 0.3);
  transform: translateY(-6px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), 0 0 30px rgba(16, 185, 129, 0.05);
}

/* ── Cover Image ── */
.card-cover {
  width: 100%;
  aspect-ratio: 16 / 9;
  overflow: hidden;
  position: relative;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  background: #0d1110;
  flex-shrink: 0;
}

.card-cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.card:hover .card-cover img {
  transform: scale(1.06);
}

.card-cover-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, transparent 40%, rgba(17, 20, 19, 0.7) 100%);
}

.card-cover-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #111413 0%, #161c1a 100%);
}

.placeholder-icon {
  font-size: 36px;
  opacity: 0.25;
}

/* ── Card Body ── */
.card-body {
  padding: 22px 24px 24px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.card-hd {
  flex-grow: 1;
}

.card-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 12px;
}

.sector {
  font-size: 11px;
  color: #9ca3af;
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 0.02em;
}

.ticker {
  font-family: 'IBM Plex Mono', 'Fira Code', monospace;
  font-size: 11px;
  color: #10b981;
  background: rgba(16, 185, 129, 0.07);
  border: 1px solid rgba(16, 185, 129, 0.2);
  padding: 2px 7px;
  border-radius: 4px;
  font-weight: 600;
}

h2 {
  font-family: 'Sora', sans-serif;
  font-size: 18px;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 10px 0;
  line-height: 1.4;
  transition: color 0.2s ease;
}

.card:hover h2 {
  color: #10b981;
}

.card-sub {
  font-size: 13px;
  color: #9ca3af;
  line-height: 1.6;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Deep rendering styling for sub field span formatting */
.card-sub :deep(span) {
  border-radius: 4px;
  padding: 1px 4px;
  font-size: 11.5px;
}

.cdiv {
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  margin: 16px 0;
}

.card-m {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 8px;
  text-align: center;
}

.cmv {
  font-family: 'IBM Plex Mono', monospace;
  font-size: 13px;
  font-weight: 700;
  color: #ffffff;
  overflow-wrap: anywhere;
}

.cml {
  font-size: 10px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  margin-top: 4px;
  letter-spacing: 0.02em;
}

.card-ft {
  margin-top: auto;
}

.cta {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  color: #d1d5db;
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.1);
  text-decoration: none;
  transition: all 0.2s ease;
}

.card:hover .cta {
  border-color: #10b981;
  background: #10b981;
  color: #fff;
  box-shadow: 0 0 15px rgba(16, 185, 129, 0.35);
}
</style>
