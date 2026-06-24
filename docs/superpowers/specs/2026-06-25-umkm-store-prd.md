# Product Requirements Document (PRD)

## UMKM Store — Aplikasi Penjualan Online Sederhana untuk UMKM

| Informasi | Nilai |
|---|---|
| Versi | 1.1 |
| Tanggal | 25 Juni 2026 |
| Status | Disetujui untuk perencanaan implementasi |
| Jenis produk | E-commerce single-vendor |
| Studi kasus | UMKM makanan dan minuman |
| Platform | Web mobile-first dengan desktop adaptif |

## 1. Ringkasan Produk

UMKM Store adalah aplikasi penjualan online milik satu UMKM makanan dan minuman. Pelanggan dapat melihat katalog, memilih varian dan tambahan, memesan tanpa membuat akun, membayar secara otomatis, serta memilih pengambilan di toko atau pengiriman lokal.

Admin menggunakan satu akun untuk mengelola produk, stok, pre-order, pesanan, pembayaran, promo, pengiriman, dan laporan bisnis.

Produk dirancang dengan pendekatan mobile-first. Alur pelanggan dioptimalkan terlebih dahulu untuk layar ponsel dan interaksi sentuh. Pada PC atau laptop, sistem menampilkan layout desktop penuh—seperti navigasi horizontal, grid yang lebih lebar, panel berdampingan, dan tabel yang sesuai—bukan tampilan mobile yang sekadar diperbesar.

## 2. Latar Belakang

UMKM makanan dan minuman sering menerima pesanan melalui chat. Cara ini menimbulkan beberapa masalah:

- Pesanan dan permintaan khusus mudah terlewat.
- Harga, diskon, stok, dan ongkir dihitung secara manual.
- Bukti pembayaran harus diperiksa satu per satu.
- Jadwal produksi dan pengambilan tidak terkoordinasi.
- Pemilik sulit mengetahui omzet, produk terlaris, dan stok menipis.
- UMKM bergantung pada marketplace pihak ketiga dan kebijakan komisinya.

## 3. Tujuan Produk

1. Menyediakan kanal penjualan online yang dimiliki dan dikendalikan UMKM.
2. Membuat pelanggan dapat memesan tanpa registrasi akun.
3. Mendukung penjualan ready stock dan pre-order terjadwal.
4. Mengotomatisasi pembayaran melalui Midtrans.
5. Menghitung ongkir lokal berdasarkan jarak dari toko.
6. Menyatukan pengelolaan produk, stok, pesanan, promo, dan laporan.
7. Memberikan informasi status pesanan kepada pelanggan.

## 4. Sasaran Pengguna

### 4.1 Pelanggan

Pelanggan ingin menemukan produk, membuat pesanan, membayar, dan mengetahui status pesanan dengan cepat tanpa membuat akun.

### 4.2 Admin

Pemilik atau pengelola UMKM ingin menjalankan operasional toko dan memantau performa bisnis melalui satu dashboard.

## 5. Ruang Lingkup Versi Pertama

### Termasuk

- Satu toko dan satu akun admin.
- Katalog makanan dan minuman.
- Produk ready stock dan pre-order.
- Varian, level pilihan, add-on atau topping, dan catatan item.
- Stok per varian.
- Keranjang dan checkout tanpa login.
- Pickup terjadwal dan pengiriman lokal.
- Ongkir berdasarkan jarak dengan radius maksimal 10 km.
- Midtrans Snap dalam mode sandbox selama pengembangan.
- Voucher dan promo produk otomatis.
- Pelacakan pesanan.
- Dashboard dan ekspor laporan.
- Notifikasi dalam dashboard admin.
- Pengalaman pelanggan mobile-first dengan desktop adaptif.

### Tidak termasuk

- Akun pelanggan.
- Multi-vendor atau marketplace.
- Banyak akun dan pembagian peran admin.
- Aplikasi khusus kurir.
- Integrasi POS.
- Stok bahan baku berbasis resep.
- WhatsApp API dan email otomatis.
- Pembayaran production sebelum proses aktivasi merchant selesai.

## 6. Kebutuhan Fungsional Pelanggan

### 6.1 Katalog

Pelanggan dapat:

- Melihat produk aktif berdasarkan kategori.
- Mencari produk berdasarkan nama.
- Memfilter produk berdasarkan kategori dan jenis penjualan.
- Melihat foto, deskripsi, harga awal, status stok, dan status promo.
- Melihat apakah produk tersedia sebagai ready stock atau pre-order.

### 6.2 Detail Produk

Pelanggan dapat:

