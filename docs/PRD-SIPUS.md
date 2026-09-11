# PRD — SIPUS (Sistem Informasi Perpustakaan)

## 1. Ringkasan (Overview)
SIPUS adalah **sistem informasi manajemen perpustakaan** berbasis web yang membantu petugas
perpustakaan mengelola data buku, anggota, proses peminjaman, pengembalian, denda, riwayat
transaksi, dan laporan dalam satu aplikasi terpadu.

- **Teknologi:** Laravel 12 · PHP 8.2 · SQLite · Bootstrap 5 · MySQL (opsional)
- **Bahasa UI:** Indonesia
- **Sasaran pengguna:** Petugas/administrator perpustakaan

## 2. Tujuan (Goals)
1. Mendigitalkan pencatatan peminjaman & pengembalian buku agar akurat dan cepat.
2. Menghitung **denda keterlambatan secara otomatis** (Rp1.000/hari).
3. Memantau ketersediaan stok buku secara real-time.
4. Menyediakan **laporan & statistik** untuk pengambilan keputusan.
5. Tampilan responsif yang ramah digunakan di berbagai perangkat.

## 3. Fitur Utama (Features)

### 3.1 Autentikasi
- Login petugas dilindungi akun & password.
- Profil & pengaturan akun (foto, nama, email, password).

### 3.2 Katalog Buku (Publik)
- Pencarian buku berdasarkan judul/penulis/ISBN.
- Detail buku beserta status ketersediaan stok.

### 3.3 Master Data
- **Anggota:** CRUD lengkap, status aktif/nonaktif, kode anggota otomatis.
- **Kategori Buku:** CRUD.
- **Data Buku:** CRUD, upload sampul, stok, lokasi rak, cetak tahun terbit.

### 3.4 Transaksi
- **Peminjaman:** pilih anggota, tanggal pinjam, lama pinjam (1–30 hari, default 7),
  jatuh tempo otomatis, validasi stok.
- **Pengembalian:** proses kembali buku, hitung keterlambatan & denda otomatis.
- **Riwayat Transaksi:** filter tanggal & status.

### 3.5 Dashboard & Analitik
- Statistik koleksi, anggota, stok, dan peminjaman.
- Grafik tren 6 bulan terakhir.
- Buku terpopuler, stok menipis, anggota terbaru, aktivitas terbaru.
- Jam & tanggal live.

### 3.6 Laporan & Ekspor
- Rekap peminjaman per periode (filter tanggal).
- Ekspor **CSV** dan **PDF**.

## 4. Alur Utama (User Flow)
```
Login → Dashboard
  → Kelola Buku / Kategori / Anggota
  → Buat Peminjaman
  → (jatuh tempo) → Proses Pengembalian → hitung denda
  → Lihat Riwayat / Laporan → Ekspor CSV/PDF
```

## 5. Data Model (Ringkas)
- **users** — akun petugas.
- **categories** — kategori buku.
- **books** — data buku (stok, stok tersedia, rak, sampul).
- **members** — anggota perpustakaan (status aktif/nonaktif).
- **loans** — transaksi pinjam (tanggal pinjam, jatuh tempo, kembali, status, total denda).
- **loan_items** — rincian buku pada tiap transaksi pinjam.

## 6. Aturan Bisnis (Business Rules)
- Denda keterlambatan: **Rp1.000 / hari** keterlambatan.
- Stok buku berkurang saat dipinjam, kembali saat dikembalikan.
- Anggota nonaktif tidak dapat meminjam.
- Satu transaksi dapat berisi lebih dari satu buku.

## 7. Keunggulan / Nilai Jual (USP)
- Menghitung denda **otomatis** dan akurat.
- Dashboard statistik **real-time** yang informatif.
- Ekspor laporan **CSV & PDF** siap cetak.
- UI modern, bersih, dan responsif (Bootstrap 5 + Lucide).
- Kode terstruktur rapi dengan lapisan `LoanService` dan test otomatis.

## 8. Akun Demo
- **Email:** `admin@sipus.test`
- **Password:** `password`

## 9. Lingkup (Scope)
- **Dalam cakupan:** modul di atas (buku, anggota, kategori, transaksi, dashboard, laporan).
- **Di luar cakupan (fase berikutnya):** multi-role pengguna, integrasi barcode/RFID,
  modul e-book, notifikasi email/SMS, aplikasi mobile.
