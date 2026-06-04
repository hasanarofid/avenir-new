<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    articles: Array
});
</script>

<template>
  <AppLayout>
    <div class="artikel-dark-wrapper">
      <!-- Glow Backdrops -->
      <div class="radial-glow glow-top-right"></div>
      <div class="radial-glow glow-bottom-left"></div>

      <div class="artikel-page">
        <!-- Header -->
        <div class="artikel-page-header">
          <div class="artikel-page-tag"><span class="dot"></span>ARTIKEL &amp; RISET</div>
          <h1>Corporate Stories &amp; <span class="hl">Industry Deep Dives</span></h1>
          <p>Studi kasus, sejarah korporasi, dan analisis industri jangka panjang dari tim Avenir Research. Bebas diakses untuk semua pembaca.</p>
        </div>

        <!-- Grid Artikel -->
        <div class="artikel-grid">
          <Link 
            v-for="article in articles" 
            :key="article.id"
            :href="'/artikel/' + article.slug"
            class="artikel-card"
            :id="'artikel-card-' + article.slug"
          >
            <!-- Cover Image -->
            <div class="artikel-card-cover">
              <img 
                v-if="article.cover_image" 
                :src="article.cover_image" 
                :alt="article.title"
                loading="lazy"
              />
              <div v-else class="artikel-card-cover-placeholder">
                <span class="artikel-cover-icon">📄</span>
              </div>
              <div v-if="article.badge" class="artikel-badge">{{ article.badge }}</div>
            </div>

            <!-- Body -->
            <div class="artikel-card-body">
              <div v-if="article.category" class="artikel-card-cat">{{ article.category }}</div>
              <div class="artikel-card-title">{{ article.title }}</div>
              <div class="artikel-card-excerpt">{{ article.excerpt }}</div>

              <!-- Meta -->
              <div class="artikel-card-meta">
                <div class="meta-left">
                  <span v-if="article.published_at" class="meta-date">{{ article.published_at }}</span>
                  <span v-if="article.published_at && article.author" class="meta-sep">&middot;</span>
                  <span v-if="article.author" class="author">{{ article.author }}</span>
                </div>
                <div class="meta-right">
                  <span v-if="article.is_paid" class="badge-paid">Berlangganan</span>
                  <span v-else class="badge-free">Gratis</span>
                </div>
              </div>
            </div>
          </Link>

          <!-- Empty State -->
          <div v-if="!articles || articles.length === 0" class="artikel-empty">
            <div class="empty-icon">📭</div>
            <h3>Belum ada artikel</h3>
            <p>Artikel akan ditampilkan di sini setelah ditambahkan.</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap');

.artikel-dark-wrapper {
  background-color: #090b0a;
  color: #cbd5e1;
  font-family: 'Inter', sans-serif;
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
  opacity: 0.15;
}
.glow-top-right {
  top: -10%;
  right: -5%;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, #10B981 0%, transparent 70%);
}
.glow-bottom-left {
  bottom: 5%;
  left: -10%;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.8) 0%, transparent 70%);
}

.artikel-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 48px 24px 80px;
  position: relative;
  z-index: 2;
}

/* ── Header ── */
.artikel-page-header {
  margin-bottom: 48px;
  text-align: center;
  position: relative;
  max-width: 800px;
  margin-left: auto;
  margin-right: auto;
}

.artikel-page-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  border: 1px solid rgba(16, 185, 129, 0.2);
  background: rgba(16, 185, 129, 0.05);
  color: #10B981;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  padding: 6px 16px;
  border-radius: 100px;
  margin-bottom: 20px;
}

.artikel-page-tag .dot {
  width: 6px;
  height: 6px;
  background-color: #10B981;
  border-radius: 50%;
  box-shadow: 0 0 8px #10B981;
  animation: pulse-dot 2s infinite;
}

@keyframes pulse-dot {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(0.85); }
}

.artikel-page-header h1 {
  font-family: 'Sora', sans-serif;
  font-size: clamp(32px, 5vw, 48px);
  font-weight: 700;
  line-height: 1.15;
  color: #ffffff;
  margin: 0 0 16px;
  letter-spacing: -0.02em;
}

