<script setup>
import { Head, usePage, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ResearchCard from '@/Components/ResearchCard.vue';
import { authStore } from '@/Stores/authStore';

const props = defineProps({
    researches: {
        type: Array,
        default: () => []
    },
    unlockedTickers: {
        type: Array,
        default: () => []
    }
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

// --- Filters State ---
const searchQuery = ref('');
const selectedSector = ref('Semua');
const selectedRec = ref('Semua');
const selectedType = ref('Semua');
const selectedAnalyst = ref('Semua');
const selectedYear = ref('Semua');
const sortOrder = ref('Terbaru');
const viewMode = ref('grid'); // grid or list

const currentPage = ref(1);
const itemsPerPage = 18;

// --- Options for Dropdowns ---
const uniqueSectors = computed(() => {
    const sectors = props.researches.map(r => r.sector).filter(Boolean).map(s => s.trim());
    return ['Semua', ...new Set(sectors)];
});
const uniqueRecs = computed(() => {
    const recs = props.researches.map(r => r.recommendation).filter(Boolean).map(s => s.trim().toUpperCase());
    return ['Semua', ...new Set(recs)];
});
const uniqueTypes = computed(() => {
    const types = props.researches.map(r => r.report_type).filter(Boolean).map(s => s.trim());
    return ['Semua', ...new Set(types)];
});
const uniqueAnalysts = computed(() => {
    const analysts = props.researches.map(r => r.author?.name).filter(Boolean);
    return ['Semua', ...new Set(analysts)];
});
const uniqueYears = computed(() => {
    const years = props.researches.map(r => {
        if(!r.date) return null;
        const match = r.date.match(/\d{4}/);
        return match ? match[0] : null;
    }).filter(Boolean);
    return ['Semua', ...new Set(years)].sort((a,b) => b-a);
});

// --- Filtering Logic ---
const filteredResearches = computed(() => {
    return props.researches.filter(r => {
        // Search
        if (searchQuery.value) {
            const q = searchQuery.value.toLowerCase();
            const matchTitle = r.title?.toLowerCase().includes(q);
            const matchTicker = r.ticker?.toLowerCase().includes(q);
            const matchCompany = r.sector?.toLowerCase().includes(q);
            if (!matchTitle && !matchTicker && !matchCompany) return false;
        }
        // Sector
        if (selectedSector.value !== 'Semua' && r.sector !== selectedSector.value) return false;
        // Recommendation
        if (selectedRec.value !== 'Semua' && r.recommendation?.toUpperCase() !== selectedRec.value) return false;
        // Type
        if (selectedType.value !== 'Semua' && r.report_type !== selectedType.value) return false;
        // Analyst
        if (selectedAnalyst.value !== 'Semua' && r.author?.name !== selectedAnalyst.value) return false;
        // Year
        if (selectedYear.value !== 'Semua') {
            if (!r.date || !r.date.includes(selectedYear.value)) return false;
        }
        return true;
    }).sort((a, b) => {
        if (sortOrder.value === 'Terbaru') {
            return new Date(b.created_at || 0) - new Date(a.created_at || 0);
        } else {
            return new Date(a.created_at || 0) - new Date(b.created_at || 0);
        }
    });
});

// --- Featured & Pagination ---
const featuredReport = computed(() => {
    // Return first premium report from the filtered list, or just the first report
    const premium = filteredResearches.value.find(r => r.is_premium);
    return premium || filteredResearches.value[0];
});

const paginatedReports = computed(() => {
    // Exclude featured report from the list
    let list = filteredResearches.value;
    if (featuredReport.value && currentPage.value === 1) {
        list = list.filter(r => r.id !== featuredReport.value.id);
    }
    
    const start = (currentPage.value - 1) * itemsPerPage;
    return list.slice(start, start + itemsPerPage);
});

const totalPages = computed(() => Math.ceil(filteredResearches.value.length / itemsPerPage));

const changePage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const resetFilters = () => {
    searchQuery.value = '';
    selectedSector.value = 'Semua';
    selectedRec.value = 'Semua';
    selectedType.value = 'Semua';
    selectedAnalyst.value = 'Semua';
    selectedYear.value = 'Semua';
    sortOrder.value = 'Terbaru';
    currentPage.value = 1;
};

// --- Sidebar Stats ---
const totalPremium = computed(() => props.researches.filter(r => r.is_premium).length);

const newThisMonth = computed(() => {
    const now = new Date();
    const currentMonth = now.getMonth();
    const currentYear = now.getFullYear();
    return props.researches.filter(r => {
        if (!r.created_at) return false;
        const d = new Date(r.created_at);
        return d.getMonth() === currentMonth && d.getFullYear() === currentYear;
    }).length;
});

const topSectors = computed(() => {
    const counts = {};
    props.researches.forEach(r => {
        if (r.sector) {
            counts[r.sector] = (counts[r.sector] || 0) + 1;
        }
    });
    const sorted = Object.entries(counts).sort((a,b) => b[1] - a[1]).slice(0, 5);
    const max = sorted.length > 0 ? sorted[0][1] : 1;
    return sorted.map(([name, count]) => ({
        name,
        count,
        percent: (count / max) * 100
    }));
});
</script>

<template>
  <Head title="Katalog Riset — Avenir Research" />
  <AppLayout>
    <div class="katalog-dashboard">
      <!-- Glow Backdrops -->
      <div class="radial-glow glow-top-left"></div>
      <div class="radial-glow glow-bottom-right"></div>

      <div class="dashboard-container">
        
        <!-- ==================== LEFT COLUMN ==================== -->
        <div class="main-column">
          
          <!-- Hero Section -->
          <div class="hero-section">
            <h1 class="hero-title">Katalog Riset</h1>
            <p class="hero-desc">
              Akses laporan riset mendalam untuk keputusan investasi yang lebih cerdas.
              <br/><br/>
              Semua laporan disusun oleh tim analis berpengalaman, berbasis data audited,
              metodologi terverifikasi, dan insight eksklusif dari AVENIR Research.
            </p>
          </div>

          <!-- Search & Filter Bar -->
          <div class="filter-box">
            <div class="search-input-wrapper">
              <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <input type="text" v-model="searchQuery" placeholder="Cari ticker, perusahaan, atau topik..." class="search-input" />
            </div>
            
            <div class="filters-row">
              <div class="filter-group">
                <label>Sektor</label>
                <select v-model="selectedSector">
                  <option v-for="opt in uniqueSectors" :key="opt" :value="opt">{{ opt }}</option>
                </select>
              </div>
              <div class="filter-group">
                <label>Rekomendasi</label>
                <select v-model="selectedRec">
                  <option v-for="opt in uniqueRecs" :key="opt" :value="opt">{{ opt }}</option>
                </select>
              </div>
              <div class="filter-group">
                <label>Tipe Laporan</label>
                <select v-model="selectedType">
                  <option v-for="opt in uniqueTypes" :key="opt" :value="opt">{{ opt }}</option>
                </select>
              </div>
              <div class="filter-group">
                <label>Analis</label>
                <select v-model="selectedAnalyst">
                  <option v-for="opt in uniqueAnalysts" :key="opt" :value="opt">{{ opt }}</option>
                </select>
              </div>
              <div class="filter-group">
                <label>Tahun</label>
                <select v-model="selectedYear">
                  <option v-for="opt in uniqueYears" :key="opt" :value="opt">{{ opt }}</option>
                </select>
              </div>
              <button class="reset-btn ml-auto" @click="resetFilters">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                Reset
              </button>
            </div>
          </div>

          <!-- Featured Report -->
          <div v-if="featuredReport && currentPage === 1" class="featured-section">
            <ResearchCard :data="featuredReport" :isFeatured="true" />
          </div>

          <!-- Grid Header -->
          <div class="grid-header">
            <div class="grid-title">
              <h2>Semua Laporan</h2>
              <span class="count-badge">{{ filteredResearches.length }} laporan</span>
            </div>
            <div class="grid-actions">
              <div class="sort-group">
                <label>Urutkan:</label>
                <select v-model="sortOrder">
                  <option value="Terbaru">Terbaru</option>
                  <option value="Terlama">Terlama</option>
                </select>
              </div>
              <div class="view-toggles">
                <button :class="['toggle-btn', viewMode === 'grid' ? 'active' : '']" @click="viewMode = 'grid'">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </button>
                <button :class="['toggle-btn', viewMode === 'list' ? 'active' : '']" @click="viewMode = 'list'">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Reports Grid -->
          <div v-if="paginatedReports.length > 0" :class="['reports-grid', viewMode === 'grid' ? 'is-grid' : 'is-list']">
            <ResearchCard 
              v-for="report in paginatedReports" 
              :key="report.id" 
              :data="report" 
            />
          </div>
          <div v-else class="empty-state">
             <div class="empty-icon">🔍</div>
             <h3>Laporan Tidak Ditemukan</h3>
             <p>Coba sesuaikan filter atau kata kunci pencarian Anda.</p>
             <button class="empty-btn" @click="resetFilters">Reset Filter</button>
          </div>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="pagination">
            <button class="page-nav" :disabled="currentPage === 1" @click="changePage(currentPage - 1)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
              Sebelumnya
            </button>
            
            <div class="page-numbers">
              <button 
                v-for="p in totalPages" 
                :key="p" 
                :class="['page-num', p === currentPage ? 'active' : '']"
                @click="changePage(p)"
              >
                {{ p }}
              </button>
            </div>

            <button class="page-nav" :disabled="currentPage === totalPages" @click="changePage(currentPage + 1)">
              Berikutnya
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
          </div>
          
          <div class="disclaimer mt-8 flex items-start gap-2 text-slate-500 text-xs border-t border-white/5 pt-4">
            <svg class="mt-0.5 shrink-0" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
            <p>Laporan ini disusun untuk tujuan informasi dan bukan merupakan rekomendasi jual atau beli saham. Seluruh data bersumber dari pihak terpercaya dan telah melalui proses verifikasi.</p>
          </div>

        </div>

        <!-- ==================== RIGHT COLUMN (SIDEBAR) ==================== -->
        <div class="sidebar-column">
          
          <!-- Stat Box 1 -->
          <div class="sidebar-box flex items-center gap-4">
            <div class="stat-icon-wrap text-emerald-400 bg-emerald-500/10">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <div>
              <div class="text-xs text-slate-400 font-medium">Total Laporan</div>
              <div class="text-2xl font-bold text-white my-0.5">{{ totalPremium }}</div>
              <div class="text-[10px] text-slate-500">Laporan premium tersedia</div>
            </div>
          </div>

          <!-- Stat Box 2 -->
          <div class="sidebar-box flex items-center gap-4">
            <div class="stat-icon-wrap text-emerald-400 bg-emerald-500/10">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div>
              <div class="text-xs text-slate-400 font-medium">Baru Bulan Ini</div>
              <div class="text-2xl font-bold text-white my-0.5">{{ newThisMonth }}</div>
              <div class="text-[10px] text-slate-500">Laporan terbaru diterbitkan</div>
            </div>
          </div>

          <!-- Top Sectors -->
          <div class="sidebar-box">
            <h3 class="sidebar-title">Sektor Terpopuler</h3>
            <div class="sectors-list mt-4 flex flex-col gap-3">
              <div v-for="sector in topSectors" :key="sector.name" class="sector-item">
                <div class="flex justify-between text-xs font-medium mb-1.5">
                  <span class="text-slate-300">{{ sector.name }}</span>
                  <span class="text-white">{{ sector.count }}</span>
                </div>
                <div class="progress-bg">
                  <div class="progress-bar" :style="`width: ${sector.percent}%`"></div>
                </div>
              </div>
            </div>
            <div class="mt-4 text-right">
               <button class="text-[10px] text-emerald-400 font-bold hover:text-emerald-300 transition-colors">Lihat semua sektor →</button>
            </div>
          </div>

          <!-- Benefits -->
          <div class="sidebar-box">
            <h3 class="sidebar-title">Manfaat Premium AVENIR</h3>
            <ul class="benefits-list mt-4 flex flex-col gap-3">
              <li>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Akses penuh seluruh laporan riset premium
              </li>
              <li>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Update harian & insight eksklusif analis
              </li>
              <li>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Model valuasi & data terverifikasi
              </li>
              <li>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Metodologi riset berstandar institusi
              </li>
              <li>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Diskon eksklusif untuk event & webinar
              </li>
            </ul>
            <Link href="/langganan" class="block w-full mt-5 bg-emerald-500 hover:bg-emerald-400 text-white text-center text-xs font-bold py-2.5 rounded-lg transition-colors">
              Lihat Paket Langganan →
            </Link>
          </div>

          <!-- Help Box -->
          <div class="sidebar-box border-none bg-transparent px-0 pb-0">
            <h3 class="sidebar-title text-base">Butuh Bantuan?</h3>
            <p class="text-xs text-slate-400 mt-2 mb-4 leading-relaxed">Hubungi tim kami untuk pertanyaan atau permintaan khusus.</p>
            <button class="w-full bg-[#111413] border border-white/10 hover:border-emerald-500/50 text-slate-300 text-xs font-bold py-2.5 rounded-lg transition-colors flex items-center justify-center gap-2">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              Hubungi Analis
            </button>
          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.katalog-dashboard {
  background-color: #090b0a;
  color: #cbd5e1;
  font-family: 'Roboto', sans-serif;
  min-height: 100vh;
  position: relative;
  overflow-x: hidden;
  padding: 40px 24px 80px;
}

/* Glowing Background Vectors */
.radial-glow {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  filter: blur(140px);
  z-index: 0;
  opacity: 0.6;
}
.glow-top-left {
  top: -100px;
  left: -100px;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, transparent 80%);
}
.glow-bottom-right {
  bottom: -150px;
  right: -150px;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.05) 0%, transparent 80%);
}

