<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    newsList: Array
});
</script>

<template>
  <AppLayout>
    <div class="news-dark-wrapper">
      <!-- Glow Backdrops -->
      <div class="radial-glow glow-top-right"></div>
      <div class="radial-glow glow-bottom-left"></div>

      <div class="news-page">
        <!-- Header -->
        <div class="news-page-header">
          <div class="news-page-tag"><span class="dot"></span>MARKET NEWS</div>
          <h1>Update Pasar &amp; <span class="hl">Keterbukaan Informasi</span></h1>
          <p>Kabar terbaru, analisis ringkas aksi korporasi, dan rangkuman data pasar modal Indonesia yang relevan untuk keputusan investasi Anda.</p>
        </div>

        <!-- Grid Berita -->
        <div class="news-grid">
          <Link 
            v-for="item in newsList" 
            :key="item.slug"
            :href="'/news/' + item.slug"
            class="news-card"
            :id="'news-card-' + item.slug"
          >
            <!-- Card Content -->
            <div class="news-card-body">
              <div v-if="item.category" class="news-card-cat">{{ item.category }}</div>
              <h2 class="news-card-title">{{ item.title }}</h2>
              <p class="news-card-excerpt">{{ item.excerpt }}</p>

              <!-- Meta -->
              <div class="news-card-meta">
                <div class="meta-left">
                  <span v-if="item.published_at" class="meta-date">{{ item.published_at }}</span>
                </div>
                <div class="meta-right">
                  <span class="badge-free">Akses Gratis</span>
                </div>
              </div>
            </div>
          </Link>

          <!-- Empty State -->
          <div v-if="!newsList || newsList.length === 0" class="news-empty">
            <div class="empty-icon">📭</div>
            <h3>Belum ada berita</h3>
            <p>Berita terbaru akan ditampilkan di sini setelah dirilis.</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap');

.news-dark-wrapper {
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

.news-page {
  max-width: 900px;
  margin: 0 auto;
  padding: 40px 24px 100px;
  position: relative;
  z-index: 2;
}

.news-page-header {
  text-align: center;
  margin-bottom: 60px;
}

.news-page-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.12em;
  color: #10b981;
  text-transform: uppercase;
  background: rgba(16, 185, 129, 0.1);
  padding: 6px 14px;
  border-radius: 50px;
  margin-bottom: 20px;
}

.news-page-tag .dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: #10b981;
  box-shadow: 0 0 10px #10b981;
}

.news-page-header h1 {
  font-family: 'Sora', sans-serif;
  font-size: clamp(28px, 4vw, 42px);
  font-weight: 700;
  line-height: 1.2;
  color: #f8fafc;
  margin-bottom: 16px;
}

.news-page-header h1 .hl {
  color: #10b981;
  background: linear-gradient(120deg, #10b981 0%, #34d399 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.news-page-header p {
  font-size: 16px;
  color: #94a3b8;
  line-height: 1.6;
  max-width: 650px;
  margin: 0 auto;
}

.news-grid {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.news-card {
  display: block;
  background: #121614;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  overflow: hidden;
  text-decoration: none;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.news-card:hover {
  transform: translateY(-2px);
  border-color: rgba(16, 185, 129, 0.25);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3), 0 0 1px rgba(16, 185, 129, 0.2);
}

.news-card-body {
  padding: 28px;
}

.news-card-cat {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  color: #34d399;
  letter-spacing: 0.05em;
  margin-bottom: 12px;
}

.news-card-title {
  font-family: 'Sora', sans-serif;
  font-size: clamp(18px, 2.5vw, 22px);
  font-weight: 600;
  color: #f8fafc;
  line-height: 1.35;
  margin-bottom: 12px;
  transition: color 0.2s;
}

.news-card:hover .news-card-title {
  color: #10b981;
}

.news-card-excerpt {
  font-size: 14px;
  color: #94a3b8;
  line-height: 1.6;
  margin-bottom: 20px;
}

.news-card-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.04);
}

.meta-left {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 13px;
  color: #64748b;
}

.badge-free {
  font-size: 11px;
  font-weight: 700;
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
  padding: 4px 10px;
  border-radius: 50px;
}

.news-empty {
  text-align: center;
  padding: 80px 20px;
  background: #121614;
  border: 1px dashed rgba(255, 255, 255, 0.08);
  border-radius: 20px;
}

.empty-icon {
  font-size: 40px;
  margin-bottom: 16px;
}

.news-empty h3 {
  font-family: 'Sora', sans-serif;
  font-size: 18px;
  font-weight: 600;
  color: #f8fafc;
  margin-bottom: 8px;
}

.news-empty p {
  font-size: 14px;
  color: #64748b;
}

@media (max-width: 640px) {
  .news-page {
    padding-top: 20px;
  }
  .news-card-body {
    padding: 20px;
  }
}
</style>
