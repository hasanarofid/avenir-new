<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  packages: Array,
});

const isEditModalOpen = ref(false);
const editingPackage = ref(null);

const form = useForm({
  name: '',
  price: 0,
  discount_percent: 0,
  discount_end_at: '',
  duration_days: 0,
  is_active: true,
});

const openEditModal = (pkg) => {
  editingPackage.value = pkg;
  form.name = pkg.name;
  form.price = pkg.price;
  form.discount_percent = pkg.discount_percent;
  // Convert date format for input type="datetime-local"
  form.discount_end_at = pkg.discount_end_at ? pkg.discount_end_at.slice(0, 16) : '';
  form.duration_days = pkg.duration_days;
  form.is_active = pkg.is_active;
  isEditModalOpen.value = true;
};

const closeEditModal = () => {
  isEditModalOpen.value = false;
  editingPackage.value = null;
  form.reset();
  form.clearErrors();
};

const updatePackage = () => {
  if (!editingPackage.value) return;

  form.put(route('admin.packages.update', editingPackage.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeEditModal();
    },
  });
};
</script>

<template>
  <Head title="Manajemen Paket Langganan" />

  <AdminLayout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
      <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-slate-100 flex items-center gap-2">
            🎟️ Manajemen Paket Langganan
          </h1>
          <p class="mt-1 text-sm text-slate-400">
            Kelola harga dasar, durasi, dan diskon promo paket langganan.
          </p>
        </div>
      </div>

      <div class="bg-slate-900 shadow-lg rounded-xl border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-400">
            <thead class="text-xs uppercase bg-slate-800/50 text-slate-400">
              <tr>
                <th scope="col" class="px-6 py-4 font-semibold">Nama Paket</th>
                <th scope="col" class="px-6 py-4 font-semibold">Harga Dasar</th>
                <th scope="col" class="px-6 py-4 font-semibold">Diskon</th>
                <th scope="col" class="px-6 py-4 font-semibold">Harga Aktif</th>
                <th scope="col" class="px-6 py-4 font-semibold">Durasi</th>
                <th scope="col" class="px-6 py-4 font-semibold">Batas Waktu Diskon</th>
                <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                <th scope="col" class="px-6 py-4 font-semibold text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="pkg in packages" :key="pkg.id" class="hover:bg-slate-800/30 transition-colors">
                <td class="px-6 py-4">
                  <div class="font-bold text-slate-200">{{ pkg.name }}</div>
                  <div class="text-xs text-slate-500">{{ pkg.id }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                  Rp {{ pkg.price.toLocaleString('id-ID') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="pkg.discount_percent > 0" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    {{ pkg.discount_percent }}% OFF
                  </span>
                  <span v-else class="text-slate-500">-</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="pkg.has_active_discount" class="font-bold text-emerald-400">
                    Rp {{ pkg.active_price.toLocaleString('id-ID') }}
                  </span>
                  <span v-else class="text-slate-300">
                    Rp {{ pkg.active_price.toLocaleString('id-ID') }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-slate-300">
                  {{ pkg.duration_days }} Hari
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div v-if="pkg.discount_percent > 0 && pkg.discount_end_at" class="text-sm">
                    <span v-if="new Date(pkg.discount_end_at) > new Date()" class="text-slate-300">
                      {{ new Date(pkg.discount_end_at).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'}) }}
                    </span>
                    <span v-else class="text-rose-400 text-xs font-semibold">
                      Berakhir (Kadalursa)
                    </span>
                  </div>
                  <span v-else class="text-slate-500">-</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="pkg.is_active" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Aktif
                  </span>
                  <span v-else class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Nonaktif
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button @click="openEditModal(pkg)" class="text-indigo-400 hover:text-indigo-300 transition-colors">
                    Edit
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4">
      <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden" @click.stop>
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
          <h3 class="text-lg font-bold text-slate-100">Edit Paket: {{ editingPackage?.name }}</h3>
          <button @click="closeEditModal" class="text-slate-400 hover:text-slate-200 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>

        <form @submit.prevent="updatePackage" class="p-6">
          <div class="space-y-5">
            <!-- Nama Paket -->
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-1.5">Nama Paket</label>
              <input v-model="form.name" type="text" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" required>
              <div v-if="form.errors.name" class="mt-1 text-sm text-rose-400">{{ form.errors.name }}</div>
            </div>

            <!-- Harga Dasar -->
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-1.5">Harga Dasar (Rp)</label>
              <input v-model="form.price" type="number" min="0" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" required>
              <div v-if="form.errors.price" class="mt-1 text-sm text-rose-400">{{ form.errors.price }}</div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <!-- Diskon -->
              <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Diskon (%)</label>
                <input v-model="form.discount_percent" type="number" min="0" max="100" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" required>
                <div v-if="form.errors.discount_percent" class="mt-1 text-sm text-rose-400">{{ form.errors.discount_percent }}</div>
              </div>

              <!-- Durasi -->
              <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Durasi (Hari)</label>
                <input v-model="form.duration_days" type="number" min="1" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" required>
                <div v-if="form.errors.duration_days" class="mt-1 text-sm text-rose-400">{{ form.errors.duration_days }}</div>
              </div>
            </div>

            <!-- Batas Waktu Diskon -->
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-1.5">Batas Waktu Diskon</label>
              <input v-model="form.discount_end_at" type="datetime-local" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all [color-scheme:dark]">
              <div class="mt-1.5 text-xs text-slate-500">Kosongkan jika tidak ada batas waktu.</div>
              <div v-if="form.errors.discount_end_at" class="mt-1 text-sm text-rose-400">{{ form.errors.discount_end_at }}</div>
            </div>

            <!-- Status Aktif -->
            <div class="flex items-center mt-2">
              <input v-model="form.is_active" id="is_active" type="checkbox" class="w-4 h-4 text-emerald-500 bg-slate-800 border-slate-700 rounded focus:ring-emerald-500 focus:ring-offset-slate-900">
              <label for="is_active" class="ml-2 text-sm font-medium text-slate-300">Tampilkan Paket ini di Website</label>
            </div>
          </div>

          <div class="mt-8 flex justify-end gap-3">
            <button type="button" @click="closeEditModal" class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-lg transition-colors">
              Batal
            </button>
            <button type="submit" :disabled="form.processing" class="px-5 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-500 rounded-lg transition-colors disabled:opacity-50">
              {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
