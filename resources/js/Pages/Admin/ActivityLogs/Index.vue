<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Activity, ShieldAlert } from '@lucide/vue';

const props = defineProps({
    logs: Object,
});
</script>

<template>
    <Head title="Activity Logs" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-100 flex items-center gap-2">
                        <ShieldAlert class="w-6 h-6 text-emerald-500" />
                        Activity Logs
                    </h1>
                    <p class="text-sm text-slate-400 mt-1">Audit log untuk memantau aktivitas user (termasuk login via Passmaster).</p>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="bg-[#121614] rounded-2xl border border-emerald-950/20 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-emerald-950/20 bg-[#090b0a]/50">
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider">ID</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider">User</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider">Action</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider">Description</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider">IP / Browser</th>
                                <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-950/20">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-emerald-950/10 transition-colors">
                                <td class="py-4 px-6 text-sm text-slate-300">#{{ log.id }}</td>
                                <td class="py-4 px-6 text-sm text-emerald-400">
                                    {{ log.user ? log.user.name : 'Unknown User' }}
                                    <div class="text-xs text-slate-500">{{ log.user ? log.user.email : '' }}</div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-900/30 text-emerald-400 border border-emerald-800/50">
                                        {{ log.action }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-sm text-slate-300">{{ log.description || '-' }}</td>
                                <td class="py-4 px-6 text-xs text-slate-400">
                                    <div class="font-mono text-emerald-500">{{ log.ip_address }}</div>
                                    <div class="truncate max-w-xs" :title="log.user_agent">{{ log.user_agent }}</div>
                                </td>
                                <td class="py-4 px-6 text-sm text-slate-400">{{ new Date(log.created_at).toLocaleString('id-ID') }}</td>
                            </tr>
                            <tr v-if="logs.data.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-500 text-sm">
                                    Belum ada activity log.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Placeholder -->
                <div v-if="logs.data.length > 0" class="p-4 border-t border-emerald-950/20 flex justify-between items-center text-sm text-slate-400">
                    <div>Showing {{ logs.from }} to {{ logs.to }} of {{ logs.total }} entries</div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
