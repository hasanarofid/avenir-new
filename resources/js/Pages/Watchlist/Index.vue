<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Bookmark, TrendingUp } from 'lucide-vue-next';

defineProps({
    watchlists: Array
});
</script>

<template>
    <Head title="Watchlist Anda" />
    <AppLayout>
        <div class="min-h-screen bg-[#090b0a] pt-24 pb-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="flex items-center gap-4 mb-8 border-b border-emerald-950/30 pb-6">
                    <div class="w-12 h-12 rounded-xl bg-emerald-900/30 flex items-center justify-center">
                        <Bookmark class="w-6 h-6 text-emerald-500" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white tracking-tight">Watchlist Anda</h1>
                        <p class="text-slate-400 mt-1">Pantau emiten favorit Anda dalam satu tempat.</p>
                    </div>
                </div>

                <div v-if="watchlists.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <Link 
                        v-for="item in watchlists" 
                        :key="item.id" 
                        :href="route('emiten.show', item.ticker.symbol)"
                        class="bg-[#121614] border border-emerald-950/20 hover:border-emerald-500/50 rounded-2xl p-6 transition-all group relative overflow-hidden"
                    >
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full blur-3xl -mr-10 -mt-10 group-hover:bg-emerald-500/10 transition-colors"></div>
                        
                        <div class="relative">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h2 class="text-2xl font-black text-white group-hover:text-emerald-400 transition-colors">{{ item.ticker.symbol }}</h2>
                                    <p class="text-sm text-slate-400 truncate w-32">{{ item.ticker.company_name }}</p>
                                </div>
                                <div class="bg-slate-800/50 px-2 py-1 rounded-md text-xs font-medium text-slate-300">
                                    {{ item.ticker.sector || 'Umum' }}
                                </div>
                            </div>

                            <div class="flex items-end justify-between mt-6">
                                <div>
                                    <p class="text-xs text-slate-500 mb-1">Harga Terakhir</p>
                                    <p class="text-lg font-bold text-emerald-400">Rp {{ Number(item.ticker.current_price || 0).toLocaleString('id-ID') }}</p>
                                </div>
                                <div class="flex items-center text-emerald-500 bg-emerald-500/10 px-2 py-1 rounded">
                                    <TrendingUp class="w-4 h-4 mr-1" />
                                    <span class="text-xs font-bold">+0.0%</span>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-else class="text-center py-20 bg-[#121614] border border-emerald-950/20 rounded-3xl">
                    <div class="w-20 h-20 bg-emerald-900/20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <Bookmark class="w-10 h-10 text-emerald-500/50" />
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-2">Watchlist Kosong</h2>
                    <p class="text-slate-400 max-w-md mx-auto mb-8">Anda belum menambahkan emiten ke dalam Watchlist. Cari emiten potensial di Emiten Hub dan tambahkan sekarang.</p>
                    <Link :href="route('emiten.index')" class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-xl transition-colors">
                        Eksplorasi Emiten
                    </Link>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
