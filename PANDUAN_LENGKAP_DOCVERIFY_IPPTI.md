# PANDUAN LENGKAP PENGGUNAAN SISTEM DOCVERIFY IPPTI
**Sistem Verifikasi & Manajemen Dokumen Terjemahan Resmi Ikatan Penerjemah Indonesia (IPPTI)**

---

## DAFTAR ISI
1. [Pendahuluan & Gambaran Umum Sistem](#1-pendahuluan--gambaran-umum-sistem)
2. [Panduan User Publik (Masyarakat, Kedutaan, Notaris, Instansi)](#2-panduan-user-publik)
   - 2.1 [Verifikasi Keaslian Dokumen via Scan QR Code](#21-verifikasi-keaslian-dokumen-via-scan-qr-code)
   - 2.2 [Pencarian & Verifikasi Keabsahan Penerjemah Tersumpah](#22-pencarian--verifikasi-keabsahan-penerjemah-tersumpah)
3. [Panduan Penerjemah Tersumpah (Translator)](#3-panduan-penerjemah-tersumpah-translator)
   - 3.1 [Pendaftaran Akun & Login](#31-pendaftaran-akun--login)
   - 3.2 [Input Dokumen Tunggal (Manual)](#32-input-dokumen-tunggal-manual)
   - 3.3 [Impor Dokumen Massal via File Excel](#33-impor-dokumen-massal-via-file-excel)
   - 3.4 [Mengunduh & Menempelkan QR Code Resmi pada Berkas Terjemahan](#34-mengunduh--menempelkan-qr-code-resmi)
   - 3.5 [Sistem Saldo Poin, Top-Up, & Klaim Voucher Diskon](#35-sistem-saldo-poin-top-up--klaim-voucher-diskon)
   - 3.6 [Upgrade Akun ke Mode PRO](#36-upgrade-akun-ke-mode-pro)
   - 3.7 [Kelola Profil, Nomor SK, & Sertifikasi Bahasa](#37-kelola-profil-nomor-sk--sertifikasi-bahasa)
   - 3.8 [Panduan Instalasi Aplikasi Mobile (PWA Android & iPhone)](#38-panduan-instalasi-aplikasi-mobile-pwa)
4. [Panduan Pengurus IPPTI (Admin)](#4-panduan-pengurus-ippti-admin)
   - 4.1 [Manajemen & Verifikasi Akun Penerjemah](#41-manajemen--verifikasi-akun-penerjemah)
   - 4.2 [Monitoring Seluruh Dokumen Terdaftar](#42-monitoring-seluruh-dokumen-terdaftar)
   - 4.3 [Laporan Keuangan & Rekap Bagi Hasil (50% IPPTI : 50% Benlaris)](#43-laporan-keuangan--rekap-bagi-hasil)
   - 4.4 [Pembuatan & Pengelolaan Kode Voucher Diskon](#44-pembuatan--pengelolaan-kode-voucher-diskon)
   - 4.5 [Pengelolaan Master Data (Tipe Dokumen & Arah Bahasa)](#45-pengelolaan-master-data)
5. [Panduan Super Admin (Pengurus Pusat & Auditor Sistem)](#5-panduan-super-admin)
   - 5.1 [Portal Audit Registrasi Nasional](#51-portal-audit-registrasi-nasional)
   - 5.2 [Audit Trail & Log Aktivitas Sistem](#52-audit-trail--log-aktivitas-sistem)
   - 5.3 [Konfigurasi Payment Gateway (Xenith Pay Production & Sandbox)](#53-konfigurasi-payment-gateway)
   - 5.4 [Pengaturan Rekening Bank Resmi & Ambang Payout](#54-pengaturan-rekening-bank-resmi)
6. [Tanya Jawab & Penyelesaian Masalah (FAQ & Troubleshooting)](#6-tanya-jawab--penyelesaian-masalah)

---

## 1. Pendahuluan & Gambaran Umum Sistem
**DocVerify IPPTI** adalah platform resmi berbasis web dan PWA (*Progressive Web App*) yang dirancang khusus untuk meningkatkan integritas, legalitas, dan keamanan dokumen hasil terjemahan resmi penerjemah tersumpah anggota IPPTI di seluruh Indonesia.

### Fitur Unggulan Sistem:
* **QR Code Verifikasi Dinamis**: Setiap dokumen terjemahan memiliki kode QR unik berkeamanan tinggi yang terhubung langsung ke basis data nasional IPPTI.
* **Multi-Channel Payment Gateway Realtime**: Terintegrasi langsung dengan Xenith Pay (QRIS otomatis dan Transfer Bank Virtual Account).
* **Skema Transparan Bagi Hasil 50:50**: Rekapitulasi otomatis antara kas organisasi IPPTI dan pengembang Benlaris.
* **Progressive Web App (PWA)**: Dapat dipasang langsung pada smartphone Android maupun iPhone selayaknya aplikasi native tanpa memerlukan Google Play Store atau App Store.

---

## 2. Panduan User Publik
*(Masyarakat umum, Kedutaan Besar, Notaris, Kementerian Hukum dan HAM, Kementerian Luar Negeri, serta Klien Institusi/Korporasi)*

### 2.1 Verifikasi Keaslian Dokumen via Scan QR Code
1. Buka aplikasi kamera atau pemindai QR (*QR Scanner*) pada smartphone Anda.
2. Arahkan kamera ke gambar **QR Code DocVerify IPPTI** yang tertera pada lembar dokumen terjemahan resmi.
3. Ketuk tautan URL yang muncul (berformat: `https://domain-anda/verify/DOC-XXXXX`).
4. Halaman verifikasi resmi akan menampilkan:
   * **Status Keabsahan Dokumen**: Tanda centang hijau *"DOKUMEN TERDAFTAR RESMI"*.
   * **Nomor Registrasi Dokumen**: Nomor unik yang dicantumkan penerjemah.
   * **Nama Pemilik Dokumen / Klien**: Nama yang tercantum pada dokumen asli.
   * **Jenis Dokumen**: (contoh: Akta Kelahiran, Ijazah, Perjanjian Kontrak, dsb.).
   * **Pasangan Bahasa**: (contoh: *Indonesia - Inggris*, *Belanda - Indonesia*).
   * **Tanggal Dokumen Diterbitkan**: Waktu penerjemahan.
   * **Identitas Penerjemah Tersumpah**: Nama lengkap, Nomor Anggota IPPTI, dan Nomor SK Penetapan resmi.
5. *Jika dokumen tidak valid atau palsu*, sistem akan menampilkan peringatan merah bahwa dokumen tidak ditemukan di basis data resmi IPPTI.

---

### 2.2 Pencarian & Verifikasi Keabsahan Penerjemah Tersumpah
1. Kunjungi halaman utama publik dan pilih menu **Verifikasi Penerjemah** atau buka URL `/verify-translator`.
2. Masukkan kata kunci pencarian pada kolom yang tersedia:
   * Nama lengkap penerjemah, ATAU
   * Nomor Anggota IPPTI (contoh: `25008`), ATAU
   * Nomor SK Penetapan Menteri Kehakiman / Kemenkumham / Gubernur.
3. Klik tombol **Cari Penerjemah**.
4. Sistem akan menampilkan profil penerjemah tersumpah, meliputi:
   * Foto resmi dan status keanggotaan aktif IPPTI.
   * Pasangan bahasa yang menjadi wewenang resminya.
   * Riwayat verifikasi dan jumlah dokumen resmi yang telah diterbitkan.

---

## 3. Panduan Penerjemah Tersumpah (Translator)

### 3.1 Pendaftaran Akun & Login
1. Buka situs DocVerify IPPTI dan klik **Daftar Penerjemah Baru** (`/register`).
2. Lengkapi formulir pendaftaran:
   * Nama Lengkap (beserta gelar).
   * Alamat Email aktif & Nomor WhatsApp.
   * Nomor SK Penetapan Penerjemah Tersumpah.
   * Nomor Anggota IPPTI.
   * Pasangan/Arah Bahasa yang dikuasai.
   * Kata Sandi akun.
3. Klik **Daftar Sekarang**.
4. Lakukan login menggunakan email dan password yang telah didaftarkan.

---

### 3.2 Input Dokumen Tunggal (Manual)
1. Setelah login, Anda akan berada di menu **Data Dokumen** (`/admin`).
2. Klik tombol **+ Tambah Dokumen Baru**.
3. Isi data dokumen yang diterjemahkan:
   * **Nama Pemilik / Klien di Dokumen**: Nama yang tertera pada berkas asli.
   * **Nomor Registrasi Dokumen**: Nomor penomoran internal buku register Anda.
   * **Tipe Dokumen**: Pilih dari dropdown (contoh: Akta Notaris, Putusan Pengadilan, Ijazah, Paspor).
   * **Arah Bahasa**: Pilih arah bahasa (contoh: *Indonesia - Inggris*).
   * **Tanggal Terjemahan**: Tanggal penyelesaian terjemahan.
4. Klik tombol **Simpan & Terbitkan QR Code**.
5. Saldo poin Anda akan terpotong secara otomatis sesuai tarif per dokumen.

---

### 3.3 Impor Dokumen Massal via File Excel
1. Pada dashboard dokumen, klik tombol **Impor Berkas Excel**.
2. Unduh template resmi dengan mengklik **Unduh Template Excel (.xlsx)**.
3. Buka file template di Microsoft Excel / Google Sheets, lalu isi data dokumen:
   * Kolom A: *Nama di Dokumen*
   * Kolom B: *Nomor Registrasi*
   * Kolom C: *Tipe Dokumen*
   * Kolom D: *Arah Bahasa*
   * Kolom E: *Tanggal (Format: YYYY-MM-DD)*
4. Unggah kembali file Excel yang telah diisi.
5. Sistem akan menampilkan **Modal Preview Data**. Periksa data yang akan diimpor.
6. Klik **Konfirmasi & Impor**. Sistem menerapkan mekanisme *All-or-Nothing Transaction* (semua dokumen berhasil diimpor atau dibatalkan aman jika ada baris yang keliru).

---

### 3.4 Mengunduh & Menempelkan QR Code Resmi
1. Pada tabel daftar dokumen, cari dokumen yang ingin dicetak.
2. Pada kolom aksi sebelah kanan, klik **Unduh QR Code** atau **Lihat QR**.
3. Simpan gambar QR Code berformat PNG beresolusi tinggi tersebut.
4. Sisipkan / cetak QR Code tersebut pada:
   * Halaman terakhir lembar terjemahan tersumpah Anda, ATAU
   * Lembar pernyataan penerjemah (*Translator's Statement / Certificate of Translation Accuracy*).

---

### 3.5 Sistem Saldo Poin, Top-Up, & Klaim Voucher Diskon
1. Setiap pendaftaran dokumen membutuhkan saldo poin (default: 1 dokumen = 10.000 Poin / Rp 10.000).
2. Untuk melakukan isi ulang:
   * Klik menu **Upgrade PRO / Topup Poin** atau klik lencana saldo poin di bilah atas.
   * Pilih paket poin yang diinginkan (contoh: Paket 10.000 Poin, 50.000 Poin, atau 100.000 Poin).
   * Masukkan **Kode Voucher Diskon** jika Anda memiliki kode promo dari pengurus IPPTI (contoh: diskon 50% atau diskon 100% Free Pass).
   * Jika total tagihan setelah diskon adalah Rp 0 (Diskon 100%), poin akan langsung ditambahkan ke akun Anda secara instan tanpa perlu ke halaman pembayaran.
   * Jika tagihan > Rp 0, klik **Lanjutkan Pembayaran**.
3. Pilih metode pembayaran di Xenith Pay:
   * **QRIS**: Scan langsung menggunakan aplikasi perbankan (BCA, Mandiri, BRI, BNI, CIMB) atau E-Wallet (GoPay, OVO, Dana, ShopeePay).
   * **Transfer Bank Virtual Account (VA)**: Bayar ke nomor VA yang tertera.
4. Setelah pembayaran berhasil, saldo poin akan langsung bertambah secara realtime dan otomatis.

---

### 3.6 Upgrade Akun ke Mode PRO
1. Pengguna dapat meningkatkan level akun dari **REGULER** ke **PRO**.
2. Keunggulan Penerjemah Mode PRO:
   * Lencana emas **PRO VERIFIED** pada halaman verifikasi publik.
   * Prioritas penempatan pada direktori pencarian nasional.
   * Fitur kustomisasi identitas stempel dan logo kantor penerjemah.
3. Cara upgrade: Masuk ke menu **Upgrade Mode PRO**, pilih paket langganan PRO, dan selesaikan transaksi.

---

### 3.7 Kelola Profil, Nomor SK, & Sertifikasi Bahasa
1. Klik menu **Profil & Layanan** (`/admin/profile`).
2. Anda dapat memperbarui:
   * Foto profil / pas foto resmi.
   * Nomor SK Penetapan & Tahun Pengangkatan.
   * Nomor Anggota IPPTI.
   * Alamat kantor, email resmi, dan nomor telepon kontak klien.
   * Pasangan bahasa yang didaftarkan.
3. Klik **Simpan Perubahan Profil**.

---

### 3.8 Panduan Instalasi Aplikasi Mobile (PWA Android & iPhone)
Aplikasi DocVerify IPPTI dapat diinstall langsung di smartphone tanpa perlu download dari Playstore/App Store:

#### Untuk Pengguna Android (Google Chrome / Edge):
1. Buka situs DocVerify IPPTI di browser Google Chrome pada HP Anda.
2. Buka menu samping (drawer), lalu ketuk tombol **"Pasang Aplikasi di HP (PWA)"** atau ketuk popup *"Tambahkan ke Layar Utama"*.
3. Ketuk **Instal**. Ikon DocVerify akan langsung muncul di menu utama HP Anda.

#### Untuk Pengguna iPhone / iPad (Apple Safari):
1. Buka situs DocVerify IPPTI di browser Safari.
2. Ketuk tombol **Share / Bagikan** (ikon kotak dengan panah ke atas di bagian bawah layar).
3. Gulir ke bawah dan pilih menu **"Add to Home Screen"** (*Tambah ke Layar Utama*).
4. Ketuk **Add / Tambah** di pojok kanan atas.
5. Aplikasi DocVerify siap digunakan dengan tampilan layar penuh (*Full Screen App*).

---

## 4. Panduan Pengurus IPPTI (Admin)

### 4.1 Manajemen & Verifikasi Akun Penerjemah
1. Masuk ke menu **Manajemen User** (`/admin/users`).
2. Pengurus dapat:
   * Melihat daftar seluruh penerjemah tersumpah yang terdaftar.
   * Mengubah status akun (*Aktif*, *Ditangguhkan/Suspended*).
   * Menyesuaikan level akun (*Reguler* atau *PRO*).
   * Menambahkan saldo poin subsidi/manual jika diperlukan.
   * Melakukan ekspor data anggota ke file Excel atau mengimpor data anggota lama.

---

### 4.2 Monitoring Seluruh Dokumen Terdaftar
1. Pengurus dapat memantau seluruh lalu lintas dokumen yang diterbitkan oleh anggota di menu **Data Dokumen**.
2. Gunakan kolom pencarian cepat untuk mencari dokumen berdasarkan:
   * Nomor registrasi
   * Nama penerjemah
   * Nama klien/dokumen
   * Tipe dokumen dan pasangan bahasa

---

### 4.3 Laporan Keuangan & Rekap Bagi Hasil (50% IPPTI : 50% Benlaris)
1. Buka menu **Keuangan & Bagi Hasil** (`/admin/finance`).
2. Dashboard keuangan menyajikan ringkasan transparan:
   * **Saldo Kas Real Xenith**: Saldo kas bersih yang siap dicairkan.
   * **Total Pemasukan Bersih**: Total dana yang masuk setelah dipotong biaya gateway.
   * **Hak Bagi Hasil IPPTI (50%)**: Porsi kas resmi organisasi IPPTI.
   * **Hak Bagi Hasil Benlaris (50%)**: Porsi pengembang teknologi Benlaris.
3. **Skema Potongan Biaya Gateway (Fee)**:
   * Saluran QRIS: Rp 500 (Flat) + 0,7% dari nominal transaksi.
   * Saluran Transfer VA Bank: Rp 4.000 (Flat) per transaksi.
4. **Kelola Rekening Bank**:
   * Klik tombol **Kelola Data Rekening** untuk memperbarui rekening tujuan BCA IPPTI dan Bank Mandiri Benlaris.
5. **Sinkronisasi Data**: Klik **Sinkronkan Data Realtime** untuk menyelaraskan data mutasi secara langsung dengan server Xenith Pay.

---

### 4.4 Pembuatan & Pengelolaan Kode Voucher Diskon
1. Buka menu **Voucher Diskon** (`/admin/vouchers`).
2. Klik tombol **+ Buat Voucher Baru**.
3. Isi parameter voucher:
   * **Kode Voucher**: (contoh: `IPPTI-MERDEKA`, `PROMO100`, `BEASISWA`).
   * **Tipe Diskon**: *Persentase (%)* atau *Nominal Tetap (Rp)*.
   * **Nilai Diskon**: Masukkan angka (misal `100` untuk diskon 100%, atau `5000` untuk potongan Rp 5.000).
   * **Batas Kuota Penggunaan**: Batas maksimal klaim voucher.
   * **Masa Berlaku**: Tanggal kadaluarsa voucher.
4. Klik **Simpan Voucher**. Penerjemah dapat langsung menggunakan kode ini saat top-up poin.

---

### 4.5 Pengelolaan Master Data
1. **Master Tipe Dokumen** (`/admin/document-types`):
   * Menambah jenis dokumen baru (misal: *Surat Kuasa Khusus*, *Perjanjian Kerahasiaan (NDA)*, *Putusan Arbitrase*).
2. **Master Arah Bahasa** (`/admin/language-directions`):
   * Menambah kombinasi bahasa baru (misal: *Indonesia - Swedia*, *Vietnam - Indonesia*).

---

## 5. Panduan Super Admin (Pengurus Pusat & Auditor)

### 5.1 Portal Audit Registrasi Nasional
1. Halaman utama Super Admin menampilkan grafik dan metrik statistik se-Indonesia:
   * Total Penerjemah Terdaftar Nasional
   * Total Dokumen Resmi Diterbitkan
   * Total Verifikasi QR Aktif
   * Distribusi Tipe Dokumen Terjemahan

---

### 5.2 Audit Trail & Log Aktivitas Sistem
1. Buka menu **Log Audit Sistem** (`/admin/audit-logs`).
2. Fitur ini mencatat seluruh jejak audit (*security audit trail*) secara otomatis:
   * Waktu dan tanggal aktivitas.
   * Pelaku aktivitas (Nama & Role).
   * Jenis aksi: *Login, Registrasi Dokumen, Perubahan Data Dokumen, Top-Up Poin, Perubahan Pengaturan*.
   * Alamat IP Address & User Agent perangkat yang digunakan.

---

### 5.3 Konfigurasi Payment Gateway (Xenith Pay)
1. Buka menu **Pengaturan Aplikasi** (`/admin/settings`).
2. Pada bagian **Payment Gateway Xenith Pay**, Anda dapat mengatur:
   * **Environment**: `production` (Live Transaksi) atau `sandbox` (Pengujian).
   * **Access Key**: Kunci publik API Xenith Pay.
   * **Secret Key**: Kunci rahasia API Xenith Pay.
   * **Webhook Signature Secret**: Kunci verifikasi HMAC-SHA256 callback webhook.
   * **URL Webhook IPPTI**: `https://domain-anda/api/payment/callback` (daftarkan URL ini pada dashboard Xenith Pay).
3. Klik **Simpan Pengaturan Gateway**.

---

### 5.4 Pengaturan Rekening Bank Resmi & Ambang Payout
1. Di menu pengaturan keuangan, tentukan:
   * Ambang Batas Minimum Pencairan (*Minimum Payout Threshold*), default: Rp 100.000.
   * Pengaturan mode pencairan otomatis bulanan (*Auto Monthly Payout*).

---

## 6. Tanya Jawab & Penyelesaian Masalah (FAQ & Troubleshooting)

**Q1: Apa yang harus dilakukan jika QR Code pada dokumen yang dicetak tidak bisa discan?**
* Pastikan resolusi cetak QR Code jelas dan tidak blur/terpotong. Unduh kembali file PNG QR Code dari dashboard dengan ukuran asli.

**Q2: Mengapa setelah top-up via QRIS saldo poin belum bertambah?**
* Tunggu beberapa detik, lalu klik tombol **Refresh / Sinkronkan Data** di dashboard. Sistem otomatis memproses callback webhook dalam hitungan detik. Jika jaringan bank mengalami delay, sistem cron otomatis melakukan verifikasi status transaksi ke server Xenith Pay.

**Q3: Bagaimana jika ada kesalahan input nama pemilik atau nomor registrasi dokumen?**
* Penerjemah dapat mengklik tombol **Edit** pada baris dokumen bersangkutan di menu Data Dokumen untuk memperbaiki data teks tanpa mengubah tautan QR Code yang sudah dicetak.

**Q4: Apakah masyarakat umum perlu membuat akun untuk memverifikasi dokumen?**
* **Tidak perlu.** Halaman verifikasi dokumen dan pencarian penerjemah bersifat publik dan dapat diakses bebas tanpa login oleh siapa saja, kapan saja, dan di mana saja.

---
*Dokumentasi resmi ini disusun untuk DocVerify IPPTI - Ikatan Penerjemah Indonesia bersama PT Benlaris Sukses Indonesia.*
