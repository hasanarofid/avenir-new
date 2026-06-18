<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    articles: Array
});

const categories = ['Semua', 'Fundamental', 'Makro', 'Sektor', 'Valuasi', 'Belajar Saham', 'Strategi', 'Opini'];
const selectedCategory = ref('Semua');
const searchQuery = ref('');
const sortOrder = ref('Terbaru');

const filteredArticles = computed(() => {
    let result = props.articles || [];
    
    if (selectedCategory.value !== 'Semua') {
        result = result.filter(a => a.category && a.category.toLowerCase() === selectedCategory.value.toLowerCase());
    }
    
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        result = result.filter(a => (a.title && a.title.toLowerCase().includes(q)) || (a.excerpt && a.excerpt.toLowerCase().includes(q)));
    }
    
    // Sort logic
    if (sortOrder.value === 'Terbaru') {
        result = result.sort((a, b) => new Date(b.published_at || 0) - new Date(a.published_at || 0));
    } else {
        result = result.sort((a, b) => new Date(a.published_at || 0) - new Date(b.published_at || 0));
    }
    
    return result;
});

const featuredArticle = computed(() => filteredArticles.value[0]);
const recentArticles = computed(() => filteredArticles.value.slice(1, 5));
const editorPicks = computed(() => (props.articles || []).slice(0, 3)); 
const trendingArticles = computed(() => (props.articles || []).slice(0, 5)); 

const populerTopics = [
  { name: 'IHSG', count: 128 },
  { name: 'Suku Bunga', count: 96 },
  { name: 'Inflasi', count: 82 },
  { name: 'Bank', count: 75 },
  { name: 'Rupiah', count: 64 },
  { name: 'Kredit', count: 58 },
  { name: 'Energi', count: 54 },
  { name: 'Dividen', count: 47 },
  { name: 'AS', count: 45 },
  { name: 'Komoditas', count: 43 },
  { name: 'Teknologi', count: 39 }
];

const newsletterEmail = ref('');
const subscribeNewsletter = () => {
    newsletterEmail.value = '';
    alert('Terima kasih telah berlangganan!');
};

// Mock function for read_time if not provided
const getReadTime = (article) => article.read_time || Math.floor(Math.random() * 5 + 5);
</script>

