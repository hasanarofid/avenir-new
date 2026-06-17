<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { authStore } from '@/Stores/authStore';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  status: {
    type: String,
    default: 'guest' // 'guest', 'free', 'pending', 'active'
  },
  isSubscriber: {
    type: Boolean,
    default: false
  },
  pendingSubmission: {
    type: Object,
    default: null
  },
  bankAccountInfo: {
    type: String,
    default: 'Marta Fikri 3370748356 bank BCA'
  }
});

const page = usePage();
const isLoggedIn = computed(() => !!page.props.auth?.user);

// Packages Data
const packages = [
  {
    id: '1bulan',
    name: 'Bulanan',
    price: 149000,
    priceStr: '149.000',
    period: '/ bulan',
    saveText: 'Fleksibel · Bisa berhenti kapan saja',
    durationDays: 30,
    badge: null,
    image: '/images/subscription/plan-bulanan.png'
  },
  {
    id: '3bulan',
    name: '3 Bulan',
    price: 399000,
    priceStr: '399.000',
    period: '/ 3 bulan',
    perMonth: '≈ Rp 133.000 / bulan',
    saveText: 'Hemat Rp 48.000 vs bulanan',
    durationDays: 90,
    badge: 'Populer',
    image: '/images/subscription/plan-3bulan.png'
  },
  {
    id: '6bulan',
    name: '6 Bulan',
    price: 729000,
    priceStr: '729.000',
    period: '/ 6 bulan',
    perMonth: '≈ Rp 121.500 / bulan',
    saveText: 'Hemat Rp 165.000 · Diskon 18%',
    durationDays: 180,
    badge: 'Nilai Terbaik',
    specialBg: true,
    image: '/images/subscription/plan-6bulan.png'
  },
  {
    id: '12bulan',
    name: '1 Tahun',
    price: 1289000,
    priceStr: '1.289.000',
    period: '/ tahun',
    perMonth: '≈ Rp 107.500 / bulan',
    saveText: 'Hemat Rp 499.000 · Diskon 28%',
    durationDays: 365,
    badge: 'Paling Hemat',
    image: '/images/subscription/plan-12bulan.png'
  }
];

// Payment Modal State
const isModalOpen = ref(false);
const activeStep = ref(1); // 1: Bank Info, 2: Upload Proof, 3: Success
const selectedPackage = ref(null);
const fileInput = ref(null);
const filePreview = ref(null);
const uploadError = ref(null);

const form = useForm({
  paket: '',
  durasi_hari: 0,
  nominal: 0,
  bukti_transfer: null
});

const getPaketLabel = (key) => {
  const map = {
    '1bulan': 'Bulanan',
    '3bulan': '3 Bulan',
    '6bulan': '6 Bulan',
    '12bulan': '1 Tahun'
  };
  return map[key] || key;
};

const handleSelectPackage = (pkg) => {
  if (!isLoggedIn.value) {
    authStore.open('login');
    return;
  }

  if (props.status === 'pending') {
    Swal.fire({
      title: 'Menunggu Verifikasi',
      text: 'Anda masih memiliki submission yang sedang diverifikasi. Tunggu sampai admin memprosesnya.',
      icon: 'info',
      background: '#121614',
      color: '#cbd5e1',
      confirmButtonColor: '#10b981'
    });
    return;
  }

  selectedPackage.value = pkg;
  form.paket = pkg.id;
  form.durasi_hari = pkg.durationDays;
  form.nominal = pkg.price;
  form.bukti_transfer = null;
  filePreview.value = null;
  uploadError.value = null;
  activeStep.value = 1;
  isModalOpen.value = true;
};

// Clipboard copying utility
const copyBtnText = ref({
  rek: 'Copy',
  nominal: 'Copy'
});

const handleCopy = (text, type) => {
  navigator.clipboard.writeText(text).then(() => {
    copyBtnText.value[type] = '✓ Copied';
    setTimeout(() => {
      copyBtnText.value[type] = 'Copy';
    }, 1500);
  });
};

