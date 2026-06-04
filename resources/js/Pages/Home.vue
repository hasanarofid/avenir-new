<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { authStore } from '@/Stores/authStore';
</script>

<template>
  <Head title="Riset Saham Kelas Institusi" />
  <AppLayout>
    <div class="landing-dark-wrapper">
      
      <!-- HERO SECTION -->
      <section class="hero-section">
        <!-- Glow Backdrops -->
        <div class="radial-glow glow-top-right"></div>
        <div class="radial-glow glow-bottom-left"></div>

        <div class="hero-container">
          <!-- Left Column: Copy & Actions -->
          <div class="hero-content">
            <div class="hero-tag">
              <span class="hero-tag-dot"></span>
              RISET EKUITAS · LANGGANAN PROFESIONAL
            </div>
            
            <h1 class="hero-title">
              Riset Saham Kelas <br />
              <span class="text-green-glow">Institusi</span> untuk <br />
              <span class="text-green-glow">Investor Cerdas</span>
            </h1>
            
            <p class="hero-lead">
              Berlangganan untuk akses penuh ke katalog riset ekuitas mendalam dan artikel pasar yang ditulis analis bersertifikasi — semuanya berbasis Annual Report audited.
            </p>
            
            <div class="hero-cta-group">
              <button class="btn-primary-green" @click="authStore.open('register')">
                Berlangganan sekarang
              </button>
              <Link href="/katalog" class="btn-secondary-outline">
                Coba demo gratis
              </Link>
            </div>

            <!-- Mini Stats Grid -->
            <div class="hero-stats">
              <div class="stat-item">
                <span class="stat-num">13<span class="stat-unit"> Riset</span></span>
                <span class="stat-label">Di Katalog</span>
              </div>
              <div class="stat-item">
                <span class="stat-num">4<span class="stat-unit"> Tahun</span></span>
                <span class="stat-label">Data Audited</span>
              </div>
              <div class="stat-item">
                <span class="stat-num">DCF<span class="stat-unit">/FCFE</span></span>
                <span class="stat-label">Model Valuasi</span>
              </div>
              <div class="stat-item">
                <span class="stat-num">100<span class="stat-unit">%</span></span>
                <span class="stat-label">Annual Report</span>
              </div>
            </div>
          </div>

          <!-- Right Column: Premium Mockup Graphic -->
          <div class="hero-graphics">
            <div class="mockup-container">
              <img src="/images/hero_laptop.png" alt="Avenir Terminal Mockup" class="laptop-img" />
              <!-- Background stock chart animation decorative element -->
              <div class="chart-glow-vector">
                <svg viewBox="0 0 400 200" fill="none" class="chart-svg">
                  <path d="M10,180 L80,140 L150,160 L220,90 L290,110 L360,40" stroke="#22c55e" stroke-width="3" opacity="0.3" stroke-linecap="round"/>
                  <path d="M10,180 L80,140 L150,160 L220,90 L290,110 L360,40 L360,200 L10,200 Z" fill="url(#chart-grad)" opacity="0.05" />
                  <defs>
                    <linearGradient id="chart-grad" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stop-color="#22c55e" />
                      <stop offset="100%" stop-color="#22c55e" stop-opacity="0" />
                    </linearGradient>
                  </defs>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- RINGKASAN UTAMA (CATEGORY CARDS GRID) -->
      <section class="summary-section">
        <div class="section-container">
          <h2 class="section-title">Ringkasan Utama</h2>
          
          <div class="category-grid">
            <!-- Card 1: Makroekonomi -->
            <div class="category-card" @click="window.location.href='/katalog?cat=macro'">
              <div class="card-bg-overlay"></div>
              <!-- Custom SVG illustration representing macroeconomic growth -->
              <div class="card-art">
                <svg viewBox="0 0 100 100" class="art-svg-icon">
                  <path d="M20,80 L35,65 L50,70 L70,45 L85,30" stroke="rgba(34, 197, 94, 0.4)" stroke-width="2" fill="none"/>
                  <circle cx="85" cy="30" r="3" fill="#22c55e"/>
                  <line x1="20" y1="80" x2="80" y2="80" stroke="rgba(255,255,255,0.1)" stroke-width="1"/>
                  <line x1="20" y1="80" x2="20" y2="30" stroke="rgba(255,255,255,0.1)" stroke-width="1"/>
                </svg>
              </div>
              <div class="card-content">
                <span class="card-num">01</span>
                <h3 class="card-name">Makroekonomi</h3>
              </div>
            </div>

            <!-- Card 2: Sektor & Industri -->
            <div class="category-card" @click="window.location.href='/katalog?cat=sector'">
              <div class="card-bg-overlay"></div>
              <!-- Custom SVG representing sectors / heavy industry logistics -->
              <div class="card-art">
                <svg viewBox="0 0 100 100" class="art-svg-icon">
                  <rect x="25" y="55" width="12" height="25" fill="none" stroke="rgba(34, 197, 94, 0.3)" stroke-width="1.5"/>
                  <rect x="42" y="45" width="12" height="35" fill="none" stroke="rgba(34, 197, 94, 0.3)" stroke-width="1.5"/>
                  <rect x="59" y="35" width="12" height="45" fill="none" stroke="rgba(34, 197, 94, 0.3)" stroke-width="1.5"/>
                </svg>
              </div>
              <div class="card-content">
                <span class="card-num">02</span>
                <h3 class="card-name">Sektor & Industri</h3>
              </div>
            </div>

            <!-- Card 3: Komoditas -->
            <div class="category-card" @click="window.location.href='/katalog?cat=commodity'">
              <div class="card-bg-overlay"></div>
              <!-- Custom SVG representing materials/mining grid -->
              <div class="card-art">
                <svg viewBox="0 0 100 100" class="art-svg-icon">
                  <polygon points="50,25 75,40 75,70 50,85 25,70 25,40" fill="none" stroke="rgba(34, 197, 94, 0.3)" stroke-width="1.5"/>
                  <line x1="50" y1="25" x2="50" y2="85" stroke="rgba(34, 197, 94, 0.2)" stroke-width="1"/>
                  <line x1="25" y1="40" x2="75" y2="70" stroke="rgba(34, 197, 94, 0.2)" stroke-width="1"/>
                  <line x1="25" y1="70" x2="75" y2="40" stroke="rgba(34, 197, 94, 0.2)" stroke-width="1"/>
                </svg>
              </div>
              <div class="card-content">
                <span class="card-num">03</span>
                <h3 class="card-name">Komoditas</h3>
              </div>
            </div>

            <!-- Card 4: Teknologi & Inovasi -->
            <div class="category-card" @click="window.location.href='/katalog?cat=tech'">
              <div class="card-bg-overlay"></div>
              <!-- Custom SVG representing microchips / tech networks -->
              <div class="card-art">
                <svg viewBox="0 0 100 100" class="art-svg-icon">
                  <rect x="35" y="35" width="30" height="30" rx="3" fill="none" stroke="rgba(34, 197, 94, 0.4)" stroke-width="1.5"/>
                  <circle cx="50" cy="50" r="5" fill="#22c55e" opacity="0.5"/>
                  <line x1="50" y1="20" x2="50" y2="35" stroke="rgba(255,255,255,0.2)" stroke-width="1.5"/>
                  <line x1="50" y1="65" x2="50" y2="80" stroke="rgba(255,255,255,0.2)" stroke-width="1.5"/>
                  <line x1="20" y1="50" x2="35" y2="50" stroke="rgba(255,255,255,0.2)" stroke-width="1.5"/>
                  <line x1="65" y1="50" x2="80" y2="50" stroke="rgba(255,255,255,0.2)" stroke-width="1.5"/>
                </svg>
              </div>
              <div class="card-content">
                <span class="card-num">04</span>
                <h3 class="card-name">Teknologi & Inovasi</h3>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- FEATURED RESEARCH (DARK GLASSMORPHIC LAYOUT) -->
      <section class="featured-section">
        <div class="section-container">
          <div class="featured-eyebrow">◆ RISET PILIHAN MINGGU INI · GRATIS</div>
          
          <div class="featured-card-glass">
            <div class="feat-left">
              <div class="feat-badge">🎁 GRATIS — PROMOSI</div>
              <h2 class="feat-title">PT Sinar Mas Agro Resources & Technology</h2>
              <p class="feat-desc">
                CPO terintegrasi terbesar Indonesia. Q1-2026 Net Income melonjak +517% YoY. P/E 6.8x — undervalued vs peer regional.
              </p>
              
              <div class="feat-metrics">
                <div class="metric-box">
                  <span class="m-val">+517%</span>
                  <span class="m-lbl">YoY NI Q1-26</span>
                </div>
                <div class="metric-box">
                  <span class="m-val">6.8x</span>
                  <span class="m-lbl">P/E Ratio</span>
                </div>
                <div class="metric-box">
                  <span class="m-val">5.2x</span>
                  <span class="m-lbl">EV/EBITDA</span>
                </div>
              </div>
              
              <Link href="/katalog" class="btn-read-free">
                Baca Riset Gratis →
              </Link>
            </div>
            
            <div class="feat-right">
              <!-- Radial Dial representing Analyst Conviction -->
              <div class="radial-dial">
                <svg viewBox="0 0 200 200" class="dial-svg">
                  <circle cx="100" cy="100" r="80" fill="none" stroke="#1c201e" stroke-width="12"/>
                  <circle cx="100" cy="100" r="80" fill="none" stroke="url(#dial-grad)" stroke-width="12"
                          stroke-dasharray="502" stroke-dashoffset="75" stroke-linecap="round"
                          transform="rotate(-90 100 100)"/>
                  <defs>
                    <linearGradient id="dial-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" stop-color="#4ade80"/>
                      <stop offset="100%" stop-color="#22c55e"/>
                    </linearGradient>
                  </defs>
                </svg>
                <div class="dial-labels">
                  <span class="dial-number">85%</span>
                  <span class="dial-text">Conviction</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- WHY AVENIR (4 PILLARS IN DARK GRID) -->
      <section class="why-section">
        <div class="section-container">
          <div class="why-eyebrow">◆ MENGAPA AVENIR</div>
          <h2 class="why-title">Riset Ekuitas yang <span class="text-green-glow">Bekerja</span> untuk Investor</h2>
          <p class="why-lead">
            Lebih dari sekadar arsip riset. Avenir Research adalah platform langganan yang dibangun di atas tiga prinsip utama: data audited, metodologi multi-skenario, dan analis bersertifikasi.
          </p>

          <div class="why-grid">
            <div class="why-card">
              <span class="why-num">01</span>
              <h3>Data Audited, Bukan Kira-Kira</h3>
              <p>Setiap angka — dari revenue hingga DER — bersumber langsung dari Annual Report yang sudah diaudit dan laporan keuangan resmi IDX. Tidak ada estimasi liar, tidak ada halusinasi AI.</p>
              <div class="why-badge">Source-First Methodology</div>
            </div>
            
            <div class="why-card">
              <span class="why-num">02</span>
              <h3>Multi-Skenario, Bukan Single Target</h3>
              <p>Setiap riset menyajikan framework valuasi tiga skenario — Base Case, Bull Case, Bear Case. Anda mendapatkan range yang jujur, bukan angka tunggal yang menyesatkan.</p>
              <div class="why-badge">FCFE DCF Framework</div>
            </div>
            
            <div class="why-card">
              <span class="why-num">03</span>
              <h3>Ditulis Analis Bersertifikasi</h3>
              <p>Setiap riset dikerjakan oleh analis dengan sertifikasi profesional CFA, WPPE, atau setara — bukan algoritma scraping, bukan tipster Telegram. Kualitas riset terjamin secara profesional.</p>
              <div class="why-badge">Certified Analyst Network</div>
            </div>
            
            <div class="why-card">
              <span class="why-num">04</span>
              <h3>Akses Demokratis, Harga Terjangkau</h3>
              <p>Riset sell-side institusi biasa dijual ratusan juta per tahun ke klien besar saja. Avenir membuka akses penuh dengan langganan bersahabat agar ritel pun bisa berinvestasi layaknya institusi.</p>
              <div class="why-badge">Grandfathered Pricing</div>
            </div>
          </div>
        </div>
      </section>

      <!-- PRICING TIERS SECTION (DARK GLASSMORPHISM) -->
      <section class="pricing-section">
        <div class="section-container">
          <div class="pricing-header">
            <h2 class="pricing-title">Rencana Berlangganan</h2>
            <p class="pricing-lead">Pilih paket terbaik untuk kebutuhan riset investasi Anda. Semua paket diawali dengan trial 7 hari gratis.</p>
          </div>

          <div class="pricing-grid">
            <!-- Tier 1: 1 Bulan -->
            <div class="price-card">
              <div class="tier-trial">FREE TRIAL · 7 HARI</div>
              <h3 class="tier-name">1 Bulan</h3>
              <div class="tier-cost">
                <span class="t-currency">Rp</span>
                <span class="t-amount">149.000</span>
                <span class="t-period">/ bulan</span>
              </div>
              <button class="btn-tier" @click="authStore.open('register')">
                Daftar &amp; Coba Gratis
              </button>
              <div class="tier-foot">Dapat dibatalkan kapan saja</div>
            </div>

            <!-- Tier 2: 6 Bulan (Featured) -->
            <div class="price-card price-card-featured">
              <div class="featured-badge">NILAI TERBAIK</div>
              <div class="tier-trial">FREE TRIAL · 7 HARI</div>
              <h3 class="tier-name">6 Bulan</h3>
              <div class="tier-cost">
                <span class="t-currency">Rp</span>
                <span class="t-amount">729.000</span>
                <span class="t-period">/ 6 bulan</span>
              </div>
              <div class="tier-monthly-eq">≈ Rp 121.500 / bulan</div>
              <button class="btn-tier btn-tier-primary" @click="authStore.open('register')">
                Daftar &amp; Coba Gratis
              </button>
              <div class="tier-foot">Hemat Rp 165.000 · Diskon 18%</div>
            </div>

            <!-- Tier 3: 1 Tahun -->
            <div class="price-card">
              <div class="tier-trial">FREE TRIAL · 7 HARI</div>
              <h3 class="tier-name">1 Tahun</h3>
              <div class="tier-cost">
                <span class="t-currency">Rp</span>
                <span class="t-amount">1.289.000</span>
                <span class="t-period">/ tahun</span>
              </div>
              <div class="tier-monthly-eq">≈ Rp 107.500 / bulan</div>
              <button class="btn-tier" @click="authStore.open('register')">
                Daftar &amp; Coba Gratis
              </button>
              <div class="tier-foot">Hemat Rp 499.000 · Diskon 28%</div>
            </div>
          </div>

          <!-- Common features list -->
          <div class="pricing-foot">
            <h4 class="foot-title">Semua paket sudah termasuk:</h4>
            <div class="foot-grid">
              <div class="foot-feature"><span>✓</span> Akses penuh ke seluruh katalog riset ekuitas</div>
              <div class="foot-feature"><span>✓</span> Market news harian dari tim analis</div>
              <div class="foot-feature"><span>✓</span> Artikel corporate history &amp; sector deep dives</div>
              <div class="foot-feature"><span>✓</span> Update otomatis setiap rilis laporan keuangan kuartalan</div>
              <div class="foot-feature"><span>✓</span> Notifikasi instan via dashboard &amp; email</div>
              <div class="foot-feature"><span>✓</span> 7 hari uji coba gratis tanpa kewajiban bayar di muka</div>
            </div>
          </div>
        </div>
      </section>

      <!-- FINAL CALL TO ACTION -->
      <section class="final-cta-section">
        <div class="section-container">
          <div class="cta-box-glass">
            <h2>Siap Mengambil Keputusan Investasi Lebih Cerdas?</h2>
            <p>Daftar sekarang secara gratis, nikmati 7 hari trial penuh ke seluruh dokumen analisis fundamental Avenir.</p>
            <div class="cta-actions">
              <button class="btn-primary-green" @click="authStore.open('register')">
                Daftar Gratis
              </button>
              <Link href="/katalog" class="btn-secondary-outline">
                Lihat Katalog Riset
              </Link>
            </div>
          </div>
        </div>
      </section>

    </div>
  </AppLayout>
