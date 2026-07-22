<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Upload, 
    FileText, 
    CheckCircle, 
    AlertCircle, 
    Clock, 
    Trash2, 
    Edit, 
    RotateCw, 
    Search, 
    Filter, 
    RefreshCw,
    X
} from 'lucide-vue-next';

const props = defineProps({
    uploads: Object,
    filters: Object,
});

// Upload Form
const uploadForm = useForm({
    files: [],
});
const uploadError = ref(null);

const handleFileChange = (e) => {
    uploadForm.files = Array.from(e.target.files);
};

const submitUpload = () => {
    uploadError.value = null;
    if (uploadForm.files.length > 20) {
        uploadError.value = "Maksimal upload 20 file sekaligus (Limit Server).";
        return;
    }

    uploadForm.post(route('admin.eod-uploads.store'), {
        preserveScroll: true,
        onSuccess: () => {
            uploadForm.reset();
            const fileInput = document.getElementById('file-upload');
            if (fileInput) fileInput.value = '';
        },
        onError: (errors) => {
            console.error(errors);
            if (Object.keys(errors).length === 0) {
                uploadError.value = "Gagal mengunggah file. Ukuran file terlalu besar atau melebihi batas server.";
            }
        }
    });
};

// Search & Filtering
const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');

const applyFilter = () => {
    router.get(route('admin.eod-uploads.index'), {
        search: search.value,
        status: statusFilter.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilter = () => {
    search.value = '';
    statusFilter.value = 'all';
    applyFilter();
};

// Bulk Delete
const selectedIds = ref([]);
const selectAll = ref(false);

const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedIds.value = props.uploads.data.map(u => u.id);
    } else {
        selectedIds.value = [];
    }
};

const bulkDeleteForm = useForm({
    ids: []
});

const executeBulkDelete = () => {
    if (selectedIds.value.length === 0) return;
    if (!confirm(`Apakah Anda yakin ingin menghapus ${selectedIds.value.length} data riwayat upload terpilih?`)) return;

    bulkDeleteForm.ids = selectedIds.value;
    bulkDeleteForm.delete(route('admin.eod-uploads.bulk-destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = [];
            selectAll.value = false;
        }
    });
};

// Single Item Actions
const isEditModalOpen = ref(false);
const editingItem = ref(null);
const editForm = useForm({
    trading_date: '',
    status: '',
});

const openEditModal = (item) => {
    editingItem.value = item;
    // Format date string for HTML date input YYYY-MM-DD
    const dateObj = new Date(item.trading_date);
    const dateStr = !isNaN(dateObj.getTime()) 
        ? dateObj.toISOString().split('T')[0] 
        : item.trading_date;

    editForm.trading_date = dateStr;
    editForm.status = item.status;
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    editingItem.value = null;
    editForm.reset();
};

const submitEdit = () => {
    if (!editingItem.value) return;
    editForm.put(route('admin.eod-uploads.update', editingItem.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
        }
    });
};

const deleteItem = (item) => {
    if (!confirm(`Hapus riwayat upload file "${item.filename}"?`)) return;
    router.delete(route('admin.eod-uploads.destroy', item.id), {
        preserveScroll: true,
    });
};

