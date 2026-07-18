<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Upload, FileText, CheckCircle, AlertCircle, Clock } from '@lucide/vue';

const props = defineProps({
    uploads: Object,
});

const form = useForm({
    files: [],
});

const uploadError = ref(null);

const submit = () => {
    uploadError.value = null;
    
    // Validasi frontend untuk jumlah file (Opsional, menghindari limit PHP)
    if (form.files.length > 20) {
        uploadError.value = "Maksimal upload 20 file sekaligus (Limit Server).";
        return;
    }

    form.post(route('admin.eod-uploads.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            document.getElementById('file-upload').value = '';
        },
        onError: (errors) => {
            console.error(errors);
            if (Object.keys(errors).length === 0) {
                uploadError.value = "Gagal mengunggah file. Kemungkinan ukuran total terlalu besar atau melebihi batas server.";
            }
        }
    });
};

const handleFileChange = (e) => {
    form.files = Array.from(e.target.files);
};

const getStatusBadgeClass = (status) => {
    switch(status) {
        case 'completed': return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
        case 'processing': return 'bg-blue-500/10 text-blue-500 border-blue-500/20';
        case 'failed': return 'bg-red-500/10 text-red-500 border-red-500/20';
        default: return 'bg-slate-500/10 text-slate-400 border-slate-500/20';
    }
};

const getStatusIcon = (status) => {
    switch(status) {
        case 'completed': return CheckCircle;
        case 'processing': return Clock;
        case 'failed': return AlertCircle;
        default: return Clock;
    }
};
</script>

<template>
    <Head title="EOD Uploads" />

    <AdminLayout>
        <div class="p-6 max-w-7xl mx-auto space-y-6">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                        <Upload class="w-6 h-6 text-emerald-500" />
                        EOD Stock Summary Upload
                    </h1>
                    <p class="text-slate-400 text-sm mt-1">Upload satu atau beberapa file Excel (Batch Upload). Tanggal akan dibaca otomatis dari nama file.</p>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="bg-[#121614] border border-slate-800 rounded-xl p-6">
                <div v-if="uploadError" class="mb-4 bg-red-500/10 border border-red-500/20 text-red-500 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                    <AlertCircle class="w-5 h-5" />
                    {{ uploadError }}
                </div>
                
                <form @submit.prevent="submit" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:flex-1">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Pilih File Excel (.xlsx, .csv)</label>
                        <input type="file" id="file-upload" @change="handleFileChange" accept=".xlsx,.xls,.csv" multiple required class="w-full bg-[#0a0d0b] border border-slate-800 rounded-lg px-4 py-1.5 text-slate-200 focus:outline-none focus:border-emerald-500/50 file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-500/10 file:text-emerald-500 hover:file:bg-emerald-500/20">
                        <div v-if="form.errors.files" class="text-red-500 text-xs mt-1">{{ form.errors.files }}</div>
                    </div>
                    <div class="w-full md:w-auto">
                        <button type="submit" :disabled="form.processing || form.files.length === 0" class="w-full md:w-auto bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-2 rounded-lg font-medium transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                            <Upload class="w-4 h-4" />
                            {{ form.processing ? 'Uploading...' : `Upload ${form.files.length > 0 ? form.files.length + ' File(s)' : 'Data'}` }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Upload History Table -->
            <div class="bg-[#121614] border border-slate-800 rounded-xl overflow-hidden">
                <div class="p-6 border-b border-slate-800 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-white">Riwayat Upload</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-400">
                        <thead class="text-xs uppercase bg-[#0a0d0b] text-slate-500 border-b border-slate-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-medium">Trading Date</th>
                                <th scope="col" class="px-6 py-4 font-medium">File Name</th>
                                <th scope="col" class="px-6 py-4 font-medium">Status</th>
                                <th scope="col" class="px-6 py-4 font-medium">Rows Processed</th>
                                <th scope="col" class="px-6 py-4 font-medium">Uploader</th>
                                <th scope="col" class="px-6 py-4 font-medium text-right">Uploaded At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <tr v-for="upload in uploads.data" :key="upload.id" class="hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 font-medium text-slate-300">
                                    {{ new Date(upload.trading_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                                </td>
                                <td class="px-6 py-4 flex items-center gap-2">
                                    <FileText class="w-4 h-4 text-slate-500" />
                                    <span class="truncate max-w-[200px]" :title="upload.filename">{{ upload.filename }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium border', getStatusBadgeClass(upload.status)]">
                                        <component :is="getStatusIcon(upload.status)" class="w-3 h-3" />
                                        {{ upload.status }}
                                    </span>
                                    <div v-if="upload.error_message" class="text-xs text-red-400 mt-1 truncate max-w-[200px]" :title="upload.error_message">
                                        {{ upload.error_message }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    {{ upload.processed_rows }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ upload.user?.name || '-' }}
                                </td>
                                <td class="px-6 py-4 text-right text-slate-500">
                                    {{ new Date(upload.created_at).toLocaleString('id-ID') }}
                                </td>
                            </tr>
                            <tr v-if="uploads.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    <FileText class="w-12 h-12 mx-auto mb-3 opacity-20" />
                                    Belum ada data riwayat upload.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="p-4 border-t border-slate-800 flex items-center justify-center gap-1" v-if="uploads.links && uploads.links.length > 3">
                    <template v-for="(link, index) in uploads.links" :key="index">
                        <Link 
                            v-if="link.url"
                            :href="link.url" 
                            v-html="link.label"
                            class="px-3 py-1 rounded text-sm transition-colors"
                            :class="link.active ? 'bg-emerald-500/20 text-emerald-500 font-medium' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'"
                        />
                        <span 
                            v-else 
                            v-html="link.label"
                            class="px-3 py-1 rounded text-sm text-slate-600"
                        ></span>
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
