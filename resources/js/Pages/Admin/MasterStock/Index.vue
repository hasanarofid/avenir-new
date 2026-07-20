<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Upload, Trash2, Pencil, X, Search, Building2, RefreshCw } from '@lucide/vue';
import Swal from 'sweetalert2';

const props = defineProps({
    stocks: Array,
    stats: Object,
});

/* ── Import Excel Form ── */
const importForm = useForm({ file: null });
const isDragging = ref(false);

const handleDrop = (e) => {
    isDragging.value = false;
    if (e.dataTransfer.files?.[0]) importForm.file = e.dataTransfer.files[0];
};
const handleFileSelect = (e) => {
    if (e.target.files?.[0]) importForm.file = e.target.files[0];
};

const submitImport = () => {
    if (!importForm.file) {
        Swal.fire({ icon: 'warning', title: 'Pilih file dulu', background: '#0b1210', color: '#fff', confirmButtonColor: '#1ea95b' });
        return;
    }
    importForm.post(route('admin.master-stock.import'), {
        preserveScroll: true,
        onSuccess: (page) => {
            Swal.fire({ icon: 'success', title: 'Import Berhasil!', text: page.props.flash?.success, background: '#0b1210', color: '#fff', confirmButtonColor: '#1ea95b' });
            importForm.reset('file');
        },
        onError: (errors) => {
            Swal.fire({ icon: 'error', title: 'Import Gagal', text: errors.message || 'Periksa format file.', background: '#0b1210', color: '#fff', confirmButtonColor: '#dc2626' });
        },
    });
};

/* ── Sync Logos ── */
const syncingLogos = ref(false);
const syncLogos = () => {
    Swal.fire({
        title: 'Sync Logo dari IDX?',
        text: 'Akan mengisi kolom logo_url untuk semua emiten yang belum memiliki logo.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1ea95b',
        cancelButtonColor: '#374151',
        confirmButtonText: 'Ya, Sync!',
        cancelButtonText: 'Batal',
        background: '#0b1210',
        color: '#fff',
    }).then((result) => {
        if (result.isConfirmed) {
            syncingLogos.value = true;
            router.post(route('admin.master-stock.sync-logos'), {}, {
                preserveScroll: true,
                onSuccess: (page) => {
                    syncingLogos.value = false;
                    Swal.fire({ icon: 'success', title: 'Sync Selesai!', text: page.props.flash?.success, background: '#0b1210', color: '#fff', confirmButtonColor: '#1ea95b' });
                },
                onFinish: () => { syncingLogos.value = false; },
            });
        }
    });
};

/* ── Edit Modal ── */
const editModal = ref(false);
const editForm = useForm({ code: '', name: '', sector: '', sub_industry_code: '', sub_industry: '', is_sharia: false, logo_url: '', fs_date: '', fiscal_year_end: '' });

const openEdit = (stock) => {
    editForm.code = stock.code;
    editForm.name = stock.name ?? '';
    editForm.sector = stock.sector ?? '';
    editForm.sub_industry_code = stock.sub_industry_code ?? '';
    editForm.sub_industry = stock.sub_industry ?? '';
    editForm.is_sharia = !!stock.is_sharia;
    editForm.logo_url = stock.logo_url ?? '';
    editForm.fs_date = stock.fs_date ?? '';
    editForm.fiscal_year_end = stock.fiscal_year_end ?? '';
    editModal.value = true;
};

const submitEdit = () => {
    editForm.put(route('admin.master-stock.update', editForm.code), {
        preserveScroll: true,
        onSuccess: () => {
            editModal.value = false;
            Swal.fire({ icon: 'success', title: 'Disimpan!', background: '#0b1210', color: '#fff', confirmButtonColor: '#1ea95b', timer: 1500, showConfirmButton: false });
        },
        onError: (e) => {
            Swal.fire({ icon: 'error', title: 'Gagal', text: e.message, background: '#0b1210', color: '#fff', confirmButtonColor: '#dc2626' });
        },
    });
};

/* ── Delete ── */
const deleteStock = (code) => {
    Swal.fire({
        title: `Hapus ${code}?`,
        text: 'Data emiten ini akan dihapus dari master list.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#374151',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        background: '#0b1210',
        color: '#fff',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('admin.master-stock.destroy', code), { preserveScroll: true });
        }
    });
};

/* ── Table Filters ── */
const search = ref('');
const sectorFilter = ref('');

const sectorOptions = computed(() => {
    const s = new Set(props.stocks.map(s => s.sector).filter(Boolean));
    return [...s].sort();
});