</template>

<style scoped>
/* GENERAL RESET & THEME STYLINGS */
.landing-dark-wrapper {
  background-color: #090b0a;
  color: #d1d5db;
  font-family: 'DM Sans', 'Inter', sans-serif;
  min-height: 100vh;
  overflow-x: hidden;
  position: relative;
}

/* Typography styles */
h1, h2, h3, h4 {
  font-family: 'Space Grotesk', 'Inter', sans-serif;
  color: #ffffff;
}

/* Glowing Background Vectors */
.radial-glow {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  filter: blur(140px);
  z-index: 0;
  opacity: 0.7;
}
.glow-top-right {
  top: -100px;
  right: -100px;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(34, 197, 94, 0.15) 0%, rgba(9, 11, 10, 0) 70%);
}
.glow-bottom-left {
  bottom: 10%,;
  left: -200px;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(34, 197, 94, 0.08) 0%, rgba(9, 11, 10, 0) 70%);
}

.text-green-glow {
  color: #22c55e;
  text-shadow: 0 0 20px rgba(34, 197, 94, 0.2);
}

.section-container {
  max-width: 1120px;
  margin: 0 auto;
  padding: 0 24px;
  position: relative;
  z-index: 10;
}

/* HERO SECTION */
.hero-section {
  position: relative;
  padding: 80px 0 60px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}
