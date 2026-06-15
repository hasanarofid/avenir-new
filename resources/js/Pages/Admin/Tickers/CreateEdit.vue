<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Save, ArrowLeft, Wand2, Plus, Trash2, FileText, CheckCircle2, AlertCircle, X, Upload, Copy } from '@lucide/vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    ticker: Object,
    articles: Array,
    disclosures: Array,
});

const isEditing = computed(() => !!props.ticker);

const form = useForm({
    symbol: props.ticker?.symbol || '',
    company_name: props.ticker?.company_name || '',
    sector: props.ticker?.sector || '',
    description: props.ticker?.description || '',
    current_price: props.ticker?.current_price || '',
    target_price: props.ticker?.target_price || '',
    recommendation: props.ticker?.recommendation || '',
    status: props.ticker?.status || 'Draft',
    sub_sektor: props.ticker?.sub_sektor || '',
    industri: props.ticker?.industri || '',
    papan_pencatatan: props.ticker?.papan_pencatatan || '',
    tanggal_listing: props.ticker?.tanggal_listing || '',
    website: props.ticker?.website || '',
    logo_url: props.ticker?.logo_url || '',
    business_summary: props.ticker?.business_summary || '',
    ticker_brief: props.ticker?.ticker_brief || '',
    risk_summary: props.ticker?.risk_summary || '',
    investment_angle: props.ticker?.investment_angle || '',
    business_segments: props.ticker?.business_segments || [],
    competitive_advantage: props.ticker?.competitive_advantage || [],
    key_risks: props.ticker?.key_risks || [],
    
    company_profile: props.ticker?.company_profile || {
        industry: '', board: '', listingDate: '', website: '', business: '',
        marketCap: '', outstandingShares: '', address: '', phone: '', email: '',
        tags: []
    },
    financial_highlights: props.ticker?.financial_highlights || [],
    financial_ratios: props.ticker?.financial_ratios || [],
    main_risks: props.ticker?.main_risks || [],
    article_ids: props.ticker?.article_ids || [],
    disclosure_ids: props.ticker?.disclosure_ids || [],
});

// UI States
const activeTab = ref('profil');
const tabs = [
    { id: 'profil', name: 'Profil & Data' },
    { id: 'keuangan', name: 'Keuangan' },
    { id: 'analisis', name: 'Analisis & Risiko' },
    { id: 'konten', name: 'Konten' },
    { id: 'metadata', name: 'Metadata' },
];

const isAiModalOpen = ref(false);
const isGeneratingAI = ref(false);

const aiForm = useForm({
    symbol: form.symbol,
    company_name: form.company_name,
    current_price: form.current_price,
    pdf_files: [],
});

const handleFileUpload = (e) => {
    const files = Array.from(e.target.files);
    if (files.length > 7) {
        Swal.fire({ icon: 'warning', title: 'Batas File', text: 'Maksimal 7 file PDF sekaligus.', background: '#121614', color: '#f1f5f9' });
        aiForm.pdf_files = files.slice(0, 7);
    } else {
        aiForm.pdf_files = files;
    }
};

const openAiModal = () => {
    aiForm.symbol = form.symbol;
    aiForm.company_name = form.company_name;
    aiForm.current_price = form.current_price;
    isAiModalOpen.value = true;
};

