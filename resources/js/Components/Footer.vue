<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <footer class="bg-[#090b0a] text-slate-400 border-t border-slate-900/60 font-sans py-12 md:py-16">
    <div class="max-w-[1200px] mx-auto px-6">
      <!-- Top Grid Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 mb-12 text-left">
        
        <!-- Column 1: Branding, Description, and Socials (Col Span 3) -->
        <div class="lg:col-span-3 space-y-6">
          <div class="flex items-center">
            <img src="/images/logo.png" class="h-8 w-auto" alt="Avenir" />
          </div>
          
          <p class="text-xs text-slate-400 leading-relaxed max-w-xs">
            Riset ekuitas independen dengan standar institusi untuk investor cerdas.
          </p>
          
          <!-- Social Icons -->
          <div class="flex items-center gap-3">
            <!-- LinkedIn -->
            <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full border border-slate-800 flex items-center justify-center text-slate-500 hover:text-emerald-400 hover:border-emerald-500/50 hover:bg-emerald-950/20 transition-all duration-200" aria-label="LinkedIn">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                <rect x="2" y="9" width="4" height="12"></rect>
                <circle cx="4" cy="4" r="2"></circle>
              </svg>
            </a>
            <!-- Twitter / X -->
            <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full border border-slate-800 flex items-center justify-center text-slate-500 hover:text-emerald-400 hover:border-emerald-500/50 hover:bg-emerald-950/20 transition-all duration-200" aria-label="Twitter">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
              </svg>
            </a>
            <!-- YouTube -->
            <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full border border-slate-800 flex items-center justify-center text-slate-500 hover:text-emerald-400 hover:border-emerald-500/50 hover:bg-emerald-950/20 transition-all duration-200" aria-label="YouTube">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
                <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
              </svg>
            </a>
            <!-- Instagram -->
            <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full border border-slate-800 flex items-center justify-center text-slate-500 hover:text-emerald-400 hover:border-emerald-500/50 hover:bg-emerald-950/20 transition-all duration-200" aria-label="Instagram">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
              </svg>
            </a>
          </div>
        </div>

        <!-- Columns 2-5: Nav Columns (Col Span 6) -->
        <div class="lg:col-span-6 grid grid-cols-2 md:grid-cols-4 gap-8">
          <!-- Produk -->
          <div class="space-y-4">
            <h4 class="text-xs font-bold text-slate-300 uppercase tracking-widest">PRODUK</h4>
            <ul class="space-y-2.5 text-[13px] text-slate-500">
              <li><Link href="/katalog" class="hover:text-emerald-400 transition-colors">Riset Premium</Link></li>
              <li><Link href="/katalog" class="hover:text-emerald-400 transition-colors">Katalog Riset</Link></li>
              <li><Link href="/disclosure-radar" class="hover:text-emerald-400 transition-colors">Disclosure Radar</Link></li>
              <li><Link href="/emiten" class="hover:text-emerald-400 transition-colors">Emiten Hub</Link></li>
            </ul>
          </div>
          
          <!-- Market -->
          <div class="space-y-4">
            <h4 class="text-xs font-bold text-slate-300 uppercase tracking-widest">MARKET</h4>
            <ul class="space-y-2.5 text-[13px] text-slate-500">
              <li><Link href="/katalog" class="hover:text-emerald-400 transition-colors">Market Overview</Link></li>
              <li><Link href="/katalog" class="hover:text-emerald-400 transition-colors">Sektor &amp; Indeks</Link></li>
              <li><Link href="/news" class="hover:text-emerald-400 transition-colors">Market News</Link></li>
              <li><Link href="/katalog" class="hover:text-emerald-400 transition-colors">Economic Calendar</Link></li>
            </ul>
          </div>

          <!-- Perusahaan -->
          <div class="space-y-4">
            <h4 class="text-xs font-bold text-slate-300 uppercase tracking-widest">PERUSAHAAN</h4>
            <ul class="space-y-2.5 text-[13px] text-slate-500">
              <li><Link href="/tentang" class="hover:text-emerald-400 transition-colors">Tentang Kami</Link></li>
              <li><Link href="/mitra" class="hover:text-emerald-400 transition-colors">Tim Analis</Link></li>
              <li><Link href="/langganan" class="hover:text-emerald-400 transition-colors">Karir</Link></li>
              <li><Link href="/tentang" class="hover:text-emerald-400 transition-colors">Kontak</Link></li>
            </ul>
          </div>

          <!-- Legal -->
          <div class="space-y-4">
            <h4 class="text-xs font-bold text-slate-300 uppercase tracking-widest">LEGAL</h4>
            <ul class="space-y-2.5 text-[13px] text-slate-500">
              <li><Link href="/syarat-penggunaan" class="hover:text-emerald-400 transition-colors">Syarat &amp; Ketentuan</Link></li>
              <li><Link href="/privacy-policy" class="hover:text-emerald-400 transition-colors">Kebijakan Privasi</Link></li>
              <li><Link href="/syarat-penggunaan" class="hover:text-emerald-400 transition-colors">Disclaimer</Link></li>
              <li><Link href="/langganan" class="hover:text-emerald-400 transition-colors">Kode Etik Analis</Link></li>
            </ul>
          </div>
        </div>

        <!-- Column 6: Kontak (Col Span 3) -->
        <div class="lg:col-span-3 space-y-4 lg:pl-6">
          <h4 class="text-xs font-bold text-slate-300 uppercase tracking-widest">KONTAK</h4>
          <ul class="space-y-3.5 text-[13px] text-slate-500">
            <!-- Email -->
            <li class="flex items-center gap-2.5">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-600">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
              <a href="mailto:info@avenir.co.id" class="hover:text-emerald-400 transition-colors">info@avenir.co.id</a>
            </li>
            <!-- Phone -->
            <li class="flex items-center gap-2.5">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-600">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
              </svg>
              <a href="tel:+622130001234" class="hover:text-emerald-400 transition-colors">+62 21 3000 1234</a>
            </li>
            <!-- Location -->
            <li class="flex items-start gap-2.5">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-600 mt-0.5">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                <circle cx="12" cy="10" r="3"></circle>
              </svg>
              <span>Jakarta, Indonesia</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Divider -->
      <div class="border-t border-slate-900/80 my-8"></div>

      <!-- Bottom Disclaimer / Copyright & OJK -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-6 text-xs text-slate-500">
        <span>
          &copy; 2025 Avenir Research. All rights reserved.
        </span>
        
        <!-- OJK Compliance Badge -->
        <div class="flex items-center gap-2 border border-slate-900 rounded-md px-3 py-1.5 bg-slate-950/20">
          <svg viewBox="0 0 100 100" class="w-6 h-6">
            <!-- Custom graphic OJK styling -->
            <circle cx="50" cy="50" r="45" fill="none" stroke="#ef4444" stroke-width="5" />
            <circle cx="50" cy="50" r="35" fill="#ef4444" />
            <path d="M40 50 Q 50 30, 60 50 Q 50 70, 40 50 Z" fill="#ffffff" />
          </svg>
          <div class="text-left leading-none">
            <div class="text-[8px] text-slate-500 uppercase tracking-widest">Terdaftar &amp; Diawasi</div>
            <div class="text-[11px] font-extrabold text-white tracking-wider">OJK</div>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>
