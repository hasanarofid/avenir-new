<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="min-h-screen bg-[#F7F5F0] text-[#18211D] font-sans antialiased">
    <!-- Navbar (migrated from nav-avenir.js logic) -->
    <nav class="sticky top-0 z-50 flex items-center h-[52px] px-6 bg-[#F7F5F0] border-b border-[#DDE7E2]">
      <div class="flex-1 flex justify-start">
        <Link href="/" class="flex items-center">
          <span class="font-bold text-lg text-[#1B6B3A]">Avenir Research</span>
        </Link>
      </div>

      <div class="hidden sm:flex flex-1 justify-center gap-1">
        <Link href="/research" class="px-3.5 py-1.5 rounded-full text-xs font-medium text-[#4A6355] hover:bg-[#F0EDE8] hover:text-[#18211D] transition-colors">
          Riset
        </Link>
        <Link href="/news" class="px-3.5 py-1.5 rounded-full text-xs font-medium text-[#4A6355] hover:bg-[#F0EDE8] hover:text-[#18211D] transition-colors">
          Berita
        </Link>
        <Link href="/mitra" class="px-3.5 py-1.5 rounded-full text-xs font-medium text-[#4A6355] hover:bg-[#F0EDE8] hover:text-[#18211D] transition-colors">
          Mitra
        </Link>
      </div>

      <div class="flex-1 flex justify-end gap-2">
        <button class="px-3.5 py-1.5 rounded-full text-xs font-semibold border border-[#8B6914] text-[#8B6914] bg-[#8B6914]/10 hover:bg-[#8B6914]/20 transition-colors hidden sm:block">
          Analyst Program
        </button>
        <Link href="/login" class="px-3.5 py-1.5 rounded-full text-xs font-semibold border border-gray-300 text-[#4A6355] hover:border-[#8B6914] hover:text-[#8B6914] transition-colors">
          Masuk
        </Link>
        <Link href="/register" class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-[#1B6B3A] text-white hover:bg-[#3DAA6E] transition-colors">
          Daftar
        </Link>
      </div>
    </nav>

    <!-- Page Content -->
    <main class="max-w-4xl mx-auto py-8 px-6">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-[#18211D] border-t border-[#DDE7E2] py-8 px-6 text-center text-white/50">
      <p class="text-xs mb-2">© 2026 PT Avenir Fortuna Corporindo. All Rights Reserved.</p>
      <div class="flex justify-center gap-4 text-[10px]">
        <Link href="/privacy" class="hover:text-white transition-colors">Kebijakan Privasi</Link>
        <Link href="/terms" class="hover:text-white transition-colors">Syarat & Ketentuan</Link>
      </div>
    </footer>
  </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;600;700&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,300..600&family=DM+Sans:wght@300;400;500;600;700&display=swap');
</style>
