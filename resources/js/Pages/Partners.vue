<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    partners: Array
});

// Fallback mock data if database has no verified partners yet
const mockPartners = [
    {
        name: 'Hasan Arofid',
        certification: 'CFA, WPPE',
        specializations: ['Mining', 'Commodities', 'Corporate Action']
    },
    {
        name: 'Faisal Ahmad',
        certification: 'WPEE',
        specializations: ['Consumer Goods', 'Banking', 'Valuation']
    },
    {
        name: 'Diana Eka',
        certification: 'CFA charterholder',
        specializations: ['Tech & EV', 'US-listed stocks', 'Macroeconomic']
    },
    {
        name: 'Rian Pratama',
        certification: 'WPPE',
        specializations: ['Infrastructure', 'Telco', 'Bioethanol']
    }
];

const displayedPartners = computed(() => {
    if (props.partners && props.partners.length > 0) {
        return props.partners;
    }
    return mockPartners;
});

const TIER_PCT = [25, 18, 14, 11, 9, 7, 6, 5, 3, 2];
const TIER_VIEWS = [312, 284, 241, 198, 175, 142, 118, 97, 82, 71];
const TIER_BAGIAN = [
    'Rp 745.000', 'Rp 536.400', 'Rp 417.200', 'Rp 327.800', 'Rp 268.200',
    'Rp 208.600', 'Rp 178.800', 'Rp 149.000', 'Rp 89.400', 'Rp 59.600'
];
</script>

