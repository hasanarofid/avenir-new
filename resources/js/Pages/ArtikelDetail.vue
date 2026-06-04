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
</script>

<template>
  <Head :title="article.title" />

  <AppLayout>
    <div class="article-detail-page">
      <div 
        class="guest-lock-wrap" 
        :class="{ 'is-guest': isLocked }"
      >
        <!-- Article HTML Content -->
        <div 
          class="guest-lock-content" 
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
/* Global style sheet embedded dynamically by the HTML file's <style> tag */
.article-detail-page {
  background: #F7F5F0;
  min-height: calc(100vh - 52px);
  padding: 40px 0;
}

/* Guest lock positioning and styles */
.guest-lock-wrap {
  position: relative;
  min-height: 600px;
  max-width: 1000px;
  margin: 0 auto;
}

.guest-lock-content {
  filter: none;
  pointer-events: auto;
  user-select: auto;
}

/* When article is locked (is_guest status) */
.guest-lock-wrap.is-guest .guest-lock-content {
  filter: blur(6px);
  pointer-events: none;
  user-select: none;
  max-height: 700px;
  overflow: hidden;
  -webkit-mask-image: linear-gradient(180deg, #000 0%, #000 60%, transparent 100%);
  mask-image: linear-gradient(180deg, #000 0%, #000 60%, transparent 100%);
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
  background: linear-gradient(180deg, rgba(247, 245, 240, 0) 0%, rgba(247, 245, 240, 0.95) 30%, #F7F5F0 60%);
  z-index: 100;
}

.gl-icon {
  font-size: 48px;
  margin-bottom: 16px;
  opacity: 0.6;
}

.gl-title {
  font-family: 'Cormorant Garamond', 'Fraunces', Georgia, serif;
  font-size: 32px;
  font-weight: 700;
  color: #1a1a1a;
  margin-bottom: 12px;
  line-height: 1.2;
  max-width: 600px;
}

.gl-sub {
  font-size: 15px;
  color: #4b5563;
  line-height: 1.65;
  margin-bottom: 16px;
  max-width: 560px;
}

.gl-price {
  display: inline-block;
  padding: 10px 20px;
  background: rgba(27, 107, 58, 0.06);
  border: 1px solid rgba(27, 107, 58, 0.18);
  border-radius: 8px;
  font-size: 13.5px;
  color: #14532d;
  margin-bottom: 24px;
}

.gl-price strong {
  color: #14532d;
  font-weight: 700;
}

.gl-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  justify-content: center;
  margin-bottom: 16px;
}

.gl-btn-primary,
.gl-btn-secondary {
  padding: 14px 28px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 13.5px;
  cursor: pointer;
  letter-spacing: .02em;
  transition: all .2s;
  border: none;
  font-family: inherit;
}

.gl-btn-primary {
  background: #1B6B3A;
  color: #fff;
  box-shadow: 0 4px 12px rgba(27, 107, 58, 0.2);
}

.gl-btn-primary:hover {
  background: #14532d;
  transform: translateY(-1px);
}

.gl-btn-secondary {
  background: #fff;
  color: #1B6B3A;
  border: 1.5px solid #1B6B3A;
}

.gl-btn-secondary:hover {
  background: rgba(27, 107, 58, 0.04);
}

.gl-link {
  font-size: 12.5px;
  color: #6b7280;
}

.gl-link a {
  color: #1B6B3A;
  text-decoration: underline;
  font-weight: 600;
}

@media (max-width: 600px) {
  .gl-title { font-size: 26px; }
  .gl-sub { font-size: 14px; }
  .gl-price { font-size: 12.5px; padding: 8px 14px; }
  .gl-btn-primary, .gl-btn-secondary { padding: 12px 20px; font-size: 13px; width: 100%; }
  .gl-actions { flex-direction: column; }
}
</style>
