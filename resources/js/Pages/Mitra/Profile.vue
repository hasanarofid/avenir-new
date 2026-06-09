<script setup>
import MitraLayout from '@/Layouts/MitraLayout.vue';
import { Head } from '@inertiajs/vue3';
import { User, CheckCircle, Clock } from '@lucide/vue';

const props = defineProps({
  user: Object,
  partner: Object,
});
</script>

<template>
  <Head title="Profile Mitra" />

  <MitraLayout>
    <div class="space-y-6 pb-12">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-emerald-950/30 pb-4">
        <div>
          <h2 class="text-2xl font-extrabold tracking-tight text-white flex items-center gap-2">
            Profile Mitra
          </h2>
          <p class="text-sm text-slate-400 mt-1">Kelola profil dan informasi mitra kamu</p>
        </div>
      </div>

      <!-- Profile Card -->
      <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6">
        <div class="flex items-center gap-4 mb-6">
          <div class="w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center font-bold text-2xl text-emerald-450 border border-slate-700">
            {{ user.name.charAt(0).toUpperCase() }}
          </div>
          <div>
            <h3 class="text-xl font-bold text-white">{{ user.name }}</h3>
            <p class="text-sm text-slate-400">{{ user.email }}</p>
            <div class="flex items-center gap-2 mt-2">
              <CheckCircle v-if="partner?.is_verified" class="w-5 h-5 text-emerald-400" />
              <Clock v-else class="w-5 h-5 text-amber-400" />
              <span v-if="partner?.is_verified" class="text-sm text-emerald-400 font-semibold">Mitra Aktif</span>
              <span v-else class="text-sm text-amber-400 font-semibold">Menunggu Verifikasi</span>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="p-4 bg-[#090b0a] rounded-xl border border-emerald-950/20">
            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Sertifikasi</p>
            <p class="text-sm font-semibold text-slate-200">{{ partner?.certification || '-' }}</p>
          </div>
          <div class="p-4 bg-[#090b0a] rounded-xl border border-emerald-950/20">
            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Spesialisasi</p>
            <p class="text-sm font-semibold text-slate-200">{{ partner?.specializations?.join(', ') || '-' }}</p>
          </div>
          <div class="p-4 bg-[#090b0a] rounded-xl border border-emerald-950/20">
            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Portfolio</p>
            <p class="text-sm font-semibold text-slate-200">
              <a v-if="partner?.portfolio_link" :href="partner.portfolio_link" target="_blank" class="text-emerald-400 hover:underline">
                {{ partner.portfolio_link }}
              </a>
              <span v-else>-</span>
            </p>
          </div>
          <div class="p-4 bg-[#090b0a] rounded-xl border border-emerald-950/20">
            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Bank</p>
            <p class="text-sm font-semibold text-slate-200">{{ partner?.bank_name || '-' }}</p>
          </div>
          <div class="md:col-span-2 p-4 bg-[#090b0a] rounded-xl border border-emerald-950/20">
            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Nomor Rekening</p>
            <p class="text-sm font-semibold text-slate-200">{{ partner?.bank_account_number || '-' }} (a.n. {{ partner?.bank_account_name || '-' }})</p>
          </div>
        </div>
      </div>
    </div>
  </MitraLayout>
</template>