.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 32px;
  position: relative;
  z-index: 10;
}

@media (max-width: 1024px) {
  .dashboard-container {
    grid-template-columns: 1fr;
  }
  .sidebar-column {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
  }
}

/* --- Left Column --- */
.main-column {
  min-width: 0;
}

.hero-section {
  margin-bottom: 32px;
}
.hero-title {
  font-size: 36px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 12px;
}
.hero-desc {
  font-size: 14.5px;
  color: #94a3b8;
  line-height: 1.6;
  max-width: 700px;
}
.hero-desc br { margin-bottom: 8px; }

/* Filter Box */
.filter-box {
  background: #111413;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 32px;
}

.search-input-wrapper {
  position: relative;
  margin-bottom: 16px;
}
.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
}
.search-input {
  width: 100%;
  background: #090b0a;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #fff;
  padding: 12px 16px 12px 40px;
  border-radius: 8px;
  font-size: 13.5px;
  transition: border-color 0.2s;
}
.search-input:focus {
  outline: none;
  border-color: rgba(16, 185, 129, 0.5);
}

.filters-row {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  align-items: flex-end;
}
.filter-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.filter-group label {
  font-size: 10.5px;
  color: #64748b;
  font-weight: 600;
}
.filter-group select {
  background: #090b0a;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #cbd5e1;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 12px;
  min-width: 140px;
  cursor: pointer;
}
.filter-group select:focus {
  outline: none;
  border-color: rgba(16, 185, 129, 0.5);
}