const handleFileChange = (e) => {
  const file = e.target.files?.[0];
  if (!file) return;

  uploadError.value = null;
  if (!/^image\/(jpe?g|png|webp)$/.test(file.type)) {
    uploadError.value = 'Format file tidak didukung. Hanya JPG/PNG/WEBP.';
    return;
  }
  if (file.size > 3 * 1024 * 1024) {
    uploadError.value = 'Ukuran file melebihi 3 MB. Compress dulu atau pakai screenshot.';
    return;
  }

  form.bukti_transfer = file;
  const reader = new FileReader();
  reader.onload = (ev) => {
    filePreview.value = ev.target.result;
  };
  reader.readAsDataURL(file);
};

const triggerFileInput = () => {
  fileInput.value.click();
};

const handleSubmit = () => {
  if (!form.bukti_transfer) {
    uploadError.value = 'Silakan upload bukti transfer Anda terlebih dahulu.';
    return;
  }

  form.post('/langganan/kirim', {
    onSuccess: () => {
      activeStep.value = 3;
    },
    onError: (errors) => {
      uploadError.value = errors.error || 'Terjadi kesalahan. Silakan coba lagi.';
    }
  });
};

const handleCloseModal = () => {
  isModalOpen.value = false;
  selectedPackage.value = null;
  form.reset();
};
</script>

