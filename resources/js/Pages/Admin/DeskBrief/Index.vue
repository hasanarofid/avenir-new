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
  { text: 'Score', value: 'market_stance_id' },
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
          {{ item.market_stance_id ? 'Tersedia' : '-' }}
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
