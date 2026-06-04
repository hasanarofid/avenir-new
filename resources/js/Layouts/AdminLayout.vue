<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { 
  LayoutDashboard, 
  Settings as SettingsIcon, 
  FileText, 
  Users, 
  Menu, 
  X, 
  LogOut, 
  Layers,
  ChevronDown,
  Search,
  Server,
  ChevronLeft,
  ChevronRight
} from '@lucide/vue';

const page = usePage();
const user = page.props.auth.user;

const isSidebarOpen = ref(false);
const isSidebarCollapsed = ref(false);
const isUserMenuOpen = ref(false);

const navigation = [
  { name: 'Dashboard', href: route('admin.dashboard'), icon: LayoutDashboard, current: route().current('admin.dashboard') },
  { name: 'Pages & Sections', href: route('admin.pages.index'), icon: Layers, current: route().current('admin.pages.*') },
  { name: 'Posts & Categories', href: route('admin.posts.index'), icon: FileText, current: route().current('admin.posts.*') },
  { name: 'Web Settings', href: route('admin.settings.index'), icon: SettingsIcon, current: route().current('admin.settings.index') },
];

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
      <div>
        <!-- Sidebar Header / Logo -->
        <div :class="[isSidebarCollapsed ? 'lg:px-2 lg:justify-center' : 'px-6 justify-between', 'flex items-center h-20 border-b border-emerald-950/20']">
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

        <!-- Navigation Links -->
        <nav class="px-3 py-6 space-y-1.5 overflow-y-auto">
          <Link 
            v-for="item in navigation" 
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
          <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center font-bold text-emerald-450 border border-slate-700 shrink-0">
            {{ user.name.charAt(0).toUpperCase() }}
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
              class="w-full bg-slate-900/60 border border-slate-800 rounded-xl pl-10 pr-4 py-2.5 text-xs text-slate-250 placeholder-slate-550 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
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
              <div class="w-7 h-7 rounded-lg bg-emerald-600/10 border border-emerald-500/20 text-emerald-450 font-bold flex items-center justify-center text-xs">
                {{ user.name.charAt(0).toUpperCase() }}
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
        <!-- Toast Notification Alert if session contains flash success/error -->
        <div v-if="$page.props.flash?.success" class="mb-6 p-4 bg-emerald-600/10 border border-emerald-500/20 text-emerald-400 rounded-xl flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="text-sm font-medium">{{ $page.props.flash.success }}</span>
          </div>
        </div>
        <div v-if="$page.props.flash?.error" class="mb-6 p-4 bg-rose-600/10 border border-rose-500/20 text-rose-400 rounded-xl flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            <span class="text-sm font-medium">{{ $page.props.flash.error }}</span>
          </div>
        </div>

        <slot />
      </main>
    </div>
  </div>
</template>