.artikel-page-header h1 .hl {
  color: #10B981;
  background: linear-gradient(120deg, #10B981 0%, #34d399 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.artikel-page-header p {
  font-size: 16px;
  line-height: 1.65;
  color: #94a3b8;
  margin: 0;
  max-width: 640px;
  margin-left: auto;
  margin-right: auto;
}

/* ── Grid ── */
.artikel-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 28px;
  margin-top: 48px;
}

/* ── Card ── */
.artikel-card {
  background: #111413;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), 
              border-color 0.3s ease, 
              box-shadow 0.3s ease;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  display: flex;
  flex-direction: column;
  position: relative;
  z-index: 2;
  text-decoration: none;
}

.artikel-card:hover {
  transform: translateY(-5px);
  border-color: rgba(16, 185, 129, 0.4);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5), 
              0 0 15px rgba(16, 185, 129, 0.1);
}

/* ── Card Cover ── */
.artikel-card-cover {
  width: 100%;
  aspect-ratio: 16 / 9;
  background: #090b0a;
  overflow: hidden;
  position: relative;
  border-bottom: 1px solid rgba(255, 255, 255, 0.03);
}

.artikel-card-cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.artikel-card:hover .artikel-card-cover img {
  transform: scale(1.05);
}

.artikel-card-cover-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #111413 0%, #161c1a 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.artikel-cover-icon {
  font-size: 40px;
  opacity: 0.3;
}

.artikel-badge {
  position: absolute;
  bottom: 12px;
  left: 12px;
  background: rgba(9, 11, 10, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: #e2e8f0;
  font-size: 11px;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 6px;
  backdrop-filter: blur(8px);
}

/* ── Card Body ── */
.artikel-card-body {
  padding: 24px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.artikel-card-cat {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.15em;
  color: #10B981;
  text-transform: uppercase;
  margin-bottom: 12px;
}

.artikel-card-title {
  font-family: 'Sora', sans-serif;
  font-size: 20px;
  font-weight: 600;
  line-height: 1.3;
  color: #ffffff;
  margin: 0 0 12px;
  letter-spacing: -0.01em;
  transition: color 0.2s ease;
}

.artikel-card:hover .artikel-card-title {
  color: #10B981;
}

.artikel-card-excerpt {
  font-size: 14px;
  line-height: 1.6;
  color: #94a3b8;
  flex: 1;
  margin: 0 0 20px;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* ── Card Meta ── */
.artikel-card-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.04);
  font-size: 12px;
}

.meta-left {
  display: flex;
  align-items: center;
  color: #64748b;
}

.meta-date {
  font-family: 'IBM Plex Mono', monospace;
}

.meta-sep {
  margin: 0 6px;
  color: #475569;
}

.artikel-card-meta .author {
  color: #cbd5e1;
  font-weight: 500;
}

.badge-free {
  background: rgba(16, 185, 129, 0.1);
  color: #10B981;
  border: 1px solid rgba(16, 185, 129, 0.2);
  padding: 3px 10px;
  border-radius: 6px;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.badge-paid {
  background: rgba(245, 158, 11, 0.1);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.2);
  padding: 3px 10px;
  border-radius: 6px;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* ── Empty State ── */
.artikel-empty {
  grid-column: 1 / -1;
  background: #111413;
  border: 1.5px dashed rgba(16, 185, 129, 0.25);
  border-radius: 16px;
  padding: 64px 24px;
  text-align: center;
  max-width: 550px;
  margin: 40px auto 0;
}

.artikel-empty .empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
  opacity: 0.7;
}

.artikel-empty h3 {
  font-family: 'Sora', sans-serif;
  color: #ffffff;
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 8px;
}

.artikel-empty p {
  color: #94a3b8;
  font-size: 14px;
  line-height: 1.5;
  margin: 0;
}

@media (max-width: 768px) {
  .artikel-grid {
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
    gap: 20px;
  }
}

@media (max-width: 640px) {
  .artikel-grid {
    grid-template-columns: 1fr;
  }
  .artikel-page {
    padding: 28px 16px 60px;
  }
  .artikel-page-header h1 {
    font-size: 32px;
  }
}
</style>
