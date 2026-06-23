<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MitraLayout from '@/Layouts/MitraLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Plus, Trash2, FileText } from '@lucide/vue';
import Swal from 'sweetalert2';

const props = defineProps({
  researches: {
    type: Object,
    required: true
  },
  filters: Object
});

const headers = [
  { text: 'Judul Riset', value: 'title' },
  { text: 'Penulis', value: 'author' },
  { text: 'Ticker', value: 'ticker' },
  { text: 'Rekomendasi', value: 'recommendation' },
  { text: 'Tipe', value: 'report_type' },
  { text: 'Tanggal', value: 'created_at', type: 'date' }
];

const selectedItems = ref([]);

const handleEdit = (item) => {
  router.get(route('mitra.researches.edit', item.id));
};

const handleDelete = async (item) => {
  const result = await Swal.fire({
    title: 'Konfirmasi Hapus',
    text: `Apakah Anda yakin ingin menghapus riset "${item.title}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#ef4444',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal',
    background: '#121614',
    color: '#cbd5e1'
  });

  if (result.isConfirmed) {
    router.delete(route('mitra.researches.destroy', item.id));
  }
};

const handleBulkDelete = async () => {
  if (selectedItems.value.length === 0) return;
  const result = await Swal.fire({
    title: 'Konfirmasi Hapus Massal',
    text: `Apakah Anda yakin ingin menghapus ${selectedItems.value.length} riset yang dipilih?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#ef4444',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal',
    background: '#121614',
    color: '#cbd5e1'
  });

  if (result.isConfirmed) {
    router.delete(route('mitra.researches.bulk-destroy'), {
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

  <MitraLayout>
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
            :href="route('mitra.researches.create')"
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
          >
            <template #cell(author)="{ item }">
              {{ typeof item.author === 'object' && item.author !== null ? item.author.name : item.author }}
            </template>
          </DataTable>
        </div>
      </div>

    </div>
  </MitraLayout>
</template>