<template>
  <Head>
    <title>Artikel | AVENIR Research</title>
  </Head>

  <AppLayout>
    <div class="artikel-dashboard">
      <!-- Glow Backdrops -->
      <div class="radial-glow glow-top-left"></div>
      <div class="radial-glow glow-bottom-right"></div>

      <div class="dashboard-container">
        
        <!-- Header area spans full width of container, before layout split -->
        <div class="header-section">
          <h1 class="hero-title">Artikel</h1>
          <p class="hero-desc">Analisis mendalam, edukasi investasi, dan perspektif pasar.</p>
          
          <div class="categories-row">
            <button 
              v-for="cat in categories" 
              :key="cat"
              :class="['category-pill', selectedCategory === cat ? 'active' : '']"
              @click="selectedCategory = cat"
            >
              {{ cat }}
            </button>
          </div>
        </div>

        <div class="main-layout">
          <!-- ==================== LEFT COLUMN ==================== -->
          <div class="main-column">
            
            <!-- Featured Article -->
            <div v-if="featuredArticle" class="featured-article">
              <Link :href="'/artikel/' + featuredArticle.slug" class="featured-inner">
                <div class="featured-image">
                  <img v-if="featuredArticle.cover_image" :src="featuredArticle.cover_image" :alt="featuredArticle.title" />
                  <div v-else class="placeholder-img"></div>
                  <div class="badge-cat" v-if="featuredArticle.category">{{ featuredArticle.category.toUpperCase() }}</div>
                </div>
                <div class="featured-content">
                  <div class="featured-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    Artikel Unggulan
                  </div>
                  <h2 class="featured-title">{{ featuredArticle.title }}</h2>
                  <p class="featured-excerpt">{{ featuredArticle.excerpt }}</p>
                  
                  <div class="author-meta">
                    <div class="author-avatar rounded-full overflow-hidden flex items-center justify-center bg-slate-800">
                      <img v-if="featuredArticle.author?.profile_photo_url" :src="featuredArticle.author.profile_photo_url" alt="Author" class="w-full h-full object-cover" />
                      <img v-else :src="'https://ui-avatars.com/api/?name=' + encodeURIComponent(featuredArticle.author?.name || 'Tim Avenir') + '&background=10B981&color=fff'" alt="Author" />
                    </div>
                    <div class="author-info">
                      <div class="author-name">{{ featuredArticle.author?.name || 'Tim Avenir' }}</div>
                      <div class="author-role">Tim Riset AVENIR</div>
                    </div>
                  </div>
                  
                  <div class="article-meta-bottom">
                    <span>
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                      {{ getReadTime(featuredArticle) }} min read
                    </span>
                    <span>
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                      {{ featuredArticle.published_at || 'Baru Saja' }}
                    </span>
                  </div>
                </div>
              </Link>
            </div>

            <!-- Artikel Terbaru Section -->
            <div class="section-header">
              <h2>Artikel Terbaru</h2>
              <div class="sort-group">
                <label>Urutkan:</label>
                <select v-model="sortOrder">
                  <option value="Terbaru">Terbaru</option>
                  <option value="Terlama">Terlama</option>
                </select>
              </div>
            </div>

            <div class="recent-grid">
              <Link 
                v-for="article in recentArticles" 
                :key="article.id"
                :href="'/artikel/' + article.slug"
                class="recent-card"
              >
                <div class="recent-image">
                  <img v-if="article.cover_image" :src="article.cover_image" :alt="article.title" />
                  <div v-else class="placeholder-img"></div>
                  <div class="badge-cat" v-if="article.category">{{ article.category.toUpperCase() }}</div>
                </div>
                <div class="recent-content">
                  <h3 class="recent-title">{{ article.title }}</h3>
                  <p class="recent-excerpt">{{ article.excerpt }}</p>
                  
                  <div class="author-meta-small">
                    <div class="w-[28px] h-[28px] rounded-full overflow-hidden flex items-center justify-center bg-slate-800 shrink-0">
                      <img v-if="article.author?.profile_photo_url" :src="article.author.profile_photo_url" alt="Author" class="w-full h-full object-cover" />
                      <img v-else :src="'https://ui-avatars.com/api/?name=' + encodeURIComponent(article.author?.name || 'Tim Avenir') + '&background=10B981&color=fff'" alt="Author" class="w-full h-full" />
                    </div>
                    <div>
                      <div class="author-name">{{ article.author?.name || 'Tim Avenir' }}</div>
                      <div class="author-role">Analis Avenir</div>
                    </div>
                  </div>
                  
                  <div class="article-meta-bottom small mt-auto">
                    <span>
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                      {{ getReadTime(article) }} min read
                    </span>
                    <span>
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                      {{ article.published_at || 'Baru Saja' }}
                    </span>
                  </div>
                </div>
              </Link>
            </div>

            <div class="flex justify-center mt-6">
              <button class="load-more-btn">
                Muat lebih banyak artikel
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
              </button>
            </div>

            <!-- Pilihan Editor -->
            <div class="editor-picks-wrapper">
              <div class="editor-picks-header">
                <div class="crown-icon">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="#fbbf24" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14"/></svg>
                </div>
                <div>
                  <h2>Pilihan Editor</h2>
                  <p>Artikel pilihan editor yang wajib Anda baca minggu ini.</p>
                </div>
              </div>
              <div class="editor-picks-grid">
                <Link 
                  v-for="article in editorPicks" 
                  :key="'editor-' + article.id"
                  :href="'/artikel/' + article.slug"
                  class="editor-pick-card"
                >
                  <div class="editor-pick-image">
                    <img v-if="article.cover_image" :src="article.cover_image" :alt="article.title" />
                    <div v-else class="placeholder-img"></div>
                  </div>
                  <div class="editor-pick-content">
                    <div class="badge-cat-text" v-if="article.category">{{ article.category.toUpperCase() }}</div>
                    <h3 class="editor-pick-title">{{ article.title }}</h3>
                    <div class="editor-pick-meta">{{ getReadTime(article) }} min read</div>
                  </div>
                </Link>
              </div>
            </div>

          </div>

          <!-- ==================== RIGHT COLUMN (SIDEBAR) ==================== -->
          <div class="sidebar-column">
            
            <!-- Search -->
            <div class="search-input-wrapper">
              <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <input type="text" v-model="searchQuery" placeholder="Cari artikel..." class="search-input" />
            </div>

            <!-- Trending Artikel -->
            <div class="sidebar-box trending-box">
              <h3 class="sidebar-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0011 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 11-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 002.5 2.5z"/></svg>
                Trending Artikel
              </h3>
              <div class="trending-list">
                <Link 
                  v-for="(article, index) in trendingArticles" 
                  :key="'trend-' + article.id"
                  :href="'/artikel/' + article.slug"
                  class="trending-item"
                >
                  <div class="trending-num">{{ index + 1 }}</div>
                  <div class="trending-image">
                     <img v-if="article.cover_image" :src="article.cover_image" :alt="article.title" />
                     <div v-else class="placeholder-img"></div>
                  </div>
                  <div class="trending-content">
                    <h4>{{ article.title }}</h4>
                    <div class="trending-meta">{{ getReadTime(article) }} min read &middot; {{ article.published_at || 'Baru Saja' }}</div>
                  </div>
                </Link>
              </div>
            </div>

            <!-- Topik Populer -->
            <div class="sidebar-box topics-box">
              <h3 class="sidebar-title">
                <span class="hash-icon">#</span> Topik Populer
              </h3>
              <div class="topics-list">
                <div v-for="topic in populerTopics" :key="topic.name" class="topic-pill">
                  <span class="topic-name">{{ topic.name }}</span>
                  <span class="topic-count">{{ topic.count }}</span>
                </div>
              </div>
            </div>

            <!-- Newsletter -->
            <div class="sidebar-box">
              <h3 class="text-[15px] font-bold text-white flex items-center gap-2 mb-3">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                Newsletter AVENIR
              </h3>
              <p class="text-[13px] text-slate-400 leading-relaxed mb-5">Dapatkan analisis pasar, artikel eksklusif, dan insight terbaru langsung ke inbox Anda.</p>
              
              <form @submit.prevent="subscribeNewsletter" class="flex gap-2 mb-4">
                <input type="email" v-model="newsletterEmail" placeholder="Masukkan email Anda" required class="flex-1 bg-transparent border border-white/10 text-white px-3 py-2 rounded-lg text-[13px] focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 h-[40px] w-full placeholder:text-slate-500" />
                <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 rounded-lg text-[13px] font-semibold transition-colors h-[40px] flex items-center justify-center whitespace-nowrap">Berlangganan</button>
              </form>
              <p class="text-[11px] text-slate-500">Kami menghargai privasi Anda. Unsubscribe kapan saja.</p>
            </div>

          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.artikel-dashboard {
  background-color: #090b0a;
  color: #cbd5e1;
  font-family: 'Roboto', sans-serif;
  min-height: 100vh;
  position: relative;
  overflow-x: hidden;
  padding: 40px 24px 80px;
}

