<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { AlertCircle, ShieldAlert, FileQuestion, Wrench, ArrowLeft, Home } from '@lucide/vue'

const props = defineProps({
  status: {
    type: Number,
    required: true,
  },
})

const title = computed(() => {
  return {
    503: 'Website Sedang Dalam Perawatan',
    500: 'Kesalahan Server Internal',
    404: 'Halaman Tidak Ditemukan',
    403: 'Akses Ditolak',
  }[props.status] || 'Terjadi Kesalahan'
})

const description = computed(() => {
  return {
    503: 'Kami sedang melakukan pemeliharaan rutin untuk meningkatkan pengalaman Anda. Silakan kembali lagi nanti.',
    500: 'Ups, sepertinya ada masalah pada server kami. Tim teknis kami sedang memperbaikinya.',
    404: 'Maaf, halaman yang Anda cari tidak dapat ditemukan. Mungkin URL salah atau halaman telah dipindahkan.',
    403: 'Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.',
  }[props.status] || 'Terjadi kesalahan yang tidak terduga pada sistem.'
})

const icon = computed(() => {
  return {
    503: Wrench,
    500: AlertCircle,
    404: FileQuestion,
    403: ShieldAlert,
  }[props.status] || AlertCircle
})
</script>

<template>
  <Head :title="title" />
  
  <div class="min-h-screen bg-[#090b0a] text-slate-100 flex items-center justify-center relative overflow-hidden p-4">
    <!-- Abstract Background Effects -->
    <div class="absolute inset-0 z-0">
      <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-emerald-900/20 rounded-full mix-blend-screen filter blur-[100px] opacity-70 animate-pulse"></div>
      <div class="absolute bottom-1/4 right-1/4 w-[500px] h-[500px] bg-[#090b0a]/80 rounded-full mix-blend-multiply filter blur-[150px]"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 w-full max-w-2xl text-center">
      <!-- Logo -->
      <div class="mb-10 animate-fade-in-down">
        <Link href="/" class="inline-block">
          <img src="/images/logo.png" alt="Avenir Logo" class="h-10 md:h-12 w-auto mx-auto object-contain transition-transform duration-300 hover:scale-105" />
        </Link>
      </div>

      <!-- Glassmorphic Card -->
      <div class="bg-[#121614]/80 backdrop-blur-xl border border-emerald-950/40 rounded-3xl p-8 md:p-12 shadow-2xl shadow-emerald-900/10 animate-fade-in-up">
        
        <!-- Icon Container -->
        <div class="relative mx-auto w-24 h-24 mb-8">
          <div class="absolute inset-0 bg-emerald-500/20 rounded-full animate-ping opacity-75"></div>
          <div class="relative flex items-center justify-center w-full h-full bg-[#090b0a] border border-emerald-500/30 rounded-full shadow-lg shadow-emerald-500/20">
            <component :is="icon" class="w-10 h-10 text-emerald-450" />
          </div>
        </div>

        <!-- Error Info -->
        <h1 class="text-6xl md:text-8xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-200 mb-4 tracking-tighter drop-shadow-sm">
          {{ status }}
        </h1>
        <h2 class="text-xl md:text-2xl font-bold text-white mb-3 tracking-wide">
          {{ title }}
        </h2>
        <p class="text-sm md:text-base text-slate-400 max-w-md mx-auto mb-10 leading-relaxed">
          {{ description }}
        </p>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <button 
            @click="() => window.history.back()"
            class="w-full sm:w-auto px-6 py-3 rounded-xl bg-[#090b0a] text-slate-300 font-semibold text-sm border border-emerald-950/40 hover:bg-[#1a1f1c] hover:text-white hover:border-emerald-800 transition-all duration-300 flex items-center justify-center gap-2"
          >
            <ArrowLeft class="w-4 h-4" />
            Kembali
          </button>
          
          <Link 
            href="/"
            class="w-full sm:w-auto px-6 py-3 rounded-xl bg-emerald-600 text-white font-semibold text-sm hover:bg-emerald-500 shadow-lg shadow-emerald-600/20 hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2"
          >
            <Home class="w-4 h-4" />
            Beranda Utama
          </Link>
        </div>

      </div>

      <!-- Footer / Contact Info -->
      <div class="mt-12 text-xs font-medium text-slate-500 animate-fade-in-up" style="animation-delay: 200ms;">
        <p v-if="status === 503">
          Butuh bantuan mendesak? Hubungi tim support kami.
        </p>
        <p class="mt-4">
          &copy; {{ new Date().getFullYear() }} Avenir. All rights reserved.
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-fade-in-up {
  animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  opacity: 0;
  transform: translateY(20px);
}

.animate-fade-in-down {
  animation: fadeInDown 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  opacity: 0;
  transform: translateY(-20px);
}

@keyframes fadeInUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeInDown {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
