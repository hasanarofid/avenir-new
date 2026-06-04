<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Users, FileText, Layers, Settings, ArrowRight } from '@lucide/vue';

const props = defineProps({
  stats: {
    type: Object,
    required: true
  },
  recent_posts: {
    type: Array,
    required: true
  }
});
</script>

<template>
  <Head title="Admin Dashboard" />

  <AdminLayout>
    <div class="space-y-8">
      <!-- Title Page -->
      <div>
        <h2 class="text-3xl font-extrabold tracking-tight text-white">Dashboard Overview</h2>
        <p class="text-sm text-slate-400 mt-1">Selamat datang kembali di pusat pengelolaan konten Anda.</p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Users count card -->
        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 relative overflow-hidden shadow-xl shadow-slate-950/20 group">
          <div class="absolute -right-4 -bottom-4 opacity-5 text-emerald-400 group-hover:scale-110 transition-transform duration-300">
            <Users class="w-32 h-32" />
          </div>
          <div class="flex items-center justify-between">
            <div class="space-y-2">
              <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Users</span>
              <h3 class="text-3xl font-black text-white">{{ stats.users_count }}</h3>
            </div>
            <div class="p-3 bg-emerald-500/10 text-emerald-400 rounded-xl border border-emerald-500/20">
              <Users class="w-6 h-6" />
            </div>
          </div>
        </div>

        <!-- Pages count card -->
        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 relative overflow-hidden shadow-xl shadow-slate-950/20 group">
          <div class="absolute -right-4 -bottom-4 opacity-5 text-emerald-400 group-hover:scale-110 transition-transform duration-300">
            <Layers class="w-32 h-32" />
          </div>
          <div class="flex items-center justify-between">
            <div class="space-y-2">
              <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Pages</span>
              <h3 class="text-3xl font-black text-white">{{ stats.pages_count }}</h3>
            </div>
            <div class="p-3 bg-emerald-500/10 text-emerald-400 rounded-xl border border-emerald-500/20">
              <Layers class="w-6 h-6" />
            </div>
          </div>
        </div>

        <!-- Posts count card -->
        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 relative overflow-hidden shadow-xl shadow-slate-950/20 group">
          <div class="absolute -right-4 -bottom-4 opacity-5 text-amber-400 group-hover:scale-110 transition-transform duration-300">
            <FileText class="w-32 h-32" />
          </div>
          <div class="flex items-center justify-between">
            <div class="space-y-2">
              <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Posts</span>
              <h3 class="text-3xl font-black text-white">{{ stats.posts_count }}</h3>
            </div>
            <div class="p-3 bg-amber-500/10 text-amber-400 rounded-xl border border-amber-500/20">
              <FileText class="w-6 h-6" />
            </div>
          </div>
        </div>

        <!-- Settings count card -->
        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 relative overflow-hidden shadow-xl shadow-slate-950/20 group">
          <div class="absolute -right-4 -bottom-4 opacity-5 text-emerald-400 group-hover:scale-110 transition-transform duration-300">
            <Settings class="w-32 h-32" />
          </div>
          <div class="flex items-center justify-between">
            <div class="space-y-2">
              <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Global Settings</span>
              <h3 class="text-3xl font-black text-white">{{ stats.settings_count }}</h3>
            </div>
            <div class="p-3 bg-emerald-500/10 text-emerald-400 rounded-xl border border-emerald-500/20">
              <Settings class="w-6 h-6" />
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions and Recent posts -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent posts list -->
        <div class="lg:col-span-2 bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl shadow-slate-950/20 space-y-6">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-bold text-white">Postingan Terbaru</h3>
              <p class="text-xs text-slate-500">Artikel terbaru yang telah diterbitkan atau disimpan.</p>
            </div>
            <Link :href="route('admin.posts.index')" class="text-xs text-emerald-400 hover:text-emerald-300 font-semibold flex items-center gap-1">
              Lihat semua <ArrowRight class="w-3.5 h-3.5" />
            </Link>
          </div>

          <div class="divide-y divide-slate-800/60">
            <div 
              v-for="post in recent_posts" 
              :key="post.id"
              class="py-4 first:pt-0 last:pb-0 flex items-center justify-between gap-4"
            >
              <div class="min-w-0 space-y-1">
                <span class="text-xs font-semibold text-emerald-400 px-2 py-0.5 bg-emerald-500/10 rounded-full border border-emerald-500/25">
                  {{ post.category?.name || 'Uncategorized' }}
                </span>
                <h4 class="text-sm font-semibold text-slate-200 truncate mt-1.5">{{ post.title }}</h4>
                <p class="text-xs text-slate-500">{{ new Date(post.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
              </div>

              <div>
                <span 
                  :class="[
                    post.status === 'published' 
                      ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' 
                      : 'bg-slate-800 text-slate-400 border border-slate-700',
                    'px-2.5 py-0.5 rounded-full text-xs font-semibold'
                  ]"
                >
                  {{ post.status === 'published' ? 'Published' : 'Draft' }}
                </span>
              </div>
            </div>
            <div v-if="recent_posts.length === 0" class="py-12 text-center text-slate-500 text-sm">
              Belum ada postingan.
            </div>
          </div>
        </div>

        <!-- Quick actions / instructions panel -->
        <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl shadow-slate-950/20 space-y-6">
          <div>
            <h3 class="text-lg font-bold text-white">Panduan CMS</h3>
            <p class="text-xs text-slate-500">Tips cepat navigasi dan setup CMS boilerplate Anda.</p>
          </div>

          <div class="space-y-4 text-sm text-slate-400">
            <div class="p-3 bg-[#090b0a] rounded-xl border border-emerald-950/20">
              <h4 class="font-bold text-slate-200 text-xs uppercase tracking-wider mb-1">Pengaturan Global</h4>
              <p class="text-xs">Ubah link WA, Play Store, dan upload Logo secara terpusat pada menu <strong>Web Settings</strong>.</p>
            </div>
            <div class="p-3 bg-[#090b0a] rounded-xl border border-emerald-950/20">
              <h4 class="font-bold text-slate-200 text-xs uppercase tracking-wider mb-1">Landing Page Dinamis</h4>
              <p class="text-xs">Edit bagian visual di menu <strong>Pages & Sections</strong>. Struktur JSON didesain fleksibel untuk merubah layout landing page.</p>
            </div>
            <div class="p-3 bg-[#090b0a] rounded-xl border border-emerald-950/20">
              <h4 class="font-bold text-slate-200 text-xs uppercase tracking-wider mb-1">Spatie Permissions</h4>
              <p class="text-xs">Sistem ini dilengkapi dengan hak akses Spatie. Admin default: <code>admin@cms.com</code>, Editor: <code>editor@cms.com</code>, Client: <code>client@cms.com</code> (password: <code>password</code>).</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