/* Glowing Background Vectors */
.radial-glow {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  filter: blur(140px);
  z-index: 0;
  opacity: 0.6;
}
.glow-top-left {
  top: -100px;
  left: -100px;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, transparent 80%);
}
.glow-bottom-right {
  bottom: -150px;
  right: -150px;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.05) 0%, transparent 80%);
}

.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
  position: relative;
  z-index: 10;
}

.header-section {
  margin-bottom: 32px;
}
.hero-title {
  font-size: 36px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 8px;
}
.hero-desc {
  font-size: 14px;
  color: #94a3b8;
  margin-bottom: 24px;
}

.categories-row {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}
.category-pill {
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #cbd5e1;
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}
.category-pill:hover {
  background: rgba(255, 255, 255, 0.05);
}
.category-pill.active {
  background: #10b981;
  border-color: #10b981;
  color: #fff;
}

.main-layout {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 32px;
}

@media (max-width: 1024px) {
  .main-layout {
    grid-template-columns: 1fr;
  }
}

/* --- Left Column --- */
.main-column {
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 40px;
}

.placeholder-img {
  width: 100%;
  height: 100%;
  background: #1e293b;
}

/* Featured Article */
.featured-inner {
  display: flex;
  background: #111413;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  overflow: hidden;
  text-decoration: none;
  transition: transform 0.2s, border-color 0.2s;
}
.featured-inner:hover {
  transform: translateY(-2px);
  border-color: rgba(16, 185, 129, 0.3);
}

.featured-image {
  flex: 1;
  position: relative;
  min-height: 250px;
}
.featured-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.badge-cat {
  position: absolute;
  top: 16px;
  left: 16px;
  background: rgba(16, 185, 129, 0.2);
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.3);
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.05em;
  backdrop-filter: blur(4px);
}

.featured-content {
  flex: 1;
  padding: 32px;
  display: flex;
  flex-direction: column;
}
.featured-label {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #10b981;
  font-size: 12px;
  font-weight: 600;
  margin-bottom: 12px;
}
.featured-label svg {
  fill: #10b981;
}
.featured-title {
  font-size: 24px;
  font-weight: 700;
  color: #fff;
  line-height: 1.3;
  margin-bottom: 12px;
}
.featured-excerpt {
  font-size: 14px;
  color: #94a3b8;
  line-height: 1.6;
  margin-bottom: 24px;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.author-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 24px;
}
.author-avatar img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
}
.author-name {
  font-size: 14px;
  font-weight: 600;
  color: #fff;
}
.author-role {
  font-size: 12px;
  color: #64748b;
}

.article-meta-bottom {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-top: auto;
  font-size: 12px;
  color: #64748b;
}
.article-meta-bottom span {
  display: flex;
  align-items: center;
  gap: 6px;
}

@media (max-width: 768px) {
  .featured-inner {
    flex-direction: column;
  }
}

