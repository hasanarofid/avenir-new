<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Plus, Upload, Loader2 } from '@lucide/vue';
import Swal from 'sweetalert2';
import { ref } from 'vue';

const props = defineProps({
  emitens: {
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  }
});

const headers = [
  { text: 'Simbol', value: 'symbol' },
  { text: 'Nama Perusahaan', value: 'company_name' },
  { text: 'Sektor', value: 'sector' },
  { text: 'Rekomendasi', value: 'recommendation' }
];

const handleEdit = (item) => {
  router.get(route('admin.emitens.edit', item.id));
};

const handleDelete = async (item) => {
  const result = await Swal.fire({
    title: 'Hapus Emiten?',
    text: `Apakah Anda yakin ingin menghapus Emiten "${item.symbol} - ${item.company_name}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#e11d48',
    cancelButtonColor: '#475569',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal',
    background: '#121614',
    color: '#f1f5f9'
  });

  if (result.isConfirmed) {
    router.delete(route('admin.emitens.destroy', item.id));
  }
};

let searchTimeout = null;
const handleSearch = (query) => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get(route('admin.emitens.index'), { search: query }, { preserveState: true, replace: true });
  }, 300);
};

const fileInput = ref(null);
const isUploading = ref(false);
const form = useForm({
  file: null,
});

const triggerFileInput = () => {
  if (fileInput.value) {
    fileInput.value.click();
  }
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  form.file = file;
  isUploading.value = true;
  form.post(route('admin.emitens.import'), {
    preserveScroll: true,
    onSuccess: () => {
      Swal.fire({
        title: 'Berhasil',
        text: 'Data emiten berhasil diimpor.',
        icon: 'success',
        background: '#121614',
        color: '#f1f5f9',
        confirmButtonColor: '#10b981'
      });
      if (fileInput.value) fileInput.value.value = '';
    },
    onError: (errors) => {
      Swal.fire({
        title: 'Gagal',
        text: errors.file || 'Gagal mengimpor data.',
        icon: 'error',
        background: '#121614',
        color: '#f1f5f9',
        confirmButtonColor: '#e11d48'
      });
      if (fileInput.value) fileInput.value.value = '';
    },
    onFinish: () => {
      isUploading.value = false;
    }
  });
};
</script>

<template>
  <Head title="Kelola Emiten" />

  <AdminLayout>
    <div class="space-y-8">
      <!-- Title Page -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-3xl font-extrabold tracking-tight text-white">Emiten Hub</h2>
          <p class="text-sm text-slate-400 mt-1">Kelola data profil, finansial, dan risiko untuk Emiten.</p>
        </div>
        <div class="flex items-center gap-3">
          <input 
            type="file" 
            ref="fileInput" 
            class="hidden" 
            accept=".xlsx,.xls,.csv"
            @change="handleFileUpload" 
          />
          <button 
            @click="triggerFileInput"
            :disabled="isUploading"
            class="inline-flex items-center px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-sm font-semibold text-white rounded-xl shadow-lg shadow-slate-900/20 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed border border-slate-700"
          >
            <Loader2 v-if="isUploading" class="w-4.5 h-4.5 mr-1.5 animate-spin" />
            <Upload v-else class="w-4.5 h-4.5 mr-1.5" />
            {{ isUploading ? 'Mengimpor...' : 'Import Excel' }}
          </button>

          <Link 
            :href="route('admin.emitens.create')"
            class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-sm font-semibold text-white rounded-xl shadow-lg shadow-emerald-600/20 transition-all cursor-pointer"
          >
            <Plus class="w-4.5 h-4.5 mr-1.5" />
            Tambah Emiten Baru
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-8 items-start">
        <div class="col-span-1">
          <DataTable 
            :items="emitens.data" 
            :headers="headers" 
            :pagination="emitens.links"
            :serverSearch="true"
            :initialSearch="filters.search"
            search-placeholder="Cari emiten berdasarkan nama atau simbol..."
            search-key="company_name"
            @edit="handleEdit"
            @delete="handleDelete"
            @search="handleSearch"
          />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
