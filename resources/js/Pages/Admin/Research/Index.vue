<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Plus, Trash2, FileText } from '@lucide/vue';

const props = defineProps({
  researches: {
    type: Object,
    required: true
  },
  filters: Object
});

const headers = [
  { text: 'Judul Riset', value: 'title' },
  { text: 'Ticker', value: 'ticker' },
  { text: 'Rekomendasi', value: 'recommendation' },
  { text: 'Tipe', value: 'report_type' },
  { text: 'Tanggal', value: 'created_at', type: 'date' }
];

const selectedItems = ref([]);

const handleEdit = (item) => {
  router.get(route('admin.katalog-riset.edit', item.id));
};

const handleDelete = (item) => {
  if (confirm(`Apakah Anda yakin ingin menghapus riset "${item.title}"?`)) {
    router.delete(route('admin.katalog-riset.destroy', item.id));
  }
};

const handleBulkDelete = () => {
  if (selectedItems.value.length === 0) return;
  if (confirm(`Apakah Anda yakin ingin menghapus ${selectedItems.value.length} riset yang dipilih?`)) {
    router.delete(route('admin.katalog-riset.bulk-destroy'), {
      data: { ids: selectedItems.value },
      onSuccess: () => {
        selectedItems.value = [];
      }
    });
  }
};
</script>

<template>
  <Head title="Katalog Riset" />

  <AdminLayout>
    <div class="space-y-8">
      <!-- Title Page -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-3xl font-extrabold tracking-tight text-white flex items-center gap-3">
            <FileText class="w-8 h-8 text-emerald-500" />
            Katalog Riset
          </h2>
          <p class="text-sm text-slate-400 mt-1">Kelola daftar laporan riset saham untuk klien dan publik.</p>
        </div>
        <div class="flex items-center gap-3">
          <button 
            v-if="selectedItems.length > 0"
            @click="handleBulkDelete"
            class="inline-flex items-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-sm font-semibold text-white rounded-xl shadow-lg shadow-rose-600/20 transition-all cursor-pointer"
          >
            <Trash2 class="w-4.5 h-4.5 mr-1.5" />
            Hapus {{ selectedItems.length }}
          </button>
          <Link 
            :href="route('admin.katalog-riset.create')"
            class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-sm font-semibold text-white rounded-xl shadow-lg shadow-emerald-600/20 transition-all cursor-pointer"
          >
            <Plus class="w-4.5 h-4.5 mr-1.5" />
            Tambah Riset
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-8 items-start">
        <!-- Researches List -->
        <div>
          <DataTable 
            :items="researches.data" 
            :headers="headers" 
            :pagination="researches.links"
            selectable
            v-model:selectedItems="selectedItems"
            search-placeholder="Cari judul riset atau ticker..."
            search-key="title"
            @edit="handleEdit"
            @delete="handleDelete"
          />
        </div>
      </div>

    </div>
  </AdminLayout>
</template>
