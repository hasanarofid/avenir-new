<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Users, Plus, Edit, Trash2, X, Eye, EyeOff } from '@lucide/vue';

const props = defineProps({
  teamResearch: Object,
  filters: Object
});

const headers = [
  { text: 'Nama', value: 'name' },
  { text: 'Email', value: 'email' },
  { text: 'Terdaftar Sejak', value: 'created_at', type: 'date' }
];

const handleSearch = (searchQuery) => {
  router.get(
    route('admin.tim-internal.index'),
    { search: searchQuery },
    { preserveState: true, preserveScroll: true, replace: true }
  );
};

// Modals State
const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedUser = ref(null);

// Forms
const addForm = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

const editForm = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

// Password Visibility State
const showAddPassword = ref(false);
const showAddConfirmPassword = ref(false);
const showEditPassword = ref(false);
const showEditConfirmPassword = ref(false);

// Modal Toggles
const openAddModal = () => {
  addForm.reset();
  addForm.clearErrors();
  isAddModalOpen.value = true;
};

const closeAddModal = () => {
  isAddModalOpen.value = false;
};

const openEditModal = (user) => {
  selectedUser.value = user;
  editForm.name = user.name;
  editForm.email = user.email;
  editForm.password = '';
  editForm.password_confirmation = '';
  editForm.clearErrors();
  isEditModalOpen.value = true;
};

const closeEditModal = () => {
  isEditModalOpen.value = false;
  selectedUser.value = null;
};

// Actions
const submitAdd = () => {
  addForm.post(route('admin.tim-internal.store'), {
    preserveScroll: true,
    onSuccess: () => {
      closeAddModal();
    }
  });
};

const submitEdit = () => {
  editForm.put(route('admin.tim-internal.update', selectedUser.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeEditModal();
    }
  });
};

const deleteUser = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus akun ini?')) {
    router.delete(route('admin.tim-internal.destroy', id), {
      preserveScroll: true
    });
  }
};

