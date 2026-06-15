<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Save, ChevronLeft, Image as ImageIcon } from '@lucide/vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps({
  research: {
    type: Object,
    default: null
  }
});

const isEdit = !!props.research;

const form = useForm({
  _method: isEdit ? 'PUT' : 'POST',
  title: props.research?.title || '',
  ticker: props.research?.ticker || '',
  sector: props.research?.sector || '',
  recommendation: props.research?.recommendation || 'BUY',
  target_price: props.research?.target_price || '',
  upside: props.research?.upside || '',
  report_type: props.research?.report_type || 'Equity Research',
  is_premium: props.research?.is_premium ? true : false,
  pdf_path: props.research?.pdf_path || '',
  image: props.research?.image || '',
  subtitle: props.research?.subtitle || '',
  content: props.research?.content || ''
});

const isRawHtmlMode = ref(false);

const imagePreview = ref(props.research?.image || null);
const isDragging = ref(false);

const handleImageChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const handleDrop = (e) => {
  isDragging.value = false;
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const handlePdfChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.pdf_path = file;
  }
};

const submit = () => {
  if (isEdit) {
    form.post(route('admin.katalog-riset.update', props.research.id), {
      forceFormData: true,
      preserveScroll: true
    });
  } else {
    form.post(route('admin.katalog-riset.store'), {
      forceFormData: true
    });
  }
};
</script>

