<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Save, Image as ImageIcon, Link as LinkIcon, Phone, FileText, Globe, CreditCard, UserCheck, BookOpen, BarChart2, BrainCircuit, ShieldAlert, TrendingUp } from '@lucide/vue';

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
  bank_account_info: props.settings.bank_account_info || '',
  trial_artikel_limit: parseInt(props.settings.trial_artikel_limit ?? 3),
  trial_riset_limit:   parseInt(props.settings.trial_riset_limit   ?? 3),
  openrouter_api_key: props.settings.openrouter_api_key || '',
  openrouter_default_model: props.settings.openrouter_default_model || 'anthropic/claude-3.5-sonnet',
  openrouter_fallback_model: props.settings.openrouter_fallback_model || 'openai/gpt-4o',
  maint_home: props.settings.maint_home === '1' || props.settings.maint_home === true,
  maint_katalog: props.settings.maint_katalog === '1' || props.settings.maint_katalog === true,
  maint_artikel: props.settings.maint_artikel === '1' || props.settings.maint_artikel === true,
  maint_news: props.settings.maint_news === '1' || props.settings.maint_news === true,
  maint_emiten: props.settings.maint_emiten === '1' || props.settings.maint_emiten === true,
  maint_ki_brief: props.settings.maint_ki_brief === '1' || props.settings.maint_ki_brief === true,
  maint_disclosure: props.settings.maint_disclosure === '1' || props.settings.maint_disclosure === true,
  maint_tentang: props.settings.maint_tentang === '1' || props.settings.maint_tentang === true,
  maint_mitra: props.settings.maint_mitra === '1' || props.settings.maint_mitra === true,
  maint_langganan: props.settings.maint_langganan === '1' || props.settings.maint_langganan === true,
  market_top_tickers: props.settings.market_top_tickers || 'BBRI.JK, TLKM.JK, ASII.JK, AMMN.JK, MDKA.JK',
  market_watchlist_tickers: props.settings.market_watchlist_tickers || 'BBRI.JK, TLKM.JK, ASII.JK, MDKA.JK',
  market_trending_tickers: props.settings.market_trending_tickers || 'BBRI.JK, TLKM.JK, ASII.JK, MDKA.JK, AMMN.JK, GOTO.JK',
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
    <div class="space-y-8">
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
        <button 
          @click="currentTab = 'bank'"
          :class="[
            currentTab === 'bank' 
              ? 'border-emerald-500 text-emerald-450 bg-[#090b0a]/10' 
              : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-emerald-800',
            'px-6 py-3 border-b-2 font-semibold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer'
          ]"
        >
          <span class="flex items-center gap-2">
            <CreditCard class="w-4 h-4" />
            Informasi Rekening
          </span>
        </button>
        <button 
          @click="currentTab = 'trial'"
          :class="[
            currentTab === 'trial' 
              ? 'border-emerald-500 text-emerald-450 bg-[#090b0a]/10' 
              : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-emerald-800',
            'px-6 py-3 border-b-2 font-semibold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer'
          ]"
        >
          <span class="flex items-center gap-2">
            <UserCheck class="w-4 h-4" />
            Akses Trial
          </span>
        </button>
        <button 
          @click="currentTab = 'ai'"
          :class="[
            currentTab === 'ai' 
              ? 'border-emerald-500 text-emerald-450 bg-[#090b0a]/10' 
              : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-emerald-800',
            'px-6 py-3 border-b-2 font-semibold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer'
          ]"
        >
          <span class="flex items-center gap-2">
            <BrainCircuit class="w-4 h-4" />
            AI Config
          </span>
        </button>
        <button 
          @click="currentTab = 'maintenance'"
          :class="[
            currentTab === 'maintenance' 
              ? 'border-emerald-500 text-emerald-450 bg-[#090b0a]/10' 
              : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-emerald-800',
            'px-6 py-3 border-b-2 font-semibold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer'
          ]"
        >
          <span class="flex items-center gap-2">
            <ShieldAlert class="w-4 h-4" />
            Maintenance
          </span>
        </button>
        <button 
          @click="currentTab = 'market'"
          :class="[
            currentTab === 'market' 
              ? 'border-emerald-500 text-emerald-450 bg-[#090b0a]/10' 
              : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-emerald-800',
            'px-6 py-3 border-b-2 font-semibold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer'
          ]"
        >
          <span class="flex items-center gap-2">
            <TrendingUp class="w-4 h-4" />
            Market Config
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

          <!-- Tab 4: Informasi Rekening -->
          <div v-if="currentTab === 'bank'" class="space-y-6 animate-fadeIn">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
              <div>
                <label for="bank_account_info" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rekening Tujuan</label>
                <p class="text-xxs text-slate-550 mt-1">Informasi rekening yang ditampilkan ke pengguna saat akan melakukan pembayaran atau perpanjangan berlangganan.</p>
              </div>
              <div class="md:col-span-2">
                <textarea 
                  id="bank_account_info"
                  v-model="form.bank_account_info"
                  rows="3"
                  class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250 resize-none"
                  placeholder="Misal: Marta Fikri 3370748356 bank BCA"
                ></textarea>
                <div v-if="form.errors.bank_account_info" class="text-xs text-rose-500 font-semibold mt-1">
                  {{ form.errors.bank_account_info }}
                </div>
              </div>
            </div>
          </div>

          <!-- Tab 5: Akses Trial -->
          <div v-if="currentTab === 'trial'" class="space-y-6 animate-fadeIn">
            <!-- Info Banner -->
            <div class="flex items-start gap-3 p-4 rounded-xl bg-emerald-500/8 border border-emerald-500/20">
              <UserCheck class="w-5 h-5 text-emerald-400 flex-shrink-0 mt-0.5" />
              <div>
                <p class="text-sm font-semibold text-emerald-300">Akses User Trial</p>
                <p class="text-xs text-slate-400 mt-1">
                  Pengguna yang sudah login tetapi <strong class="text-slate-300">belum berlangganan</strong> dianggap sebagai user trial.
                  Mereka hanya dapat membaca <strong class="text-slate-300">N artikel terbaru</strong> dan mengakses <strong class="text-slate-300">N riset terbaru</strong> sesuai batas yang Anda tentukan.
                </p>
              </div>
            </div>

            <!-- Artikel Limit -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
              <div>
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                  <BookOpen class="w-4 h-4 text-indigo-400" />
                  Batas Artikel Trial
                </label>
                <p class="text-xs text-slate-500 mt-1">Jumlah artikel terbaru yang dapat diakses penuh oleh user trial.</p>
              </div>
              <div class="md:col-span-2">
                <div class="flex items-center gap-4">
                  <input
                    type="number"
                    v-model.number="form.trial_artikel_limit"
                    min="0" max="100"
                    class="w-32 bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 text-center font-bold focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250"
                  />
                  <span class="text-sm text-slate-400">artikel terbaru</span>
                </div>
                <p class="text-xs text-slate-500 mt-2">
                  Set ke <span class="text-emerald-400 font-bold">0</span> untuk memblokir semua artikel bagi trial user.
                </p>
                <div v-if="form.errors.trial_artikel_limit" class="text-xs text-rose-500 font-semibold mt-1">
                  {{ form.errors.trial_artikel_limit }}
                </div>
              </div>
            </div>

            <div class="border-t border-emerald-950/30"></div>

            <!-- Riset Limit -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
              <div>
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                  <BarChart2 class="w-4 h-4 text-emerald-400" />
                  Batas Riset Trial
                </label>
                <p class="text-xs text-slate-500 mt-1">Jumlah riset terbaru yang dapat diakses penuh oleh user trial.</p>
              </div>
              <div class="md:col-span-2">
                <div class="flex items-center gap-4">
                  <input
                    type="number"
                    v-model.number="form.trial_riset_limit"
                    min="0" max="100"
                    class="w-32 bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 text-center font-bold focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250"
                  />
                  <span class="text-sm text-slate-400">riset terbaru</span>
                </div>
                <p class="text-xs text-slate-500 mt-2">
                  Set ke <span class="text-emerald-400 font-bold">0</span> untuk memblokir semua riset bagi trial user.
                </p>
                <div v-if="form.errors.trial_riset_limit" class="text-xs text-rose-500 font-semibold mt-1">
                  {{ form.errors.trial_riset_limit }}
                </div>
              </div>
            </div>

            <!-- Preview Info -->
            <div class="p-4 rounded-xl bg-[#090b0a] border border-emerald-950/30 text-xs text-slate-400 space-y-1">
              <p class="font-semibold text-slate-300 mb-2">Pratinjau konfigurasi saat ini:</p>
              <p>📄 Trial user dapat membaca <span class="text-white font-bold">{{ form.trial_artikel_limit }} artikel</span> terbaru secara penuh. Sisanya akan dipotong (paywall).</p>
              <p>📊 Trial user dapat mengakses <span class="text-white font-bold">{{ form.trial_riset_limit }} riset</span> terbaru secara penuh. Sisanya terkunci.</p>
            </div>
          </div>

          <!-- Tab 6: AI Config -->
          <div v-if="currentTab === 'ai'" class="space-y-6 animate-fadeIn">
            <div class="flex items-start gap-3 p-4 rounded-xl bg-indigo-500/8 border border-indigo-500/20">
              <BrainCircuit class="w-5 h-5 text-indigo-400 flex-shrink-0 mt-0.5" />
              <div>
                <p class="text-sm font-semibold text-indigo-300">OpenRouter Integration</p>
                <p class="text-xs text-slate-400 mt-1">
                  Konfigurasi API Key dan model AI yang digunakan untuk generate riset otomatis.
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">API Key</label>
              <div class="md:col-span-2 relative">
                <input 
                  v-model="form.openrouter_api_key"
                  type="password" 
                  class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250"
                  placeholder="sk-or-v1-..."
                />
                <div v-if="form.errors.openrouter_api_key" class="text-xs text-rose-500 font-semibold mt-1">
                  {{ form.errors.openrouter_api_key }}
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
              <div>
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Default Model</label>
                <p class="text-xxs text-slate-550 mt-1">Digunakan untuk mayoritas tugas.</p>
              </div>
              <div class="md:col-span-2 relative">
                <input 
                  v-model="form.openrouter_default_model"
                  type="text" 
                  class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
              <div>
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Fallback Model</label>
                <p class="text-xxs text-slate-550 mt-1">Digunakan jika model default gagal atau butuh penalaran tingkat lanjut.</p>
              </div>
              <div class="md:col-span-2 relative">
                <input 
                  v-model="form.openrouter_fallback_model"
                  type="text" 
                  class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250"
                />
              </div>
            </div>
          </div>
          <!-- Tab 7: Maintenance Config -->
          <div v-if="currentTab === 'maintenance'" class="space-y-6 animate-fadeIn">
            <div class="flex items-start gap-3 p-4 rounded-xl bg-rose-500/8 border border-rose-500/20">
              <ShieldAlert class="w-5 h-5 text-rose-400 flex-shrink-0 mt-0.5" />
              <div>
                <p class="text-sm font-semibold text-rose-300">Mode Perawatan (Maintenance)</p>
                <p class="text-xs text-slate-400 mt-1">
                  Aktifkan toggle di bawah ini untuk menutup halaman secara spesifik (publik akan melihat layar Maintenance 503 saat mengaksesnya).
                </p>
              </div>
            </div>

            <!-- Generate list of granular toggles -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Beranda -->
              <div class="p-4 rounded-xl bg-[#090b0a] border border-emerald-950/40 flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-bold text-slate-200">Beranda (Home)</h4>
                  <p class="text-xxs text-slate-500">Halaman utama website</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="form.maint_home" class="sr-only peer">
                  <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                </label>
              </div>
              
              <!-- Katalog -->
              <div class="p-4 rounded-xl bg-[#090b0a] border border-emerald-950/40 flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-bold text-slate-200">Katalog</h4>
                  <p class="text-xxs text-slate-500">Halaman direktori riset</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="form.maint_katalog" class="sr-only peer">
                  <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                </label>
              </div>
              
              <!-- Artikel -->
              <div class="p-4 rounded-xl bg-[#090b0a] border border-emerald-950/40 flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-bold text-slate-200">Artikel</h4>
                  <p class="text-xxs text-slate-500">Blog dan postingan artikel</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="form.maint_artikel" class="sr-only peer">
                  <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                </label>
              </div>
              
              <!-- News -->
              <div class="p-4 rounded-xl bg-[#090b0a] border border-emerald-950/40 flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-bold text-slate-200">Berita (News AI)</h4>
                  <p class="text-xxs text-slate-500">Berita dari generator AI</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="form.maint_news" class="sr-only peer">
                  <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                </label>
              </div>

              <!-- Emiten Hub -->
              <div class="p-4 rounded-xl bg-[#090b0a] border border-emerald-950/40 flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-bold text-slate-200">Emiten Hub</h4>
                  <p class="text-xxs text-slate-500">Data dan profil Emiten</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="form.maint_emiten" class="sr-only peer">
                  <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                </label>
              </div>
              
              <!-- KI Brief -->
              <div class="p-4 rounded-xl bg-[#090b0a] border border-emerald-950/40 flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-bold text-slate-200">KI Brief</h4>
                  <p class="text-xxs text-slate-500">Ringkasan riset KI</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="form.maint_ki_brief" class="sr-only peer">
                  <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                </label>
              </div>
              
              <!-- Disclosure Radar -->
              <div class="p-4 rounded-xl bg-[#090b0a] border border-emerald-950/40 flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-bold text-slate-200">Disclosure Radar</h4>
                  <p class="text-xxs text-slate-500">Keterbukaan informasi Bursa</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="form.maint_disclosure" class="sr-only peer">
                  <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                </label>
              </div>
              
              <!-- Tentang Kami -->
              <div class="p-4 rounded-xl bg-[#090b0a] border border-emerald-950/40 flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-bold text-slate-200">Tentang Kami</h4>
                  <p class="text-xxs text-slate-500">Halaman company profile</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="form.maint_tentang" class="sr-only peer">
                  <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                </label>
              </div>
              
              <!-- Mitra Analis -->
              <div class="p-4 rounded-xl bg-[#090b0a] border border-emerald-950/40 flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-bold text-slate-200">Mitra Analis</h4>
                  <p class="text-xxs text-slate-500">Halaman registrasi/info mitra</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="form.maint_mitra" class="sr-only peer">
                  <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                </label>
              </div>
              
              <!-- Langganan -->
              <div class="p-4 rounded-xl bg-[#090b0a] border border-emerald-950/40 flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-bold text-slate-200">Langganan</h4>
                  <p class="text-xxs text-slate-500">Halaman paket langganan (Subs)</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="form.maint_langganan" class="sr-only peer">
                  <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                </label>
              </div>

            </div>
          </div>

          <!-- Tab 8: Market Config -->
          <div v-if="currentTab === 'market'" class="space-y-6 animate-fadeIn">
            <div class="flex items-start gap-3 p-4 rounded-xl bg-emerald-500/8 border border-emerald-500/20">
              <TrendingUp class="w-5 h-5 text-emerald-400 flex-shrink-0 mt-0.5" />
              <div>
                <p class="text-sm font-semibold text-emerald-300">Konfigurasi Market Dashboard (News)</p>
                <p class="text-xs text-slate-400 mt-1">
                  Atur emiten/saham apa saja yang akan ditampilkan pada widget News Dashboard. 
                  Pisahkan dengan koma dan gunakan format Yahoo Finance (contoh: <strong class="text-white">BBRI.JK, TLKM.JK</strong>).
                </p>
              </div>
            </div>

            <!-- Top Movers Tickers -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
              <div>
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Top Movers (Top Banner)</label>
                <p class="text-xxs text-slate-550 mt-1">Muncul di banner atas halaman News.</p>
              </div>
              <div class="md:col-span-2">
                <textarea 
                  v-model="form.market_top_tickers"
                  rows="2"
                  class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250 resize-none"
                  placeholder="BBRI.JK, TLKM.JK, ASII.JK..."
                ></textarea>
                <div v-if="form.errors.market_top_tickers" class="text-xs text-rose-500 font-semibold mt-1">
                  {{ form.errors.market_top_tickers }}
                </div>
              </div>
            </div>

            <!-- Watchlist Tickers -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
              <div>
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Watchlist News</label>
                <p class="text-xxs text-slate-550 mt-1">Widget sidebar untuk berita terkait saham pilihan.</p>
              </div>
              <div class="md:col-span-2">
                <textarea 
                  v-model="form.market_watchlist_tickers"
                  rows="2"
                  class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250 resize-none"
                ></textarea>
                <div v-if="form.errors.market_watchlist_tickers" class="text-xs text-rose-500 font-semibold mt-1">
                  {{ form.errors.market_watchlist_tickers }}
                </div>
              </div>
            </div>

            <!-- Trending Tickers -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
              <div>
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Trending Tickers</label>
                <p class="text-xxs text-slate-550 mt-1">Kumpulan tombol filter cepat di sidebar.</p>
              </div>
              <div class="md:col-span-2">
                <textarea 
                  v-model="form.market_trending_tickers"
                  rows="2"
                  class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-550 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250 resize-none"
                ></textarea>
                <div v-if="form.errors.market_trending_tickers" class="text-xs text-rose-500 font-semibold mt-1">
                  {{ form.errors.market_trending_tickers }}
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
