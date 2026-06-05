<script setup>
import { ref } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Play, Globe, FileText, CheckCircle, Save, Settings as SettingsIcon, AlertCircle, Image as ImageIcon } from '@lucide/vue';
import Swal from 'sweetalert2';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const page = usePage();

const generateForm = useForm({
    source_type: 'url',
    url: '',
    manual_text: '',
});

const publishForm = useForm({
    title: '',
    badge: '',
    excerpt: '',
    cover_image_file: null,
    content_html: '',
});

const isGenerating = ref(false);
const generatedData = ref(null);

const generateNews = () => {
    isGenerating.value = true;
    generatedData.value = null;
    
    generateForm.post(route('admin.news-generator.generate'), {
        preserveScroll: true,
        onSuccess: (response) => {
            isGenerating.value = false;
            const flash = response.props.flash;
            if (flash && flash.generated_news) {
                generatedData.value = flash.generated_news;
                publishForm.title = generatedData.value.title;
                publishForm.badge = generatedData.value.badge;
                publishForm.excerpt = generatedData.value.excerpt;
                publishForm.content_html = generatedData.value.content_html;
            }
        },
        onError: () => {
            isGenerating.value = false;
        }
    });
};

const publishNews = () => {
    Swal.fire({
        title: 'Terbitkan Berita?',
        text: "Berita ini akan langsung terpublikasi di halaman depan Avenir News.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#1e293b',
        confirmButtonText: 'Ya, Terbitkan!',
        cancelButtonText: 'Batal',
        background: '#121614',
        color: '#f8fafc',
    }).then((result) => {
        if (result.isConfirmed) {
            publishForm.post(route('admin.news-generator.publish'), {
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Berita berhasil diterbitkan.',
                        background: '#121614',
                        color: '#f8fafc',
                        confirmButtonColor: '#059669'
                    });
                    generatedData.value = null;
                    generateForm.reset();
                }
            });
        }
    });
};
</script>

