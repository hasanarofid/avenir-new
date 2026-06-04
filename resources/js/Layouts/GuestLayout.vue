<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="min-h-screen bg-[#090b0a] text-slate-100 font-sans antialiased relative overflow-hidden flex flex-col items-center justify-center p-6 selection:bg-emerald-500 selection:text-white">
    <!-- Background Glow Blobs -->
    <div class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] rounded-full bg-emerald-950/15 blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] rounded-full bg-emerald-950/15 blur-[100px] pointer-events-none"></div>

    <!-- Card Container -->
    <div class="w-full sm:max-w-md bg-[#121614]/80 border border-emerald-950/30 backdrop-blur-md shadow-2xl rounded-3xl p-8 relative z-10 space-y-6">
      <!-- Branding -->
      <div class="flex flex-col items-center gap-3">
        <Link href="/" class="hover:scale-105 transition-all">
          <img src="/images/logo.png" class="h-10 w-auto" alt="Avenir" />
        </Link>
        <div class="text-center">
          <h2 class="text-base font-bold text-white tracking-tight uppercase">Avenir Research</h2>
          <p class="text-[10px] text-emerald-400 font-bold uppercase tracking-widest mt-1">Premium Access Panel</p>
        </div>
      </div>

      <!-- Form Content Slot -->
      <div class="space-y-4">
        <slot />
      </div>
    </div>
  </div>
</template>
