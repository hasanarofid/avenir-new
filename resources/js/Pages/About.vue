<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Tentang Kami" />

  <AppLayout>
    <div class="about-dark-wrapper">
      <!-- Glow Backdrops -->
      <div class="radial-glow glow-top-right"></div>
      <div class="radial-glow glow-bottom-left"></div>

      <div class="about-page">
        <!-- Header -->
        <div class="about-header">
          <div class="about-tag">◆ TENTANG KAMI</div>
          <h1>Riset Ekuitas yang <span class="hl">Jujur</span> dan Terverifikasi</h1>
          <p class="about-lead">
            Avenir Research adalah divisi riset independen PT Avenir Fortuna Corporindo. Kami memproduksi laporan riset ekuitas mendalam untuk emiten-emiten Indonesia — berbasis data laporan keuangan audited, tanpa rekomendasi harga.
          </p>
        </div>

        <!-- Misi & Value Cards -->
        <div class="about-grid">
          <div class="about-card">
            <div class="card-icon">🎯</div>
            <h3>Misi Kami</h3>
            <p>Menyediakan analisis fundamental berkualitas institusi yang dapat diakses oleh semua kalangan investor — mulai dari ritel hingga fund manager.</p>
          </div>
          <div class="about-card">
            <div class="card-icon">📋</div>
            <h3>Metodologi</h3>
            <p>Setiap riset berbasis Annual Report audited dan laporan keuangan resmi IDX. Tidak ada data yang dikira-kira. Valuasi menggunakan FCFE DCF tiga skenario.</p>
          </div>
          <div class="about-card">
            <div class="card-icon">⚖️</div>
            <h3>Independen</h3>
            <p>Kami tidak menerima bayaran dari emiten yang kami teliti. Tidak ada conflict of interest. Tidak ada rekomendasi beli/jual.</p>
          </div>
          <div class="about-card">
            <div class="card-icon">💎</div>
            <h3>Model Langganan</h3>
            <p>Akses penuh ke seluruh katalog riset dan artikel hanya dengan satu langganan bulanan. Tanpa beli per-laporan, tanpa biaya tersembunyi — semua riset baru langsung tersedia begitu rilis.</p>
          </div>
        </div>

        <!-- Stats Bar -->
        <div class="about-stats-box">
          <div class="stats-num">12</div>
          <div class="stats-text">
            <strong>Riset Aktif Saat Ini</strong>
            <p>Mencakup 8 sektor utama: Mining, Consumer, Banking, Technology, Telco, Manufacturing, Agribusiness, dan US-listed stocks.</p>
          </div>
        </div>

        <!-- Call to Action -->
        <div class="about-cta-box">
          <h2>Mulai Jelajahi Riset</h2>
          <p>
            Dapatkan akses penuh ke seluruh katalog riset dan artikel dengan <strong>7 hari trial gratis</strong> untuk akun baru, lalu Rp 149.000/bulan.
          </p>
          <Link href="/katalog" class="about-cta-btn">Lihat Semua Riset →</Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>


.about-dark-wrapper {
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
  filter: blur(140px);
  pointer-events: none;
  z-index: 1;
}
.glow-top-right {
  top: -10%;
  right: -10%;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, rgba(9, 11, 10, 0) 70%);
}
.glow-bottom-left {
  bottom: -10%;
  left: -10%;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(52, 211, 153, 0.06) 0%, rgba(9, 11, 10, 0) 70%);
}

.about-page {
  max-width: 800px;
  margin: 0 auto;
  padding: 40px 24px 100px;
  position: relative;
  z-index: 2;
}

.about-header {
  text-align: center;
  margin-bottom: 56px;
}

.about-tag {
  display: inline-block;
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.12em;
  padding: 6px 14px;
  border-radius: 50px;
  margin-bottom: 20px;
}

.about-header h1 {
  font-family: 'Inter', sans-serif;
  font-size: clamp(28px, 5vw, 44px);
  font-weight: 700;
  line-height: 1.15;
  color: #ffffff;
  margin-bottom: 20px;
}

.about-header h1 .hl {
  color: #10b981;
  background: linear-gradient(120deg, #10b981 0%, #34d399 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.about-lead {
  font-size: 16px;
  line-height: 1.75;
  color: #94a3b8;
  max-width: 700px;
  margin: 0 auto;
}

.about-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  margin-bottom: 48px;
}

@media (max-width: 640px) {
  .about-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
}

.about-card {
  background: #121614;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 28px;
  transition: border-color 0.2s, transform 0.2s;
}

.about-card:hover {
  border-color: rgba(16, 185, 129, 0.2);
  transform: translateY(-2px);
}

.card-icon {
  font-size: 24px;
  margin-bottom: 16px;
}

.about-card h3 {
  font-family: 'Inter', sans-serif;
  font-size: 18px;
  font-weight: 600;
  color: #ffffff;
  margin-bottom: 8px;
}

.about-card p {
  font-size: 13.5px;
  line-height: 1.6;
  color: #94a3b8;
}

.about-stats-box {
  display: flex;
  align-items: center;
  gap: 24px;
  background: rgba(16, 185, 129, 0.05);
  border: 1px solid rgba(16, 185, 129, 0.15);
  border-radius: 16px;
  padding: 24px 28px;
  margin-bottom: 56px;
}

@media (max-width: 640px) {
  .about-stats-box {
    flex-direction: column;
    text-align: center;
    gap: 16px;
  }
}

.stats-num {
  font-family: 'Inter', sans-serif;
  font-size: 54px;
  font-weight: 700;
  color: #10b981;
  line-height: 1;
}

.stats-text strong {
  font-family: 'Inter', sans-serif;
  font-size: 16px;
  color: #ffffff;
  display: block;
  margin-bottom: 4px;
}

.stats-text p {
  font-size: 13.5px;
  color: #94a3b8;
  line-height: 1.5;
}

.about-cta-box {
  text-align: center;
  padding: 44px 32px;
  background: linear-gradient(135deg, #111513 0%, #171d1a 100%);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 20px;
}

.about-cta-box h2 {
  font-family: 'Inter', sans-serif;
  font-size: 24px;
  font-weight: 600;
  color: #ffffff;
  margin-bottom: 12px;
}

.about-cta-box p {
  font-size: 14.5px;
  color: #94a3b8;
  line-height: 1.6;
  margin-bottom: 24px;
}

.about-cta-btn {
  display: inline-block;
  background: #10b981;
  color: #090b0a;
  padding: 12px 28px;
  border-radius: 50px;
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.2s;
  box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
}

.about-cta-btn:hover {
  background: #34d399;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}
</style>
