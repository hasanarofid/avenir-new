<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Newspaper, ChevronRight, Info } from 'lucide-vue-next';

const props = defineProps({
    kiBriefs: Object
});

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
    <Head title="KI Brief - Keterbukaan Informasi AI Summary" />
    <AppLayout>
        <div class="min-h-screen bg-[#090b0a] pt-24 pb-12">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                    <div>
                        <h1 class="text-3xl font-black text-white tracking-tight mb-2">KI Brief</h1>
                        <p class="text-slate-400">Ringkasan AI untuk Keterbukaan Informasi emiten IHSG secara real-time.</p>
                    </div>
                </div>

                <div v-if="kiBriefs.data.length > 0" class="space-y-6">
                    <div 
                        v-for="brief in kiBriefs.data" 
                        :key="brief.id" 
                        class="bg-[#121614] border border-emerald-950/20 rounded-2xl p-6 hover:border-emerald-500/30 transition-all group"
                    >
                        <div class="flex flex-col md:flex-row gap-6">
                            <!-- Ticker Info -->
                            <div class="md:w-32 flex-shrink-0">
                                <Link 
                                    :href="route('emiten.show', brief.disclosure.ticker.symbol)"
                                    class="inline-block px-4 py-2 bg-emerald-900/20 text-emerald-400 border border-emerald-800/40 rounded-xl text-lg font-black hover:bg-emerald-900/40 transition-colors"
                                >
                                    {{ brief.disclosure.ticker.symbol }}
                                </Link>
                                <div class="mt-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                    {{ brief.disclosure.category || 'Disclosure' }}
                                </div>
                                <div class="text-xs text-slate-600 mt-1">
                                    {{ brief.disclosure.date }}
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1">
                                <h2 class="text-xl font-bold text-white mb-3 group-hover:text-emerald-400 transition-colors">
                                    {{ brief.disclosure.title }}
                                </h2>
                                
                                <div class="bg-emerald-900/10 border border-emerald-500/10 rounded-xl p-5 mb-4">
                                    <div class="flex items-center gap-2 text-emerald-500 font-bold text-xs uppercase tracking-widest mb-3">
                                        <Info class="w-3.5 h-3.5" />
                                        AI Summary (KI Brief)
                                    </div>
                                    <p class="text-slate-300 text-sm leading-relaxed mb-4">
                                        {{ brief.summary }}
                                    </p>
                                    
                                    <div v-if="brief.key_numbers && brief.key_numbers.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                                        <div 
                                            v-for="(num, idx) in brief.key_numbers" 
                                            :key="idx"
                                            class="flex items-center gap-2 text-xs text-slate-400 bg-white/5 px-3 py-2 rounded-lg"
                                        >
                                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                            {{ num }}
                                        </div>
                                    </div>

                                    <div v-if="brief.impact" class="text-xs text-emerald-400/80 italic border-t border-emerald-500/10 pt-3">
                                        <span class="font-bold uppercase not-italic mr-2">Impact:</span>
                                        {{ brief.impact }}
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <a 
                                        v-if="brief.disclosure.source_url" 
                                        :href="brief.disclosure.source_url" 
                                        target="_blank"
                                        class="text-xs text-slate-500 hover:text-emerald-400 transition-colors flex items-center gap-1"
                                    >
                                        Sumber: IDX/Emiten <ChevronRight class="w-3 h-3" />
                                    </a>
                                    <Link 
                                        :href="route('emiten.show', brief.disclosure.ticker.symbol)"
                                        class="text-xs font-bold text-emerald-500 hover:underline"
                                    >
                                        Lihat Analisis Lengkap &rarr;
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-[#121614] border border-emerald-950/20 rounded-3xl p-20 text-center">
                    <div class="w-16 h-16 bg-slate-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <Newspaper class="w-8 h-8 text-slate-600" />
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Belum ada KI Brief</h3>
                    <p class="text-slate-500">Keterbukaan informasi terbaru akan muncul di sini setelah dianalisis oleh AI.</p>
                </div>

                <!-- Simple Pagination -->
                <div v-if="kiBriefs.links.length > 3" class="mt-12 flex justify-center gap-2">
                    <Link 
                        v-for="(link, idx) in kiBriefs.links" 
                        :key="idx"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
                        :class="[
                            link.active 
                                ? 'bg-emerald-600 text-white' 
                                : 'bg-[#121614] text-slate-400 hover:bg-[#1a201d] hover:text-white border border-emerald-950/20'
                        ]"
                    />
                </div>

            </div>
        </div>
    </AppLayout>
</template>
