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
    <div class="space-y-6 pb-12">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-emerald-950/30 pb-4">
        <div>
          <h2 class="text-2xl font-extrabold tracking-tight text-white flex items-center gap-2">
            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            Manajemen Paket Langganan
          </h2>
          <p class="text-sm text-slate-400 mt-1">Kelola harga dasar, durasi, dan diskon promo paket langganan.</p>
        </div>
        <div class="px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-mono font-bold text-xs rounded-full">
          {{ packages.length }} PAKET
        </div>
      </div>

      <div class="space-y-4">
        <div v-for="pkg in packages" :key="pkg.id" class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl hover:border-emerald-700/50 transition-colors">
          <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
              <div class="flex items-center gap-3 mb-1">
                <h3 class="text-lg font-bold text-white">{{ pkg.name }}</h3>
                <span v-if="pkg.is_active" class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                  ✅ Ditampilkan
                </span>
                <span v-else class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-rose-500/20 text-rose-400 border border-rose-500/30">
                  ❌ Disembunyikan
                </span>
                <span v-if="pkg.discount_percent > 0" class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-500/20 text-amber-400 border border-amber-500/30">
                  PROMO {{ pkg.discount_percent }}% OFF
                </span>
              </div>
              <div class="text-sm text-slate-400 font-mono">ID: {{ pkg.id }}</div>
            </div>
            
            <div class="flex gap-2">
              <button @click="openEditModal(pkg)" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#090b0a] border border-emerald-950/50 text-slate-300 hover:text-emerald-400 hover:border-emerald-700 text-xs font-semibold rounded-lg transition-all">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                Edit Paket
              </button>
            </div>
          </div>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-5 pt-4 border-t border-emerald-950/30">
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Harga Dasar</div>
              <div class="text-sm font-semibold text-slate-300">Rp {{ pkg.price.toLocaleString('id-ID') }}</div>
            </div>
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Harga Saat Ini</div>
              <div class="text-sm font-semibold" :class="pkg.has_active_discount ? 'text-emerald-400' : 'text-slate-300'">
                Rp {{ pkg.active_price.toLocaleString('id-ID') }}
              </div>
            </div>
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Durasi Paket</div>
              <div class="text-sm font-semibold text-slate-300">{{ pkg.duration_days }} Hari</div>
            </div>
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Batas Promo Diskon</div>
              <div class="text-sm font-semibold text-slate-300">
                <template v-if="pkg.discount_percent > 0 && pkg.discount_end_at">
                  <span v-if="new Date(pkg.discount_end_at) > new Date()" class="text-amber-400">
                    {{ new Date(pkg.discount_end_at).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'}) }}
                  </span>
                  <span v-else class="text-rose-400 line-through">
                    {{ new Date(pkg.discount_end_at).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'}) }}
                  </span>
                </template>
                <span v-else class="text-slate-500">-</span>
              </div>
            </div>
          </div>
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