<template>
  <Head :title="isEdit ? 'Edit Katalog Riset' : 'Tambah Katalog Riset'" />

  <AdminLayout>
    <div class="space-y-8 pb-12">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link :href="route('admin.katalog-riset.index')" class="p-2 bg-[#121614] border border-emerald-950/25 hover:bg-[#090b0a] rounded-xl text-slate-400 hover:text-slate-200 transition-colors">
          <ChevronLeft class="w-5 h-5" />
        </Link>
        <div>
          <h2 class="text-3xl font-extrabold tracking-tight text-white">
            {{ isEdit ? 'Edit Katalog Riset' : 'Tambah Katalog Riset' }}
          </h2>
          <p class="text-sm text-slate-400 mt-1">
            Isi detail dan laporan panjang HTML menggunakan editor di bawah ini.
          </p>
        </div>
      </div>

      <form @submit.prevent="submit" class="bg-[#121614] border border-emerald-950/30 rounded-2xl overflow-hidden shadow-xl shadow-slate-950/20">
        <div class="p-6 md:p-8 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Title -->
            <div class="space-y-1 md:col-span-2">
              <label for="title" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Judul Riset</label>
              <input 
                id="title"
                v-model="form.title"
                type="text" 
                required
                class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-650 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
              />
              <div v-if="form.errors.title" class="text-xs text-rose-500 font-semibold">{{ form.errors.title }}</div>
            </div>

            <!-- Ticker & Sector -->
            <div class="space-y-1">
              <label for="ticker" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ticker</label>
              <input 
                id="ticker"
                v-model="form.ticker"
                type="text" 
                class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-650 focus:outline-none focus:border-emerald-500 transition-all"
                placeholder="Contoh: BBCA"
              />
            </div>
            
            <div class="space-y-1">
              <label for="sector" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Sektor</label>
              <input 
                id="sector"
                v-model="form.sector"
                type="text" 
                class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-650 focus:outline-none focus:border-emerald-500 transition-all"
              />
            </div>

            <!-- Recommendation & Target Price -->
            <div class="space-y-1">
              <label for="recommendation" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rekomendasi</label>
              <select 
                id="recommendation"
                v-model="form.recommendation"
                class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-emerald-500 transition-all"
              >
                <option value="BUY">BUY</option>
                <option value="HOLD">HOLD</option>
                <option value="SELL">SELL</option>
              </select>
            </div>
            
            <div class="space-y-1">
              <label for="target_price" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Target Harga</label>
              <input 
                id="target_price"
                v-model="form.target_price"
                type="text" 
                class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-emerald-500 transition-all"
                placeholder="Contoh: 10.000"
              />
            </div>

            <!-- Upside & Report Type -->
            <div class="space-y-1">
              <label for="upside" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Upside</label>
              <input 
                id="upside"
                v-model="form.upside"
                type="text" 
                class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-emerald-500 transition-all"
                placeholder="Contoh: +15%"
              />
            </div>

            <div class="space-y-1">
              <label for="report_type" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tipe Laporan</label>
              <input 
                id="report_type"
                v-model="form.report_type"
                type="text" 
                class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-emerald-500 transition-all"
              />
            </div>
            
            <!-- File paths -->
            <div class="space-y-1">
              <label for="pdf_path" class="text-xs font-bold text-slate-400 uppercase tracking-wider">File PDF Laporan</label>
              <div class="flex items-center gap-3">
                <input 
                  id="pdf_path"
                  type="file" 
                  accept=".pdf"
                  @change="handlePdfChange"
                  class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-slate-100 focus:outline-none focus:border-emerald-500 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-emerald-500/10 file:text-emerald-500 hover:file:bg-emerald-500/20"
                />
                <a v-if="isEdit && typeof form.pdf_path === 'string' && form.pdf_path" :href="form.pdf_path" target="_blank" class="text-xs text-emerald-400 hover:underline whitespace-nowrap">Lihat PDF Saat Ini</a>
              </div>
            </div>

            <div class="space-y-1">
              <label for="image" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cover Image Laporan</label>
              <div 
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop"
                :class="[
                  isDragging ? 'border-emerald-500 bg-emerald-500/5' : 'border-emerald-950/40 bg-[#090b0a]',
                  'border-2 border-dashed rounded-xl p-4 flex flex-col items-center justify-center text-center cursor-pointer transition-all duration-300 relative min-h-[120px]'
                ]"
              >
                <input 
                  id="image"
                  type="file" 
                  class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                  accept="image/*"
                  @change="handleImageChange"
                />

                <div v-if="imagePreview" class="relative group max-h-[100px] rounded overflow-hidden">
                  <img 
                    :src="imagePreview" 
                    alt="Cover Preview" 
                    class="max-w-full max-h-[100px] object-contain"
                  />
                </div>
                <div v-else class="flex flex-col items-center gap-2">
                  <div class="p-2 bg-emerald-500/10 rounded-lg text-emerald-400 border border-emerald-500/20">
                    <ImageIcon class="w-5 h-5" />
                  </div>
                  <div>
                    <p class="text-xs font-semibold text-slate-200">Klik atau Drag Cover ke sini</p>
                    <p class="text-[10px] text-slate-500 mt-0.5">Maks 2MB (JPG, PNG)</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="space-y-1 md:col-span-2">
              <label class="flex items-center gap-2 cursor-pointer bg-[#090b0a] border border-emerald-950/40 p-4 rounded-xl">
                <input type="checkbox" v-model="form.is_premium" class="w-5 h-5 rounded text-emerald-600 focus:ring-emerald-500 bg-slate-900 border-slate-700" />
                <span class="text-sm font-semibold text-slate-200">Jadikan Laporan PREMIUM (Terkunci)</span>
              </label>
            </div>
          </div>

          <!-- HTML Content / WYSIWYG Editor -->
          <div class="space-y-1 mt-6">
            <div class="flex items-center justify-between mb-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Isi Artikel HTML (Vercel Legacy)</label>
              
              <!-- Toggle switch for Raw HTML -->
              <label class="flex items-center cursor-pointer">
                <div class="relative">
                  <input type="checkbox" v-model="isRawHtmlMode" class="sr-only" />
                  <div class="block bg-[#090b0a] border border-emerald-950/40 w-10 h-6 rounded-full transition-colors" :class="{'bg-emerald-600': isRawHtmlMode}"></div>
                  <div class="dot absolute left-1 top-1 bg-slate-300 w-4 h-4 rounded-full transition-transform" :class="{'translate-x-4 bg-white': isRawHtmlMode}"></div>
                </div>
                <div class="ml-3 text-xs font-medium text-slate-300">
                  Raw HTML Mode (Untuk Kode Kompleks)
                </div>
              </label>
            </div>

            <div v-if="!isRawHtmlMode" class="bg-[#090b0a] border border-emerald-950/40 rounded-xl overflow-hidden quill-wrapper">
              <QuillEditor 
                theme="snow" 
                v-model:content="form.content" 
                contentType="html"
                style="min-height: 400px; color: white;" 
              />
            </div>
            
            <div v-else class="bg-[#090b0a] border border-emerald-950/40 rounded-xl overflow-hidden">
              <textarea 
                v-model="form.content"
                class="w-full bg-transparent p-4 text-sm font-mono text-emerald-400 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                style="min-height: 400px;"
                placeholder="Paste kode HTML lengkap di sini..."
              ></textarea>
            </div>
          </div>

          <div class="space-y-1">
            <label for="subtitle" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Subtitle / Short Summary</label>
            <textarea 
              id="subtitle"
              v-model="form.subtitle"
              rows="3"
              class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-emerald-500 transition-all resize-none"
            ></textarea>
          </div>

        </div>

        <div class="px-6 py-4 bg-[#090b0a]/40 border-t border-emerald-950/30 flex justify-end">
          <button 
            type="submit" 
            :disabled="form.processing"
            class="inline-flex items-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-sm font-semibold text-white rounded-xl shadow-lg shadow-emerald-600/20 transition-all duration-200 cursor-pointer"
          >
            <Save class="w-4 h-4 mr-2" />
            {{ isEdit ? 'Perbarui Katalog' : 'Simpan Katalog' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<style>
/* Adjusting quill editor dark theme */
.quill-wrapper .ql-toolbar {
  background: #121614 !important;
  border: none !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
}
.quill-wrapper .ql-container {
  border: none !important;
  font-family: inherit !important;
  font-size: 14px !important;
}
.quill-wrapper .ql-editor {
  min-height: 400px;
}
.quill-wrapper .ql-stroke {
  stroke: #94a3b8 !important;
}
.quill-wrapper .ql-fill {
  fill: #94a3b8 !important;
}
.quill-wrapper .ql-picker-label {
  color: #94a3b8 !important;
}
</style>
