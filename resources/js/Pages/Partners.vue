<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    contributors: {
        type: Array,
        default: () => []
    }
});

function handleDaftarClick() {
  router.visit(route('mitra.register'));
}
</script>

<template>
  <Head title="Contributor Research | Avenir" />

  <AppLayout>
    <div class="contributor-page-wrapper">
      <div class="radial-glow glow-top"></div>
      
      <div class="contributor-container">
        
        <!-- Hero Section -->
        <section class="hero-section">
          <div class="hero-content">
            <div class="hero-eyebrow">AVENIR RESEARCH CONTRIBUTOR NETWORK</div>
            <h1>Jadi <span class="hl">Contributor Research</span> di Avenir</h1>
            <p>
              Terbitkan riset, paper, dan artikel analitis berbasis data melalui ekosistem Avenir Research. Bantu investor memahami pasar lebih dalam dengan insight yang berkualitas dan dapat diandalkan.
            </p>
            <div class="hero-actions">
              <button @click="handleDaftarClick" class="btn-primary">Daftar Sebagai Contributor &rarr;</button>
              <a href="#registered-contributors" class="btn-outline">Lihat Contributor Terdaftar &darr;</a>
            </div>
            
            <div class="hero-social-proof">
              <div class="avatars-group">
                <img src="/images/avatar-1.jpg" alt="Avatar" class="avatar-sm" onerror="this.src='https://ui-avatars.com/api/?name=A&background=10b981&color=fff'" />
                <img src="/images/avatar-2.jpg" alt="Avatar" class="avatar-sm" onerror="this.src='https://ui-avatars.com/api/?name=B&background=047857&color=fff'" />
                <img src="/images/avatar-3.jpg" alt="Avatar" class="avatar-sm" onerror="this.src='https://ui-avatars.com/api/?name=C&background=065f46&color=fff'" />
              </div>
              <div class="proof-text">
                Bergabung bersama analis independen, investment club, dan penulis pasar modal lainnya dalam ekosistem riset premium.
              </div>
            </div>
          </div>
          
          <div class="hero-visual">
            <div class="dashboard-mockup">
                <div class="mock-header">
                    <div class="mock-logo"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg> Contributor Workspace</div>
                </div>
                <div class="mock-body">
                    <div class="mock-sidebar">
                        <div class="mock-nav-item active">Dashboard</div>
                        <div class="mock-nav-item">Katalog Riset</div>
                        <div class="mock-nav-item">Artikel Premium</div>
                        <div class="mock-nav-item">Kinerja Saya</div>
                        <div class="mock-nav-item">Draft</div>
                        <div class="mock-nav-item">Analitik</div>
                        <div class="mock-btn-submit">Buat Konten</div>
                    </div>
                    <div class="mock-main">
                        <div class="mock-top-cards">
                            <div class="mock-card lg">
                                <div class="mock-card-header">Katalog Riset <span class="mock-link">Lihat Semua &rarr;</span></div>
                                <div class="mock-grid-3">
                                    <div class="mock-mini-card">
                                        <div class="mock-img img-1"></div>
                                        <div class="mock-mini-title">BBCA Company Deep Dive</div>
                                        <div class="mock-mini-date">15 Nov 2024</div>
                                    </div>
                                    <div class="mock-mini-card">
                                        <div class="mock-img img-2"></div>
                                        <div class="mock-mini-title">Banking Sector Outlook 2024</div>
                                        <div class="mock-mini-date">10 Nov 2024</div>
                                    </div>
                                    <div class="mock-mini-card">
                                        <div class="mock-img img-3"></div>
                                        <div class="mock-mini-title">ASII Valuation Update</div>
                                        <div class="mock-mini-date">5 Nov 2024</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mock-card md">
                                <div class="mock-profile-header">
                                    <div class="mock-avatar">A</div>
                                    <div>
                                        <div class="mock-name">Contributor Research</div>
                                        <div class="mock-badge">✓ Aktif</div>
                                    </div>
                                </div>
                                <div class="mock-stats">
                                    <div class="mock-stat-item">
                                        <div class="mock-stat-val">12</div>
                                        <div class="mock-stat-lbl">Konten Terbit</div>
                                    </div>
                                    <div class="mock-stat-item">
                                        <div class="mock-stat-val">3</div>
                                        <div class="mock-stat-lbl">Katalog Riset</div>
                                    </div>
                                    <div class="mock-stat-item">
                                        <div class="mock-stat-val">4</div>
                                        <div class="mock-stat-lbl">Artikel Premium</div>
                                    </div>
                                </div>
                                <div class="mock-premium-box">
                                    <div class="mock-premium-title">🔒 Akses Premium</div>
                                    <div class="mock-premium-desc">Konten Anda hanya dapat diakses oleh user berlangganan premium.</div>
                                    <div class="mock-premium-tag">Premium Subscriber Access</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
          </div>
        </section>

        <!-- Feature Strip -->
        <section class="feature-strip">
          <div class="feature-item">
            <div class="feat-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></div>
            <div class="feat-text">
              <h4>Katalog Riset Premium</h4>
              <p>Riset mendalam, paper, dan analisis komprehensif berbasis data.</p>
            </div>
          </div>
          <div class="feature-item">
            <div class="feat-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></div>
            <div class="feat-text">
              <h4>Artikel Analisis</h4>
              <p>Insight pasar, emiten, sektor, makro, dan tema investasi.</p>
            </div>
          </div>
          <div class="feature-item">
            <div class="feat-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></div>
            <div class="feat-text">
              <h4>Contributor Network</h4>
              <p>Jaringan analis dan penulis yang berkontribusi untuk ekosistem Avenir.</p>
            </div>
          </div>
          <div class="feature-item">
            <div class="feat-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg></div>
            <div class="feat-text">
              <h4>Hanya untuk Subscriber</h4>
              <p>Konten hanya dapat diakses oleh user berlangganan premium.</p>
            </div>
          </div>
        </section>

        <!-- Apa itu Contributor Research -->
        <section class="about-contributor">
          <div class="about-text">
            <h2>Apa itu <br/>Contributor Research?</h2>
            <p>
              Contributor Research adalah program Avenir untuk analis independen, penulis pasar modal, investment club, dan contributor yang ingin menerbitkan konten berkualitas untuk pembaca premium.
            </p>
            <p>
              Setiap kontribusi membantu memperluas coverage emiten, sektor, dan tema pasar modal di Indonesia.
            </p>
          </div>
          <div class="about-cards">
            <div class="feature-box">
              <h3>Katalog Riset</h3>
              <p>Ruang untuk riset yang lebih komprehensif dan mendalam.</p>
              <div class="tags">
                <span class="tag">Equity Research</span>
                <span class="tag">Company Deep Dive</span>
                <span class="tag">Sector Deep Dive</span>
                <span class="tag">Valuation Paper</span>
                <span class="tag">Scenario Analysis</span>
                <span class="tag">Corporate Action</span>
              </div>
              <a href="/katalog" class="card-link">Lihat Katalog Riset &rarr;</a>
            </div>
            <div class="feature-box secondary">
              <h3>Artikel Premium</h3>
              <p>Kanal untuk artikel analitis berbasis data yang relevan untuk investor.</p>
              <div class="tags">
                <span class="tag">Artikel Emiten</span>
                <span class="tag">Artikel Sektor</span>
                <span class="tag">Macro Market</span>
                <span class="tag">Public Expose</span>
                <span class="tag">Keterbukaan Informasi</span>
                <span class="tag">Edukasi Riset</span>
              </div>
              <a href="/artikel" class="card-link">Lihat Artikel &rarr;</a>
            </div>
          </div>
        </section>

        <!-- Cara Kerja -->
        <section class="how-it-works">
          <h2>Cara Kerja Contributor Research?</h2>
          <div class="steps-container">
            <div class="step-line"></div>
            <div class="step-item">
              <div class="step-circle">1</div>
              <div class="step-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></div>
              <h4>Daftar</h4>
              <p>Lengkapi profil, pengalaman, dan sample riset atau tulisanmu.</p>
            </div>
            <div class="step-item">
              <div class="step-circle">2</div>
              <div class="step-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg></div>
              <h4>Dapatkan Akses</h4>
              <p>Akunmu akan diverifikasi dan mendapat akses Contributor Workspace.</p>
            </div>
            <div class="step-item">
              <div class="step-circle">3</div>
              <div class="step-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg></div>
              <h4>Pilih Kanal</h4>
              <p>Tentukan apakah kontenmu untuk Katalog Riset atau Artikel Premium.</p>
            </div>
            <div class="step-item">
              <div class="step-circle">4</div>
              <div class="step-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg></div>
              <h4>Tulis & Lengkapi</h4>
              <p>Tulis dan lengkapi metadata, sumber, serta disclosure sesuai standar Avenir.</p>
            </div>
            <div class="step-item">
              <div class="step-circle">5</div>
              <div class="step-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg></div>
              <h4>Publish Konten</h4>
              <p>Kontenmu melalui proses review dan kemudian diterbitkan ke ekosistem Avenir.</p>
            </div>
            <div class="step-item">
              <div class="step-circle">6</div>
              <div class="step-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg></div>
              <h4>Bangun Track Record</h4>
              <p>Kontribusi berkualitas akan membangun reputasi dan eksposur di platform.</p>
            </div>
          </div>
        </section>

        <!-- Program Apresiasi -->
        <section class="apresiasi-program">
          <div class="apresiasi-header">
            <div class="ap-title-box">
                <h2>Program Apresiasi Contributor</h2>
                <p>
                Avenir memiliki program apresiasi untuk Contributor Research berdasarkan kontribusi konten terhadap ekosistem premium Avenir.
                </p>
                <p>
                Sistem ini tidak berbentuk gaji tetap dan tidak dihitung dari jumlah artikel semata.
                </p>
                <div class="apresiasi-disclaimer">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                Detail mekanisme apresiasi mengikuti kebijakan internal Avenir dan dapat diperbarui sesuai perkembangan platform.
                </div>
            </div>
            
            <div class="apresiasi-cards">
                <div class="apresiasi-card">
                <div class="apresiasi-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></div>
                <h4>Berbasis Engagement Premium</h4>
                <p>Dinilai dari interaksi user premium seperti pembacaan, penyimpanan, akses paper, dan aktivitas bernilai lainnya.</p>
                </div>
                <div class="apresiasi-card">
                <div class="apresiasi-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg></div>
                <h4>Bukan Gaji Tetap</h4>
                <p>Apresiasi bukan fixed salary dan bukan fee per artikel. Diberikan berdasarkan kontribusi aktual.</p>
                </div>
                <div class="apresiasi-card">
                <div class="apresiasi-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></div>
                <h4>Kualitas Tetap Utama</h4>
                <p>Konten berbasis data, thesis jelas, dan relevan bagi investor premium memiliki nilai lebih tinggi.</p>
                </div>
                <div class="apresiasi-card">
                <div class="apresiasi-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg></div>
                <h4>Terukur & Terkurasi</h4>
                <p>Konten tetap melewati standar editorial dan compliance. Konten yang melanggar dapat kehilangan eligibility apresiasi.</p>
                </div>
            </div>
          </div>
          
          <div class="apresiasi-footer">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 12px; flex-shrink: 0;"><path d="M3 3v18h18"></path><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"></path></svg>
            Semakin berguna konten bagi subscriber premium, semakin besar peluang contributor memperoleh apresiasi dari ekosistem Avenir.
          </div>
        </section>

        <!-- Contributor List -->
        <section id="registered-contributors" class="contributor-list-section">
          <div class="section-header-flex">
            <div>
                <h2>Contributor Research Terdaftar</h2>
                <p class="section-subtitle">Kenali contributor yang sudah bergabung dan berkontribusi di Avenir Research.</p>
            </div>
            <a href="#" class="view-all-link">Lihat semua contributor &rarr;</a>
          </div>
          
          <div class="contributor-grid" v-if="props.contributors && props.contributors.length > 0">
            <div v-for="contributor in props.contributors" :key="contributor.id" class="contributor-profile-card">
              <div class="card-top">
                <img :src="contributor.avatar" :alt="contributor.name" class="profile-avatar" onerror="this.src='https://ui-avatars.com/api/?name=A&background=10b981&color=fff'" />
                <div class="profile-info">
                  <h4 class="profile-name">{{ contributor.name }}</h4>
                  <div class="profile-role">
                    <span class="role-badge">Contributor Research</span>
                  </div>
                  <div class="profile-specs" v-if="contributor.specializations && contributor.specializations.length > 0">
                    {{ contributor.specializations.join(' • ') }}
                  </div>
                </div>
              </div>
              <div class="card-stats">
                <div class="stat-box">
                  <span class="stat-label">Konten Terbit</span>
                  <span class="stat-value">{{ contributor.konten_terbit }}</span>
                </div>
                <div class="stat-box">
                  <span class="stat-label">Kanal</span>
                  <span class="stat-value">{{ contributor.kanal }}</span>
                </div>
              </div>
              <button class="btn-full-outline">Lihat Konten</button>
            </div>
          </div>
          <div v-else class="empty-state">
             Belum ada Contributor Research yang terverifikasi saat ini.
          </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
          <div class="cta-content">
            <h2>Siap Menerbitkan Riset di Avenir?</h2>
            <p>Bangun track record riset, perluas distribusi insight, dan jadi bagian dari ekosistem Avenir Research.</p>
            <div class="cta-actions">
              <button @click="handleDaftarClick" class="btn-primary">Daftar Sebagai Contributor</button>
              <a href="/katalog" class="btn-outline-white">Lihat Katalog Riset</a>
            </div>
          </div>
          <div class="cta-visual">
             <div class="mock-book">
                <div class="book-cover">
                    <div class="book-logo">▵ AVENIR RESEARCH</div>
                    <div class="book-line"></div>
                    <div class="book-chart">
                       <svg viewBox="0 0 100 40" preserveAspectRatio="none">
                          <polyline points="0,30 20,25 40,35 60,15 80,20 100,5" fill="none" stroke="#10b981" stroke-width="2"/>
                       </svg>
                    </div>
                </div>
             </div>
          </div>
        </section>

        <!-- Mandatory Disclaimer -->
        <div class="mandatory-disclaimer">
            <strong>Disclaimer:</strong> Avenir Research bukan penasihat investasi personal. Seluruh konten riset, artikel, dan analisis yang diterbitkan di platform Avenir bersifat informasi dan edukasi, bukan ajakan membeli atau menjual efek tertentu. Keputusan investasi sepenuhnya menjadi tanggung jawab masing-masing investor. Mitra Analis Avenir wajib mematuhi standar editorial, disclosure konflik kepentingan, dan kebijakan publikasi Avenir. Avenir berhak menolak, mengedit, menunda, atau mencabut konten yang tidak sesuai dengan standar platform. Partisipasi dalam Avenir Mitra Pool tidak menjamin pendapatan tetap.
        </div>
        
        <div class="footer-guarantees">
            <span class="guarantee-item"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Thesis jelas</span>
            <span class="guarantee-item"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Data dapat ditelusuri</span>
            <span class="guarantee-item"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Sumber dicantumkan</span>
            <span class="guarantee-item"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Disclosure wajib</span>
            <span class="guarantee-item"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Tidak menyesatkan</span>
            <span class="guarantee-item"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Bebas plagiarisme</span>
            <a href="#" class="learn-more">Pelajari standar konten kami &rarr;</a>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.contributor-page-wrapper {
  background-color: #050907;
  color: #cbd5e1;
  font-family: 'Inter', 'Roboto', sans-serif;
  min-height: 100vh;
  position: relative;
  overflow-x: hidden;
  padding-bottom: 60px;
}

