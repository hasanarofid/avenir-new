<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Users } from '@lucide/vue';

const props = defineProps({
  users: Array
});

function formatDate(dateString) {
  if (!dateString) return '-';
  const d = new Date(dateString);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
  <Head title="Kelola User Subscriber" />

  <AdminLayout>
    <div class="space-y-6 max-w-7xl mx-auto pb-12">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-emerald-950/30 pb-4">
        <div>
          <h2 class="text-2xl font-extrabold tracking-tight text-white flex items-center gap-2">
            <Users class="w-6 h-6 text-emerald-500" />
            User Subscriber
          </h2>
          <p class="text-sm text-slate-400 mt-1">Daftar semua pengguna terdaftar dan status berlangganan.</p>
        </div>
        <div class="px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-mono font-bold text-xs rounded-full">
          {{ users.length }} USERS
        </div>
      </div>

      <!-- List Users -->
      <div v-if="users.length === 0" class="text-center py-12 bg-[#121614] border border-emerald-950/30 rounded-2xl">
        <Users class="w-12 h-12 text-emerald-900/50 mx-auto mb-3" />
        <h3 class="text-lg font-bold text-slate-300">Belum Ada User</h3>
        <p class="text-sm text-slate-500 mt-1">Sistem belum memiliki pengguna terdaftar.</p>
      </div>

      <div v-else class="space-y-4">
        <div v-for="user in users" :key="user.id" class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-5 shadow-xl hover:border-emerald-700/50 transition-colors">
          <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
              <div class="flex items-center gap-3 mb-1">
                <h3 class="text-lg font-bold text-white">{{ user.name }}</h3>
                <span v-if="user.is_admin" class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-500/20 text-amber-500 border border-amber-500/30">Admin</span>
                <span v-if="user.is_subscriber" class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Subscriber</span>
                <span v-else class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-blue-500/10 text-blue-400 border border-blue-500/30">Trial/Basic</span>
              </div>
              <div class="text-sm text-slate-400 font-mono">{{ user.email }}</div>
            </div>
            
            <div class="flex gap-2">
              <button class="px-3 py-1.5 bg-[#090b0a] border border-emerald-950/50 text-slate-300 hover:text-emerald-400 hover:border-emerald-700 text-xs font-semibold rounded-lg transition-all">
                Edit
              </button>
              <button class="px-3 py-1.5 bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-500/20 text-xs font-semibold rounded-lg transition-all">
                Hapus
              </button>
            </div>
          </div>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-5 pt-4 border-t border-emerald-950/30">
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Trial Started</div>
              <div class="text-sm font-semibold text-slate-300">{{ formatDate(user.trial_started_at) }}</div>
            </div>
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Daftar Sejak</div>
              <div class="text-sm font-semibold text-slate-300">{{ formatDate(user.created_at) }}</div>
            </div>
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">No HP</div>
              <div class="text-sm font-semibold text-slate-300">{{ user.phone_number || '-' }}</div>
            </div>
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Status</div>
              <div class="text-sm font-semibold" :class="user.is_subscriber ? 'text-emerald-400' : 'text-blue-400'">
                {{ user.is_subscriber ? 'Premium' : 'Standard' }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
