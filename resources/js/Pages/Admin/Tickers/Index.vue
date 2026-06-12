<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Plus } from '@lucide/vue';
import Swal from 'sweetalert2';

const props = defineProps({
  emitens: {
    type: Array,
    required: true
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
            :items="emitens" 
            :headers="headers" 
            search-placeholder="Cari emiten berdasarkan nama atau simbol..."
            search-key="company_name"
            @edit="handleEdit"
            @delete="handleDelete"
          />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
