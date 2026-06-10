<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    data: Object
});
</script>

<template>
  <div class="card">
    <Link :href="data.slug ? `/katalog/${data.slug}` : `/katalog/${data.id}`" class="card-link">
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
          <div class="badges">
            <span class="badge bnew" v-if="data.date">✓ {{ data.date }}</span>
          </div>
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
        
        <div class="cdiv" v-if="data.tags"></div>
        
        <div class="card-tags" v-if="data.tags" v-html="data.tags"></div>
        
        <div class="cdiv"></div>
        
        <div class="card-ft">
          <div class="ft-authors">
            <span class="ft-author-badge">● Tim Avenir</span>
            <span class="ft-new-badge">📊 Riset Baru</span>
          </div>
          <div class="ft-date">📅 {{ data.date || 'Terbaru' }}</div>
          
          <div class="ft-icons">
            <span>♡ 0</span>
            <span>💬 0</span>
            <span>🔗</span>
          </div>
          
          <div class="ft-comment-box">
            <span class="comment-icon">💬</span>
            <div class="comment-text">
              <strong>Belum ada komentar</strong>
              <span>Jadi yang pertama mendiskusikan riset ini →</span>
            </div>
          </div>
          
          <div class="cta">
            BACA RISET →
          </div>
        </div>
      </div>
    </Link>
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

.card-link {
  text-decoration: none;
  display: flex;
  flex-direction: column;
  height: 100%;
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
  padding: 16px 20px 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.card-hd {
  flex-grow: 1;
}

.badges {
  display: flex;
  gap: 5px;
  flex-wrap: wrap;
  margin-bottom: 12px;
}

.badge {
  font-size: 9.5px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 100px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.03);
  color: #9ca3af;
}

.badge.bnew {
  color: #10b981;
  background: rgba(16, 185, 129, 0.08);
  border-color: rgba(16, 185, 129, 0.2);
}

.card-meta {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 10px;
}

.sector {
  font-size: 10.5px;
  color: #9ca3af;
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 0.02em;
}

.ticker {
  font-family: 'Roboto', sans-serif;
  font-size: 10.5px;
  color: #10b981;
  background: rgba(16, 185, 129, 0.07);
  border: 1px solid rgba(16, 185, 129, 0.2);
  padding: 2px 7px;
  border-radius: 4px;
  font-weight: 600;
  margin-left: auto;
}

h2 {
  font-family: 'Roboto', sans-serif;
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

.card-sub :deep(span) {
  border-radius: 4px;
  padding: 1px 4px;
  font-size: 11.5px;
}

.cdiv {
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  margin: 14px 0;
}

.card-m {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 8px;
  text-align: center;
}

.cmv {
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  font-weight: 700;
  color: #10b981;
  overflow-wrap: anywhere;
}

.cml {
  font-size: 9.5px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  margin-top: 4px;
  letter-spacing: 0.02em;
}

.card-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.card-tags :deep(.tag), .card-tags :deep(span) {
  font-size: 9.5px;
  padding: 3px 8px;
  border-radius: 100px;
  font-weight: 600;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.03);
  color: #9ca3af;
}

.card-ft {
  margin-top: auto;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.ft-authors {
  display: flex;
  gap: 8px;
}

.ft-author-badge, .ft-new-badge {
  font-size: 10px;
  padding: 3px 8px;
  border-radius: 100px;
  font-weight: 600;
}

.ft-author-badge {
  background: rgba(255, 255, 255, 0.05);
  color: #cbd5e1;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.ft-new-badge {
  background: rgba(16, 185, 129, 0.08);
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.ft-date {
  font-size: 11px;
  color: #6b7280;
}

.ft-icons {
  display: flex;
  justify-content: space-between;
  padding: 0 8px;
  color: #6b7280;
  font-size: 12px;
}

.ft-comment-box {
  display: flex;
  gap: 10px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px dashed rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 12px;
  align-items: center;
}

.comment-icon {
  font-size: 16px;
  opacity: 0.5;
}

.comment-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.comment-text strong {
  font-size: 11px;
  color: #9ca3af;
}

.comment-text span {
  font-size: 10px;
  color: #6b7280;
}

.cta {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  color: #ffffff;
  background: #1B6B3A;
  border: none;
  text-decoration: none;
  transition: all 0.2s ease;
  letter-spacing: 0.05em;
}

.card:hover .cta {
  background: #10b981;
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
}
</style>