const filtered = computed(() => {
    let list = props.stocks;
    if (sectorFilter.value) list = list.filter(s => s.sector === sectorFilter.value);
    if (search.value.trim()) {
        const q = search.value.trim().toUpperCase();
        list = list.filter(s =>
            s.code?.toUpperCase().includes(q) ||
            s.name?.toUpperCase().includes(q) ||
            s.sub_industry?.toUpperCase().includes(q)
        );
    }
    return list;
});

/* ── Pagination ── */
const page = ref(1);
const perPage = 20;
const totalPages = computed(() => Math.ceil(filtered.value.length / perPage));
const paginated = computed(() => {
    const start = (page.value - 1) * perPage;
    return filtered.value.slice(start, start + perPage);
});
const goPage = (p) => { page.value = Math.max(1, Math.min(p, totalPages.value)); };
</script>

<template>
    <Head title="Master Emiten Admin" />

    <AdminLayout>
        <div class="admin-theme max-w-7xl mx-auto py-6">

            <!-- Title & Actions -->
            <div class="flex justify-between items-center mb-6 px-1">
                <h2 class="font-semibold text-xl leading-tight" style="color: #6dff9d">
                    Master Emiten
                </h2>
                <button @click="syncLogos" :disabled="syncingLogos"
                    class="btn flex items-center gap-2 text-sm">
                    <RefreshCw :class="['w-4 h-4', syncingLogos && 'animate-spin']" />
                    Sync Logo IDX
                </button>
            </div>

            <!-- Stats -->

            <div class="statsGrid">
                <div class="statCard">
                    <div class="statLabel">Total Emiten</div>
                    <div class="statNum">{{ stats.total }}</div>
                </div>
                <div class="statCard" v-for="s in stats.sectors" :key="s.sector">
                    <div class="statLabel">{{ s.sector }}</div>
                    <div class="statNum">{{ s.total }}</div>
                </div>
            </div>

            <!-- Import Section -->
            <div class="adminForm">
                <h3>Import Master Emiten</h3>
                <p class="adminHint">Upload file "Financial Data and Ratio" Excel (kolom A–H). Data akan di-upsert berdasarkan kode saham — aman untuk re-upload.</p>

                <label for="masterFile"
                    class="admUpload block mt-4"
                    :class="{ drag: isDragging }"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop">
                    <div class="ic">📊</div>
                    <div class="tx">
                        <span v-if="importForm.file" class="text-[#6dff9d] font-bold">{{ importForm.file.name }}</span>
                        <span v-else><b>Klik atau seret file Excel</b> ke sini — Financial Data and Ratio (.xlsx)</span>
                    </div>
                    <input type="file" id="masterFile" @change="handleFileSelect" accept=".xlsx,.xls" class="hidden" />
                </label>

                <div class="flex justify-end mt-4">
                    <button type="button" @click="submitImport" :disabled="importForm.processing"
                        class="btn primary flex items-center gap-2">
                        <Upload v-if="!importForm.processing" class="w-4 h-4" />
                        <span v-else class="w-4 h-4 rounded-full border-2 border-white/20 border-t-transparent animate-spin"></span>
                        {{ importForm.processing ? 'Memproses...' : 'Import Excel' }}
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="adminForm !p-0 overflow-hidden">
                <!-- Table Header / Filters -->
                <div class="flex items-center gap-3 p-4 border-b border-[var(--line)]">
                    <div class="relative flex-1 max-w-sm">
                        <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-[var(--muted)]" />
                        <input
                            v-model="search"
                            placeholder="Cari kode, nama, sub-industri..."
                            class="w-full bg-[#111] border border-[var(--line)] rounded-lg text-sm text-white pl-9 pr-3 py-2 focus:border-[#42e579] outline-none"
                            @input="page = 1"
                        />
                    </div>
                    <select v-model="sectorFilter" @change="page = 1"
                        class="bg-[#111] border border-[var(--line)] rounded-lg text-sm text-white px-3 py-2 focus:border-[#42e579] outline-none">
                        <option value="">Semua Sektor</option>
                        <option v-for="s in sectorOptions" :key="s" :value="s">{{ s }}</option>
                    </select>
                    <span class="text-xs text-[var(--muted)] ml-auto whitespace-nowrap">
                        {{ filtered.length }} dari {{ stocks.length }} emiten
                    </span>
                </div>

                <!-- Table Body -->
                <div class="overflow-x-auto">
                    <table class="admTable">
                        <thead>
                            <tr>
                                <th>Logo</th>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Sektor</th>
                                <th>Sub Industri</th>
                                <th>Kode SI</th>
                                <th>Sharia</th>
                                <th>FS Date</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="stock in paginated" :key="stock.code">
                                <td>
                                    <img
                                        v-if="stock.logo_url && !stock.imgError"
                                        :src="stock.logo_url"
                                        :alt="stock.code"
                                        class="w-8 h-8 rounded object-contain bg-white/5"
                                        @error="stock.imgError = true"
                                    />
                                    <div v-else class="w-8 h-8 rounded bg-[#26352f] flex items-center justify-center text-[10px] text-[var(--muted)] font-bold">{{ stock.code.slice(0,2) }}</div>
                                </td>
                                <td><span class="font-mono font-bold text-[#6dff9d]">{{ stock.code }}</span></td>
                                <td class="max-w-[220px] truncate" :title="stock.name">{{ stock.name }}</td>
                                <td>
                                    <span class="text-[var(--text2)] text-xs">{{ stock.sector }}</span>
                                </td>
                                <td>
                                    <span class="badge">{{ stock.sub_industry }}</span>
                                </td>
                                <td><span class="font-mono text-xs text-[var(--muted)]">{{ stock.sub_industry_code }}</span></td>
                                <td>
                                    <span :class="stock.is_sharia ? 'badge-sharia' : 'text-[var(--muted)] text-xs'">
                                        {{ stock.is_sharia ? 'Syariah' : '—' }}
                                    </span>
                                </td>
                                <td class="text-xs text-[var(--muted)]">{{ stock.fs_date }}</td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <button @click="openEdit(stock)" class="miniBtn flex items-center gap-1">
                                            <Pencil class="w-3 h-3" /> Edit
                                        </button>
                                        <button @click="deleteStock(stock.code)" class="miniBtn danger flex items-center gap-1">
                                            <Trash2 class="w-3 h-3" /> Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!paginated.length">
                                <td colspan="9" class="text-center py-10 text-[var(--muted)] italic">Tidak ada data ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="flex items-center justify-between px-4 py-3 border-t border-[var(--line)]">
                    <span class="text-xs text-[var(--muted)]">Halaman {{ page }} / {{ totalPages }}</span>
                    <div class="flex gap-1">
                        <button @click="goPage(1)" :disabled="page === 1" class="pageBtn">«</button>
                        <button @click="goPage(page - 1)" :disabled="page === 1" class="pageBtn">‹</button>
                        <button
                            v-for="p in Math.min(5, totalPages)"
                            :key="p"
                            @click="goPage(p)"
                            :class="['pageBtn', page === p && 'active']">{{ p }}</button>
                        <button @click="goPage(page + 1)" :disabled="page === totalPages" class="pageBtn">›</button>
                        <button @click="goPage(totalPages)" :disabled="page === totalPages" class="pageBtn">»</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>

    <!-- Edit Modal -->
    <Teleport to="body">
        <div v-if="editModal" class="modalOverlay" @click.self="editModal = false">
            <div class="modalBox">
                <div class="modalHead">
                    <h3>Edit Emiten — <span class="text-[#6dff9d]">{{ editForm.code }}</span></h3>
                    <button @click="editModal = false" class="modalClose"><X class="w-5 h-5"/></button>
                </div>

                <form @submit.prevent="submitEdit" class="modalBody">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="formGroup col-span-2">
                            <label>Nama Perusahaan</label>
                            <input v-model="editForm.name" type="text" class="formInput" />
                        </div>
                        <div class="formGroup">
                            <label>Sektor</label>
                            <input v-model="editForm.sector" type="text" class="formInput" />
                        </div>
                        <div class="formGroup">
                            <label>Kode Sub Industri</label>
                            <input v-model="editForm.sub_industry_code" type="text" class="formInput font-mono" maxlength="10" />
                        </div>
                        <div class="formGroup col-span-2">
                            <label>Sub Industri</label>
                            <input v-model="editForm.sub_industry" type="text" class="formInput" />
                        </div>
                        <div class="formGroup col-span-2">
                            <label>Logo URL</label>
                            <input v-model="editForm.logo_url" type="url" class="formInput font-mono text-xs" placeholder="https://..." />
                        </div>
                        <div class="formGroup">
                            <label>FS Date</label>
                            <input v-model="editForm.fs_date" type="text" class="formInput" placeholder="03/31/2026" />
                        </div>
                        <div class="formGroup">
                            <label>Fiscal Year End</label>
                            <input v-model="editForm.fiscal_year_end" type="text" class="formInput" placeholder="Dec" />
                        </div>
                        <div class="formGroup">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="editForm.is_sharia" type="checkbox" class="accent-[#42e579] w-4 h-4" />
                                <span>Saham Syariah</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-[var(--line)]">
                        <button type="button" @click="editModal = false" class="btn">Batal</button>
                        <button type="submit" :disabled="editForm.processing" class="btn primary flex items-center gap-2">
                            <span v-if="editForm.processing" class="w-4 h-4 rounded-full border-2 border-white/30 border-t-transparent animate-spin"></span>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.admin-theme {
    --bg: #0b1210;
    --surface: #111a16;
    --line: #1f2f28;
    --text: #ffffff;
    --text2: #9ca3af;
    --green: #1ea95b;
    --green2: #42e579;
    --greenSoft: rgba(66, 229, 121, 0.08);
    --muted: #4b6358;
    color: var(--text);
}