.reset-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #94a3b8;
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}
.reset-btn:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #fff;
}

/* Featured Section */
.featured-section {
  margin-bottom: 40px;
}

/* Grid Header */
.grid-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 24px;
}
.grid-title {
  display: flex;
  align-items: center;
  gap: 12px;
}
.grid-title h2 {
  font-size: 20px;
  font-weight: 700;
  color: #fff;
}
.count-badge {
  font-size: 11px;
  color: #94a3b8;
}

.grid-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}
.sort-group {
  display: flex;
  align-items: center;
  gap: 8px;
}
.sort-group label {
  font-size: 11px;
  color: #64748b;
}
.sort-group select {
  background: transparent;
  border: none;
  color: #fff;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}
.sort-group select:focus { outline: none; }

.view-toggles {
  display: flex;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 6px;
  padding: 2px;
}
.toggle-btn {
  background: transparent;
  border: none;
  color: #64748b;
  padding: 6px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}
.toggle-btn.active {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
}

/* Reports Grid */
.reports-grid {
  display: grid;
  gap: 20px;
}
.reports-grid.is-grid {
  grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
}
.reports-grid.is-list {
  grid-template-columns: 1fr;
}

@media (max-width: 640px) {
  .reports-grid.is-grid {
    grid-template-columns: 1fr;
  }
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: #111413;
  border: 1px dashed rgba(255, 255, 255, 0.1);
  border-radius: 12px;
}
.empty-icon { font-size: 40px; margin-bottom: 16px; opacity: 0.5; }
.empty-state h3 { font-size: 18px; color: #fff; font-weight: 600; margin-bottom: 8px; }
.empty-state p { font-size: 13px; color: #94a3b8; margin-bottom: 20px; }
.empty-btn { background: #10b981; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer; }

/* Pagination */
.pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 40px;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}
.page-nav {
  display: flex;
  align-items: center;
  gap: 6px;
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: color 0.2s;
}
.page-nav:hover:not(:disabled) { color: #fff; }
.page-nav:disabled { opacity: 0.5; cursor: not-allowed; }

.page-numbers {
  display: flex;
  gap: 4px;
}
.page-num {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 6px;
  color: #cbd5e1;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.page-num:hover {
  border-color: rgba(255, 255, 255, 0.2);
}
.page-num.active {
  background: #10b981;
  border-color: #10b981;
  color: #fff;
}


/* --- Right Column (Sidebar) --- */
.sidebar-box {
  background: #111413;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
}

.stat-icon-wrap {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.sidebar-title {
  font-size: 14px;
  font-weight: 700;
  color: #fff;
}

.progress-bg {
  width: 100%;
  height: 4px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 2px;
  overflow: hidden;
}
.progress-bar {
  height: 100%;
  background: #10b981;
  border-radius: 2px;
}

.benefits-list li {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  font-size: 11.5px;
  color: #94a3b8;
  line-height: 1.5;
}
.benefits-list svg {
  color: #10b981;
  flex-shrink: 0;
  margin-top: 1px;
}
</style>
