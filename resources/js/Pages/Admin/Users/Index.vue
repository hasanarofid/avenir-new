<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
  <Head title="User Subscriber - Admin Console" />

  <AdminLayout>
    <div class="px-6 py-8 mx-auto max-w-7xl lg:px-8">
      <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl font-serif">
          Manajemen User & Subscriber
        </h1>
        <p class="mt-2 text-sm text-slate-400">
          Pantau status berlangganan dan riwayat trial dari para pembaca.
        </p>
      </div>

      <div class="bg-[#121614] border border-slate-800 rounded-xl overflow-hidden p-8 text-center text-slate-400">
        <div class="text-4xl mb-4">👥</div>
        <h3 class="text-lg font-medium text-white mb-2">Halaman Sedang Dibangun</h3>
        <p>Tabel manajemen user lengkap dengan fungsi intervensi status (trial vs active subscriber) akan segera hadir.</p>
      </div>
    </div>
  </AdminLayout>
</template>
