<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Upload, Database, FileSpreadsheet, Trash2 } from '@lucide/vue';
import Swal from 'sweetalert2';

const props = defineProps({
    recentSnapshots: Array,
});

const form = useForm({
    date_current: '',
    file_current: null,
    date_previous: '',
    file_previous: null,
});

const submit = () => {
    // Client-side file size validation to prevent PHP post_max_size silent drops
    const maxSizeBytes = 10 * 1024 * 1024; // 10MB
    
    if (form.file_current && form.file_current.size > maxSizeBytes) {
        Swal.fire({
            icon: 'error',
            title: 'File Terlalu Besar!',
            text: 'Ukuran file Current melebihi batas 10MB. Silakan kompres PDF atau perbesar konfigurasi upload_max_filesize di server Anda.',
            background: '#1A1A1A', color: '#fff', confirmButtonColor: '#dc2626'
        });
        return;
    }
    
    if (form.file_previous && form.file_previous.size > maxSizeBytes) {
        Swal.fire({
            icon: 'error',
            title: 'File Terlalu Besar!',
            text: 'Ukuran file Previous melebihi batas 10MB. Silakan kompres PDF atau perbesar konfigurasi upload_max_filesize di server Anda.',
            background: '#1A1A1A', color: '#fff', confirmButtonColor: '#dc2626'
        });
        return;
    }

    form.post(route('admin.desk-brief.ownership.upload'), {
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.flash && page.props.flash.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: page.props.flash.success,
                    background: '#1A1A1A',
                    color: '#fff',
                    confirmButtonColor: '#059669'
                });
            }
            form.reset('file_current', 'file_previous');
        },
        onError: (errors) => {
            let errorMsg = 'Pastikan semua kolom terisi dengan benar.';
            if (errors.message) errorMsg = errors.message;
            else if (errors.file_current) errorMsg = errors.file_current;
            else if (errors.file_previous) errorMsg = errors.file_previous;
            
            Swal.fire({
                icon: 'error',
                title: 'Upload Gagal!',
                text: errorMsg,
                background: '#1A1A1A',
                color: '#fff',
                confirmButtonColor: '#dc2626'
            });
        }
    });
};

