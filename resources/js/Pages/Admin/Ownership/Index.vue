<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';

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
    form.post(route('admin.desk-brief.ownership.upload'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Ownership Intelligence Admin" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Ownership Intelligence Data Sync
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Success Message -->
                <div v-if="$page.props.flash && $page.props.flash.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ $page.props.flash.success }}
                </div>

                <!-- Error Message -->
                <div v-if="form.errors.message" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ form.errors.message }}
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium mb-4">Upload Data KSEI (PDF / CSV / Excel)</h3>
                        <p class="text-sm text-gray-500 mb-6">
                            Unggah pengumuman bursa (KSEI) untuk periode saat ini dan sebelumnya. Sistem menerima format PDF (otomasi parser) maupun CSV/Excel (hasil konversi manual).
                        </p>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Current Period -->
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <h4 class="font-medium text-gray-800 mb-4">Data Periode Saat Ini</h4>
                                    
                                    <div class="mb-4">
                                        <label class="block font-medium text-sm text-gray-700 mb-1">Tanggal Data (Current)</label>
                                        <input type="date" v-model="form.date_current" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" required>
                                        <InputError :message="form.errors.date_current" class="mt-2" />
                                    </div>
                                    
                                    <div>
                                        <label class="block font-medium text-sm text-gray-700 mb-1">File KSEI (Current)</label>
                                        <input type="file" @input="form.file_current = $event.target.files[0]" accept="application/pdf, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                                        <InputError :message="form.errors.file_current" class="mt-2" />
                                    </div>
                                </div>

                                <!-- Previous Period -->
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <h4 class="font-medium text-gray-800 mb-4">Data Periode Sebelumnya (Opsional)</h4>
                                    <p class="text-xs text-gray-500 mb-3">Dibutuhkan jika ingin menghitung akumulasi/distribusi (perubahan saham).</p>
                                    
                                    <div class="mb-4">
                                        <label class="block font-medium text-sm text-gray-700 mb-1">Tanggal Data (Previous)</label>
                                        <input type="date" v-model="form.date_previous" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                        <InputError :message="form.errors.date_previous" class="mt-2" />
                                    </div>
                                    
                                    <div>
                                        <label class="block font-medium text-sm text-gray-700 mb-1">File KSEI (Previous)</label>
                                        <input type="file" @input="form.file_previous = $event.target.files[0]" accept="application/pdf, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                        <InputError :message="form.errors.file_previous" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" :class="{ 'opacity-25': form.processing }">
                                    <span v-if="form.processing">Mengunggah & Memproses...</span>
                                    <span v-else>Unggah & Proses Data</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Recent Snapshots Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium mb-4">Riwayat Data Kepemilikan (Snapshots)</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Snapshot ID</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Periode</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Unggah</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="snapshot in recentSnapshots" :key="snapshot.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ snapshot.id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ snapshot.period_date }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(snapshot.created_at).toLocaleString('id-ID') }}</td>
                                    </tr>
                                    <tr v-if="recentSnapshots.length === 0">
                                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Belum ada data snapshot yang diunggah.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </AdminLayout>
</template>