.radial-glow {
  position: absolute;
  border-radius: 50%;
  filter: blur(140px);
  pointer-events: none;
  z-index: 1;
}
.glow-top {
  top: -10%;
  right: -5%;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, rgba(5, 9, 7, 0) 70%);
}

.contributor-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 24px;
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  gap: 80px;
}

/* Typography Shared */
h1, h2, h3, h4 {
  color: #F8FAFC;
  font-weight: 600;
  line-height: 1.2;
}

/* HERO SECTION */
.hero-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 40px;
  align-items: center;
  margin-top: 20px;
}

@media (max-width: 992px) {
  .hero-section {
    grid-template-columns: 1fr;
  }
}

.hero-eyebrow {
  color: #10b981;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  margin-bottom: 20px;
}

.hero-content h1 {
  font-size: clamp(36px, 4vw, 52px);
  font-weight: 700;
  margin-bottom: 24px;
}

.hero-content h1 .hl {
  color: #10b981;
}

.hero-content p {
  font-size: 16px;
  line-height: 1.7;
  color: #94A3B8;
  margin-bottom: 32px;
  max-width: 540px;
}

.hero-actions {
  display: flex;
  gap: 16px;
  margin-bottom: 40px;
  flex-wrap: wrap;
}

.btn-primary {
  background: #10b981;
  color: #042f2e;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary:hover {
  background: #059669;
  color: #ffffff;
}

