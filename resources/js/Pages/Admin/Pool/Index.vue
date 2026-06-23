<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    pools: Array,
    currentYear: Number,
    currentMonth: Number,
});

const form = useForm({
    period_year: props.currentYear,
    period_month: props.currentMonth,
    pool_budget_idr: 0,
    notes: ''
});

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(number);
};

const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

const submitForm = () => {
    form.post(route('admin.pool.store'), {
        onSuccess: () => {
            form.reset('pool_budget_idr', 'notes');
        }
    });
};
</script>

<template>
    <Head title="Pool Mitra" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen Pool Mitra</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Form Input Pool -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">Update Pool Bulanan</h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Tentukan budget pool pembagian hasil (engagement) untuk mitra analis di bulan tertentu.
                        </p>
                    </header>

                    <form @submit.prevent="submitForm" class="mt-6 space-y-6 max-w-xl">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="period_year" value="Tahun" />
                                <TextInput id="period_year" type="number" class="mt-1 block w-full" v-model="form.period_year" required />
                            </div>
                            <div>
                                <InputLabel for="period_month" value="Bulan" />
                                <select id="period_month" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" v-model="form.period_month" required>
                                    <option v-for="(name, index) in monthNames" :key="index" :value="index + 1">{{ name }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <InputLabel for="pool_budget_idr" value="Total Budget Pool (Rp)" />
                            <TextInput id="pool_budget_idr" type="number" class="mt-1 block w-full" v-model="form.pool_budget_idr" required />
                        </div>

                        <div>
                            <InputLabel for="notes" value="Catatan (Opsional)" />
                            <TextInput id="notes" type="text" class="mt-1 block w-full" v-model="form.notes" />
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">Simpan Pool</PrimaryButton>
                            <transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Berhasil disimpan.</p>
                            </transition>
                        </div>
                    </form>
                </div>

                <!-- History Pool -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Riwayat Pool</h2>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pool</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diupdate</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="pool in pools" :key="pool.period_year + '-' + pool.period_month">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ monthNames[pool.period_month - 1] }} {{ pool.period_year }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatRupiah(pool.pool_budget_idr) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ pool.notes || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(pool.updated_at).toLocaleDateString('id-ID') }}</td>
                            </tr>
                            <tr v-if="pools.length === 0">
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data pool.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