.hero-container {
  max-width: 1120px;
  margin: 0 auto;
  padding: 0 24px;
  display: grid;
  grid-template-cols: 1fr;
  gap: 60px;
  align-items: center;
  position: relative;
  z-index: 10;
}
@media (min-width: 900px) {
  .hero-container {
    grid-template-columns: 1.1fr 0.9fr;
  }
}
.hero-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 14px;
  border-radius: 50px;
  background: rgba(34, 197, 94, 0.08);
  border: 1px solid rgba(34, 197, 94, 0.2);
  color: #4ade80;
  font-family: 'JetBrains Mono', monospace;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.02em;
  margin-bottom: 24px;
}
.hero-tag-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: #22c55e;
  box-shadow: 0 0 10px #22c55e;
  animation: pulse 2s infinite;
}
.hero-title {
  font-size: 42px;
  line-height: 1.15;
  font-weight: 700;
  letter-spacing: -0.02em;
  margin-bottom: 20px;
}
@media (min-width: 600px) {
  .hero-title {
    font-size: 54px;
  }
}
.hero-lead {
  font-size: 16px;
  line-height: 1.6;
  color: #9ca3af;
  max-width: 540px;
  margin-bottom: 32px;
}
.hero-cta-group {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 48px;
}
.btn-primary-green {
  padding: 12px 28px;
  border-radius: 50px;
  background-color: #22c55e;
  color: #ffffff;
  font-weight: 700;
  font-size: 14px;
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 20px rgba(34, 197, 94, 0.3);
  transition: all 0.2s ease-in-out;
}
.btn-primary-green:hover {
  background-color: #16a34a;
  transform: translateY(-2px);
  box-shadow: 0 6px 24px rgba(34, 197, 94, 0.45);
}
.btn-secondary-outline {
  padding: 12px 28px;
  border-radius: 50px;
  background-color: transparent;
  color: #ffffff;
  font-weight: 600;
  font-size: 14px;
  border: 1px solid rgba(255, 255, 255, 0.15);
  cursor: pointer;
  text-decoration: none;
  text-align: center;
  transition: all 0.2s ease-in-out;
}
.btn-secondary-outline:hover {
  border-color: rgba(255, 255, 255, 0.4);
  background-color: rgba(255, 255, 255, 0.05);
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  max-width: 480px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  padding-top: 28px;
}
@media (min-width: 450px) {
  .hero-stats {
    grid-template-columns: repeat(4, 1fr);
  }
}
.stat-item {
  display: flex;
  flex-col: column;
}
.stat-num {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: #ffffff;
  display: block;
}
.stat-unit {
  font-size: 12px;
  color: #22c55e;
  font-weight: 600;
  margin-left: 2px;
}
.stat-label {
  font-size: 11px;
  color: #6b7280;
  margin-top: 4px;
}