.btn-outline {
  background: transparent;
  color: #e2e8f0;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  border: 1px solid rgba(255, 255, 255, 0.2);
  cursor: pointer;
  text-decoration: none;
  transition: all 0.2s;
}

.btn-outline:hover {
  background: rgba(255, 255, 255, 0.05);
}

.btn-outline-white {
  background: transparent;
  color: #ffffff;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  border: 1px solid rgba(255, 255, 255, 0.3);
  cursor: pointer;
  text-decoration: none;
  transition: all 0.2s;
}

.btn-outline-white:hover {
  background: rgba(255, 255, 255, 0.1);
}

.hero-social-proof {
  display: flex;
  align-items: center;
  gap: 16px;
}

.avatars-group {
  display: flex;
}

.avatar-sm {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 2px solid #050907;
  margin-left: -8px;
}
.avatar-sm:first-child {
  margin-left: 0;
}

.proof-text {
  font-size: 12px;
  color: #64748b;
  line-height: 1.5;
  max-width: 300px;
}

/* Mockup CSS */
.hero-visual {
  position: relative;
  display: flex;
  justify-content: flex-end;
}

.dashboard-mockup {
  background: #09100C;
  border: 1px solid rgba(16, 185, 129, 0.2);
  border-radius: 12px;
  width: 100%;
  max-width: 550px;
  overflow: hidden;
  box-shadow: 0 24px 40px rgba(0, 0, 0, 0.4);
}

