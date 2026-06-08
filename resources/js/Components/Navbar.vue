<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { authStore } from '@/Stores/authStore';
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const notifications = computed(() => page.props.notifications ?? []);
const hasNotifications = computed(() => notifications.value.length > 0);

const dropdownOpen = ref(false);
const notifOpen = ref(false);
const mobileMenuOpen = ref(false);
const isHomePage = computed(() => ['Home', 'Dashboard', 'Artikel', 'ArtikelDetail', 'News', 'NewsDetail', 'About', 'Partners', 'Subscription', 'KatalogDetail', 'EmitenHub/Index', 'EmitenHub/Show', 'Watchlist/Index'].includes(page.component));

const handleLogout = () => {
    dropdownOpen.value = false;
    router.post('/logout');
};

function toggleNotif() {
    notifOpen.value = !notifOpen.value;
    dropdownOpen.value = false;
}

function closeNotif() {
    notifOpen.value = false;
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

const categoryColors = {
    // Content types
    'ARTIKEL':      { bg: 'rgba(99,102,241,0.14)', color: '#818cf8', border: 'rgba(99,102,241,0.3)' },
    'Artikel':      { bg: 'rgba(99,102,241,0.14)', color: '#818cf8', border: 'rgba(99,102,241,0.3)' },
    'NEWS':         { bg: 'rgba(251,146,60,0.14)', color: '#fb923c', border: 'rgba(251,146,60,0.3)' },
    'News':         { bg: 'rgba(251,146,60,0.14)', color: '#fb923c', border: 'rgba(251,146,60,0.3)' },
    // Admin-defined categories
    'Riset Baru':   { bg: 'rgba(34,197,94,0.12)',  color: '#22c55e', border: 'rgba(34,197,94,0.25)' },
    'Info Sistem':  { bg: 'rgba(59,130,246,0.12)', color: '#60a5fa', border: 'rgba(59,130,246,0.25)' },
    'Promo / Event':{ bg: 'rgba(234,179,8,0.12)',  color: '#facc15', border: 'rgba(234,179,8,0.25)' },
    'Alert':        { bg: 'rgba(239,68,68,0.12)',  color: '#f87171', border: 'rgba(239,68,68,0.25)' },
    'General':      { bg: 'rgba(156,163,175,0.12)',color: '#9ca3af', border: 'rgba(156,163,175,0.25)' },
};

function catStyle(cat) {
    return categoryColors[cat] ?? categoryColors['General'];
}

// Build correct URL — stored format: "artikel-slug.html" or "news-slug.html"
// Artikel: strip "artikel-" prefix → /artikel/{slug}
// News: keep full slug (news- prefix is part of the slug) → /news/{news-slug}
function notifUrl(url, category) {
    if (!url) return '#';
    const cat = (category || '').toUpperCase();
    // Strip .html extension and leading slashes
    let raw = url.replace(/\.html$/i, '').replace(/^\/+/, '');

    if (cat === 'ARTIKEL') {
        // Remove stored /artikel/ path prefix if present
        let slug = raw.replace(/^artikel\//i, '');
        // Remove embedded "artikel-" filename prefix
        slug = slug.replace(/^artikel-/i, '');
        return '/artikel/' + slug;
    }
    if (cat === 'NEWS') {
        // News slugs already include "news-" prefix (e.g. news-btpn-xxx)
        // Just strip any path prefix, keep the full slug
        let slug = raw.replace(/^news\//i, '');
        return '/news/' + slug;
    }
    // Other categories: return as-is (external URLs kept intact)
    return raw.startsWith('http') ? raw : '/' + raw;
}

// Close notification dropdown when clicking outside
const notifRef = ref(null);
function handleOutsideClick(e) {
    if (notifRef.value && !notifRef.value.contains(e.target)) {
        notifOpen.value = false;
    }
}
onMounted(() => document.addEventListener('mousedown', handleOutsideClick));
onBeforeUnmount(() => document.removeEventListener('mousedown', handleOutsideClick));
</script>

<template>
  <nav class="nav" :class="{ 'nav-dark': isHomePage }">
    <div class="nav-in">
      <div class="nav-col nav-left">
        <Link href="/" class="nav-logo-link">
          <img src="/images/logo.png" class="nav-logo" alt="Avenir" />
        </Link>
        <div class="nav-links">
          <Link href="/" class="nav-link" :class="{ active: $page.component === 'Home' }">Beranda</Link>
          <Link href="/katalog" class="nav-link" :class="{ active: $page.component === 'Dashboard' || $page.component === 'KatalogDetail' }">Research Desk</Link>
          <Link href="/emiten" class="nav-link" :class="{ active: $page.component === 'EmitenHub/Index' || $page.component === 'EmitenHub/Show' }">Emiten Hub</Link>
          <Link href="/ki-brief" class="nav-link" :class="{ active: $page.component === 'KIBrief/Index' }">KI Brief</Link>
          <Link v-if="user" href="/watchlist" class="nav-link" :class="{ active: $page.component === 'Watchlist/Index' }">Watchlist</Link>
          <Link href="/artikel" class="nav-link" :class="{ active: $page.component === 'Artikel' || $page.component === 'ArtikelDetail' }">Artikel</Link>
          <Link href="/news" class="nav-link" :class="{ active: $page.component === 'News' || $page.component === 'NewsDetail' }">News</Link>
          <Link href="/tentang" class="nav-link" :class="{ active: $page.component === 'About' }">Tentang</Link>
          <Link href="/mitra" class="nav-link" :class="{ active: $page.component === 'Partners' }">Mitra</Link>
          <Link href="/langganan" class="nav-link" :class="{ active: $page.component === 'Subscription' }">Langganan</Link>
        </div>
      </div>

      <div class="nav-col nav-right">
        <!-- Guest Menu -->
        <div v-if="!user" class="nav-auth">
          <button class="nav-btn-login hidden-mobile" @click="authStore.open('login')">Sign In</button>
          <button class="nav-btn-register hidden-mobile" @click="authStore.open('register')">Daftar</button>

          <!-- Notification Bell -->
          <div class="notif-wrapper" ref="notifRef">
            <button class="nav-btn-notif" @click="toggleNotif" :class="{ active: notifOpen }">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
              </svg>
              <span v-if="hasNotifications" class="notif-dot"></span>
            </button>

            <!-- Notification Dropdown -->
            <transition name="notif-drop">
              <div v-if="notifOpen" class="notif-dropdown">
                <div class="notif-header">
                  <span class="notif-header-title">Notifikasi</span>
                  <span class="notif-count">{{ notifications.length }}</span>
                </div>
                <div class="notif-list">
                  <div v-if="notifications.length === 0" class="notif-empty">
                    Belum ada notifikasi.
                  </div>
                  <template v-else>
                    <a
                      v-for="notif in notifications"
                      :key="notif.id"
                      :href="notifUrl(notif.url, notif.category)"
                      :target="notif.url ? '_self' : '_self'"
                      class="notif-item"
                      @click="closeNotif"
                    >
                      <span
                        class="notif-cat-badge"
                        :style="{ background: catStyle(notif.category).bg, color: catStyle(notif.category).color, borderColor: catStyle(notif.category).border }"
                      >{{ notif.category }}</span>
                      <span class="notif-item-title">{{ notif.title }}</span>
                      <span class="notif-item-date">{{ formatDate(notif.published_at) }}</span>
                    </a>
                  </template>
                </div>
              </div>
            </transition>
          </div>

          <button class="nav-btn-hamburger" @click="mobileMenuOpen = !mobileMenuOpen" aria-label="Toggle menu">
            <svg v-if="!mobileMenuOpen" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="6" x2="20" y2="6"></line><line x1="4" y1="18" x2="20" y2="18"></line></svg>
            <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
          </button>
        </div>

        <!-- User Menu dropdown -->
        <div v-else class="nav-auth">
          <div class="hidden-mobile" style="position: relative;">
            <button 
              class="nav-btn-user" 
              @click="dropdownOpen = !dropdownOpen"
            >
              👤 <span class="username-text">{{ user.name }}</span>
              <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="6 9 12 15 18 9"/>
              </svg>
            </button>
            <div 
              v-if="dropdownOpen" 
              class="user-dropdown"
              @click="dropdownOpen = false"
            >
              <div class="user-dd-item">
                <span class="user-dd-icon">👤</span>
                <span>
                  <strong>Akun Saya</strong>
                  <span class="user-dd-hint">Profil &amp; pengaturan</span>
                </span>
              </div>
              <div class="user-dd-item">
                <span class="user-dd-icon">🎟️</span>
                <span>
                  <strong>Status Langganan</strong>
                  <span class="user-dd-hint">Cek masa aktif</span>
                </span>
              </div>
              <button class="user-dd-logout" @click="handleLogout">
                Keluar →
              </button>
            </div>
          </div>
          <button class="nav-btn-hamburger" @click="mobileMenuOpen = !mobileMenuOpen" aria-label="Toggle menu">
            <svg v-if="!mobileMenuOpen" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="6" x2="20" y2="6"></line><line x1="4" y1="18" x2="20" y2="18"></line></svg>
            <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Drawer -->
    <transition name="slide-down">
      <div v-if="mobileMenuOpen" class="nav-mobile-drawer">
        <div class="nav-mobile-links">
          <Link href="/" class="nav-mobile-link" :class="{ active: $page.component === 'Home' }" @click="mobileMenuOpen = false">Beranda</Link>
          <Link href="/katalog" class="nav-mobile-link" :class="{ active: $page.component === 'Dashboard' || $page.component === 'KatalogDetail' }" @click="mobileMenuOpen = false">Katalog</Link>
          <Link href="/emiten" class="nav-mobile-link" :class="{ active: $page.component === 'EmitenHub/Index' || $page.component === 'EmitenHub/Show' }" @click="mobileMenuOpen = false">Emiten</Link>
          <Link v-if="user" href="/watchlist" class="nav-mobile-link" :class="{ active: $page.component === 'Watchlist/Index' }" @click="mobileMenuOpen = false">Watchlist</Link>
          <Link href="/artikel" class="nav-mobile-link" :class="{ active: $page.component === 'Artikel' || $page.component === 'ArtikelDetail' }" @click="mobileMenuOpen = false">Artikel</Link>
          <Link href="/news" class="nav-mobile-link" :class="{ active: $page.component === 'News' || $page.component === 'NewsDetail' }" @click="mobileMenuOpen = false">News</Link>
          <Link href="/tentang" class="nav-mobile-link" :class="{ active: $page.component === 'About' }" @click="mobileMenuOpen = false">Tentang</Link>
          <Link href="/mitra" class="nav-mobile-link" :class="{ active: $page.component === 'Partners' }" @click="mobileMenuOpen = false">Mitra</Link>
          <Link href="/langganan" class="nav-mobile-link" :class="{ active: $page.component === 'Subscription' }" @click="mobileMenuOpen = false">Langganan</Link>
        </div>

        <div class="nav-mobile-divider"></div>

        <!-- Guest Actions on Mobile -->
        <div v-if="!user" class="nav-mobile-auth">
          <button class="nav-mobile-btn-login" @click="authStore.open('login'); mobileMenuOpen = false">Sign In</button>
          <button class="nav-mobile-btn-register" @click="authStore.open('register'); mobileMenuOpen = false">Daftar</button>
        </div>

        <!-- User Actions on Mobile -->
        <div v-else class="nav-mobile-auth">
          <div class="nav-mobile-user-info">
            <span class="user-avatar">👤</span>
            <span class="username-text-mobile">{{ user.name }}</span>
          </div>
          
          <div class="nav-mobile-user-links">
            <div class="user-dd-item-mobile">
              <span class="user-dd-icon">👤</span>
              <span>
                <strong>Akun Saya</strong>
                <span class="user-dd-hint">Profil &amp; pengaturan</span>
              </span>
            </div>
            <div class="user-dd-item-mobile">
              <span class="user-dd-icon">🎟️</span>
              <span>
                <strong>Status Langganan</strong>
                <span class="user-dd-hint">Cek masa aktif</span>
              </span>
            </div>
          </div>

          <button class="user-dd-logout-mobile" @click="handleLogout(); mobileMenuOpen = false">
            Keluar →
          </button>
        </div>
      </div>
    </transition>
  </nav>
</template>

<style scoped>
.nav {
  position: sticky;
  top: 0;
  z-index: 400;
  display: flex;
  align-items: center;
  padding: 0 24px;
  height: 52px;
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
}
.nav-in {
  max-width: 1120px;
  margin: 0 auto;
  width: 100%;
  height: 52px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
}
.nav-col {
  display: flex;
  align-items: center;
}
.nav-left {
  flex: 1;
  justify-content: flex-start;
}
.nav-right {
  justify-content: flex-end;
  gap: 6px;
}
.nav-logo-link {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  margin-right: 16px;
  text-decoration: none;
}
.nav-logo {
  height: 28px;
  width: auto;
  display: block;
}
.nav-links {
  display: flex;
  gap: 4px;
}
.nav-link {
  padding: 6px 14px;
  border-radius: 50px;
  font-size: 14px;
  font-weight: 500;
  border: none;
  background: transparent;
  color: #4b5563;
  cursor: pointer;
  text-decoration: none;
  font-family: inherit;
  transition: all 0.2s;
  letter-spacing: 0.01em;
}
.nav-link:hover {
  background: #f3f4f6;
  color: #111827;
}
.nav-link.active {
  background: #ecfdf5;
  color: #166534;
  font-weight: 600;
}
.nav-auth {
  display: flex;
  align-items: center;
  gap: 6px;
}
.nav-btn-login {
  padding: 6px 16px;
  border: 1px solid #d1d5db;
  border-radius: 50px;
  font-weight: 600;
  font-size: 14px;
  background: transparent;
  cursor: pointer;
  color: #374151;
  font-family: inherit;
  transition: all 0.2s;
}
.nav-btn-login:hover {
  border-color: #9ca3af;
}
.nav-btn-register {
  padding: 6px 16px;
  background-color: #166534;
  color: #fff;
  border: none;
  border-radius: 50px;
  font-weight: 700;
  font-size: 14px;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.2s;
}
.nav-btn-register:hover {
  background-color: #14532d;
}
.nav-btn-notif {
  position: relative;
  padding: 6px;
  border: 1px solid #d1d5db;
  border-radius: 50px;
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: border-color 0.2s;
}
.nav-btn-notif:hover {
  border-color: #9ca3af;
}
.notif-dot {
  position: absolute;
  top: 4px;
  right: 5px;
  width: 6px;
  height: 6px;
  background-color: #ef4444;
  border-radius: 50%;
  animation: notif-pulse 2s infinite;
}
@keyframes notif-pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(1.3); }
}

/* Notification wrapper & dropdown */
.notif-wrapper {
  position: relative;
}
.nav-btn-notif.active {
  border-color: #22c55e !important;
  background: rgba(34,197,94,0.08) !important;
  color: #22c55e !important;
}
.notif-dropdown {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  width: 320px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  box-shadow: 0 16px 40px rgba(0,0,0,0.12);
  z-index: 500;
  overflow: hidden;
}
.notif-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid #f3f4f6;
}
.notif-header-title {
  font-size: 13px;
  font-weight: 700;
  color: #111827;
}
.notif-count {
  font-size: 11px;
  font-weight: 700;
  background: #ecfdf5;
  color: #166534;
  border-radius: 50px;
  padding: 1px 8px;
}
.notif-list {
  max-height: 340px;
  overflow-y: auto;
  overflow-x: hidden;
}
.notif-empty {
  padding: 24px 16px;
  text-align: center;
  font-size: 13px;
  color: #9ca3af;
}
.notif-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 10px 16px;
  text-decoration: none;
  border-bottom: 1px solid #f9fafb;
  transition: background 0.15s;
  cursor: pointer;
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: #f9fafb; }
.notif-cat-badge {
  display: inline-block;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  padding: 2px 8px;
  border-radius: 50px;
  border: 1px solid;
  width: fit-content;
}
.notif-item-title {
  font-size: 13px;
  font-weight: 600;
  color: #111827;
  line-height: 1.4;
}
.notif-item-date {
  font-size: 11px;
  color: #9ca3af;
}