const generateWithAI = async () => {
    if (!aiForm.symbol) {
        Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Simbol wajib diisi.', background: '#121614', color: '#f1f5f9' });
        return;
    }

    isGeneratingAI.value = true;
    try {
        const formData = new FormData();
        formData.append('symbol', aiForm.symbol);
        if (aiForm.company_name) formData.append('company_name', aiForm.company_name);
        if (aiForm.current_price) formData.append('current_price', aiForm.current_price);
        if (aiForm.pdf_files && aiForm.pdf_files.length > 0) {
            let base64Index = 0;
            let fileIndex = 0;
            for (let i = 0; i < aiForm.pdf_files.length; i++) {
                const file = aiForm.pdf_files[i];
                if (file.size > 1500000) {
                    const base64 = await new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = () => resolve(reader.result);
                        reader.onerror = error => reject(error);
                    });
                    formData.append(`pdf_base64[${base64Index}]`, base64);
                    base64Index++;
                } else {
                    formData.append(`pdf_files[${fileIndex}]`, file);
                    fileIndex++;
                }
            }
        }

        const response = await axios.post(route('admin.emitens.generate-ai'), formData, {
            timeout: 120000 // 2 minutes timeout for large PDFs
        });
        
        const data = response.data;
        
        if (data.company_name) form.company_name = data.company_name;
        if (data.description) form.description = data.description;
        if (data.sector) form.sector = data.sector;
        if (data.sub_sector) form.sub_sektor = data.sub_sector;
        if (data.industry) form.industri = data.industry;
        
        if (data.company_profile) {
            form.company_profile = { ...form.company_profile, ...data.company_profile };
            if (data.company_profile.board) form.papan_pencatatan = data.company_profile.board;
            if (data.company_profile.website) form.website = data.company_profile.website;
        }
        
        if (data.financial_highlights) form.financial_highlights = data.financial_highlights;
        if (data.financial_ratios) form.financial_ratios = data.financial_ratios;
        if (data.main_risks) form.main_risks = data.main_risks;
        
        form.symbol = aiForm.symbol;
        if (aiForm.current_price) form.current_price = aiForm.current_price;

        isAiModalOpen.value = false;
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Data dari PDF berhasil diekstrak oleh AI.', background: '#121614', color: '#f1f5f9' });
    } catch (error) {
        console.error(error);
        Swal.fire({ icon: 'error', title: 'Gagal', text: "Gagal memproses AI: " + (error.response?.data?.message || error.message), background: '#121614', color: '#f1f5f9' });
    } finally {
        isGeneratingAI.value = false;
    }
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.emitens.update', props.ticker.id));
    } else {
        form.post(route('admin.emitens.store'));
    }
};

// Dynamic Array Helpers
const addHighlight = () => form.financial_highlights.push({ title: '', value: '', change: '', type: 'up', icon: 'TrendingUp' });
const removeHighlight = (index) => form.financial_highlights.splice(index, 1);
const addRatio = () => form.financial_ratios.push({ name: '', value: '', period: '', change: '' });
const removeRatio = (index) => form.financial_ratios.splice(index, 1);
const addRisk = () => form.main_risks.push('');
const removeRisk = (index) => form.main_risks.splice(index, 1);
const tagsInput = ref(form.company_profile.tags ? form.company_profile.tags.join(', ') : '');
const updateTags = () => { form.company_profile.tags = tagsInput.value.split(',').map(t => t.trim()).filter(t => t); };

// Manual JSON State
const isJsonModalOpen = ref(false);
const jsonModalTab = ref('prompt'); // 'prompt' or 'paste'
const manualJsonInput = ref('');

const manualAiPrompt = computed(() => {
    let symbolText = form.symbol || '[TICKER]';
    let companyText = form.company_name || '[NAMA PERUSAHAAN]';
    let priceText = form.current_price ? ` The current stock price is Rp ${form.current_price}. Use this for valuation metrics if relevant.` : '';
    
    return `You are a professional Equity Research Analyst focused on the Indonesian stock market (IDX). Provide comprehensive profile and financial estimates for the given company in JSON format exactly matching the schema provided. Format the monetary values in Indonesian Rupiah (e.g. 'Rp 100,5 T', 'Rp 15,2 T') and percentages with commas (e.g. '12,5%'). CRITICAL: If source document text is provided, extract the data STRICTLY from the text. Do not hallucinate or guess numbers. If a specific metric is not found in the text, return null or an empty string. CRITICAL: ALL text values (description, business summary, risks, etc) MUST be written in Bahasa Indonesia. Translate them if the source document is in English.

===

Generate data for Ticker: ${symbolText} (Company: ${companyText}).${priceText} Return a JSON object with this exact structure:
{
  "company_name": "Full legal name of the company",
  "description": "A professional summary of the company's main business, market position, and strengths. (1-2 paragraphs)",
  "sector": "The company's primary sector (e.g., Financials, Energy, Consumer Goods)",
  "sub_sector": "The company's sub-sector",
  "industry": "Industry name",
  "company_profile": {
    "board": "Utama or Pengembangan",
    "listingDate": "e.g., 10 November 2003",
    "website": "Company website URL",
    "business": "Short description of business activities",
    "marketCap": "e.g., Rp 600 T",
    "outstandingShares": "e.g., 150 Miliar",
    "address": "Company headquarters address",
    "phone": "Company phone number",
    "email": "Corporate secretary email",
    "tags": ["Array", "of", "3-4", "keywords", "like", "Bluechip", "SOE"]
  },
  "main_risks": [
    "Array of 4-5 main business or macroeconomic risks facing the company"
  ],
  "financial_highlights": [
    { "title": "Net Profit", "value": "Rp X T", "change": "+Y%", "type": "up", "icon": "TrendingUp" },
    { "title": "Total Assets", "value": "Rp Z T", "change": "+W%", "type": "up", "icon": "Building2" }
  ],
  "financial_ratios": [
    { "name": "PER", "value": "XX,Xx", "period": "TTM", "change": "+Y%" },
    { "name": "PBV", "value": "X,Xx", "period": "TTM", "change": "-Z%" },
    { "name": "ROE", "value": "XX,X%", "period": "TTM", "change": "+W%" }
  ]
}`;
});

