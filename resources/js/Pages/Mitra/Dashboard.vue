<script setup>
import MitraLayout from '@/Layouts/MitraLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { FileText, TrendingUp, UserPlus, Wallet, Coins, Heart, MessageSquare, Share2 } from '@lucide/vue';
import { computed } from 'vue';

const page = usePage();

const props = defineProps({
  researches: Array,
  articles: Array,
  monthlyIncome: Number,
  cumulativeIncome: Number,
  currentPeriod: String,
});

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(number || 0);
};

const user = computed(() => page.props.auth.user);
const partner = computed(() => user.value?.partner);
</script>

<template>
  <Head title="Dashboard Mitra" />

  <MitraLayout>
    <div class="space-y-6 pb-12">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-emerald-950/30 pb-4">
        <div>
          <h2 class="text-2xl font-extrabold tracking-tight text-white flex items-center gap-2">
            Dashboard Mitra
          </h2>
          <p class="text-sm text-slate-400 mt-1">Selamat datang di panel Mitra Analis Avenir</p>
        </div>
      </div>

      <!-- Not Registered Prompt -->
      <div v-if="!partner" class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-6">
        <div class="flex items-center gap-3">
          <UserPlus class="w-6 h-6 text-amber-400" />
          <div>
            <h3 class="text-lg font-bold text-white">Anda belum terdaftar sebagai Mitra Analis</h3>
            <p class="text-sm text-slate-400 mt-1">Daftarkan diri Anda untuk mulai berkontribusi dan mendapatkan bagian dari pool pendapatan!</p>
          </div>
        </div>
        <div class="mt-4">
          <Link :href="route('mitra.register')" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500/20 border border-amber-500/30 text-amber-400 rounded-lg text-sm font-semibold hover:bg-amber-500/30 transition-colors">
            Daftar Sekarang
          </Link>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Research</p>
              <p class="text-2xl font-bold text-white mt-1">{{ researches.length }}</p>
            </div>
            <div class="p-3 bg-emerald-500/10 rounded-xl">
              <TrendingUp class="w-6 h-6 text-emerald-400" />
            </div>
          </div>
        </div>
        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Artikel</p>
              <p class="text-2xl font-bold text-white mt-1">{{ articles.length }}</p>
            </div>
            <div class="p-3 bg-emerald-500/10 rounded-xl">
              <FileText class="w-6 h-6 text-emerald-400" />
            </div>
          </div>
        </div>
        <!-- Revenue Cards -->
        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 relative overflow-hidden">
          <div class="absolute -right-4 -bottom-4 opacity-5">
            <Coins class="w-24 h-24" />
          </div>
          <div class="flex items-center justify-between relative z-10">
            <div>
              <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Pendapatan {{ currentPeriod }}</p>
              <p class="text-2xl font-bold text-emerald-400 mt-1">{{ formatRupiah(monthlyIncome) }}</p>
            </div>
            <div class="p-3 bg-emerald-500/10 rounded-xl">
              <Coins class="w-6 h-6 text-emerald-400" />
            </div>
          </div>
        </div>
        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 relative overflow-hidden">
          <div class="absolute -right-4 -bottom-4 opacity-5">
            <Wallet class="w-24 h-24" />
          </div>
          <div class="flex items-center justify-between relative z-10">
            <div>
              <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Akumulasi Pendapatan</p>
              <p class="text-2xl font-bold text-white mt-1">{{ formatRupiah(cumulativeIncome) }}</p>
            </div>
            <div class="p-3 bg-slate-800 rounded-xl">
              <Wallet class="w-6 h-6 text-slate-400" />
            </div>
          </div>
        </div>
      </div>

      <!-- Latest Content -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6">
          <h3 class="text-lg font-bold text-white mb-4">Research Terbaru</h3>
          <div class="space-y-3" v-if="researches.length">
            <div v-for="research in researches.slice(0, 5)" :key="research.id" class="p-4 border border-emerald-950/30 rounded-xl hover:border-emerald-500/30 transition-colors bg-[#0a0d0b]">
              <div class="flex justify-between items-start">
                <p class="text-sm font-semibold text-white leading-snug pr-4">{{ research.title }}</p>
                <p class="text-xs text-slate-500 whitespace-nowrap">{{ new Date(research.created_at).toLocaleDateString('id-ID') }}</p>
              </div>
              <div class="flex items-center gap-4 mt-3 text-xs text-slate-400">
                <div class="flex items-center gap-1.5" title="Suka">
                  <Heart class="w-3.5 h-3.5" /> <span>{{ research.likes_count || 0 }}</span>
                </div>
                <div class="flex items-center gap-1.5" title="Komentar">
                  <MessageSquare class="w-3.5 h-3.5" /> <span>{{ research.comments_count || 0 }}</span>
                </div>
                <div class="flex items-center gap-1.5" title="Bagikan">
                  <Share2 class="w-3.5 h-3.5" /> <span>{{ research.shares_count || 0 }}</span>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8">
            <p class="text-sm text-slate-500">Belum ada research</p>
          </div>
        </div>

        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6">
          <h3 class="text-lg font-bold text-white mb-4">Artikel Terbaru</h3>
          <div class="space-y-3" v-if="articles.length">
            <div v-for="article in articles.slice(0, 5)" :key="article.id" class="p-4 border border-emerald-950/30 rounded-xl hover:border-emerald-500/30 transition-colors bg-[#0a0d0b]">
              <div class="flex justify-between items-start">
                <p class="text-sm font-semibold text-white leading-snug pr-4">{{ article.title }}</p>
                <p class="text-xs text-slate-500 whitespace-nowrap">{{ new Date(article.created_at).toLocaleDateString('id-ID') }}</p>
              </div>
              <div class="flex items-center gap-4 mt-3 text-xs text-slate-400">
                <div class="flex items-center gap-1.5" title="Suka">
                  <Heart class="w-3.5 h-3.5" /> <span>{{ article.likes_count || 0 }}</span>
                </div>
                <div class="flex items-center gap-1.5" title="Komentar">
                  <MessageSquare class="w-3.5 h-3.5" /> <span>{{ article.comments_count || 0 }}</span>
                </div>
                <div class="flex items-center gap-1.5" title="Bagikan">
                  <Share2 class="w-3.5 h-3.5" /> <span>{{ article.shares_count || 0 }}</span>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8">
            <p class="text-sm text-slate-500">Belum ada artikel</p>
          </div>
        </div>
      </div>
    </div>
  </MitraLayout>
</template>
