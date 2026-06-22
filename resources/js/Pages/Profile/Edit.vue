<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MitraLayout from '@/Layouts/MitraLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    hasActivePremium: {
        type: Boolean,
        default: false,
    },
    activePackage: {
        type: String,
        default: null,
    },
    subscriptionEndsAt: {
        type: String,
        default: null,
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const hasRole = (roleName) => {
    return user.value?.roles?.some(r => r.name === roleName) || false;
};

const isAdmin = computed(() => hasRole('admin'));
const isMitra = computed(() => hasRole('mitra'));

// Select layout dynamically based on role
const activeLayout = computed(() => {
    if (isAdmin.value) return AdminLayout;
    if (isMitra.value) return MitraLayout;
    return AppLayout;
});
</script>

<template>
  <Head title="Pengaturan Profil — Avenir Research" />
  <component :is="activeLayout">
    <!-- Panel Mode: Admin or Mitra -->
    <div v-if="isAdmin || isMitra" class="space-y-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-3xl font-extrabold tracking-tight text-white">Pengaturan Profil</h2>
          <p class="text-sm text-slate-400 mt-1">Kelola informasi identitas pribadi, pembaruan password, dan preferensi akun Anda.</p>
        </div>
        
        <!-- Status Badge for Verified Mitra -->
        <div v-if="isMitra" class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold rounded-full w-fit">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
          Mitra Terverifikasi
        </div>
      </div>

      <div class="grid grid-cols-1 gap-8">
        <!-- Subscription Info Card -->
        <div class="panel-card">
          <h3 class="text-lg font-bold text-white mb-4">Status Langganan</h3>
          <div v-if="hasActivePremium" class="flex items-center justify-between p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
            <div>
              <p class="text-sm text-slate-400 mb-1">Paket Aktif</p>
              <p class="font-bold text-emerald-400">{{ activePackage }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm text-slate-400 mb-1">Berlaku Sampai</p>
              <p class="font-bold text-white">{{ subscriptionEndsAt || 'Seumur Hidup' }}</p>
            </div>
          </div>
          <div v-else class="flex flex-col sm:flex-row items-center justify-between p-4 bg-slate-800/50 border border-slate-700/50 rounded-xl gap-4">
            <div>
              <p class="text-sm text-slate-400 mb-1">Status Anda saat ini</p>
              <p class="font-bold text-white">Pengguna Gratis (Basic)</p>
            </div>
            <a href="/langganan" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-lg transition-colors">
              Tingkatkan ke Premium
            </a>
          </div>
        </div>

        <!-- Update Info Card -->
        <div class="panel-card">
          <UpdateProfileInformationForm
            :must-verify-email="mustVerifyEmail"
            :status="status"
          />
        </div>

        <!-- Update Password Card -->
        <div class="panel-card">
          <UpdatePasswordForm />
        </div>

        <!-- Delete User Card (only for non-admin) -->
        <div v-if="!isAdmin" class="panel-card delete-panel-card">
          <DeleteUserForm />
        </div>
      </div>
    </div>

    <!-- Public Mode: Regular Subscriber / User -->
    <div v-else class="profile-dark-wrapper">
      <!-- Glow Backdrops -->
      <div class="radial-glow glow-top-right"></div>
      <div class="radial-glow glow-bottom-left"></div>

      <div class="profile-page">
        <!-- Hero Section -->
        <div class="profile-hero">
          <div class="hero-eyebrow">◆ PENGATURAN AKUN</div>
          <h1>Pengaturan <span class="hl">Profil</span></h1>
          <p>Kelola informasi identitas pribadi, pembaruan password, dan preferensi akun Anda di satu tempat.</p>

          <!-- Mitra Application Status Badge for Pending/Unverified Users -->
          <div v-if="user?.partner" class="mitra-status-box">
            <div class="status-header">Status Pengajuan Mitra Analis</div>
            <div class="status-badge-wrapper">
              <div v-if="user.partner.is_verified" class="status-badge approved">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="status-icon"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                Terverifikasi (Aktif)
              </div>
              <div v-else class="status-badge pending">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="status-icon"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                Menunggu Verifikasi (Pending)
              </div>
            </div>
            <p class="status-desc">
              <span v-if="user.partner.is_verified">Akun Anda telah disetujui sebagai Mitra Analis. Silakan buka dashboard mitra Anda.</span>
              <span v-else>Pengajuan pendaftaran mitra Anda telah diterima dan sedang direview oleh tim editorial Avenir Fortuna. Mohon tunggu 5-7 hari kerja.</span>
            </p>
          </div>
        </div>

        <!-- Forms Section -->
        <div class="profile-sections">
          <!-- Subscription Info Card -->
          <div class="profile-card">
            <h3 class="text-lg font-bold text-white mb-4">Status Langganan</h3>
            <div v-if="hasActivePremium" class="flex items-center justify-between p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
              <div>
                <p class="text-sm text-slate-400 mb-1">Paket Aktif</p>
                <p class="font-bold text-emerald-400">{{ activePackage }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm text-slate-400 mb-1">Berlaku Sampai</p>
                <p class="font-bold text-white">{{ subscriptionEndsAt || 'Seumur Hidup' }}</p>
              </div>
            </div>
            <div v-else class="flex flex-col sm:flex-row items-center justify-between p-4 bg-slate-800/50 border border-slate-700/50 rounded-xl gap-4">
              <div>
                <p class="text-sm text-slate-400 mb-1">Status Anda saat ini</p>
                <p class="font-bold text-white">Pengguna Gratis (Basic)</p>
              </div>
              <a href="/langganan" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-lg transition-colors">
                Tingkatkan ke Premium
              </a>
            </div>
          </div>

          <!-- Update Info Card -->
          <div class="profile-card">
            <UpdateProfileInformationForm
              :must-verify-email="mustVerifyEmail"
              :status="status"
            />
          </div>

          <!-- Update Password Card -->
          <div class="profile-card">
            <UpdatePasswordForm />
          </div>

          <!-- Delete User Card (only for non-admin) -->
          <div v-if="!isAdmin" class="profile-card delete-card">
            <DeleteUserForm />
          </div>
        </div>
      </div>
    </div>
  </component>
</template>

<style scoped>
/* Panel Mode Styles */
.panel-card {
  background: #121614;
  border: 1px solid rgba(16, 185, 129, 0.15);
  border-radius: 20px;
  padding: 40px 32px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.delete-panel-card {
  border-color: rgba(239, 68, 68, 0.2);
}

/* Public Mode Styles */
.profile-dark-wrapper {
  background-color: #090b0a;
  color: #cbd5e1;
  font-family: 'Roboto', sans-serif;
  min-height: 100vh;
  position: relative;
  overflow-x: hidden;
  padding-top: 40px;
}

/* Glowing Background Vectors */
.radial-glow {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  filter: blur(140px);
  z-index: 1;
  opacity: 0.6;
}

.glow-top-right {
  top: -100px;
  right: -100px;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(34, 197, 94, 0.08) 0%, transparent 80%);
}

.glow-bottom-left {
  bottom: -150px;
  left: -150px;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(34, 197, 94, 0.05) 0%, transparent 80%);
}

.profile-page {
  max-width: 800px;
  margin: 0 auto;
  padding: 40px 24px 100px;
  position: relative;
  z-index: 2;
}

.profile-hero {
  text-align: center;
  margin-bottom: 48px;
}

.hero-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.1em;
  color: #22c55e;
  background: rgba(34, 197, 94, 0.08);
  border: 1px solid rgba(34, 197, 94, 0.18);
  padding: 6px 14px;
  border-radius: 30px;
  margin-bottom: 20px;
}

.profile-hero h1 {
  font-family: 'Roboto', sans-serif;
  font-size: clamp(28px, 4vw, 36px);
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 16px 0;
  letter-spacing: -0.02em;
}

.hl {
  color: #22c55e;
  text-shadow: 0 0 20px rgba(34, 197, 94, 0.2);
}

.profile-hero p {
  font-size: 15px;
  color: #94a3b8;
  max-width: 580px;
  margin: 0 auto;
  line-height: 1.7;
}

.profile-sections {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.profile-card {
  background: #121614;
  border: 1px solid rgba(34, 197, 94, 0.15);
  border-radius: 20px;
  padding: 40px 32px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.delete-card {
  border-color: rgba(239, 68, 68, 0.2);
}

:deep(.text-lg) {
  font-size: 18px !important;
  font-weight: 700 !important;
  color: #ffffff !important;
}

:deep(.text-sm) {
  color: #94a3b8 !important;
}

.mitra-status-box {
  margin: 24px auto 0;
  background: #121614;
  border: 1px solid rgba(16, 185, 129, 0.15);
  border-radius: 12px;
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  max-width: 480px;
  width: 100%;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.status-header {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #64748b;
}

.status-badge-wrapper {
  display: flex;
  justify-content: center;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 700;
  padding: 6px 16px;
  border-radius: 50px;
}

.status-badge.approved {
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: #10b981;
}

.status-badge.pending {
  background: rgba(245, 158, 11, 0.1);
  border: 1px solid rgba(245, 158, 11, 0.3);
  color: #f59e0b;
}

.status-icon {
  flex-shrink: 0;
}

.status-desc {
  font-size: 12px;
  color: #94a3b8;
  line-height: 1.5;
  text-align: center;
}
</style>
