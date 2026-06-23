<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { UserCheck, Edit, Trash, Check } from '@lucide/vue';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  mitra: Array
});

const pendingCount = computed(() => props.mitra.filter(m => !m.is_verified).length);
const activeCount = computed(() => props.mitra.filter(m => m.is_verified).length);

const editingId = ref(null);
const editForm = ref({
  certification: '',
  specializations: '',
  portfolio_link: '',
  bank_name: '',
  bank_account_number: '',
  bank_account_name: '',
});

function formatDate(dateString) {
  if (!dateString) return '-';
  const d = new Date(dateString);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function formatSpecializations(spec) {
  if (!spec) return '';
  if (Array.isArray(spec)) return spec.join(', ');
  try {
    const parsed = JSON.parse(spec);
    return Array.isArray(parsed) ? parsed.join(', ') : parsed;
  } catch (e) {
    return spec;
  }
}

function startEdit(m) {
  editingId.value = m.id;
  editForm.value = {
    certification: m.certification || '',
    specializations: formatSpecializations(m.specializations),
    portfolio_link: m.portfolio_link || '',
    bank_name: m.bank_name || '',
    bank_account_number: m.bank_account_number || '',
    bank_account_name: m.bank_account_name || '',
    phone_number: m.phone_number || '', // Tambahkan ini
  };
}

function cancelEdit() {
  editingId.value = null;
}

function saveEdit(id) {
  router.put(route('admin.mitra.update', id), editForm.value);
}

async function approveMitra(id) {
  const result = await Swal.fire({
    title: 'Konfirmasi Verifikasi',
    text: 'Yakin ingin verifikasi mitra ini?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#ef4444',
    confirmButtonText: 'Ya, Verifikasi',
    cancelButtonText: 'Batal',
    background: '#121614',
    color: '#cbd5e1'
  });
  if (result.isConfirmed) {
    router.post(route('admin.mitra.approve', id));
  }
}

async function deleteMitra(id) {
  const result = await Swal.fire({
    title: 'Konfirmasi Hapus',
    text: 'Yakin ingin menghapus mitra ini?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#ef4444',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal',
    background: '#121614',
    color: '#cbd5e1'
  });
  if (result.isConfirmed) {
    router.delete(route('admin.mitra.destroy', id));
  }
}
</script>

<template>
  <Head title="Kelola Mitra Analis" />

  <AdminLayout>
    <div class="space-y-6 pb-12">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-emerald-950/30 pb-4">
        <div>
          <h2 class="text-2xl font-extrabold tracking-tight text-white flex items-center gap-2">
            <UserCheck class="w-6 h-6 text-amber-500" />
            Mitra Analis
          </h2>
          <p class="text-sm text-slate-400 mt-1">Kelola permohonan dan status keanggotaan Mitra Analis Avenir.</p>
        </div>
        <div class="px-3 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-400 font-mono font-bold text-xs rounded-full">
          {{ pendingCount }} PENDING / {{ activeCount }} AKTIF
        </div>
      </div>

      <!-- List Mitra -->
      <div v-if="mitra.length === 0" class="text-center py-12 bg-[#121614] border border-emerald-950/30 rounded-2xl">
        <UserCheck class="w-12 h-12 text-emerald-900/50 mx-auto mb-3" />
        <h3 class="text-lg font-bold text-slate-300">Belum Ada Mitra</h3>
        <p class="text-sm text-slate-500 mt-1">Tidak ada pengajuan mitra untuk saat ini.</p>
      </div>

      <div v-else class="space-y-4">
        <div v-for="m in mitra" :key="m.id" class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl hover:border-emerald-700/50 transition-colors">
          <div v-if="editingId !== m.id" class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
              <div class="flex items-center gap-3 mb-1">
                <h3 class="text-lg font-bold text-white">{{ m.name || m.user_id }}</h3>
                <span v-if="m.is_verified" class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">✅ Mitra Aktif</span>
                <span v-else class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-500/20 text-amber-400 border border-amber-500/30">⏳ Pending</span>
              </div>
              <div class="text-sm text-slate-400 font-mono">{{ m.email || '-' }}</div>
            </div>
            
            <div class="flex gap-2">
              <button v-if="!m.is_verified" @click="approveMitra(m.id)" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500 hover:text-white text-xs font-semibold rounded-lg transition-all">
                <Check class="w-3 h-3" />
                Approve
              </button>
              <button @click="startEdit(m)" class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#090b0a] border border-emerald-950/50 text-slate-300 hover:text-emerald-400 hover:border-emerald-700 text-xs font-semibold rounded-lg transition-all">
                <Edit class="w-3 h-3" />
                Edit
              </button>
              <button @click="deleteMitra(m.id)" class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#090b0a] border border-rose-500/30 text-rose-400 hover:bg-rose-500 hover:text-white text-xs font-semibold rounded-lg transition-all">
                <Trash class="w-3 h-3" />
                Hapus
              </button>
            </div>
          </div>

          <div v-else class="space-y-4">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-bold text-white">Edit Mitra: {{ m.name }}</h3>
              <button @click="cancelEdit" class="text-slate-400 hover:text-white text-sm">Batal</button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="text-xs text-slate-400 font-bold uppercase tracking-widest">Sertifikasi</label>
                <input v-model="editForm.certification" type="text" class="w-full bg-[#090b0a] border border-emerald-950/30 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50">
              </div>
              <div>
                <label class="text-xs text-slate-400 font-bold uppercase tracking-widest">Spesialisasi (pisah koma)</label>
                <input v-model="editForm.specializations" type="text" class="w-full bg-[#090b0a] border border-emerald-950/30 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50">
              </div>
              <div>
                <label class="text-xs text-slate-400 font-bold uppercase tracking-widest">No HP</label>
                <input v-model="editForm.phone_number" type="text" class="w-full bg-[#090b0a] border border-emerald-950/30 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50">
              </div>
              <div>
                <label class="text-xs text-slate-400 font-bold uppercase tracking-widest">Link Portfolio</label>
                <input v-model="editForm.portfolio_link" type="url" class="w-full bg-[#090b0a] border border-emerald-950/30 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50">
              </div>
              <div>
                <label class="text-xs text-slate-400 font-bold uppercase tracking-widest">Nama Bank</label>
                <input v-model="editForm.bank_name" type="text" class="w-full bg-[#090b0a] border border-emerald-950/30 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50">
              </div>
              <div>
                <label class="text-xs text-slate-400 font-bold uppercase tracking-widest">No Rekening</label>
                <input v-model="editForm.bank_account_number" type="text" class="w-full bg-[#090b0a] border border-emerald-950/30 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50">
              </div>
              <div>
                <label class="text-xs text-slate-400 font-bold uppercase tracking-widest">Nama Pemilik Rekening</label>
                <input v-model="editForm.bank_account_name" type="text" class="w-full bg-[#090b0a] border border-emerald-950/30 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:border-emerald-500/50">
              </div>
            </div>
            
            <div class="flex justify-end gap-2">
              <button @click="cancelEdit" class="px-4 py-2 bg-[#090b0a] border border-slate-700 text-slate-300 rounded-lg text-sm font-semibold hover:bg-slate-800 transition-all">
                Batal
              </button>
              <button @click="saveEdit(m.id)" class="px-4 py-2 bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 rounded-lg text-sm font-semibold hover:bg-emerald-500 hover:text-white transition-all">
                Simpan Perubahan
              </button>
            </div>
          </div>

          <div v-if="editingId !== m.id" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-5 pt-4 border-t border-emerald-950/30">
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Sertifikasi</div>
              <div class="text-sm font-semibold text-slate-300">{{ m.certification || '-' }}</div>
            </div>
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Spesialisasi</div>
              <div class="text-sm font-semibold text-slate-300">{{ formatSpecializations(m.specializations) || '-' }}</div>
            </div>
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">No HP</div>
              <div class="text-sm font-semibold text-slate-300">{{ m.phone_number || '-' }}</div>
            </div>
            <div>
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Portfolio</div>
              <div class="text-sm font-semibold text-slate-300">
                <a v-if="m.portfolio_link" :href="m.portfolio_link" target="_blank" class="text-emerald-400 hover:underline">Lihat Link</a>
                <span v-if="m.portfolio_link && m.portfolio_pdf"> | </span>
                <a v-if="m.portfolio_pdf" :href="'/storage/' + m.portfolio_pdf" target="_blank" class="text-amber-400 hover:underline">Unduh PDF</a>
                <span v-if="!m.portfolio_link && !m.portfolio_pdf">-</span>
              </div>
            </div>
            <div class="col-span-2 md:col-span-4">
              <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Bank</div>
              <div class="text-sm font-semibold text-slate-300">{{ m.bank_name || '-' }} / {{ m.bank_account_number || '-' }} (a.n {{ m.bank_account_name || '-' }})</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