/* Laptop mockup styles */
.hero-graphics {
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
}
.mockup-container {
  width: 100%;
  max-width: 450px;
  position: relative;
  z-index: 10;
}
.laptop-img {
  width: 100%;
  height: auto;
  display: block;
  object-fit: contain;
  filter: drop-shadow(0 20px 50px rgba(0,0,0,0.6));
}
.chart-glow-vector {
  position: absolute;
  bottom: -15px;
  right: -30px;
  width: 80%;
  z-index: -1;
  pointer-events: none;
}
.chart-svg {
  width: 100%;
  height: auto;
}

/* SUMMARY SECTION (4 CATEGORY CARDS) */
.summary-section {
  padding: 80px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}
.section-title {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 40px;
  letter-spacing: -0.01em;
}
.category-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
}
@media (min-width: 480px) {
  .category-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (min-width: 900px) {
  .category-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}
.category-card {
  position: relative;
  background-color: #111413;
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 16px;
  padding: 24px;
  height: 240px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.category-card:hover {
  border-color: rgba(34, 197, 94, 0.3);
  transform: translateY(-4px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5), 0 0 20px rgba(34, 197, 94, 0.05);
}
.card-bg-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(9, 11, 10, 0) 50%, rgba(9, 11, 10, 0.8) 100%);
  z-index: 1;
}
.card-art {
  position: absolute;
  top: 24px;
  right: 24px;
  width: 100px;
  height: 100px;
  opacity: 0.65;
  transition: all 0.3s ease;
}
.category-card:hover .card-art {
  transform: scale(1.08) translate(-4px, 4px);
  opacity: 0.9;
}
.art-svg-icon {
  width: 100%;
  height: 100%;
}
.card-content {
  position: relative;
  z-index: 5;
}
.card-num {
  font-family: 'JetBrains Mono', monospace;
  font-size: 13px;
  color: #22c55e;
  font-weight: 600;
  display: block;
  margin-bottom: 6px;
}
.card-name {
  font-size: 18px;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
  letter-spacing: -0.01em;
}

