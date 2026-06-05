<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeft, Play, FileText, CheckCircle, RefreshCcw, Save, Settings as SettingsIcon, Globe } from '@lucide/vue';
import Swal from 'sweetalert2';

const props = defineProps({
    project: Object,
});

const isGenerating = ref(false);

const generateForm = useForm({
    model_type: 'default',
});

const generateDraft = () => {
    isGenerating.value = true;
    generateForm.post(route('admin.research-generator.generate', props.project.id), {
        onFinish: () => {
            isGenerating.value = false;
        }
    });
};

const publishToKatalog = () => {
    Swal.fire({
        title: 'Terbitkan ke Katalog?',
        text: "Data JSON ini akan otomatis dikonversi menjadi halaman Katalog.",
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
            router.post(route('admin.research-generator.publish', props.project.id), {}, {
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Riset berhasil diterbitkan ke Katalog.',
                        background: '#121614',
                        color: '#f8fafc',
                        confirmButtonColor: '#059669'
                    });
                }
            });
        }
    });
};

const getLatestDraft = () => {
    if (!props.project.drafts || props.project.drafts.length === 0) return null;
    // Get the most recent draft
    return props.project.drafts[props.project.drafts.length - 1];
};

const latestDraft = ref(getLatestDraft());

const draftForm = useForm({
    structured_json: latestDraft.value ? latestDraft.value.structured_json : null,
});

let pollInterval = null;
const draftCount = ref(props.project.drafts ? props.project.drafts.length : 0);

const startPolling = () => {
    if (!pollInterval) {
        pollInterval = setInterval(() => {
            router.reload({
                only: ['project'],
                preserveScroll: true,
                onSuccess: () => {
                    const newDraftsLength = props.project.drafts ? props.project.drafts.length : 0;
                    
                    if (props.project.status !== 'generating') {
                        stopPolling();
                        
                        if (newDraftsLength > draftCount.value) {
                            // Success: New draft added!
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Draft riset berhasil di-generate.',
                                background: '#121614',
                                color: '#f8fafc',
                                confirmButtonColor: '#059669'
                            });
                            const newDraft = getLatestDraft();
                            latestDraft.value = newDraft;
                            draftForm.structured_json = newDraft.structured_json;
                            draftCount.value = newDraftsLength;
                        } else if (props.project.status === 'draft') {
                            // Failed: Status reverted to draft but no new draft
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Proses generate gagal. Pastikan API Key valid dan saldo OpenRouter cukup, atau cek log server.',
                                background: '#121614',
                                color: '#f8fafc',
                                confirmButtonColor: '#059669'
                            });
                        }
                    }
                }
            });
        }, 3000);
    }
};

const stopPolling = () => {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
};

watch(() => props.project.status, (newStatus) => {
    if (newStatus === 'generating') {
        startPolling();
    } else {
        stopPolling();
    }
}, { immediate: true });

onUnmounted(() => {
    stopPolling();
});
</script>

