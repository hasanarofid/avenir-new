<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { UserCheck } from '@lucide/vue';

const props = defineProps({
  mitra: Array
});

function formatDate(dateString) {
  if (!dateString) return '-';
  const d = new Date(dateString);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
  <Head title="Kelola Mitra Analis" />

  <AdminLayout>
    <div class="space-y-6 pb-12">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-emerald-950/30 pb-4">
        <div>
          <h2 class="text-2xl font-extrabold tracking-tight text-white flex items-center gap-2">
            <UserCheck class="w-6 h-6 text-amber-500" />
            Mitra Analis
          </h2>
          <p class="text-sm text-slate-400 mt-1">Kelola permohonan dan status keanggotaan Mitra Analis Avenir.</p>
        </div>
        <div class="px-3 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-400 font-mono font-bold text-xs rounded-full">
          {{ mitra.length }} PENDING / AKTIF
        </div>
      </div>

      <!-- List Mitra -->
      <div v-if="mitra.length === 0" class="text-center py-12 bg-[#121614] border border-emerald-950/30 rounded-2xl">
        <UserCheck class="w-12 h-12 text-emerald-900/50 mx-auto mb-3" />
        <h3 class="text-lg font-bold text-slate-300">Belum Ada Mitra</h3>
        <p class="text-sm text-slate-500 mt-1">Tidak ada pengajuan mitra untuk saat ini.</p>
      </div>

      <div v-else class="space-y-4">
        <div v-for="m in mitra" :key="m.id" class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-5 shadow-xl hover:border-emerald-700/50 transition-colors">
          <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
              <div class="flex items-center gap-3 mb-1">
                <h3 class="text-lg font-bold text-white">{{ m.name || m.user_id }}</h3>
                <span v-if="m.is_verified" class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">✅ Mitra Aktif</span>
                <span v-else class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-500/20 text-amber-400 border border-amber-500/30">⏳ Pending</span>
              </div>
              <div class="text-sm text-slate-400 font-mono">{{ m.email || '-' }}</div>
            </div>
            
            <div class="flex gap-2">
              <button v-if="!m.is_verified" class="px-3 py-1.5 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500 hover:text-white text-xs font-semibold rounded-lg transition-all">
                Approve
              </button>
              <button class="px-3 py-1.5 bg-[#090b0a] border border-emerald-950/50 text-slate-300 hover:text-emerald-400 hover:border-emerald-700 text-xs font-semibold rounded-lg transition-all">
                Edit
              </button>
            </div>
          </div>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-5 pt-4 border-t border-emerald-950/30">
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Sertifikasi</div>
              <div class="text-sm font-semibold text-slate-300">{{ m.certification || '-' }}</div>
            </div>
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Spesialisasi</div>
              <div class="text-sm font-semibold text-slate-300">{{ Array.isArray(m.specializations) ? m.specializations.join(', ') : (m.specializations || '-') }}</div>
            </div>
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">No HP</div>
              <div class="text-sm font-semibold text-slate-300">{{ m.phone_number || '-' }}</div>
            </div>
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Portfolio</div>
              <div class="text-sm font-semibold text-slate-300">
                <a v-if="m.portfolio_link" :href="m.portfolio_link" target="_blank" class="text-emerald-400 hover:underline">Lihat</a>
                <span v-else>-</span>
              </div>
            </div>
            <div class="col-span-2 md:col-span-4">
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Bank</div>
              <div class="text-sm font-semibold text-slate-300">{{ m.bank_name || '-' }} / {{ m.bank_account_number || '-' }} (a.n {{ m.bank_account_name || '-' }})</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