- Memilih satu varian wajib jika produk memiliki varian.
- Memilih level seperti tingkat gula atau pedas jika tersedia.
- Memilih satu atau beberapa add-on.
- Menentukan jumlah.
- Menambahkan catatan khusus.
- Melihat perubahan harga sebelum menambahkan item ke keranjang.

Sistem harus menolak kombinasi produk yang tidak tersedia atau stoknya tidak mencukupi.

### 6.3 Keranjang

Pelanggan dapat:

- Menambah, mengubah, dan menghapus item.
- Melihat subtotal setiap item dan subtotal keranjang.
- Menyimpan keranjang sementara dalam sesi browser.
- Melihat peringatan jika harga atau stok berubah.

### 6.4 Checkout

Pelanggan wajib mengisi:

- Nama.
- Nomor WhatsApp.
- Metode pemenuhan pesanan.
- Jadwal yang tersedia.
- Alamat dan titik lokasi jika memilih pengiriman.
- Catatan pesanan jika diperlukan.

Server harus menghitung ulang harga, diskon, ongkir, stok, kuota slot, dan total sebelum membuat transaksi.

### 6.5 Pickup

- Pelanggan memilih tanggal dan jam pengambilan yang tersedia.
- Admin menentukan slot, kuota, dan batas pemesanan.
- Pesanan hanya dapat diambil setelah berstatus `ready_for_pickup`.

### 6.6 Pengiriman Lokal

- Pelanggan memilih titik lokasi pada peta dan melengkapi alamat.
- Jarak dihitung dari koordinat toko ke titik pelanggan menggunakan API peta.
- Radius pengiriman maksimal adalah 10 km.
- Pesanan di luar radius tidak dapat melanjutkan pembayaran.
- Rumus ongkir:

`ongkir = tarif dasar + (jarak dalam kilometer × tarif per kilometer)`

- Tarif dasar dan tarif per kilometer dapat diubah admin.
- Pembulatan jarak dan nilai ongkir harus ditentukan konsisten pada konfigurasi aplikasi.

### 6.7 Promo

Sistem mendukung:

- Promo produk otomatis berupa harga khusus dalam periode tertentu.
- Voucher diskon nominal atau persentase.
- Minimum belanja.
- Nilai maksimum diskon untuk voucher persentase.
- Periode berlaku.
- Kuota penggunaan.
- Pembatasan voucher aktif satu per pesanan.

Voucher tidak boleh mengurangi ongkir kecuali aturan voucher secara eksplisit mengizinkannya.

### 6.8 Pembayaran

- Sistem menggunakan Midtrans Snap.
- Mata uang transaksi adalah rupiah.
- Pelanggan diarahkan ke antarmuka pembayaran Midtrans.
- Status pembayaran hanya dianggap sah setelah diverifikasi server.
- Pembayaran `pending`, `settlement`, `capture`, `deny`, `cancel`, `expire`, dan `refund` dipetakan ke status internal yang sesuai.
- Webhook yang sama dapat diterima lebih dari sekali tanpa menimbulkan pemrosesan ganda.

### 6.9 Pelacakan Pesanan

- Pelanggan memasukkan kode pesanan dan nomor WhatsApp.
- Sistem hanya menampilkan pesanan jika keduanya cocok.
- Informasi yang ditampilkan mencakup ringkasan item, pembayaran, metode pemenuhan, jadwal, dan status pesanan.
- Data internal admin dan data sensitif pembayaran tidak ditampilkan.

## 7. Kebutuhan Fungsional Admin

### 7.1 Autentikasi

- Admin dapat login dan logout.
- Seluruh halaman admin dilindungi autentikasi.
- Sistem hanya menyediakan satu akun admin pada versi pertama.

### 7.2 Pengaturan Toko

Admin dapat mengatur:

- Nama, logo, deskripsi, kontak, dan alamat toko.
- Koordinat toko.
- Jam operasional.
- Tarif dasar dan tarif per kilometer.
- Batas radius 10 km.
- Ambang batas notifikasi stok menipis.

### 7.3 Produk

Admin dapat:

- Mengelola kategori.
- Membuat, membaca, mengubah, mengarsipkan, dan mengaktifkan produk.
- Mengunggah beberapa foto produk.
- Mengatur ready stock, pre-order, atau keduanya.
- Mengelola varian, harga, SKU, dan stok masing-masing varian.
- Mengelola level pilihan dan add-on.
- Menandai produk atau varian tidak tersedia.

Produk yang pernah dipesan tidak dihapus permanen agar riwayat tetap utuh.

### 7.4 Jadwal Pre-order dan Pickup

Admin dapat:

