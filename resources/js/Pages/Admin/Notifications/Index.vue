<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Bell, Plus, Send, Trash2, Link as LinkIcon, Edit } from '@lucide/vue';
import { ref } from 'vue';

const props = defineProps({
  notifications: Array
});

const form = useForm({
  category: 'Riset Baru',
  title: '',
  url: '',
  target_audience: 'Semua User',
  published_at: '',
  broadcast_email: false,
  broadcast_telegram: false,
});

const isEditing = ref(false);
const editId = ref(null);

function submitForm() {
  if (isEditing.value) {
    form.put(route('admin.notifications.update', editId.value), {
      preserveScroll: true,
      onSuccess: () => resetForm(),
    });
  } else {
    form.post(route('admin.notifications.store'), {
      preserveScroll: true,
      onSuccess: () => resetForm(),
    });
  }
}

function resetForm() {
  form.reset();
  isEditing.value = false;
  editId.value = null;
}

function editNotification(notif) {
  isEditing.value = true;
  editId.value = notif.id;
  form.category = notif.category || 'Riset Baru';
  form.title = notif.title || '';
  form.url = notif.url || '';
  // Note: we just default the rest for demo editing since DB only stores basic fields
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function deleteNotification(id) {
  if (confirm('Apakah Anda yakin ingin menghapus notifikasi ini?')) {
    router.delete(route('admin.notifications.destroy', id), {
      preserveScroll: true
    });
  }
}

function formatDate(dateString) {
  if (!dateString) return '-';
  const d = new Date(dateString);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
  <Head title="Kelola Notifications" />

  <AdminLayout>
    <div class="space-y-6 max-w-7xl mx-auto pb-12">
      <!-- Title -->
      <div class="flex items-center justify-between border-b border-emerald-950/30 pb-4">
        <div>
          <h2 class="text-2xl font-extrabold tracking-tight text-white flex items-center gap-2">
            <Bell class="w-6 h-6 text-emerald-500" />
            Notifikasi & Broadcast
          </h2>
          <p class="text-sm text-slate-400 mt-1">Buat pemberitahuan baru dan kirim ke pengguna platform.</p>
        </div>
      </div>

      <!-- Create/Edit Form -->
      <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl p-6 shadow-xl">
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
          <Plus v-if="!isEditing" class="w-5 h-5 text-emerald-500" />
          <Edit v-else class="w-5 h-5 text-emerald-500" />
          {{ isEditing ? 'Edit Notifikasi' : 'Buat Notifikasi Baru' }}
        </h3>
        
        <form @submit.prevent="submitForm" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Tipe Notifikasi -->
            <div>
              <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Tipe Notifikasi</label>
              <select v-model="form.category" class="w-full bg-[#090b0a] border border-emerald-950/50 text-white rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                <option>ARTIKEL</option>
                <option>NEWS</option>
                <option>Riset Baru</option>
                <option>Info Sistem</option>
                <option>Promo / Event</option>
                <option>Alert</option>
              </select>
            </div>
            
            <!-- Judul -->
            <div>
              <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Judul / Headline</label>
              <input type="text" v-model="form.title" placeholder="Contoh: Outlook Kuartal 3 2026 Tersedia" class="w-full bg-[#090b0a] border border-emerald-950/50 text-white rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" required />
            </div>
            
            <!-- Pesan / Link URL -->
            <div class="md:col-span-2">
              <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Pesan / Link URL (Opsional)</label>
              <input type="text" v-model="form.url" placeholder="https://researchavenir.com/..." class="w-full bg-[#090b0a] border border-emerald-950/50 text-white rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" />
            </div>

            <!-- Target Audience -->
            <div>
              <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Target Audience</label>
              <select v-model="form.target_audience" class="w-full bg-[#090b0a] border border-emerald-950/50 text-white rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                <option>Semua User</option>
                <option>Mitra & Subscriber Aktif</option>
                <option>User Trial</option>
              </select>
            </div>

            <!-- Tanggal Broadcast -->
            <div>
              <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Tanggal Broadcast</label>
              <input type="datetime-local" v-model="form.published_at" class="w-full bg-[#090b0a] border border-emerald-950/50 text-white rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" />
            </div>
          </div>

          <div class="flex items-center gap-6 py-2">
            <label class="flex items-center gap-2 cursor-pointer group">
              <div class="relative flex items-center">
                <input type="checkbox" v-model="form.broadcast_email" class="w-4 h-4 rounded border-emerald-950/50 bg-[#090b0a] text-emerald-500 focus:ring-emerald-500 focus:ring-offset-[#121614]" />
              </div>
              <span class="text-sm font-semibold text-slate-300 group-hover:text-white transition-colors">Broadcast via Email</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer group">
              <div class="relative flex items-center">
                <input type="checkbox" v-model="form.broadcast_telegram" class="w-4 h-4 rounded border-emerald-950/50 bg-[#090b0a] text-emerald-500 focus:ring-emerald-500 focus:ring-offset-[#121614]" />
              </div>
              <span class="text-sm font-semibold text-slate-300 group-hover:text-white transition-colors">Broadcast via Telegram</span>
            </label>
          </div>

          <div class="flex items-center gap-3 pt-2 border-t border-emerald-950/30">
            <button type="submit" :disabled="form.processing" class="flex items-center gap-2 px-5 py-2 bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-sm rounded-lg transition-colors disabled:opacity-50">
              <Send class="w-4 h-4" />
              {{ isEditing ? 'Simpan Perubahan' : 'Kirim / Jadwalkan' }}
            </button>
            <button v-if="isEditing" type="button" @click="resetForm" class="px-5 py-2 bg-[#090b0a] border border-emerald-950/50 hover:border-emerald-700 text-slate-300 font-bold text-sm rounded-lg transition-colors">
              Batal
            </button>
          </div>
        </form>
      </div>

      <!-- Existing Notifications List -->
      <div class="bg-[#121614] border border-emerald-950/30 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-emerald-950/30 bg-[#090b0a]/50">
          <h3 class="text-lg font-bold text-white">Riwayat Notifikasi</h3>
        </div>
        
        <div v-if="notifications.length === 0" class="p-8 text-center text-slate-400 text-sm">
          Belum ada notifikasi yang dibuat.
        </div>
        
        <div class="overflow-x-auto" v-else>
          <table class="w-full text-left text-sm text-slate-300">
            <thead class="text-[10px] uppercase bg-[#090b0a] text-slate-500 border-b border-emerald-950/50">
              <tr>
                <th class="px-5 py-3 font-bold tracking-widest w-[40%]">Isi Notifikasi</th>
                <th class="px-5 py-3 font-bold tracking-widest">Kategori</th>
                <th class="px-5 py-3 font-bold tracking-widest">Target</th>
                <th class="px-5 py-3 font-bold tracking-widest">Status</th>
                <th class="px-5 py-3 font-bold tracking-widest text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-emerald-950/30">
              <tr v-for="notif in notifications" :key="notif.id" class="hover:bg-emerald-900/10 transition-colors">
                <td class="px-5 py-4">
                  <div class="font-bold text-white mb-1">{{ notif.title }}</div>
                  <div class="flex items-center gap-1 text-xs text-slate-500 font-mono truncate max-w-xs">
                    <LinkIcon class="w-3 h-3" />
                    {{ notif.url || 'Tidak ada URL' }}
                  </div>
                </td>
                <td class="px-5 py-4">
                  <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    {{ notif.category || 'General' }}
                  </span>
                </td>
                <td class="px-5 py-4 text-xs font-semibold text-slate-400">
                  {{ notif.target_audience || 'Semua User' }}
                </td>
                <td class="px-5 py-4">
                  <div class="text-xs font-bold text-emerald-500">Sent</div>
                  <div class="text-[10px] text-slate-500 mt-0.5">{{ formatDate(notif.published_at || notif.created_at) }}</div>
                </td>
                <td class="px-5 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="editNotification(notif)" class="p-1.5 text-slate-400 hover:text-emerald-400 hover:bg-emerald-500/10 rounded transition-colors" title="Edit">
                      <Edit class="w-4 h-4" />
                    </button>
                    <button @click="deleteNotification(notif.id)" class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded transition-colors" title="Hapus">
                      <Trash2 class="w-4 h-4" />
                    </button>
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