/* FEATURED SECTION (GLASSMORPHIC CARD) */
.featured-section {
  padding: 80px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}
.featured-eyebrow {
  font-family: 'JetBrains Mono', monospace;
  font-size: 11px;
  color: #6b7280;
  letter-spacing: 0.08em;
  margin-bottom: 24px;
}
.featured-card-glass {
  background: linear-gradient(145deg, rgba(255,255,255,0.02) 0%, rgba(255,255,255,0.01) 100%);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 20px;
  padding: 40px;
  display: grid;
  grid-template-columns: 1fr;
  gap: 40px;
  align-items: center;
  backdrop-filter: blur(8px);
}
@media (min-width: 800px) {
  .featured-card-glass {
    grid-template-columns: 1.2fr 0.8fr;
  }
}
.feat-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 4px;
  background-color: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.2);
  color: #4ade80;
  font-family: 'JetBrains Mono', monospace;
  font-size: 10px;
  font-weight: 700;
  margin-bottom: 20px;
}
.feat-title {
  font-size: 28px;
  font-weight: 700;
  line-height: 1.25;
  margin-bottom: 16px;
  letter-spacing: -0.01em;
}
.feat-desc {
  font-size: 15px;
  line-height: 1.6;
  color: #9ca3af;
  margin-bottom: 28px;
}
.feat-metrics {
  display: flex;
  flex-wrap: wrap;
  gap: 32px;
  margin-bottom: 36px;
}
.metric-box {
  display: flex;
  flex-direction: column;
}
.m-val {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 24px;
  font-weight: 700;
  color: #ffffff;
}
.m-lbl {
  font-size: 11px;
  color: #6b7280;
  margin-top: 4px;
}
.btn-read-free {
  display: inline-block;
  padding: 10px 24px;
  background-color: transparent;
  border: 1px solid rgba(255,255,255,0.15);
  color: #ffffff;
  font-size: 13px;
  font-weight: 600;
  border-radius: 50px;
  text-decoration: none;
  transition: all 0.2s ease;
}
.btn-read-free:hover {
  border-color: #22c55e;
  background-color: rgba(34, 197, 94, 0.05);
  color: #4ade80;
}