/* Dark mode overrides for notif dropdown */
.nav.nav-dark .notif-dropdown {
  background: #121614 !important;
  border-color: rgba(255,255,255,0.08) !important;
  box-shadow: 0 16px 40px rgba(0,0,0,0.5) !important;
}
.nav.nav-dark .notif-header {
  border-bottom-color: rgba(255,255,255,0.06) !important;
}
.nav.nav-dark .notif-header-title {
  color: #ffffff !important;
}
.nav.nav-dark .notif-count {
  background: rgba(34,197,94,0.15) !important;
  color: #22c55e !important;
}
.nav.nav-dark .notif-item {
  border-bottom-color: rgba(255,255,255,0.04) !important;
}
.nav.nav-dark .notif-item:hover {
  background: rgba(255,255,255,0.04) !important;
}
.nav.nav-dark .notif-item-title {
  color: #f3f4f6 !important;
}
.nav.nav-dark .notif-item-date {
  color: #6b7280 !important;
}

/* Notif dropdown transition */
.notif-drop-enter-active, .notif-drop-leave-active {
  transition: all 0.2s ease-out;
}
.notif-drop-enter-from, .notif-drop-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.97);
}

.nav-btn-user {
  padding: 6px 14px;
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 50px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: inherit;
  color: #374151;
}

