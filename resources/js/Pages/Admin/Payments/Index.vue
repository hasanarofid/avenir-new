<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { FileText } from '@lucide/vue';

const props = defineProps({
  payments: Array
});

function formatDate(dateString) {
  if (!dateString) return '-';
  const d = new Date(dateString);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute:'2-digit' });
}

function formatRupiah(number) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);
}
</script>

<template>
  <Head title="Konfirmasi Pembayaran" />

  <AdminLayout>
    <div class="space-y-6 pb-12">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-emerald-950/30 pb-4">
        <div>
          <h2 class="text-2xl font-extrabold tracking-tight text-white flex items-center gap-2">
            <FileText class="w-6 h-6 text-blue-500" />
            Konfirmasi Pembayaran
          </h2>
          <p class="text-sm text-slate-400 mt-1">Verifikasi bukti transfer langganan Avenir Research.</p>
        </div>
        <div class="px-3 py-1 bg-blue-500/10 border border-blue-500/20 text-blue-400 font-mono font-bold text-xs rounded-full">
          {{ payments.length }} PENDING
        </div>
      </div>

      <!-- List Payments -->
      <div v-if="payments.length === 0" class="text-center py-12 bg-[#121614] border border-emerald-950/30 rounded-2xl">
        <FileText class="w-12 h-12 text-emerald-900/50 mx-auto mb-3" />
        <h3 class="text-lg font-bold text-slate-300">Tidak Ada Pembayaran</h3>
        <p class="text-sm text-slate-500 mt-1">Semua bukti transfer telah diverifikasi.</p>
      </div>

      <div v-else class="space-y-4">
        <div v-for="p in payments" :key="p.id" class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-5 shadow-xl hover:border-emerald-700/50 transition-colors">
          <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
              <div class="flex items-center gap-3 mb-1">
                <h3 class="text-lg font-bold text-white">{{ p.user_name || 'Anonim' }}</h3>
                <span v-if="p.status === 'pending'" class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-500/20 text-amber-400 border border-amber-500/30">⏳ Pending</span>
                <span v-else-if="p.status === 'verified'" class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">✅ Terverifikasi</span>
                <span v-else class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-rose-500/20 text-rose-400 border border-rose-500/30">❌ Ditolak</span>
              </div>
              <div class="text-sm text-slate-400 font-mono">{{ p.user_email || '-' }}</div>
            </div>
            
            <div class="flex gap-2">
              <Link v-if="p.status === 'pending'" :href="route('admin.payments.verify', p.id)" method="post" as="button" class="px-3 py-1.5 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500 hover:text-white text-xs font-semibold rounded-lg transition-all">
                Verifikasi
              </Link>
              <Link v-if="p.status === 'pending'" :href="route('admin.payments.reject', p.id)" method="post" as="button" class="px-3 py-1.5 bg-[#090b0a] border border-rose-500/30 text-rose-400 hover:bg-rose-500 hover:text-white text-xs font-semibold rounded-lg transition-all">
                Tolak
              </Link>
            </div>
          </div>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-5 pt-4 border-t border-emerald-950/30">
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Jumlah Transfer</div>
              <div class="text-sm font-semibold text-emerald-400">{{ formatRupiah(p.nominal) }}</div>
            </div>
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Paket</div>
              <div class="text-sm font-semibold text-slate-300">{{ p.paket || '-' }}</div>
            </div>
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Durasi</div>
              <div class="text-sm font-semibold text-slate-300">{{ p.durasi_hari ? p.durasi_hari + ' Hari' : '-' }}</div>
            </div>
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Waktu Submit</div>
              <div class="text-sm font-semibold text-slate-300">{{ formatDate(p.created_at) }}</div>
            </div>
            <div class="col-span-2 md:col-span-4">
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Bukti Transfer</div>
              <div class="text-sm font-semibold text-slate-300">
                <a v-if="p.bukti_url" :href="p.bukti_url" target="_blank" class="text-blue-400 hover:underline">Lihat Bukti Transfer ↗</a>
                <span v-else>-</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
