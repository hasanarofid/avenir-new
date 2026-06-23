<script setup>
import { Head, usePage, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Paywall from '@/Components/Paywall.vue';
import { authStore } from '@/Stores/authStore';
import { computed, ref } from 'vue';

const props = defineProps({
    article: {
        type: Object,
        required: true,
    }
});

const page = usePage();
const isLoggedIn = computed(() => !!page.props.auth?.user);

const isLocked = computed(() => {
    return props.article.is_paid && !props.article.is_unlocked;
});

const hasCustomHtml = computed(() => {
    return props.article.content && props.article.content.includes('art-page');
});

// ── Like ──────────────────────────────────────────────────────────────────
const isLiked = ref(props.article.is_liked ?? false);
const likesCount = ref(props.article.likes_count ?? 0);
const isLikeLoading = ref(false);

const handleLike = async () => {
    if (isLikeLoading.value) return;
    isLikeLoading.value = true;
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const res = await fetch(`/interaction/article/${props.article.id}/like`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        });
        const data = await res.json();
        isLiked.value = data.is_liked;
        likesCount.value = data.likes_count;
    } catch (e) {
        console.error('Like error:', e);
    } finally {
        isLikeLoading.value = false;
    }
};

// ── Share ─────────────────────────────────────────────────────────────────
const toastVisible = ref(false);
const toastMessage = ref('');
let toastTimer = null;

const showToast = (msg) => {
    toastMessage.value = msg;
    toastVisible.value = true;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { toastVisible.value = false; }, 2500);
};

const sharesCount = ref(props.article.shares_count ?? 0);

const showComments = ref(false);
const toggleComments = () => {
    showComments.value = !showComments.value;
};

const handleShare = async () => {
    const url   = window.location.href;
    const title = props.article.title ?? 'Avenir Artikel';
    const text  = `${title} — Baca artikel di Avenir`;

    let shared = false;
    if (navigator.share) {
        try {
            await navigator.share({ title, text, url });
            shared = true;
        } catch (e) {}
    } else {
        try {
            await navigator.clipboard.writeText(url);
            showToast('Link berhasil disalin!');
            shared = true;
        } catch (e) {
            showToast('Gagal menyalin link.');
        }
    }

    if (shared) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const res = await fetch(`/interaction/article/${props.article.id}/share`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            sharesCount.value = data.shares_count;
        } catch (e) {}
    }
};

// ── Komentar ──────────────────────────────────────────────────────────────
const commentForm = useForm({
    content: '',
    guest_name: ''
});

const submitComment = () => {
    commentForm.post(`/interaction/article/${props.article.id}/comment`, {
        preserveScroll: true,
        onSuccess: () => commentForm.reset('content'),
    });
};
</script>

