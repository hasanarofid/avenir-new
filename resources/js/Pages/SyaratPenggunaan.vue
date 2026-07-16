<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    meta: { type: Object, default: () => ({}) }
});

const lastUpdated = '16 Juli 2026';
</script>

<template>
    <Head>
        <title>Syarat & Ketentuan Penggunaan | Avenir Research</title>
        <meta name="description" content="Syarat dan ketentuan penggunaan layanan Avenir Research. Harap baca dengan seksama sebelum menggunakan platform riset pasar modal kami." />
        <meta name="robots" content="index, follow" />
        <link rel="canonical" :href="'https://researchavenir.com/syarat-penggunaan'" />
        <meta property="og:title" content="Syarat & Ketentuan Penggunaan | Avenir Research" />
        <meta property="og:description" content="Syarat dan ketentuan penggunaan layanan Avenir Research." />
        <meta property="og:type" content="website" />
        <meta name="language" content="id-ID" />
    </Head>

    <AppLayout>
        <div class="legal-page-wrapper">
            <div class="radial-glow glow-top-right"></div>
            <div class="radial-glow glow-bottom-left"></div>

            <div class="legal-container">
                <div class="legal-breadcrumb">
                    <Link href="/">Beranda</Link>
                    <span class="sep">›</span>
                    <span>Syarat & Ketentuan</span>
                </div>

                <div class="legal-header">
                    <div class="legal-badge">📄 KETENTUAN LAYANAN</div>
                    <h1>Syarat & Ketentuan</h1>
                    <p class="legal-subtitle">
                        Terakhir diperbarui: <strong>{{ lastUpdated }}</strong>
                    </p>
                    <p class="legal-intro">
                        Selamat datang di Avenir Research. Dokumen ini mengatur syarat dan ketentuan penggunaan website 
                        <strong>researchavenir.com</strong> dan layanan terkait kami. Dengan mengakses atau menggunakan platform kami, 
                        Anda setuju untuk terikat oleh syarat-syarat ini.
                    </p>
                </div>

                <div class="legal-content">

                    <section class="legal-section">
                        <h2>1. Sifat Layanan</h2>
                        <p>
                            Avenir Research menyediakan laporan riset independen, analisis pasar, dan data historis terkait pasar modal Indonesia. 
                            <strong>PENTING:</strong> Kami BUKAN penasihat investasi terdaftar, broker-dealer, atau manajer investasi.
                        </p>
                        <ul>
                            <li>Seluruh informasi yang disajikan di platform ini murni untuk tujuan pendidikan, informasi, dan riset semata.</li>
                            <li>Tidak ada bagian dari konten kami yang boleh ditafsirkan sebagai ajakan, rekomendasi, saran keuangan, penawaran jual, atau penawaran beli atas sekuritas apa pun.</li>
                            <li>Laporan riset kami didasarkan pada analisis independen atas laporan keuangan (audited), data bursa, dan informasi publik lainnya yang kami yakini kebenarannya, namun kami tidak menjamin akurasi mutlak.</li>
                        </ul>
                    </section>

                    <section class="legal-section">
                        <h2>2. Peringatan Risiko (Disclaimer)</h2>
                        <p>
                            Berinvestasi di pasar modal (termasuk saham) mengandung risiko tinggi, termasuk potensi hilangnya seluruh modal yang diinvestasikan. 
                            Kinerja masa lalu sebuah saham, sektor, atau pasar tidak menjamin hasil di masa depan.
                        </p>
                        <p>
                            Avenir Research (termasuk analis, penulis, dan afiliasinya) <strong>tidak bertanggung jawab secara hukum maupun finansial</strong> atas kerugian langsung maupun tidak langsung yang dialami oleh pengguna akibat keputusan investasi atau trading yang didasarkan pada konten di website ini. Keputusan investasi adalah tanggung jawab Anda sepenuhnya.
                        </p>
                    </section>

                    <section class="legal-section">
                        <h2>3. Akses dan Akun Pengguna</h2>
                        <ul>
                            <li>Anda bertanggung jawab menjaga kerahasiaan kata sandi dan akun Anda.</li>
                            <li>Akses ke konten premium (berbayar) disediakan secara eksklusif untuk satu pengguna/akun perseorangan (Single User License) atau institusi sesuai paket berlangganan.</li>
                            <li>Anda dilarang keras membagikan kredensial login Anda kepada pihak lain atau memfasilitasi akses secara kolektif (patungan) menggunakan satu akun.</li>
                        </ul>
                    </section>

                    <section class="legal-section">
                        <h2>4. Hak Kekayaan Intelektual (Hak Cipta)</h2>
                        <p>
                            Seluruh konten, laporan riset, desain, grafik, logo, dan format penyajian di <strong>researchavenir.com</strong> adalah properti intelektual milik PT Avenir Fortuna Corporindo dan dilindungi oleh Undang-Undang Hak Cipta Republik Indonesia serta ketentuan hukum internasional.
                        </p>
                        <ul>
                            <li>Pengguna diizinkan mengunduh dan mencetak laporan (untuk pelanggan berbayar) <strong>hanya untuk penggunaan pribadi dan internal</strong>.</li>
                            <li>Dilarang keras menyalin, mereproduksi, mendistribusikan ulang (redistribute), mempublikasikan, atau menjual kembali konten kami, baik sebagian maupun seluruhnya, tanpa izin tertulis dari kami.</li>
                        </ul>
                    </section>

                    <section class="legal-section">
                        <h2>5. Langganan dan Kebijakan Pengembalian Dana</h2>
                        <ul>
                            <li>Layanan premium Avenir ditawarkan dengan sistem berlangganan bulanan atau tahunan.</li>
                            <li><strong>Tidak Ada Pengembalian Dana (No Refunds):</strong> Semua pembayaran yang telah diproses bersifat final. Kami tidak menawarkan pengembalian dana prorata untuk pembatalan di tengah periode berlangganan.</li>
                            <li>Anda dapat membatalkan perpanjangan otomatis langganan kapan saja melalui halaman profil. Setelah dibatalkan, Anda tetap dapat mengakses konten premium hingga akhir periode penagihan saat ini.</li>
                        </ul>
                    </section>

                    <section class="legal-section">
                        <h2>6. Iklan Google AdSense dan Afiliasi</h2>
                        <p>
                            Sebagai upaya mempertahankan sebagian konten gratis, website kami dapat menampilkan iklan yang dilayani oleh pihak ketiga (termasuk Google AdSense). Avenir Research tidak memiliki kontrol langsung atas konten iklan pihak ketiga tersebut. Kami tidak mendukung produk atau layanan tertentu yang diiklankan melalui jaringan pihak ketiga.
                        </p>
                    </section>

                    <section class="legal-section">
                        <h2>7. Perilaku Pengguna</h2>
                        <p>Saat menggunakan website kami (termasuk fitur komentar), Anda setuju untuk tidak:</p>
                        <ul>
                            <li>Memposting konten yang melanggar hukum, memfitnah, melecehkan, atau mengandung unsur SARA.</li>
                            <li>Menyebarkan spam, tautan phishing, atau malware.</li>
                            <li>Berusaha meretas (hacking), mencari kelemahan keamanan (vulnerability scanning), atau melakukan teknik scraping massal (data mining) terhadap server atau konten kami.</li>
                        </ul>
                        <p>Kami berhak penuh untuk menghapus komentar dan/atau memblokir akun pengguna yang melanggar ketentuan ini tanpa kompensasi apa pun.</p>
                    </section>

                    <section class="legal-section">
                        <h2>8. Yurisdiksi dan Penyelesaian Sengketa</h2>
                        <p>
                            Syarat dan Ketentuan ini tunduk pada dan ditafsirkan berdasarkan hukum Republik Indonesia. Setiap sengketa yang timbul dari atau terkait dengan penggunaan platform ini akan diselesaikan secara musyawarah mufakat. Jika musyawarah gagal, sengketa akan diselesaikan di bawah yurisdiksi pengadilan di wilayah hukum Republik Indonesia.
                        </p>
                    </section>

                </div>

                <div class="legal-footer-nav">
                    <Link href="/privacy-policy" class="legal-nav-link">← Kebijakan Privasi</Link>
                    <Link href="/" class="legal-nav-link">Kembali ke Beranda →</Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.legal-page-wrapper {
    background-color: #090b0a;
    color: #cbd5e1;
    min-height: calc(100vh - 52px);
    padding: 60px 0 100px;
    position: relative;
    overflow: hidden;
}

