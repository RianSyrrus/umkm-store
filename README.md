# UMKM Store - Milestone 1

UMKM Store adalah aplikasi toko online sederhana dan responsif (*mobile-first*) yang dibangun menggunakan **Laravel 13**, **Livewire 4**, dan **Flux UI (Free Edition)**. Aplikasi ini dirancang khusus untuk memfasilitasi pelaku UMKM dalam mengelola katalog produk, mengelola stok, dan menerima pesanan pelanggan secara lokal.

## Fitur Utama (Milestone 1)

1. **Autentikasi Admin Tunggal:**
   - Keamanan login dan logout untuk 1 pengguna admin utama.
   - Pendaftaran publik dinonaktifkan untuk membatasi akses ilegal.

2. **Pengaturan Toko & Jam Operasional:**
   - Kelola nama, deskripsi toko, alamat, titik koordinat, dan whatsapp.
   - Konfigurasi jam operasional (buka/tutup) untuk tiap hari dalam seminggu.
   - Konfigurasi tarif dasar pengiriman dan biaya per kilometer.

3. **Manajemen Katalog (Kategori & Produk):**
   - CRUD Kategori dengan nomor urut tampilan.
   - CRUD Produk lengkap dengan varian harga/porsi, upload foto, multiselect opsi variasi (misal: Level Pedas), dan multiselect item add-on (misal: Topping).

4. **Koreksi Stok (Inventory Adjustment):**
   - Halaman pantau stok fisik, stok terpesan, dan stok tersedia.
   - Form penyesuaian stok aman dengan transaksi database dan row-locking (`lockForUpdate`).

5. **Katalog Publik (Storefront):**
   - Desain katalog responsif bertema premium, pencarian dinamis, filter kategori, dan status buka/tutup toko.
   - Halaman detail konfigurasi produk interaktif dengan kalkulasi harga secara real-time.

---

## Panduan Memulai

Silakan baca dokumen panduan setup lokal yang lengkap di:
[Panduan Setup Lokal](file:///c:/laragon/www/umkm-store/docs/setup/local-development.md)
