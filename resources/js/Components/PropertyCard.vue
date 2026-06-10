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
  background-color: #fff;
  border: 1.5px solid #DDE7E2;
  border-radius: 14px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
  z-index: 1;
  box-shadow: 0 2px 16px rgba(0,0,0,.07);
}

.card:hover {
  border-color: #1B6B3A;
  transform: translateY(-4px);
  box-shadow: 0 6px 28px rgba(0,0,0,.10);
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
  border-bottom: 1px solid #DDE7E2;
  background: #E8E5DF;
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
  display: none;
}

.card-cover-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #F0EDE8 0%, #E8E5DF 100%);
}

.placeholder-icon {
  font-size: 36px;
  opacity: 0.15;
}

/* ── Card Body ── */
.card-body {
  padding: 20px 22px 14px;
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
  margin-bottom: 9px;
}

.badge {
  font-size: 9.5px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 100px;
  border: 1.5px solid;
}

.badge.bnew {
  color: #1A7A3C;
  background: rgba(26,122,60,.08);
  border-color: rgba(26,122,60,.2);
}

.card-meta {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 9px;
}

.sector {
  font-size: 10px;
  font-weight: 700;
  color: #4A6355;
  background: #F0EDE8;
  border: 1px solid #DDE7E2;
  padding: 2px 9px;
  border-radius: 100px;
  display: inline-block;
}

.ticker {
  font-family: 'Courier New', monospace;
  font-size: 10.5px;
  padding: 2px 9px;
  border-radius: 5px;
  border: 1.5px solid;
  margin-left: auto;
  color: #1B6B3A;
  background: rgba(27,107,58,.06);
  border-color: rgba(27,107,58,.2);
  font-weight: 700;
}

h2 {
  font-family: Georgia, 'Times New Roman', serif;
  font-size: 18px;
  font-weight: 700;
  color: #18211D;
  margin: 0 0 6px 0;
  line-height: 1.3;
  clear: both;
}

.card-sub {
  font-size: 12px;
  color: #4A6355;
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
  height: 1px;
  background: #DDE7E2;
  margin: 14px -22px;
}

.card-m {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 8px;
  text-align: left;
}

.cmv {
  font-family: 'Courier New', monospace;
  font-size: 13.5px;
  font-weight: 700;
  color: #18211D;
  overflow-wrap: anywhere;
}

.cml {
  font-size: 9px;
  color: #8EA899;
  text-transform: uppercase;
  letter-spacing: .5px;
  margin-top: 2px;
}

.card-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  padding-top: 4px;
}

.card-tags :deep(.tag), .card-tags :deep(span) {
  font-size: 9.5px;
  padding: 2px 8px;
  border-radius: 100px;
  font-weight: 600;
  border: 1px solid;
  background: rgba(26,122,60,.07);
  color: #1A7A3C;
  border-color: rgba(26,122,60,.18);
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
  background: rgba(26, 122, 60, 0.05);
  color: #1A7A3C;
  border: 1px solid rgba(26, 122, 60, 0.1);
}

.ft-new-badge {
  background: rgba(26, 122, 60, 0.08);
  color: #1A7A3C;
  border: 1px solid rgba(26, 122, 60, 0.2);
}

.ft-date {
  font-size: 11px;
  color: #8EA899;
}

.ft-icons {
  display: flex;
  justify-content: space-between;
  padding: 0 8px;
  color: #4A6355;
  font-size: 12px;
}

.ft-comment-box {
  display: flex;
  gap: 10px;
  background: #F7F5F0;
  border: 1px dashed #DDE7E2;
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
  color: #18211D;
}

.comment-text span {
  font-size: 10px;
  color: #4A6355;
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
  background: #114523;
  box-shadow: 0 4px 15px rgba(27, 107, 58, 0.35);
}
</style>