- Membuat slot tanggal dan jam.
- Menentukan kuota setiap slot.
- Menentukan batas waktu pemesanan.
- Menutup slot secara manual.
- Melihat jumlah kuota yang tersedia dan dicadangkan.

### 7.5 Pesanan

Admin dapat:

- Melihat, mencari, dan memfilter pesanan.
- Melihat detail pelanggan, item, pembayaran, jadwal, dan pengiriman.
- Mengubah status operasional melalui transisi yang valid.
- Membatalkan pesanan dengan alasan.
- Melihat histori perubahan status.

### 7.6 Pembayaran

Admin dapat:

- Melihat status dan detail referensi transaksi.
- Melihat waktu pembayaran dan nominal.
- Melihat histori webhook yang telah disanitasi.
- Melakukan sinkronisasi status melalui server jika status perlu diperiksa ulang.

Admin tidak boleh mengubah transaksi menjadi lunas secara sembarang tanpa mekanisme audit.

### 7.7 Promo dan Voucher

Admin dapat mengelola:

- Harga promo produk.
- Periode promo.
- Kode voucher.
- Jenis dan nilai diskon.
- Minimum belanja dan maksimum diskon.
- Kuota dan status aktif.

### 7.8 Dashboard dan Laporan

Dashboard menampilkan:

- Omzet dari pesanan yang telah dibayar.
- Jumlah pesanan berdasarkan status.
- Tren penjualan berdasarkan periode.
- Produk dan varian terlaris.
- Stok menipis atau habis.
- Pesanan terbaru.
- Notifikasi operasional.

Admin dapat memfilter laporan berdasarkan rentang tanggal dan mengekspor data transaksi. Pesanan gagal, dibatalkan, dan kedaluwarsa tidak dihitung sebagai omzet.

### 7.9 Notifikasi

Dashboard memberikan notifikasi untuk:

- Pesanan baru.
- Pembayaran berhasil, gagal, atau kedaluwarsa.
- Pembatalan pesanan.
- Stok menipis.

## 8. Status dan Alur Pesanan

Status pembayaran dipisahkan dari status operasional.

### 8.1 Status Pembayaran

- `pending`
- `paid`
- `failed`
- `expired`
- `cancelled`
- `refunded`

### 8.2 Status Operasional

- `awaiting_payment`
- `confirmed`
- `processing`
- `ready_for_pickup`
- `out_for_delivery`
- `completed`
- `cancelled`

### 8.3 Transisi Utama

```text
awaiting_payment
        |
      paid
        |
    confirmed
        |
    processing
      /     \
ready_for_  out_for_
pickup      delivery
      \     /
     completed
```

Pembayaran gagal atau kedaluwarsa mengakhiri pesanan dan melepaskan seluruh reservasi. Perubahan status dicatat dalam histori.

## 9. Aturan Stok dan Reservasi

- Stok dikelola per varian.
- Stok tidak boleh bernilai negatif.
- Saat pesanan dibuat, stok dan kuota slot dicadangkan selama masa pembayaran.
- Pembayaran berhasil mengesahkan reservasi.
- Pembayaran gagal, dibatalkan, atau kedaluwarsa melepaskan reservasi.
- Checkout harus menggunakan transaksi database dan penguncian yang sesuai untuk mencegah overselling.
- Perubahan stok dicatat agar dapat diaudit.

## 10. Arsitektur Teknis

### 10.1 Teknologi

- Laragon 13 sebagai lingkungan pengembangan lokal.
- Laravel dan PHP.
- MySQL.
- Blade dan Livewire.
- Alpine.js.
- Tailwind CSS.
- Midtrans Snap dan webhook.
- API peta untuk geocoding, koordinat, dan perhitungan jarak.
- Laravel Queue.
- Laravel Scheduler.

Versi PHP, Laravel, Node.js, dan database akan dikunci pada rencana implementasi setelah kompatibilitas Laragon 13 diperiksa.

### 10.2 Modul

1. Autentikasi admin.
2. Pengaturan toko.
3. Katalog dan kategori.
4. Produk, varian, add-on, dan stok.
5. Keranjang.
6. Checkout tamu.
7. Slot dan pre-order.
8. Pickup dan pengiriman lokal.
9. Pesanan.
10. Pembayaran.
11. Promo dan voucher.
12. Notifikasi admin.
13. Dashboard dan laporan.

### 10.3 Entitas Data Utama

- `Admin`
- `Store`
- `Category`
- `Product`
- `ProductImage`
- `ProductVariant`
- `OptionGroup`
- `OptionValue`
- `AddOn`
- `Cart`
- `CartItem`
- `Order`
- `OrderItem`
- `OrderItemOption`
- `OrderItemAddOn`
- `OrderStatusHistory`
- `Payment`
- `PaymentNotification`
- `Delivery`
- `ScheduleSlot`
- `StockMovement`
- `Voucher`
- `VoucherRedemption`
- `ProductPromotion`
- `AdminNotification`

