<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeft, Play, FileText, CheckCircle, RefreshCcw, Save, Settings as SettingsIcon, Globe, AlertTriangle, Plus, Trash2 } from 'lucide-vue-next';
import Swal from 'sweetalert2';

const props = defineProps({
    project: Object,
});

const isGenerating = ref(false);
const activeTab = ref('overview');

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
        confirmButtonColor: '#22c55e',
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
                        confirmButtonColor: '#22c55e'
                    });
                }
            });
        }
    });
};

const getLatestDraft = () => {
    if (!props.project.drafts || props.project.drafts.length === 0) return null;
    return props.project.drafts[props.project.drafts.length - 1];
};

const latestDraft = ref(getLatestDraft());

const getInitialJson = () => {
    if (!latestDraft.value) return {};
    let json = latestDraft.value.structured_json;
    if (typeof json === 'string') {
        try {
            json = JSON.parse(json);
        } catch (e) {
            json = {};
        }
    }
    return json || {};
};

const draftForm = useForm({
    structured_json: getInitialJson(),
});

// Helper functions to safely get/set properties on the structured JSON
const getProp = (key, defaultValue = '') => {
    if (!draftForm.structured_json) return defaultValue;
    if (draftForm.structured_json[key] !== undefined) {
        return draftForm.structured_json[key];
    }
    if (draftForm.structured_json.sections && draftForm.structured_json.sections[key] !== undefined) {
        // Fallback to older MVP format structure
        const sec = draftForm.structured_json.sections[key];
        return (typeof sec === 'object' && sec !== null) ? (sec.content || defaultValue) : sec;
    }
    return defaultValue;
};

const setProp = (key, val) => {
    if (!draftForm.structured_json) {
        draftForm.structured_json = {};
    }
    
    const rootFields = [
        'ticker', 'company_name', 'sector', 'document_type', 'analysis_date',
        'rating', 'target_price', 'target_price_note', 'current_price', 'upside_downside',
        'risk_level', 'conviction_level', 'time_horizon', 'model_confidence', 'quality_score', 'title', 'disclaimer'
    ];
    
    if (rootFields.includes(key)) {
        draftForm.structured_json[key] = val;
    } else {
        if (draftForm.structured_json[key] !== undefined) {
            draftForm.structured_json[key] = val;
        } else if (draftForm.structured_json.sections && draftForm.structured_json.sections[key] !== undefined) {
            const sec = draftForm.structured_json.sections[key];
            if (typeof sec === 'object' && sec !== null) {
                draftForm.structured_json.sections[key].content = val;
            } else {
                draftForm.structured_json.sections[key] = val;
            }
        } else {
            draftForm.structured_json[key] = val;
        }
    }
};

const getArrayProp = (key) => {
    const val = getProp(key, []);
    if (typeof val === 'string') {
        return val.split('\n').filter(s => s.trim().length > 0).map(s => s.replace(/^[-\*\u2022]\s*/, ''));
    }
    return Array.isArray(val) ? val : [];
};

const updateArrayItem = (key, idx, val) => {
    const arr = [...getArrayProp(key)];
    arr[idx] = val;
    setProp(key, arr);
};

const addArrayItem = (key) => {
    const arr = [...getArrayProp(key)];
    arr.push('');
    setProp(key, arr);
};

const removeArrayItem = (key, idx) => {
    const arr = [...getArrayProp(key)];
    arr.splice(idx, 1);
    setProp(key, arr);
};

const getValuationView = (subKey, defaultValue = '') => {
    const view = getProp('valuation_view', {});
    if (typeof view === 'object' && view !== null) {
        return view[subKey] !== undefined ? view[subKey] : defaultValue;
    }
    return defaultValue;
};

const setValuationView = (subKey, val) => {
    let view = getProp('valuation_view', {});
    if (typeof view !== 'object' || view === null) {
        view = {};
    }
    view[subKey] = val;
    setProp('valuation_view', view);
};

const getScenario = (subKey, defaultValue = '') => {
    const scenario = getProp('scenario_analysis', {});
    if (typeof scenario === 'object' && scenario !== null) {
        return scenario[subKey] !== undefined ? scenario[subKey] : defaultValue;
    }
    return defaultValue;
};