<template>
    <Head title="AI News Generator" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-slate-100">AI News Generator</h1>
                <p class="text-sm text-slate-400 mt-1">Generate berita finansial harian secara otomatis menggunakan AI dari URL atau teks sumber.</p>
            </div>

            <!-- Error Alerts -->
            <div v-if="Object.keys(generateForm.errors).length > 0" class="bg-red-900/30 border border-red-500/50 rounded-xl p-4 flex gap-3">
                <AlertCircle class="w-5 h-5 text-red-400 shrink-0" />
                <div class="text-sm text-red-200">
                    <p v-for="(error, key) in generateForm.errors" :key="key">{{ error }}</p>
                </div>
            </div>

            <!-- Error Banner for Publish Form -->
            <div v-if="Object.keys(publishForm.errors).length > 0" class="mb-4 bg-red-900/50 border border-red-500/50 rounded-xl p-4 flex items-start gap-3">
                <AlertCircle class="w-5 h-5 text-red-400 mt-0.5 flex-shrink-0" />
                <div>
                    <h3 class="text-sm font-bold text-red-400">Gagal menerbitkan berita:</h3>
                    <ul class="mt-1 text-sm text-red-300 list-disc list-inside">
                        <li v-for="(error, key) in publishForm.errors" :key="key">
                            {{ error }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Source Input -->
                <div class="space-y-6">
                    <div class="bg-[#121614] rounded-2xl border border-emerald-950/20 p-6">
                        <h3 class="text-sm font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2 mb-4">
                            <Globe class="w-4 h-4 text-emerald-500" />
                            Sumber Berita
                        </h3>
                        <div class="space-y-4">
                            <!-- Source Type Toggle -->
                            <div class="flex p-1 bg-[#090b0a] rounded-lg border border-emerald-950/20">
                                <button 
                                    @click="generateForm.source_type = 'url'"
                                    :class="['flex-1 py-1.5 text-xs font-semibold rounded-md transition-colors', generateForm.source_type === 'url' ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:text-slate-200']"
                                >
                                    Dari URL
                                </button>
                                <button 
                                    @click="generateForm.source_type = 'manual'"
                                    :class="['flex-1 py-1.5 text-xs font-semibold rounded-md transition-colors', generateForm.source_type === 'manual' ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:text-slate-200']"
                                >
                                    Teks Manual
                                </button>
                            </div>

                            <!-- URL Input -->
                            <div v-if="generateForm.source_type === 'url'" class="space-y-2">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">URL Sumber Berita</label>
                                <input 
                                    v-model="generateForm.url"
                                    type="url"
                                    placeholder="https://finance.yahoo.com/..."
                                    class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-emerald-500 transition-colors"
                                >
                                <p class="text-[10px] text-slate-500">Sistem akan mengambil teks dari halaman web tersebut untuk ditulis ulang oleh AI.</p>
                            </div>

                            <!-- Manual Text Input -->
                            <div v-if="generateForm.source_type === 'manual'" class="space-y-2">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Teks Berita / Artikel</label>
                                <textarea 
                                    v-model="generateForm.manual_text"
                                    rows="10"
                                    placeholder="Paste isi berita mentah di sini..."
                                    class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-emerald-500 transition-colors resize-none"
                                ></textarea>
                                <p class="text-[10px] text-slate-500">Gunakan opsi ini jika URL diblokir oleh anti-scraping.</p>
                            </div>
                            
                            <button 
                                @click="generateNews"
                                :disabled="isGenerating || (generateForm.source_type === 'url' && !generateForm.url) || (generateForm.source_type === 'manual' && !generateForm.manual_text)"
                                class="w-full flex justify-center items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 disabled:bg-emerald-800 disabled:text-emerald-400/50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-all"
                            >
                                <template v-if="isGenerating">
                                    <span class="animate-pulse">Sedang Memproses AI...</span>
                                </template>
                                <template v-else>
                                    <Play class="w-4 h-4" />
                                    <span>Rewrite Berita (AI)</span>
                                </template>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column: AI Output & Editor -->
                <div class="lg:col-span-2">
                    <div class="bg-[#121614] rounded-2xl border border-emerald-950/20 flex flex-col h-[600px]">
                        <div class="p-4 border-b border-emerald-950/20 flex justify-between items-center bg-[#090b0a]/50 rounded-t-2xl">
                            <h3 class="text-sm font-bold text-slate-200 uppercase tracking-wider">Hasil Draft AI</h3>
                        </div>
                        <div class="p-6 overflow-y-auto flex-1">
                            <div v-if="!generatedData" class="h-full flex flex-col items-center justify-center text-slate-500">
                                <FileText class="w-12 h-12 mb-3 text-emerald-900/50" />
                                <p class="text-sm">Belum ada berita yang di-generate.</p>
                                <p class="text-xs mt-1 text-center max-w-xs">Pilih sumber, lalu klik "Rewrite Berita" untuk melihat hasil AI.</p>
                            </div>
                            
                            <!-- Editor -->
                            <div v-else class="space-y-6">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Judul Berita</label>
                                    <input 
                                        v-model="publishForm.title"
                                        type="text"
                                        class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-lg font-bold text-slate-100 focus:outline-none focus:border-emerald-500 transition-colors"
                                    >
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-400 uppercase">Badge / Kategori Utama</label>
                                        <input 
                                            v-model="publishForm.badge"
                                            type="text"
                                            class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-emerald-500 transition-colors"
                                        >
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-400 uppercase">Gambar Cover (Upload)</label>
                                        <div class="relative">
                                            <input 
                                                type="file"
                                                accept="image/*"
                                                @input="publishForm.cover_image_file = $event.target.files[0]"
                                                class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-slate-100 focus:outline-none focus:border-emerald-500 transition-colors file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-emerald-900/30 file:text-emerald-400 hover:file:bg-emerald-900/50"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Ringkasan (Excerpt)</label>
                                    <textarea 
                                        v-model="publishForm.excerpt"
                                        rows="2"
                                        class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-emerald-500 transition-colors resize-none"
                                    ></textarea>
                                </div>

                                <div class="space-y-2 flex-1 flex flex-col">
                                    <label class="text-xs font-bold text-slate-400 uppercase">Isi Berita (HTML Format)</label>
                                    <div class="bg-white text-black rounded-xl overflow-hidden mt-1 flex-1">
                                        <QuillEditor 
                                            v-model:content="publishForm.content_html"
                                            contentType="html"
                                            theme="snow"
                                            class="h-64"
                                        />
                                    </div>
                                </div>

                                <div class="flex justify-end mt-6 gap-3">
                                    <button @click="publishNews" :disabled="publishForm.processing" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2">
                                        <Save class="w-4 h-4" />
                                        Terbitkan ke News
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
