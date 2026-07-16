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
        <title>Kebijakan Privasi | Avenir Research</title>
        <meta name="description" content="Kebijakan privasi Avenir Research menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi data pribadi Anda serta penggunaan cookies dan layanan iklan Google AdSense." />
        <meta name="robots" content="index, follow" />
        <link rel="canonical" :href="'https://researchavenir.com/privacy-policy'" />
        <meta property="og:title" content="Kebijakan Privasi | Avenir Research" />
        <meta property="og:description" content="Kebijakan privasi Avenir Research: pengumpulan data, cookies, dan layanan iklan." />
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
                    <span>Kebijakan Privasi</span>
                </div>

                <div class="legal-header">
                    <div class="legal-badge">⚖️ DOKUMEN HUKUM</div>
                    <h1>Kebijakan Privasi</h1>
                    <p class="legal-subtitle">
                        Terakhir diperbarui: <strong>{{ lastUpdated }}</strong>
                    </p>
                    <p class="legal-intro">
                        Avenir Research (PT Avenir Fortuna Corporindo) berkomitmen untuk melindungi privasi Anda.
                        Kebijakan ini menjelaskan informasi apa yang kami kumpulkan, bagaimana kami menggunakannya,
                        dan hak-hak Anda terkait data pribadi Anda saat menggunakan layanan di
                        <strong>researchavenir.com</strong>.
                    </p>
                </div>

                <div class="legal-content">

                    <section class="legal-section">
                        <h2>1. Informasi yang Kami Kumpulkan</h2>
                        <p>Kami mengumpulkan beberapa jenis informasi untuk menyediakan dan meningkatkan layanan kami:</p>

                        <h3>a. Informasi yang Anda Berikan Langsung</h3>
                        <ul>
                            <li><strong>Data Akun:</strong> Nama lengkap, alamat email, dan kata sandi saat mendaftar.</li>
                            <li><strong>Data Pembayaran:</strong> Kami tidak menyimpan data kartu kredit/debit. Transaksi diproses oleh penyedia pembayaran pihak ketiga yang terotorisasi.</li>
                            <li><strong>Komentar & Interaksi:</strong> Komentar, suka (like), dan aktivitas interaksi Anda di platform.</li>
                        </ul>

                        <h3>b. Informasi yang Dikumpulkan Secara Otomatis</h3>
                        <ul>
                            <li><strong>Log Data:</strong> Alamat IP, jenis browser, halaman yang dikunjungi, waktu kunjungan, dan data teknis lainnya.</li>
                            <li><strong>Cookies:</strong> File kecil yang disimpan di perangkat Anda untuk mengidentifikasi sesi dan preferensi pengguna.</li>
                            <li><strong>Data Penggunaan:</strong> Halaman yang dilihat, tautan yang diklik, dan waktu yang dihabiskan di platform.</li>
                        </ul>
                    </section>

                    <section class="legal-section">
                        <h2>2. Penggunaan Cookies</h2>
                        <p>Kami menggunakan cookies untuk keperluan berikut:</p>
                        <ul>
                            <li><strong>Cookies Esensial:</strong> Diperlukan untuk fungsi dasar website seperti login dan keamanan sesi.</li>
                            <li><strong>Cookies Analitik:</strong> Membantu kami memahami cara pengunjung menggunakan website (misalnya Google Analytics).</li>
                            <li><strong>Cookies Iklan (Google AdSense):</strong> Google AdSense menggunakan cookies untuk menampilkan iklan yang relevan berdasarkan kunjungan Anda sebelumnya ke website ini dan website lain. Anda dapat memilih keluar dari personalisasi iklan melalui <a href="https://www.google.com/settings/ads" target="_blank" rel="nofollow noopener">Pengaturan Iklan Google</a>.</li>
                        </ul>
                        <p>Anda dapat menonaktifkan cookies melalui pengaturan browser Anda, namun ini dapat mempengaruhi fungsi website.</p>
                    </section>

                    <section class="legal-section">
                        <h2>3. Google AdSense dan Iklan Pihak Ketiga</h2>
                        <p>
                            Website ini menggunakan <strong>Google AdSense</strong> untuk menampilkan iklan. Google AdSense menggunakan cookies DART untuk menampilkan iklan kepada pengunjung berdasarkan minat dan kunjungan sebelumnya.
                        </p>
                        <p>
                            Vendor pihak ketiga, termasuk Google, menggunakan cookies untuk menayangkan iklan berdasarkan kunjungan sebelumnya pengguna ke website ini atau website lainnya. Pengguna dapat memilih keluar dari penggunaan cookies DART dengan mengunjungi <a href="https://policies.google.com/technologies/ads" target="_blank" rel="nofollow noopener">halaman kebijakan iklan Google</a>.
                        </p>
                        <p>
                            Kami mengikuti <a href="https://support.google.com/adspolicy" target="_blank" rel="nofollow noopener">Kebijakan Program Google AdSense</a> dalam menampilkan iklan di platform kami.
                        </p>
                    </section>

                    <section class="legal-section">
                        <h2>4. Bagaimana Kami Menggunakan Informasi Anda</h2>
                        <p>Informasi yang kami kumpulkan digunakan untuk:</p>
                        <ul>
                            <li>Menyediakan, mengoperasikan, dan meningkatkan layanan Avenir Research.</li>
                            <li>Mengelola akun dan langganan Anda.</li>
                            <li>Mengirimkan komunikasi terkait layanan (pembaruan, konfirmasi langganan).</li>
                            <li>Menganalisis penggunaan platform untuk meningkatkan pengalaman pengguna.</li>
                            <li>Memenuhi kewajiban hukum dan peraturan yang berlaku di Indonesia.</li>
                        </ul>
                        <p>Kami <strong>tidak menjual</strong> data pribadi Anda kepada pihak ketiga.</p>
                    </section>

                    <section class="legal-section">
                        <h2>5. Berbagi Data dengan Pihak Ketiga</h2>
                        <p>Kami dapat membagikan informasi Anda kepada pihak ketiga terpercaya hanya dalam kondisi berikut:</p>
                        <ul>
                            <li><strong>Penyedia Layanan:</strong> Pihak yang membantu operasional platform (hosting, pembayaran, email) yang terikat perjanjian kerahasiaan.</li>
                            <li><strong>Kewajiban Hukum:</strong> Jika diwajibkan oleh hukum, peraturan, atau proses hukum yang berlaku.</li>
                            <li><strong>Perlindungan Hak:</strong> Untuk melindungi hak, properti, atau keselamatan Avenir Research dan pengguna kami.</li>
                        </ul>
                    </section>

                    <section class="legal-section">
                        <h2>6. Keamanan Data</h2>
                        <p>
                            Kami menerapkan langkah-langkah keamanan teknis dan organisasional yang wajar untuk melindungi data pribadi Anda dari akses tidak sah, penggunaan yang salah, atau pengungkapan. Ini mencakup enkripsi HTTPS untuk semua transmisi data, penyimpanan kata sandi yang dienkripsi (hashed), dan akses database yang dibatasi.
                        </p>
                        <p>
                            Namun, tidak ada metode transmisi internet atau penyimpanan elektronik yang 100% aman. Kami tidak dapat menjamin keamanan absolut.
                        </p>
                    </section>

                    <section class="legal-section">
                        <h2>7. Hak-Hak Anda</h2>
                        <p>Sebagai pengguna layanan kami, Anda memiliki hak untuk:</p>
                        <ul>
                            <li><strong>Akses:</strong> Meminta salinan data pribadi yang kami simpan tentang Anda.</li>
                            <li><strong>Koreksi:</strong> Meminta perbaikan data yang tidak akurat atau tidak lengkap.</li>
                            <li><strong>Penghapusan:</strong> Meminta penghapusan data pribadi Anda (dengan mempertimbangkan kewajiban hukum yang berlaku).</li>
                            <li><strong>Penarikan Persetujuan:</strong> Menarik persetujuan Anda untuk pemrosesan data kapan saja.</li>
                        </ul>
                        <p>Untuk menggunakan hak-hak ini, hubungi kami melalui email di bawah.</p>
                    </section>

                    <section class="legal-section">
                        <h2>8. Penyimpanan Data</h2>
                        <p>
                            Kami menyimpan data pribadi Anda selama akun Anda aktif atau selama diperlukan untuk menyediakan layanan. Jika Anda menghapus akun, kami akan menghapus atau menganonimkan data Anda dalam waktu 30 hari, kecuali jika penyimpanan lebih lama diwajibkan oleh hukum.
                        </p>
                    </section>

                    <section class="legal-section">
                        <h2>9. Layanan Pihak Ketiga</h2>
                        <p>Platform kami dapat berisi tautan ke website pihak ketiga. Kami tidak bertanggung jawab atas praktik privasi website tersebut. Kami mendorong Anda untuk membaca kebijakan privasi setiap website yang Anda kunjungi.</p>
                    </section>

                    <section class="legal-section">
                        <h2>10. Perubahan Kebijakan Privasi</h2>
                        <p>
                            Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Perubahan signifikan akan diinformasikan melalui pemberitahuan di website atau email. Tanggal pembaruan terakhir selalu tercantum di bagian atas halaman ini.
                        </p>
                        <p>Dengan terus menggunakan layanan kami setelah perubahan diterbitkan, Anda menyetujui kebijakan yang telah diperbarui.</p>
                    </section>

                    <section class="legal-section">
                        <h2>11. Hubungi Kami</h2>
                        <p>Jika Anda memiliki pertanyaan, kekhawatiran, atau permintaan terkait privasi Anda, silakan hubungi kami:</p>
                        <div class="contact-box">
                            <p><strong>PT Avenir Fortuna Corporindo</strong></p>
                            <p>Email: <a href="mailto:info@researchavenir.com">info@researchavenir.com</a></p>
                            <p>Website: <a href="https://researchavenir.com">researchavenir.com</a></p>
                            <p>Negara: Indonesia</p>
                        </div>
                    </section>

                </div>

                <div class="legal-footer-nav">
                    <Link href="/" class="legal-nav-link">← Kembali ke Beranda</Link>
                    <Link href="/syarat-penggunaan" class="legal-nav-link">Syarat & Ketentuan →</Link>
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

.legal-section h3 {
    font-size: 14px;
    font-weight: 700;
    color: #cbd5e1;
    margin: 20px 0 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
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

.contact-box {
    background: rgba(16, 185, 129, 0.05);
    border: 1px solid rgba(16, 185, 129, 0.15);
    border-radius: 10px;
    padding: 20px 24px;
    margin-top: 16px;
}

.contact-box p {
    margin-bottom: 6px !important;
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
