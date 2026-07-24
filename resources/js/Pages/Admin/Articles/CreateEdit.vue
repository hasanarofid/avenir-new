<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Save, ChevronLeft, Image as ImageIcon, Eye } from '@lucide/vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps({
  article: {
    type: Object,
    default: null
  }
});

const isEdit = !!props.article;

const form = useForm({
  _method: isEdit ? 'PUT' : 'POST',
  title: props.article?.title || '',
  category: props.article?.category || 'Edukasi',
  badge: props.article?.badge || '',
  excerpt: props.article?.excerpt || '',
  content: props.article?.content || '',
  is_paid: props.article?.is_paid ? true : false,
  status: props.article?.status || 'published',
  image: '',
  is_preview: false
});

const page = usePage();

const isFormValid = computed(() => {
  return form.title && form.title.trim() !== '';
});

const localContent = ref(form.content);

const hasRawHtmlTags = (str) => {
  return typeof str === 'string' && (
    str.includes('class=') || 
    str.includes('<div') || 
    str.includes('<table') || 
    str.includes('<style') || 
    str.includes('<section') || 
    str.includes('<svg')
  );
};

const isRawHtmlMode = ref(hasRawHtmlTags(props.article?.content || form.content));

watch(localContent, (newVal) => {
  form.content = newVal;
});

watch(() => form.content, (newVal) => {
  if (newVal && hasRawHtmlTags(newVal) && !isRawHtmlMode.value) {
    isRawHtmlMode.value = true;
  }
  if (localContent.value !== newVal) {
    localContent.value = newVal;
  }
});
const imagePreview = ref(props.article?.cover_image || null);
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

const saveAsDraftAndPreview = () => {
  form.status = 'draft';
  form.is_preview = true;
  
  // Open blank window immediately to bypass popup blockers
  const previewWindow = window.open('', '_blank');
  if (previewWindow) {
    previewWindow.document.write('<div style="font-family:sans-serif;padding:2rem;text-align:center;">Menyimpan draft dan menyiapkan pratinjau...</div>');
  }

  submit((page) => {
    const slug = page.props.flash?.preview_slug;
    if (slug && previewWindow) {
      previewWindow.location.href = `/artikel/${slug}`;
    } else if (previewWindow) {
      previewWindow.close();
    }
  }, () => {
    if (previewWindow) previewWindow.close();
  });
};

const saveAndPublish = () => {
  form.status = 'published';
  form.is_preview = false;
  submit();
};

const submit = (onSuccessCb = null, onErrorCb = null) => {
  const options = {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: (page) => {
      if (typeof onSuccessCb === 'function') onSuccessCb(page);
    },
    onError: (errors) => {
      if (typeof onErrorCb === 'function') onErrorCb(errors);
    }
  };

  if (isEdit) {
    form.post(route('admin.articles.update', props.article.id), options);
  } else {
    form.post(route('admin.articles.store'), options);
  }
};
</script>

<template>
  <Head :title="isEdit ? 'Edit Artikel' : 'Tambah Artikel'" />

  <AdminLayout>
    <div class="space-y-8 pb-12">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link :href="route('admin.articles.index')" class="p-2 bg-[#121614] border border-emerald-950/25 hover:bg-[#090b0a] rounded-xl text-slate-400 hover:text-slate-200 transition-colors">
          <ChevronLeft class="w-5 h-5" />
        </Link>
        <div>
          <h2 class="text-3xl font-extrabold tracking-tight text-white">
            {{ isEdit ? 'Edit Artikel' : 'Tambah Artikel' }}
          </h2>
          <p class="text-sm text-slate-400 mt-1">
            Isi detail dan artikel panjang HTML menggunakan editor di bawah ini.
          </p>
        </div>
      </div>

      <form @submit.prevent="saveAndPublish" class="bg-[#121614] border border-emerald-950/30 rounded-2xl overflow-hidden shadow-xl shadow-slate-950/20">
        <div class="p-6 md:p-8 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Title -->
            <div class="space-y-1 md:col-span-2">
              <label for="title" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Judul Artikel</label>
              <input 
                id="title"
                v-model="form.title"
                type="text" 
                required
                class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-650 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
              />
              <div v-if="form.errors.title" class="text-xs text-rose-500 font-semibold">{{ form.errors.title }}</div>
            </div>

            <!-- Category & Badge -->
            <div class="space-y-1">
              <label for="category" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kategori</label>
              <input 
                id="category"
                v-model="form.category"
                type="text" 
                class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-650 focus:outline-none focus:border-emerald-500 transition-all"
                placeholder="Contoh: Edukasi, Tutorial, Market Insight"
              />
            </div>
            
            <div class="space-y-1">
              <label for="badge" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Badge (Opsional)</label>
              <input 
                id="badge"
                v-model="form.badge"
                type="text" 
                class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-650 focus:outline-none focus:border-emerald-500 transition-all"
                placeholder="Contoh: Hot, Trending, Info"
              />
            </div>

            <!-- Status -->
            <div class="space-y-1">
              <label for="status" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status</label>
              <select 
                id="status"
                v-model="form.status"
                class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-emerald-500 transition-all"
              >
                <option value="published">Published</option>
                <option value="draft">Draft</option>
              </select>
            </div>

            <!-- Empty slot to balance the grid -->
            <div class="space-y-1 flex items-center pt-5">
              <label class="flex items-center gap-2 cursor-pointer bg-[#090b0a] border border-emerald-950/40 p-4 rounded-xl w-full">
                <input type="checkbox" v-model="form.is_paid" class="w-5 h-5 rounded text-emerald-600 focus:ring-emerald-500 bg-slate-900 border-slate-700" />
                <span class="text-sm font-semibold text-slate-200">Jadikan Artikel PREMIUM (Terkunci)</span>
              </label>
            </div>
            
            <!-- File paths -->
            <div class="space-y-1 md:col-span-2">
              <label for="image" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cover Image Artikel</label>
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
                    <p class="text-[10px] text-slate-500 mt-0.5">Maks 5MB (JPG, PNG)</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="space-y-1 mt-6">
            <label for="excerpt" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Excerpt / Ringkasan Pendek</label>
            <textarea 
              id="excerpt"
              v-model="form.excerpt"
              rows="3"
              class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-emerald-500 transition-all resize-none"
            ></textarea>
          </div>

          <!-- HTML Content / WYSIWYG Editor -->
          <div class="space-y-1 mt-6">
            <div class="flex items-center justify-between mb-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Isi Artikel HTML</label>
              
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
                v-model:content="localContent" 
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

        </div>

        <div class="px-6 py-4 bg-[#090b0a]/40 border-t border-emerald-950/30 flex justify-end gap-3">
          <button 
            type="button" 
            @click="saveAsDraftAndPreview"
            :disabled="form.processing || !isFormValid"
            class="inline-flex items-center px-5 py-2.5 bg-slate-800 hover:bg-slate-700 disabled:opacity-50 text-sm font-semibold text-white rounded-xl shadow-lg transition-all duration-200 cursor-pointer"
          >
            <Eye class="w-4 h-4 mr-2" />
            Lihat Preview
          </button>

          <button 
            type="submit" 
            :disabled="form.processing || !isFormValid"
            class="inline-flex items-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-sm font-semibold text-white rounded-xl shadow-lg shadow-emerald-600/20 transition-all duration-200 cursor-pointer"
          >
            <Save class="w-4 h-4 mr-2" />
            Simpan & Publish
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