.username-text {
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.user-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 6px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, .1);
  min-width: 200px;
  overflow: hidden;
  z-index: 10;
}

.user-dd-item {
  display: flex;
  gap: 10px;
  align-items: flex-start;
  padding: 11px 14px;
  text-decoration: none;
  color: #374151;
  font-size: 14px;
  transition: background .15s;
  cursor: pointer;
  text-align: left;
}

.user-dd-item:hover {
  background: #f9fafb;
}

.user-dd-icon {
  font-size: 16px;
  flex-shrink: 0;
  line-height: 1.2;
}

.user-dd-item strong {
  font-weight: 600;
  color: #111827;
  display: block;
  margin-bottom: 2px;
}

.user-dd-hint {
  font-size: 12px;
  color: #9ca3af;
  font-weight: 400;
  display: block;
}

.user-dd-logout {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  text-align: left;
  padding: 11px 14px;
  background: transparent;
  border: none;
  border-top: 1px solid #f3f4f6;
  color: #dc2626;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
}

.user-dd-logout:hover {
  background: #fef2f2;
}

@media (max-width: 700px) {
  .nav-links { display: none; }
}
@media (max-width: 600px) {
  .nav-btn-login { padding: 6px 12px; font-size: 12px; }
  .nav-btn-register { padding: 6px 12px; font-size: 12px; }
}