function formatDate(dateString) {
  if (!dateString) return '-';
  const d = new Date(dateString);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
  <Head title="Kelola Tim Internal" />

  <AdminLayout>
    <div class="space-y-6 pb-12">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-emerald-950/30 pb-4">
        <div>
          <h2 class="text-2xl font-extrabold tracking-tight text-white flex items-center gap-2">
            <Users class="w-6 h-6 text-emerald-500" />
            Tim Internal
          </h2>
          <p class="text-sm text-slate-400 mt-1">Kelola akun pengguna dengan akses Tim Internal.</p>
        </div>
        <button 
          @click="openAddModal"
          class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl transition-all"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Akun</span>
        </button>
      </div>

      <DataTable 
        :items="teamResearch.data" 
        :headers="headers" 
        :pagination="teamResearch.links"
        :server-search="true"
        :initial-search="filters?.search || ''"
        search-placeholder="Cari nama atau email..."
        @search="handleSearch"
      >
        <template #actions="{ item }">
          <div class="flex items-center justify-end gap-2">
            <button 
              @click="openEditModal(item)"
              class="p-2 bg-[#090b0a] border border-emerald-950/50 text-slate-300 hover:text-emerald-400 hover:border-emerald-700 rounded-lg transition-all"
              title="Edit"
            >
              <Edit class="w-4 h-4" />
            </button>
            <button 
              @click="deleteUser(item.id)"
              class="p-2 bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-500/20 rounded-lg transition-all"
              title="Hapus"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </template>
      </DataTable>
    </div>

    <!-- Add Modal -->
    <div v-if="isAddModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeAddModal"></div>
      <div class="relative bg-[#121614] border border-emerald-950/50 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="p-5 border-b border-emerald-950/30 flex items-center justify-between">
          <h3 class="text-lg font-bold text-white">Tambah Tim Internal</h3>
          <button @click="closeAddModal" class="text-slate-400 hover:text-white transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>
        <form @submit.prevent="submitAdd" class="p-5 space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama</label>
            <input 
              type="text" 
              v-model="addForm.name" 
              required
              class="w-full bg-[#090b0a] border border-emerald-950/50 rounded-xl px-4 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
            />
            <div v-if="addForm.errors.name" class="mt-1 text-xs text-rose-400">{{ addForm.errors.name }}</div>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Email</label>
            <input 
              type="email" 
              v-model="addForm.email" 
              required
              class="w-full bg-[#090b0a] border border-emerald-950/50 rounded-xl px-4 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
            />
            <div v-if="addForm.errors.email" class="mt-1 text-xs text-rose-400">{{ addForm.errors.email }}</div>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Password</label>
            <div class="relative">
              <input 
                :type="showAddPassword ? 'text' : 'password'" 
                v-model="addForm.password" 
                required
                class="w-full bg-[#090b0a] border border-emerald-950/50 rounded-xl pl-4 pr-10 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
              />
              <button 
                type="button" 
                @click="showAddPassword = !showAddPassword" 
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 focus:outline-none"
              >
                <EyeOff v-if="showAddPassword" class="w-4 h-4" />
                <Eye v-else class="w-4 h-4" />
              </button>
            </div>
            <div v-if="addForm.errors.password" class="mt-1 text-xs text-rose-400">{{ addForm.errors.password }}</div>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Konfirmasi Password</label>
            <div class="relative">
              <input 
                :type="showAddConfirmPassword ? 'text' : 'password'" 
                v-model="addForm.password_confirmation" 
                required
                class="w-full bg-[#090b0a] border border-emerald-950/50 rounded-xl pl-4 pr-10 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
              />
              <button 
                type="button" 
                @click="showAddConfirmPassword = !showAddConfirmPassword" 
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 focus:outline-none"
              >
                <EyeOff v-if="showAddConfirmPassword" class="w-4 h-4" />
                <Eye v-else class="w-4 h-4" />
              </button>
            </div>
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t border-emerald-950/30">
            <button 
              type="button" 
              @click="closeAddModal"
              class="px-4 py-2 rounded-xl text-sm font-bold text-slate-400 hover:text-white transition-colors"
            >
              Batal
            </button>
            <button 
              type="submit" 
              :disabled="addForm.processing"
              class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold rounded-xl transition-all disabled:opacity-50"
            >
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeEditModal"></div>
      <div class="relative bg-[#121614] border border-emerald-950/50 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="p-5 border-b border-emerald-950/30 flex items-center justify-between">
          <h3 class="text-lg font-bold text-white">Edit Tim Internal</h3>
          <button @click="closeEditModal" class="text-slate-400 hover:text-white transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>
        <form @submit.prevent="submitEdit" class="p-5 space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama</label>
            <input 
              type="text" 
              v-model="editForm.name" 
              required
              class="w-full bg-[#090b0a] border border-emerald-950/50 rounded-xl px-4 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
            />
            <div v-if="editForm.errors.name" class="mt-1 text-xs text-rose-400">{{ editForm.errors.name }}</div>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Email</label>
            <input 
              type="email" 
              v-model="editForm.email" 
              required
              class="w-full bg-[#090b0a] border border-emerald-950/50 rounded-xl px-4 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
            />
            <div v-if="editForm.errors.email" class="mt-1 text-xs text-rose-400">{{ editForm.errors.email }}</div>
          </div>
          <div class="p-3 bg-blue-900/10 border border-blue-900/30 rounded-xl">
            <p class="text-xs text-blue-400 mb-3">Kosongkan kolom password di bawah ini jika tidak ingin mengubah password.</p>
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Password Baru</label>
                <div class="relative">
                  <input 
                    :type="showEditPassword ? 'text' : 'password'" 
                    v-model="editForm.password" 
                    class="w-full bg-[#090b0a] border border-emerald-950/50 rounded-xl pl-4 pr-10 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                  />
                  <button 
                    type="button" 
                    @click="showEditPassword = !showEditPassword" 
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 focus:outline-none"
                  >
                    <EyeOff v-if="showEditPassword" class="w-4 h-4" />
                    <Eye v-else class="w-4 h-4" />
                  </button>
                </div>
                <div v-if="editForm.errors.password" class="mt-1 text-xs text-rose-400">{{ editForm.errors.password }}</div>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Konfirmasi Password Baru</label>
                <div class="relative">
                  <input 
                    :type="showEditConfirmPassword ? 'text' : 'password'" 
                    v-model="editForm.password_confirmation" 
                    class="w-full bg-[#090b0a] border border-emerald-950/50 rounded-xl pl-4 pr-10 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                  />
                  <button 
                    type="button" 
                    @click="showEditConfirmPassword = !showEditConfirmPassword" 
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 focus:outline-none"
                  >
                    <EyeOff v-if="showEditConfirmPassword" class="w-4 h-4" />
                    <Eye v-else class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t border-emerald-950/30">
            <button 
              type="button" 
              @click="closeEditModal"
              class="px-4 py-2 rounded-xl text-sm font-bold text-slate-400 hover:text-white transition-colors"
            >
              Batal
            </button>
            <button 
              type="submit" 
              :disabled="editForm.processing"
              class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold rounded-xl transition-all disabled:opacity-50"
            >
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>

  </AdminLayout>
</template>
