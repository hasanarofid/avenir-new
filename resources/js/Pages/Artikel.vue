<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    articles: Array
});
</script>

<template>
  <AppLayout>
    <div class="artikel-page">
      <!-- Header -->
      <div class="artikel-page-header">
        <div class="artikel-page-tag">Artikel</div>
        <h1>Corporate Stories &amp; Industry Deep Dives</h1>
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
              <span v-if="article.published_at">{{ article.published_at }}</span>
              <span v-if="article.published_at && article.author">&middot;</span>
              <span v-if="article.author" class="author">{{ article.author }}</span>
              <span v-if="article.is_paid" class="badge-paid">Berlangganan</span>
              <span v-else class="badge-free">Gratis</span>
            </div>
          </div>
        </Link>

        <!-- Empty State -->
        <div v-if="!articles || articles.length === 0" class="artikel-empty">
          <div>📭</div>
          <div style="margin-top: 12px; font-size: 16px; font-weight: 600; color: #374151;">Belum ada artikel</div>
          <div style="margin-top: 6px;">Artikel akan ditampilkan di sini setelah ditambahkan.</div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap');

.artikel-page {
  max-width: 1100px;
  margin: 0 auto;
  padding: 48px 24px 80px;
  font-family: 'Inter', sans-serif;
}

/* ── Header ── */
.artikel-page-header {
  margin-bottom: 40px;
  padding-bottom: 24px;
  border-bottom: 1px solid #e5e7eb;
}

.artikel-page-tag {
  display: inline-block;
  background: #1B6B3A;
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  padding: 4px 10px;
  border-radius: 4px;
  margin-bottom: 14px;
}

.artikel-page-header h1 {
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-size: clamp(28px, 4.5vw, 40px);
  font-weight: 600;
  line-height: 1.15;
  color: #0f172a;
  margin: 0 0 8px;
  letter-spacing: -0.015em;
}

.artikel-page-header p {
  font-size: 15px;
  line-height: 1.65;
  color: #64748b;
  margin: 8px 0 0;
  max-width: 780px;
}

/* ── Grid ── */
.artikel-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 24px;
}

/* ── Card ── */
.artikel-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
}

.artikel-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
  border-color: #1B6B3A;
}

/* ── Card Cover ── */
.artikel-card-cover {
  width: 100%;
  aspect-ratio: 16 / 9;
  background: #0f172a;
  overflow: hidden;
  position: relative;
}

.artikel-card-cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.4s;
}

.artikel-card:hover .artikel-card-cover img {
  transform: scale(1.04);
}

.artikel-card-cover-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.artikel-cover-icon {
  font-size: 48px;
  opacity: 0.4;
}

.artikel-badge {
  position: absolute;
  bottom: 10px;
  left: 12px;
  background: rgba(15, 23, 42, 0.75);
  color: #e2e8f0;
  font-size: 10px;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 4px;
  backdrop-filter: blur(4px);
}

/* ── Card Body ── */
.artikel-card-body {
  padding: 20px 22px 22px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.artikel-card-cat {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.12em;
  color: #1B6B3A;
  text-transform: uppercase;
  margin-bottom: 10px;
}

.artikel-card-title {
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-size: 22px;
  font-weight: 600;
  line-height: 1.25;
  color: #0f172a;
  margin: 0 0 12px;
  letter-spacing: -0.01em;
}

.artikel-card-excerpt {
  font-size: 14px;
  line-height: 1.65;
  color: #475569;
  flex: 1;
  margin: 0 0 14px;
  display: -webkit-box;
  -webkit-line-clamp: 5;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* ── Card Meta ── */
.artikel-card-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 6px 12px;
  font-size: 12px;
  color: #94a3b8;
  padding-top: 12px;
  border-top: 1px solid #f1f5f9;
  align-items: center;
}

.artikel-card-meta .author {
  color: #475569;
  font-weight: 500;
}

.badge-free {
  background: #dcfce7;
  color: #15803d;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.04em;
}

.badge-paid {
  background: #fef9c3;
  color: #854d0e;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.04em;
}

/* ── Empty State ── */
.artikel-empty {
  background: #fafaf7;
  border: 2px dashed #d1d5db;
  border-radius: 14px;
  padding: 60px 24px;
  text-align: center;
  color: #94a3b8;
  font-size: 14px;
  grid-column: 1 / -1;
}

@media (max-width: 640px) {
  .artikel-grid {
    grid-template-columns: 1fr;
  }
  .artikel-page {
    padding: 28px 18px 60px;
  }
}
</style>
