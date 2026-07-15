<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Upload, Trash2 } from '@lucide/vue';
import Swal from 'sweetalert2';
import { ref } from 'vue';

const props = defineProps({
    recentSnapshots: Array,
});

const form = useForm({
    date_current: new Date().toISOString().substr(0, 10),
    file_daily_5pct: null,
    file_monthly_type: null,
    file_monthly_classification: null,
    file_monthly_1pct: null,
});

const isDraggingDaily = ref(false);
const isDraggingType = ref(false);
const isDraggingClass = ref(false);
const isDragging1Pct = ref(false);

const handleDrop = (e, field) => {
    isDraggingDaily.value = false;
    isDraggingType.value = false;
    isDraggingClass.value = false;
    isDragging1Pct.value = false;
    
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
        form[field] = e.dataTransfer.files[0];
    }
};

const handleFileSelect = (e, field) => {
    if (e.target.files && e.target.files[0]) {
        form[field] = e.target.files[0];
    }
};

const submit = () => {
    form.post(route('admin.desk-brief.ownership.upload'), {
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.flash && page.props.flash.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: page.props.flash.success,
                    background: '#0b1210',
                    color: '#fff',
                    confirmButtonColor: '#1ea95b'
                });
            }
            form.reset('file_daily_5pct', 'file_monthly_type', 'file_monthly_classification', 'file_monthly_1pct');
        },
        onError: (errors) => {
            Swal.fire({
                icon: 'error',
                title: 'Upload Gagal!',
                text: errors.message || 'Pastikan semua kolom terisi dengan benar.',
                background: '#0b1210',
                color: '#fff',
                confirmButtonColor: '#dc2626'
            });
        }
    });
};

const deleteSnapshot = (id) => {
    Swal.fire({
        title: 'Hapus Snapshot?',
        text: "Semua data kepemilikan dan relasi (edges) yang terikat dengan snapshot ini akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#2c3c34',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        background: '#0b1210',
        color: '#fff',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('admin.desk-brief.ownership.destroy', id), {
                preserveScroll: true,
            });
        }
    });
};
</script>

