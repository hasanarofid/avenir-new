<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  page: {
    type: Object,
    required: true
  }
});

const contentSection = computed(() => {
  return props.page.sections.find(s => s.key === 'content');
});
</script>

<template>
  <Head :title="page.title" />

  <AppLayout>
    <div class="page-dark-wrapper">
      <!-- Glow Backdrops -->
      <div class="radial-glow glow-top-right"></div>
      <div class="radial-glow glow-bottom-left"></div>

      <div class="generic-page">
        <!-- Header -->
        <div class="page-header">
          <h1>{{ page.title }}</h1>
        </div>

        <!-- Dynamic Content -->
        <div class="page-content" v-if="contentSection && contentSection.content">
          <div v-html="contentSection.content.body || contentSection.content"></div>
        </div>
        <div v-else class="text-center text-slate-500 py-12">
          Belum ada konten untuk halaman ini.
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-dark-wrapper {
  background-color: #090b0a;
  color: #cbd5e1;
  font-family: 'Roboto', sans-serif;
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

.generic-page {
  max-width: 800px;
  margin: 0 auto;
  padding: 40px 24px 100px;
  position: relative;
  z-index: 2;
}

.page-header {
  margin-bottom: 40px;
  padding-bottom: 24px;
  border-bottom: 1px solid rgba(16, 185, 129, 0.15);
}

.page-header h1 {
  font-family: 'Roboto', sans-serif;
  font-size: clamp(28px, 5vw, 40px);
  font-weight: 700;
  line-height: 1.15;
  color: #ffffff;
}

.page-content {
  font-size: 15px;
  line-height: 1.8;
  color: #94a3b8;
}

.page-content :deep(h2) {
  font-size: 24px;
  font-weight: 600;
  color: #ffffff;
  margin-top: 40px;
  margin-bottom: 16px;
}

.page-content :deep(h3) {
  font-size: 20px;
  font-weight: 600;
  color: #f1f5f9;
  margin-top: 32px;
  margin-bottom: 12px;
}

.page-content :deep(p) {
  margin-bottom: 20px;
}

.page-content :deep(ul) {
  list-style-type: disc;
  padding-left: 24px;
  margin-bottom: 24px;
}

.page-content :deep(ol) {
  list-style-type: decimal;
  padding-left: 24px;
  margin-bottom: 24px;
}

.page-content :deep(li) {
  margin-bottom: 8px;
}

.page-content :deep(a) {
  color: #10b981;
  text-decoration: none;
}

.page-content :deep(a:hover) {
  text-decoration: underline;
}

.page-content :deep(strong), .page-content :deep(b) {
  color: #f8fafc;
  font-weight: 600;
}
</style>
