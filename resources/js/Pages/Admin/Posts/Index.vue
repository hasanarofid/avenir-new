<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Plus, FolderPlus, Trash2, X, Save } from '@lucide/vue';

const props = defineProps({
  posts: {
    type: Array,
    required: true
  },
  categories: {
    type: Array,
    required: true
  }
});

const isCategoryModalOpen = ref(false);

const headers = [
  { text: 'Judul', value: 'title' },
  { text: 'Kategori', value: 'category_name' },
  { text: 'Status', value: 'status', type: 'badge' },
  { text: 'Tanggal Dibuat', value: 'created_at', type: 'date' }
];

// Map posts to include flat category name for table search/display
const tableItems = props.posts.map(p => ({
  ...p,
  category_name: p.category?.name || 'Uncategorized'
}));

const categoryForm = useForm({
  name: ''
});

const createCategory = () => {
  categoryForm.post(route('admin.categories.store'), {
    onSuccess: () => {
      isCategoryModalOpen.value = false;
      categoryForm.reset();
    }
  });
};

const deleteCategory = (cat) => {
  if (confirm(`Apakah Anda yakin ingin menghapus kategori "${cat.name}"? Ini akan menghapus semua postingan terkait.`)) {
    router.delete(route('admin.categories.destroy', cat.id));
  }
};

const handleEdit = (item) => {
  router.get(route('admin.posts.edit', item.id));
};

const handleDelete = (item) => {
  if (confirm(`Apakah Anda yakin ingin menghapus postingan "${item.title}"?`)) {
    router.delete(route('admin.posts.destroy', item.id));
  }
};
</script>

<template>
  <Head title="Kelola Postingan" />

  <AdminLayout>
    <div class="space-y-8">
      <!-- Title Page -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-3xl font-extrabold tracking-tight text-white">Posts & Categories</h2>
          <p class="text-sm text-slate-400 mt-1">Kelola publikasi berita, artikel, atau spesifikasi detail produk Anda.</p>
        </div>
        <div class="flex items-center gap-3">
          <button 
            @click="isCategoryModalOpen = true"
            class="inline-flex items-center px-4 py-2.5 bg-[#121614] border border-emerald-950/25 hover:bg-[#090b0a] text-sm font-semibold text-slate-200 rounded-xl transition-all cursor-pointer"
          >
            <FolderPlus class="w-4.5 h-4.5 mr-1.5 text-emerald-400" />
            Kategori Baru
          </button>
          <Link 
            :href="route('admin.posts.create')"
            class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-sm font-semibold text-white rounded-xl shadow-lg shadow-emerald-600/20 transition-all cursor-pointer"
          >
            <Plus class="w-4.5 h-4.5 mr-1.5" />
            Tulis Postingan
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">
        <!-- Posts List -->
        <div class="lg:col-span-3">
          <DataTable 
            :items="tableItems" 
            :headers="headers" 
            search-placeholder="Cari postingan..."
            search-key="title"
            @edit="handleEdit"
            @delete="handleDelete"
          />
        </div>

        <!-- Categories Sidebar Card -->
        <div class="lg:col-span-1 bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl shadow-slate-950/20 space-y-4">
          <h3 class="text-sm font-bold text-white uppercase tracking-wider pb-3 border-b border-emerald-950/30">Daftar Kategori</h3>
          <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
            <div 
              v-for="cat in categories" 
              :key="cat.id"
              class="flex items-center justify-between p-3 bg-[#090b0a] rounded-xl border border-emerald-950/20 group text-sm"
            >
              <div>
                <p class="font-semibold text-slate-200">{{ cat.name }}</p>
                <p class="text-xxs text-slate-500 mt-0.5">{{ cat.posts_count }} postingan</p>
              </div>
              <button 
                @click="deleteCategory(cat)"
                class="p-1.5 text-slate-500 hover:text-rose-455 hover:bg-rose-500/10 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity"
                title="Hapus Kategori"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
            <div v-if="categories.length === 0" class="text-center py-6 text-slate-500 text-xs">
              Belum ada kategori.
            </div>
          </div>
        </div>
      </div>

      <div v-if="isCategoryModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="isCategoryModalOpen = false" class="fixed inset-0 bg-[#090b0a]/80"></div>
        <div class="relative bg-[#121614] border border-emerald-950/30 rounded-2xl max-w-sm w-full overflow-hidden shadow-2xl z-10">
          <div class="flex items-center justify-between px-6 py-4 border-b border-emerald-950/30">
            <h3 class="text-base font-bold text-white">Buat Kategori Baru</h3>
            <button @click="isCategoryModalOpen = false" class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-[#090b0a]">
              <X class="w-5 h-5" />
            </button>
          </div>
          <form @submit.prevent="createCategory" class="p-6 space-y-4">
            <div class="space-y-1">
              <label for="cat_name" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Kategori</label>
              <input 
                id="cat_name"
                v-model="categoryForm.name"
                type="text" 
                required
                class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-650 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250"
                placeholder="Contoh: Tekno, Edukasi"
              />
              <div v-if="categoryForm.errors.name" class="text-xs text-rose-500 font-semibold">{{ categoryForm.errors.name }}</div>
            </div>

            <div class="pt-4 border-t border-emerald-950/30 flex justify-end gap-3">
              <button 
                type="button" 
                @click="isCategoryModalOpen = false" 
                class="px-4 py-2 bg-[#090b0a] hover:bg-[#090b0a]/80 border border-emerald-950/20 text-sm font-semibold text-slate-300 rounded-xl transition-colors cursor-pointer"
              >
                Batal
              </button>
              <button 
                type="submit" 
                :disabled="categoryForm.processing"
                class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-sm font-semibold text-white rounded-xl shadow-lg shadow-emerald-600/20 transition-all cursor-pointer"
              >
                <Save class="w-4 h-4 mr-1.5" />
                Simpan Kategori
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
