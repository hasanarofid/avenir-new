<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Coins, AlertCircle } from '@lucide/vue';

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
        <div class="space-y-6 pb-12">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-emerald-950/30 pb-4">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-white flex items-center gap-2">
                        <Coins class="w-6 h-6 text-emerald-500" />
                        Pool Mitra
                    </h2>
                    <p class="text-sm text-slate-400 mt-1">Tentukan budget pool pembagian hasil (engagement) untuk mitra analis di bulan tertentu.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Form Input Pool -->
                <div class="lg:col-span-1 bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl h-fit">
                    <h3 class="text-lg font-bold text-white mb-4 border-b border-emerald-950/30 pb-2">Update Pool Bulanan</h3>
                    
                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-slate-400 font-bold uppercase tracking-widest block mb-1">Tahun</label>
                                <input type="number" v-model="form.period_year" required
                                    class="w-full bg-[#090b0a] border border-emerald-950/30 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50" />
                            </div>
                            <div>
                                <label class="text-xs text-slate-400 font-bold uppercase tracking-widest block mb-1">Bulan</label>
                                <select v-model="form.period_month" required
                                    class="w-full bg-[#090b0a] border border-emerald-950/30 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50 appearance-none">
                                    <option v-for="(name, index) in monthNames" :key="index" :value="index + 1">{{ name }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs text-slate-400 font-bold uppercase tracking-widest block mb-1">Total Budget Pool (Rp)</label>
                            <input type="number" v-model="form.pool_budget_idr" required min="0"
                                class="w-full bg-[#090b0a] border border-emerald-950/30 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50" />
                        </div>

                        <div>
                            <label class="text-xs text-slate-400 font-bold uppercase tracking-widest block mb-1">Catatan (Opsional)</label>
                            <input type="text" v-model="form.notes"
                                class="w-full bg-[#090b0a] border border-emerald-950/30 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50" />
                        </div>

                        <div class="pt-4 border-t border-emerald-950/30 flex items-center justify-between">
                            <transition enter-active-class="transition ease-in-out duration-300" enter-from-class="opacity-0" leave-active-class="transition ease-in-out duration-300" leave-to-class="opacity-0">
                                <span v-if="form.recentlySuccessful" class="text-xs font-bold text-emerald-400">Berhasil disimpan!</span>
                                <span v-else></span>
                            </transition>
                            <button type="submit" :disabled="form.processing"
                                class="px-5 py-2.5 bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 rounded-lg text-sm font-semibold hover:bg-emerald-500 hover:text-white transition-all disabled:opacity-50">
                                Simpan Pool
                            </button>
                        </div>
                    </form>
                </div>

                <!-- History Pool -->
                <div class="lg:col-span-2 bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl">
                    <h3 class="text-lg font-bold text-white mb-4 border-b border-emerald-950/30 pb-2">Riwayat Pool</h3>
                    
                    <div v-if="pools.length === 0" class="text-center py-12">
                        <AlertCircle class="w-10 h-10 text-emerald-900/50 mx-auto mb-3" />
                        <h4 class="text-base font-bold text-slate-300">Belum Ada Data</h4>
                        <p class="text-xs text-slate-500 mt-1">Belum ada history input pool mitra.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-max">
                            <thead>
                                <tr class="border-b border-emerald-950/50 text-xs font-bold uppercase tracking-widest text-slate-500 bg-[#090b0a]">
                                    <th class="py-3 px-4 rounded-tl-lg">Periode</th>
                                    <th class="py-3 px-4">Total Pool</th>
                                    <th class="py-3 px-4">Catatan</th>
                                    <th class="py-3 px-4 rounded-tr-lg">Diupdate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="pool in pools" :key="pool.period_year + '-' + pool.period_month" class="border-b border-emerald-950/20 hover:bg-[#090b0a]/50 transition-colors">
                                    <td class="py-3 px-4 text-sm font-medium text-white">{{ monthNames[pool.period_month - 1] }} {{ pool.period_year }}</td>
                                    <td class="py-3 px-4 text-sm text-emerald-400 font-mono font-bold">{{ formatRupiah(pool.pool_budget_idr) }}</td>
                                    <td class="py-3 px-4 text-sm text-slate-400">{{ pool.notes || '-' }}</td>
                                    <td class="py-3 px-4 text-xs text-slate-500">{{ new Date(pool.updated_at).toLocaleDateString('id-ID') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