/* Dark glassmorphic override for landing page */
.nav.nav-dark {
  background: #090b0a !important; /* Menyesuaikan warna dasar gelap template Anda */
  backdrop-filter: none !important;
  -webkit-backdrop-filter: none !important;
  border-bottom: none !important; /* Menghilangkan garis sekat */
  box-shadow: none !important;
}
.nav.nav-dark .nav-link {
  color: #9ca3af !important;
}
.nav.nav-dark .nav-link:hover {
  background: rgba(255, 255, 255, 0.06) !important;
  color: #ffffff !important;
}
.nav.nav-dark .nav-link.active {
  background: rgba(34, 197, 94, 0.15) !important;
  color: #22c55e !important;
}
.nav.nav-dark .nav-btn-login {
  border-color: rgba(255, 255, 255, 0.15) !important;
  color: #d1d5db !important;
  background-color: transparent !important;
}
.nav.nav-dark .nav-btn-login:hover {
  border-color: rgba(255, 255, 255, 0.3) !important;
  color: #ffffff !important;
  background-color: rgba(255, 255, 255, 0.05) !important;
}
.nav.nav-dark .nav-btn-register {
  background-color: #22c55e !important;
  color: #ffffff !important;
}
.nav.nav-dark .nav-btn-register:hover {
  background-color: #16a34a !important;
}
.nav.nav-dark .nav-btn-notif {
  border-color: rgba(255, 255, 255, 0.15) !important;
  color: #d1d5db !important;
  background-color: transparent !important;
}
.nav.nav-dark .nav-btn-notif:hover {
  border-color: rgba(255, 255, 255, 0.3) !important;
  color: #ffffff !important;
}
.nav.nav-dark .nav-btn-user {
  background: rgba(255, 255, 255, 0.06) !important;
  border-color: rgba(255, 255, 255, 0.1) !important;
  color: #d1d5db !important;
}
.nav.nav-dark .user-dropdown {
  background: #121614 !important;
  border-color: rgba(255, 255, 255, 0.08) !important;
  box-shadow: 0 10px 30px rgba(0, 0, 0, .5) !important;
}
.nav.nav-dark .user-dd-item {
  color: #d1d5db !important;
}
.nav.nav-dark .user-dd-item:hover {
  background: rgba(255, 255, 255, 0.04) !important;
}
.nav.nav-dark .user-dd-item strong {
  color: #ffffff !important;
}
.nav.nav-dark .user-dd-logout {
  border-top-color: rgba(255, 255, 255, 0.08) !important;
}
.nav.nav-dark .nav-logo {
  filter: brightness(1.2) !important;
}

