<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Plus, Trash2, Newspaper, Crown, Star } from '@lucide/vue';
import Swal from 'sweetalert2';

const props = defineProps({
  news: {
    type: Object,
    required: true
  },
  filters: Object
});

const headers = [
  { text: 'Judul Artikel', value: 'title' },
  { text: 'Penulis', value: 'author' },
  { text: 'Kategori', value: 'category' },
  { text: 'Status', value: 'status' },
  { text: 'Berita Utama', value: 'is_featured' },
  { text: 'Tgl Publish', value: 'published_at', type: 'date' }
];

const selectedItems = ref([]);

const handleEdit = (item) => {
  router.get(route('admin.news.edit', item.id));
};

const handleDelete = async (item) => {
  const result = await Swal.fire({
    title: 'Konfirmasi Hapus',
    text: `Apakah Anda yakin ingin menghapus artikel "${item.title}"?`,
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
    router.delete(route('admin.news.destroy', item.id));
  }
};

const handleBulkDelete = async () => {
  if (selectedItems.value.length === 0) return;
  const result = await Swal.fire({
    title: 'Konfirmasi Hapus Massal',
    text: `Apakah Anda yakin ingin menghapus ${selectedItems.value.length} artikel yang dipilih?`,
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
    router.delete(route('admin.news.bulk-destroy'), {
      data: { ids: selectedItems.value },
      onSuccess: () => {
        selectedItems.value = [];
      }
    });
  }
};

const toggleFeatured = (item) => {
  router.patch(route('admin.news.toggle-featured', item.id), {}, {
    preserveScroll: true,
    preserveState: true,
  });
};
</script>

<template>
  <Head title="News & Articles" />

  <AdminLayout>
    <div class="space-y-8">
      <!-- Title Page -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-3xl font-extrabold tracking-tight text-white flex items-center gap-3">
            <Newspaper class="w-8 h-8 text-emerald-500" />
            News & Articles
          </h2>
          <p class="text-sm text-slate-400 mt-1">Kelola daftar berita dan artikel untuk ditampilkan di website.</p>
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
            :href="route('admin.news.create')"
            class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-sm font-semibold text-white rounded-xl shadow-lg shadow-emerald-600/20 transition-all cursor-pointer"
          >
            <Plus class="w-4.5 h-4.5 mr-1.5" />
            Tambah Berita
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-8 items-start">
        <!-- News List -->
        <div>
          <DataTable 
            :items="news.data" 
            :headers="headers" 
            :pagination="news.links"
            selectable
            v-model:selectedItems="selectedItems"
            search-placeholder="Cari judul berita atau kategori..."
            search-key="title"
            @edit="handleEdit"
            @delete="handleDelete"
          >
            <template #cell(author)="{ item }">
              {{ typeof item.author === 'object' && item.author !== null ? item.author.name : item.author }}
            </template>
            <template #cell(is_featured)="{ item }">
              <button 
                @click="toggleFeatured(item)"
                class="group relative inline-flex items-center justify-center w-8 h-8 rounded-lg transition-all duration-300 shadow-sm"
                :class="item.is_featured ? 'bg-amber-500/10 border border-amber-500/30 text-amber-500 hover:bg-amber-500/20' : 'bg-slate-800/50 border border-white/5 text-slate-500 hover:bg-slate-800 hover:text-slate-300 hover:border-white/10'"
                :title="item.is_featured ? 'Hapus dari Berita Utama' : 'Jadikan Berita Utama'"
              >
                <Crown v-if="item.is_featured" class="w-4 h-4 fill-amber-500" />
                <Star v-else class="w-4 h-4 group-hover:fill-slate-400" />
                
                <span class="absolute -top-10 left-1/2 -translate-x-1/2 px-2.5 py-1.5 bg-[#1a1f1c] border border-white/10 text-slate-300 text-[10px] font-medium rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10 shadow-xl">
                  {{ item.is_featured ? 'Hapus dari Berita Utama' : 'Jadikan Berita Utama' }}
                </span>
              </button>
            </template>
          </DataTable>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>