Nama, harga, varian, pilihan, add-on, dan diskon disalin sebagai snapshot ke detail pesanan. Perubahan katalog setelah checkout tidak boleh mengubah transaksi lama.

## 11. Alur Checkout Teknis

1. Pelanggan mengirim data checkout.
2. Server memvalidasi data pelanggan dan metode pemenuhan.
3. Server memuat ulang data produk dari database.
4. Server memvalidasi stok, jadwal, voucher, dan radius.
5. Server menghitung subtotal, diskon, ongkir, dan total.
6. Pesanan dibuat dalam transaksi database.
7. Stok dan kuota dicadangkan.
8. Sistem meminta token transaksi Midtrans.
9. Pelanggan menyelesaikan pembayaran.
10. Server menerima dan memverifikasi webhook.
11. Status pembayaran diperbarui secara idempoten.
12. Pembayaran berhasil mengesahkan reservasi dan memunculkan notifikasi admin.
13. Scheduler melepaskan reservasi transaksi yang kedaluwarsa jika diperlukan.

## 12. Penanganan Kesalahan

- API peta gagal: checkout dihentikan dan pelanggan diminta mencoba kembali.
- Lokasi tidak valid: pelanggan diminta memilih ulang titik pada peta.
- Midtrans gagal membuat transaksi: pesanan tidak diteruskan dan reservasi dilepas.
- Pembayaran masih pending: pesanan tetap menunggu webhook.
- Webhook tidak valid: tidak ada status yang diubah dan kejadian dicatat.
- Stok berubah: pelanggan diminta memperbarui keranjang.
- Slot penuh: pelanggan diminta memilih slot lain.
- Voucher tidak valid: alasan kegagalan ditampilkan.
- Kesalahan internal: pesan umum ditampilkan dan detail teknis hanya masuk ke log.

## 13. Keamanan dan Privasi

- Password admin disimpan menggunakan hashing Laravel.
- Login melakukan regenerasi sesi.
- Route admin dilindungi middleware.
- Seluruh form menggunakan perlindungan CSRF.
- Input divalidasi di server.
- Unggahan dibatasi berdasarkan tipe, ukuran, dan nama file aman.
- Login, pelacakan pesanan, checkout, dan webhook diberi rate limiting yang sesuai.
- Kredensial Midtrans dan API peta disimpan dalam `.env`.
- Signature dan data webhook Midtrans wajib diverifikasi.
- Endpoint webhook harus idempoten.
- Pelacakan pesanan membutuhkan kode pesanan dan nomor WhatsApp.
- Nomor WhatsApp dan alamat tidak ditampilkan di halaman publik.
- Data sensitif tidak dicatat utuh dalam log.
- Nominal pembayaran tidak pernah dipercaya dari browser.

## 14. Kebutuhan Nonfungsional

- Desain dimulai dari viewport mobile, kemudian ditingkatkan untuk tablet dan desktop.
- Seluruh alur pelanggan harus berfungsi tanpa horizontal scrolling pada viewport mobile yang didukung, kecuali interaksi peta.
- Navigasi utama dan aksi checkout harus mudah dijangkau melalui interaksi sentuh.
- Target sentuh untuk aksi utama harus cukup besar dan tidak saling berhimpitan.
- Form checkout menggunakan tipe input mobile yang sesuai, seperti telepon dan angka.
- Informasi utama, total, dan tombol aksi tidak boleh hanya bergantung pada efek hover.
- Desktop boleh menggunakan hover untuk feedback visual, tooltip, menu, dan aksi cepat sebagai peningkatan pengalaman.
- Pada PC atau laptop, breakpoint desktop harus menampilkan layout desktop penuh, bukan layout mobile yang diperbesar.
- Desktop menggunakan navigasi, grid, panel, dan tabel yang memanfaatkan ruang tambahan tanpa mengubah aturan bisnis utama.
- Bahasa utama adalah Bahasa Indonesia.
- Mata uang ditampilkan dalam format rupiah.
- Katalog dan checkout ditargetkan tetap nyaman digunakan pada koneksi seluler.
- Operasi penting menggunakan transaksi database.
- Log tersedia untuk pembayaran, kesalahan integrasi, dan perubahan status.
- Struktur modul harus dapat diuji dan dirawat secara terpisah.
- Aplikasi harus menggunakan zona waktu toko secara konsisten.

## 15. Pengujian

