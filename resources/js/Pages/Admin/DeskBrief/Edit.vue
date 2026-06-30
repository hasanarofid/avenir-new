<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeft, Save } from '@lucide/vue';
import Swal from 'sweetalert2';

const props = defineProps({
  deskBrief: {
    type: Object,
    required: true
  }
});

const form = useForm({
  title: props.deskBrief.title || '',
  market_read: props.deskBrief.market_read || '',
  so_what: props.deskBrief.so_what || '',
  what_to_do: props.deskBrief.what_to_do || ''
});

const submit = () => {
  form.put(route('admin.desk-brief.update', props.deskBrief.id), {
    preserveScroll: true,
    onSuccess: () => {
      Swal.fire({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        icon: 'success',
        title: 'Berhasil menyimpan narasi'
      });
    },
    onError: () => {
      Swal.fire({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        icon: 'error',
        title: 'Gagal menyimpan, periksa inputan'
      });
    }
  });
};
</script>

<template>
  <AdminLayout>
    <Head title="Edit Desk Brief" />

    <div class="mb-6 flex items-center justify-between">
      <div class="flex items-center gap-4">
        <Link
          :href="route('admin.desk-brief.index')"
          class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors"
        >
          <ArrowLeft class="w-5 h-5" />
        </Link>
        <div>
          <h2 class="text-2xl font-bold text-white">Edit Narasi Desk Brief</h2>
          <p class="text-gray-400 mt-1">Tanggal: {{ deskBrief.date }} | Status: {{ deskBrief.status }}</p>
        </div>
      </div>
      
      <button
        @click="submit"
        :disabled="form.processing"
        class="flex items-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors disabled:opacity-50"
      >
        <Save class="w-4 h-4" />
        Simpan Narasi
      </button>
    </div>

    <div class="bg-[#1A1A1A] rounded-xl border border-gray-800 p-6 space-y-6">
      <!-- Headline (Title) -->
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-2">
          Headline (Title)
        </label>
        <textarea
          v-model="form.title"
          rows="2"
          class="w-full bg-[#0F0F0F] border border-gray-800 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-colors"
          placeholder="Contoh: Pasar konstruktif di tengah perbaikan global..."
        ></textarea>
        <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
      </div>

      <!-- Sub-headline (Market Read) -->
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-2">
          Sub-headline (Market Read)
        </label>
        <textarea
          v-model="form.market_read"
          rows="3"
          class="w-full bg-[#0F0F0F] border border-gray-800 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-colors"
          placeholder="Penjelasan ringkas market hari ini..."
        ></textarea>
        <div v-if="form.errors.market_read" class="text-red-500 text-sm mt-1">{{ form.errors.market_read }}</div>
      </div>

      <!-- Regime Text (So What) -->
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-2">
          Regime Explanation (So What)
        </label>
        <textarea
          v-model="form.so_what"
          rows="4"
          class="w-full bg-[#0F0F0F] border border-gray-800 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-colors"
          placeholder="Penjelasan mengapa skor rejim ada di posisi saat ini..."
        ></textarea>
        <div v-if="form.errors.so_what" class="text-red-500 text-sm mt-1">{{ form.errors.so_what }}</div>
      </div>

      <!-- Analyst Takeaway (What to Do) -->
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-2">
          Analyst Takeaway (What to Do)
        </label>
        <textarea
          v-model="form.what_to_do"
          rows="5"
          class="w-full bg-[#0F0F0F] border border-gray-800 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-colors"
          placeholder="Rekomendasi spesifik / Takeaway analis..."
        ></textarea>
        <div v-if="form.errors.what_to_do" class="text-red-500 text-sm mt-1">{{ form.errors.what_to_do }}</div>
      </div>
    </div>
  </AdminLayout>
</template>
