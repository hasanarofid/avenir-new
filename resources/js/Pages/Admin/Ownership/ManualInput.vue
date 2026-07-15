<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Upload, Trash2, Image as ImageIcon } from '@lucide/vue';
import Swal from 'sweetalert2';

const props = defineProps({
    inputs: Array,
});

const form = useForm({
    ticker: '',
    ubo_image: null,
    shareholder_image: null,
});

const submit = () => {
    form.post(route('admin.ownership.manual.store'), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Data input manual berhasil disimpan.',
                background: '#1A1A1A', color: '#fff', confirmButtonColor: '#059669'
            });
            form.reset();
        }
    });
};

const destroy = (id) => {
    Swal.fire({
        title: 'Hapus data?',
        text: "Gambar yang diunggah akan dihapus secara permanen.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#4b5563',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        background: '#1A1A1A',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('admin.ownership.manual.destroy', id), {
                preserveScroll: true,
            });
        }
    });
};
</script>

<template>
    <Head title="Manual Inputs UBO" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white leading-tight">
                    Ownership Manual Inputs (UBO & Shareholders)
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Form Upload -->
                <div class="bg-[#1A1A1A] overflow-hidden shadow-xl sm:rounded-2xl border border-gray-800">
                    <div class="p-6 border-b border-gray-800 bg-[#222]">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <Upload class="w-5 h-5 text-emerald-500" />
                            Upload Screenshot Manual
                        </h3>
                    </div>
                    
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="mb-5">
                                <label class="block text-sm font-medium text-gray-300 mb-2">Ticker Emiten (contoh: BUMI)</label>
                                <input type="text" v-model="form.ticker" class="w-full bg-[#111] border border-gray-700 rounded-lg text-white px-4 py-2 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500" required>
                                <InputError :message="form.errors.ticker" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Screenshot UBO / Controlling Shareholders</label>
                                    <input type="file" @input="form.ubo_image = $event.target.files[0]" accept="image/*" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-600/10 file:text-emerald-400 hover:file:bg-emerald-600/20">
                                    <InputError :message="form.errors.ubo_image" class="mt-2" />
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Screenshot Komposisi Pemegang Saham</label>
                                    <input type="file" @input="form.shareholder_image = $event.target.files[0]" accept="image/*" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-600/10 file:text-emerald-400 hover:file:bg-emerald-600/20">
                                    <InputError :message="form.errors.shareholder_image" class="mt-2" />
                                </div>
                            </div>
                            
                            <div class="flex justify-end pt-4">
                                <button type="submit" :disabled="form.processing" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 px-6 rounded-lg transition-colors flex items-center gap-2 disabled:opacity-50">
                                    <Upload class="w-4 h-4" />
                                    Simpan Manual Input
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- List Manual Inputs -->
                <div class="bg-[#1A1A1A] overflow-hidden shadow-xl sm:rounded-2xl border border-gray-800">
                    <div class="p-6 border-b border-gray-800 bg-[#222]">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <ImageIcon class="w-5 h-5 text-emerald-500" />
                            Data Input Manual
                        </h3>
                    </div>
                    
                    <div class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-400">
                                <thead class="text-xs text-gray-400 uppercase bg-[#222] border-b border-gray-800">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 rounded-tl-lg">Ticker</th>
                                        <th scope="col" class="px-6 py-4">UBO Image</th>
                                        <th scope="col" class="px-6 py-4">Shareholder Image</th>
                                        <th scope="col" class="px-6 py-4 rounded-tr-lg text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="inputs.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                            Belum ada manual input
                                        </td>
                                    </tr>
                                    <tr v-for="input in inputs" :key="input.id" class="border-b border-gray-800 hover:bg-white/5 transition-colors">
                                        <td class="px-6 py-4 font-bold text-white">
                                            {{ input.ticker }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <a v-if="input.ubo_image_path" :href="'/storage/' + input.ubo_image_path" target="_blank" class="text-emerald-400 hover:underline">
                                                Lihat Gambar
                                            </a>
                                            <span v-else class="text-gray-600">-</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a v-if="input.shareholder_image_path" :href="'/storage/' + input.shareholder_image_path" target="_blank" class="text-emerald-400 hover:underline">
                                                Lihat Gambar
                                            </a>
                                            <span v-else class="text-gray-600">-</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button @click="destroy(input.id)" class="text-red-400 hover:text-red-300 p-2 hover:bg-red-400/10 rounded-lg transition-colors" title="Hapus">
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </td>
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