const applyManualJson = () => {
    try {
        if (!manualJsonInput.value) {
            Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Teks JSON tidak boleh kosong.', background: '#121614', color: '#f1f5f9' });
            return;
        }

        let jsonStr = manualJsonInput.value.trim();
        if (jsonStr.startsWith('```json')) {
            jsonStr = jsonStr.replace(/^```json\n?/, '');
            jsonStr = jsonStr.replace(/```$/, '');
        } else if (jsonStr.startsWith('```')) {
            jsonStr = jsonStr.replace(/^```\n?/, '');
            jsonStr = jsonStr.replace(/```$/, '');
        }

        const data = JSON.parse(jsonStr.trim());

        if (data.company_name) form.company_name = data.company_name;
        if (data.description) form.description = data.description;
        if (data.sector) form.sector = data.sector;
        if (data.sub_sector) form.sub_sektor = data.sub_sector;
        if (data.industry) form.industri = data.industry;
        
        if (data.company_profile) {
            form.company_profile = { ...form.company_profile, ...data.company_profile };
            if (data.company_profile.board) form.papan_pencatatan = data.company_profile.board;
            if (data.company_profile.website) form.website = data.company_profile.website;
        }
        
        if (data.financial_highlights) form.financial_highlights = data.financial_highlights;
        if (data.financial_ratios) form.financial_ratios = data.financial_ratios;
        if (data.main_risks) form.main_risks = data.main_risks;
        
        isJsonModalOpen.value = false;
        manualJsonInput.value = '';
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Data berhasil di-apply dari JSON manual.', background: '#121614', color: '#f1f5f9' });
    } catch (error) {
        console.error("JSON Parse Error:", error);
        Swal.fire({ icon: 'error', title: 'Gagal', text: "Format JSON tidak valid: " + error.message, background: '#121614', color: '#f1f5f9' });
    }
};

const copyPrompt = async () => {
    try {
        await navigator.clipboard.writeText(manualAiPrompt.value);
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Prompt disalin ke clipboard!', showConfirmButton: false, timer: 3000, background: '#121614', color: '#f1f5f9' });
    } catch (err) {
        console.error('Failed to copy', err);
    }
};
</script>

