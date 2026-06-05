<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Mitra Analis - Admin Console" />

  <AdminLayout>
    <div class="px-6 py-8 mx-auto max-w-7xl lg:px-8">
      <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl font-serif">
          Manajemen Mitra Analis
        </h1>
        <p class="mt-2 text-sm text-slate-400">
          Kelola pendaftaran mitra analis baru dan pantau mitra aktif.
        </p>
      </div>

      <div class="bg-[#121614] border border-slate-800 rounded-xl overflow-hidden p-8 text-center text-slate-400">
        <div class="text-4xl mb-4">🤝</div>
        <h3 class="text-lg font-medium text-white mb-2">Halaman Sedang Dibangun</h3>
        <p>Modul verifikasi profil mitra dan bagi hasil (revenue share) sedang dalam tahap pengembangan.</p>
      </div>
    </div>
  </AdminLayout>
</template>
