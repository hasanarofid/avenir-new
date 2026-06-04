<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PropertyCard from '@/Components/PropertyCard.vue';
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

const selectedSector = ref('all');

// Extract unique sectors from researches
const uniqueSectors = computed(() => {
    const sectors = props.researches
        .map(r => r.sector)
        .filter(Boolean)
        .map(s => s.trim());
    return [...new Set(sectors)];
});

// Filter researches based on selected filter
const filteredResearches = computed(() => {
    if (selectedSector.value === 'all') {
        return props.researches;
    }
    if (selectedSector.value === 'mine') {
        return props.researches.filter(r => r.ticker && props.unlockedTickers.includes(r.ticker));
    }
    return props.researches.filter(r => r.sector && r.sector.trim() === selectedSector.value);
});

// Get count for "Riset Saya"
const mineCount = computed(() => {
    return props.researches.filter(r => r.ticker && props.unlockedTickers.includes(r.ticker)).length;
});

const selectSector = (sector) => {
    selectedSector.value = sector;
};
</script>

<template>
  <Head title="Katalog Riset — Avenir Research" />
  <AppLayout>
    <div class="katalog-dark-wrapper">
      <!-- Glow Backdrops -->
      <div class="radial-glow glow-top-right"></div>
      <div class="radial-glow glow-bottom-left"></div>

      <!-- HERO SECTION -->
      <div class="idx-hero">
        <div class="idx-tag"><span class="dot"></span>AVENIR RESEARCH · EQUITY ANALYSIS</div>
        <h1>Riset &amp; <span class="hl">Publikasi</span></h1>
        <p>Laporan riset ekuitas mendalam berbasis Annual Report audited. Data terverifikasi, valuasi FCFE tiga skenario.</p>
        
        <div class="idx-stats">
          <div class="stat-card">
            <div class="stat-val">{{ researches.length }}</div>
            <div class="stat-lbl">Riset Tersedia</div>
          </div>
          <div class="stat-card">
            <div class="stat-val">4 Tahun</div>
            <div class="stat-lbl">Data Historis</div>
          </div>
          <div class="stat-card">
            <div class="stat-val">FCFE</div>
            <div class="stat-lbl">Model Valuasi</div>
          </div>
          <div class="stat-card">
            <div class="stat-val">100%</div>
            <div class="stat-lbl">AR Audited</div>
          </div>
        </div>
      </div>

      <!-- FILTER BAR -->
      <div class="filter-bar">
        <span class="filter-lbl">Filter:</span>
        <button 
          class="fbtn" 
          :class="{ active: selectedSector === 'all' }"
          @click="selectSector('all')"
        >
          Semua
        </button>
        <button 
          class="fbtn fbtn-mine" 
          :class="{ active: selectedSector === 'mine' }"
          @click="selectSector('mine')"
          id="filter-mine"
        >
          ⭐ Riset Saya 
          <span v-if="user" class="mine-count">{{ mineCount }}</span>
        </button>
        <button 
          v-for="sector in uniqueSectors" 
          :key="sector"
          class="fbtn" 
          :class="{ active: selectedSector === sector }"
          @click="selectSector(sector)"
          v-html="sector"
        ></button>
      </div>

      <!-- MAIN GRID -->
      <div class="grid-container">
        <transition-group name="fade-grid" tag="div" class="grid" v-if="filteredResearches.length > 0">
          <PropertyCard 
            v-for="research in filteredResearches" 
            :key="research.id" 
            :data="research" 
          />
        </transition-group>

        <!-- EMPTY STATE HANDLERS -->
        <div v-else class="empty-state">
          <!-- Guest User clicking Riset Saya -->
          <div v-if="selectedSector === 'mine' && !user">
            <div class="empty-icon">🔒</div>
            <h3>Akses Riset Saya</h3>
            <p>Silakan masuk ke akun Anda untuk melihat daftar riset yang telah Anda beli atau buka.</p>
            <button class="empty-btn" @click="authStore.open('login')">
              Sign In Sekarang
            </button>
          </div>
          
          <!-- Logged In User having no unlocked researches -->
          <div v-else-if="selectedSector === 'mine' && user">
            <div class="empty-icon">⭐</div>
            <h3>Belum Ada Riset</h3>
            <p>Anda belum memiliki riset yang dibeli atau dibuka. Jelajahi katalog kami untuk memulai analisis saham pilihan Anda.</p>
            <button class="empty-btn" @click="selectSector('all')">
              Lihat Semua Riset
            </button>
          </div>

          <!-- General empty results -->
          <div v-else>
            <div class="empty-icon">🔍</div>
            <h3>Riset Tidak Ditemukan</h3>
            <p>Maaf, tidak ada laporan riset yang cocok untuk kategori yang dipilih saat ini.</p>
            <button class="empty-btn" @click="selectSector('all')">
              Kembali ke Semua
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>


