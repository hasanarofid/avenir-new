<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Konfirmasi Pembayaran - Admin Console" />

  <AdminLayout>
    <div class="px-6 py-8 mx-auto max-w-7xl lg:px-8">
      <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl font-serif">
          Konfirmasi Pembayaran
        </h1>
        <p class="mt-2 text-sm text-slate-400">
          Kelola dan validasi pembayaran langganan dari pengguna.
        </p>
      </div>

      <div class="bg-[#121614] border border-slate-800 rounded-xl overflow-hidden p-8 text-center text-slate-400">
        <div class="text-4xl mb-4">💳</div>
        <h3 class="text-lg font-medium text-white mb-2">Halaman Sedang Dibangun</h3>
        <p>Fitur konfirmasi pembayaran dan integrasi approval sedang dalam tahap pengembangan.</p>
      </div>
    </div>
  </AdminLayout>
</template>
