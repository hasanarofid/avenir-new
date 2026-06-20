<script setup>
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Head, router } from '@inertiajs/vue3';
import { Users, Trash2 } from '@lucide/vue';
import Swal from 'sweetalert2';

const props = defineProps({
  users: Object,
  availableRoles: Array,
  filters: Object
});

const headers = [
  { text: 'Nama', value: 'name' },
  { text: 'Email', value: 'email' },
  { text: 'Status', value: 'is_subscriber' },
  { text: 'No HP', value: 'phone_number' },
  { text: 'Role', value: 'roles' },
  { text: 'Daftar Sejak', value: 'created_at', type: 'date' }
];

const selectedItems = ref([]);

const handleSearch = (searchQuery) => {
  router.get(
    route('admin.users.index'),
    { search: searchQuery },
    { preserveState: true, preserveScroll: true, replace: true }
  );
};

const updateRole = (userId, role) => {
  if (confirm(`Apakah Anda yakin ingin mengubah role user ini menjadi ${role}?`)) {
    router.put(route('admin.users.update-role', userId), { role: role }, {
      preserveScroll: true
    });
  }
};

const handleDelete = async (item) => {
  const result = await Swal.fire({
    title: 'Konfirmasi Hapus',
    text: `Apakah Anda yakin ingin menghapus user "${item.name}"?`,
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
    router.delete(route('admin.users.destroy', item.id));
  }
};

const handleBulkDelete = async () => {
  if (selectedItems.value.length === 0) return;
  const result = await Swal.fire({
    title: 'Konfirmasi Hapus Massal',
    text: `Apakah Anda yakin ingin menghapus ${selectedItems.value.length} user yang dipilih?`,
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
    router.delete(route('admin.users.bulk-destroy'), {
      data: { ids: selectedItems.value },
      onSuccess: () => {
        selectedItems.value = [];
      }
    });
  }
};
</script>

<template>
  <Head title="Kelola User Subscriber" />

  <AdminLayout>
    <div class="space-y-6 pb-12">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-emerald-950/30 pb-4">
        <div>
          <h2 class="text-2xl font-extrabold tracking-tight text-white flex items-center gap-2">
            <Users class="w-6 h-6 text-emerald-500" />
            User Subscriber
          </h2>
          <p class="text-sm text-slate-400 mt-1">Daftar semua pengguna terdaftar dan status berlangganan.</p>
        </div>
        <div class="px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-mono font-bold text-xs rounded-full">
          {{ users.total }} USERS
        </div>
      </div>

      <!-- DataTable -->
      <DataTable 
        :items="users.data" 
        :headers="headers" 
        :pagination="users.links"
        :server-search="true"
        :initial-search="filters?.search || ''"
        selectable
        v-model:selectedItems="selectedItems"
        search-placeholder="Cari nama, email, atau no HP..."
        @search="handleSearch"
        @delete="handleDelete"
      >
        <template #cell(roles)="{ item }">
          <select 
            class="bg-[#090b0a] border border-emerald-950/50 text-slate-300 text-xs rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-[120px] p-1.5"
            @change="updateRole(item.id, $event.target.value)"
          >
            <option value="">-- Pilih Role --</option>
            <option v-for="role in availableRoles" :key="role" :value="role" :selected="item.roles.includes(role)">
              {{ role }}
            </option>
          </select>
        </template>
        
        <template #cell(is_subscriber)="{ item }">
            <span v-if="item.is_subscriber" class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Premium</span>
            <span v-else class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-blue-500/10 text-blue-400 border border-blue-500/30">Standard</span>
        </template>

        <template #cell(phone_number)="{ item }">
            {{ item.phone_number || '-' }}
        </template>
        
        <template #actions-header>
          <button 
            v-if="selectedItems.length > 0"
            @click="handleBulkDelete"
            class="inline-flex items-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-sm font-semibold text-white rounded-lg shadow-lg shadow-rose-600/20 transition-all cursor-pointer"
          >
            <Trash2 class="w-4 h-4 mr-1.5" />
            Hapus {{ selectedItems.length }}
          </button>
        </template>
      </DataTable>

    </div>
  </AdminLayout>
</template>