/* Conviction Radial Dial */
.feat-right {
  display: flex;
  justify-content: center;
  align-items: center;
}
.radial-dial {
  position: relative;
  width: 180px;
  height: 180px;
}
.dial-svg {
  width: 100%;
  height: 100%;
}
.dial-labels {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}
.dial-number {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 32px;
  font-weight: 700;
  color: #22c55e;
  line-height: 1.1;
}
.dial-text {
  font-size: 11px;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-top: 4px;
}

/* WHY SECTION (4 PILLARS) */
.why-section {
  padding: 80px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}
.why-eyebrow {
  font-family: 'JetBrains Mono', monospace;
  font-size: 11px;
  color: #6b7280;
  letter-spacing: 0.08em;
  margin-bottom: 20px;
}
.why-title {
  font-size: 32px;
  font-weight: 700;
  margin-bottom: 16px;
  letter-spacing: -0.01em;
}
.why-lead {
  font-size: 16px;
  line-height: 1.6;
  color: #9ca3af;
  max-width: 680px;
  margin-bottom: 48px;
}
.why-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 24px;
}
@media (min-width: 600px) {
  .why-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (min-width: 1000px) {
  .why-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}
.why-card {
  background-color: #111413;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 32px 24px;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  transition: all 0.2s ease;
}
.why-card:hover {
  border-color: rgba(255,255,255,0.1);
  background-color: #141817;
}
.why-num {
  font-family: 'JetBrains Mono', monospace;
  font-size: 14px;
  color: #6b7280;
  font-weight: 600;
  display: block;
  margin-bottom: 16px;
}
.why-card h3 {
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 12px;
}
.why-card p {
  font-size: 13.5px;
  line-height: 1.6;
  color: #9ca3af;
  margin-bottom: 24px;
  flex-grow: 1;
}
.why-badge {
  align-self: flex-start;
  font-family: 'JetBrains Mono', monospace;
  font-size: 10px;
  color: #4ade80;
  background: rgba(34, 197, 94, 0.06);
  border: 1px solid rgba(34, 197, 94, 0.15);
  padding: 4px 10px;
  border-radius: 4px;
  font-weight: 600;
}

/* PRICING SECTION */
.pricing-section {
  padding: 80px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}
.pricing-header {
  text-align: center;
  margin-bottom: 60px;
}
.pricing-title {
  font-size: 32px;
  font-weight: 700;
  margin-bottom: 14px;
}
.pricing-lead {
  font-size: 15px;
  color: #9ca3af;
  max-width: 500px;
  margin: 0 auto;
}
.pricing-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 24px;
  align-items: center;
  margin-bottom: 60px;
}
@media (min-width: 700px) {
  .pricing-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (min-width: 1000px) {
  .pricing-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}
.price-card {
  background-color: #111413;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 40px 32px;
  display: flex;
  flex-direction: column;
  position: relative;
  transition: all 0.2s ease;
}
.price-card:hover {
  border-color: rgba(255,255,255,0.08);
}
.tier-trial {
  font-family: 'JetBrains Mono', monospace;
  font-size: 10px;
  font-weight: 700;
  color: #6b7280;
  letter-spacing: 0.04em;
  margin-bottom: 16px;
}
.tier-name {
  font-size: 22px;
  font-weight: 700;
  margin-bottom: 24px;
}
.tier-cost {
  display: flex;
  align-items: baseline;
  margin-bottom: 8px;
}
.t-currency {
  font-size: 16px;
  font-weight: 600;
  color: #6b7280;
}
.t-amount {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 40px;
  font-weight: 700;
  color: #ffffff;
  margin: 0 4px;
}
.t-period {
  font-size: 13px;
  color: #6b7280;
}
.tier-monthly-eq {
  font-size: 12px;
  color: #22c55e;
  font-weight: 500;
  margin-bottom: 24px;
}
.btn-tier {
  width: 100%;
  padding: 12px;
  border-radius: 50px;
  border: 1px solid rgba(255, 255, 255, 0.15);
  background-color: transparent;
  color: #ffffff;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  margin-bottom: 16px;
  transition: all 0.2s ease;
}
.btn-tier:hover {
  border-color: rgba(255,255,255,0.3);
  background-color: rgba(255,255,255,0.05);
}
.tier-foot {
  font-size: 11px;
  color: #6b7280;
  text-align: center;
}

/* Featured Price Card Override */
.price-card-featured {
  background-color: #121815;
  border-color: rgba(34, 197, 94, 0.25);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
}
@media (min-width: 1000px) {
  .price-card-featured {
    transform: scale(1.04);
    z-index: 10;
  }
}
.price-card-featured:hover {
  border-color: rgba(34, 197, 94, 0.4);
}
.featured-badge {
  position: absolute;
  top: -12px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #22c55e;
  color: #ffffff;
  font-size: 9px;
  font-weight: 800;
  padding: 4px 14px;
  border-radius: 50px;
  letter-spacing: 0.06em;
  box-shadow: 0 4px 10px rgba(34, 197, 94, 0.3);
}
.price-card-featured .tier-trial {
  color: #4ade80;
}
.btn-tier-primary {
  background-color: #22c55e;
  border: none;
  color: #ffffff;
  font-weight: 700;
  box-shadow: 0 4px 15px rgba(34, 197, 94, 0.2);
}
.btn-tier-primary:hover {
  background-color: #16a34a;
  transform: scale(1.02);
  box-shadow: 0 6px 20px rgba(34, 197, 94, 0.35);
}

/* Common Features List below cards */
.pricing-foot {
  max-width: 800px;
  margin: 0 auto;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  padding-top: 48px;
}
.foot-title {
  font-size: 15px;
  font-weight: 600;
  margin-bottom: 24px;
  text-align: center;
  color: #9ca3af;
}
.foot-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
}
@media (min-width: 550px) {
  .foot-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
.foot-feature {
  font-size: 13.5px;
  color: #9ca3af;
  display: flex;
  align-items: center;
  gap: 10px;
}
.foot-feature span {
  color: #22c55e;
  font-weight: 700;
}

/* FINAL CALL TO ACTION */
.final-cta-section {
  padding: 80px 0 100px 0;
}
.cta-box-glass {
  background: linear-gradient(145deg, rgba(255,255,255,0.02) 0%, rgba(255,255,255,0.01) 100%);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 24px;
  padding: 60px 40px;
  text-align: center;
  backdrop-filter: blur(8px);
}
.cta-box-glass h2 {
  font-size: 32px;
  font-weight: 700;
  margin-bottom: 16px;
  letter-spacing: -0.01em;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}
.cta-box-glass p {
  font-size: 15px;
  line-height: 1.6;
  color: #9ca3af;
  max-width: 500px;
  margin: 0 auto 36px auto;
}
.cta-actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 16px;
}

/* ANIMATIONS */
@keyframes pulse {
  0% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
  }
  70% {
    transform: scale(1);
    box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
  }
  100% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
  }
}
</style>