.mock-header {
  padding: 12px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  display: flex;
  align-items: center;
}

.mock-logo {
  font-size: 12px;
  font-weight: 600;
  color: #f8fafc;
  display: flex;
  align-items: center;
  gap: 6px;
}

.mock-body {
  display: flex;
  height: 320px;
}

.mock-sidebar {
  width: 140px;
  border-right: 1px solid rgba(255, 255, 255, 0.05);
  padding: 16px 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.mock-nav-item {
  font-size: 10px;
  color: #64748b;
  padding: 6px 8px;
  border-radius: 4px;
}

.mock-nav-item.active {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.mock-btn-submit {
  margin-top: auto;
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
  font-size: 10px;
  text-align: center;
  padding: 8px;
  border-radius: 4px;
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.mock-main {
  flex: 1;
  padding: 16px;
  background: #050907;
}

.mock-top-cards {
  display: flex;
  gap: 12px;
}

.mock-card {
  background: #0A1310;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  padding: 12px;
}

.mock-card.lg {
  flex: 2;
}

.mock-card.md {
  flex: 1;
}

.mock-card-header {
  font-size: 11px;
  font-weight: 600;
  color: #f8fafc;
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
}

.mock-link {
  color: #10b981;
  font-size: 9px;
}

.mock-grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
}

.mock-mini-card {
  background: #050907;
  border-radius: 6px;
  overflow: hidden;
}

.mock-img {
  height: 40px;
  background: #1e293b;
}

.mock-img.img-1 { background: linear-gradient(135deg, #1e293b, #334155); }
.mock-img.img-2 { background: linear-gradient(135deg, #0f172a, #1e293b); }
.mock-img.img-3 { background: linear-gradient(135deg, #064e3b, #047857); }

.mock-mini-title {
  font-size: 8px;
  color: #cbd5e1;
  padding: 6px 6px 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.mock-mini-date {
  font-size: 7px;
  color: #64748b;
  padding: 0 6px 6px;
}

.mock-profile-header {
  display: flex;
  gap: 8px;
  align-items: center;
  margin-bottom: 12px;
}

.mock-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #10b981;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
}

.mock-name {
  font-size: 10px;
  color: #f8fafc;
  font-weight: 500;
}

.mock-badge {
  font-size: 8px;
  color: #10b981;
}

.mock-stats {
  display: flex;
  justify-content: space-between;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  padding: 8px 0;
  margin-bottom: 12px;
}

.mock-stat-item {
  text-align: center;
}

.mock-stat-val {
  font-size: 12px;
  font-weight: 600;
  color: #f8fafc;
}

.mock-stat-lbl {
  font-size: 7px;
  color: #64748b;
}

.mock-premium-box {
  background: rgba(16, 185, 129, 0.05);
  border: 1px solid rgba(16, 185, 129, 0.1);
  padding: 8px;
  border-radius: 6px;
}

.mock-premium-title {
  font-size: 9px;
  color: #10b981;
  font-weight: 600;
  margin-bottom: 4px;
}

.mock-premium-desc {
  font-size: 7px;
  color: #94a3b8;
  line-height: 1.4;
  margin-bottom: 6px;
}

.mock-premium-tag {
  font-size: 7px;
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
  display: inline-block;
  padding: 2px 4px;
  border-radius: 2px;
}

/* Feature Strip */
.feature-strip {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  padding: 32px 0;
}

@media (max-width: 992px) {
  .feature-strip {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 576px) {
  .feature-strip {
    grid-template-columns: 1fr;
  }
}

.feature-item {
  display: flex;
  gap: 16px;
}

.feat-icon {
  color: #10b981;
  flex-shrink: 0;
}

.feat-text h4 {
  font-size: 14px;
  margin-bottom: 6px;
}

.feat-text p {
  font-size: 13px;
  color: #64748b;
  line-height: 1.5;
}

/* About Contributor */
.about-contributor {
  display: grid;
  grid-template-columns: 300px 1fr;
  gap: 40px;
}

@media (max-width: 992px) {
  .about-contributor {
    grid-template-columns: 1fr;
  }
}

.about-text h2 {
  font-size: 28px;
  margin-bottom: 24px;
}

.about-text p {
  font-size: 14px;
  color: #94A3B8;
  line-height: 1.6;
  margin-bottom: 16px;
}

.about-cards {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

@media (max-width: 640px) {
  .about-cards {
    grid-template-columns: 1fr;
  }
}

.feature-box {
  background: #0A1310;
  border: 1px solid rgba(16, 185, 129, 0.1);
  padding: 32px 24px;
  border-radius: 12px;
  position: relative;
  overflow: hidden;
}

.feature-box.secondary {
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.feature-box h3 {
  font-size: 18px;
  margin-bottom: 12px;
  color: #10b981;
}

.feature-box.secondary h3 {
  color: #f8fafc;
}

.feature-box p {
  font-size: 13px;
  color: #94a3b8;
  margin-bottom: 24px;
  line-height: 1.5;
}

.tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 32px;
}

.tag {
  background: #111e17;
  color: #cbd5e1;
  font-size: 11px;
  padding: 4px 10px;
  border-radius: 4px;
  border: 1px solid rgba(255, 255, 255, 0.03);
}

.card-link {
  color: #10b981;
  font-size: 13px;
  font-weight: 500;
  text-decoration: none;
}

.card-link:hover {
  text-decoration: underline;
}

/* How It Works */
.how-it-works h2 {
  font-size: 24px;
  margin-bottom: 40px;
}

.steps-container {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 20px;
  position: relative;
}

@media (max-width: 992px) {
  .steps-container {
    grid-template-columns: repeat(3, 1fr);
    row-gap: 40px;
  }
}
@media (max-width: 576px) {
  .steps-container {
    grid-template-columns: 1fr;
    row-gap: 32px;
  }
}

.step-line {
  position: absolute;
  top: 14px;
  left: 30px;
  right: calc(16.66% - 30px);
  height: 1px;
  background: dashed 1px rgba(255, 255, 255, 0.1);
  border-top: 1px dashed rgba(255, 255, 255, 0.1);
  z-index: 1;
}

@media (max-width: 992px) {
  .step-line {
    display: none;
  }
}

.step-item {
  position: relative;
  z-index: 2;
  text-align: left;
}

.step-circle {
  width: 28px;
  height: 28px;
  background: #10b981;
  color: #042f2e;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  margin-bottom: 16px;
}

.step-icon {
  margin-bottom: 12px;
  color: #f8fafc;
}

.step-item h4 {
  font-size: 14px;
  margin-bottom: 8px;
}

.step-item p {
  font-size: 12px;
  color: #64748b;
  line-height: 1.5;
}

/* Apresiasi Program */
.apresiasi-program {
  background: #0A1310;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 40px;
}

@media (max-width: 768px) {
  .apresiasi-program {
    padding: 24px;
  }
}

.apresiasi-header {
  display: flex;
  flex-direction: column;
  gap: 32px;
  margin-bottom: 40px;
}

@media (min-width: 992px) {
  .apresiasi-header {
    flex-direction: row;
    align-items: flex-start;
  }
  .ap-title-box {
    flex: 1;
  }
  .apresiasi-cards {
    flex: 2;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }
}

@media (max-width: 991px) {
  .apresiasi-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }
}
@media (max-width: 576px) {
  .apresiasi-cards {
    grid-template-columns: 1fr;
  }
}

.ap-title-box h2 {
  font-size: 24px;
  margin-bottom: 16px;
}

.ap-title-box p {
  font-size: 14px;
  color: #94A3B8;
  line-height: 1.6;
  margin-bottom: 12px;
}

.apresiasi-disclaimer {
  font-size: 11px;
  color: #10b981;
  display: flex;
  align-items: flex-start;
  margin-top: 24px;
  background: rgba(16, 185, 129, 0.05);
  padding: 12px;
  border-radius: 6px;
}

.apresiasi-card {
  background: #0D1914;
  border: 1px solid rgba(255, 255, 255, 0.03);
  padding: 20px;
  border-radius: 12px;
}

.apresiasi-icon {
  margin-bottom: 12px;
}

.apresiasi-card h4 {
  font-size: 14px;
  margin-bottom: 8px;
}

.apresiasi-card p {
  font-size: 12px;
  color: #64748b;
  line-height: 1.5;
}

.apresiasi-footer {
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  font-size: 13px;
  color: #cbd5e1;
  display: flex;
  align-items: center;
}

/* Contributor List */
.section-header-flex {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 8px;
}

.section-header-flex h2 {
  font-size: 24px;
  margin-bottom: 8px;
}

.section-subtitle {
  font-size: 14px;
  color: #94A3B8;
  margin-bottom: 32px;
}

.view-all-link {
  color: #10b981;
  font-size: 13px;
  text-decoration: none;
  font-weight: 500;
  margin-bottom: 32px;
}

.contributor-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.contributor-profile-card {
  background: #0A1310;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 24px;
  display: flex;
  flex-direction: column;
}

.card-top {
  display: flex;
  gap: 16px;
  margin-bottom: 20px;
  align-items: flex-start;
}

.profile-avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  object-fit: cover;
  background: #111e17;
}

.profile-name {
  font-size: 16px;
  margin-bottom: 4px;
}

.role-badge {
  font-size: 10px;
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
  padding: 2px 8px;
  border-radius: 12px;
  display: inline-block;
  margin-bottom: 8px;
}

.profile-specs {
  font-size: 11px;
  color: #64748b;
  line-height: 1.4;
}

.card-stats {
  display: flex;
  justify-content: space-between;
  margin-bottom: 24px;
}

.stat-box {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-label {
  font-size: 10px;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-value {
  font-size: 13px;
  color: #f8fafc;
  font-weight: 600;
}

.btn-full-outline {
  width: 100%;
  background: transparent;
  color: #cbd5e1;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 10px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
  margin-top: auto;
}

.btn-full-outline:hover {
  background: rgba(255, 255, 255, 0.05);
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #64748b;
  background: #0A1310;
  border-radius: 12px;
  border: 1px dashed rgba(255, 255, 255, 0.1);
}

/* CTA Section */
.cta-section {
  background: linear-gradient(135deg, #064e3b 0%, #022c22 100%);
  border-radius: 16px;
  display: flex;
  overflow: hidden;
  position: relative;
}

@media (max-width: 768px) {
  .cta-section {
    flex-direction: column;
  }
}

.cta-content {
  padding: 48px;
  flex: 1;
  z-index: 2;
}

@media (max-width: 576px) {
  .cta-content {
    padding: 32px 24px;
  }
}

.cta-content h2 {
  font-size: 28px;
  margin-bottom: 16px;
}

.cta-content p {
  font-size: 15px;
  color: #a7f3d0;
  margin-bottom: 32px;
  line-height: 1.6;
  max-width: 400px;
}

.cta-actions {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.cta-visual {
  flex: 1;
  position: relative;
  min-height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.mock-book {
  width: 200px;
  height: 280px;
  background: #042f2e;
  border-radius: 4px 12px 12px 4px;
  box-shadow: -10px 10px 30px rgba(0,0,0,0.5), inset 4px 0 10px rgba(255,255,255,0.1);
  transform: perspective(800px) rotateY(-15deg);
  position: absolute;
  right: 40px;
  top: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-left: 8px solid #022c22;
}

@media (max-width: 768px) {
  .mock-book {
    position: relative;
    right: 0;
    top: 0;
    margin: 0 auto 40px;
  }
}

.book-cover {
  width: 80%;
  height: 80%;
  display: flex;
  flex-direction: column;
}

.book-logo {
  color: #10b981;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.1em;
  margin-bottom: 40px;
}

.book-line {
  height: 2px;
  width: 40px;
  background: rgba(255,255,255,0.2);
  margin-bottom: 40px;
}

.book-chart {
  margin-top: auto;
  opacity: 0.5;
}

.footer-guarantees {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  align-items: center;
  justify-content: center;
  margin-top: -60px;
  padding: 0 24px;
}

.guarantee-item {
  font-size: 11px;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 6px;
}

.learn-more {
  font-size: 11px;
  color: #10b981;
  text-decoration: none;
  margin-left: 16px;
}

.learn-more:hover {
  text-decoration: underline;
}

.mandatory-disclaimer {
  margin: 40px auto;
  max-width: 900px;
  padding: 24px;
  background: #09100C;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  font-size: 11px;
  color: #64748b;
  line-height: 1.6;
  text-align: center;
}
.mandatory-disclaimer strong {
  color: #94a3b8;
}
</style>