/* Stats */
.statsGrid {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 24px;
}
.statCard {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 10px;
    padding: 12px 18px;
    min-width: 110px;
}
.statLabel { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
.statNum { font-size: 22px; font-weight: 800; color: var(--green2); }

/* Card / Form */
.adminForm {
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 24px;
    background: var(--surface);
    margin-bottom: 20px;
}
.adminForm h3 { margin: 0 0 6px; font-size: 15px; font-weight: 700; color: #fff; }
.adminHint { font-size: 13px; color: var(--muted); margin: 0 0 4px; line-height: 1.5; }

/* Upload area */
.admUpload {
    border: 1.5px dashed var(--line);
    border-radius: 12px;
    padding: 24px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: rgba(0,0,0,0.3);
}
.admUpload:hover { border-color: var(--green2); }
.admUpload.drag { border-color: var(--green); background: var(--greenSoft); }
.admUpload .ic { font-size: 28px; margin-bottom: 8px; }
.admUpload .tx { font-size: 13px; color: var(--text2); }
.admUpload .tx b { color: var(--green2); }

/* Buttons */
.btn {
    background: #111a16;
    border: 1px solid var(--line);
    color: var(--text2);
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
}
.btn:hover { border-color: var(--green2); color: #fff; }
.btn.primary { background: var(--green); color: #fff; border: none; font-weight: 700; }
.btn.primary:hover { filter: brightness(1.1); }
.btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* Table */
.admTable {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.admTable thead tr {
    border-bottom: 1px solid var(--line);
    background: rgba(0,0,0,0.3);
}
.admTable th {
    padding: 10px 14px;
    text-align: left;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--muted);
    white-space: nowrap;
}
.admTable td {
    padding: 10px 14px;
    border-bottom: 1px solid var(--line);
    color: var(--text2);
    vertical-align: middle;
}
.admTable tbody tr:last-child td { border-bottom: none; }
.admTable tbody tr:hover { background: rgba(66, 229, 121, 0.03); }

/* Badges */
.badge {
    background: rgba(66, 229, 121, 0.1);
    color: var(--green2);
    border: 1px solid rgba(66, 229, 121, 0.2);
    padding: 2px 7px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}
.badge-sharia {
    background: rgba(251, 191, 36, 0.1);
    color: #fbbf24;
    border: 1px solid rgba(251, 191, 36, 0.2);
    padding: 2px 7px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
}

/* Mini buttons */
.miniBtn {
    background: transparent;
    border: 1px solid var(--line);
    color: var(--text2);
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 11px;
    cursor: pointer;
    transition: 0.2s;
    white-space: nowrap;
}
.miniBtn:hover { border-color: var(--green2); color: var(--green2); }
.miniBtn.danger:hover { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.08); }

/* Pagination */
.pageBtn {
    background: transparent;
    border: 1px solid var(--line);
    color: var(--text2);
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
    transition: 0.2s;
    min-width: 32px;
}
.pageBtn:hover { border-color: var(--green2); color: var(--green2); }
.pageBtn.active { background: var(--green); border-color: var(--green); color: #fff; font-weight: 700; }
.pageBtn:disabled { opacity: 0.3; cursor: not-allowed; }

/* Modal */
.modalOverlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.75);
    backdrop-filter: blur(4px);
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.modalBox {
    background: #111a16;
    border: 1px solid var(--line);
    border-radius: 16px;
    width: 100%;
    max-width: 560px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px rgba(0,0,0,0.5);
}
.modalHead {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 24px;
    border-bottom: 1px solid var(--line);
}
.modalHead h3 { margin: 0; font-size: 15px; font-weight: 700; color: #fff; }
.modalClose { background: none; border: none; color: var(--muted); cursor: pointer; padding: 4px; border-radius: 6px; transition: 0.2s; }
.modalClose:hover { color: #fff; background: rgba(255,255,255,0.05); }
.modalBody { padding: 20px 24px; }

.formGroup { display: flex; flex-direction: column; gap: 6px; }
.formGroup label { font-size: 12px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
.formInput {
    background: #0b1210;
    border: 1px solid var(--line);
    border-radius: 8px;
    color: #fff;
    padding: 9px 12px;
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s;
    width: 100%;
}
.formInput:focus { border-color: var(--green2); }
</style>
