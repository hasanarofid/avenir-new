<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

defineProps({
    article: {
        type: Object,
        required: true
    },
    ticker: {
        type: Object,
        default: null
    }
});
</script>

<template>
  <Head :title="article.title" />

  <MainLayout>
    <article class="bg-white border border-[#DDE7E2] rounded-2xl overflow-hidden shadow-sm">
      <!-- Article Header -->
      <div class="px-8 pt-10 pb-6 border-b border-[#DDE7E2]">
        <div class="flex items-center gap-2 mb-4">
          <span v-if="article.badge" class="px-3 py-1 bg-[#1B6B3A]/10 text-[#1B6B3A] border border-[#1B6B3A]/20 rounded-full text-[10px] font-bold uppercase tracking-wider">
            {{ article.badge }}
          </span>
          <span v-if="article.category" class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-[10px] font-bold uppercase tracking-wider">
            {{ article.category }}
          </span>
        </div>

        <h1 class="font-serif text-3xl md:text-4xl font-bold text-[#18211D] leading-tight mb-4">
          {{ article.title }}
        </h1>
        
        <p v-if="article.excerpt" class="text-[#4A6355] text-base md:text-lg mb-6">
          {{ article.excerpt }}
        </p>

        <div class="flex items-center gap-4 text-xs text-[#8AA696] pt-4 border-t border-[#DDE7E2]">
          <span>Oleh: <strong class="text-[#4A6355]">{{ article.author || 'Tim Avenir Research' }}</strong></span>
          <span v-if="article.published_at">Dipublikasikan pada: {{ new Date(article.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</span>
        </div>
      </div>

      <!-- Ticker Information if exists -->
      <div v-if="ticker" class="bg-[#F7F5F0] border-b border-[#DDE7E2] px-8 py-5 flex items-center justify-between">
        <div>
          <span class="font-mono text-xl font-bold text-[#1B6B3A]">{{ ticker.symbol }}</span>
          <p class="text-xs text-[#4A6355] mt-1">{{ ticker.company_name }}</p>
        </div>
        <div class="text-right">
          <div class="text-sm font-semibold">
            Target Price: <span class="font-mono">{{ ticker.target_price ? 'Rp ' + Number(ticker.target_price).toLocaleString('id-ID') : '-' }}</span>
          </div>
          <span class="inline-block mt-1 px-2 py-1 text-[10px] font-bold uppercase rounded"
            :class="{
              'bg-green-100 text-green-700': ticker.recommendation === 'bullish',
              'bg-red-100 text-red-700': ticker.recommendation === 'bearish',
              'bg-blue-100 text-blue-700': ticker.recommendation === 'neutral' || !ticker.recommendation
            }">
            {{ ticker.recommendation || 'No Rating' }}
          </span>
        </div>
      </div>

      <!-- Article Content -->
      <div class="px-8 py-10 prose prose-slate max-w-none text-[#18211D]" v-html="article.content"></div>
    </article>
  </MainLayout>
</template>

<style scoped>
/* You can add specific typography styles here, or let Tailwind's typography plugin handle it */
:deep(h2) {
  font-family: 'Fraunces', Georgia, serif;
  color: #18211D;
  margin-top: 2rem;
  margin-bottom: 1rem;
}
:deep(p) {
  margin-bottom: 1.25rem;
  line-height: 1.75;
  color: #4A6355;
}
</style>
