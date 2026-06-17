<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeft, Save, UploadCloud, FileText, X } from '@lucide/vue';
import Swal from 'sweetalert2';

const form = useForm({
    ticker: '',
    title: '',
    prompt: '',
    documents: [],
});

const fileInput = ref(null);

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    files.forEach(file => {
        if (file.type === 'application/pdf') {
            form.documents.push(file);
        } else {
            Swal.fire({
                title: 'Format Tidak Sesuai',
                text: 'Hanya file PDF yang diizinkan: ' + file.name,
                icon: 'error',
                background: '#121614',
                color: '#cbd5e1',
                confirmButtonColor: '#10b981'
            });
        }
    });
    // Reset input so the same file can be selected again if removed
    if (fileInput.value) fileInput.value.value = '';
};

const removeFile = (index) => {
    form.documents.splice(index, 1);
};

const submit = () => {
    form.post(route('admin.research-generator.store'));
};
</script>

<template>
    <Head title="Buat Project AI Research" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link 
                    :href="route('admin.research-generator.index')"
                    class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors"
                >
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-slate-100">Buat Project Riset Baru</h1>
                    <p class="text-sm text-slate-400 mt-1">Upload dokumen laporan keuangan untuk dianalisis oleh AI.</p>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-[#121614] rounded-2xl border border-emerald-950/20 p-6 shadow-xl">
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Ticker -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Ticker Saham <span class="text-rose-500">*</span></label>
                            <input 
                                v-model="form.ticker" 
                                type="text" 
                                placeholder="Contoh: BBRI"
                                class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 uppercase focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                                required
                            />
                            <div v-if="form.errors.ticker" class="text-xs text-rose-500 mt-1">{{ form.errors.ticker }}</div>
                        </div>

                        <!-- Judul -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Judul Riset <span class="text-rose-500">*</span></label>
                            <input 
                                v-model="form.title" 
                                type="text" 
                                placeholder="Contoh: BBRI - Q3 2024 Earnings Update"
                                class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                                required
                            />
                            <div v-if="form.errors.title" class="text-xs text-rose-500 mt-1">{{ form.errors.title }}</div>
                        </div>
                    </div>

                    <!-- Custom Prompt -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Instruksi Khusus (Prompt) <span class="text-slate-500 normal-case font-normal ml-1">(Opsional)</span></label>
                        <textarea 
                            v-model="form.prompt" 
                            rows="3"
                            placeholder="Contoh: Fokuskan analisis pada pertumbuhan penyaluran kredit segmen mikro dan dampaknya terhadap NIM..."
                            class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                        ></textarea>
                        <div v-if="form.errors.prompt" class="text-xs text-rose-500 mt-1">{{ form.errors.prompt }}</div>
                    </div>

                    <div class="border-t border-emerald-950/20 pt-6">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Dokumen Laporan (PDF) <span class="text-rose-500">*</span></label>
                        
                        <!-- File Upload Area -->
                        <div 
                            class="border-2 border-dashed border-emerald-900/30 rounded-2xl p-8 text-center hover:bg-emerald-900/5 hover:border-emerald-500/50 transition-colors cursor-pointer"
                            @click="$refs.fileInput.click()"
                        >
                            <input 
                                type="file" 
                                ref="fileInput" 
                                @change="handleFileSelect" 
                                accept=".pdf"
                                multiple
                                class="hidden" 
                            />
                            <div class="flex flex-col items-center justify-center">
                                <UploadCloud class="w-10 h-10 text-emerald-500 mb-3" />
                                <p class="text-sm text-slate-300 font-semibold mb-1">Klik untuk upload dokumen PDF</p>
                                <p class="text-xs text-slate-500">Maksimal 20MB per file. Mendukung multi-upload.</p>
                            </div>
                        </div>
                        <div v-if="form.errors.documents" class="text-xs text-rose-500 mt-2">{{ form.errors.documents }}</div>

                        <!-- Selected Files List -->
                        <div v-if="form.documents.length > 0" class="mt-4 space-y-2">
                            <div 
                                v-for="(file, index) in form.documents" 
                                :key="index"
                                class="flex items-center justify-between p-3 rounded-xl bg-[#090b0a] border border-emerald-950/20"
                            >
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="w-8 h-8 rounded-lg bg-rose-500/10 flex items-center justify-center shrink-0">
                                        <FileText class="w-4 h-4 text-rose-400" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm text-slate-200 font-semibold truncate">{{ file.name }}</p>
                                        <p class="text-xs text-slate-500">{{ (file.size / 1024 / 1024).toFixed(2) }} MB</p>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    @click.stop="removeFile(index)"
                                    class="p-2 text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors"
                                >
                                    <X class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-emerald-950/20">
                        <Link 
                            :href="route('admin.research-generator.index')"
                            class="px-5 py-2.5 text-sm font-semibold text-slate-300 hover:text-white transition-colors"
                        >
                            Batal
                        </Link>
                        <button 
                            type="submit" 
                            :disabled="form.processing || form.documents.length === 0"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 disabled:bg-emerald-800 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-emerald-900/20"
                        >
                            <Save class="w-4 h-4" />
                            <span>{{ form.processing ? 'Menyimpan...' : 'Simpan & Ekstrak Dokumen' }}</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AdminLayout>
</template>