<template>
    <Head :title="isEditing ? `Edit Emiten ${ticker?.symbol}` : 'Tambah Emiten'" />

    <AdminLayout>
        <div class="max-w-[1600px] mx-auto pb-12">
            
            <!-- Top Navigation Breadcrumb & Header -->
            <div class="mb-6 flex flex-col md:flex-row justify-between md:items-end gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">
                        <Link :href="route('admin.dashboard')" class="hover:text-emerald-400">Dashboard</Link>
                        <span>›</span>
                        <Link :href="route('admin.emitens.index')" class="hover:text-emerald-400">Emiten Hub</Link>
                        <span>›</span>
                        <span class="text-slate-200">{{ isEditing ? 'Edit Emiten' : 'Tambah Emiten' }}</span>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button @click="openAiModal" type="button" class="inline-flex items-center px-4 py-2 bg-indigo-600/10 hover:bg-indigo-600/20 text-indigo-400 border border-indigo-500/20 text-xs font-semibold rounded-lg transition-all">
                        <Wand2 class="w-4 h-4 mr-2" /> Autofill AI (PDF)
                    </button>
                    <button @click="isJsonModalOpen = true" type="button" class="inline-flex items-center px-4 py-2 bg-[#121614] hover:bg-[#1a1f1c] text-emerald-400 border border-emerald-950/40 text-xs font-semibold rounded-lg transition-all">
                        &lt;/&gt; Generate JSON
                    </button>
                    <button @click="submit" :disabled="form.processing" class="inline-flex items-center px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white rounded-lg shadow-lg shadow-emerald-600/20 transition-all">
                        <Save class="w-4 h-4 mr-2" /> Simpan
                    </button>
                </div>
            </div>

            <!-- Main Emiten Profile Header Panel -->
            <div class="bg-[#121614]/80 border border-emerald-950/40 rounded-2xl p-6 mb-6 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div class="flex items-center gap-5">
                    <div class="w-20 h-20 bg-blue-600 rounded-2xl flex items-center justify-center text-3xl font-black text-white shrink-0 shadow-inner">
                        {{ form.symbol ? form.symbol.substring(0,2) : 'EM' }}
                    </div>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h2 class="text-2xl font-bold text-white uppercase">{{ form.symbol || 'TICKER' }}</h2>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                {{ form.status }}
                            </span>
                        </div>
                        <p class="text-sm font-semibold text-slate-300">{{ form.company_name || 'Nama Perusahaan Belum Diisi' }}</p>
                        <p class="text-xs text-slate-500 mt-2">Last updated: {{ isEditing ? new Date(ticker.updated_at).toLocaleString('id-ID') : 'Baru' }} by Admin Avenir</p>
                    </div>
                </div>

                <!-- Stats Preview -->
                <div class="flex items-center gap-8 bg-[#090b0a]/50 p-4 rounded-xl border border-emerald-950/30">
                    <div>
                        <p class="text-[10px] text-slate-500 font-semibold mb-1 uppercase">Completion</p>
                        <p class="text-lg font-bold text-white">92%</p>
                        <div class="w-24 h-1 bg-slate-800 rounded-full mt-1"><div class="h-1 bg-emerald-500 rounded-full w-[92%]"></div></div>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-500 font-semibold mb-1 uppercase">AI Confidence</p>
                        <p class="text-lg font-bold text-white">88%</p>
                        <div class="w-24 h-1 bg-slate-800 rounded-full mt-1"><div class="h-1 bg-indigo-500 rounded-full w-[88%]"></div></div>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-500 font-semibold mb-1 uppercase">Data Quality</p>
                        <p class="text-lg font-bold text-emerald-400">A</p>
                        <p class="text-[10px] text-emerald-500">Excellent</p>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-emerald-950/30 mb-6 flex space-x-6 overflow-x-auto scrollbar-hide">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    :class="[
                        activeTab === tab.id ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-700',
                        'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm transition-colors'
                    ]"
                >
                    {{ tab.name }}
                </button>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- LEFT MAIN CONTENT -->
                <div class="xl:col-span-2 space-y-6">
                    
                    <!-- TAB 1: Profil & Data -->
                    <div v-show="activeTab === 'profil'" class="space-y-6">
                        <!-- Company Profile Box -->
                        <div class="bg-[#121614] border border-emerald-950/40 rounded-2xl p-6">
                            <h3 class="text-base font-bold text-white mb-4">Company Profile</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                <div class="md:col-span-2 space-y-2">
                                    <label class="text-xs text-slate-400">Nama Perusahaan <span class="text-rose-500">*</span></label>
                                    <input v-model="form.company_name" type="text" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs text-slate-400">Ticker / Kode <span class="text-rose-500">*</span></label>
                                    <input v-model="form.symbol" type="text" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-white focus:ring-1 focus:ring-emerald-500/50 uppercase" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs text-slate-400">Sektor</label>
                                    <input v-model="form.sector" type="text" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs text-slate-400">Sub Sektor</label>
                                    <input v-model="form.sub_sektor" type="text" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs text-slate-400">Industry</label>
                                    <input v-model="form.industri" type="text" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" />
                                </div>
                                <div class="md:col-span-3 space-y-2 mt-2">
                                    <label class="text-xs text-slate-400">Deskripsi Perusahaan</label>
                                    <textarea v-model="form.description" rows="3" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-3 text-sm text-white focus:ring-1 focus:ring-emerald-500/50"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Market Data Box -->
                        <div class="bg-[#121614] border border-emerald-950/40 rounded-2xl p-6">
                            <h3 class="text-base font-bold text-white mb-4">Market Data</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                                <div class="space-y-2">
                                    <label class="text-xs text-slate-400">Harga (IDR)</label>
                                    <input v-model="form.current_price" type="number" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-white" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs text-slate-400">Target Price (IDR)</label>
                                    <input v-model="form.target_price" type="number" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-white" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs text-slate-400">Papan Pencatatan</label>
                                    <input v-model="form.papan_pencatatan" type="text" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-white" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs text-slate-400">Tanggal Listing</label>
                                    <input v-model="form.tanggal_listing" type="date" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-white" />
                                </div>
                                <div class="space-y-2 md:col-span-2">
                                    <label class="text-xs text-slate-400">Website</label>
                                    <input v-model="form.website" type="url" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-white" />
                                </div>
                                <div class="space-y-2 md:col-span-2">
                                    <label class="text-xs text-slate-400">Logo URL</label>
                                    <input v-model="form.logo_url" type="url" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-white" />
                                </div>
                            </div>
                        </div>

                        <!-- Briefs & Risks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-[#121614] border border-emerald-950/40 rounded-2xl p-6">
                                <h3 class="text-sm font-bold text-white mb-4">Ticker Brief</h3>
                                <textarea v-model="form.ticker_brief" rows="4" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-3 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" placeholder="Ringkasan singkat..."></textarea>
                            </div>
                            <div class="bg-[#121614] border border-emerald-950/40 rounded-2xl p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-sm font-bold text-white">Key Risks</h3>
                                    <button type="button" @click="addRisk" class="text-[10px] font-bold text-emerald-400 flex items-center gap-1 hover:text-emerald-300"><Plus class="w-3 h-3"/> Tambah</button>
                                </div>
                                <div class="space-y-3">
                                    <div v-for="(risk, index) in form.main_risks" :key="index" class="flex gap-2">
                                        <input v-model="form.main_risks[index]" type="text" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-lg px-3 py-1.5 text-xs text-white" />
                                        <button type="button" @click="removeRisk(index)" class="text-rose-500 hover:bg-rose-500/10 p-1.5 rounded-lg"><Trash2 class="w-4 h-4"/></button>
                                    </div>
                                    <p v-if="form.main_risks.length === 0" class="text-xs text-slate-500">Belum ada risiko.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: Keuangan -->
                    <div v-show="activeTab === 'keuangan'" class="space-y-6">
                        <div class="bg-[#121614] border border-emerald-950/40 rounded-2xl p-6">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h3 class="text-base font-bold text-white">Financial Highlights</h3>
                                    <p class="text-xs text-slate-400">Ringkasan indikator keuangan utama untuk halaman overview.</p>
                                </div>
                                <button type="button" @click="addHighlight" class="px-3 py-1.5 bg-emerald-600/20 text-emerald-400 rounded-lg text-xs font-bold hover:bg-emerald-600/30">Tambah Highlight</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="(item, index) in form.financial_highlights" :key="index" class="p-4 bg-[#090b0a] border border-emerald-950/30 rounded-xl relative">
                                    <button type="button" @click="removeHighlight(index)" class="absolute top-2 right-2 text-slate-500 hover:text-rose-500"><X class="w-4 h-4"/></button>
                                    <div class="space-y-3">
                                        <div><label class="text-[10px] text-slate-500 uppercase">Judul</label><input v-model="item.title" type="text" class="w-full bg-[#121614] border-emerald-950/30 rounded-lg text-xs text-white p-2" /></div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div><label class="text-[10px] text-slate-500 uppercase">Nilai</label><input v-model="item.value" type="text" class="w-full bg-[#121614] border-emerald-950/30 rounded-lg text-xs text-white p-2" /></div>
                                            <div><label class="text-[10px] text-slate-500 uppercase">Perubahan</label><input v-model="item.change" type="text" class="w-full bg-[#121614] border-emerald-950/30 rounded-lg text-xs text-white p-2" /></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-[#121614] border border-emerald-950/40 rounded-2xl p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-base font-bold text-white">Rasio Keuangan</h3>
                                <button type="button" @click="addRatio" class="px-3 py-1.5 bg-emerald-600/20 text-emerald-400 rounded-lg text-xs font-bold hover:bg-emerald-600/30">Tambah Rasio</button>
                            </div>
                            <div class="space-y-3">
                                <div v-for="(ratio, index) in form.financial_ratios" :key="index" class="flex items-center gap-3 bg-[#090b0a] p-3 rounded-xl border border-emerald-950/30">
                                    <input v-model="ratio.name" type="text" placeholder="Nama (PER)" class="w-1/4 bg-[#121614] border-emerald-950/30 rounded-lg text-xs text-white p-2" />
                                    <input v-model="ratio.value" type="text" placeholder="Nilai (12.5x)" class="w-1/4 bg-[#121614] border-emerald-950/30 rounded-lg text-xs text-white p-2" />
                                    <input v-model="ratio.period" type="text" placeholder="Periode (TTM)" class="w-1/4 bg-[#121614] border-emerald-950/30 rounded-lg text-xs text-white p-2" />
                                    <input v-model="ratio.change" type="text" placeholder="Perubahan (+1%)" class="w-1/4 bg-[#121614] border-emerald-950/30 rounded-lg text-xs text-white p-2" />
                                    <button type="button" @click="removeRatio(index)" class="text-rose-500 p-2"><Trash2 class="w-4 h-4"/></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: Konten & Relasi -->
                    <div v-show="activeTab === 'konten'" class="space-y-6">
                        <div class="bg-[#121614] border border-emerald-950/40 rounded-2xl p-6">
                            <h3 class="text-base font-bold text-white mb-4">Relasi Riset & Artikel</h3>
                            <select v-model="form.article_ids" multiple class="w-full h-48 bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-white focus:ring-1 focus:ring-emerald-500/50">
                                <option v-for="article in articles" :key="article.id" :value="article.id">{{ article.title }}</option>
                            </select>
                            <p class="text-xs text-slate-500 mt-2">Tahan tombol Ctrl/Cmd untuk memilih banyak.</p>
                        </div>
                        <div class="bg-[#121614] border border-emerald-950/40 rounded-2xl p-6">
                            <h3 class="text-base font-bold text-white mb-4">Relasi Disclosures</h3>
                            <select v-model="form.disclosure_ids" multiple class="w-full h-48 bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-white focus:ring-1 focus:ring-emerald-500/50">
                                <option v-for="disclosure in disclosures" :key="disclosure.id" :value="disclosure.id">{{ disclosure.title }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- TAB 4: Metadata -->
                    <div v-show="activeTab === 'metadata'" class="space-y-6">
                        <div class="bg-[#121614] border border-emerald-950/40 rounded-2xl p-6">
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <label class="text-xs text-slate-400">Rekomendasi Utama</label>
                                    <select v-model="form.recommendation" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-white">
                                        <option value="">Tidak ada</option>
                                        <option value="BUY">BUY</option>
                                        <option value="HOLD">HOLD</option>
                                        <option value="SELL">SELL</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs text-slate-400">Status Publish</label>
                                    <select v-model="form.status" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-white">
                                        <option value="Draft">Draft</option>
                                        <option value="Review">Need Review</option>
                                        <option value="Published">Published</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs text-slate-400">Tags Emiten</label>
                                    <input v-model="tagsInput" @input="updateTags" type="text" placeholder="Banking, BUMN, Bluechip" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-white" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>

                <!-- RIGHT SIDEBAR (AI & Status) -->
                <div class="xl:col-span-1 space-y-6">
                    <!-- AI Extraction Panel -->
                    <div class="bg-[#121614]/80 border border-emerald-950/40 rounded-2xl p-6">
                        <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                            <Wand2 class="w-4 h-4 text-indigo-400" />
                            AI Extraction Status
                        </h3>
                        <div class="flex items-center justify-center mb-6">
                            <div class="relative w-24 h-24 flex items-center justify-center rounded-full border-4 border-emerald-500/20 border-t-emerald-500">
                                <span class="text-2xl font-bold text-white">87%</span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <p class="text-xs text-slate-400 text-center">Extraction Completed</p>
                            <p class="text-[10px] text-slate-500 text-center">Data berhasil diekstrak dari PDF menggunakan ChatGPT.</p>
                            <div class="flex items-center gap-2 justify-center mt-2">
                                <CheckCircle2 class="w-4 h-4 text-emerald-500" />
                                <span class="text-xs text-emerald-400">All critical fields extracted</span>
                            </div>
                        </div>
                        <button @click="openAiModal" type="button" class="mt-6 w-full py-2.5 bg-indigo-600/10 hover:bg-indigo-600/20 border border-indigo-500/20 text-indigo-400 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2">
                            <Wand2 class="w-4 h-4" /> Run AI Autofill again
                        </button>
                    </div>

                    <!-- Source Documents Placeholder -->
                    <div class="bg-[#121614]/80 border border-emerald-950/40 rounded-2xl p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-sm font-bold text-white">Source Documents</h3>
                            <button class="text-xs text-emerald-400 hover:text-emerald-300">Manage</button>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2 text-slate-300"><FileText class="w-3.5 h-3.5 text-rose-400"/> Laporan Keuangan 2024.pdf</div>
                                <span class="text-[10px] text-emerald-500 font-bold">Processed</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2 text-slate-300"><FileText class="w-3.5 h-3.5 text-blue-400"/> Company Profile.docx</div>
                                <span class="text-[10px] text-emerald-500 font-bold">Processed</span>
                            </div>
                            <button class="text-xs text-emerald-400 flex items-center gap-1 mt-2 hover:underline">
                                <Plus class="w-3 h-3" /> Add New Source
                            </button>
                        </div>
                    </div>

                    <!-- Publish Checklist Placeholder -->
                    <div class="bg-[#121614]/80 border border-emerald-950/40 rounded-2xl p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-sm font-bold text-white">Publish Checklist</h3>
                            <span class="text-xs text-emerald-400 font-bold">7/8</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-800 rounded-full mb-4"><div class="h-1.5 bg-emerald-500 rounded-full w-[85%]"></div></div>
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center gap-2 text-slate-300"><CheckCircle2 class="w-4 h-4 text-emerald-500"/> Company Profile Lengkap</div>
                            <div class="flex items-center gap-2 text-slate-300"><CheckCircle2 class="w-4 h-4 text-emerald-500"/> Market Data Terisi</div>
                            <div class="flex items-center gap-2 text-slate-300"><CheckCircle2 class="w-4 h-4 text-emerald-500"/> Keuangan Terupdate</div>
                            <div class="flex items-center gap-2 text-slate-300"><CheckCircle2 class="w-4 h-4 text-emerald-500"/> Analisis & Rekomendasi</div>
                            <div class="flex items-center gap-2 text-slate-400"><div class="w-4 h-4 rounded-full border border-slate-600"></div> Review & Approval</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>

    <!-- AI Autofill Modal -->
    <div v-if="isAiModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-[#121614] border border-emerald-950/40 rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden">
            <div class="p-5 border-b border-emerald-950/40 flex justify-between items-center bg-gradient-to-r from-[#121614] to-emerald-950/10">
                <h3 class="text-base font-bold text-white flex items-center gap-2"><Wand2 class="w-5 h-5 text-indigo-400" /> AI Document Extraction</h3>
                <button @click="isAiModalOpen = false" class="text-slate-500 hover:text-white transition-colors"><X class="w-5 h-5" /></button>
            </div>
            
            <div class="p-6 space-y-5">
                <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-xl p-4 flex gap-3 text-sm text-indigo-200">
                    <AlertCircle class="w-5 h-5 text-indigo-400 shrink-0" />
                    <p>Unggah file PDF Laporan Keuangan. AI akan mengekstrak data profil, keuangan, dan risiko secara presisi berdasarkan dokumen ini.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-1.5 block">Kode Saham (Ticker) <span class="text-rose-500">*</span></label>
                        <input v-model="aiForm.symbol" type="text" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white uppercase" placeholder="BBRI" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-1.5 block">Nama Perusahaan (Opsional)</label>
                        <input v-model="aiForm.company_name" type="text" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white" placeholder="PT Bank Rakyat Indonesia..." />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-1.5 block">Harga Saat Ini (Opsional, untuk valuasi)</label>
                        <input v-model="aiForm.current_price" type="number" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white" placeholder="4820" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-1.5 block">Dokumen Laporan Keuangan (PDF) <span class="text-rose-500">*</span></label>
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-emerald-950/50 rounded-xl cursor-pointer hover:bg-[#090b0a]/50 transition-colors bg-[#090b0a]">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4">
                                <Upload class="w-8 h-8 mb-3 text-emerald-500/50" />
                                <p class="mb-1 text-sm text-slate-300"><span class="font-bold">Klik untuk unggah</span> atau seret file PDF</p>
                                <p class="text-xs text-slate-500">{{ aiForm.pdf_files.length > 0 ? `${aiForm.pdf_files.length} file dipilih` : 'Maksimal 7 File' }}</p>
                            </div>
                            <input type="file" multiple class="hidden" accept=".pdf" @change="handleFileUpload" />
                        </label>
                    </div>
                </div>
            </div>

            <div class="p-5 border-t border-emerald-950/40 bg-[#090b0a] flex justify-end gap-3">
                <button @click="isAiModalOpen = false" type="button" class="px-5 py-2.5 text-sm font-semibold text-slate-300 hover:text-white transition-colors">Batal</button>
                <button 
                    @click="generateWithAI" 
                    :disabled="isGeneratingAI || !aiForm.symbol"
                    class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-all disabled:opacity-50"
                >
                    <span v-if="isGeneratingAI" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Mengekstrak AI...
                    </span>
                    <span v-else class="flex items-center gap-2"><Wand2 class="w-4 h-4"/> Ekstrak Data</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Manual Generate JSON Modal -->
    <div v-if="isJsonModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-[#121614] border border-emerald-950/40 rounded-2xl max-w-4xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
            <div class="p-5 border-b border-emerald-950/40 flex justify-between items-center bg-[#090b0a]">
                <h3 class="text-base font-bold text-white flex items-center gap-2"><span class="text-emerald-400 font-mono text-xl">&lt;/&gt;</span> Manual JSON Extraction</h3>
                <button @click="isJsonModalOpen = false" class="text-slate-500 hover:text-white transition-colors"><X class="w-5 h-5" /></button>
            </div>
            
            <!-- Modal Tabs -->
            <div class="flex border-b border-emerald-950/40 bg-[#090b0a] px-6">
                <button @click="jsonModalTab = 'prompt'" :class="['py-3 px-4 text-sm font-bold border-b-2 transition-colors', jsonModalTab === 'prompt' ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-slate-500 hover:text-slate-300']">
                    1. Salin Prompt & Schema
                </button>
                <button @click="jsonModalTab = 'paste'" :class="['py-3 px-4 text-sm font-bold border-b-2 transition-colors', jsonModalTab === 'paste' ? 'border-emerald-500 text-emerald-400' : 'border-transparent text-slate-500 hover:text-slate-300']">
                    2. Tempel & Apply JSON
                </button>
            </div>

            <div class="p-6 flex-1 overflow-y-auto">
                <div v-if="jsonModalTab === 'prompt'" class="space-y-4">
                    <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-xl p-4 flex gap-3 text-sm text-indigo-200">
                        <AlertCircle class="w-5 h-5 text-indigo-400 shrink-0" />
                        <p>Salin prompt di bawah ini lalu tempelkan ke antarmuka ChatGPT atau Claude. Anda bisa menambahkan teks yang di-copy dari PDF/laporan keuangan di bawahnya agar hasil AI lebih akurat.</p>
                    </div>

                    <div class="relative group">
                        <textarea readonly :value="manualAiPrompt" rows="16" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-4 text-xs font-mono text-slate-300 focus:outline-none scrollbar-hide resize-none"></textarea>
                        <button @click="copyPrompt" class="absolute top-4 right-4 p-2 bg-[#121614] border border-emerald-950/50 rounded-lg text-emerald-400 hover:bg-emerald-900/20 hover:text-emerald-300 transition-colors shadow-lg opacity-0 group-hover:opacity-100 flex items-center gap-2">
                            <Copy class="w-4 h-4" /> <span class="text-xs font-bold">Salin</span>
                        </button>
                    </div>
                </div>

                <div v-else-if="jsonModalTab === 'paste'" class="space-y-4">
                    <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-4 flex gap-3 text-sm text-emerald-200">
                        <AlertCircle class="w-5 h-5 text-emerald-400 shrink-0" />
                        <p>Tempel (Paste) format JSON mentah yang telah dihasilkan oleh ChatGPT/Claude ke dalam kotak di bawah ini. Sistem akan otomatis mengisi form sesuai struktur tersebut.</p>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase mb-2 block">RAW JSON OUTPUT</label>
                        <textarea v-model="manualJsonInput" rows="16" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-4 text-xs font-mono text-emerald-400 focus:ring-1 focus:ring-emerald-500/50 resize-none" placeholder='{\n  "company_name": "PT Telkom Indonesia",\n  ...\n}'></textarea>
                    </div>
                </div>
            </div>

            <div class="p-5 border-t border-emerald-950/40 bg-[#090b0a] flex justify-between items-center">
                <p class="text-[10px] text-slate-500 max-w-sm">Fitur ini merupakan fallback manual jika sistem API kehabisan limit atau gagal terhubung ke provider.</p>
                <div class="flex gap-3">
                    <button @click="isJsonModalOpen = false" type="button" class="px-5 py-2.5 text-sm font-semibold text-slate-300 hover:text-white transition-colors">Tutup</button>
                    <button 
                        v-if="jsonModalTab === 'paste'"
                        @click="applyManualJson" 
                        class="inline-flex items-center px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition-all"
                    >
                        Apply JSON ke Form
                    </button>
                    <button 
                        v-if="jsonModalTab === 'prompt'"
                        @click="jsonModalTab = 'paste'" 
                        class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-all"
                    >
                        Lanjut ke Tahap 2 &rarr;
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