const setScenario = (subKey, val) => {
    let scenario = getProp('scenario_analysis', {});
    if (typeof scenario !== 'object' || scenario === null) {
        scenario = {};
    }
    scenario[subKey] = val;
    setProp('scenario_analysis', scenario);
};

const saveDraft = () => {
    draftForm.put(route('admin.research-generator.update-draft', [props.project.id, latestDraft.value.id]), {
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: 'Tersimpan!',
                text: 'Draft riset berhasil diperbarui.',
                background: '#121614',
                color: '#f8fafc',
                confirmButtonColor: '#22c55e'
            });
        }
    });
};

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
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Draft riset berhasil di-generate.',
                                background: '#121614',
                                color: '#f8fafc',
                                confirmButtonColor: '#22c55e'
                            });
                            const newDraft = getLatestDraft();
                            latestDraft.value = newDraft;
                            draftForm.structured_json = getInitialJson();
                            draftCount.value = newDraftsLength;
                        } else if (props.project.status === 'draft') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Proses generate gagal. Pastikan API Key valid dan saldo OpenRouter cukup, atau cek log server.',
                                background: '#121614',
                                color: '#f8fafc',
                                confirmButtonColor: '#22c55e'
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

const needsPolling = () => {
    if (props.project.status === 'generating') return true;
    
    if (props.project.documents) {
        const isExtracting = props.project.documents.some(doc => !doc.extracted_text);
        if (isExtracting) return true;
    }
    
    return false;
};

