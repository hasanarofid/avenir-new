<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Edit, Eye, CheckCircle } from '@lucide/vue';
import Swal from 'sweetalert2';

const props = defineProps({
  deskBriefs: {
    type: Object,
    required: true
  }
});

const headers = [
  { text: 'Tanggal', value: 'date', type: 'date' },
  { text: 'Regime Summary', value: 'market_stance_id' },
  { text: 'Status', value: 'status' },
  { text: 'Tgl Publish', value: 'published_at', type: 'datetime' }
];

const handlePreview = (item) => {
  window.open(route('desk-brief.index', { preview_id: item.id }), '_blank');
};

const handleEdit = (item) => {
  router.get(route('admin.desk-brief.edit', item.id));
};

const handlePublish = (item) => {
  Swal.fire({
    title: 'Publish Desk Brief?',
    text: "Brief akan langsung tampil di halaman publik.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#10B981',
    cancelButtonColor: '#EF4444',
    confirmButtonText: 'Ya, Publish!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      router.post(route('admin.desk-brief.publish', item.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
          Swal.fire(
            'Berhasil!',
            'Desk Brief telah dipublish.',
            'success'
          );
        }
      });
    }
  });
};
</script>

<template>
  <AdminLayout>
    <Head title="Manajemen Desk Brief" />

    <div class="mb-6 flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-white">Desk Brief</h2>
        <p class="text-gray-400 mt-1">Kelola data market intelligence harian</p>
      </div>
    </div>

    <div class="bg-[#1A1A1A] rounded-xl border border-gray-800">
      <DataTable
        :headers="headers"
        :items="deskBriefs.data"
        :pagination="deskBriefs.links"
        :show-checkbox="false"
      >
        <template #cell(status)="{ item }">
          <span :class="[
            'px-2 py-1 text-xs font-medium rounded-full',
            item.status === 'published' ? 'bg-green-500/10 text-green-500' : 'bg-yellow-500/10 text-yellow-500'
          ]">
            {{ item.status === 'published' ? 'Published' : 'Draft' }}
          </span>
        </template>
        
        <template #cell(market_stance_id)="{ item }">
          <div v-if="item.market_stance" class="flex flex-col gap-2 py-2">
            <div class="flex items-center">
              <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 text-xs font-bold rounded border border-emerald-500/20 shadow-sm flex items-center gap-2">
                <span class="text-white">{{ item.market_stance.score }}</span> <span class="text-emerald-600/50">|</span> {{ item.market_stance.label }}
              </span>
            </div>
            <div class="grid grid-cols-3 md:grid-cols-6 gap-2 text-[10px] mt-1 bg-[#161616] p-2 rounded-lg border border-gray-800/60 shadow-inner w-fit">
              <div class="flex flex-col items-center justify-center px-1">
                <span class="uppercase tracking-wider text-[8px] text-gray-500 mb-0.5">Foreign</span>
                <span class="font-bold text-[11px]" :class="item.market_stance.foreign_score >= 50 ? 'text-emerald-400' : 'text-red-400'">{{ item.market_stance.foreign_score }}</span>
              </div>
              <div class="flex flex-col items-center justify-center px-1 border-l border-gray-800/50">
                <span class="uppercase tracking-wider text-[8px] text-gray-500 mb-0.5">Breadth</span>
                <span class="font-bold text-[11px]" :class="item.market_stance.breadth_score >= 50 ? 'text-emerald-400' : 'text-red-400'">{{ item.market_stance.breadth_score }}</span>
              </div>
              <div class="flex flex-col items-center justify-center px-1 border-l border-gray-800/50">
                <span class="uppercase tracking-wider text-[8px] text-gray-500 mb-0.5">Momentum</span>
                <span class="font-bold text-[11px]" :class="item.market_stance.momentum_score >= 50 ? 'text-emerald-400' : 'text-red-400'">{{ item.market_stance.momentum_score }}</span>
              </div>
              <div class="flex flex-col items-center justify-center px-1 md:border-l border-gray-800/50">
                <span class="uppercase tracking-wider text-[8px] text-gray-500 mb-0.5">Rupiah</span>
                <span class="font-bold text-[11px]" :class="item.market_stance.rupiah_score >= 50 ? 'text-emerald-400' : 'text-red-400'">{{ item.market_stance.rupiah_score }}</span>
              </div>
              <div class="flex flex-col items-center justify-center px-1 border-l border-gray-800/50">
                <span class="uppercase tracking-wider text-[8px] text-gray-500 mb-0.5">Yield</span>
                <span class="font-bold text-[11px]" :class="item.market_stance.yield_score >= 50 ? 'text-emerald-400' : 'text-red-400'">{{ item.market_stance.yield_score }}</span>
              </div>
              <div class="flex flex-col items-center justify-center px-1 border-l border-gray-800/50">
                <span class="uppercase tracking-wider text-[8px] text-gray-500 mb-0.5">Sector</span>
                <span class="font-bold text-[11px]" :class="item.market_stance.sector_score >= 50 ? 'text-emerald-400' : 'text-red-400'">{{ item.market_stance.sector_score }}</span>
              </div>
            </div>
          </div>
          <div v-else class="text-gray-500 italic text-sm">
            -
          </div>
        </template>

        <template #actions="{ item }">
          <div class="flex items-center gap-2">
            <button
              @click="handlePreview(item)"
              class="p-2 text-blue-400 hover:bg-blue-500/10 rounded-lg transition-colors"
              title="Preview Frontend"
            >
              <Eye class="w-4 h-4" />
            </button>
            <button
              @click="handleEdit(item)"
              class="p-2 text-yellow-400 hover:bg-yellow-500/10 rounded-lg transition-colors"
              title="Edit Narasi"
            >
              <Edit class="w-4 h-4" />
            </button>
            <button
              v-if="item.status !== 'published'"
              @click="handlePublish(item)"
              class="p-2 text-green-400 hover:bg-green-500/10 rounded-lg transition-colors"
              title="Publish"
            >
              <CheckCircle class="w-4 h-4" />
            </button>
          </div>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>
