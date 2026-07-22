<script setup>
import { ref, watch, onMounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { 
  LayoutDashboard, 
  Settings as SettingsIcon, 
  FileText, 
  Newspaper,
  Users, 
  Menu, 
  X, 
  LogOut, 
  Layers,
  LibraryBig,
  ChevronDown,
  Search,
  Server,
  ChevronLeft,
  ChevronRight,
  CreditCard,
  UserCheck,
  Bell,
  BrainCircuit,
  Globe,
  Activity,
  Database,
  TrendingUp,
  ShieldAlert,
  Coins,
  Building2,
  Upload
} from '@lucide/vue';

const page = usePage();
const user = page.props.auth.user;

const showToast = (icon, title, text) => {
    Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        background: '#121614',
        color: '#f1f5f9',
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    }).fire({
        icon: icon,
        title: title || (icon === 'success' ? 'Berhasil' : 'Error'),
        text: text
    });
};

onMounted(() => {
    if (page.props.flash?.success) showToast('success', null, page.props.flash.success);
    if (page.props.flash?.error) showToast('error', null, page.props.flash.error);
});

watch(() => page.props.flash, (flash) => {
    if (flash?.success) showToast('success', null, flash.success);
    if (flash?.error) showToast('error', null, flash.error);
}, { deep: true });

const isSidebarOpen = ref(false);
const isSidebarCollapsed = ref(false);
const isUserMenuOpen = ref(false);

const navigationGroups = [
  {
    title: 'MAIN MENU',
    roles: ['admin', 'tim_internal'],
    items: [
      { name: 'Dashboard', href: route('admin.dashboard'), icon: LayoutDashboard, current: route().current('admin.dashboard') },
    ]
  },
  {
    title: 'CONTENT MANAGEMENT',
    roles: ['admin', 'tim_internal'],
    items: [
      { name: 'Desk Brief', href: route('admin.desk-brief.index'), icon: Activity, current: route().current('admin.desk-brief.index') || route().current('admin.desk-brief.edit') },
      { name: 'Ownership Intel', href: route('admin.desk-brief.ownership.index'), icon: Database, current: route().current('admin.desk-brief.ownership.*') },
      { name: 'Katalog Riset', href: route('admin.katalog-riset.index'), icon: FileText, current: route().current('admin.katalog-riset.*') },
      { name: 'News (Berita Pasar)', href: route('admin.news.index'), icon: Newspaper, current: route().current('admin.news.*') },
      { name: 'Artikel Edukasi', href: route('admin.articles.index'), icon: LibraryBig, current: route().current('admin.articles.*') },
    ]
  },
  {
    title: 'AVENIR AI ENGINES',
    roles: ['admin', 'tim_internal'],
    items: [
      { name: 'Research AI', href: route('admin.research-generator.index'), icon: BrainCircuit, current: route().current('admin.research-generator.*') },
      { name: 'News AI Generator', href: route('admin.news-generator.index'), icon: Globe, current: route().current('admin.news-generator.*') },
      { name: 'AI Logs (Audit)', href: route('admin.ai-logs.index'), icon: Activity, current: route().current('admin.ai-logs.*'), roles: ['admin'] },
      { name: 'Activity Logs', href: route('admin.activity-logs.index'), icon: ShieldAlert, current: route().current('admin.activity-logs.*'), roles: ['admin'] },
    ]
  },
  {
    title: 'COMMUNITY & FINANCE',
    roles: ['admin'],
    items: [
      { name: 'Paket Langganan', href: route('admin.packages.index'), icon: CreditCard, current: route().current('admin.packages.*') },
      { name: 'Pembayaran', href: route('admin.payments.index'), icon: CreditCard, current: route().current('admin.payments.*') },
      { name: 'Pool Mitra', href: route('admin.pool.index'), icon: Coins, current: route().current('admin.pool.*') },
      { name: 'Mitra Analis', href: route('admin.mitra.index'), icon: UserCheck, current: route().current('admin.mitra.*') },
      { name: 'Tim Internal', href: route('admin.tim-internal.index'), icon: Users, current: route().current('admin.tim-internal.*') },
      { name: 'Subscriber', href: route('admin.users.index'), icon: Users, current: route().current('admin.users.*') },
      { name: 'Notifikasi & Blast', href: route('admin.notifications.index'), icon: Bell, current: route().current('admin.notifications.*') },
    ]
  },
  {
    title: 'SYSTEM & CMS',
    roles: ['admin', 'tim_internal'],
    items: [
      { name: 'Emiten Hub', href: route('admin.emitens.index'), icon: TrendingUp, current: route().current('admin.emitens.*'), roles: ['admin'] },
      { name: 'Master Emiten', href: route('admin.master-stock.index'), icon: Building2, current: route().current('admin.master-stock.*'), roles: ['admin'] },
      { name: 'EOD Uploads', href: route('admin.eod-uploads.index'), icon: Upload, current: route().current('admin.eod-uploads.*'), roles: ['admin', 'tim_internal'] },
      { name: 'Web Settings', href: route('admin.settings.index'), icon: SettingsIcon, current: route().current('admin.settings.index'), roles: ['admin'] },
    ]
  }
];