watch(() => props.project, () => {
    if (needsPolling()) {
        startPolling();
    } else {
        stopPolling();
    }
}, { immediate: true, deep: true });

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
                <div class="text-left">
                    <h1 class="text-2xl font-bold text-slate-100">{{ project.ticker }} - {{ project.title }}</h1>
                    <p class="text-sm text-slate-400 mt-1">Status: <span class="font-bold text-avenir-neon uppercase">{{ project.status }}</span></p>
                </div>
            </div>

            <!-- Warning Banner -->
            <div v-if="latestDraft && (!getProp('target_price') || getProp('quality_score', 80) < 70)" class="bg-amber-950/20 border border-amber-500/30 rounded-2xl p-4 flex items-start gap-3 text-left">
                <AlertTriangle class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" />
                <div>
                    <h4 class="text-sm font-bold text-amber-400">Peringatan Kualitas Draft AI</h4>
                    <p class="text-xs text-amber-300/80 mt-1">
                        <span v-if="!getProp('target_price')">Target Price belum ditentukan (NULL). </span>
                        <span v-if="getProp('quality_score', 80) < 70">Skor kualitas QC rendah ({{ getProp('quality_score') }}/100). </span>
                        Pastikan Anda melengkapi analisis sebelum menerbitkan riset ini ke Katalog.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Documents & AI Controller Panel -->
                <div class="space-y-6">
                    <!-- Referensi Dokumen -->
                    <div class="bg-avenir-surface rounded-2xl border border-avenir-border p-6 text-left">
                        <h3 class="text-sm font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2 mb-4">
                            <FileText class="w-4 h-4 text-avenir-neon" />
                            Dokumen Referensi
                        </h3>
                        <div class="space-y-3">
                            <div v-for="doc in project.documents" :key="doc.id" class="p-3 bg-avenir-bg rounded-xl border border-avenir-border">
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

                    <!-- AI Controller -->
                    <div class="bg-avenir-surface rounded-2xl border border-avenir-border p-6 text-left">
                        <h3 class="text-sm font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2 mb-4">
                            <SettingsIcon class="w-4 h-4 text-avenir-neon" />
                            AI Generation
                        </h3>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Model AI</label>
                                <select 
                                    v-model="generateForm.model_type"
                                    class="w-full bg-avenir-bg border border-avenir-border rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                >
                                    <option value="default">Default (Standard)</option>
                                    <option value="fallback">Fallback (Advanced)</option>
                                </select>
                            </div>
                            
                            <button 
                                @click="generateDraft"
                                :disabled="isGenerating || project.status === 'generating'"
                                class="w-full flex justify-center items-center gap-2 px-4 py-2.5 bg-avenir-neon hover:bg-emerald-600 disabled:bg-emerald-800 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-all shadow-neon"
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

                <!-- Right Column: Structured Tabbed Editor -->
                <div class="lg:col-span-2">
                    <div v-if="!latestDraft" class="bg-avenir-surface rounded-2xl border border-avenir-border flex flex-col h-[500px] items-center justify-center text-slate-500 p-6 text-center">
                        <FileText class="w-12 h-12 mb-3 text-emerald-900/50" />
                        <p class="text-sm">Belum ada draft riset yang di-generate.</p>
                        <p class="text-xs mt-1 text-center max-w-xs">Pilih model AI dan klik tombol Generate Riset untuk memulai.</p>
                    </div>

                    <div v-else class="space-y-6">
                        <!-- Snapshot Cards Header -->
                        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                            <!-- Rating -->
                            <div class="bg-avenir-surface border border-avenir-border rounded-xl p-3.5 text-left">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Rating</span>
                                <div class="text-lg font-bold mt-1 text-avenir-neon flex items-center gap-1.5">
                                    <select 
                                        :value="getProp('rating', 'NEUTRAL')"
                                        @change="setProp('rating', $event.target.value)"
                                        class="bg-transparent text-avenir-neon font-bold border-none p-0 focus:ring-0 focus:outline-none cursor-pointer text-lg font-sans w-full"
                                    >
                                        <option value="BUY" class="bg-avenir-surface text-emerald-400">BUY</option>
                                        <option value="HOLD" class="bg-avenir-surface text-yellow-500">HOLD</option>
                                        <option value="SELL" class="bg-avenir-surface text-red-500">SELL</option>
                                        <option value="NEUTRAL" class="bg-avenir-surface text-slate-400">NEUTRAL</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Target Price -->
                            <div class="bg-avenir-surface border border-avenir-border rounded-xl p-3.5 text-left">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Target Price</span>
                                <input 
                                    type="text" 
                                    :value="getProp('target_price', '')"
                                    @input="setProp('target_price', $event.target.value || null)"
                                    placeholder="N/A"
                                    class="bg-transparent text-white font-bold border-none p-0 focus:ring-0 focus:outline-none w-full text-base font-sans mt-1.5"
                                />
                            </div>

                            <!-- Upside/Downside -->
                            <div class="bg-avenir-surface border border-avenir-border rounded-xl p-3.5 text-left">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Upside</span>
                                <input 
                                    type="text" 
                                    :value="getProp('upside_downside', '')"
                                    @input="setProp('upside_downside', $event.target.value || null)"
                                    placeholder="N/A"
                                    class="bg-transparent text-white font-bold border-none p-0 focus:ring-0 focus:outline-none w-full text-base font-sans mt-1.5"
                                />
                            </div>

                            <!-- Risk Level -->
                            <div class="bg-avenir-surface border border-avenir-border rounded-xl p-3.5 text-left">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Risk Level</span>
                                <select 
                                    :value="getProp('risk_level', 'Medium')"
                                    @change="setProp('risk_level', $event.target.value)"
                                    class="bg-transparent text-white font-bold border-none p-0 focus:ring-0 focus:outline-none cursor-pointer w-full text-base font-sans mt-1.5"
                                >
                                    <option value="Low" class="bg-avenir-surface text-white">Low</option>
                                    <option value="Medium" class="bg-avenir-surface text-white">Medium</option>
                                    <option value="High" class="bg-avenir-surface text-white">High</option>
                                </select>
                            </div>

                            <!-- Conviction -->
                            <div class="bg-avenir-surface border border-avenir-border rounded-xl p-3.5 text-left">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Conviction</span>
                                <select 
                                    :value="getProp('conviction_level', 'Medium')"
                                    @change="setProp('conviction_level', $event.target.value)"
                                    class="bg-transparent text-white font-bold border-none p-0 focus:ring-0 focus:outline-none cursor-pointer w-full text-base font-sans mt-1.5"
                                >
                                    <option value="Low" class="bg-avenir-surface text-white">Low</option>
                                    <option value="Medium" class="bg-avenir-surface text-white">Medium</option>
                                    <option value="High" class="bg-avenir-surface text-white">High</option>
                                </select>
                            </div>

                            <!-- Confidence Score -->
                            <div class="bg-avenir-surface border border-avenir-border rounded-xl p-3.5 text-left">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">QC Score</span>
                                <div class="text-base font-bold text-white font-sans mt-1.5 flex items-center">
                                    <span :class="getProp('quality_score', 80) >= 85 ? 'text-emerald-400' : 'text-amber-500'">
                                        {{ getProp('quality_score', 80) }}/100
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Main Tabbed Editor Panel -->
                        <div class="bg-avenir-surface rounded-2xl border border-avenir-border flex flex-col min-h-[500px]">
                            <!-- Tabs header -->
                            <div class="flex border-b border-avenir-border bg-avenir-bg/60 rounded-t-2xl overflow-x-auto scrollbar-none sticky top-[52px] z-20">
                                <button 
                                    v-for="tab in [
                                        { id: 'overview', label: 'Overview' },
                                        { id: 'valuation', label: 'Valuation' },
                                        { id: 'financial', label: 'Financial' },
                                        { id: 'risks', label: 'Risks & Flags' },
                                        { id: 'sources', label: 'Sources' },
                                        { id: 'editorial', label: 'Editorial' }
                                    ]" 
                                    :key="tab.id"
                                    @click="activeTab = tab.id"
                                    :class="activeTab === tab.id ? 'border-avenir-neon text-avenir-neon bg-avenir-surface' : 'border-transparent text-slate-400 hover:text-white hover:bg-white/2'"
                                    class="px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap transition-all focus:outline-none"
                                >
                                    {{ tab.label }}
                                </button>
                            </div>

                            <!-- Tab Contents -->
                            <div class="p-6 flex-1 text-left space-y-6">
                                <!-- OVERVIEW TAB -->
                                <div v-if="activeTab === 'overview'" class="space-y-6">
                                    <div class="space-y-2">
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Judul Laporan</label>
                                        <input 
                                            type="text" 
                                            :value="getProp('title', '')" 
                                            @input="setProp('title', $event.target.value)"
                                            class="w-full bg-avenir-bg border border-avenir-border rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                        />
                                    </div>

                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center">
                                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Executive Summary (Bullet Points)</label>
                                            <button @click="addArrayItem('executive_summary')" class="p-1 hover:bg-slate-800 rounded text-avenir-neon flex items-center gap-1 text-xs font-bold transition-all">
                                                <Plus class="w-3.5 h-3.5" /> Tambah Poin
                                            </button>
                                        </div>
                                        <div class="space-y-2">
                                            <div v-for="(bullet, idx) in getArrayProp('executive_summary')" :key="idx" class="flex gap-2 items-center">
                                                <span class="text-avenir-neon font-bold">•</span>
                                                <input 
                                                    type="text" 
                                                    :value="bullet" 
                                                    @input="updateArrayItem('executive_summary', idx, $event.target.value)"
                                                    class="flex-1 bg-avenir-bg border border-avenir-border rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                                />
                                                <button @click="removeArrayItem('executive_summary', idx)" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                                    <Trash2 class="w-4 h-4" />
                                                </button>
                                            </div>
                                            <p v-if="getArrayProp('executive_summary').length === 0" class="text-xs text-slate-500 italic">Belum ada poin. Klik Tambah Poin.</p>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Event Overview</label>
                                        <textarea 
                                            :value="getProp('event_overview', '')" 
                                            @input="setProp('event_overview', $event.target.value)"
                                            rows="5"
                                            class="w-full bg-avenir-bg border border-avenir-border rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                        ></textarea>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Analyst Conclusion</label>
                                        <textarea 
                                            :value="getProp('analyst_conclusion', '')" 
                                            @input="setProp('analyst_conclusion', $event.target.value)"
                                            rows="5"
                                            class="w-full bg-avenir-bg border border-avenir-border rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                        ></textarea>
                                    </div>
                                </div>

                                <!-- VALUATION TAB -->
                                <div v-if="activeTab === 'valuation'" class="space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Metode Valuasi Utama</label>
                                            <input 
                                                type="text" 
                                                :value="getValuationView('method', '')" 
                                                @input="setValuationView('method', $event.target.value)"
                                                class="w-full bg-avenir-bg border border-avenir-border rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                            />
                                        </div>
                                        <div class="space-y-2">
                                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Time Horizon</label>
                                            <input 
                                                type="text" 
                                                :value="getProp('time_horizon', '6-12 bulan')" 
                                                @input="setProp('time_horizon', $event.target.value)"
                                                class="w-full bg-avenir-bg border border-avenir-border rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                            />
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Catatan Target Price / Valuasi View</label>
                                        <textarea 
                                            :value="getProp('target_price_note', '')" 
                                            @input="setProp('target_price_note', $event.target.value)"
                                            rows="3"
                                            class="w-full bg-avenir-bg border border-avenir-border rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                        ></textarea>
                                    </div>

                                    <!-- Valuation Assumptions -->
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center">
                                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Asumsi Valuasi (Assumptions)</label>
                                            <button @click="addArrayItem('valuation_assumptions')" class="p-1 hover:bg-slate-800 rounded text-avenir-neon flex items-center gap-1 text-xs font-bold transition-all">
                                                <Plus class="w-3.5 h-3.5" /> Tambah Asumsi
                                            </button>
                                        </div>
                                        <div class="space-y-2">
                                            <div v-for="(item, idx) in getArrayProp('valuation_assumptions')" :key="idx" class="flex gap-2 items-center">
                                                <span class="text-avenir-neon font-bold">•</span>
                                                <input 
                                                    type="text" 
                                                    :value="item" 
                                                    @input="updateArrayItem('valuation_assumptions', idx, $event.target.value)"
                                                    class="flex-1 bg-avenir-bg border border-avenir-border rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                                />
                                                <button @click="removeArrayItem('valuation_assumptions', idx)" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                                    <Trash2 class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Scenario Analysis -->
                                    <div class="space-y-4 pt-4 border-t border-avenir-border">
                                        <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider">Analisis Skenario (Scenario Analysis)</h4>
                                        <div class="space-y-3">
                                            <div class="space-y-1">
                                                <label class="text-xs text-slate-400 font-semibold">Bull Case</label>
                                                <input 
                                                    type="text" 
                                                    :value="getScenario('bull_case', '')" 
                                                    @input="setScenario('bull_case', $event.target.value)"
                                                    class="w-full bg-avenir-bg border border-avenir-border rounded-xl px-4 py-2 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                                />
                                            </div>
                                            <div class="space-y-1">
                                                <label class="text-xs text-slate-400 font-semibold">Base Case</label>
                                                <input 
                                                    type="text" 
                                                    :value="getScenario('base_case', '')" 
                                                    @input="setScenario('base_case', $event.target.value)"
                                                    class="w-full bg-avenir-bg border border-avenir-border rounded-xl px-4 py-2 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                                />
                                            </div>
                                            <div class="space-y-1">
                                                <label class="text-xs text-slate-400 font-semibold">Bear Case</label>
                                                <input 
                                                    type="text" 
                                                    :value="getScenario('bear_case', '')" 
                                                    @input="setScenario('bear_case', $event.target.value)"
                                                    class="w-full bg-avenir-bg border border-avenir-border rounded-xl px-4 py-2 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- FINANCIAL TAB -->
                                <div v-if="activeTab === 'financial'" class="space-y-6">
                                    <div class="space-y-2">
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Analisis Dampak Finansial (Financial Impact Analysis)</label>
                                        <textarea 
                                            :value="getProp('financial_impact', '')" 
                                            @input="setProp('financial_impact', $event.target.value)"
                                            rows="6"
                                            class="w-full bg-avenir-bg border border-avenir-border rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                        ></textarea>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Business Impact (Kualitatif / Model Bisnis)</label>
                                        <textarea 
                                            :value="getProp('business_impact', '')" 
                                            @input="setProp('business_impact', $event.target.value)"
                                            rows="5"
                                            class="w-full bg-avenir-bg border border-avenir-border rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                        ></textarea>
                                    </div>
                                </div>

                                <!-- RISKS TAB -->
                                <div v-if="activeTab === 'risks'" class="space-y-6">
                                    <!-- Key Risks List -->
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center">
                                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Risiko Bisnis Utama (Key Risks)</label>
                                            <button @click="addArrayItem('key_risks')" class="p-1 hover:bg-slate-800 rounded text-avenir-neon flex items-center gap-1 text-xs font-bold transition-all">
                                                <Plus class="w-3.5 h-3.5" /> Tambah Risiko
                                            </button>
                                        </div>
                                        <div class="space-y-2">
                                            <div v-for="(item, idx) in getArrayProp('key_risks')" :key="idx" class="flex gap-2 items-center">
                                                <span class="text-red-500 font-bold">•</span>
                                                <input 
                                                    type="text" 
                                                    :value="item" 
                                                    @input="updateArrayItem('key_risks', idx, $event.target.value)"
                                                    class="flex-1 bg-avenir-bg border border-avenir-border rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                                />
                                                <button @click="removeArrayItem('key_risks', idx)" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                                    <Trash2 class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Red Flags List -->
                                    <div class="space-y-3 pt-4 border-t border-avenir-border">
                                        <div class="flex justify-between items-center">
                                            <label class="text-xs font-bold text-red-400 uppercase tracking-wider">Tanda Bahaya (Red Flags)</label>
                                            <button @click="addArrayItem('red_flags')" class="p-1 hover:bg-slate-800 rounded text-red-400 flex items-center gap-1 text-xs font-bold transition-all">
                                                <Plus class="w-3.5 h-3.5" /> Tambah Flag
                                            </button>
                                        </div>
                                        <div class="space-y-2">
                                            <div v-for="(item, idx) in getArrayProp('red_flags')" :key="idx" class="flex gap-2 items-center">
                                                <span class="text-red-500 font-bold">⚠️</span>
                                                <input 
                                                    type="text" 
                                                    :value="item" 
                                                    @input="updateArrayItem('red_flags', idx, $event.target.value)"
                                                    class="flex-1 bg-avenir-bg border border-avenir-border rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                                />
                                                <button @click="removeArrayItem('red_flags', idx)" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                                    <Trash2 class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SOURCES TAB -->
                                <div v-if="activeTab === 'sources'" class="space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Jenis Dokumen</label>
                                            <input 
                                                type="text" 
                                                :value="getProp('document_type', '')" 
                                                @input="setProp('document_type', $event.target.value)"
                                                class="w-full bg-avenir-bg border border-avenir-border rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                            />
                                        </div>
                                        <div class="space-y-2">
                                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal Analisis</label>
                                            <input 
                                                type="text" 
                                                :value="getProp('analysis_date', '')" 
                                                @input="setProp('analysis_date', $event.target.value)"
                                                class="w-full bg-avenir-bg border border-avenir-border rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                            />
                                        </div>
                                    </div>

                                    <!-- Data Gaps -->
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center">
                                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Celah Data / Data Gaps (Hal-hal yang belum diketahui)</label>
                                            <button @click="addArrayItem('data_gaps')" class="p-1 hover:bg-slate-800 rounded text-avenir-neon flex items-center gap-1 text-xs font-bold transition-all">
                                                <Plus class="w-3.5 h-3.5" /> Tambah Celah
                                            </button>
                                        </div>
                                        <div class="space-y-2">
                                            <div v-for="(item, idx) in getArrayProp('data_gaps')" :key="idx" class="flex gap-2 items-center">
                                                <span class="text-amber-500 font-bold">?</span>
                                                <input 
                                                    type="text" 
                                                    :value="item" 
                                                    @input="updateArrayItem('data_gaps', idx, $event.target.value)"
                                                    class="flex-1 bg-avenir-bg border border-avenir-border rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                                />
                                                <button @click="removeArrayItem('data_gaps', idx)" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                                    <Trash2 class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Extracted Facts List -->
                                    <div class="space-y-3 pt-4 border-t border-avenir-border">
                                        <div class="flex justify-between items-center">
                                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Fakta Utama Hasil Ekstraksi (Extracted Facts)</label>
                                            <button @click="addArrayItem('extracted_facts')" class="p-1 hover:bg-slate-800 rounded text-avenir-neon flex items-center gap-1 text-xs font-bold transition-all">
                                                <Plus class="w-3.5 h-3.5" /> Tambah Fakta
                                            </button>
                                        </div>
                                        <div class="space-y-2">
                                            <div v-for="(item, idx) in getArrayProp('extracted_facts')" :key="idx" class="flex gap-2 items-center">
                                                <span class="text-avenir-neon font-bold">•</span>
                                                <input 
                                                    type="text" 
                                                    :value="item" 
                                                    @input="updateArrayItem('extracted_facts', idx, $event.target.value)"
                                                    class="flex-1 bg-avenir-bg border border-avenir-border rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                                />
                                                <button @click="removeArrayItem('extracted_facts', idx)" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                                    <Trash2 class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Evidence Map List -->
                                    <div class="space-y-3 pt-4 border-t border-avenir-border">
                                        <div class="flex justify-between items-center">
                                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Peta Bukti / Kutipan Halaman (Evidence Map)</label>
                                            <button @click="addArrayItem('evidence_map')" class="p-1 hover:bg-slate-800 rounded text-avenir-neon flex items-center gap-1 text-xs font-bold transition-all">
                                                <Plus class="w-3.5 h-3.5" /> Tambah Bukti
                                            </button>
                                        </div>
                                        <div class="space-y-2">
                                            <div v-for="(item, idx) in getArrayProp('evidence_map')" :key="idx" class="flex gap-2 items-center">
                                                <span class="text-emerald-500 font-bold">✓</span>
                                                <input 
                                                    type="text" 
                                                    :value="item" 
                                                    @input="updateArrayItem('evidence_map', idx, $event.target.value)"
                                                    class="flex-1 bg-avenir-bg border border-avenir-border rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                                />
                                                <button @click="removeArrayItem('evidence_map', idx)" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                                    <Trash2 class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Disclaimer Laporan</label>
                                        <textarea 
                                            :value="getProp('disclaimer', '')" 
                                            @input="setProp('disclaimer', $event.target.value)"
                                            rows="4"
                                            class="w-full bg-avenir-bg border border-avenir-border rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors text-slate-400"
                                        ></textarea>
                                    </div>
                                </div>

                                <!-- EDITORIAL TAB -->
                                <div v-if="activeTab === 'editorial'" class="space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Quality Score (0-100)</label>
                                            <input 
                                                type="number" 
                                                :value="getProp('quality_score', 80)" 
                                                @input="setProp('quality_score', parseInt($event.target.value) || 80)"
                                                min="0" max="100"
                                                class="w-full bg-avenir-bg border border-avenir-border rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                            />
                                        </div>
                                        <div class="space-y-2">
                                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Model Confidence</label>
                                            <select 
                                                :value="getProp('confidence_level', 'Medium')"
                                                @change="setProp('confidence_level', $event.target.value)"
                                                class="w-full bg-avenir-bg border border-avenir-border rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                            >
                                                <option value="Low">Low</option>
                                                <option value="Medium">Medium</option>
                                                <option value="High">High</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Publish Recommendation -->
                                    <div class="p-4 rounded-xl border flex items-center justify-between text-left" 
                                         :class="{
                                           'bg-emerald-950/20 border-emerald-500/30 text-emerald-450': getProp('publish_recommendation', 'Revise') === 'Approve',
                                           'bg-amber-950/20 border-amber-500/30 text-amber-400': getProp('publish_recommendation', 'Revise') === 'Revise',
                                           'bg-red-950/20 border-red-500/30 text-red-400': getProp('publish_recommendation', 'Revise') === 'Reject'
                                         }">
                                        <div>
                                            <h4 class="text-xs font-bold uppercase tracking-wider">Rekomendasi Publikasi QC</h4>
                                            <p class="text-sm font-semibold mt-1">Status: {{ getProp('publish_recommendation', 'Revise') }}</p>
                                        </div>
                                        <select 
                                            :value="getProp('publish_recommendation', 'Revise')" 
                                            @change="setProp('publish_recommendation', $event.target.value)"
                                            class="bg-avenir-bg border border-avenir-border rounded-xl px-3 py-1.5 text-xs font-bold text-slate-100 focus:outline-none"
                                        >
                                            <option value="Approve">Approve</option>
                                            <option value="Revise">Revise</option>
                                            <option value="Reject">Reject</option>
                                        </select>
                                    </div>

                                    <!-- Unsupported Claims List -->
                                    <div class="space-y-3 pt-4 border-t border-avenir-border">
                                        <div class="flex justify-between items-center">
                                            <label class="text-xs font-bold text-amber-400 uppercase tracking-wider">Klaim Tanpa Sumber / Bukti (Unsupported Claims)</label>
                                            <button @click="addArrayItem('unsupported_claims')" class="p-1 hover:bg-slate-800 rounded text-amber-450 flex items-center gap-1 text-xs font-bold transition-all">
                                                <Plus class="w-3.5 h-3.5" /> Tambah Klaim
                                            </button>
                                        </div>
                                        <div class="space-y-2">
                                            <div v-for="(item, idx) in getArrayProp('unsupported_claims')" :key="idx" class="flex gap-2 items-center">
                                                <span class="text-amber-500 font-bold">✗</span>
                                                <input 
                                                    type="text" 
                                                    :value="item" 
                                                    @input="updateArrayItem('unsupported_claims', idx, $event.target.value)"
                                                    class="flex-1 bg-avenir-bg border border-avenir-border rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                                />
                                                <button @click="removeArrayItem('unsupported_claims', idx)" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                                    <Trash2 class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Required Revisions List -->
                                    <div class="space-y-3 pt-4 border-t border-avenir-border">
                                        <div class="flex justify-between items-center">
                                            <label class="text-xs font-bold text-red-400 uppercase tracking-wider">Perbaikan yang Diperlukan (Required Revisions)</label>
                                            <button @click="addArrayItem('required_revision')" class="p-1 hover:bg-slate-800 rounded text-red-400 flex items-center gap-1 text-xs font-bold transition-all">
                                                <Plus class="w-3.5 h-3.5" /> Tambah Revisi
                                            </button>
                                        </div>
                                        <div class="space-y-2">
                                            <div v-for="(item, idx) in getArrayProp('required_revision')" :key="idx" class="flex gap-2 items-center">
                                                <span class="text-red-500 font-bold">!</span>
                                                <input 
                                                    type="text" 
                                                    :value="item" 
                                                    @input="updateArrayItem('required_revision', idx, $event.target.value)"
                                                    class="flex-1 bg-avenir-bg border border-avenir-border rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                                />
                                                <button @click="removeArrayItem('required_revision', idx)" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                                    <Trash2 class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Data Ticker Tambahan (Emiten Hub)</label>
                                        <input 
                                            type="text" 
                                            :value="getProp('ticker', '')" 
                                            @input="setProp('ticker', $event.target.value)"
                                            class="w-full bg-avenir-bg border border-avenir-border rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-avenir-neon transition-colors"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Action Bar -->
                            <div class="p-4 border-t border-avenir-border flex justify-end gap-3 bg-avenir-bg/30 rounded-b-2xl">
                                <button 
                                    @click="saveDraft"
                                    class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold rounded-xl transition-all flex items-center gap-2"
                                >
                                    <Save class="w-4 h-4" />
                                    Simpan Draft
                                </button>
                                <button 
                                    @click="publishToKatalog" 
                                    class="px-5 py-2.5 bg-avenir-neon hover:bg-emerald-600 text-white text-sm font-semibold rounded-xl transition-all flex items-center gap-2 shadow-neon"
                                >
                                    <Globe class="w-4 h-4" />
                                    Terbitkan ke Katalog
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
