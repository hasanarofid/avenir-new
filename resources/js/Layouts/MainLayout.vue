<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="min-h-screen bg-[#090b0a] text-slate-300 font-sans antialiased">
    <!-- Navbar (migrated from nav-avenir.js logic) -->
    <nav class="sticky top-0 z-50 flex items-center h-[52px] px-6 bg-[#090b0a] border-b border-white/5">
      <div class="flex-1 flex justify-start">
        <Link href="/" class="flex items-center">
          <img src="/images/logo.png" class="h-6 w-auto" alt="Avenir" />
        </Link>
      </div>

      <div class="hidden sm:flex flex-1 justify-center gap-1">
        <Link href="/research" class="px-3.5 py-1.5 rounded-full text-xs font-medium text-slate-400 hover:bg-white/5 hover:text-white transition-colors">
          Riset
        </Link>
        <Link href="/news" class="px-3.5 py-1.5 rounded-full text-xs font-medium text-slate-400 hover:bg-white/5 hover:text-white transition-colors">
          Berita
        </Link>
        <Link href="/mitra" class="px-3.5 py-1.5 rounded-full text-xs font-medium text-slate-400 hover:bg-white/5 hover:text-white transition-colors">
          Mitra
        </Link>
      </div>

      <div class="flex-1 flex justify-end gap-2">
        <button class="px-3.5 py-1.5 rounded-full text-xs font-semibold border border-emerald-500/25 text-emerald-450 bg-emerald-950/20 hover:bg-emerald-900/30 transition-colors hidden sm:block">
          Analyst Program
        </button>
        <Link href="/login" class="px-3.5 py-1.5 rounded-full text-xs font-semibold border border-slate-800 text-slate-400 hover:border-slate-700 hover:text-white transition-colors">
          Masuk
        </Link>
        <Link href="/register" class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-[#22c55e] text-black hover:bg-emerald-500 transition-colors">
          Daftar
        </Link>
      </div>
    </nav>

    <!-- Page Content -->
    <main class="max-w-4xl mx-auto py-8 px-6">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-[#121614] border-t border-white/5 py-8 px-6 text-center text-slate-500">
      <p class="text-xs mb-2">© 2026 PT Avenir Fortuna Corporindo. All Rights Reserved.</p>
      <div class="flex justify-center gap-4 text-[10px]">
        <Link href="/privacy" class="hover:text-slate-350 transition-colors">Kebijakan Privasi</Link>
        <Link href="/terms" class="hover:text-slate-350 transition-colors">Syarat & Ketentuan</Link>
      </div>
    </footer>
  </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Roboto:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Roboto:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap');
</style>
