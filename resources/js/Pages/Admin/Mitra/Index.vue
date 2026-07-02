<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Head, router } from '@inertiajs/vue3';
import { UserCheck, Edit, Trash2, Check, X } from '@lucide/vue';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  mitra: Object,
  filters: Object
});

const pendingCount = computed(() => props.mitra.data.filter(m => !m.is_verified).length);
const activeCount = computed(() => props.mitra.data.filter(m => m.is_verified).length);

const headers = [
  { text: 'Nama', value: 'name' },
  { text: 'Email', value: 'email' },
  { text: 'No HP', value: 'phone_number' },
  { text: 'Status', value: 'is_verified' },
  { text: 'Terdaftar Sejak', value: 'created_at', type: 'date' }
];

const handleSearch = (searchQuery) => {
  router.get(
    route('admin.mitra.index'),
    { search: searchQuery },
    { preserveState: true, preserveScroll: true, replace: true }
  );
};

// Modal State
const isEditModalOpen = ref(false);
const editingId = ref(null);
const editForm = ref({
  certification: '',
  specializations: '',
  portfolio_link: '',
  bank_name: '',
  bank_account_number: '',
  bank_account_name: '',
  phone_number: ''
});

function formatSpecializations(spec) {
  if (!spec) return '';
  if (Array.isArray(spec)) return spec.join(', ');
  try {
    const parsed = JSON.parse(spec);
    return Array.isArray(parsed) ? parsed.join(', ') : parsed;
  } catch (e) {
    return spec;
  }
}

function startEdit(m) {
  editingId.value = m.id;
  editForm.value = {
    certification: m.certification || '',
    specializations: formatSpecializations(m.specializations),
    portfolio_link: m.portfolio_link || '',
    bank_name: m.bank_name || '',
    bank_account_number: m.bank_account_number || '',
    bank_account_name: m.bank_account_name || '',
    phone_number: m.phone_number || '',
  };
  isEditModalOpen.value = true;
}

function closeEditModal() {
  isEditModalOpen.value = false;
  editingId.value = null;
}

function saveEdit() {
  router.put(route('admin.mitra.update', editingId.value), editForm.value, {
    preserveScroll: true,
    onSuccess: () => closeEditModal()
  });
}

async function approveMitra(id) {
  const result = await Swal.fire({
    title: 'Konfirmasi Verifikasi',
    text: 'Yakin ingin verifikasi mitra ini?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#ef4444',
    confirmButtonText: 'Ya, Verifikasi',
    cancelButtonText: 'Batal',
    background: '#121614',
    color: '#cbd5e1'
  });
  if (result.isConfirmed) {
    router.post(route('admin.mitra.approve', id), {}, { preserveScroll: true });
  }
}

async function deleteMitra(id) {
  const result = await Swal.fire({
    title: 'Konfirmasi Hapus',
    text: 'Yakin ingin menghapus mitra ini?',
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
    router.delete(route('admin.mitra.destroy', id), { preserveScroll: true });
  }
}
</script>

<template>
  <Head title="Kelola Mitra Analis" />

  <AdminLayout>
    <div class="space-y-6 pb-12">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-emerald-950/30 pb-4">
        <div>
          <h2 class="text-2xl font-extrabold tracking-tight text-white flex items-center gap-2">
            <UserCheck class="w-6 h-6 text-amber-500" />
            Mitra Analis
          </h2>
          <p class="text-sm text-slate-400 mt-1">Kelola permohonan dan status keanggotaan Mitra Analis Avenir.</p>
        </div>
        <div class="px-3 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-400 font-mono font-bold text-xs rounded-full">
          {{ pendingCount }} PENDING / {{ activeCount }} AKTIF
        </div>
      </div>

      <DataTable 
        :items="mitra.data" 
        :headers="headers" 
        :pagination="mitra.links"
        :server-search="true"
        :initial-search="filters?.search || ''"
        search-placeholder="Cari nama atau email..."
        @search="handleSearch"
      >
        <template #cell(name)="{ item }">
          <div class="font-bold text-white">{{ item.name || item.user_id }}</div>
        </template>
        
        <template #cell(email)="{ item }">
          <div class="font-mono text-slate-400 text-xs">{{ item.email || '-' }}</div>
        </template>

        <template #cell(phone_number)="{ item }">
          {{ item.phone_number || '-' }}
        </template>
        
        <template #cell(is_verified)="{ item }">
          <span v-if="item.is_verified" class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">✅ Mitra Aktif</span>
          <span v-else class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-500/20 text-amber-400 border border-amber-500/30">⏳ Pending</span>
        </template>

        <template #actions="{ item }">
          <div class="flex items-center justify-end gap-2">
            <button 
              v-if="!item.is_verified" 
              @click="approveMitra(item.id)" 
              class="p-2 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500 hover:text-white rounded-lg transition-all"
              title="Approve"
            >
              <Check class="w-4 h-4" />
            </button>
            <button 
              @click="startEdit(item)"
              class="p-2 bg-[#090b0a] border border-emerald-950/50 text-slate-300 hover:text-emerald-400 hover:border-emerald-700 rounded-lg transition-all"
              title="Edit"
            >
              <Edit class="w-4 h-4" />
            </button>
            <button 
              @click="deleteMitra(item.id)"
              class="p-2 bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-500/20 rounded-lg transition-all"
              title="Hapus"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </template>
      </DataTable>
    </div>

    <!-- Edit Modal -->
    <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeEditModal"></div>
      <div class="relative bg-[#121614] border border-emerald-950/50 rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden max-h-[90vh] flex flex-col">
        <div class="p-5 border-b border-emerald-950/30 flex items-center justify-between shrink-0">
          <h3 class="text-lg font-bold text-white">Edit Data Mitra</h3>
          <button @click="closeEditModal" class="text-slate-400 hover:text-white transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>
        
        <div class="p-5 overflow-y-auto space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Sertifikasi</label>
              <input v-model="editForm.certification" type="text" class="w-full bg-[#090b0a] border border-emerald-950/50 rounded-xl px-4 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Spesialisasi (pisah koma)</label>
              <input v-model="editForm.specializations" type="text" class="w-full bg-[#090b0a] border border-emerald-950/50 rounded-xl px-4 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">No HP</label>
              <input v-model="editForm.phone_number" type="text" class="w-full bg-[#090b0a] border border-emerald-950/50 rounded-xl px-4 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Link Portfolio</label>
              <input v-model="editForm.portfolio_link" type="url" class="w-full bg-[#090b0a] border border-emerald-950/50 rounded-xl px-4 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
            </div>
            <div class="md:col-span-2 mt-2">
              <h4 class="text-sm font-bold text-emerald-400 border-b border-emerald-950/50 pb-2 mb-3">Informasi Bank</h4>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Bank</label>
              <input v-model="editForm.bank_name" type="text" class="w-full bg-[#090b0a] border border-emerald-950/50 rounded-xl px-4 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">No Rekening</label>
              <input v-model="editForm.bank_account_number" type="text" class="w-full bg-[#090b0a] border border-emerald-950/50 rounded-xl px-4 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
            </div>
            <div class="md:col-span-2">
              <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Pemilik Rekening</label>
              <input v-model="editForm.bank_account_name" type="text" class="w-full bg-[#090b0a] border border-emerald-950/50 rounded-xl px-4 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
            </div>
          </div>
        </div>

        <div class="p-5 border-t border-emerald-950/30 flex justify-end gap-3 shrink-0">
          <button @click="closeEditModal" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-400 hover:text-white transition-colors">
            Batal
          </button>
          <button @click="saveEdit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold rounded-xl transition-all">
            Simpan Perubahan
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
