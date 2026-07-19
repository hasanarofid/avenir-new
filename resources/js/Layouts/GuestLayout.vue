<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="min-h-screen bg-[#090b0a] text-slate-100 font-sans antialiased flex flex-col lg:flex-row selection:bg-emerald-500 selection:text-white">
    <!-- Left Side: Branding & Value Prop (Hidden on small screens) -->
    <div class="hidden lg:flex lg:w-1/2 relative flex-col justify-between p-12 overflow-hidden border-r border-emerald-950/40 bg-[#0c0e0d]">
      <!-- Background Glow -->
      <div class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] rounded-full bg-emerald-900/20 blur-[120px] pointer-events-none"></div>
      <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] rounded-full bg-emerald-900/10 blur-[120px] pointer-events-none"></div>

      <div class="relative z-10">
        <Link href="/" class="inline-block hover:scale-105 transition-transform duration-300">
          <img src="/images/logo.png" class="h-10 w-auto drop-shadow-md" alt="Avenir" />
        </Link>
      </div>

      <div class="relative z-10 max-w-lg mt-12 mb-auto">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 mb-6">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest">Premium Access Panel</span>
        </div>
        <h1 class="text-4xl sm:text-5xl font-bold text-white tracking-tight mb-6 leading-tight">
          Navigating Markets <br/><span class="text-emerald-500">with Clarity.</span>
        </h1>
        <p class="text-slate-400 text-sm sm:text-base leading-relaxed mb-10">
          Avenir Research provides premium access to data-driven equity research, real-time market intelligence, and institutional-grade analytics.
        </p>

        <div class="space-y-5">
          <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-[#121614] border border-emerald-950/50 text-emerald-400 shadow-inner">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <span class="text-sm font-medium text-slate-300">Deep-dive Company Profiles</span>
          </div>
          <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-[#121614] border border-emerald-950/50 text-emerald-400 shadow-inner">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <span class="text-sm font-medium text-slate-300">Real-time Desk Briefs & AI Analytics</span>
          </div>
          <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-[#121614] border border-emerald-950/50 text-emerald-400 shadow-inner">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <span class="text-sm font-medium text-slate-300">Historical EOD Data & Regime Scores</span>
          </div>
        </div>
      </div>

      <div class="relative z-10 text-xs text-slate-500 font-medium">
        &copy; {{ new Date().getFullYear() }} Avenir Research. All rights reserved.
      </div>
    </div>

    <!-- Right Side: Form Content -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 relative overflow-hidden">
      <!-- Mobile Background Glow -->
      <div class="absolute top-[-10%] right-[-10%] w-[400px] h-[400px] rounded-full bg-emerald-900/15 blur-[100px] pointer-events-none lg:hidden"></div>
      
      <div class="w-full sm:max-w-md bg-[#121614]/80 lg:bg-transparent lg:border-none border border-emerald-950/40 backdrop-blur-xl rounded-3xl p-8 lg:p-0 relative z-10 shadow-2xl lg:shadow-none">
        <!-- Mobile Branding -->
        <div class="flex flex-col items-center gap-3 mb-8 lg:hidden">
          <Link href="/" class="hover:scale-105 transition-all">
            <img src="/images/logo.png" class="h-10 w-auto" alt="Avenir" />
          </Link>
          <div class="text-center">
            <h2 class="text-base font-bold text-white tracking-tight uppercase">Avenir Research</h2>
            <p class="text-[10px] text-emerald-400 font-bold uppercase tracking-widest mt-1">Premium Access Panel</p>
          </div>
        </div>

        <!-- Form Slot -->
        <slot />
      </div>
    </div>
  </div>
</template>
