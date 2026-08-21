# DocVerify IPPTI - Portal Sistem Verifikasi Dokumen Terjemahan Resmi
**Platform Resmi Ikatan Penerjemah Indonesia (IPPTI) bekerjasama dengan PT Benlaris Sukses Indonesia**

---

## 📖 Buku Panduan, PRD & Dokumentasi Lengkap
Panduan lengkap dan spesifikasi kebutuhan produk (*Product Requirement Document*) telah tersedia pada berkas:
👉 **[PANDUAN_LENGKAP_DOCVERIFY_IPPTI.md](PANDUAN_LENGKAP_DOCVERIFY_IPPTI.md)** (Buku Panduan Penggunaan 4 Peran User)
👉 **[PRD_DOCVERIFY_IPPTI.md](PRD_DOCVERIFY_IPPTI.md)** (Dokumen Kebutuhan Produk / Product Requirement Document)

Daftar panduan & PRD mencakup:
1. **User Publik (Masyarakat, Kedutaan, Notaris, Kementerian)**: Panduan scan QR code & pencarian keabsahan SK penerjemah.
2. **Penerjemah Tersumpah (Translator Reguler & PRO)**: Input dokumen, impor Excel massal, unduh QR Code, top-up poin otomatis, klaim voucher promo, dan instalasi PWA di smartphone.
3. **Pengurus IPPTI (Admin)**: Manajemen keanggotaan penerjemah, monitoring dokumen, laporan keuangan & bagi hasil 50:50, pembuatan voucher diskon, dan master data.
4. **Super Admin & Auditor**: Portal audit nasional, audit log security trail, konfigurasi payment gateway Xenith Pay, dan pengaturan rekening payout.

---

## 🚀 Fitur Utama Sistem
* **Verifikasi QR Code Realtime**: Validasi instan keaslian dokumen terjemahan resmi melalui pemindaian kamera HP.
* **Integrasi Xenith Pay Live**: Pembayaran top-up poin otomatis dengan QRIS dan Virtual Account Bank.
* **Skema Transparan Bagi Hasil 50:50**: Perhitungan proporsional otomatis antara kas organisasi IPPTI dan pengembang Benlaris berdasarkan saldo bersih real Xenith Pay.
* **Progressive Web App (PWA)**: Dapat diinstall langsung di smartphone Android dan iOS (iPhone/iPad).
* **Impor Berkas Excel Massal**: Kemudahan mendaftarkan puluhan hingga ratusan dokumen sekaligus dengan validasi All-or-Nothing.

---

## 🛠️ Persyaratan Sistem & Instalasi
* **PHP**: `>= 8.2`
* **Database**: MySQL / MariaDB / SQLite
* **Ekstensi PHP**: `BCMath`, `Ctype`, `cURL`, `DOM`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`, `PDO`, `Tokenizer`, `XML`
* **Composer**: `>= 2.0`

### Langkah Instalasi Cepat:
```bash
# 1. Clone repository
git clone https://github.com/brofatkur/doc-verify-laravel.git
cd doc-verify-laravel

# 2. Salin environment file
cp .env.example .env

# 3. Install dependensi
composer install

# 4. Generate Application Key & Jalankan Migrasi
php artisan key:generate
php artisan migrate --seed

# 5. Jalankan server lokal
php artisan serve
```

---

## 🔒 Konfigurasi Payment Gateway Xenith Pay
Tambahkan konfigurasi berikut pada file `.env`:
```env
XENITH_ENV=production
XENITH_ACCESS_KEY=ak-xxxxxxxxxxxxxxx
XENITH_SECRET_KEY=sk-xxxxxxxxxxxxxxx
XENITH_WEBHOOK_SECRET=tqYxuHTdCIRApkXloJviGV0l5aBcMSMhf8K05nvgFXfEMs7-Xw0D1lV79V_PJt3Q
```
* **URL Callback / Webhook**: `https://domain-anda.com/api/payment/callback`
