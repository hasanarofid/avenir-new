<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Notifikasi & Blast Email - Admin Console" />

  <AdminLayout>
    <div class="px-6 py-8 mx-auto max-w-7xl lg:px-8">
      <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl font-serif">
            Notifikasi & Blast Email
          </h1>
          <p class="mt-2 text-sm text-slate-400">
            Kirimkan notifikasi artikel baru dan buat blast email roundup ke seluruh subscriber aktif.
          </p>
        </div>
        <div>
          <button type="button" class="inline-flex items-center justify-center rounded-md bg-[#1B6B3A] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#155028] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1B6B3A]">
            Buat Notifikasi Baru
          </button>
        </div>
      </div>

      <div class="bg-[#121614] border border-slate-800 rounded-xl overflow-hidden p-8 text-center text-slate-400">
        <div class="text-4xl mb-4">📨</div>
        <h3 class="text-lg font-medium text-white mb-2">Halaman Sedang Dibangun</h3>
        <p>Integrasi dengan Resend API (Edge Functions) untuk pengiriman email roundup blast sedang disiapkan.</p>
      </div>
    </div>
  </AdminLayout>
</template>