<template>
    <Head>
        <title>ArtikelDetail | Avenir</title>
        <meta name="description" content="Avenir - Platform riset dan direktori pasar modal Indonesia yang komprehensif." />
        <meta property="og:title" content="ArtikelDetail | Avenir" />
        <meta property="og:description" content="Avenir - Platform riset dan direktori pasar modal Indonesia yang komprehensif." />
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

  <Head :title="article.title" />

  <AppLayout>
    <div class="article-detail-page">
      <!-- Glow Backdrops -->
      <div class="radial-glow glow-top-right"></div>
      <div class="radial-glow glow-bottom-left"></div>
       <div class="article-container">
        <!-- Back Link -->
        <a href="/artikel" class="back-link">
          <span class="arrow">←</span> Kembali ke Artikel
        </a>
      <div 
        class="guest-lock-wrap" 
        :class="{ 'is-guest': isLocked }"
      >
        <!-- Dynamic Header for DB-seeded articles -->
        <div v-if="!hasCustomHtml" class="db-article-header">
          <div class="db-article-cat" v-if="article.category">{{ article.category }}</div>
          <h1 class="db-article-title">{{ article.title }}</h1>
          <div class="db-article-meta flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-[#1e293b] overflow-hidden border border-white/10 flex items-center justify-center text-slate-500">
              <img v-if="article.author?.profile_photo_url" :src="article.author.profile_photo_url" alt="Author" class="w-full h-full object-cover">
              <img v-else :src="'https://ui-avatars.com/api/?name=' + encodeURIComponent(article.author?.name || (typeof article.author === 'string' ? article.author : 'Tim Avenir Research')) + '&background=10B981&color=fff'" alt="Author" class="w-full h-full object-cover">
            </div>
            <span class="author">Oleh <strong>{{ article.author?.name || (typeof article.author === 'string' ? article.author : 'Tim Avenir Research') }}</strong></span>
            <span class="meta-sep">·</span>
            <span class="date">{{ article.published_at }}</span>
            <span v-if="article.read_time" class="meta-sep">·</span>
            <span v-if="article.read_time" class="read-time">{{ article.read_time }} mnt baca</span>
          </div>
          <img 
            v-if="article.cover_image" 
            :src="article.cover_image" 
            :alt="article.title" 
            class="db-article-hero"
          />

          <!-- Engagement Bar (di bawah cover image) -->
          <div class="kdp-stats-bar-wrapper mb-8">
            <div class="kdp-stats-bar">
              <button
                class="kdp-stat-item kdp-stat-btn"
                :class="{ 'is-active': isLiked }"
                @click="handleLike"
                :disabled="isLikeLoading"
              >
                <svg width="18" height="18" viewBox="0 0 24 24" :fill="isLiked ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                <span><strong>{{ likesCount }}</strong> Suka</span>
              </button>
              <div class="kdp-stat-sep"></div>
              <button
                class="kdp-stat-item kdp-stat-btn"
                @click="toggleComments"
              >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span><strong>{{ article.comments_count ?? 0 }}</strong> Komentar</span>
              </button>
              <div class="kdp-stat-sep"></div>
              <div class="kdp-stat-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <span><strong>{{ article.views_count ?? 0 }}</strong> Tayangan</span>
              </div>
              <div class="kdp-stat-sep"></div>
              <button
                class="kdp-stat-item kdp-stat-btn"
                @click="handleShare"
              >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                <span><strong>{{ sharesCount }}</strong> Bagikan</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Comments Section (Toggled) -->
        <div v-show="showComments" class="comments-card mb-8 max-w-3xl mx-auto">
          <h3 class="comments-title">Komentar</h3>
          
          <form @submit.prevent="submitComment" class="comment-form mb-8">
            <div v-if="!isLoggedIn" class="mb-4">
              <input v-model="commentForm.guest_name" type="text" placeholder="Nama Anda (Guest)" class="w-full bg-[#1e293b] text-white border border-slate-700 rounded-lg px-4 py-2 focus:outline-none focus:border-emerald-500" />
            </div>
            <div class="mb-4">
              <textarea v-model="commentForm.content" rows="3" placeholder="Tulis komentar atau pertanyaan Anda di sini..." class="w-full bg-[#1e293b] text-white border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:border-emerald-500" required></textarea>
              <div v-if="commentForm.errors.content" class="text-red-400 text-sm mt-1">{{ commentForm.errors.content }}</div>
            </div>
            <div class="flex justify-end">
              <button type="submit" :disabled="commentForm.processing" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2 px-6 rounded-lg transition-colors disabled:opacity-50">
                Kirim Komentar
              </button>
            </div>
          </form>

          <div class="comments-list space-y-6">
            <div v-if="article.comments && article.comments.length === 0" class="text-slate-500 text-sm italic">
              Belum ada komentar. Jadilah yang pertama!
            </div>
            <div v-for="comment in article.comments" :key="comment.id" class="comment-item">
              <div class="flex justify-between items-start mb-2">
                <div class="font-bold text-emerald-400">{{ comment.author_name }}</div>
                <div class="text-xs text-slate-500">{{ comment.date }}</div>
              </div>
              <p class="text-slate-300 text-sm whitespace-pre-wrap">{{ comment.content }}</p>
            </div>
          </div>
        </div>

        <!-- Article HTML Content -->
        <div 
          class="guest-lock-content" 
          :class="{ 'db-content': !hasCustomHtml }"
          v-html="article.content"
        />

        <!-- Lock Overlay Gate -->
        <Paywall 
          v-if="isLocked"
          :price="'Setelah trial: mulai <strong>Rp 149.000 / bulan</strong>'"
        />
      </div>

      <!-- Article Engagement & Comments Section -->
      <div v-if="!isLocked || hasCustomHtml" class="article-engagement-section mt-12 pt-8 border-t border-white/10">
        <div class="kdp-stats-bar-wrapper mb-10">
          <div class="kdp-stats-bar">
            <button
              class="kdp-stat-item kdp-stat-btn"
              :class="{ 'is-active': isLiked }"
              @click="handleLike"
              :disabled="isLikeLoading"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" :fill="isLiked ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              <span><strong>{{ likesCount }}</strong> Suka</span>
            </button>
            <div class="kdp-stat-sep"></div>
            <button
              class="kdp-stat-item kdp-stat-btn"
              @click="toggleComments"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              <span><strong>{{ article.comments_count ?? 0 }}</strong> Komentar</span>
            </button>
            <div class="kdp-stat-sep"></div>
            <div class="kdp-stat-item">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <span><strong>{{ article.views_count ?? 0 }}</strong> Tayangan</span>
            </div>
            <div class="kdp-stat-sep"></div>
            <button
              class="kdp-stat-item kdp-stat-btn"
              @click="handleShare"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
              <span><strong>{{ sharesCount }}</strong> Bagikan</span>
            </button>
          </div>
        </div>


      </div>

      </div>

      <!-- Toast Notification -->
      <Transition name="toast">
        <div v-if="toastVisible" class="kdp-toast" role="status" aria-live="polite">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          {{ toastMessage }}
        </div>
      </Transition>
    </div>
  </AppLayout>
</template>