<template>
    <Head :title="project.title" />

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
                    <h1 class="text-2xl font-bold text-slate-100">{{ project.ticker }} - {{ project.title }}</h1>
                    <p class="text-sm text-slate-400 mt-1">Status: <span class="font-bold text-emerald-400 uppercase">{{ project.status }}</span></p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Documents & Actions -->
                <div class="space-y-6">
                    <div class="bg-[#121614] rounded-2xl border border-emerald-950/20 p-6">
                        <h3 class="text-sm font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2 mb-4">
                            <FileText class="w-4 h-4 text-emerald-500" />
                            Dokumen Referensi
                        </h3>
                        <div class="space-y-3">
                            <div v-for="doc in project.documents" :key="doc.id" class="p-3 bg-[#090b0a] rounded-xl border border-emerald-950/20">
                                <p class="text-sm font-semibold text-slate-300 truncate" :title="doc.file_name">{{ doc.file_name }}</p>
                                <div class="flex items-center gap-2 mt-2 text-xs">
                                    <span v-if="doc.extracted_text" class="text-emerald-400 flex items-center gap-1">
                                        <CheckCircle class="w-3 h-3" /> Teks Terekstrak
                                    </span>
                                    <span v-else class="text-amber-400 flex items-center gap-1">
                                        <RefreshCcw class="w-3 h-3 animate-spin" /> Mengekstrak...
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#121614] rounded-2xl border border-emerald-950/20 p-6">
                        <h3 class="text-sm font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2 mb-4">
                            <SettingsIcon class="w-4 h-4 text-emerald-500" />
                            AI Generation
                        </h3>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Model AI</label>
                                <select 
                                    v-model="generateForm.model_type"
                                    class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-emerald-500 transition-colors"
                                >
                                    <option value="default">Default (Standard)</option>
                                    <option value="fallback">Fallback (Advanced)</option>
                                </select>
                            </div>
                            
                            <button 
                                @click="generateDraft"
                                :disabled="isGenerating || project.status === 'generating'"
                                class="w-full flex justify-center items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 disabled:bg-emerald-800 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-all"
                            >
                                <template v-if="project.status === 'generating' || isGenerating">
                                    <RefreshCcw class="w-4 h-4 animate-spin" />
                                    <span>Sedang Generate...</span>
                                </template>
                                <template v-else>
                                    <Play class="w-4 h-4" />
                                    <span>Generate Riset</span>
                                </template>
                            </button>
                            <p class="text-xs text-slate-500 text-center">Proses generate membutuhkan waktu 30-60 detik.</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Draft Editor Preview -->
                <div class="lg:col-span-2">
                    <div class="bg-[#121614] rounded-2xl border border-emerald-950/20 flex flex-col h-[600px]">
                        <div class="p-4 border-b border-emerald-950/20 flex justify-between items-center bg-[#090b0a]/50 rounded-t-2xl">
                            <h3 class="text-sm font-bold text-slate-200 uppercase tracking-wider">Hasil Draft AI</h3>
                            <span v-if="latestDraft" class="text-xs text-slate-500">Model: {{ latestDraft.model_used }}</span>
                        </div>
                        <div class="p-6 overflow-y-auto flex-1">
                            <div v-if="!latestDraft" class="h-full flex flex-col items-center justify-center text-slate-500">
                                <FileText class="w-12 h-12 mb-3 text-emerald-900/50" />
                                <p class="text-sm">Belum ada draft riset yang di-generate.</p>
                                <p class="text-xs mt-1 text-center max-w-xs">Pilih model AI dan klik tombol Generate Riset untuk memulai.</p>
                            </div>
                            
                            <!-- Simple JSON Viewer / Editor for MVP -->
                            <div v-else class="space-y-6">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-slate-400">Rating</label>
                                        <div class="text-lg font-bold text-emerald-400">{{ draftForm.structured_json?.rating || '-' }}</div>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-bold text-slate-400">Target Price</label>
                                        <div class="text-lg font-bold text-emerald-400">{{ draftForm.structured_json?.target_price || '-' }}</div>
                                    </div>
                                </div>
                                
                                <div class="space-y-4">
                                    <div v-for="(content, sectionKey) in draftForm.structured_json?.sections" :key="sectionKey" class="space-y-2">
                                        <label class="text-xs font-bold text-slate-400 uppercase">{{ sectionKey.replace('_', ' ') }}</label>
                                        <!-- If array (like highlights/risks), show as JSON block for now -->
                                        <textarea v-if="typeof content === 'string'"
                                            v-model="draftForm.structured_json.sections[sectionKey]"
                                            rows="4"
                                            class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-emerald-500 transition-colors"
                                        ></textarea>
                                        <div v-else class="p-3 bg-[#090b0a] rounded-xl border border-emerald-950/40 text-xs font-mono text-slate-300 overflow-x-auto">
                                            <pre>{{ JSON.stringify(content, null, 2) }}</pre>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end mt-6 gap-3">
                                    <button class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2">
                                        <Save class="w-4 h-4" />
                                        Simpan Draft
                                    </button>
                                    <button @click="publishToKatalog" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2">
                                        <Globe class="w-4 h-4" />
                                        Terbitkan ke Katalog
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