### Unit Test

- Perhitungan subtotal dan total.
- Harga promo dan voucher.
- Rumus ongkir.
- Validasi radius.
- Reservasi dan pelepasan stok.
- Kuota slot.
- Transisi status.

### Feature Test

- Katalog dan detail produk.
- Keranjang.
- Checkout pickup dan pengiriman.
- Pelacakan pesanan.
- Autentikasi dan pengelolaan admin.
- Filter serta ekspor laporan.

### Integration Test

- Pembuatan transaksi Midtrans menggunakan sandbox atau mock.
- Webhook valid, tidak valid, dan berulang.
- API peta menggunakan mock.
- Scheduler untuk transaksi kedaluwarsa.

### Skenario Kritis

- Dua pelanggan membeli stok terakhir secara bersamaan.
- Harga berubah ketika item masih berada di keranjang.
- Voucher kedaluwarsa atau kuotanya habis.
- Slot penuh saat checkout.
- Alamat berada di luar 10 km.
- Pembayaran berhasil tetapi redirect pelanggan gagal.
- Webhook diterima beberapa kali.
- Pembayaran kedaluwarsa setelah stok dicadangkan.

## 16. Kriteria Penerimaan Utama

Produk dianggap memenuhi versi pertama jika:

1. Pelanggan dapat membuat pesanan tanpa akun.
2. Produk mendukung varian, pilihan, add-on, dan stok per varian.
3. Ready stock dan pre-order terjadwal berjalan sesuai aturan.
4. Pickup dan pengiriman lokal dapat dipilih.
5. Ongkir dihitung berdasarkan jarak dan pesanan di luar 10 km ditolak.
6. Midtrans sandbox dapat membuat pembayaran dan webhook memperbarui status.
7. Stok serta kuota tetap konsisten pada pembayaran berhasil maupun gagal.
8. Pelanggan dapat melacak pesanan menggunakan kode dan nomor WhatsApp.
9. Admin dapat mengelola operasional dari satu dashboard.
10. Omzet dan laporan hanya menghitung transaksi yang sah.
11. Data satu pelanggan tidak dapat diakses pelanggan lain.
12. Alur kritis memiliki pengujian otomatis.

## 17. Tahapan Pengembangan

### Fase 1 — Fondasi dan Katalog

- Setup proyek dan database.
- Autentikasi admin.
- Pengaturan toko.
- Kategori, produk, varian, add-on, foto, dan stok.
- Katalog pelanggan.

### Fase 2 — Keranjang dan Pemenuhan

- Keranjang.
- Slot jadwal.
- Ready stock dan pre-order.
- Pickup.
- Integrasi peta, radius, dan ongkir.

### Fase 3 — Checkout dan Pembayaran

- Checkout tamu.
- Reservasi stok dan kuota.
- Midtrans Snap.
- Webhook dan transaksi kedaluwarsa.
- Pelacakan pesanan.

### Fase 4 — Operasional dan Pertumbuhan

- Pengelolaan status pesanan.
- Promo produk dan voucher.
- Notifikasi dashboard.
- Dashboard bisnis dan laporan.

### Fase 5 — Kualitas dan Peluncuran

- Pengujian menyeluruh.
- Audit keamanan.
- Optimasi performa mobile dan layout adaptif untuk layar lebih besar.
- Dokumentasi setup.
- Persiapan deployment dan konfigurasi production.

## 18. Risiko dan Mitigasi

| Risiko | Mitigasi |
|---|---|
| Overselling | Transaksi database, reservasi, dan penguncian stok |
| Webhook ganda | Pemrosesan idempoten dan penyimpanan referensi unik |
| Lokasi pelanggan tidak akurat | Pin peta, validasi alamat, dan konfirmasi jarak |
| Ketergantungan API eksternal | Timeout, penanganan error, logging, dan mock test |
| Ruang lingkup terlalu besar | Implementasi modular dalam lima fase |
| Data laporan tidak konsisten | Definisi omzet tunggal dan snapshot transaksi |
| Kunci API bocor | `.env`, sanitasi log, dan konfigurasi server |

## 19. Indikator Keberhasilan

Pada tahap project dan demonstrasi:

- Seluruh alur utama dapat dijalankan dari katalog sampai selesai.
- Tidak terjadi perbedaan antara total pesanan dan pembayaran.
- Tidak terjadi stok negatif.
- Pembayaran sandbox memperbarui pesanan secara otomatis.
- Dashboard menghasilkan angka yang sama dengan data transaksi.
- Seluruh alur pelanggan dapat digunakan dengan baik pada perangkat mobile tanpa kehilangan fungsi pada desktop.