<template>
    <Head>
        <title>Subscription | AVENIR</title>
        <meta name="description" content="AVENIR - Platform riset dan direktori pasar modal Indonesia yang komprehensif." />
        <meta property="og:title" content="Subscription | AVENIR" />
        <meta property="og:description" content="AVENIR - Platform riset dan direktori pasar modal Indonesia yang komprehensif." />
        <meta property="og:type" content="website" />
        <meta name="twitter:card" content="summary_large_image" />
        
        <!-- GEO Tags -->
        <meta name="geo.region" content="ID" />
        <meta name="geo.placename" content="Indonesia" />
        <meta name="geo.position" content="-0.789275;113.921327" />
        <meta name="ICBM" content="-0.789275, 113.921327" />
        <meta name="language" content="id-ID" />
        <meta name="view-transition" content="same-origin" />
    </Head>

  <Head title="Langganan Premium" />

  <AppLayout>
    <div class="sub-dark-wrapper">
      <!-- Glow Backdrops -->
      <div class="radial-glow glow-top-right"></div>
      <div class="radial-glow glow-bottom-left"></div>

      <div class="sub-page">
        <!-- Header -->
        <div class="sub-header">
          <div class="sub-tag">◆ LANGGANAN</div>
          <h1>Pilih Paket <span class="hl">Langganan</span></h1>
          <p class="sub-lead">
            Akses penuh ke seluruh katalog riset dan artikel Avenir Research. Pembayaran via transfer bank, diaktivasi maksimal 1×24 jam setelah verifikasi.
          </p>
        </div>

        <!-- Status Banner -->
        <div class="status-banner-container">
          <!-- Guest status -->
          <div v-if="status === 'guest'" class="status-banner lg-guest">
            <div class="banner-lbl">⚠ BELUM LOGIN</div>
            <div class="banner-msg">
              Anda perlu <strong>login terlebih dahulu</strong> sebelum berlangganan. Silakan masuk ke akun Anda atau daftar baru.
            </div>
            <button @click="authStore.open('login')" class="banner-cta">
              LOGIN / DAFTAR →
            </button>
          </div>

          <!-- Pending status -->
          <div v-else-if="status === 'pending' && pendingSubmission" class="status-banner lg-pending">
            <div class="banner-lbl">⏳ MENUNGGU VERIFIKASI</div>
            <div class="banner-msg">
              Submission Anda untuk paket <strong>{{ getPaketLabel(pendingSubmission.paket) }}</strong> 
              (Rp {{ pendingSubmission.nominal.toLocaleString('id-ID') }}) telah terkirim. Admin akan melakukan verifikasi bukti transfer maksimal 1×24 jam.
            </div>
          </div>

          <!-- Active status -->
          <div v-else-if="status === 'active'" class="status-banner lg-active">
            <div class="banner-lbl">✓ LANGGANAN AKTIF</div>
            <div class="banner-msg">
              Langganan Anda aktif! Anda memiliki akses penuh ke seluruh konten riset premium Avenir. Anda dapat memperpanjang masa aktif dengan memilih paket di bawah ini.
            </div>
          </div>
        </div>

        <!-- Pricing Card Grid -->
        <div class="pricing-grid">
          <div 
            v-for="pkg in packages" 
            :key="pkg.id" 
            class="pricing-card"
            :class="{ 
              'popular-tier': pkg.badge === 'Populer', 
              'best-value-tier': pkg.badge === 'Nilai Terbaik',
              'most-saving-tier': pkg.badge === 'Paling Hemat'
            }"
          >
            <!-- Badge -->
            <div v-if="pkg.badge" class="card-badge" :class="pkg.badge.toLowerCase().replace(/\s/g, '-')">
              {{ pkg.badge }}
            </div>

            <!-- Cover Image -->
            <div class="plan-card-cover">
              <img :src="pkg.image" :alt="pkg.name" loading="lazy" />
              <div class="plan-card-cover-overlay" />
            </div>

            <!-- Card Content -->
            <div class="plan-card-body">
              <div class="trial-header">FREE TRIAL · 7 HARI</div>
              <h3 class="plan-title">{{ pkg.name }}</h3>
              
              <div class="price-box">
                <span class="currency">Rp</span>
                <span class="amount">{{ pkg.priceStr }}</span>
                <span class="period">{{ pkg.period }}</span>
              </div>
              
              <div v-if="pkg.perMonth" class="per-month-hint">
                {{ pkg.perMonth }}
              </div>

              <!-- Action Button -->
              <button 
                @click="handleSelectPackage(pkg)" 
                class="plan-btn"
                :class="{ 
                  'btn-solid': pkg.badge === 'Populer',
                  'btn-gold-solid': pkg.badge === 'Nilai Terbaik',
                  'btn-outline': !pkg.badge || pkg.badge === 'Paling Hemat'
                }"
              >
                PILIH PAKET INI
              </button>

              <div class="card-save-text">
                {{ pkg.saveText }}
              </div>
            </div>
          </div>
        </div>

        <!-- FAQ Section -->
        <div class="faq-section">
          <h2>Pertanyaan Umum</h2>
          
          <div class="faq-accordion">
            <div class="faq-card">
              <h3>Berapa lama proses aktivasi?</h3>
              <p>Setelah Anda mengunggah bukti transfer, tim admin kami akan melakukan verifikasi manual dalam waktu maksimal <strong>1×24 jam</strong> (umumnya lebih cepat pada hari kerja). Anda akan menerima email konfirmasi begitu akun diaktifkan.</p>
            </div>
            <div class="faq-card">
              <h3>Apa yang saya dapatkan dengan berlangganan?</h3>
              <p>Akses penuh tanpa batas ke <strong>seluruh katalog riset</strong> Avenir Research (Deep Dive Saham, Macro &amp; Sectoral Insight, Valuation Model) serta update berkala emiten yang Anda pantau selama masa langganan aktif.</p>
            </div>
            <div class="faq-card">
              <h3>Apakah bisa membatalkan langganan kapan saja?</h3>
              <p>Ya. Sistem kami menggunakan model transfer manual sekali bayar tanpa perpanjangan otomatis (no auto-renew). Setelah masa langganan berakhir, akun Anda kembali menjadi akun free biasa dan Anda dapat memilih berlangganan kembali kapan pun dibutuhkan.</p>
            </div>
            <div class="faq-card">
              <h3>Bagaimana jika bukti transfer saya ditolak?</h3>
              <p>Admin akan mengirimkan email pemberitahuan beserta alasan penolakan (misal gambar kurang jelas atau nominal transfer tidak sesuai). Anda dapat mengunggah ulang bukti transfer baru yang valid melalui menu ini.</p>
            </div>
            <div class="faq-card">
              <h3>Apakah harga paket langganan sudah final?</h3>
              <p>Harga yang tertera adalah harga promo rilis. Bagi pengguna yang sudah memiliki paket aktif, harga berlangganan Anda akan tetap sama sampai masa berlaku paket tersebut habis sekalipun ada penyesuaian harga di kemudian hari.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ════ PAYMENT MODAL ════ -->
    <Transition name="fade">
      <div v-if="isModalOpen" class="modal-overlay" @click.self="handleCloseModal">
        <div class="modal-box">
          <button class="modal-close-btn" @click="handleCloseModal">✕</button>

          <!-- STEP 1: Bank details -->
          <div v-if="activeStep === 1" class="modal-step-content">
            <div class="step-progress">
              <span class="dot active"></span>
              <span class="dot"></span>
              <span class="dot"></span>
            </div>
            <h2>Transfer ke Rekening Berikut</h2>
            <p class="modal-subtitle">Silakan lakukan transfer sesuai dengan detail nominal paket di bawah ini. Pastikan nominal pas agar verifikasi berjalan cepat.</p>

            <div class="selected-package-badge">
              <span class="lbl">Paket Dipilih:</span>
              <span class="val">{{ selectedPackage?.name }}</span>
            </div>

            <div class="bank-details-card">
              <div class="detail-row" style="flex-direction: column; align-items: flex-start; gap: 12px;">
                <span class="lbl">Informasi Rekening Tujuan</span>
                <div class="val font-mono" style="white-space: pre-wrap; line-height: 1.5;">{{ bankAccountInfo }}</div>
                <button @click="handleCopy(bankAccountInfo, 'rek')" class="copy-btn" :class="{ copied: copyBtnText.rek.includes('✓') }" style="align-self: flex-start; margin-top: 4px;">
                  Salin Informasi Rekening
                </button>
              </div>
              <div class="detail-row highlight-row">
                <span class="lbl">Nominal Transfer</span>
                <span class="val price-val">Rp {{ selectedPackage?.price.toLocaleString('id-ID') }}</span>
                <button @click="handleCopy(String(selectedPackage?.price), 'nominal')" class="copy-btn" :class="{ copied: copyBtnText.nominal.includes('✓') }">
                  {{ copyBtnText.nominal }}
                </button>
              </div>
            </div>

            <div class="info-alert">
              💡 <strong>Tips:</strong> Simpan bukti transfer Anda (struk/screenshot) untuk diunggah pada langkah berikutnya.
            </div>

            <div class="modal-actions">
              <button @click="activeStep = 2" class="modal-primary-btn">SAYA SUDAH TRANSFER →</button>
              <button @click="handleCloseModal" class="modal-secondary-btn">Batal</button>
            </div>
          </div>

          <!-- STEP 2: Upload Proof -->
          <div v-if="activeStep === 2" class="modal-step-content">
            <div class="step-progress">
              <span class="dot completed"></span>
              <span class="dot active"></span>
              <span class="dot"></span>
            </div>
            <h2>Upload Bukti Transfer</h2>
            <p class="modal-subtitle">Unggah screenshot atau foto bukti pembayaran Anda. Kami mendukung file berekstensi JPG, PNG, atau WEBP dengan ukuran maks 3MB.</p>

            <div v-if="uploadError" class="upload-error-box">
              {{ uploadError }}
            </div>

            <input 
              type="file" 
              ref="fileInput" 
              @change="handleFileChange" 
              class="hidden-file-input" 
              accept="image/*"
            />

            <!-- Drag & Drop Box -->
            <div 
              @click="triggerFileInput" 
              class="upload-drop-zone"
              :class="{ 'zone-has-file': filePreview }"
            >
              <div v-if="!filePreview" class="zone-placeholder">
                <span class="upload-icon">📸</span>
                <span class="upload-title">Ketuk untuk Memilih File</span>
                <span class="upload-subtitle">Format: JPG, PNG, WEBP (Maks 3MB)</span>
              </div>
              <div v-else class="zone-preview">
                <img :src="filePreview" alt="Preview Bukti Transfer" />
                <span class="file-name">{{ form.bukti_transfer?.name }}</span>
              </div>
            </div>

            <div class="modal-actions">
              <button 
                @click="handleSubmit" 
                :disabled="form.processing || !form.bukti_transfer" 
                class="modal-primary-btn"
              >
                {{ form.processing ? 'MENGIRIM...' : 'KIRIM BUKTI PEMBAYARAN →' }}
              </button>
              <button @click="activeStep = 1" :disabled="form.processing" class="modal-secondary-btn">Kembali</button>
            </div>
          </div>

          <!-- STEP 3: Success -->
          <div v-if="activeStep === 3" class="modal-step-content text-center">
            <div class="success-glow">
              <div class="success-icon">✓</div>
            </div>
            <h2 class="mt-6">Terima Kasih!</h2>
            <p class="modal-subtitle text-center mt-2 max-w-sm mx-auto">
              Bukti transfer Anda telah berhasil dikirim ke sistem kami. Tim admin akan memverifikasi pembayaran Anda dalam waktu 1x24 jam.
            </p>
            <p class="modal-subtitle text-center mt-1">
              Email konfirmasi akan dikirimkan ke <strong>{{ page.props.auth.user.email }}</strong> begitu akses premium diaktifkan.
            </p>

            <div class="modal-actions justify-center mt-6">
              <button @click="handleCloseModal" class="modal-primary-btn px-10">TUTUP</button>
            </div>
          </div>

        </div>
      </div>
    </Transition>
  </AppLayout>
