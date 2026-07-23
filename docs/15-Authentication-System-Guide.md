# Panduan Sistem Otentikasi & Keamanan (Login, Register & Email Activation)

Dokumen ini menjelaskan alur sistem otentikasi terbaru yang diimplementasikan pada platform Avenir Research, yang menggunakan **Email Activation (Verifikasi Email via SMTP)** dan **Google Login (SSO)**.

---

## 1. Alur Registrasi (Pembuatan Akun Baru)

Sistem pendaftaran dirancang aman dengan verifikasi email terlebih dahulu sebelum akun dapat digunakan secara penuh.

### A. Registrasi Manual (Email & Password)
1. Pengguna mengunjungi halaman **Register**.
2. Pengguna mengisi Form: Nama, Email, Password, dan Konfirmasi Password.
3. Setelah mendaftar, sistem membuat akun dengan status `email_verified_at = NULL`.
4. Sistem mengirimkan **Email Aktivasi / Verifikasi** secara otomatis via SMTP ke alamat email pengguna.
5. Pengguna mengklik tombol/link verifikasi di inbox email mereka.
6. Sistem memverifikasi token dan memperbarui status akun (`email_verified_at = now()`).
7. Pengguna diarahkan ke Dashboard/Katalog dan otomatis mengaktifkan paket **Free Trial**.

### B. Registrasi via Google (Google Login / SSO)
1. Pengguna mengklik tombol **"Lanjutkan dengan Google"**.
2. Pengguna memilih akun Google yang aktif.
3. Karena email sudah tervalidasi oleh Google, sistem secara otomatis mengeset `email_verified_at = now()`.
4. Pengguna langsung dapat menggunakan akun tanpa perlu verifikasi email manual.

---

## 2. Alur Login (Masuk ke Akun)

1. Pengguna mengunjungi halaman **Login**.
2. Pengguna memasukkan Email & Password atau memilih **Google Login**.
3. Sistem memeriksa kredensial:
   - **Pengguna ber-role `admin` / `tim_internal`**: Bebas dari kewajiban aktivasi email (`hasVerifiedEmail()` otomatis mengembalikan `true`) dan langsung masuk ke Admin Dashboard.
   - Jika kredensial valid namun **email pengguna umum belum diverifikasi**, sistem akan menampilkan notifikasi agar pengguna memeriksa inbox email (dengan opsi kirim ulang email verifikasi).
   - Jika email sudah terverifikasi, pengguna langsung masuk ke Dashboard/Katalog.

---


## 3. Alur Lupa Password

1. Pengguna mengunjungi halaman **Lupa Password** dan memasukkan email.
2. Sistem mengirimkan **Link Reset Password** ke email pengguna via SMTP.
3. Pengguna mengklik link dari email untuk membuat password baru.

---

## 4. Konfigurasi SMTP (Webuzo VPS & Laravel .env)

### A. Detail SMTP Server (dari Webuzo Panel)
- **Username**: `admin@researchavenir.com`
- **Password**: Password email `admin@researchavenir.com`
- **Outgoing Server (Host)**: `mail.researchavenir.com` (atau IP VPS `101.50.1.12`)
- **SMTP Port**: `465` (SSL) atau `587` (TLS)

### B. Konfigurasi `.env` Laravel
```env
MAIL_MAILER=smtp
MAIL_HOST=mail.researchavenir.com
MAIL_PORT=465
MAIL_USERNAME=admin@researchavenir.com
MAIL_PASSWORD=password_email_admin_anda
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="admin@researchavenir.com"
MAIL_FROM_NAME="${APP_NAME}"
```

*(Catatan: Jika menggunakan Port `465` set `MAIL_ENCRYPTION=ssl`. Jika menggunakan Port `587` set `MAIL_ENCRYPTION=tls`).*

---

## FAQ (Tanya Jawab Teknis)

**1. Mengapa beralih dari TOTP 2FA ke Email Activation?**
Email activation via SMTP lebih ramah pengguna (*user friendly*) untuk platform katalog riset saham publik, memudahkan proses *onboarding*, dan memastikan setiap pengguna mendaftar dengan email aktif yang valid.

**2. Bagaimana menangani email verifikasi yang masuk ke folder Spam?**
Pastikan VPS Webuzo sudah dikonfigurasi record DNS berikut di domain Registrar/DNS Manager:
- **SPF Record**: `v=spf1 ip4:101.50.1.12 ~all`
- **DKIM & DMARC**: Aktifkan fitur DKIM di Webuzo (`Email -> DKIM Manager`) dan tambahkan DMARC record di DNS.

