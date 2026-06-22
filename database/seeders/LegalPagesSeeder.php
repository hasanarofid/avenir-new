<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Section;
use Illuminate\Database\Seeder;

class LegalPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'meta_description' => 'Kebijakan privasi Avenir Research mengenai pengumpulan dan penggunaan data pengguna.',
                'content' => '<h2>Kebijakan Privasi (Privacy Policy)</h2>
                <p>Terakhir diperbarui: ' . date('d F Y') . '</p>
                <p>Di Avenir Research (dapat diakses dari website kami), salah satu prioritas utama kami adalah privasi pengunjung kami. Dokumen Kebijakan Privasi ini berisi jenis informasi yang dikumpulkan dan dicatat oleh Avenir Research dan bagaimana kami menggunakannya.</p>
                <p>Jika Anda memiliki pertanyaan tambahan atau memerlukan informasi lebih lanjut tentang Kebijakan Privasi kami, jangan ragu untuk menghubungi kami.</p>
                
                <h3>File Log</h3>
                <p>Avenir Research mengikuti prosedur standar menggunakan file log. File-file ini mencatat pengunjung saat mereka mengunjungi situs web. Semua perusahaan hosting melakukan ini dan merupakan bagian dari analitik layanan hosting. Informasi yang dikumpulkan oleh file log termasuk alamat protokol internet (IP), jenis browser, Internet Service Provider (ISP), stempel tanggal dan waktu, halaman rujukan/keluar, dan mungkin jumlah klik. Ini tidak terkait dengan informasi apa pun yang dapat diidentifikasi secara pribadi. Tujuan informasi ini adalah untuk menganalisis tren, mengelola situs, melacak pergerakan pengguna di situs web, dan mengumpulkan informasi demografis.</p>
                
                <h3>Cookie dan Suar Web</h3>
                <p>Seperti situs web lainnya, Avenir Research menggunakan "cookies". Cookie ini digunakan untuk menyimpan informasi termasuk preferensi pengunjung, dan halaman-halaman di situs web yang diakses atau dikunjungi pengunjung. Informasi tersebut digunakan untuk mengoptimalkan pengalaman pengguna dengan menyesuaikan konten halaman web kami berdasarkan jenis browser pengunjung dan/atau informasi lainnya.</p>
                
                <h3>Google DoubleClick DART Cookie</h3>
                <p>Google adalah salah satu vendor pihak ketiga di situs kami. Google juga menggunakan cookie, yang dikenal sebagai cookie DART, untuk menayangkan iklan kepada pengunjung situs kami berdasarkan kunjungan mereka ke www.website.com dan situs lain di internet. Namun, pengunjung dapat memilih untuk menolak penggunaan cookie DART dengan mengunjungi Kebijakan Privasi jaringan iklan dan konten Google di URL berikut – <a href="https://policies.google.com/technologies/ads" target="_blank">https://policies.google.com/technologies/ads</a></p>
                
                <h3>Mitra Iklan Kami</h3>
                <p>Beberapa pengiklan di situs kami mungkin menggunakan cookie dan suar web. Mitra iklan kami tercantum di bawah ini. Setiap mitra iklan kami memiliki Kebijakan Privasi mereka sendiri untuk kebijakan mereka tentang data pengguna.</p>
                <ul><li>Google AdSense</li></ul>
                
                <h3>Kebijakan Privasi Pihak Ketiga</h3>
                <p>Kebijakan Privasi Avenir Research tidak berlaku untuk pengiklan atau situs web lain. Karena itu, kami menyarankan Anda untuk berkonsultasi dengan Kebijakan Privasi masing-masing dari server iklan pihak ketiga ini untuk informasi lebih rinci.</p>
                
                <h3>Persetujuan</h3>
                <p>Dengan menggunakan situs web kami, Anda dengan ini menyetujui Kebijakan Privasi kami dan menyetujui syarat dan ketentuannya.</p>'
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'meta_description' => 'Syarat dan Ketentuan layanan penggunaan Avenir Research',
                'content' => '<h2>Syarat dan Ketentuan (Terms of Service)</h2>
                <p>Selamat datang di Avenir Research!</p>
                <p>Syarat dan ketentuan ini menguraikan aturan dan peraturan untuk penggunaan Situs Web Avenir Research.</p>
                <p>Dengan mengakses situs web ini, kami menganggap Anda menerima syarat dan ketentuan ini. Jangan terus menggunakan Avenir Research jika Anda tidak setuju untuk menyetujui semua syarat dan ketentuan yang dinyatakan di halaman ini.</p>
                
                <h3>Lisensi dan Hak Cipta</h3>
                <p>Kecuali dinyatakan lain, Avenir Research dan/atau pemberi lisensinya memiliki hak kekayaan intelektual atas semua materi di Avenir Research. Semua hak kekayaan intelektual dilindungi undang-undang. Anda dapat mengakses ini dari Avenir Research untuk penggunaan pribadi Anda sendiri dengan tunduk pada batasan yang diatur dalam syarat dan ketentuan ini.</p>
                <p>Anda tidak boleh:</p>
                <ul>
                    <li>Mempublikasikan ulang materi dari Avenir Research tanpa izin tertulis</li>
                    <li>Menjual, menyewakan, atau mensublisensikan materi dari Avenir Research</li>
                    <li>Mereproduksi, menggandakan, atau menyalin materi dari Avenir Research untuk tujuan komersial</li>
                    <li>Mendistribusikan ulang konten dari Avenir Research</li>
                </ul>
                
                <h3>Komentar Pengguna</h3>
                <p>Bagian tertentu dari situs web ini menawarkan kesempatan bagi pengguna untuk memposting dan bertukar pendapat dan informasi. Avenir Research tidak menyaring, mengedit, menerbitkan, atau meninjau Komentar sebelum kehadiran mereka di situs web. Komentar tidak mencerminkan pandangan dan pendapat Avenir Research, agennya, dan/atau afiliasinya.</p>
                
                <h3>Penafian Kewajiban</h3>
                <p>Sejauh diizinkan oleh hukum yang berlaku, kami mengecualikan semua representasi, jaminan, dan kondisi yang berkaitan dengan situs web kami dan penggunaan situs web ini. Layanan dan informasi di website ini disediakan "sebagaimana adanya" (as is).</p>'
            ],
            [
                'title' => 'Disclaimer',
                'slug' => 'disclaimer',
                'meta_description' => 'Penafian (Disclaimer) investasi dan informasi di Avenir Research',
                'content' => '<h2>Penafian (Disclaimer)</h2>
                <p>Semua informasi di situs web Avenir Research diterbitkan dengan itikad baik dan hanya untuk tujuan informasi umum dan edukasi. Avenir Research tidak memberikan jaminan apa pun tentang kelengkapan, keandalan, dan keakuratan informasi ini.</p>
                
                <h3>Bukan Nasihat Keuangan (Not Financial Advice)</h3>
                <p>Avenir Research <strong>BUKAN</strong> merupakan penasihat keuangan, sekuritas, atau broker yang terdaftar. Semua laporan, analisis, harga target (target price), metrik, dan opini yang dipublikasikan di situs ini murni merupakan hasil riset independen dan hanya ditujukan sebagai sarana edukasi dan informasi.</p>
                <p>Informasi yang terdapat di website ini tidak boleh dianggap sebagai rekomendasi, ajakan, atau penawaran untuk membeli atau menjual efek, saham, atau instrumen investasi apa pun. Anda memikul tanggung jawab penuh atas semua keputusan investasi Anda sendiri.</p>
                
                <h3>Risiko Investasi</h3>
                <p>Investasi di pasar modal mengandung risiko kerugian finansial yang signifikan. Kinerja masa lalu sebuah saham atau aset tidak menjamin kinerja di masa depan. Setiap tindakan yang Anda ambil atas informasi yang Anda temukan di situs web ini (Avenir Research), sepenuhnya merupakan risiko Anda sendiri. Avenir Research tidak akan bertanggung jawab atas kerugian dan/atau kerusakan apa pun sehubungan dengan penggunaan situs web kami.</p>
                
                <h3>Tautan Eksternal</h3>
                <p>Dari situs web kami, Anda dapat mengunjungi situs web lain dengan mengikuti hyper-link ke situs eksternal tersebut. Meskipun kami berusaha untuk hanya menyediakan tautan berkualitas ke situs web yang bermanfaat dan etis, kami tidak memiliki kendali atas konten dan sifat situs-situs tersebut.</p>
                
                <h3>Persetujuan</h3>
                <p>Dengan menggunakan situs web kami, Anda dengan ini menyetujui penafian (disclaimer) kami dan menyetujui persyaratannya.</p>'
            ]
        ];

        foreach ($pages as $p) {
            $page = Page::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'title' => $p['title'],
                    'meta_description' => $p['meta_description'],
                    'is_active' => true
                ]
            );

            Section::updateOrCreate(
                [
                    'page_id' => $page->id,
                    'key' => 'content'
                ],
                [
                    'title' => 'Konten Halaman',
                    'order' => 1,
                    'content' => ['body' => $p['content']],
                    'is_active' => true
                ]
            );
        }
    }
}