</template>

<style scoped>


.sub-dark-wrapper {
  background-color: #090b0a;
  color: #cbd5e1;
  font-family: 'Roboto', sans-serif;
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

.sub-page {
  max-width: 1080px;
  margin: 0 auto;
  padding: 40px 24px 100px;
  position: relative;
  z-index: 2;
}

.sub-header {
  text-align: center;
  margin-bottom: 48px;
}

.sub-tag {
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

.sub-header h1 {
  font-family: 'Roboto', sans-serif;
  font-size: clamp(28px, 5vw, 44px);
  font-weight: 700;
  line-height: 1.15;
  color: #ffffff;
  margin-bottom: 20px;
}

.sub-header h1 .hl {
  color: #10b981;
  background: linear-gradient(120deg, #10b981 0%, #34d399 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.sub-lead {
  font-size: 15px;
  line-height: 1.7;
  color: #94a3b8;
  max-width: 650px;
  margin: 0 auto;
}

/* Status Banner styles */
.status-banner-container {
  margin-bottom: 40px;
}

.status-banner {
  background: #121614;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  position: relative;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.lg-guest {
  border-color: rgba(239, 68, 68, 0.25);
  background: linear-gradient(to right, rgba(239, 68, 68, 0.03), rgba(18, 22, 20, 0.9));
}

.lg-guest .banner-lbl {
  color: #ef4444;
}

.lg-pending {
  border-color: rgba(245, 158, 11, 0.3);
  background: linear-gradient(to right, rgba(245, 158, 11, 0.03), rgba(18, 22, 20, 0.9));
}

.lg-pending .banner-lbl {
  color: #f59e0b;
}

.lg-active {
  border-color: rgba(16, 185, 129, 0.3);
  background: linear-gradient(to right, rgba(16, 185, 129, 0.04), rgba(18, 22, 20, 0.9));
}

.lg-active .banner-lbl {
  color: #10b981;
}

.banner-lbl {
  font-family: 'Roboto', sans-serif;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.1em;
}

.banner-msg {
  font-size: 13.5px;
  line-height: 1.6;
  color: #94a3b8;
}

.banner-msg strong {
  color: #ffffff;
}

.banner-cta {
  align-self: flex-start;
  background: #ef4444;
  color: #ffffff;
  border: none;
  font-size: 11px;
  font-weight: 700;
  padding: 8px 18px;
  border-radius: 50px;
  cursor: pointer;
  transition: all 0.2s;
}

.banner-cta:hover {
  background: #dc2626;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
}

/* Pricing Grid Layout */
.pricing-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
  gap: 20px;
  margin-bottom: 64px;
}

.pricing-card {
  background: #121614;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 20px;
  overflow: hidden;
  position: relative;
  display: flex;
  flex-direction: column;
  transition: all 0.25s ease;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
}

/* Plan Card Cover Image */
.plan-card-cover {
  width: 100%;
  aspect-ratio: 16 / 9;
  overflow: hidden;
  position: relative;
  background: #0d1110;
  flex-shrink: 0;
}

.plan-card-cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.pricing-card:hover .plan-card-cover img {
  transform: scale(1.06);
}

.plan-card-cover-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, transparent 30%, rgba(18, 22, 20, 0.85) 100%);
}