<style>
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
.article-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 24px 100px;
  position: relative;
  z-index: 2;
}
.article-detail-page {
  background-color: #090b0a;
  color: #cbd5e1;
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
  font-family: 'Roboto', sans-serif;
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

/* Stats Bar & Interactions (From Katalog) */
.kdp-stats-bar-wrapper {
  background: #111413;
  border: 1px solid rgba(255,255,255,0.05);
  border-radius: 12px;
  padding: 0 16px;
}
.kdp-stats-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 0;
}
.kdp-stat-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  color: #64748b;
  font-size: 12px;
}
.kdp-stat-item svg {
  color: #94a3b8;
}
.kdp-stat-item strong {
  color: #fff;
  font-size: 13px;
}
.kdp-stat-sep {
  width: 1px;
  height: 32px;
  background: rgba(255,255,255,0.08);
}
.kdp-stat-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  transition: color 0.2s, transform 0.15s;
}
.kdp-stat-btn:hover {
  color: #10B981;
  transform: scale(1.05);
}
.kdp-stat-btn.is-active {
  color: #10B981;
}
.kdp-stat-btn.is-active svg {
  color: #10B981;
}
.kdp-stat-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Comments Section */
.comments-card {
  background: #111413;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}
@media (min-width: 900px) {
  .comments-card {
    padding: 32px;
  }
}
.comments-title {
  font-size: 18px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}
.comment-item {
  padding: 16px;
  background: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.05);
  border-radius: 12px;
}

/* Toast Notification */
.kdp-toast {
  position: fixed;
  bottom: 32px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(16, 185, 129, 0.15);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: #10B981;
  font-size: 13px;
  font-weight: 600;
  padding: 10px 20px;
  border-radius: 999px;
  display: flex;
  align-items: center;
  gap: 8px;
  z-index: 200;
  white-space: nowrap;
  pointer-events: none;
}
.toast-enter-active,
.toast-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(10px);
}

/* ── Dynamic Content Styles Overrides ── */
.article-detail-page .art-page {
  padding: 0 0 80px;
  font-family: 'Roboto', sans-serif !important;
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
  font-family: 'Roboto', sans-serif;
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

.article-detail-page .guest-lock-content,
.article-detail-page .db-content {
  font-size: 16px !important;
  line-height: 1.85 !important;
  color: #cbd5e1 !important;
}

.article-detail-page .guest-lock-content p,
.article-detail-page .db-content p {
  margin: 0 0 24px !important;
}

.article-detail-page .guest-lock-content h2,
.article-detail-page .db-content h2 {
  font-family: 'Roboto', sans-serif !important;
  font-size: 28px !important;
  font-weight: 600 !important;
  color: #ffffff;
  margin: 48px 0 20px !important;
  padding-top: 24px !important;
  border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
}

.article-detail-page .guest-lock-content h3,
.article-detail-page .db-content h3 {
  font-family: 'Roboto', sans-serif !important;
  font-size: 20px !important;
  font-weight: 600 !important;
  color: #ffffff;
  margin: 32px 0 16px !important;
}

.article-detail-page .guest-lock-content strong,
.article-detail-page .db-content strong {
  color: #ffffff;
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
  font-family: 'Roboto', sans-serif;
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
  font-family: 'Roboto', sans-serif;
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
  font-family: 'Roboto', sans-serif;
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
  margin-bottom: 40px;
  font-family: 'Roboto', sans-serif;
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
  font-family: 'Roboto', sans-serif;
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
  font-family: 'Roboto', sans-serif;
}

.db-article-meta .meta-sep {
  color: #475569 !important;
  margin: 0 4px !important;
}

.db-article-hero {
  width: 100% !important;
  height: auto !important;
  max-height: 500px !important;
  object-fit: cover !important;
  border-radius: 16px !important;
  margin-bottom: 40px !important;
  background: #090b0a !important;
  border: 1px solid rgba(255, 255, 255, 0.05) !important;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
}

/* Fallback styles for database-seeded articles that don't have .art-page wrapper */
.article-detail-page .guest-lock-content.db-content {
  padding: 0 0 80px;
}

/* Force inner wrappers from CMS to span full width to match image width */
.article-detail-page .guest-lock-content div,
.article-detail-page .guest-lock-content p,
.article-detail-page .art-page div,
.article-detail-page .art-page p {
  max-width: 100% !important;
}

.article-detail-page .guest-lock-content.db-content p {
  font-size: 16px !important;
  line-height: 1.85 !important;
  color: #cbd5e1 !important;
  margin: 0 0 24px !important;
}

.article-detail-page .guest-lock-content.db-content h2 {
  font-family: 'Roboto', sans-serif;
  font-size: 28px !important;
  font-weight: 600 !important;
  color: #ffffff !important;
  margin: 48px 0 20px !important;
  padding-top: 24px !important;
  border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
}

.article-detail-page .guest-lock-content.db-content h3 {
  font-family: 'Roboto', sans-serif;
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