.katalog-dark-wrapper {
  background-color: #090b0a;
  color: #cbd5e1;
  font-family: 'Inter', sans-serif;
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
  z-index: 0;
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

/* HERO SECTION */
.idx-hero {
  max-width: 1120px;
  margin: 0 auto;
  padding: 40px 24px 48px;
  text-align: center;
  position: relative;
  z-index: 10;
}

.idx-tag {
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

.dot {
  width: 6px;
  height: 6px;
  background-color: #22c55e;
  border-radius: 50%;
  box-shadow: 0 0 8px #22c55e;
  display: inline-block;
}

.idx-hero h1 {
  font-family: 'Inter', sans-serif;
  font-size: 38px;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 16px 0;
  letter-spacing: -0.02em;
}

.hl {
  color: #22c55e;
  text-shadow: 0 0 20px rgba(34, 197, 94, 0.2);
}

.idx-hero p {
  font-size: 15px;
  color: #94a3b8;
  max-width: 580px;
  margin: 0 auto 36px;
  line-height: 1.7;
}

.idx-stats {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  max-width: 720px;
  margin: 0 auto;
}

@media (min-width: 640px) {
  .idx-stats {
    grid-template-columns: repeat(4, 1fr);
  }
}

.stat-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 16px;
  text-align: center;
  transition: all 0.3s ease;
  backdrop-filter: blur(8px);
}

.stat-card:hover {
  background: rgba(34, 197, 94, 0.02);
  border-color: rgba(34, 197, 94, 0.20);
  transform: translateY(-2px);
}

.stat-val {
  font-family: 'Inter', sans-serif;
  font-size: 22px;
  font-weight: 700;
  color: #22c55e;
}

.stat-lbl {
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  margin-top: 4px;
  letter-spacing: 0.05em;
}

/* FILTER BAR */
.filter-bar {
  max-width: 1120px;
  margin: 0 auto 36px;
  padding: 0 24px;
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  position: relative;
  z-index: 10;
}

.filter-lbl {
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-right: 8px;
}

.fbtn {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: #cbd5e1;
  font-size: 12.5px;
  font-weight: 600;
  padding: 7px 16px;
  border-radius: 30px;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.fbtn:hover {
  border-color: rgba(34, 197, 94, 0.35);
  color: #ffffff;
}

.fbtn.active {
  background: #22c55e;
  border-color: #22c55e;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}

.fbtn-mine {
  border-color: rgba(234, 179, 8, 0.2);
}

.fbtn-mine.active {
  background: #eab308;
  border-color: #eab308;
  box-shadow: 0 4px 12px rgba(234, 179, 8, 0.3);
}

.mine-count {
  font-size: 11px;
  background: rgba(0, 0, 0, 0.2);
  padding: 1px 6px;
  border-radius: 20px;
  margin-left: 2px;
}

/* GRID LAYOUT */
.grid-container {
  max-width: 1120px;
  margin: 0 auto;
  padding: 0 24px 80px;
  position: relative;
  z-index: 10;
}

.grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 24px;
}

@media (min-width: 640px) {
  .grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* TRANSITION ANIMATIONS */
.fade-grid-enter-active,
.fade-grid-leave-active {
  transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.fade-grid-enter-from {
  opacity: 0;
  transform: translateY(15px);
}

.fade-grid-leave-to {
  opacity: 0;
  transform: translateY(-15px);
}

.fade-grid-move {
  transition: transform 0.4s ease;
}

/* EMPTY STATES */
.empty-state {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 16px;
  padding: 56px 24px;
  text-align: center;
  max-width: 480px;
  margin: 20px auto 40px;
  backdrop-filter: blur(8px);
}

.empty-icon {
  font-size: 40px;
  margin-bottom: 16px;
}

.empty-state h3 {
  font-family: 'Inter', sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 10px 0;
}

.empty-state p {
  font-size: 13.5px;
  color: #94a3b8;
  margin: 0 0 24px 0;
  line-height: 1.6;
}

.empty-btn {
  background: #22c55e;
  border: none;
  color: #ffffff;
  font-size: 13px;
  font-weight: 700;
  padding: 10px 24px;
  border-radius: 30px;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.25);
}

.empty-btn:hover {
  background: #16a34a;
  box-shadow: 0 4px 16px rgba(34, 197, 94, 0.4);
}
</style>
