<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowLeft, TrendingUp, Building2, Newspaper, BookmarkPlus, BookmarkMinus } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    ticker: Object,
    articles: Array,
    disclosures: Array,
    realtimePrice: [Number, String],
    isWatchlisted: Boolean
});

const page = usePage();
const form = useForm({});

const toggleWatchlist = () => {
    if (!page.props.auth.user) {
        Swal.fire({
            icon: 'info',
            title: 'Perlu Login',
            text: 'Silakan login terlebih dahulu untuk menggunakan fitur Watchlist.',
            background: '#121614',
            color: '#f8fafc',
            confirmButtonColor: '#059669'
        });
        return;
    }

    form.post(route('watchlist.toggle', props.ticker.id), {
        preserveScroll: true,
        onSuccess: () => {
            const flash = usePage().props.flash;
            if (flash.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: flash.success,
                    background: '#121614',
                    color: '#f8fafc',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        }
    });
};
</script>

<template>
    <Head :title="`${ticker.symbol} - ${ticker.company_name}`" />
    <AppLayout>
        <div class="min-h-screen bg-[#090b0a] pt-24 pb-12">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Back Link -->
                <Link :href="route('emiten.index')" class="inline-flex items-center text-sm font-medium text-emerald-500 hover:text-emerald-400 mb-8 transition-colors">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Kembali ke Emiten Hub
                </Link>

                <!-- Header / Profile -->
                <div class="bg-[#121614] border border-emerald-950/30 rounded-3xl p-8 md:p-10 mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
                    <div>
                        <div class="flex items-center gap-4 mb-3">
                            <h1 class="text-4xl md:text-5xl font-black text-white tracking-tight">{{ ticker.symbol }}</h1>
                            <span class="px-3 py-1 bg-emerald-900/40 text-emerald-400 border border-emerald-800/50 rounded-full text-xs font-bold uppercase">
                                {{ ticker.sector || 'Sektor Umum' }}
                            </span>
                        </div>
                        <p class="text-xl text-slate-400 font-medium">{{ ticker.company_name }}</p>
                    </div>

                    <div class="flex flex-col md:items-end w-full md:w-auto">
                        <p class="text-sm text-slate-500 mb-1">Harga Terakhir (Dummy/MVP)</p>
                        <div class="flex items-baseline gap-3 mb-4">
                            <h2 class="text-4xl font-bold text-emerald-400">Rp {{ Number(realtimePrice).toLocaleString('id-ID') }}</h2>
                            <span class="flex items-center text-emerald-500 text-sm font-bold bg-emerald-500/10 px-2 py-1 rounded">
                                <TrendingUp class="w-4 h-4 mr-1" /> +0.0%
                            </span>
                        </div>
                        <button 
                            @click="toggleWatchlist"
                            :disabled="form.processing"
                            :class="[
                                'w-full md:w-auto flex justify-center items-center gap-2 px-6 py-3 font-semibold rounded-xl transition-colors',
                                isWatchlisted 
                                    ? 'bg-red-600/10 text-red-500 hover:bg-red-600/20 border border-red-500/50' 
                                    : 'bg-emerald-600 hover:bg-emerald-500 text-white'
                            ]"
                        >
                            <BookmarkMinus v-if="isWatchlisted" class="w-5 h-5" />
                            <BookmarkPlus v-else class="w-5 h-5" />
                            {{ isWatchlisted ? 'Hapus dari Watchlist' : 'Tambah ke Watchlist' }}
                        </button>
                    </div>
                </div>

                <!-- Two Column Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Left Col: Profile & Info -->
                    <div class="lg:col-span-1 space-y-8">
                        <div class="bg-[#121614] border border-emerald-950/20 rounded-2xl p-6">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2 mb-4">
                                <Building2 class="w-5 h-5 text-emerald-500" />
                                Profil Perusahaan
                            </h3>
                            <p class="text-slate-400 text-sm leading-relaxed">
                                {{ ticker.description || 'Belum ada deskripsi profil untuk emiten ini.' }}
                            </p>
                            
                            <div class="mt-6 pt-6 border-t border-emerald-950/20 space-y-4">
                                <div>
                                    <p class="text-xs text-slate-500 mb-1">Target Harga (Konsensus)</p>
                                    <p class="text-white font-medium">Rp {{ Number(ticker.target_price || 0).toLocaleString('id-ID') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 mb-1">Rekomendasi</p>
                                    <span class="inline-block px-2 py-1 bg-slate-800 text-slate-300 text-xs font-bold rounded uppercase">
                                        {{ ticker.recommendation || 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Col: Related Articles & News -->
                    <div class="lg:col-span-2 space-y-6">
                        <h3 class="text-xl font-bold text-white flex items-center gap-2 mb-6">
                            <Newspaper class="w-6 h-6 text-emerald-500" />
                            Berita & Riset Terkait
                        </h3>

                        <div v-if="articles.length > 0" class="space-y-4">
                            <Link 
                                v-for="article in articles" 
                                :key="article.id" 
                                :href="route('news.detail', article.slug)"
                                class="block bg-[#121614] border border-emerald-950/20 hover:border-emerald-500/40 rounded-2xl p-5 transition-all group"
                            >
                                <div class="flex items-start gap-4">
                                    <img 
                                        :src="article.cover_image" 
                                        :alt="article.title" 
                                        class="w-24 h-24 rounded-xl object-cover"
                                    />
                                    <div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-xs font-bold text-emerald-500">{{ article.badge }}</span>
                                            <span class="text-xs text-slate-600">•</span>
                                            <span class="text-xs text-slate-500">{{ article.published_at }}</span>
                                        </div>
                                        <h4 class="text-lg font-bold text-slate-200 group-hover:text-emerald-400 transition-colors line-clamp-2 mb-2">
                                            {{ article.title }}
                                        </h4>
                                        <p class="text-sm text-slate-400 line-clamp-1">{{ article.excerpt }}</p>
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <div v-else class="bg-[#121614] border border-emerald-950/20 rounded-2xl p-10 text-center">
                            <p class="text-slate-500">Belum ada berita atau riset terkait emiten ini.</p>
                        </div>
                        
                        <!-- KI Brief Feed -->
                        <h3 class="text-xl font-bold text-white flex items-center gap-2 mb-6 mt-10">
                            <Newspaper class="w-6 h-6 text-emerald-500" />
                            Keterbukaan Informasi
                        </h3>

                        <div v-if="disclosures && disclosures.length > 0" class="space-y-4">
                            <div 
                                v-for="disclosure in disclosures" 
                                :key="disclosure.id" 
                                class="block bg-[#121614] border border-emerald-950/20 rounded-2xl p-5 transition-all group"
                            >
                                <div class="flex items-start gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-xs font-bold text-emerald-500">{{ disclosure.category || 'KI' }}</span>
                                            <span class="text-xs text-slate-600">•</span>
                                            <span class="text-xs text-slate-500">{{ disclosure.date }}</span>
                                        </div>
                                        <h4 class="text-lg font-bold text-slate-200 mb-2">
                                            {{ disclosure.title }}
                                        </h4>
                                        <div v-if="disclosure.ki_brief" class="bg-emerald-900/20 p-4 rounded-lg mt-3">
                                            <p class="text-sm text-slate-300 font-semibold mb-1">KI Brief (AI Summary):</p>
                                            <p class="text-sm text-slate-400 mb-2">{{ disclosure.ki_brief.summary }}</p>
                                            <div v-if="disclosure.ki_brief.impact" class="text-xs text-emerald-400 mt-2">
                                                <strong>Impact:</strong> {{ disclosure.ki_brief.impact }}
                                            </div>
                                        </div>
                                        <a v-if="disclosure.source_url" :href="disclosure.source_url" target="_blank" class="text-xs text-emerald-500 hover:text-emerald-400 mt-3 inline-block">
                                            Baca sumber asli &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="bg-[#121614] border border-emerald-950/20 rounded-2xl p-10 text-center">
                            <p class="text-slate-500">Belum ada Keterbukaan Informasi untuk emiten ini.</p>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AppLayout>
</template>
