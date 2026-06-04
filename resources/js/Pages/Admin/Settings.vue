<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Save, Image as ImageIcon, Link as LinkIcon, Phone, FileText, Globe } from '@lucide/vue';

const props = defineProps({
  settings: {
    type: Object,
    required: true
  }
});

const currentTab = ref('site'); // Tab state: 'site', 'links', 'logo'

const form = useForm({
  site_name: props.settings.site_name || '',
  site_description: props.settings.site_description || '',
  whatsapp_number: props.settings.whatsapp_number || '',
  playstore_link: props.settings.playstore_link || '',
  site_logo: null
});

const logoPreview = ref(props.settings.site_logo_url || null);
const isDragging = ref(false);

const handleLogoChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.site_logo = file;
    logoPreview.value = URL.createObjectURL(file);
  }
};

const handleDrop = (e) => {
  isDragging.value = false;
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    form.site_logo = file;
    logoPreview.value = URL.createObjectURL(file);
  }
};

const submit = () => {
  form.post(route('admin.settings.update'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      form.site_logo = null;
    }
  });
};
</script>

<template>
  <Head title="Web Settings" />

  <AdminLayout>
    <div class="space-y-8 max-w-4xl">
      <!-- Title -->
      <div>
        <h2 class="text-3xl font-extrabold tracking-tight text-white">Web Settings</h2>
        <p class="text-sm text-slate-400 mt-1">Kelola konfigurasi global website Anda seperti logo, kontak WhatsApp, dan lainnya.</p>
      </div>

      <!-- Navigation Tabs (SaaS Style) -->
      <div class="flex border-b border-emerald-950/30">
        <button 
          @click="currentTab = 'site'"
          :class="[
            currentTab === 'site' 
              ? 'border-emerald-500 text-emerald-450 bg-[#090b0a]/10' 
              : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-emerald-800',
            'px-6 py-3 border-b-2 font-semibold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer'
          ]"
        >
          <span class="flex items-center gap-2">
            <Globe class="w-4 h-4" />
            Profil Situs
          </span>
        </button>
        <button 
          @click="currentTab = 'links'"
          :class="[
            currentTab === 'links' 
              ? 'border-emerald-500 text-emerald-450 bg-[#090b0a]/10' 
              : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-emerald-800',
            'px-6 py-3 border-b-2 font-semibold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer'
          ]"
        >
          <span class="flex items-center gap-2">
            <LinkIcon class="w-4 h-4" />
            Kontak & Tautan
          </span>
        </button>
        <button 
          @click="currentTab = 'logo'"
          :class="[
            currentTab === 'logo' 
              ? 'border-emerald-500 text-emerald-450 bg-[#090b0a]/10' 
              : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-emerald-800',
            'px-6 py-3 border-b-2 font-semibold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer'
          ]"
        >
          <span class="flex items-center gap-2">
            <ImageIcon class="w-4 h-4" />
            Logo Website
          </span>
        </button>
      </div>

      <!-- Settings Card -->
      <form @submit.prevent="submit" class="bg-[#121614] border border-emerald-950/30 rounded-2xl overflow-hidden shadow-xl shadow-slate-950/20">
        <div class="p-6 md:p-8">
          
          <!-- Tab 1: Profil Situs -->
          <div v-if="currentTab === 'site'" class="space-y-6 animate-fadeIn">
            <!-- Site Name -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
              <label for="site_name" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Website</label>
              <div class="md:col-span-2 relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                  <FileText class="w-4 h-4" />
                </span>
                <input 
                  id="site_name"
                  v-model="form.site_name"
                  type="text" 
                  class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250"
                  placeholder="Masukkan Nama Web..."
                />
                <div v-if="form.errors.site_name" class="text-xs text-rose-500 font-semibold mt-1">
                  {{ form.errors.site_name }}
                </div>
              </div>
            </div>

            <!-- Site Description -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
              <div>
                <label for="site_description" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tentang Kami</label>
                <p class="text-xxs text-slate-550 mt-1">Deskripsi singkat yang tampil di footer atau halaman tentang kami.</p>
              </div>
              <div class="md:col-span-2">
                <textarea 
                  id="site_description"
                  v-model="form.site_description"
                  rows="5"
                  class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250 resize-none"
                  placeholder="Tulis deskripsi singkat..."
                ></textarea>
                <div v-if="form.errors.site_description" class="text-xs text-rose-500 font-semibold mt-1">
                  {{ form.errors.site_description }}
                </div>
              </div>
            </div>
          </div>

          <!-- Tab 2: Kontak & Tautan -->
          <div v-if="currentTab === 'links'" class="space-y-6 animate-fadeIn">
            <!-- Whatsapp Number -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
              <div>
                <label for="whatsapp_number" class="text-xs font-bold text-slate-400 uppercase tracking-wider">No. WhatsApp</label>
                <p class="text-xxs text-slate-550 mt-1">Gunakan kode negara tanpa spasi, contoh: 628123456789</p>
              </div>
              <div class="md:col-span-2 relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                  <Phone class="w-4 h-4" />
                </span>
                <input 
                  id="whatsapp_number"
                  v-model="form.whatsapp_number"
                  type="text" 
                  class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250"
                  placeholder="628123456..."
                />
                <div v-if="form.errors.whatsapp_number" class="text-xs text-rose-500 font-semibold mt-1">
                  {{ form.errors.whatsapp_number }}
                </div>
              </div>
            </div>

            <!-- Play Store Link -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
              <label for="playstore_link" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Link Play Store</label>
              <div class="md:col-span-2 relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                  <LinkIcon class="w-4 h-4" />
                </span>
                <input 
                  id="playstore_link"
                  v-model="form.playstore_link"
                  type="url" 
                  class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250"
                  placeholder="https://play.google.com/store/apps/details?id=..."
                />
                <div v-if="form.errors.playstore_link" class="text-xs text-rose-500 font-semibold mt-1">
                  {{ form.errors.playstore_link }}
                </div>
              </div>
            </div>
          </div>

          <!-- Tab 3: Logo Website -->
          <div v-if="currentTab === 'logo'" class="space-y-6 animate-fadeIn">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
              <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Logo Website</h3>
                <p class="text-xxs text-slate-550 mt-1">Logo resmi situs web Anda. Format yang disarankan adalah PNG dengan latar belakang transparan.</p>
              </div>

              <div class="md:col-span-2 space-y-6">
                <!-- Advanced Dashed Drag-and-Drop Area -->
                <div 
                  @dragover.prevent="isDragging = true"
                  @dragleave.prevent="isDragging = false"
                  @drop.prevent="handleDrop"
                  :class="[
                    isDragging ? 'border-emerald-500 bg-emerald-500/5' : 'border-emerald-950/45 bg-[#090b0a]/20 hover:border-emerald-800',
                    'border-2 border-dashed rounded-3xl p-8 flex flex-col items-center justify-center text-center cursor-pointer transition-all duration-300 relative min-h-[220px]'
                  ]"
                >
                  <input 
                    id="site_logo"
                    type="file" 
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                    accept="image/*"
                    @change="handleLogoChange"
                  />

                  <!-- Dynamic Icon/Preview -->
                  <div v-if="logoPreview" class="relative group max-w-[200px] max-h-[120px] rounded-xl overflow-hidden">
                    <img 
                      :src="logoPreview" 
                      alt="Site Logo Preview" 
                      class="max-w-full max-h-full object-contain p-2"
                    />
                  </div>
                  <div v-else class="flex flex-col items-center gap-3">
                    <div class="p-4 bg-emerald-500/10 rounded-2xl text-emerald-400 border border-emerald-500/20">
                      <ImageIcon class="w-8 h-8" />
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-slate-200">Drag & drop logo Anda di sini</p>
                      <p class="text-xs text-slate-500 mt-1">atau klik untuk memilih dari komputer Anda</p>
                    </div>
                  </div>
                </div>

                <p class="text-xxs text-slate-500">Maksimal ukuran berkas 2 MB (PNG, JPG, JPEG, WEBP)</p>
                <div v-if="form.errors.site_logo" class="text-xs text-rose-500 font-semibold mt-1">
                  {{ form.errors.site_logo }}
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Footer Action -->
        <div class="px-6 py-4 bg-[#090b0a]/40 border-t border-emerald-950/30 flex justify-end">
          <button 
            type="submit" 
            :disabled="form.processing"
            class="inline-flex items-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-sm font-semibold text-white rounded-xl shadow-lg shadow-emerald-600/20 hover:shadow-emerald-600/30 transition-all duration-200 cursor-pointer"
          >
            <Save class="w-4 h-4 mr-2" />
            Simpan Konfigurasi
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<style scoped>
.animate-fadeIn {
  animation: fadeIn 0.25s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