/* Plan Card Body */
.plan-card-body {
  padding: 28px 24px 32px;
  display: flex;
  flex-direction: column;
  flex: 1;
}


.pricing-card:hover {
  transform: translateY(-4px);
  border-color: rgba(16, 185, 129, 0.3);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4), 0 0 20px rgba(16, 185, 129, 0.03);
}

/* Specific Card overrides */
.popular-tier {
  border-color: rgba(16, 185, 129, 0.3);
  background: linear-gradient(180deg, #121614 0%, rgba(16, 185, 129, 0.03) 100%);
}
.popular-tier:hover {
  border-color: #10b981;
}

.best-value-tier {
  border-color: rgba(139, 105, 20, 0.3);
  background: linear-gradient(180deg, #121614 0%, rgba(139, 105, 20, 0.03) 100%);
}
.best-value-tier:hover {
  border-color: #a8820f;
}

.card-badge {
  position: absolute;
  top: 14px;
  right: 14px;
  font-family: 'Roboto', sans-serif;
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 0.05em;
  padding: 3px 10px;
  border-radius: 50px;
  text-transform: uppercase;
}

.card-badge.populer {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.25);
}

.card-badge.nilai-terbaik {
  background: rgba(139, 105, 20, 0.1);
  color: #e8c96b;
  border: 1px solid rgba(139, 105, 20, 0.25);
}

.card-badge.paling-hemat {
  background: rgba(16, 92, 155, 0.1);
  color: #60a5fa;
  border: 1px solid rgba(16, 92, 155, 0.25);
}

.trial-header {
  font-size: 9px;
  font-weight: 700;
  color: #64748b;
  letter-spacing: 0.08em;
  margin-bottom: 12px;
}

.plan-title {
  font-family: 'Roboto', sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 20px;
}

.price-box {
  display: flex;
  align-items: baseline;
  margin-bottom: 4px;
}

.currency {
  font-size: 16px;
  font-weight: 600;
  color: #10b981;
  margin-right: 2px;
}

.amount {
  font-family: 'Roboto', sans-serif;
  font-size: 32px;
  font-weight: 700;
  color: #ffffff;
  line-height: 1;
}

.period {
  font-size: 13px;
  color: #64748b;
  margin-left: 4px;
}

.per-month-hint {
  font-size: 11px;
  color: #10b981;
  margin-bottom: 24px;
  font-weight: 500;
}

.plan-btn {
  width: 100%;
  padding: 12px;
  border-radius: 50px;
  font-family: 'Roboto', sans-serif;
  font-size: 11.5px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
  border: 1px solid transparent;
  margin-top: auto;
}

.btn-solid {
  background: #10b981;
  color: #090b0a;
}
.btn-solid:hover {
  background: #34d399;
  box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);
  transform: translateY(-1px);
}

