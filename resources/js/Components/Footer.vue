<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <footer class="bg-[#090b0a] text-slate-400 border-t border-slate-900/60 font-sans py-12 md:py-16">
    <div class="max-w-7xl mx-auto px-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 md:gap-12 mb-12 text-left">
        <!-- Column 1: Branding -->
        <div class="space-y-4">
          <h3 class="text-white font-bold text-lg tracking-tight">Avenir Research</h3>
          <p class="text-xs text-slate-400 leading-relaxed max-w-xs">
            Riset ekuitas independen oleh PT Avenir Fortuna Corporindo. Berbasis laporan keuangan audited.
          </p>
        </div>

        <!-- Column 2: Riset Links -->
        <div class="space-y-4">
          <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Riset</h4>
          <ul class="space-y-2 text-xs">
            <li>
              <Link href="/katalog" class="hover:text-emerald-400 transition-colors">Katalog Riset</Link>
            </li>
            <li>
              <Link href="/artikel" class="hover:text-emerald-400 transition-colors">Market News</Link>
            </li>
          </ul>
        </div>

        <!-- Column 3: Perusahaan Links -->
        <div class="space-y-4">
          <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Perusahaan</h4>
          <ul class="space-y-2 text-xs">
            <li>
              <Link href="/tentang" class="hover:text-emerald-400 transition-colors">Tentang Avenir</Link>
            </li>
            <li>
              <Link href="/mitra" class="hover:text-emerald-400 transition-colors">Mitra Terverifikasi</Link>
            </li>
            <li>
              <Link href="/langganan" class="hover:text-emerald-400 transition-colors">Panduan Pengguna</Link>
            </li>
          </ul>
        </div>

        <!-- Column 4: Kontak Details -->
        <div class="space-y-4">
          <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Kontak</h4>
          <ul class="space-y-2 text-xs text-slate-400">
            <li>
              <a href="https://avenirfortuna.com" target="_blank" rel="noopener noreferrer" class="hover:text-emerald-400 transition-colors font-medium">
                avenirfortuna.com
              </a>
            </li>
            <li class="text-slate-300">PT Avenir Fortuna Corporindo</li>
            <li class="text-slate-500">IDX Tower, Jakarta</li>
          </ul>
        </div>
      </div>

      <!-- Divider -->
      <div class="border-t border-slate-900/80 my-8"></div>

      <!-- Bottom Disclaimer / Copyright -->
      <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-500">
        <span>
          &copy; {{ new Date().getFullYear() }} PT Avenir Fortuna Corporindo. Semua hak dilindungi.
        </span>
        <span class="text-slate-600 text-center md:text-right">
          Riset bersifat informatif & edukatif — bukan rekomendasi investasi.
        </span>
      </div>
    </div>
  </footer>
</template>