<template>
  <Head title="Mitra Analis" />

  <AppLayout>
    <div class="partners-dark-wrapper">
      <!-- Glow Backdrops -->
      <div class="radial-glow glow-top-right"></div>
      <div class="radial-glow glow-bottom-left"></div>

      <div class="partners-page">
        <!-- Hero Section -->
        <div class="partners-hero">
          <div class="hero-eyebrow">◆ PROGRAM MITRA ANALIS</div>
          <h1>Tulis Riset, Naik <span class="hl">Peringkat</span>, Dapat Bagian Pool</h1>
          <p>
            Bergabunglah sebagai Mitra Analis Avenir Research. Submit riset Anda, capai peringkat views tertinggi, dan dapatkan bagian dari pool bulanan — yang dibentuk dari 20% revenue subscription dan dibagi berdasarkan peringkat performa riset Anda.
          </p>
        </div>

        <!-- Formula Section -->
        <div class="partners-formula">
          <div class="formula-header">💰 Model Pool & Pembagian Ranking</div>
          <div class="formula-equation">
            <strong>Pool Mitra Bulanan</strong> = 20% × Total Revenue Subscription<br>
            <strong>Bagian Mitra</strong> = Persentase Ranking × Pool Mitra Bulanan
          </div>
          <p class="formula-explain">
            Setiap akhir bulan, 20% dari total revenue subscription dialokasikan ke <strong>pool mitra</strong>. Dari pool tersebut, hanya <strong>Top 10 mitra dengan views terbanyak</strong> yang dapat bagian — masing-masing dengan persentase berdasarkan peringkat. Bukan proporsional views, melainkan <strong>peringkat</strong>: yang nomor 1 dapat bagian paling besar tanpa harus punya selisih views besar dengan nomor 2. Pembayaran via transfer bank tiap awal bulan berikutnya.
          </p>

          <div class="formula-tier-box">
            <div class="tier-box-title">Persentase Pool per Peringkat</div>
            <div class="tier-grid">
              <div v-for="(pct, idx) in TIER_PCT" :key="idx" class="tier-card" :class="'rank-' + (idx + 1)">
                <div class="rank-lbl">RANK #{{ idx + 1 }}</div>
                <div class="rank-pct">{{ pct }}%</div>
              </div>
            </div>
            <div class="tier-note">
              Total 100% dari pool. Peringkat di-reset setiap bulan — setiap mitra punya kesempatan baru tiap awal periode.
            </div>
          </div>
        </div>

        <!-- Steps Section -->
        <div class="section-divider">CARA BERGABUNG &amp; WORKFLOW</div>
        <div class="partners-steps">
          <div class="step-card">
            <div class="step-num">01</div>
            <h3>Daftar &amp; Verifikasi</h3>
            <p>Submit form pendaftaran lengkap dengan sertifikasi (CFA, WPPE, WPEE, atau setara) dan link portfolio. Tim editorial review dalam 5–7 hari kerja.</p>
          </div>
          <div class="step-card">
            <div class="step-num">02</div>
            <h3>Tulis &amp; Submit</h3>
            <p>Tulis riset menggunakan template Avenir Research. Kirim file (Word/PDF) via email ke admin: <strong>mitra@avenirfortuna.com</strong></p>
          </div>
          <div class="step-card">
            <div class="step-num">03</div>
            <h3>Review Editorial</h3>
            <p>Tim editorial Avenir review metodologi, data, dan format. Mitra revisi sesuai feedback. Setelah approved, riset dijadwalkan deploy.</p>
          </div>
          <div class="step-card">
            <div class="step-num">04</div>
            <h3>Avenir Deploy</h3>
            <p>Tim Avenir HTML-kan riset dan publikasikan ke website dengan byline mitra. Anda track performa di dashboard mitra.</p>
          </div>
        </div>

        <!-- Simulation Section -->
        <div class="partners-simulation">
          <h2>📊 Contoh Perhitungan Pendapatan Bulanan</h2>
          <p>
            Asumsi: <strong>100 subscriber</strong> × Rp 149.000 = <strong>Rp 14.900.000</strong> revenue → Pool 20% = <strong>Rp 2.980.000</strong>. Berikut simulasi distribusi ke peringkat Top 10:
          </p>
          <div class="table-container">
            <table class="simulation-table">
              <thead>
                <tr>
                  <th>Peringkat</th>
                  <th>Mitra</th>
                  <th>Views (contoh)</th>
                  <th class="text-right">% Pool</th>
                  <th class="text-right">Bagian</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="i in 10" :key="i">
                  <td><strong>#{{ i }}</strong></td>
                  <td>{{ displayedPartners[i-1] ? displayedPartners[i-1].name : 'Slot tersedia' }}</td>
                  <td class="mono">{{ TIER_VIEWS[i-1] }}</td>
                  <td class="mono text-right font-green">{{ TIER_PCT[i-1] }}%</td>
                  <td class="mono text-right font-green">{{ TIER_BAGIAN[i-1] }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="simulation-note">
            <strong>Catatan:</strong> Nama-nama di atas adalah <strong>mitra terverifikasi</strong> yang sudah aktif di Avenir Research. Angka views, persentase pool, dan nominal bagian adalah <strong>contoh ilustrasi</strong> untuk menunjukkan mekanisme distribusi — bukan data aktual. Peringkat sesungguhnya akan ditentukan oleh views aktual setiap bulan setelah subscription berbayar aktif.
          </div>
        </div>

        <!-- Features / Advantages Section -->
        <div class="section-divider">KENAPA JADI MITRA AVENIR?</div>
        <div class="partners-features">
          <div class="feature-card">
            <div class="feat-icon">📈</div>
            <h3>Skala Audience</h3>
            <p>Riset Anda dibaca oleh komunitas analis dan investor serius yang berlangganan platform.</p>
          </div>
          <div class="feature-card">
            <div class="feat-icon">🎯</div>
            <h3>Fokus Analisis</h3>
            <p>Anda fokus tulis riset bagus. Tim Avenir handle deploy, editing, hosting, dan retention.</p>
          </div>
          <div class="feature-card">
            <div class="feat-icon">📊</div>
            <h3>Dashboard Real-time</h3>
            <p>Pantau views per riset, estimasi pendapatan bulanan, dan tren performa di dashboard mitra.</p>
          </div>
          <div class="feature-card">
            <div class="feat-icon">🤝</div>
            <h3>Editorial Support</h3>
            <p>Tim editor kasih feedback metodologi dan format. Riset Anda jadi lebih tajam.</p>
          </div>
          <div class="feature-card">
            <div class="feat-icon">🏷️</div>
            <h3>Byline &amp; Brand</h3>
            <p>Setiap riset Anda tampil dengan nama, foto, dan bio Anda. Bangun personal brand.</p>
          </div>
          <div class="feature-card">
            <div class="feat-icon">⚖️</div>
            <h3>Transparan &amp; Fair</h3>
            <p>Formula publik. Setiap views terlacak. Pembayaran rutin dengan rincian lengkap.</p>
          </div>
        </div>

        <!-- Verified Partners List -->
        <div class="verified-section">
          <div class="verified-header">
            <div class="verified-eyebrow">✓ MITRA TERVERIFIKASI</div>
            <h2>Tim Analis Avenir Research</h2>
            <p>Para analis profesional yang sudah lolos verifikasi tim editorial dan aktif menulis riset di Avenir Research.</p>
          </div>

          <div class="verified-grid">
            <div 
              v-for="(mitra, index) in displayedPartners" 
              :key="index"
              class="verified-card"
            >
              <div class="verified-badge">✓ Verified</div>
              <div class="avatar-fallback">{{ (mitra.name || 'M').charAt(0).toUpperCase() }}</div>
              <div class="verified-name">{{ mitra.name }}</div>
              <div class="verified-cert" v-if="mitra.certification">{{ mitra.certification }}</div>
              <div class="verified-spec">
                <span 
                  v-for="(spec, sIdx) in (mitra.specializations || ['Multi-sektor'])" 
                  :key="sIdx"
                  class="spec-tag"
                >
                  {{ spec }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Join CTA Box -->
        <div class="join-cta-box">
          <h2>Siap Bergabung sebagai Mitra?</h2>
          <p>Daftarkan diri Anda hari ini. Tim editorial akan review aplikasi dan menghubungi Anda dalam 5-7 hari kerja.</p>
          <a href="mailto:mitra@avenirfortuna.com" class="join-btn">Daftar Sekarang via Email</a>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap');

.partners-dark-wrapper {
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

.partners-page {
  max-width: 900px;
  margin: 0 auto;
  padding: 40px 24px 100px;
  position: relative;
  z-index: 2;
}

.partners-hero {
  text-align: center;
  margin-bottom: 56px;
}

.hero-eyebrow {
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

.partners-hero h1 {
  font-family: 'Sora', sans-serif;
  font-size: clamp(28px, 5vw, 46px);
  font-weight: 700;
  line-height: 1.15;
  color: #ffffff;
  margin-bottom: 20px;
}

.partners-hero h1 .hl {
  color: #10b981;
  background: linear-gradient(120deg, #10b981 0%, #34d399 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.partners-hero p {
  font-size: 15.5px;
  line-height: 1.7;
  color: #94a3b8;
  max-width: 750px;
  margin: 0 auto;
}

.partners-formula {
  background: #111513;
  border: 1px solid rgba(16, 185, 129, 0.2);
  border-radius: 20px;
  padding: 32px;
  margin-bottom: 56px;
}

.formula-header {
  font-family: 'Sora', sans-serif;
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.1em;
  color: #10b981;
  text-transform: uppercase;
  margin-bottom: 16px;
}

.formula-equation {
  font-family: 'IBM Plex Mono', monospace;
  font-size: 14.5px;
  line-height: 2;
  color: #f8fafc;
  padding: 20px;
  background: #090b0a;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.03);
  margin-bottom: 20px;
}

.formula-equation strong {
  color: #10b981;
}

.formula-explain {
  font-size: 14px;
  line-height: 1.65;
  color: #94a3b8;
  margin-bottom: 32px;
}

.formula-tier-box {
  background: #0c0e0d;
  border-radius: 12px;
  padding: 24px;
}

.tier-box-title {
  font-family: 'Sora', sans-serif;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  color: #64748b;
  text-transform: uppercase;
  margin-bottom: 16px;
}

.tier-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 12px;
}

@media (max-width: 640px) {
  .tier-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.tier-card {
  background: #111513;
  border: 1px solid rgba(255, 255, 255, 0.03);
  padding: 14px 10px;
  border-radius: 8px;
  text-align: center;
}

.tier-card.rank-1 {
  background: rgba(16, 185, 129, 0.15);
  border-color: rgba(16, 185, 129, 0.3);
}

.tier-card.rank-2, .tier-card.rank-3 {
  background: rgba(16, 185, 129, 0.08);
}

.rank-lbl {
  font-size: 9px;
  font-weight: 700;
  color: #64748b;
  margin-bottom: 4px;
}

.rank-pct {
  font-family: 'Sora', sans-serif;
  font-size: 18px;
  font-weight: 700;
  color: #ffffff;
}

.tier-card.rank-1 .rank-pct {
  color: #10b981;
}

.tier-note {
  font-size: 12px;
  color: #64748b;
  margin-top: 14px;
  line-height: 1.5;
}

.section-divider {
  font-family: 'Sora', sans-serif;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.14em;
  color: #64748b;
  text-transform: uppercase;
  margin: 56px 0 32px;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 20px;
}

.section-divider::before, .section-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: rgba(255, 255, 255, 0.05);
}

.partners-steps {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 56px;
}

@media (max-width: 768px) {
  .partners-steps {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .partners-steps {
    grid-template-columns: 1fr;
  }
}

.step-card {
  background: #121614;
  border: 1px solid rgba(255, 255, 255, 0.03);
  border-radius: 12px;
  padding: 24px 20px;
}

.step-num {
  font-family: 'IBM Plex Mono', monospace;
  font-size: 12px;
  font-weight: 700;
  background: #10b981;
  color: #090b0a;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.step-card h3 {
  font-family: 'Sora', sans-serif;
  font-size: 17px;
  font-weight: 600;
  color: #ffffff;
  margin-bottom: 10px;
}

.step-card p {
  font-size: 12.5px;
  line-height: 1.55;
  color: #94a3b8;
}

.partners-simulation {
  background: #121614;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 32px;
  margin-bottom: 56px;
}

.partners-simulation h2 {
  font-family: 'Sora', sans-serif;
  font-size: 22px;
  font-weight: 600;
  color: #ffffff;
  margin-bottom: 12px;
}

.partners-simulation p {
  font-size: 14px;
  color: #94a3b8;
  line-height: 1.6;
  margin-bottom: 24px;
}

.table-container {
  overflow-x: auto;
  margin-bottom: 16px;
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.03);
}

.simulation-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13.5px;
  text-align: left;
}

.simulation-table th {
  background: #090b0a;
  color: #64748b;
  font-family: 'Sora', sans-serif;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  padding: 12px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.simulation-table td {
  padding: 12px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.02);
  color: #cbd5e1;
}

.simulation-table tr:last-child td {
  border-bottom: none;
}

.simulation-table tr:nth-child(even) td {
  background: rgba(255, 255, 255, 0.01);
}

.mono {
  font-family: 'IBM Plex Mono', monospace;
}

.text-right {
  text-align: right;
}

.font-green {
  color: #10b981;
  font-weight: 600;
}

.simulation-note {
  font-size: 12px;
  color: #64748b;
  line-height: 1.6;
}

.partners-features {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 56px;
}

@media (max-width: 768px) {
  .partners-features {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .partners-features {
    grid-template-columns: 1fr;
  }
}

.feature-card {
  background: #121614;
  border: 1px solid rgba(255, 255, 255, 0.03);
  border-radius: 12px;
  padding: 24px 20px;
}

.feat-icon {
  font-size: 24px;
  margin-bottom: 12px;
}

.feature-card h3 {
  font-family: 'Sora', sans-serif;
  font-size: 17px;
  font-weight: 600;
  color: #ffffff;
  margin-bottom: 8px;
}

.feature-card p {
  font-size: 12.5px;
  line-height: 1.55;
  color: #94a3b8;
}

/* Verified Partners */
.verified-section {
  background: #121614;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 20px;
  padding: 40px 32px;
  margin-bottom: 56px;
}

.verified-header {
  text-align: center;
  margin-bottom: 40px;
}

.verified-eyebrow {
  font-family: 'Sora', sans-serif;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.1em;
  color: #10b981;
  margin-bottom: 12px;
}

.verified-header h2 {
  font-family: 'Sora', sans-serif;
  font-size: 26px;
  font-weight: 600;
  color: #ffffff;
  margin-bottom: 10px;
}

.verified-header p {
  font-size: 14px;
  color: #94a3b8;
  max-width: 600px;
  margin: 0 auto;
}

.verified-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 20px;
}

.verified-card {
  background: #090b0a;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 14px;
  padding: 24px 20px;
  text-align: center;
  position: relative;
  transition: transform 0.2s, border-color 0.2s;
}

.verified-card:hover {
  transform: translateY(-2px);
  border-color: rgba(16, 185, 129, 0.3);
}

.verified-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  font-size: 9px;
  font-weight: 700;
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
  padding: 3px 8px;
  border-radius: 50px;
}

.avatar-fallback {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  margin: 0 auto 16px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #ffffff;
  font-family: 'Sora', sans-serif;
  font-size: 28px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid rgba(255, 255, 255, 0.05);
}

.verified-name {
  font-family: 'Sora', sans-serif;
  font-size: 18px;
  font-weight: 600;
  color: #ffffff;
  margin-bottom: 4px;
}

.verified-cert {
  font-size: 11px;
  font-weight: 700;
  color: #10b981;
  letter-spacing: 0.05em;
  background: rgba(16, 185, 129, 0.08);
  padding: 4px 10px;
  border-radius: 6px;
  display: inline-block;
  margin-bottom: 12px;
}

.verified-spec {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  justify-content: center;
}

.spec-tag {
  font-size: 10.5px;
  padding: 4px 8px;
  background: #111513;
  color: #94a3b8;
  border-radius: 4px;
  border: 1px solid rgba(255, 255, 255, 0.03);
}

/* Join CTA */
.join-cta-box {
  text-align: center;
  padding: 48px 32px;
  background: linear-gradient(135deg, #10b981 0%, #047857 100%);
  border-radius: 20px;
  color: #090b0a;
}

.join-cta-box h2 {
  font-family: 'Sora', sans-serif;
  font-size: clamp(24px, 4vw, 32px);
  font-weight: 700;
  margin-bottom: 12px;
}

.join-cta-box p {
  font-size: 15px;
  line-height: 1.6;
  margin-bottom: 24px;
  max-width: 500px;
  margin-left: auto;
  margin-right: auto;
  opacity: 0.9;
}

.join-btn {
  display: inline-block;
  background: #090b0a;
  color: #ffffff;
  padding: 14px 32px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  text-decoration: none;
  transition: all 0.2s;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
}

.join-btn:hover {
  transform: translateY(-2px);
  background: #111513;
}
</style>