const hasRole = (allowedRoles) => {
  if (!allowedRoles) return true;
  if (user.roles && Array.isArray(user.roles)) {
    return user.roles.some(r => allowedRoles.includes(r.name));
  }
  return false;
};

const filteredNavigationGroups = navigationGroups.filter(group => hasRole(group.roles)).map(group => {
  return {
    ...group,
    items: group.items.filter(item => hasRole(item.roles))
  };
});

const logout = () => {
  router.post(route('logout'));
};
</script>

<template>
  <div class="min-h-screen bg-[#090b0a] text-slate-100 font-sans antialiased relative overflow-hidden">
    <!-- Mobile Sidebar Backdrop -->
    <div 
      v-if="isSidebarOpen" 
      @click="isSidebarOpen = false" 
      class="fixed inset-0 z-40 bg-[#090b0a]/80 lg:hidden transition-opacity"
    ></div>

    <!-- Sidebar for Mobile and Desktop -->
    <aside 
      :class="[
        isSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        isSidebarCollapsed ? 'lg:w-20' : 'lg:w-72',
        'fixed top-0 bottom-0 left-0 z-50 w-72 bg-[#121614] border-r border-emerald-950/20 transition-all duration-300 ease-in-out lg:fixed flex flex-col justify-between'
      ]"
    >
      <div class="flex flex-col flex-1 overflow-hidden">
        <!-- Sidebar Header / Logo -->
        <div :class="[isSidebarCollapsed ? 'lg:px-2 lg:justify-center' : 'px-6 justify-between', 'flex items-center h-20 border-b border-emerald-950/20 shrink-0']">
          <Link :href="route('admin.dashboard')" class="flex items-center gap-3">
            <img src="/images/logo.png" :class="[isSidebarCollapsed ? 'h-5' : 'h-7', 'w-auto object-contain transition-all']" alt="Avenir" />
            <div v-if="!isSidebarCollapsed" class="transition-opacity duration-300">
              <h1 class="text-xs font-bold tracking-tight text-white uppercase">Admin Panel</h1>
            </div>
          </Link>
          <button 
            @click="isSidebarOpen = false" 
            class="p-2 text-slate-400 rounded-lg hover:bg-slate-800 hover:text-white lg:hidden"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <nav class="flex-1 px-3 py-6 space-y-6 overflow-y-auto custom-scrollbar">
          <div v-for="group in filteredNavigationGroups" :key="group.title">
            <h3 v-if="!isSidebarCollapsed" class="px-4 text-[10px] font-black tracking-widest text-emerald-600/70 uppercase mb-2">
              {{ group.title }}
            </h3>
            <div class="space-y-1.5">
              <Link 
                v-for="item in group.items" 
                :key="item.name" 
                :href="item.href"
                :class="[
                  item.current 
                    ? 'bg-gradient-to-r from-emerald-600/15 to-emerald-600/5 text-emerald-400 border-l-4 border-emerald-500' 
                    : 'text-slate-450 hover:bg-emerald-900/55 hover:text-slate-200 border-l-4 border-transparent',
                  isSidebarCollapsed ? 'lg:justify-center lg:px-0' : 'px-4',
                  'group flex items-center py-3.5 text-sm font-semibold rounded-r-xl transition-all duration-300'
                ]"
                :title="isSidebarCollapsed ? item.name : ''"
              >
                <component 
                  :is="item.icon" 
                  :class="[
                    item.current ? 'text-emerald-400' : 'text-slate-500 group-hover:text-slate-350',
                    isSidebarCollapsed ? 'lg:mr-0' : 'mr-3',
                    'h-5 w-5 flex-shrink-0 transition-transform duration-300 group-hover:scale-105'
                  ]" 
                />
                <span :class="[isSidebarCollapsed ? 'lg:hidden' : 'block', 'whitespace-nowrap']">{{ item.name }}</span>
              </Link>
            </div>
          </div>
        </nav>
      </div>

      <!-- Sidebar Footer / Template Owner & Profile -->
      <div :class="[isSidebarCollapsed ? 'lg:p-2' : 'p-4', 'border-t border-emerald-950/20 bg-[#121614]/50']">
        <!-- Template Owner & Collapse Toggle Row -->
        <div :class="[isSidebarCollapsed ? 'lg:px-0 lg:justify-center' : 'px-2 justify-between', 'mb-4 flex items-center text-xxs text-slate-500 font-bold uppercase tracking-wider transition-opacity duration-300']">
          <div v-if="!isSidebarCollapsed" class="flex items-center gap-1">
            <Link href="/" target="_blank" class="text-emerald-400 hover:text-emerald-300 transition-colors normal-case">
              Halaman Depan
            </Link>
          </div>
          <!-- Collapse Toggle for Desktop -->
          <div class="hidden lg:block">
            <button 
              @click="isSidebarCollapsed = !isSidebarCollapsed"
              class="p-1.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-lg text-slate-400 hover:text-white transition-colors cursor-pointer"
            >
              <ChevronLeft v-if="!isSidebarCollapsed" class="w-4 h-4" />
              <ChevronRight v-else class="w-4 h-4" />
            </button>
          </div>
        </div>

        <div :class="[isSidebarCollapsed ? 'lg:px-0 lg:justify-center' : 'px-2 gap-3', 'flex items-center pt-3 border-t border-emerald-950/20']">
          <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center font-bold text-emerald-450 border border-slate-700 shrink-0 overflow-hidden">
            <img v-if="user.profile_photo_path" :src="user.profile_photo_url" :alt="user.name" class="w-full h-full object-cover" />
            <span v-else>{{ user.name.charAt(0).toUpperCase() }}</span>
          </div>
          <div :class="[isSidebarCollapsed ? 'lg:hidden' : 'block', 'flex-1 min-w-0 transition-opacity duration-300']">
            <p class="text-sm font-semibold text-slate-200 truncate">{{ user.name }}</p>
            <p class="text-xs text-slate-550 truncate">{{ user.email }}</p>
          </div>
          <button 
            :class="[isSidebarCollapsed ? 'lg:hidden' : 'block']"
            @click="logout" 
            title="Keluar"
            class="p-2 text-slate-500 rounded-lg hover:bg-slate-900 hover:text-rose-400 transition-colors"
          >
            <LogOut class="w-5 h-5" />
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div 
      :class="[
        isSidebarCollapsed ? 'lg:pl-20' : 'lg:pl-72',
        'flex flex-col min-h-screen transition-all duration-300 ease-in-out'
      ]"
    >
      <!-- Top header bar (Sticky Glassmorphic) -->
      <header class="flex items-center justify-between h-20 px-6 md:px-8 border-b border-emerald-950/20 bg-[#121614]/40 backdrop-blur sticky top-0 z-30">
        <div class="flex items-center gap-6 flex-1 max-w-md">
          <button 
            @click="isSidebarOpen = true" 
            class="p-2 text-slate-400 rounded-lg hover:bg-slate-800 hover:text-white lg:hidden"
          >
            <Menu class="w-6 h-6" />
          </button>
          
          <!-- Floating search bar -->
          <div class="relative w-full hidden sm:block">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
              <Search class="w-4 h-4" />
            </span>
            <input 
              type="text" 
              placeholder="Ketik '/' untuk mencari..."
              class="w-full bg-[#090b0a] border border-[#090b0a] rounded-xl pl-10 pr-4 py-2.5 text-xs text-slate-250 placeholder-slate-550 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
            />
            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 px-1.5 py-0.5 bg-slate-800 text-[10px] font-bold text-slate-500 rounded border border-slate-700">
              /
            </span>
          </div>
        </div>

        <!-- Right Header Elements -->
        <div class="flex items-center gap-6">
          <!-- Server Status Indicator -->
          <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-500/10 border border-emerald-500/20 rounded-full text-[10px] font-bold text-emerald-450 uppercase tracking-wider">
            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></span>
            Server: Online
          </div>

          <!-- Profile dropdown -->
          <div class="relative">
            <button 
              @click="isUserMenuOpen = !isUserMenuOpen"
              class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-[#121614] border border-transparent hover:border-emerald-950/20 transition-all text-slate-200"
            >
              <div class="w-7 h-7 rounded-lg bg-emerald-600/10 border border-emerald-500/20 text-emerald-450 font-bold flex items-center justify-center text-xs overflow-hidden">
                <img v-if="user.profile_photo_path" :src="user.profile_photo_url" :alt="user.name" class="w-full h-full object-cover" />
                <span v-else>{{ user.name.charAt(0).toUpperCase() }}</span>
              </div>
              <span class="text-xs font-semibold hidden md:inline-block">{{ user.name }}</span>
              <ChevronDown class="w-4 h-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': isUserMenuOpen }" />
            </button>
            <div 
              v-if="isUserMenuOpen" 
              @click="isUserMenuOpen = false" 
              class="fixed inset-0 z-10"
            ></div>
            <div 
              v-if="isUserMenuOpen" 
              class="absolute right-0 mt-2 w-48 rounded-2xl bg-[#121614] border border-emerald-950/20 shadow-xl shadow-slate-950/50 py-1.5 z-20 overflow-hidden transform origin-top-right transition-all"
            >
              <Link 
                :href="route('profile.edit')" 
                class="block px-4 py-2.5 text-xs font-semibold text-slate-350 hover:bg-slate-900 transition-colors"
              >
                Profile Settings
              </Link>
              <button 
                @click="logout" 
                class="w-full text-left block px-4 py-2.5 text-xs font-bold text-rose-400 hover:bg-slate-900 transition-colors border-t border-slate-850"
              >
                Sign Out
              </button>
            </div>
          </div>
        </div>
      </header>

      <!-- Main Panel Page -->
      <main class="flex-1 p-6 md:p-8 bg-[#090b0a]/60">
        <slot />
      </main>
    </div>
  </div>
</template>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgba(255, 255, 255, 0.1) transparent;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
