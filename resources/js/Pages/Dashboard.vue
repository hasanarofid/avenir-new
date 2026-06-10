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
.katalog-light-wrapper {
  background-color: #F7F5F0;
  color: #18211D;
  font-family: 'Segoe UI', Arial, 'Helvetica Neue', sans-serif;
  min-height: 100vh;
  position: relative;
  overflow-x: hidden;
  padding-top: 0px;
}

/* Glowing Background Vectors - adjusted for light theme */
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
  background: radial-gradient(circle, rgba(27, 107, 58, 0.05) 0%, transparent 80%);
}

.glow-bottom-left {
  bottom: -150px;
  left: -150px;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(27, 107, 58, 0.04) 0%, transparent 80%);
}

/* HERO SECTION */
.idx-hero {
  padding: 64px 28px 48px;
  text-align: center;
  background: linear-gradient(160deg, #EEF7F2 0%, #F7F5F0 60%, #F0EDE8 100%);
  border-bottom: 1px solid #DDE7E2;
  position: relative;
  overflow: hidden;
  margin-bottom: 36px;
}

.idx-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  opacity: 0.04;
  background-image: radial-gradient(circle, #1B6B3A 1px, transparent 1px);
  background-size: 28px 28px;
}

.idx-tag {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-size: 9.5px;
  font-weight: 700;
  color: #1B6B3A;
  letter-spacing: 2px;
  text-transform: uppercase;
  background: rgba(27, 107, 58, 0.08);
  border: 1px solid rgba(27, 107, 58, 0.2);
  padding: 4px 13px;
  border-radius: 100px;
  margin-bottom: 14px;
  position: relative;
}

.dot {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: #1B6B3A;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.3; }
}

.idx-hero h1 {
  font-family: Georgia, 'Times New Roman', serif;
  font-size: clamp(28px, 5vw, 46px);
  font-weight: 700;
  margin: 12px 0 16px 0;
  color: #18211D;
  position: relative;
}

.hl {
  color: #1B6B3A;
}

.idx-hero p {
  font-size: 14.5px;
  color: #4A6355;
  max-width: 560px;
  margin: 0 auto 28px;
  position: relative;
  line-height: 1.7;
}

.idx-stats {
  display: inline-flex;
  gap: 28px;
  flex-wrap: wrap;
  justify-content: center;
  padding: 14px 28px;
  background: #fff;
  border: 1.5px solid #DDE7E2;
  border-radius: 12px;
  box-shadow: 0 2px 16px rgba(0,0,0,.07);
  position: relative;
  max-width: none;
}

.stat-card {
  text-align: center;
  padding: 0;
  border: none;
  background: transparent;
  backdrop-filter: none;
}

.stat-val {
  font-family: 'Courier New', monospace;
  font-size: 22px;
  font-weight: 700;
  color: #1B6B3A;
}

.stat-lbl {
  font-size: 9.5px;
  color: #8EA899;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  margin-top: 2px;
  font-weight: 600;
}

/* FILTER BAR */
.filter-bar {
  max-width: 1120px;
  margin: 0 auto 36px;
  padding: 0 28px;
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  position: relative;
  z-index: 10;
}

.filter-lbl {
  font-size: 10.5px;
  color: #8EA899;
  font-weight: 600;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  margin-right: 4px;
}

.fbtn {
  padding: 5px 15px;
  border-radius: 100px;
  border: 1.5px solid #DDE7E2;
  background: #fff;
  color: #4A6355;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.18s;
  font-family: 'Segoe UI', Arial, sans-serif;
  box-shadow: 0 1px 3px rgba(0,0,0,.05);
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.fbtn:hover {
  background: #1B6B3A;
  border-color: #1B6B3A;
  color: #fff;
  box-shadow: 0 2px 8px rgba(27,107,58,.3);
}

.fbtn.active {
  background: #1B6B3A;
  border-color: #1B6B3A;
  color: #fff;
  box-shadow: 0 2px 8px rgba(27,107,58,.3);
}

.fbtn-mine {
  border-color: rgba(234, 179, 8, 0.3);
  color: #b45309;
}

.fbtn-mine:hover {
  background: #eab308;
  border-color: #eab308;
  color: #fff;
  box-shadow: 0 2px 8px rgba(234,179,8,.3);
}

.fbtn-mine.active {
  background: #eab308;
  border-color: #eab308;
  color: #fff;
  box-shadow: 0 2px 8px rgba(234,179,8,.3);
}

.mine-count {
  font-size: 11px;
  background: rgba(255, 255, 255, 0.3);
  padding: 1px 6px;
  border-radius: 20px;
  margin-left: 2px;
}

/* GRID LAYOUT */
.grid-container {
  max-width: 1120px;
  margin: 0 auto;
  padding: 0 28px 80px;
  position: relative;
  z-index: 10;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
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
  background: #fff;
  border: 1.5px solid #DDE7E2;
  border-radius: 14px;
  padding: 56px 24px;
  text-align: center;
  max-width: 480px;
  margin: 20px auto 40px;
  box-shadow: 0 2px 16px rgba(0,0,0,.07);
}

.empty-icon {
  font-size: 40px;
  margin-bottom: 16px;
}

.empty-state h3 {
  font-family: Georgia, 'Times New Roman', serif;
  font-size: 20px;
  font-weight: 700;
  color: #18211D;
  margin: 0 0 10px 0;
}

.empty-state p {
  font-size: 13.5px;
  color: #4A6355;
  margin: 0 0 24px 0;
  line-height: 1.6;
}

.empty-btn {
  background: #1B6B3A;
  border: none;
  color: #ffffff;
  font-size: 13px;
  font-weight: 600;
  padding: 10px 24px;
  border-radius: 30px;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(27, 107, 58, 0.3);
}

.empty-btn:hover {
  background: #114523;
  box-shadow: 0 4px 12px rgba(27, 107, 58, 0.4);
}
</style>