<template>
    <Head title="Ownership Intelligence Admin" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white leading-tight" style="color: #6dff9d">
                    Ownership Intelligence (Admin)
                </h2>
            </div>
        </template>

        <div class="admin-theme max-w-5xl mx-auto py-6">
            
            <div class="readCard intel">
                <h4>Kenapa perlu input manual?</h4>
                <p>Data KSEI hanya mencatat pemegang saham langsung emiten tercatat. Rantai kepemilikan di balik entitas <b>offshore atau holdco privat yang tidak listed</b> (mis. Mach Energy pemegang BUMI) tidak tersedia di CSV — informasi itu ada di prospektus, laporan tahunan, RUPS, atau investigasi pihak ketiga.</p>
                <p style="margin-top:8px"><b>Dua jenis input:</b> (1) <b>Data harian KSEI ≥5%</b> — unggah file Excel tiap hari untuk memantau pergerakan pemegang saham per emiten; (2) <b>Override UBO/grup manual</b> — untuk melengkapi pengendali akhir yang tak terbaca dari Excel.</p>
                <div class="flex gap-2 mt-4">
                    <a href="#" class="btn">Lihat/Edit Manual UBO</a>
                </div>
            </div>
            
            <form @submit.prevent="submit">
                <div class="adminForm">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3>Data Pergerakan Harian (KSEI ≥5%)</h3>
                            <p class="adminHint">Unggah file Excel "Kepemilikan Efek ≥5%" (Balance_SID) dari KSEI setiap hari. Sistem membaca perbandingan dua tanggal & kolom perubahan.</p>
                        </div>
                        <div>
                            <input type="date" v-model="form.date_current" class="bg-[#0a100f] border border-[#26352f] rounded-lg text-white px-3 py-1.5 text-sm focus:border-[#42e579] focus:ring-1 focus:ring-[#42e579]" required title="Tanggal Snapshot">
                        </div>
                    </div>
                    
                    <label for="dailyFile" 
                           class="admUpload block" 
                           :class="{ drag: isDraggingDaily }"
                           @dragover.prevent="isDraggingDaily = true"
                           @dragleave.prevent="isDraggingDaily = false"
                           @drop.prevent="handleDrop($event, 'file_daily_5pct')">
                        <div class="ic">▤</div>
                        <div class="tx">
                            <span v-if="form.file_daily_5pct" class="text-[#6dff9d] font-bold">{{ form.file_daily_5pct.name }}</span>
                            <span v-else><b>Klik untuk upload</b> atau tarik file Excel KSEI Harian ke sini — .xlsx</span>
                        </div>
                        <input type="file" id="dailyFile" @change="handleFileSelect($event, 'file_daily_5pct')" accept=".xlsx,.xls" class="hidden" required/>
                    </label>

                    <!-- Snapshot List -->
                    <div class="mt-8 pt-4 border-t border-[#26352f]">
                        <div class="text-xs font-bold text-[#6b7d74] mb-2 uppercase tracking-wider">Snapshot Tersimpan ({{ recentSnapshots.length }})</div>
                        <div class="snapRow" v-for="snap in recentSnapshots" :key="snap.id">
                            <div>
                                <div class="snapTitle">{{ snap.period_date }} <span class="badge">KSEI ≥5%</span></div>
                                <div class="snapSub">Diunggah: {{ new Date(snap.created_at).toLocaleString('id-ID') }}</div>
                            </div>
                            <div>
                                <button type="button" @click="deleteSnapshot(snap.id)" class="miniBtn">Hapus</button>
                            </div>
                        </div>
                        <div v-if="!recentSnapshots.length" class="text-sm text-[#6b7d74] py-4 italic">
                            Belum ada snapshot tersimpan.
                        </div>
                    </div>
                </div>

                <div class="adminForm">
                    <h3>Data Bulanan KSEI</h3>
                    <p class="adminHint">Unggah 3 jenis file Excel bulanan dari KSEI (Komposisi 1%, Klasifikasi, Tipe Domestik/Asing) secara bersamaan untuk melengkapi data bulanan.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <label for="monthly1Pct" 
                               class="admUpload block !p-4" 
                               :class="{ drag: isDragging1Pct }"
                               @dragover.prevent="isDragging1Pct = true"
                               @dragleave.prevent="isDragging1Pct = false"
                               @drop.prevent="handleDrop($event, 'file_monthly_1pct')">
                            <div class="ic text-xl mb-1">▦</div>
                            <div class="tx text-[11px]">
                                <span v-if="form.file_monthly_1pct" class="text-[#6dff9d] font-bold block truncate" :title="form.file_monthly_1pct.name">{{ form.file_monthly_1pct.name }}</span>
                                <span v-else><b>Satu Persen</b> (.xlsx)</span>
                            </div>
                            <input type="file" id="monthly1Pct" @change="handleFileSelect($event, 'file_monthly_1pct')" accept=".xlsx,.xls" class="hidden" required/>
                        </label>
                        
                        <label for="monthlyClass" 
                               class="admUpload block !p-4" 
                               :class="{ drag: isDraggingClass }"
                               @dragover.prevent="isDraggingClass = true"
                               @dragleave.prevent="isDraggingClass = false"
                               @drop.prevent="handleDrop($event, 'file_monthly_classification')">
                            <div class="ic text-xl mb-1">▦</div>
                            <div class="tx text-[11px]">
                                <span v-if="form.file_monthly_classification" class="text-[#6dff9d] font-bold block truncate" :title="form.file_monthly_classification.name">{{ form.file_monthly_classification.name }}</span>
                                <span v-else><b>Klasifikasi</b> (.xlsx)</span>
                            </div>
                            <input type="file" id="monthlyClass" @change="handleFileSelect($event, 'file_monthly_classification')" accept=".xlsx,.xls" class="hidden" required/>
                        </label>
                        
                        <label for="monthlyType" 
                               class="admUpload block !p-4" 
                               :class="{ drag: isDraggingType }"
                               @dragover.prevent="isDraggingType = true"
                               @dragleave.prevent="isDraggingType = false"
                               @drop.prevent="handleDrop($event, 'file_monthly_type')">
                            <div class="ic text-xl mb-1">▦</div>
                            <div class="tx text-[11px]">
                                <span v-if="form.file_monthly_type" class="text-[#6dff9d] font-bold block truncate" :title="form.file_monthly_type.name">{{ form.file_monthly_type.name }}</span>
                                <span v-else><b>Tipe Domestik</b> (.xlsx)</span>
                            </div>
                            <input type="file" id="monthlyType" @change="handleFileSelect($event, 'file_monthly_type')" accept=".xlsx,.xls" class="hidden" required/>
                        </label>
                    </div>
                    
                    <div class="flex justify-end mt-6 pt-6 border-t border-[#26352f]">
                        <button type="submit" :disabled="form.processing" class="btn primary flex items-center gap-2">
                            <Upload v-if="!form.processing" class="w-4 h-4" />
                            <span v-else class="w-4 h-4 rounded-full border-2 border-white/20 border-t-[#001b0a] animate-spin"></span>
                            {{ form.processing ? 'Memproses di Background...' : 'Proses Semua File' }}
                        </button>
                    </div>
                </div>
            </form>
            
        </div>
    </AdminLayout>