const reprocessItem = (item) => {
    if (!confirm(`Proses ulang data dari file "${item.filename}"?`)) return;
    router.post(route('admin.eod-uploads.reprocess', item.id), {}, {
        preserveScroll: true,
    });
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
                    <p class="text-slate-400 text-sm mt-1">Upload dan kelola riwayat pemrosesan data EOD Stock Summary saham daily.</p>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="bg-[#121614] border border-slate-800 rounded-xl p-6 shadow-sm">
                <div v-if="uploadError" class="mb-4 bg-red-500/10 border border-red-500/20 text-red-500 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                    <AlertCircle class="w-5 h-5 shrink-0" />
                    {{ uploadError }}
                </div>
                
                <form @submit.prevent="submitUpload" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:flex-1">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Unggah File Excel (.xlsx, .csv)</label>
                        <input 
                            type="file" 
                            id="file-upload" 
                            @change="handleFileChange" 
                            accept=".xlsx,.xls,.csv" 
                            multiple 
                            required 
                            class="w-full bg-[#0a0d0b] border border-slate-800 rounded-lg px-4 py-2 text-slate-200 focus:outline-none focus:border-emerald-500/50 file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-500/10 file:text-emerald-500 hover:file:bg-emerald-500/20 cursor-pointer"
                        />
                        <div v-if="uploadForm.errors.files" class="text-red-500 text-xs mt-1">{{ uploadForm.errors.files }}</div>
                    </div>
                    <div class="w-full md:w-auto">
                        <button 
                            type="submit" 
                            :disabled="uploadForm.processing || uploadForm.files.length === 0" 
                            class="w-full md:w-auto bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-2.5 rounded-lg font-medium transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            <Upload class="w-4 h-4" />
                            {{ uploadForm.processing ? 'Uploading...' : `Upload ${uploadForm.files.length > 0 ? uploadForm.files.length + ' File(s)' : 'Data'}` }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table Section with Search & Action Controls -->
            <div class="bg-[#121614] border border-slate-800 rounded-xl overflow-hidden shadow-sm">
                <div class="p-6 border-b border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <h2 class="text-lg font-semibold text-white">Riwayat Upload</h2>
                        <button 
                            v-if="selectedIds.length > 0"
                            @click="executeBulkDelete" 
                            class="bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors flex items-center gap-1.5"
                        >
                            <Trash2 class="w-3.5 h-3.5" />
                            Hapus Terpilih ({{ selectedIds.length }})
                        </button>
                    </div>

                    <!-- Search & Filter Inputs -->
                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <div class="relative w-full sm:w-64">
                            <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" />
                            <input 
                                v-model="search" 
                                type="text" 
                                placeholder="Cari nama file / tanggal..."
                                @keyup.enter="applyFilter"
                                class="w-full bg-[#0a0d0b] border border-slate-800 rounded-lg pl-9 pr-4 py-1.5 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/50"
                            />
                        </div>

                        <select 
                            v-model="statusFilter" 
                            @change="applyFilter"
                            class="w-full sm:w-auto bg-[#0a0d0b] border border-slate-800 rounded-lg px-3 py-1.5 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/50"
                        >
                            <option value="all">Semua Status</option>
                            <option value="completed">Completed</option>
                            <option value="processing">Processing</option>
                            <option value="failed">Failed</option>
                            <option value="pending">Pending</option>
                        </select>

                        <button 
                            @click="applyFilter" 
                            class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors flex items-center gap-1"
                        >
                            <Filter class="w-3.5 h-3.5" />
                            Filter
                        </button>

                        <button 
                            v-if="search || statusFilter !== 'all'"
                            @click="resetFilter" 
                            class="text-slate-400 hover:text-slate-200 text-xs underline"
                        >
                            Reset
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-400">
                        <thead class="text-xs uppercase bg-[#0a0d0b] text-slate-500 border-b border-slate-800">
                            <tr>
                                <th scope="col" class="p-4 w-4">
                                    <input 
                                        type="checkbox" 
                                        v-model="selectAll" 
                                        @change="toggleSelectAll"
                                        class="rounded bg-[#121614] border-slate-700 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-0 cursor-pointer"
                                    />
                                </th>
                                <th scope="col" class="px-6 py-4 font-medium">Trading Date</th>
                                <th scope="col" class="px-6 py-4 font-medium">File Name</th>
                                <th scope="col" class="px-6 py-4 font-medium">Status</th>
                                <th scope="col" class="px-6 py-4 font-medium">Rows Processed</th>
                                <th scope="col" class="px-6 py-4 font-medium">Uploader</th>
                                <th scope="col" class="px-6 py-4 font-medium">Uploaded At</th>
                                <th scope="col" class="px-6 py-4 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <tr v-for="upload in uploads.data" :key="upload.id" class="hover:bg-slate-800/30 transition-colors">
                                <td class="p-4 w-4">
                                    <input 
                                        type="checkbox" 
                                        :value="upload.id" 
                                        v-model="selectedIds"
                                        class="rounded bg-[#121614] border-slate-700 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-0 cursor-pointer"
                                    />
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-300 whitespace-nowrap">
                                    {{ upload.trading_date ? new Date(upload.trading_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-' }}
                                </td>
                                <td class="px-6 py-4 flex items-center gap-2">
                                    <FileText class="w-4 h-4 text-slate-500 shrink-0" />
                                    <span class="truncate max-w-[220px]" :title="upload.filename">{{ upload.filename }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="['inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium border', getStatusBadgeClass(upload.status)]">
                                        <component :is="getStatusIcon(upload.status)" class="w-3 h-3" />
                                        {{ upload.status }}
                                    </span>
                                    <div v-if="upload.error_message" class="text-xs text-red-400 mt-1 truncate max-w-[200px]" :title="upload.error_message">
                                        {{ upload.error_message }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ upload.processed_rows }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ upload.user?.name || '-' }}
                                </td>
                                <td class="px-6 py-4 text-slate-500 whitespace-nowrap text-xs">
                                    {{ new Date(upload.created_at).toLocaleString('id-ID') }}
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <button 
                                            @click="reprocessItem(upload)" 
                                            title="Proses Ulang File"
                                            class="p-1.5 hover:bg-blue-500/10 text-slate-400 hover:text-blue-400 rounded transition-colors"
                                        >
                                            <RotateCw class="w-4 h-4" />
                                        </button>
                                        <button 
                                            @click="openEditModal(upload)" 
                                            title="Edit Data"
                                            class="p-1.5 hover:bg-emerald-500/10 text-slate-400 hover:text-emerald-400 rounded transition-colors"
                                        >
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button 
                                            @click="deleteItem(upload)" 
                                            title="Hapus"
                                            class="p-1.5 hover:bg-red-500/10 text-slate-400 hover:text-red-400 rounded transition-colors"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="uploads.data.length === 0">
                                <td colspan="8" class="px-6 py-12 text-center text-slate-500">
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

        <!-- Modal Edit -->
        <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
            <div class="bg-[#121614] border border-slate-800 rounded-xl w-full max-w-md overflow-hidden shadow-2xl">
                <div class="p-5 border-b border-slate-800 flex justify-between items-center">
                    <h3 class="font-semibold text-white">Edit Riwayat Upload EOD</h3>
                    <button @click="closeEditModal" class="text-slate-400 hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <form @submit.prevent="submitEdit" class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Nama File</label>
                        <input type="text" :value="editingItem?.filename" disabled class="w-full bg-[#0a0d0b] border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Trading Date</label>
                        <input 
                            type="date" 
                            v-model="editForm.trading_date" 
                            required 
                            class="w-full bg-[#0a0d0b] border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/50"
                        />
                        <div v-if="editForm.errors.trading_date" class="text-red-500 text-xs mt-1">{{ editForm.errors.trading_date }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Status</label>
                        <select 
                            v-model="editForm.status" 
                            required 
                            class="w-full bg-[#0a0d0b] border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-emerald-500/50"
                        >
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                        </select>
                        <div v-if="editForm.errors.status" class="text-red-500 text-xs mt-1">{{ editForm.errors.status }}</div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                        <button 
                            type="button" 
                            @click="closeEditModal" 
                            class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-sm font-medium transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="editForm.processing" 
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-medium transition-colors disabled:opacity-50"
                        >
                            {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