.legal-page-wrapper .radial-glow {
    position: absolute;
    border-radius: 50%;
    filter: blur(140px);
    pointer-events: none;
    z-index: 1;
    opacity: 0.08;
}

.legal-page-wrapper .glow-top-right {
    top: -10%;
    right: -5%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, #10B981 0%, transparent 70%);
}

.legal-page-wrapper .glow-bottom-left {
    bottom: 5%;
    left: -10%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.6) 0%, transparent 70%);
}

.legal-container {
    max-width: 860px;
    margin: 0 auto;
    padding: 0 24px;
    position: relative;
    z-index: 2;
}

.legal-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #64748b;
    margin-bottom: 32px;
}

.legal-breadcrumb a {
    color: #10b981;
    text-decoration: none;
    transition: color 0.2s;
}

.legal-breadcrumb a:hover {
    color: #34d399;
}

.legal-breadcrumb .sep {
    color: #374151;
}

.legal-header {
    margin-bottom: 48px;
    padding-bottom: 32px;
    border-bottom: 1px solid rgba(16, 185, 129, 0.12);
}

.legal-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    color: #10b981;
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.2);
    padding: 4px 12px;
    border-radius: 20px;
    margin-bottom: 16px;
}

.legal-header h1 {
    font-size: 36px;
    font-weight: 800;
    color: #f1f5f9;
    margin: 0 0 8px;
    line-height: 1.2;
}