/* Responsive visibility utility classes */
.hidden-mobile {
  display: block;
}
.show-mobile {
  display: none;
}
.nav-btn-hamburger {
  display: none;
  background: transparent;
  border: none;
  cursor: pointer;
  color: #374151;
  padding: 6px;
  align-items: center;
  justify-content: center;
}

/* On screens < 768px (Mobile & Tablet) */
@media (max-width: 768px) {
  .hidden-mobile {
    display: none !important;
  }
  .show-mobile {
    display: flex !important;
  }
  .nav-btn-hamburger {
    display: flex;
  }
  .nav-links {
    display: none; /* Hide desktop links */
  }
  .nav-auth {
    gap: 12px;
  }
}

/* Mobile Drawer Container */
.nav-mobile-drawer {
  position: absolute;
  top: 100%;
  left: 0;
  width: 100%;
  background: #ffffff;
  border-bottom: 1px solid #e5e7eb;
  padding: 16px 24px 24px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  gap: 16px;
  z-index: 399;
}

/* Dark theme overrides for Mobile Drawer */
.nav.nav-dark .nav-mobile-drawer {
  background: #090b0a !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
}

.nav.nav-dark .nav-btn-hamburger {
  color: #d1d5db !important;
}

.nav.nav-dark .nav-btn-hamburger:hover {
  color: #ffffff !important;
}

