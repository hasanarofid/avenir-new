<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, BrainCircuit, FileText, Search, ChevronRight } from '@lucide/vue';

defineProps({
    projects: Object,
});

const getStatusColor = (status) => {
    const colors = {
        'draft': 'bg-slate-500/20 text-slate-400 border-slate-500/30',
        'generating': 'bg-amber-500/20 text-amber-400 border-amber-500/30 animate-pulse',
        'review': 'bg-indigo-500/20 text-indigo-400 border-indigo-500/30',
        'published': 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
    };
    return colors[status] || colors['draft'];
};
</script>

<template>
    <Head title="AI Research Generator" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-100 flex items-center gap-3">
                        <BrainCircuit class="w-8 h-8 text-emerald-500" />
                        AI Research Generator
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">Buat draft riset otomatis menggunakan OpenRouter AI.</p>
                </div>
                <Link 
                    :href="route('admin.research-generator.create')" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-emerald-900/20"
                >
                    <Plus class="w-4 h-4" />
                    Buat Project Baru
                </Link>
            </div>

            <!-- List -->
            <div class="bg-[#121614] rounded-2xl border border-emerald-950/20 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#090b0a]/50 border-b border-emerald-950/20">
                                <th class="p-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Ticker</th>
                                <th class="p-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Judul Project</th>
                                <th class="p-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                                <th class="p-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Tanggal</th>
                                <th class="p-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-950/10">
                            <tr v-for="project in projects.data" :key="project.id" class="hover:bg-emerald-900/10 transition-colors group">
                                <td class="p-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                        {{ project.ticker }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <p class="text-sm font-semibold text-slate-200">{{ project.title }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5 truncate max-w-xs">{{ project.prompt || 'Tidak ada instruksi khusus' }}</p>
                                </td>
                                <td class="p-4">
                                    <span :class="['inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border', getStatusColor(project.status)]">
                                        {{ project.status }}
                                    </span>
                                </td>
                                <td class="p-4 text-xs text-slate-400">
                                    {{ new Date(project.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                </td>
                                <td class="p-4 text-right">
                                    <Link 
                                        :href="route('admin.research-generator.show', project.id)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-400 hover:text-emerald-300 bg-emerald-500/10 hover:bg-emerald-500/20 rounded-lg transition-colors"
                                    >
                                        Buka Workspace
                                        <ChevronRight class="w-3.5 h-3.5" />
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!projects.data.length">
                                <td colspan="5" class="p-8 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <BrainCircuit class="w-12 h-12 mb-3 text-slate-700" />
                                        <p class="text-sm">Belum ada project riset AI.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