</template>

<style scoped>
.admin-theme {
    --bg: #1A1A1A;
    --surface: #222222;
    --line: #374151; /* gray-700 */
    --text: #ffffff;
    --text2: #9ca3af; /* gray-400 */
    --green: #10b981; /* emerald-500 */
    --green2: #34d399; /* emerald-400 */
    --greenSoft: rgba(16, 185, 129, 0.1);
    --muted: #6b7280; /* gray-500 */
    color: var(--text);
}
.btn {
    background: #111111;
    border: 1px solid var(--line);
    color: var(--text2);
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
}
.btn:hover { background: #1f2937; border-color: var(--green2); color: #fff; }
.btn.primary {
    background: var(--green);
    color: #fff;
    border: 0;
    font-weight: bold;
}
.btn.primary:hover { filter: brightness(1.1); }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }

.readCard {
    border: 1px solid var(--line);
    background: var(--surface);
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 24px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
.readCard h4 { margin: 0 0 8px; font-size: 15px; font-weight: 700; color: #fff; }
.readCard p { margin: 0; color: var(--text2); font-size: 13px; line-height: 1.6; }
.readCard.intel { border-left: 4px solid var(--green); }

.adminForm {
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 24px;
    background: var(--surface);
    margin-bottom: 24px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
.adminForm h3 { margin: 0 0 6px; font-size: 16px; font-weight: 700; color: #fff; }
.adminHint { font-size: 13px; color: var(--muted); margin: 0 0 16px; line-height: 1.5; }

.admUpload {
    border: 1.5px dashed var(--line);
    border-radius: 12px;
    padding: 24px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
    background: rgba(17, 24, 39, 0.5); /* dark grayish */
}
.admUpload:hover { border-color: var(--green2); background: rgba(17, 24, 39, 0.8); }
.admUpload.drag { border-color: var(--green); background: var(--greenSoft); }
.admUpload .ic { font-size: 28px; color: var(--muted); margin-bottom: 8px; }
.admUpload .tx { font-size: 13px; color: var(--text2); }
.admUpload .tx b { color: var(--green2); }

.snapRow {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid var(--line);
}
.snapRow:last-child { border-bottom: none; }
.snapTitle { font-size: 14px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px; }
.badge {
    background: var(--greenSoft);
    color: var(--green2);
    border: 1px solid rgba(16, 185, 129, 0.2);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 800;
}
.snapSub { font-size: 12px; color: var(--muted); margin-top: 4px; }

.miniBtn {
    background: transparent;
    border: 1px solid var(--line);
    color: var(--text2);
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
    transition: 0.2s;
}
.miniBtn:hover { border-color: #ef4444; color: #ef4444; background: rgba(239, 68, 68, 0.1); }
</style>
