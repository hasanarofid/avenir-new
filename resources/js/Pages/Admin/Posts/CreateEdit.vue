<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Save, ChevronLeft, Image as ImageIcon } from '@lucide/vue';

const props = defineProps({
  post: {
    type: Object,
    default: null
  },
  categories: {
    type: Array,
    required: true
  }
});

const isEdit = !!props.post;

const form = useForm({
  _method: isEdit ? 'PUT' : 'POST',
  category_id: props.post?.category_id || '',
  title: props.post?.title || '',
  slug: props.post?.slug || '',
  content: props.post?.content || '',
  image: null,
  status: props.post?.status || 'draft',
  is_featured: props.post?.is_featured ?? false
});

const imagePreview = ref(props.post?.image_url || null);
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

const submit = () => {
  if (isEdit) {
    form.post(route('admin.posts.update', props.post.id), {
      forceFormData: true,
      preserveScroll: true
    });
  } else {
    form.post(route('admin.posts.store'), {
      forceFormData: true
    });
  }
};
</script>

<template>
  <Head :title="isEdit ? 'Edit Postingan' : 'Tulis Postingan Baru'" />

  <AdminLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link :href="route('admin.posts.index')" class="p-2 bg-[#121614] border border-emerald-950/25 hover:bg-[#090b0a] rounded-xl text-slate-400 hover:text-slate-200 transition-colors">
          <ChevronLeft class="w-5 h-5" />
        </Link>
        <div>
          <h2 class="text-3xl font-extrabold tracking-tight text-white">
            {{ isEdit ? 'Edit Postingan' : 'Tulis Postingan Baru' }}
          </h2>
          <p class="text-sm text-slate-400 mt-1">
            {{ isEdit ? 'Perbarui konten artikel atau deskripsi produk Anda.' : 'Tulis artikel atau deskripsi produk baru untuk diterbitkan.' }}
          </p>
        </div>
      </div>

      <!-- Form Card -->
      <form @submit.prevent="submit" class="bg-[#121614] border border-emerald-950/30 rounded-2xl overflow-hidden shadow-xl shadow-slate-950/20">
        <div class="p-6 md:p-8 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Title -->
            <div class="space-y-1 md:col-span-2">
              <label for="title" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Judul Postingan</label>
              <input 
                id="title"
                v-model="form.title"
                type="text" 
                required
                class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-650 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250"
                placeholder="Masukkan judul postingan..."
                @input="!isEdit && (form.slug = form.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''))"
              />
              <div v-if="form.errors.title" class="text-xs text-rose-500 font-semibold">{{ form.errors.title }}</div>
            </div>

            <!-- Slug -->
            <div class="space-y-1">
              <label for="slug" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Slug (URL)</label>
              <input 
                id="slug"
                v-model="form.slug"
                type="text" 
                required
                class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-650 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250"
                placeholder="slug-url-postingan"
              />
              <div v-if="form.errors.slug" class="text-xs text-rose-500 font-semibold">{{ form.errors.slug }}</div>
            </div>

            <!-- Category -->
            <div class="space-y-1">
              <label for="category_id" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kategori</label>
              <select 
                id="category_id"
                v-model="form.category_id"
                required
                class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250"
              >
                <option value="" disabled>Pilih Kategori...</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
              <div v-if="form.errors.category_id" class="text-xs text-rose-500 font-semibold">{{ form.errors.category_id }}</div>
            </div>
          </div>

          <!-- Content (Textarea) -->
          <div class="space-y-1">
            <label for="content" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Konten / Isi Artikel</label>
            <textarea 
              id="content"
              v-model="form.content"
              rows="12"
              required
              class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-650 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250 resize-none"
              placeholder="Tulis artikel atau konten produk di sini..."
            ></textarea>
            <div v-if="form.errors.content" class="text-xs text-rose-500 font-semibold">{{ form.errors.content }}</div>
          </div>

          <!-- Image and Configuration Settings Grid -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start border-t border-emerald-950/30 pt-6">
            <!-- Image Upload Preview & Selector -->
            <div class="md:col-span-2 space-y-4">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Gambar Cover / Postingan</label>
              
              <!-- Dashed Upload Zone -->
              <div 
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop"
                :class="[
                  isDragging ? 'border-emerald-500 bg-emerald-500/5' : 'border-emerald-950/30 bg-[#090b0a]/20 hover:border-emerald-800',
                  'border-2 border-dashed rounded-3xl p-6 flex flex-col items-center justify-center text-center cursor-pointer transition-all duration-300 relative min-h-[160px]'
                ]"
              >
                <input 
                  id="image"
                  type="file" 
                  class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                  accept="image/*"
                  @change="handleImageChange"
                />

                <!-- Dynamic Icon/Preview -->
                <div v-if="imagePreview" class="relative group max-w-[240px] max-h-[120px] rounded-xl overflow-hidden">
                  <img 
                    :src="imagePreview" 
                    alt="Post Cover Preview" 
                    class="max-w-full max-h-full object-contain p-2"
                  />
                </div>
                <div v-else class="flex flex-col items-center gap-2">
                  <div class="p-3 bg-emerald-500/10 rounded-xl text-emerald-400 border border-emerald-500/20">
                    <ImageIcon class="w-6 h-6" />
                  </div>
                  <div>
                    <p class="text-xs font-semibold text-slate-200">Drag & drop cover di sini</p>
                    <p class="text-[10px] text-slate-500 mt-0.5">Maksimal 2 MB (PNG, JPG, WEBP)</p>
                  </div>
                </div>
              </div>
              
              <div v-if="form.errors.image" class="text-xs text-rose-500 font-semibold mt-1">{{ form.errors.image }}</div>
            </div>

            <!-- Configuration / Visibility -->
            <div class="space-y-4 md:col-span-1 md:border-l border-emerald-950/30 md:pl-6">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Pengaturan Publikasi</label>
              
              <!-- Status -->
              <div class="space-y-1">
                <label for="status" class="text-xxs font-bold text-slate-500">Status</label>
                <select 
                  id="status"
                  v-model="form.status"
                  class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-lg px-3 py-2 text-xs text-slate-100 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-250"
                >
                  <option value="draft">Draft (Simpan Robotonal)</option>
                  <option value="published">Published (Tampil Publik)</option>
                </select>
              </div>

              <!-- Featured -->
              <div class="flex items-center gap-2 pt-2">
                <input 
                  id="is_featured"
                  v-model="form.is_featured"
                  type="checkbox" 
                  class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 bg-[#090b0a] border-emerald-950/40"
                />
                <label for="is_featured" class="text-xs font-semibold text-slate-350">Rekomendasikan (Featured)</label>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Action -->
        <div class="px-6 py-4 bg-[#090b0a]/40 border-t border-emerald-950/30 flex justify-end">
          <button 
            type="submit" 
            :disabled="form.processing"
            class="inline-flex items-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-sm font-semibold text-white rounded-xl shadow-lg shadow-emerald-600/20 transition-all duration-200 cursor-pointer"
          >
            <Save class="w-4 h-4 mr-2" />
            {{ isEdit ? 'Perbarui Postingan' : 'Simpan & Publikasikan' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