.legal-subtitle {
    font-size: 14px;
    color: #64748b;
    margin: 0 0 20px;
}

.legal-intro {
    font-size: 15px;
    line-height: 1.8;
    color: #94a3b8;
    background: rgba(16, 185, 129, 0.04);
    border-left: 3px solid rgba(16, 185, 129, 0.4);
    padding: 16px 20px;
    border-radius: 0 8px 8px 0;
}

.legal-content {
    display: flex;
    flex-direction: column;
    gap: 40px;
}

.legal-section {
    padding: 28px 32px;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 12px;
    transition: border-color 0.2s;
}

.legal-section:hover {
    border-color: rgba(16, 185, 129, 0.15);
}

.legal-section h2 {
    font-size: 18px;
    font-weight: 700;
    color: #10b981;
    margin: 0 0 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(16, 185, 129, 0.1);
}

.legal-section p {
    font-size: 14px;
    line-height: 1.8;
    color: #94a3b8;
    margin: 0 0 12px;
}

.legal-section p:last-child {
    margin-bottom: 0;
}

.legal-section ul {
    padding-left: 20px;
    margin: 8px 0 12px;
}

.legal-section li {
    font-size: 14px;
    line-height: 1.8;
    color: #94a3b8;
    margin-bottom: 6px;
}

.legal-section strong {
    color: #cbd5e1;
    font-weight: 600;
}

.legal-section a {
    color: #10b981;
    text-decoration: underline;
    text-underline-offset: 2px;
    transition: color 0.2s;
}

.legal-section a:hover {
    color: #34d399;
}

.legal-footer-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 48px;
    padding-top: 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.legal-nav-link {
    color: #10b981;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: color 0.2s;
}

.legal-nav-link:hover {
    color: #34d399;
}

@media (max-width: 640px) {
    .legal-header h1 {
        font-size: 26px;
    }
    .legal-section {
        padding: 20px 18px;
    }
    .legal-footer-nav {
        flex-direction: column;
        gap: 16px;
        text-align: center;
    }
}
</style>
