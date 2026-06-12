<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Save, ArrowLeft, Wand2, Plus, Trash2 } from '@lucide/vue';
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

const isGeneratingAI = ref(false);

const generateWithAI = async () => {
    if (!form.symbol) {
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: 'Mohon isi field Simbol (Ticker) terlebih dahulu.',
            background: '#121614',
            color: '#f1f5f9',
            confirmButtonColor: '#059669'
        });
        return;
    }

    const result = await Swal.fire({
        title: 'Generate dengan AI?',
        text: `Apakah Anda yakin ingin overwrite data dengan hasil generate AI untuk emiten ${form.symbol}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#475569',
        confirmButtonText: 'Ya, Lanjutkan!',
        cancelButtonText: 'Batal',
        background: '#121614',
        color: '#f1f5f9'
    });

    if (result.isConfirmed) {
        isGeneratingAI.value = true;
        try {
            const response = await axios.post(route('admin.emitens.generate-ai'), {
                symbol: form.symbol,
                company_name: form.company_name
            });
            
            const data = response.data;
            
            if (data.description) form.description = data.description;
            if (data.sector) form.sector = data.sector;
            if (data.company_profile) {
                form.company_profile = { ...form.company_profile, ...data.company_profile };
            }
            if (data.financial_highlights) form.financial_highlights = data.financial_highlights;
            if (data.financial_ratios) form.financial_ratios = data.financial_ratios;
            if (data.main_risks) form.main_risks = data.main_risks;
            
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Berhasil menghasilkan data dari AI! Silakan periksa kolom-kolom di bawah ini.',
                background: '#121614',
                color: '#f1f5f9',
                confirmButtonColor: '#059669'
            });
        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: "Gagal menghubungi AI: " + (error.response?.data?.error || error.message),
                background: '#121614',
                color: '#f1f5f9',
                confirmButtonColor: '#059669'
            });
        } finally {
            isGeneratingAI.value = false;
        }
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
const addHighlight = () => {
    form.financial_highlights.push({ title: '', value: '', change: '', type: 'up', icon: 'TrendingUp' });
};
const removeHighlight = (index) => form.financial_highlights.splice(index, 1);

const addRatio = () => {
    form.financial_ratios.push({ name: '', value: '', period: '', change: '' });
};
const removeRatio = (index) => form.financial_ratios.splice(index, 1);

const addRisk = () => form.main_risks.push('');
const removeRisk = (index) => form.main_risks.splice(index, 1);

const tagsInput = ref(form.company_profile.tags ? form.company_profile.tags.join(', ') : '');
const updateTags = () => {
    form.company_profile.tags = tagsInput.value.split(',').map(t => t.trim()).filter(t => t);
};

</script>

<template>
    <Head :title="isEditing ? 'Edit Emiten' : 'Tambah Emiten Baru'" />

    <AdminLayout>
        <div class="space-y-6 pb-12">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.emitens.index')" class="p-2 bg-[#121614] border border-emerald-950/30 text-slate-400 hover:text-white rounded-xl transition-colors">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h2 class="text-2xl font-extrabold text-white">{{ isEditing ? 'Edit Emiten' : 'Tambah Emiten Baru' }}</h2>
                        <p class="text-sm text-slate-400">{{ isEditing ? `Mengedit data ${ticker.symbol}` : 'Masukkan detail emiten baru' }}</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button 
                        type="button"
                        @click="generateWithAI"
                        :disabled="isGeneratingAI"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600/10 hover:bg-indigo-600/20 text-indigo-400 border border-indigo-500/20 text-sm font-semibold rounded-xl transition-all disabled:opacity-50"
                    >
                        <Wand2 v-if="!isGeneratingAI" class="w-4 h-4 mr-2" />
                        <svg v-else class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ isGeneratingAI ? 'Menghasilkan Data...' : '🪄 Autofill AI' }}
                    </button>
                    <button 
                        @click="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-sm font-semibold text-white rounded-xl shadow-lg shadow-emerald-600/20 transition-all disabled:opacity-50"
                    >
                        <Save class="w-4 h-4 mr-2" />
                        Simpan
                    </button>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- 1. Header & Brief -->
                <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl space-y-6">
                    <h3 class="text-sm font-bold text-emerald-500 uppercase tracking-wider border-b border-emerald-950/30 pb-3">1. Informasi Dasar Emiten</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase">Simbol <span class="text-rose-500">*</span></label>
                            <input v-model="form.symbol" type="text" required placeholder="Contoh: BBRI" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-1 focus:ring-emerald-500/50 uppercase" />
                            <div v-if="form.errors.symbol" class="text-xs text-rose-500">{{ form.errors.symbol }}</div>
                        </div>
                        <div class="space-y-2 lg:col-span-2">
                            <label class="text-xs font-bold text-slate-400 uppercase">Nama Perusahaan <span class="text-rose-500">*</span></label>
                            <input v-model="form.company_name" type="text" required placeholder="PT Bank Rakyat Indonesia (Persero) Tbk." class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" />
                            <div v-if="form.errors.company_name" class="text-xs text-rose-500">{{ form.errors.company_name }}</div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase">Sektor</label>
                            <input v-model="form.sector" type="text" placeholder="Financials" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase">Harga Saat Ini (Rp)</label>
                            <input v-model="form.current_price" type="number" step="0.01" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase">Target Harga (Rp)</label>
                            <input v-model="form.target_price" type="number" step="0.01" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase">Rekomendasi</label>
                            <select v-model="form.recommendation" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-1 focus:ring-emerald-500/50">
                                <option value="">Tidak ada</option>
                                <option value="BUY">BUY</option>
                                <option value="HOLD">HOLD</option>
                                <option value="SELL">SELL</option>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase">Deskripsi / Ticker Brief</label>
                        <textarea v-model="form.description" rows="3" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-1 focus:ring-emerald-500/50"></textarea>
                    </div>
                </div>

                <!-- 2. Profil Perusahaan -->
                <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl space-y-6">
                    <h3 class="text-sm font-bold text-emerald-500 uppercase tracking-wider border-b border-emerald-950/30 pb-3">2. Profil Perusahaan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="space-y-2"><label class="text-xs font-bold text-slate-400 uppercase">Industri</label><input v-model="form.company_profile.industry" type="text" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" /></div>
                        <div class="space-y-2"><label class="text-xs font-bold text-slate-400 uppercase">Papan Pencatatan</label><input v-model="form.company_profile.board" type="text" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" /></div>
                        <div class="space-y-2"><label class="text-xs font-bold text-slate-400 uppercase">Tanggal Listing</label><input v-model="form.company_profile.listingDate" type="text" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" /></div>
                        <div class="space-y-2"><label class="text-xs font-bold text-slate-400 uppercase">Market Cap</label><input v-model="form.company_profile.marketCap" type="text" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" /></div>
                        <div class="space-y-2"><label class="text-xs font-bold text-slate-400 uppercase">Saham Beredar</label><input v-model="form.company_profile.outstandingShares" type="text" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" /></div>
                        <div class="space-y-2"><label class="text-xs font-bold text-slate-400 uppercase">Website</label><input v-model="form.company_profile.website" type="text" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" /></div>
                        <div class="space-y-2"><label class="text-xs font-bold text-slate-400 uppercase">Telepon</label><input v-model="form.company_profile.phone" type="text" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" /></div>
                        <div class="space-y-2"><label class="text-xs font-bold text-slate-400 uppercase">Email</label><input v-model="form.company_profile.email" type="text" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" /></div>
                        <div class="space-y-2"><label class="text-xs font-bold text-slate-400 uppercase">Tags (Pisahkan dengan koma)</label>
                            <input v-model="tagsInput" @input="updateTags" type="text" placeholder="Contoh: SOE, Banking, Bluechip" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase">Bidang Usaha</label>
                        <textarea v-model="form.company_profile.business" rows="2" class="w-full bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-1 focus:ring-emerald-500/50"></textarea>
                    </div>
                </div>

                <!-- 3. Risiko Utama -->
                <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl space-y-6">
                    <div class="flex justify-between items-center border-b border-emerald-950/30 pb-3">
                        <h3 class="text-sm font-bold text-emerald-500 uppercase tracking-wider">3. Risiko Utama</h3>
                        <button type="button" @click="addRisk" class="text-xs font-semibold bg-emerald-600/20 text-emerald-400 px-3 py-1 rounded-lg hover:bg-emerald-600/30 transition-colors flex items-center gap-1"><Plus class="w-3 h-3" /> Tambah Risiko</button>
                    </div>
                    <div v-for="(risk, index) in form.main_risks" :key="index" class="flex items-center gap-3">
                        <input v-model="form.main_risks[index]" type="text" placeholder="Masukkan risiko utama..." class="flex-1 bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2.5 text-sm text-white focus:ring-1 focus:ring-emerald-500/50" />
                        <button type="button" @click="removeRisk(index)" class="p-2.5 text-rose-500 hover:bg-rose-500/10 rounded-xl transition-colors"><Trash2 class="w-4 h-4" /></button>
                    </div>
                    <p v-if="form.main_risks.length === 0" class="text-slate-500 text-sm">Belum ada risiko utama ditambahkan.</p>
                </div>

                <!-- 4. Financial Highlight -->
                <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl space-y-6">
                    <div class="flex justify-between items-center border-b border-emerald-950/30 pb-3">
                        <h3 class="text-sm font-bold text-emerald-500 uppercase tracking-wider">4. Financial Highlights</h3>
                        <button type="button" @click="addHighlight" class="text-xs font-semibold bg-emerald-600/20 text-emerald-400 px-3 py-1 rounded-lg hover:bg-emerald-600/30 transition-colors flex items-center gap-1"><Plus class="w-3 h-3" /> Tambah Data</button>
                    </div>
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                        <div v-for="(item, index) in form.financial_highlights" :key="index" class="p-4 bg-[#090b0a] border border-emerald-950/40 rounded-xl flex flex-col sm:flex-row gap-4 relative">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 flex-1">
                                <div class="col-span-2"><label class="text-xs text-slate-500">Judul</label><input v-model="item.title" type="text" class="w-full bg-[#121614] border-emerald-950/40 rounded-lg text-xs text-white p-2" placeholder="Net Profit" /></div>
                                <div class="col-span-2"><label class="text-xs text-slate-500">Nilai (Rp/%)</label><input v-model="item.value" type="text" class="w-full bg-[#121614] border-emerald-950/40 rounded-lg text-xs text-white p-2" placeholder="Rp 15,0 T" /></div>
                                <div class="col-span-2 sm:col-span-1"><label class="text-xs text-slate-500">Perubahan YoY</label><input v-model="item.change" type="text" class="w-full bg-[#121614] border-emerald-950/40 rounded-lg text-xs text-white p-2" placeholder="+10%" /></div>
                                <div class="col-span-2 sm:col-span-1">
                                    <label class="text-xs text-slate-500">Arah</label>
                                    <select v-model="item.type" class="w-full bg-[#121614] border-emerald-950/40 rounded-lg text-xs text-white p-2">
                                        <option value="up">Naik (Hijau)</option>
                                        <option value="down">Turun (Merah)</option>
                                    </select>
                                </div>
                                <div class="col-span-2"><label class="text-xs text-slate-500">Lucide Icon (Opsional)</label><input v-model="item.icon" type="text" class="w-full bg-[#121614] border-emerald-950/40 rounded-lg text-xs text-white p-2" placeholder="TrendingUp" /></div>
                            </div>
                            <button type="button" @click="removeHighlight(index)" class="self-end sm:self-center p-2 text-rose-500 hover:bg-rose-500/10 rounded-lg transition-colors mt-2 sm:mt-0"><Trash2 class="w-4 h-4" /></button>
                        </div>
                    </div>
                </div>

                <!-- 5. Rasio Keuangan -->
                <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl space-y-6">
                    <div class="flex justify-between items-center border-b border-emerald-950/30 pb-3">
                        <h3 class="text-sm font-bold text-emerald-500 uppercase tracking-wider">5. Rasio Keuangan</h3>
                        <button type="button" @click="addRatio" class="text-xs font-semibold bg-emerald-600/20 text-emerald-400 px-3 py-1 rounded-lg hover:bg-emerald-600/30 transition-colors flex items-center gap-1"><Plus class="w-3 h-3" /> Tambah Rasio</button>
                    </div>
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                        <div v-for="(ratio, index) in form.financial_ratios" :key="'ratio'+index" class="p-4 bg-[#090b0a] border border-emerald-950/40 rounded-xl flex gap-3 relative items-center">
                            <div class="grid grid-cols-4 gap-3 flex-1">
                                <div class="col-span-1"><label class="text-xs text-slate-500">Rasio</label><input v-model="ratio.name" type="text" class="w-full bg-[#121614] border-emerald-950/40 rounded-lg text-xs text-white p-2" placeholder="PER" /></div>
                                <div class="col-span-1"><label class="text-xs text-slate-500">Nilai</label><input v-model="ratio.value" type="text" class="w-full bg-[#121614] border-emerald-950/40 rounded-lg text-xs text-white p-2" placeholder="12,5x" /></div>
                                <div class="col-span-1"><label class="text-xs text-slate-500">Periode</label><input v-model="ratio.period" type="text" class="w-full bg-[#121614] border-emerald-950/40 rounded-lg text-xs text-white p-2" placeholder="2025" /></div>
                                <div class="col-span-1"><label class="text-xs text-slate-500">YoY</label><input v-model="ratio.change" type="text" class="w-full bg-[#121614] border-emerald-950/40 rounded-lg text-xs text-white p-2" placeholder="+1,2%" /></div>
                            </div>
                            <button type="button" @click="removeRatio(index)" class="p-2 text-rose-500 hover:bg-rose-500/10 rounded-lg transition-colors"><Trash2 class="w-4 h-4" /></button>
                        </div>
                    </div>
                </div>

                <!-- 6. Relasi Dokumen -->
                <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl space-y-6">
                    <h3 class="text-sm font-bold text-emerald-500 uppercase tracking-wider border-b border-emerald-950/30 pb-3">6. Berita & Keterbukaan Informasi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase">Tautkan Artikel / Riset</label>
                            <select v-model="form.article_ids" multiple class="w-full h-48 bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-white focus:ring-1 focus:ring-emerald-500/50">
                                <option v-for="article in articles" :key="article.id" :value="article.id">
                                    {{ article.title }}
                                </option>
                            </select>
                            <p class="text-xs text-slate-500">Tahan tombol Ctrl (Windows) / Cmd (Mac) untuk memilih lebih dari satu.</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase">Tautkan Keterbukaan Informasi</label>
                            <select v-model="form.disclosure_ids" multiple class="w-full h-48 bg-[#090b0a] border border-emerald-950/40 rounded-xl px-4 py-2 text-sm text-white focus:ring-1 focus:ring-emerald-500/50">
                                <option v-for="disclosure in disclosures" :key="disclosure.id" :value="disclosure.id">
                                    {{ disclosure.title }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>