.btn-gold-solid {
  background: #854d0e;
  color: #ffffff;
}
.btn-gold-solid:hover {
  background: #a16207;
  box-shadow: 0 4px 14px rgba(161, 98, 7, 0.25);
  transform: translateY(-1px);
}

.btn-outline {
  background: transparent;
  border-color: rgba(255, 255, 255, 0.1);
  color: #d1d5db;
}
.btn-outline:hover {
  background: rgba(255, 255, 255, 0.03);
  border-color: rgba(255, 255, 255, 0.25);
  color: #ffffff;
}

.card-save-text {
  font-size: 10.5px;
  color: #64748b;
  text-align: center;
  margin-top: 14px;
}

/* FAQ Accordion Styling */
.faq-section {
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  padding-top: 56px;
}

.faq-section h2 {
  font-family: 'Roboto', sans-serif;
  font-size: 24px;
  font-weight: 600;
  color: #ffffff;
  margin-bottom: 36px;
  text-align: center;
}

.faq-accordion {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
  max-width: 760px;
  margin: 0 auto;
}

.faq-card {
  background: #121614;
  border: 1px solid rgba(255, 255, 255, 0.03);
  border-radius: 12px;
  padding: 24px;
}

.faq-card h3 {
  font-family: 'Roboto', sans-serif;
  font-size: 15px;
  font-weight: 600;
  color: #ffffff;
  margin-bottom: 10px;
}

.faq-card p {
  font-size: 13px;
  line-height: 1.6;
  color: #94a3b8;
  margin: 0;
}

/* ════ MODAL STYLING ════ */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 1000;
  background: rgba(8, 13, 9, 0.85);
  backdrop-filter: blur(16px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-box {
  background: #0d110f;
  border: 1px solid rgba(16, 185, 129, 0.2);
  border-radius: 24px;
  padding: 36px;
  width: 100%;
  max-width: 480px;
  position: relative;
  box-shadow: 0 30px 60px rgba(0, 0, 0, 0.6);
}

.modal-close-btn {
  position: absolute;
  top: 20px;
  right: 20px;
  background: rgba(255, 255, 255, 0.05);
  border: none;
  color: #94a3b8;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  font-size: 12px;
}

.modal-close-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #ffffff;
}

