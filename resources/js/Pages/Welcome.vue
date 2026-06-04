<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { 
  Phone, 
  Download, 
  ArrowUpRight, 
  CheckCircle, 
  MessageSquare, 
  Layers, 
  User, 
  ChevronRight 
} from '@lucide/vue';

const props = defineProps({
  settings: {
    type: Object,
    required: true
  },
  navigation: {
    type: Array,
    required: true
  },
  page: {
    type: Object,
    default: null
  },
  posts: {
    type: Array,
    default: () => []
  }
});

const pageData = usePage();
const user = pageData.props.auth?.user;

// Helper to find specific section by key
const getSection = (key) => {
  if (!props.page || !props.page.sections) return null;
  return props.page.sections.find(s => s.key === key && s.is_active);
};

const heroSection = getSection('hero');
const featuresSection = getSection('features');
const testimonialsSection = getSection('testimonials');
</script>

<template>
  <Head :title="page?.title || 'Selamat Datang'" />

  <div class="min-h-screen bg-slate-950 text-slate-100 font-sans selection:bg-indigo-500 selection:text-white relative overflow-hidden">
    <!-- Background Glow Blobs -->
    <div class="absolute top-[-20%] left-[-10%] w-[600px] h-[600px] rounded-full bg-indigo-900/10 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[600px] h-[600px] rounded-full bg-indigo-900/10 blur-[120px] pointer-events-none"></div>

    <!-- Sticky Glassmorphic Navbar -->
    <nav class="sticky top-0 z-50 bg-slate-950/70 backdrop-blur-md border-b border-slate-900/80 transition-all duration-300">
      <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
        <!-- Logo -->
        <Link href="/" class="flex items-center gap-3 group">
          <div v-if="settings.site_logo_url" class="h-10 w-auto flex items-center">
            <img :src="settings.site_logo_url" :alt="settings.site_name" class="h-8 object-contain" />
          </div>
          <div v-else class="p-2 bg-indigo-600 rounded-xl">
            <Layers class="w-5 h-5 text-white" />
          </div>
          <span class="font-extrabold text-xl tracking-tight text-white group-hover:text-indigo-400 transition-colors">
            {{ settings.site_name || 'CMS Boilerplate' }}
          </span>
        </Link>

        <!-- Navigation Pages from Database -->
        <div class="hidden md:flex items-center gap-8">
          <Link 
            v-for="nav in navigation" 
            :key="nav.id" 
            :href="nav.slug === 'home' ? '/' : `/${nav.slug}`"
            class="text-sm font-medium text-slate-400 hover:text-white transition-colors"
          >
            {{ nav.title }}
          </Link>
        </div>

        <!-- Auth Actions -->
        <div class="flex items-center gap-4">
          <Link 
            v-if="user" 
            :href="route('admin.dashboard')" 
            class="inline-flex items-center justify-center px-4 py-2 bg-indigo-650 hover:bg-indigo-700 text-xs font-semibold text-white rounded-xl shadow-lg shadow-indigo-600/20 transition-all duration-200"
          >
            Dashboard Admin
            <ArrowUpRight class="w-3.5 h-3.5 ml-1" />
          </Link>
          <template v-else>
            <Link 
              :href="route('login')" 
              class="text-xs font-semibold text-slate-400 hover:text-white transition-colors"
            >
              Sign In
            </Link>
            <Link 
              :href="route('register')" 
              class="hidden sm:inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-xs font-semibold text-white rounded-xl shadow-lg shadow-indigo-600/20 transition-all duration-200"
            >
              Daftar Gratis
            </Link>
          </template>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <header v-if="heroSection" class="relative max-w-7xl mx-auto px-6 pt-16 pb-24 md:pt-24 md:pb-36">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
        <!-- Content -->
        <div class="lg:col-span-7 space-y-8 text-left">
          <div class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-500/10 border border-indigo-500/20 rounded-full text-indigo-400 text-xs font-semibold">
            <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-pulse"></span>
            Siap Digunakan Untuk Bisnis Anda
          </div>

          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight text-white leading-[1.1] md:leading-[1.15]">
            <span class="bg-gradient-to-r from-white via-slate-100 to-indigo-400 bg-clip-text text-transparent">
              {{ heroSection.content.headline }}
            </span>
          </h1>

          <p class="text-lg text-slate-400 font-medium max-w-xl">
            {{ heroSection.content.subheadline }}
          </p>

          <!-- CTAs -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 pt-2">
            <a 
              v-if="heroSection.content.cta_url"
              :href="heroSection.content.cta_url" 
              target="_blank"
              class="inline-flex items-center justify-center px-6 py-4 bg-indigo-650 hover:bg-indigo-750 text-sm font-semibold text-white rounded-xl shadow-xl shadow-indigo-600/30 hover:scale-[1.02] hover:shadow-indigo-600/40 active:scale-[0.98] transition-all duration-200"
            >
              <Phone class="w-4 h-4 mr-2" />
              {{ heroSection.content.cta_text || 'Hubungi WhatsApp' }}
            </a>

            <a 
              v-if="settings.playstore_link"
              :href="settings.playstore_link" 
              target="_blank"
              class="inline-flex items-center justify-center px-6 py-4 bg-slate-900 hover:bg-slate-850 border border-slate-800 hover:border-slate-700 text-sm font-semibold text-slate-200 rounded-xl hover:scale-[1.02] active:scale-[0.98] transition-all duration-200"
            >
              <Download class="w-4 h-4 mr-2 text-indigo-400" />
              Download Play Store
            </a>
          </div>
        </div>

        <!-- Visual / Graphic Mockup -->
        <div class="lg:col-span-5 relative flex justify-center">
          <div class="w-72 h-[540px] rounded-[36px] bg-slate-950 border-8 border-slate-900 shadow-2xl overflow-hidden relative shadow-indigo-950/20">
            <!-- Top Notch -->
            <div class="absolute top-0 inset-x-0 h-5 bg-slate-900 flex justify-center items-center z-20">
              <div class="w-20 h-3.5 bg-slate-950 rounded-b-xl"></div>
            </div>

            <!-- Dynamic Demo Screen content -->
            <div class="w-full h-full bg-slate-900 p-6 pt-10 flex flex-col justify-between text-left">
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <div class="space-y-1">
                    <span class="text-xxs text-slate-500 font-bold uppercase">Ringkasan</span>
                    <h4 class="text-sm font-bold text-white">Pendapatan Hari Ini</h4>
                  </div>
                  <span class="text-xxs px-2 py-0.5 bg-emerald-500/10 text-emerald-400 rounded border border-emerald-500/25 font-bold">+24%</span>
                </div>
                <div class="h-24 bg-slate-950 border border-slate-800 rounded-2xl flex items-center justify-center relative overflow-hidden p-4">
                  <div class="text-center">
                    <span class="text-xxs text-slate-500 uppercase tracking-wider font-bold">Total Penjualan</span>
                    <p class="text-lg font-black text-white mt-1">Rp 4.850.000</p>
                  </div>
                </div>
              </div>

              <div class="space-y-3">
                <div class="flex items-center gap-2 p-2.5 bg-indigo-600/10 border border-indigo-500/20 rounded-xl">
                  <CheckCircle class="w-4 h-4 text-indigo-400 shrink-0" />
                  <span class="text-xxs text-indigo-300 font-semibold">Integrasi Cloud Lancar</span>
                </div>
                <div class="flex items-center gap-2 p-2.5 bg-slate-950 border border-slate-800 rounded-xl">
                  <CheckCircle class="w-4 h-4 text-emerald-400 shrink-0" />
                  <span class="text-xxs text-slate-400 font-semibold">Semua Laporan Tersinkron</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Dynamic Features Section -->
    <section v-if="featuresSection" class="py-24 border-t border-slate-900 bg-slate-950/40 relative">
      <div class="max-w-7xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto space-y-3 mb-16">
          <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest">Keunggulan</span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
            {{ featuresSection.content.title }}
          </h2>
          <p class="text-sm text-slate-450">
            Didesain khusus untuk memberikan performa maksimal demi kesuksesan operasional Anda.
          </p>
        </div>

        <!-- Dynamic Grid Features (1 col Mobile, 3 cols Desktop) with auto-height cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div 
            v-for="(item, idx) in featuresSection.content.items" 
            :key="idx"
            class="bg-slate-950 border border-slate-900 rounded-3xl p-8 hover:border-indigo-500/40 hover:scale-[1.02] shadow-xl hover:shadow-indigo-950/10 transition-all duration-300 flex flex-col justify-between group"
          >
            <div class="space-y-4">
              <div class="w-12 h-12 rounded-2xl bg-indigo-600/10 text-indigo-400 flex items-center justify-center border border-indigo-500/20 group-hover:bg-indigo-650 group-hover:text-white transition-all duration-300">
                <CheckCircle class="w-6 h-6" />
              </div>
              <h3 class="text-xl font-bold text-white group-hover:text-indigo-400 transition-colors">
                {{ item.title }}
              </h3>
              <p class="text-sm text-slate-400 leading-relaxed font-medium">
                {{ item.description }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Dynamic Testimonials Section -->
    <section v-if="testimonialsSection" class="py-24 border-t border-slate-900 relative">
      <div class="max-w-7xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto space-y-3 mb-16">
          <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest">Testimoni</span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
            {{ testimonialsSection.content.title }}
          </h2>
          <p class="text-sm text-slate-450">
            Dengarkan ulasan jujur dari pengusaha yang telah bertransformasi bersama kami.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
          <div 
            v-for="(item, idx) in testimonialsSection.content.items" 
            :key="idx"
            class="bg-slate-950 border border-slate-900 rounded-3xl p-8 space-y-6 relative hover:border-slate-800 transition-all"
          >
            <div class="absolute top-6 right-8 text-indigo-900/40">
              <MessageSquare class="w-12 h-12" />
            </div>
            <p class="text-base text-slate-350 italic leading-relaxed relative z-10 font-medium">
              "{{ item.comment }}"
            </p>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center font-bold text-indigo-400 border border-slate-700 text-sm">
                {{ item.name.charAt(0) }}
              </div>
              <div>
                <h4 class="text-sm font-bold text-white">{{ item.name }}</h4>
                <p class="text-xs text-slate-500 font-semibold">{{ item.role }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Blog / Articles Grid Section -->
    <section v-if="posts && posts.length > 0" class="py-24 border-t border-slate-900 bg-slate-950/40 relative">
      <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-16">
          <div class="space-y-3">
            <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest">Artikel & Berita</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">Kabar Terbaru</h2>
            <p class="text-sm text-slate-450">Dapatkan wawasan seputar dunia bisnis kuliner dan pengembangan teknologi terbaru.</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div 
            v-for="post in posts" 
            :key="post.id"
            class="bg-slate-950 border border-slate-900 rounded-3xl overflow-hidden group hover:border-indigo-500/35 transition-all duration-300 flex flex-col justify-between"
          >
            <div>
              <!-- Cover image wrapper -->
              <div class="h-48 bg-slate-900 relative overflow-hidden">
                <img 
                  v-if="post.image_url" 
                  :src="post.image_url" 
                  :alt="post.title" 
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                />
                <div v-else class="w-full h-full flex items-center justify-center bg-slate-900 text-slate-700">
                  <Layers class="w-12 h-12" />
                </div>
                <!-- Category badge -->
                <span class="absolute top-4 left-4 text-xxs font-bold text-indigo-400 px-2.5 py-1 bg-slate-950/80 border border-slate-800 backdrop-blur rounded-full uppercase tracking-wider">
                  {{ post.category?.name || 'Umum' }}
                </span>
              </div>

              <!-- Post body -->
              <div class="p-6 space-y-3 text-left">
                <p class="text-xxs text-slate-500 font-semibold">
                  {{ new Date(post.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                </p>
                <h3 class="text-lg font-bold text-white group-hover:text-indigo-400 transition-colors line-clamp-2">
                  {{ post.title }}
                </h3>
                <p class="text-xs text-slate-450 line-clamp-3 font-medium">
                  {{ post.content }}
                </p>
              </div>
            </div>

            <div class="p-6 pt-0 text-left">
              <span class="text-xs font-bold text-indigo-400 hover:text-indigo-300 flex items-center gap-1 cursor-pointer">
                Baca Selengkapnya <ChevronRight class="w-4 h-4" />
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-slate-900 bg-slate-950 py-16 text-slate-500 text-sm relative z-10">
      <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 text-left mb-12">
          <!-- Column 1: Info -->
          <div class="md:col-span-2 space-y-4">
            <span class="font-extrabold text-lg text-white tracking-tight">
              {{ settings.site_name || 'CMS Boilerplate' }}
            </span>
            <p class="text-xs text-slate-450 leading-relaxed max-w-sm">
              {{ settings.site_description || 'Boilerplate CMS premium berbasis Laravel 11, Vue 3, Inertia.js, dan Tailwind CSS.' }}
            </p>
          </div>

          <!-- Column 2: Tautan Halaman -->
          <div class="space-y-4">
            <h4 class="text-xs font-bold text-slate-350 uppercase tracking-widest">Halaman</h4>
            <ul class="space-y-2 text-xs">
              <li v-for="nav in navigation" :key="nav.id">
                <Link :href="nav.slug === 'home' ? '/' : `/${nav.slug}`" class="hover:text-white transition-colors">
                  {{ nav.title }}
                </Link>
              </li>
            </ul>
          </div>

          <!-- Column 3: Kontak & Hak Cipta -->
          <div class="space-y-4">
            <h4 class="text-xs font-bold text-slate-350 uppercase tracking-widest">Kontak</h4>
            <ul class="space-y-2 text-xs">
              <li v-if="settings.whatsapp_number" class="flex items-center gap-1.5">
                <Phone class="w-3.5 h-3.5 text-indigo-400" />
                <span>+{{ settings.whatsapp_number }}</span>
              </li>
              <li>
                <span>Email: support@cms.com</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Lower Footer -->
        <div class="pt-8 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between gap-4">
          <p class="text-xs text-slate-600">
            &copy; {{ new Date().getFullYear() }} {{ settings.site_name || 'CMS Boilerplate' }}. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>