.nav-mobile-links {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.nav-mobile-link {
  font-size: 15px;
  font-weight: 550;
  color: #4b5563;
  padding: 8px 12px;
  border-radius: 8px;
  text-decoration: none;
  transition: all 0.2s;
}

.nav-mobile-link:hover {
  background: #f3f4f6;
  color: #111827;
}

.nav-mobile-link.active {
  background: #ecfdf5;
  color: #166534;
  font-weight: 600;
}

/* Dark theme overrides for links */
.nav.nav-dark .nav-mobile-link {
  color: #9ca3af !important;
}

.nav.nav-dark .nav-mobile-link:hover {
  background: rgba(255, 255, 255, 0.06) !important;
  color: #ffffff !important;
}

.nav.nav-dark .nav-mobile-link.active {
  background: rgba(34, 197, 94, 0.15) !important;
  color: #22c55e !important;
}

.nav-mobile-divider {
  height: 1px;
  background: #e5e7eb;
}

.nav.nav-dark .nav-mobile-divider {
  background: rgba(255, 255, 255, 0.08);
}

.nav-mobile-auth {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.nav-mobile-btn-login {
  width: 100%;
  padding: 10px 16px;
  border: 1px solid #d1d5db;
  border-radius: 50px;
  font-weight: 600;
  font-size: 14px;
  background: transparent;
  cursor: pointer;
  color: #374151;
  text-align: center;
  transition: all 0.2s;
}

.nav-mobile-btn-login:hover {
  border-color: #9ca3af;
  background: #f9fafb;
}

.nav-mobile-btn-register {
  width: 100%;
  padding: 10px 16px;
  background-color: #166534;
  color: #fff;
  border: none;
  border-radius: 50px;
  font-weight: 700;
  font-size: 14px;
  cursor: pointer;
  text-align: center;
  transition: background 0.2s;
}

.nav-mobile-btn-register:hover {
  background-color: #14532d;
}

/* Dark theme overrides for buttons */
.nav.nav-dark .nav-mobile-btn-login {
  border-color: rgba(255, 255, 255, 0.15) !important;
  color: #d1d5db !important;
}

.nav.nav-dark .nav-mobile-btn-login:hover {
  border-color: rgba(255, 255, 255, 0.3) !important;
  color: #ffffff !important;
  background-color: rgba(255, 255, 255, 0.05) !important;
}

.nav.nav-dark .nav-mobile-btn-register {
  background-color: #22c55e !important;
  color: #ffffff !important;
}

.nav.nav-dark .nav-mobile-btn-register:hover {
  background-color: #16a34a !important;
}

/* User Profile Mobile View */
.nav-mobile-user-info {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
}

.username-text-mobile {
  font-weight: 600;
  color: #111827;
  font-size: 15px;
}

.nav.nav-dark .username-text-mobile {
  color: #ffffff;
}

.nav-mobile-user-links {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.user-dd-item-mobile {
  display: flex;
  gap: 10px;
  align-items: flex-start;
  padding: 10px 12px;
  color: #374151;
  font-size: 14px;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.15s;
}

.user-dd-item-mobile:hover {
  background: #f9fafb;
}

.nav.nav-dark .user-dd-item-mobile {
  color: #d1d5db;
}

.nav.nav-dark .user-dd-item-mobile:hover {
  background: rgba(255, 255, 255, 0.04);
}

.nav-mobile-user-links strong {
  font-weight: 600;
  color: #111827;
  display: block;
}

.nav.nav-dark .nav-mobile-user-links strong {
  color: #ffffff;
}

.user-dd-logout-mobile {
  width: 100%;
  text-align: center;
  padding: 10px 16px;
  background: transparent;
  border: 1px solid #fee2e2;
  border-radius: 50px;
  color: #dc2626;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.user-dd-logout-mobile:hover {
  background: #fef2f2;
}

.nav.nav-dark .user-dd-logout-mobile {
  border-color: rgba(220, 38, 38, 0.2) !important;
}

.nav.nav-dark .user-dd-logout-mobile:hover {
  background: rgba(220, 38, 38, 0.1) !important;
}

/* Slide Down Transition */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.25s ease-out;
}

.slide-down-enter-from,
.slide-down-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}
</style>
