# Panduan Sistem Otentikasi & Keamanan (Login, Register & Lupa Password)

Dokumen ini menjelaskan alur sistem otentikasi terbaru yang telah diimplementasikan pada platform Avenir Research, yang mencakup keamanan tingkat tinggi menggunakan **Google Authenticator (2FA)** dan integrasi **Google Login (SSO)**.

---

## 1. Alur Registrasi (Pembuatan Akun Baru)

Sistem pendaftaran kini dirancang untuk lebih terpusat dan aman. 

### A. Registrasi Manual (Menggunakan Email & Password)
1. Pengguna mengunjungi halaman **Register** (tidak lagi menggunakan *pop-up modal* untuk meningkatkan kenyamanan di perangkat *mobile*).
2. Pengguna mengisi Form: Nama, Email, Password, dan Konfirmasi Password.
3. Setelah klik daftar, sistem akan langsung membuatkan **QR Code rahasia**.
4. Pengguna **diwajibkan** mengunduh aplikasi Google Authenticator di *smartphone* mereka dan memindai QR Code tersebut.
5. Pengguna harus memasukkan 6-digit OTP dari aplikasi untuk memverifikasi pemasangan 2FA.
6. Sistem akan memberikan **Recovery Codes** (Kode Pemulihan). Pengguna wajib menyimpannya di tempat yang aman.
7. Setelah selesai, akun akan otomatis mendapatkan profil dan bisa mengklaim paket **Free Trial**.

### B. Registrasi via Google (Google Login / SSO)
1. Pengguna mengklik tombol **"Lanjutkan dengan Google"**.
2. Pengguna memilih akun Google mereka yang aktif (sistem mendapatkan nama dan email yang sudah pasti tervalidasi oleh Google).
3. Sama seperti registrasi manual, setelah profil Google terhubung, pengguna akan **diarahkan ke halaman Setup 2FA**.
4. Pengguna tetap harus memindai QR Code dan memasukkan OTP untuk menjaga standar keamanan yang seragam.
5. Setelah 2FA selesai, akun siap digunakan.

*(Catatan: Pengguna Google Login tidak memerlukan password, karena otentikasi ditangani langsung oleh Google).*

---

## 2. Alur Login (Masuk ke Akun)

1. Pengguna mengunjungi halaman **Login**.
2. Pengguna dapat memilih login manual (Email & Password) atau via **Google**.
3. Jika kredensial benar, pengguna akan diarahkan ke halaman **Verifikasi 2FA**.
4. Pengguna harus membuka aplikasi Google Authenticator di HP mereka dan memasukkan 6-digit angka yang berubah setiap 30 detik.
5. Jika pengguna kehilangan HP atau akses ke Google Authenticator, mereka dapat menggunakan salah satu **Recovery Code** yang diberikan saat registrasi untuk masuk secara darurat.
6. Setelah lolos tahap verifikasi 2FA, barulah pengguna masuk ke Dashboard/Katalog.

---

## 3. Alur Lupa Password

Alur lupa password telah dimodifikasi secara khusus agar mengakomodir pengguna baru dengan 2FA dan pengguna lama (Legacy).

1. Pengguna mengunjungi halaman **Lupa Password** dan memasukkan email mereka.
2. Sistem akan mendeteksi tipe akun pengguna tersebut:
   
   - **Tipe A (Akun Baru dengan 2FA Aktif):**
     Sistem akan meminta pengguna untuk memasukkan **Kode 2FA OTP** (atau Recovery Code) untuk membuktikan bahwa itu benar-benar pemilik akun, bukan sekadar orang yang mengetahui emailnya. Setelah lolos, pengguna bisa memasukkan password baru.
   
   - **Tipe B (Akun Lama/Legacy tanpa 2FA):**
     Karena akun lama belum mengaktifkan Google Authenticator di masa lalu, sistem akan **mengirimkan Link Reset Password ke Inbox Email** mereka secara otomatis (*Fallback*). Pengguna mengklik link dari email untuk mereset password dengan aman.

---

## FAQ (Tanya Jawab Teknis)

**1. Mengapa QR Code 2FA tidak dimunculkan saat Lupa Password?**
Menampilkan QR Code di halaman publik hanya dengan modal mengetahui "Email" merupakan celah keamanan fatal. Siapapun bisa mengetik email pengguna lain dan mencuri akses 2FA-nya. QR Code hanya dibuat dan ditampilkan satu kali secara eksklusif saat pengguna *sudah berhasil melewati proses registrasi/login pertama kalinya*.

**2. Apakah fitur Google Login memerlukan izin khusus dari Klien?**
Developer cukup mendaftarkan *project* di Google Cloud Console menggunakan akun email admin/developer. Bebas menggunakan email Gmail klien ataupun developer, selama *Client ID* dan *Client Secret* didapatkan dan dimasukkan ke dalam *environment* sistem (file `.env`).

**3. Apa yang terjadi jika pengguna benar-benar kehilangan HP dan Recovery Codes?**
Ini adalah tingkat keamanan *strict*. Jika pengguna kehilangan keduanya, mereka kehilangan akses ke akun secara permanen kecuali mereka menghubungi *Customer Support* / Admin Avenir yang memiliki akses *database* untuk mereset *secret key* 2FA akun tersebut secara manual.
