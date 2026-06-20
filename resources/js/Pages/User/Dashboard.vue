<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  user: Object
});
</script>

<template>
  <Head title="Dashboard User" />

  <AppLayout>
    <div class="py-24 bg-[#090b0a] min-h-screen">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-[#121614] border border-emerald-950/30 overflow-hidden shadow-xl sm:rounded-2xl p-8">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-500 flex items-center justify-center text-3xl font-bold border border-emerald-500/30">
              {{ user.name.charAt(0) }}
            </div>
            <div>
              <h2 class="text-3xl font-bold text-white">Selamat Datang di Avenir!</h2>
              <p class="text-slate-400 mt-1">Halo {{ user.name }}, Anda login sebagai <span class="text-emerald-400 font-bold uppercase tracking-wider text-xs px-2 py-0.5 bg-emerald-500/10 rounded-full border border-emerald-500/20">{{ (user.role || 'User').replace('_', ' ') }}</span></p>
            </div>
          </div>
          
          <div class="mt-8 border-t border-emerald-950/30 pt-8">
            <h3 class="text-lg font-bold text-white mb-4">Informasi Akun Anda</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl">
              <div class="bg-[#090b0a] border border-emerald-950/30 p-4 rounded-xl flex items-center justify-between">
                <div class="text-xs text-slate-500 font-bold uppercase tracking-widest">Nama Lengkap</div>
                <div class="text-white font-medium">{{ user.name }}</div>
              </div>
              <div class="bg-[#090b0a] border border-emerald-950/30 p-4 rounded-xl flex items-center justify-between">
                <div class="text-xs text-slate-500 font-bold uppercase tracking-widest">Email</div>
                <div class="text-white font-medium">{{ user.email }}</div>
              </div>
              <div class="bg-[#090b0a] border border-emerald-950/30 p-4 rounded-xl flex items-center justify-between">
                <div class="text-xs text-slate-500 font-bold uppercase tracking-widest">Role Akses</div>
                <div class="text-white font-medium capitalize">{{ (user.role || 'User').replace('_', ' ') }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
