<script setup>
import MitraLayout from '@/Layouts/MitraLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { FileText } from '@lucide/vue';

const props = defineProps({
  researches: Array,
});
</script>

<template>
  <Head title="Research & Articles" />

  <MitraLayout>
    <div class="space-y-6 pb-12">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-emerald-950/30 pb-4">
        <div>
          <h2 class="text-2xl font-extrabold tracking-tight text-white flex items-center gap-2">
            Research & Articles
          </h2>
          <p class="text-sm text-slate-400 mt-1">Kelola semua research dan artikel kamu</p>
        </div>
      </div>

      <!-- Research List -->
      <div class="space-y-4">
        <div v-if="researches.length === 0" class="text-center py-12 bg-[#121614] border border-emerald-950/30 rounded-2xl">
          <FileText class="w-12 h-12 text-emerald-900/50 mx-auto mb-3" />
          <h3 class="text-lg font-bold text-slate-300">Belum Ada Research</h3>
          <p class="text-sm text-slate-500 mt-1">Kamu belum memiliki research</p>
        </div>

        <div v-else class="space-y-4">
          <div v-for="research in researches" :key="research.id" class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 hover:border-emerald-700/50 transition-colors">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-bold text-white">{{ research.title }}</h3>
              <span class="px-2 py-1 text-xs font-bold rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                {{ research.status || 'Draft' }}
              </span>
            </div>
            <p class="text-sm text-slate-400 mt-2">{{ research.summary }}</p>
            <div class="mt-4 flex items-center gap-4">
              <p class="text-xs text-slate-500">
                Dibuat: {{ new Date(research.created_at).toLocaleDateString('id-ID') }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MitraLayout>
</template>
