<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Activity, Search, RefreshCcw, Eye, X } from 'lucide-vue-next';

const props = defineProps({
    logs: Object,
});

const selectedLog = ref(null);
const isModalOpen = ref(false);

const openLog = (log) => {
    selectedLog.value = log;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedLog.value = null;
};
</script>

<template>
    <Head title="AI Logs (Audit)" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-100 flex items-center gap-2">
                        <Activity class="w-6 h-6 text-emerald-500" />
                        AI Logs (Avenir Guard Base)
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">Audit log pemakaian AI untuk memitigasi halusinasi dan melacak output model.</p>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="bg-[#121614] rounded-2xl border border-emerald-950/20 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-emerald-950/20 bg-[#090b0a]/50">
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider">ID</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider">Feature</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider">Model</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider">Input Hash (Truncated)</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider">Time</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-950/20">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-emerald-950/10 transition-colors">
                                <td class="py-4 px-6 text-sm text-slate-300">#{{ log.id }}</td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-900/30 text-emerald-400 border border-emerald-800/50">
                                        {{ log.feature }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-sm text-slate-300">{{ log.model }}</td>
                                <td class="py-4 px-6 text-sm text-slate-500 font-mono">{{ log.input_hash ? log.input_hash.substring(0, 16) + '...' : '-' }}</td>
                                <td class="py-4 px-6 text-sm text-slate-400">{{ new Date(log.created_at).toLocaleString('id-ID') }}</td>
                                <td class="py-4 px-6 text-right">
                                    <button 
                                        @click="openLog(log)"
                                        class="p-2 text-slate-400 hover:text-emerald-400 hover:bg-emerald-950/30 rounded-lg transition-colors"
                                    >
                                        <Eye class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="logs.data.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-500 text-sm">
                                    Belum ada log AI. Coba gunakan fitur AI Generator terlebih dahulu.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Placeholder -->
                <div v-if="logs.data.length > 0" class="p-4 border-t border-emerald-950/20 flex justify-between items-center text-sm text-slate-400">
                    <div>Showing {{ logs.from }} to {{ logs.to }} of {{ logs.total }} entries</div>
                    <!-- Assuming standard Laravel/Inertia pagination links can be added here if needed -->
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl w-full max-w-3xl max-h-[90vh] flex flex-col shadow-2xl">
                <div class="p-6 border-b border-emerald-950/20 flex justify-between items-center bg-[#090b0a]/50 rounded-t-2xl">
                    <div>
                        <h2 class="text-lg font-bold text-emerald-400 flex items-center gap-2">
                            <Activity class="w-5 h-5" /> Log Detail #{{ selectedLog?.id }}
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">Feature: {{ selectedLog?.feature }} | Model: {{ selectedLog?.model }}</p>
                    </div>
                    <button @click="closeModal" class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto space-y-6">
                    <div v-if="selectedLog?.sources" class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase">Sources</label>
                        <div class="p-3 bg-[#090b0a] rounded-xl border border-emerald-950/20 text-xs font-mono text-emerald-300/70 overflow-x-auto">
                            <pre>{{ JSON.stringify(selectedLog.sources, null, 2) }}</pre>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase">Input Hash</label>
                        <div class="p-3 bg-[#090b0a] rounded-xl border border-emerald-950/20 text-xs font-mono text-slate-300">
                            {{ selectedLog?.input_hash }}
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase">Raw Output (JSON / Text)</label>
                        <div class="p-4 bg-[#090b0a] rounded-xl border border-emerald-950/40 text-sm font-mono text-slate-300 whitespace-pre-wrap overflow-x-auto">
                            {{ selectedLog?.output }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