/* Section Header */
.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}
.section-header h2 {
  font-size: 20px;
  font-weight: 700;
  color: #fff;
}
.sort-group {
  display: flex;
  align-items: center;
  gap: 8px;
}
.sort-group label {
  font-size: 12px;
  color: #64748b;
}
.sort-group select {
  background: transparent;
  border: none;
  color: #fff;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}
.sort-group select:focus {
  outline: none;
}

/* Recent Grid */
.recent-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 24px;
}
.recent-card {
  background: #111413;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  overflow: hidden;
  text-decoration: none;
  display: flex;
  flex-direction: column;
  transition: transform 0.2s, border-color 0.2s;
}
.recent-card:hover {
  transform: translateY(-2px);
  border-color: rgba(16, 185, 129, 0.3);
}
.recent-image {
  width: 100%;
  aspect-ratio: 16/9;
  position: relative;
}
.recent-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.recent-content {
  padding: 20px;
  display: flex;
  flex-direction: column;
  flex: 1;
}
.recent-title {
  font-size: 16px;
  font-weight: 600;
  color: #fff;
  line-height: 1.4;
  margin-bottom: 8px;
}
.recent-excerpt {
  font-size: 13px;
  color: #94a3b8;
  line-height: 1.5;
  margin-bottom: 16px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.author-meta-small {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
}
.author-meta-small img {
  width: 28px;
  height: 28px;
  border-radius: 50%;
}
.article-meta-bottom.small {
  font-size: 11px;
}

@media (max-width: 640px) {
  .recent-grid {
    grid-template-columns: 1fr;
  }
}

.load-more-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #111413;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #cbd5e1;
  padding: 10px 20px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}
.load-more-btn:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #fff;
}

/* Editor Picks */
.editor-picks-wrapper {
  background: #111413;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 24px;
}
.editor-picks-header {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 24px;
}
.crown-icon {
  background: rgba(251, 191, 36, 0.1);
  padding: 8px;
  border-radius: 8px;
}
.editor-picks-header h2 {
  font-size: 18px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 4px;
}
.editor-picks-header p {
  font-size: 13px;
  color: #94a3b8;
}

.editor-picks-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}
.editor-pick-card {
  display: flex;
  gap: 12px;
  text-decoration: none;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  padding: 12px;
  border-radius: 12px;
  transition: background 0.2s;
}
.editor-pick-card:hover {
  background: rgba(255, 255, 255, 0.05);
}
.editor-pick-image {
  width: 70px;
  height: 70px;
  border-radius: 8px;
  overflow: hidden;
  flex-shrink: 0;
}
.editor-pick-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.editor-pick-content {
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.badge-cat-text {
  font-size: 9px;
  color: #10b981;
  font-weight: 700;
  letter-spacing: 0.05em;
  margin-bottom: 4px;
}
.editor-pick-title {
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  line-height: 1.4;
  margin-bottom: 6px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.editor-pick-meta {
  font-size: 11px;
  color: #64748b;
}

@media (max-width: 768px) {
  .editor-picks-grid {
    grid-template-columns: 1fr;
  }
}

/* --- Right Column --- */
.sidebar-box {
  background: #111413;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
}
.sidebar-title {
  font-size: 15px;
  font-weight: 700;
  color: #fff;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 20px;
}

/* Search */
.search-input-wrapper {
  position: relative;
  margin-bottom: 20px;
}
.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
}
.search-input {
  width: 100%;
  background: #111413;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #fff;
  padding: 12px 16px 12px 40px;
  border-radius: 12px;
  font-size: 13.5px;
  transition: border-color 0.2s;
}
.search-input:focus {
  outline: none;
  border-color: rgba(16, 185, 129, 0.5);
}

/* Trending */
.trending-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.trending-item {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  group: hover;
}
.trending-num {
  font-size: 18px;
  font-weight: 700;
  color: #10b981;
  width: 20px;
  text-align: center;
}
.trending-image {
  width: 60px;
  height: 45px;
  border-radius: 6px;
  overflow: hidden;
  flex-shrink: 0;
}
.trending-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.trending-content h4 {
  font-size: 13px;
  font-weight: 600;
  color: #cbd5e1;
  line-height: 1.4;
  margin-bottom: 4px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  transition: color 0.2s;
}
.trending-item:hover h4 {
  color: #fff;
}
.trending-meta {
  font-size: 11px;
  color: #64748b;
}

/* Topics */
.hash-icon {
  color: #10b981;
  font-weight: 400;
  font-size: 18px;
}
.topics-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}
.topic-pill {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 6px 12px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: #cbd5e1;
  transition: background 0.2s;
  cursor: pointer;
}
.topic-pill:hover {
  background: rgba(255, 255, 255, 0.08);
}
.topic-count {
  color: #64748b;
  font-size: 11px;
}

</style>
