<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Radio, ChevronRight, FileText, Info } from 'lucide-vue-next';

const props = defineProps({
    disclosures: Object
});
</script>

<template>
    <Head title="Disclosure Radar - Monitoring Keterbukaan Informasi IHSG" />
    <AppLayout>
        <div class="min-h-screen bg-[#090b0a] pt-24 pb-12">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                    <div>
                        <div class="flex items-center gap-2 text-emerald-500 mb-2">
                            <Radio class="w-5 h-5 animate-pulse" />
                            <span class="text-xs font-black uppercase tracking-widest">Live Monitoring</span>
                        </div>
                        <h1 class="text-3xl font-black text-white tracking-tight mb-2">Disclosure Radar</h1>
                        <p class="text-slate-400">Pantau aksi korporasi dan pengumuman material emiten secara real-time.</p>
                    </div>
                </div>

                <div v-if="disclosures.data.length > 0" class="space-y-4">
                    <div 
                        v-for="disclosure in disclosures.data" 
                        :key="disclosure.id" 
                        class="bg-[#121614] border border-emerald-950/20 rounded-2xl overflow-hidden hover:border-emerald-500/30 transition-all"
                    >
                        <div class="p-6">
                            <div class="flex flex-col md:flex-row gap-6">
                                <!-- Ticker & Date -->
                                <div class="md:w-32 flex-shrink-0">
                                    <Link 
                                        :href="route('emiten.show', disclosure.ticker.symbol)"
                                        class="inline-block px-4 py-2 bg-emerald-900/20 text-emerald-400 border border-emerald-800/40 rounded-xl text-lg font-black hover:bg-emerald-900/40 transition-colors"
                                    >
                                        {{ disclosure.ticker.symbol }}
                                    </Link>
                                    <div class="mt-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                        {{ disclosure.date }}
                                    </div>
                                </div>

                                <!-- Main Content -->
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-0.5 bg-slate-800 text-slate-400 rounded text-[10px] font-bold uppercase">
                                            {{ disclosure.category || 'General' }}
                                        </span>
                                    </div>
                                    <h2 class="text-lg font-bold text-white mb-3">
                                        {{ disclosure.title }}
                                    </h2>
                                    
                                    <!-- AI Summary Preview if available -->
                                    <div v-if="disclosure.ki_brief" class="bg-emerald-900/10 border border-emerald-500/10 rounded-xl p-4 mb-4">
                                        <div class="flex items-center gap-2 text-emerald-500 font-bold text-[10px] uppercase tracking-widest mb-2">
                                            <Info class="w-3 h-3" />
                                            AI Brief Available
                                        </div>
                                        <p class="text-slate-400 text-xs leading-relaxed line-clamp-2">
                                            {{ disclosure.ki_brief.summary }}
                                        </p>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <div class="flex gap-4">
                                            <a 
                                                v-if="disclosure.source_url" 
                                                :href="disclosure.source_url" 
                                                target="_blank"
                                                class="text-xs text-slate-500 hover:text-emerald-400 transition-colors flex items-center gap-1"
                                            >
                                                <FileText class="w-3.5 h-3.5" /> Sumber Asli
                                            </a>
                                        </div>
                                        <Link 
                                            :href="route('emiten.show', disclosure.ticker.symbol)"
                                            class="text-xs font-bold text-emerald-500 flex items-center gap-1 hover:underline"
                                        >
                                            Analisis & Brief <ChevronRight class="w-3.5 h-3.5" />
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-[#121614] border border-emerald-950/20 rounded-3xl p-20 text-center">
                    <h3 class="text-xl font-bold text-white mb-2">Radar Kosong</h3>
                    <p class="text-slate-500">Belum ada keterbukaan informasi yang tercatat hari ini.</p>
                </div>

                <!-- Pagination -->
                <div v-if="disclosures.links.length > 3" class="mt-12 flex justify-center gap-2">
                    <Link 
                        v-for="(link, idx) in disclosures.links" 
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
