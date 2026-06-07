<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Search, TrendingUp, TrendingDown, Minus } from 'lucide-vue-next';

const props = defineProps({
    tickers: Object,
    filters: Object
});
</script>

<template>
    <Head title="Emiten Hub Lite" />
    <AppLayout>
        <div class="min-h-screen bg-[#090b0a] pt-24 pb-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="text-center max-w-3xl mx-auto mb-12">
                    <h1 class="text-4xl md:text-5xl font-bold text-white tracking-tight mb-6">
                        Emiten Hub <span class="text-emerald-500">Lite</span>
                    </h1>
                    <p class="text-lg text-slate-400">
                        Cari data fundamental, sentimen berita, dan harga saham emiten pilihan Anda secara cepat.
                    </p>
                </div>

                <!-- Search -->
                <div class="max-w-xl mx-auto mb-12">
                    <form @submit.prevent="() => $inertia.get(route('emiten.index'), { search: filters.search })" class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <Search class="h-5 w-5 text-slate-500" />
                        </div>
                        <input 
                            v-model="filters.search"
                            type="text" 
                            class="block w-full pl-11 pr-4 py-4 bg-[#121614] border border-emerald-950/30 rounded-2xl text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all text-lg" 
                            placeholder="Cari kode emiten (cth: BBCA, GOTO)..."
                        />
                        <button type="submit" class="absolute inset-y-2 right-2 px-6 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-medium transition-colors">
                            Cari
                        </button>
                    </form>
                </div>

                <!-- Grid Tickers -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <Link 
                        v-for="ticker in tickers.data" 
                        :key="ticker.id" 
                        :href="route('emiten.show', ticker.symbol)"
                        class="bg-[#121614] border border-emerald-950/20 hover:border-emerald-500/50 rounded-2xl p-6 transition-all group"
                    >
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h2 class="text-2xl font-black text-white group-hover:text-emerald-400 transition-colors">{{ ticker.symbol }}</h2>
                                <p class="text-sm text-slate-400 truncate w-32">{{ ticker.company_name }}</p>
                            </div>
                            <div class="bg-slate-800/50 px-2 py-1 rounded-md text-xs font-medium text-slate-300">
                                {{ ticker.sector || 'Umum' }}
                            </div>
                        </div>

                        <div class="flex items-end justify-between mt-6">
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Harga Terakhir</p>
                                <p class="text-lg font-bold text-emerald-400">Rp {{ Number(ticker.current_price || 0).toLocaleString('id-ID') }}</p>
                            </div>
                            <div class="flex items-center text-emerald-500 bg-emerald-500/10 px-2 py-1 rounded">
                                <!-- Dummy indicator for MVP -->
                                <TrendingUp class="w-4 h-4 mr-1" />
                                <span class="text-xs font-bold">+0.0%</span>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-if="tickers.data.length === 0" class="text-center py-20 text-slate-500">
                    <p class="text-xl">Tidak ada emiten ditemukan.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