const deleteSnapshot = (id) => {
    Swal.fire({
        title: 'Hapus Snapshot?',
        text: "Semua data kepemilikan dan relasi (edges) yang terikat dengan snapshot ini akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#4b5563',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        background: '#1A1A1A',
        color: '#fff',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('admin.desk-brief.ownership.destroy', id), {
                preserveScroll: true,
                onSuccess: (page) => {
                    if (page.props.flash && page.props.flash.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: page.props.flash.success,
                            background: '#1A1A1A',
                            color: '#fff',
                            confirmButtonColor: '#059669'
                        });
                    }
                },
                onError: (errors) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menghapus!',
                        text: errors.message || 'Terjadi kesalahan sistem.',
                        background: '#1A1A1A',
                        color: '#fff',
                        confirmButtonColor: '#dc2626'
                    });
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Ownership Intelligence Admin" />

    <AdminLayout>
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-white">Ownership Intelligence</h2>
                <p class="text-gray-400 mt-1">Kelola sinkronisasi data kepemilikan KSEI / BEI</p>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Success Message -->
            <div v-if="$page.props.flash && $page.props.flash.success" class="bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-3 rounded-lg relative">
                {{ $page.props.flash.success }}
            </div>

            <!-- Error Message -->
            <div v-if="form.errors.message" class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg relative">
                {{ form.errors.message }}
            </div>

            <div class="bg-[#1A1A1A] border border-gray-800 rounded-xl overflow-hidden shadow-xl">
                <div class="p-6 border-b border-gray-800 flex items-center gap-2">
                    <Database class="w-5 h-5 text-emerald-400" />
                    <h3 class="text-lg font-bold text-white">Upload Data KSEI (PDF / CSV / Excel)</h3>
                </div>
                
                <div class="p-6">
                    <p class="text-sm text-gray-400 mb-6">
                        Unggah pengumuman bursa (KSEI) untuk periode saat ini dan sebelumnya. Sistem menerima format PDF (otomasi parser) maupun CSV/Excel (hasil konversi manual).
                    </p>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Current Period -->
                            <div class="bg-[#222] p-5 rounded-xl border border-emerald-500/20 relative flex flex-col justify-between">
                                <div class="absolute -top-3 -left-3 w-6 h-6 bg-emerald-600 rounded-full flex items-center justify-center text-xs font-bold text-white border-2 border-[#1A1A1A]">1</div>
                                
                                <div>
                                    <h4 class="font-bold text-emerald-400 mb-4">Data Periode Saat Ini</h4>
                                    
                                    <div class="mb-5">
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Tanggal Data (Current)</label>
                                        <input type="date" v-model="form.date_current" class="w-full bg-[#111] border border-gray-700 rounded-lg text-white px-4 py-2 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500" required>
                                        <InputError :message="form.errors.date_current" class="mt-2" />
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">File KSEI (Current)</label>
                                        <input type="file" @input="form.file_current = $event.target.files[0]" accept="application/pdf, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-600/10 file:text-emerald-400 hover:file:bg-emerald-600/20" required>
                                        <InputError :message="form.errors.file_current" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Previous Period -->
                            <div class="bg-[#222] p-5 rounded-xl border border-gray-700 relative flex flex-col justify-between">
                                <div class="absolute -top-3 -left-3 w-6 h-6 bg-gray-600 rounded-full flex items-center justify-center text-xs font-bold text-white border-2 border-[#1A1A1A]">2</div>
                                
                                <div>
                                    <h4 class="font-bold text-gray-300 mb-2">Data Periode Sebelumnya (Opsional)</h4>
                                    <p class="text-xs text-gray-500 mb-4">Dibutuhkan jika ingin menghitung akumulasi/distribusi (perubahan saham).</p>
                                    
                                    <div class="mb-5">
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Tanggal Data (Previous)</label>
                                        <input type="date" v-model="form.date_previous" class="w-full bg-[#111] border border-gray-700 rounded-lg text-white px-4 py-2 focus:border-gray-500 focus:ring-1 focus:ring-gray-500">
                                        <InputError :message="form.errors.date_previous" class="mt-2" />
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">File KSEI (Previous)</label>
                                        <input type="file" @input="form.file_previous = $event.target.files[0]" accept="application/pdf, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-700 file:text-gray-300 hover:file:bg-gray-600">
                                        <InputError :message="form.errors.file_previous" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 pt-6 border-t border-gray-800">
                            <button type="submit" :disabled="form.processing" class="inline-flex items-center px-5 py-2.5 bg-emerald-600 border border-transparent rounded-lg font-bold text-sm text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 focus:ring-offset-[#1A1A1A] transition-colors gap-2" :class="{ 'opacity-50 cursor-not-allowed': form.processing }">
                                <span v-if="form.processing" class="w-4 h-4 rounded-full border-2 border-white/20 border-t-white animate-spin"></span>
                                <Upload v-else class="w-4 h-4" />
                                <span>{{ form.processing ? 'Mengunggah...' : 'Unggah & Proses Data' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Recent Snapshots Table -->
            <div class="bg-[#1A1A1A] border border-gray-800 rounded-xl overflow-hidden shadow-xl">
                <div class="p-6 border-b border-gray-800 flex items-center gap-2">
                    <FileSpreadsheet class="w-5 h-5 text-blue-400" />
                    <h3 class="text-lg font-bold text-white">Riwayat Data Kepemilikan (Snapshots)</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-800">
                        <thead class="bg-[#111]">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Snapshot ID</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Tanggal Periode</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Waktu Unggah</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800 bg-[#1A1A1A]">
                            <tr v-for="snapshot in recentSnapshots" :key="snapshot.id" class="hover:bg-[#222] transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300 font-medium">#{{ snapshot.id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-emerald-400 font-bold">{{ snapshot.period_date }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(snapshot.created_at).toLocaleString('id-ID') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="deleteSnapshot(snapshot.id)" class="text-red-400 hover:text-red-300 transition-colors p-2 hover:bg-red-400/10 rounded-lg inline-flex items-center gap-1" title="Hapus Snapshot">
                                        <Trash2 class="w-4 h-4" />
                                        <span class="text-xs">Hapus</span>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="recentSnapshots.length === 0">
                                <td colspan="4" class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center italic">
                                    Belum ada data snapshot yang diunggah.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </AdminLayout>
</template>
