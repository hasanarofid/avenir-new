<script setup>
import { ref, watch, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Head, router } from '@inertiajs/vue3';
import { Users, Trash2 } from '@lucide/vue';
import Swal from 'sweetalert2';
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';

const props = defineProps({
  users: Object,
  availableRoles: Array,
  filters: Object
});

const headers = [
  { text: 'Nama', value: 'name' },
  { text: 'Email', value: 'email' },
  { text: 'Status', value: 'is_subscriber' },
  { text: 'Masa Aktif', value: 'subscription_ends_at', type: 'date' },
  { text: 'No HP', value: 'phone_number' },
  { text: 'Role', value: 'roles' },
  { text: 'Daftar Sejak', value: 'created_at', type: 'date' }
];

const selectedItems = ref([]);
const filterRole = ref(props.filters?.role || 'all');

const roleOptions = computed(() => {
    return [
        { value: 'all', label: 'Semua Role' },
        ...props.availableRoles.map(r => ({ value: r, label: r }))
    ];
});

const handleSearch = (searchQuery) => {
  router.get(
    route('admin.users.index'),
    { search: searchQuery, role: filterRole.value },
    { preserveState: true, preserveScroll: true, replace: true }
  );
};

watch(filterRole, () => {
  handleSearch(props.filters?.search || '');
});

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
            :disabled="item.roles.includes('admin')"
            :class="{'opacity-50 cursor-not-allowed': item.roles.includes('admin')}"
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

        <template #actions="{ item }">
          <div class="flex items-center justify-end gap-2">
            <button 
              v-if="!item.roles.includes('admin')"
              @click="handleDelete(item)" 
              class="p-2 text-rose-400 hover:bg-rose-600/10 rounded-lg transition-colors"
              title="Hapus"
            >
              <Trash2 class="w-4 h-4" />
            </button>
            <span v-else class="text-emerald-500/50 text-[10px] font-bold tracking-wider px-2">PROTECTED</span>
          </div>
        </template>
        
        <template #actions-header>
          <div class="w-[200px] z-20 mr-2">
            <Multiselect
              v-model="filterRole"
              :options="roleOptions"
              :searchable="true"
              placeholder="Filter by Role"
              class="w-full text-sm"
            />
          </div>
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

<style>
.multiselect {
  --ms-bg: #090b0a;
  --ms-bg-disabled: #121614;
  --ms-border-color: rgba(6, 78, 59, 0.4);
  --ms-border-width: 1px;
  --ms-border-color-active: #10b981;
  --ms-radius: 0.5rem;
  --ms-dropdown-bg: #121614;
  --ms-dropdown-border-color: rgba(6, 78, 59, 0.4);
  --ms-option-bg-pointed: rgba(16, 185, 129, 0.1);
  --ms-option-bg-selected: #059669;
  --ms-option-bg-selected-pointed: #047857;
  --ms-option-color-pointed: #e2e8f0;
  --ms-option-color-selected: #ffffff;
  --ms-font-color: #cbd5e1;
  --ms-empty-color: #64748b;
  --ms-caret-color: #94a3b8;
  --ms-clear-color: #94a3b8;
  --ms-spinner-color: #10b981;
  --ms-placeholder-color: #64748b;
}

.multiselect-dropdown {
  overflow-y: auto;
  z-index: 50;
}
</style>