.step-progress {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-bottom: 24px;
}

.step-progress .dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
}

.step-progress .dot.active {
  background: #10b981;
  box-shadow: 0 0 8px rgba(16, 185, 129, 0.6);
}

.step-progress .dot.completed {
  background: rgba(16, 185, 129, 0.4);
}

.modal-step-content h2 {
  font-family: 'Roboto', sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: #ffffff;
  text-align: center;
  margin-bottom: 8px;
}

.modal-subtitle {
  font-size: 13px;
  line-height: 1.5;
  color: #64748b;
  text-align: center;
  margin-bottom: 24px;
}

.selected-package-badge {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-bottom: 20px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  padding: 8px;
  font-size: 12px;
}

.selected-package-badge .lbl {
  color: #64748b;
}

.selected-package-badge .val {
  color: #10b981;
  font-weight: 600;
}

.bank-details-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 16px;
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.detail-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 12.5px;
}

.detail-row .lbl {
  color: #64748b;
}

.detail-row .val {
  color: #ffffff;
  font-weight: 500;
}

.detail-row .copy-btn {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.2);
  font-size: 11px;
  padding: 3px 10px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.detail-row .copy-btn:hover {
  background: #10b981;
  color: #090b0a;
}

.detail-row .copy-btn.copied {
  background: rgba(16, 185, 129, 0.2);
  border-color: #10b981;
  color: #ffffff;
}

.highlight-row {
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  padding-top: 12px;
  margin-top: 4px;
}

.highlight-row .price-val {
  font-family: 'Roboto', sans-serif;
  font-size: 16px;
  font-weight: 700;
  color: #10b981;
}

.info-alert {
  background: rgba(245, 158, 11, 0.05);
  border: 1px solid rgba(245, 158, 11, 0.15);
  color: rgba(245, 158, 11, 0.85);
  border-radius: 10px;
  padding: 12px;
  font-size: 11.5px;
  line-height: 1.5;
  margin-bottom: 24px;
}

.modal-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.modal-primary-btn {
  background: #10b981;
  color: #090b0a;
  border: none;
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  font-weight: 700;
  padding: 14px;
  border-radius: 50px;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
}

.modal-primary-btn:hover:not(:disabled) {
  background: #34d399;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.modal-primary-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.modal-secondary-btn {
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #64748b;
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  font-weight: 600;
  padding: 12px;
  border-radius: 50px;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
}

.modal-secondary-btn:hover {
  background: rgba(255, 255, 255, 0.02);
  color: #ffffff;
  border-color: rgba(255, 255, 255, 0.2);
}

.upload-error-box {
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #f87171;
  border-radius: 10px;
  padding: 12px;
  font-size: 12.5px;
  line-height: 1.5;
  margin-bottom: 18px;
}

.hidden-file-input {
  display: none;
}

.upload-drop-zone {
  border: 2px dashed rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 32px 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  background: rgba(255, 255, 255, 0.01);
  margin-bottom: 24px;
}

.upload-drop-zone:hover {
  border-color: #10b981;
  background: rgba(16, 185, 129, 0.02);
}

.zone-has-file {
  border-color: #10b981;
  background: rgba(16, 185, 129, 0.02);
  padding: 16px;
}

.zone-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.upload-icon {
  font-size: 28px;
  margin-bottom: 8px;
}

.upload-title {
  font-size: 13px;
  font-weight: 600;
  color: #ffffff;
}

.upload-subtitle {
  font-size: 11px;
  color: #64748b;
  margin-top: 4px;
}

.zone-preview {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.zone-preview img {
  max-width: 100%;
  max-height: 180px;
  border-radius: 8px;
  object-fit: contain;
}

.zone-preview .file-name {
  font-size: 11.5px;
  color: #64748b;
  font-family: monospace;
  word-break: break-all;
}

/* Success Step Glow */
.success-glow {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: rgba(16, 185, 129, 0.1);
  border: 2px solid #10b981;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
}

.success-icon {
  font-size: 32px;
  font-weight: 700;
  color: #10b981;
}

/* Fade animation */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
