# Software Requirements Specification (SRS)

## UMKM Store

| Informasi | Nilai |
|---|---|
| Versi | 1.1 |
| Tanggal | 25 Juni 2026 |
| Status | Draft untuk ditinjau |
| Acuan | PRD UMKM Store v1.1 |
| Platform | Web mobile-first dengan desktop adaptif |

## 1. Pendahuluan

### 1.1 Tujuan Dokumen

Dokumen ini mendefinisikan kebutuhan perangkat lunak UMKM Store secara terukur dan dapat diuji. SRS menjadi acuan untuk desain teknis, desain UI/UX, implementasi, dan pengujian.

### 1.2 Ruang Lingkup Sistem

UMKM Store adalah aplikasi e-commerce single-vendor untuk satu UMKM makanan dan minuman. Pelanggan menggunakan sistem tanpa akun, sedangkan satu admin terautentikasi mengelola seluruh operasional toko.

Antarmuka pelanggan dirancang dengan pendekatan mobile-first. Seluruh requirement pelanggan harus terlebih dahulu dapat dipenuhi pada layar ponsel dan interaksi sentuh. Pada PC atau laptop, breakpoint yang sesuai harus menghasilkan layout desktop penuh, bukan tampilan mobile yang sekadar diperbesar.

### 1.3 Istilah

| Istilah | Definisi |
|---|---|
| Admin | Pemilik atau pengelola tunggal aplikasi |
| Pelanggan | Pengunjung yang memesan tanpa akun |
| Ready stock | Produk yang dapat dipesan dari stok tersedia |
| Pre-order | Produk yang dipesan untuk slot tanggal dan jam tertentu |
| Varian | Pilihan produk yang memiliki harga dan stok sendiri |
| Option | Pilihan seperti level gula atau pedas |
| Add-on | Tambahan berbayar seperti topping |
| Pickup | Pesanan diambil pelanggan di toko |
| Delivery | Pesanan dikirim oleh kurir toko |
| Reservasi | Penahanan sementara stok dan kuota selama pembayaran |
| Webhook | Notifikasi server-ke-server dari Midtrans |
| Omzet | Nilai transaksi sah dari pesanan berstatus dibayar |

### 1.4 Prioritas

- **Must:** wajib tersedia pada versi pertama.
- **Should:** penting, tetapi dapat dijadwalkan setelah alur utama stabil.
- **Could:** peningkatan jika waktu pengembangan mencukupi.

## 2. Deskripsi Umum

### 2.1 Perspektif Produk

Sistem merupakan aplikasi Laravel monolitik modular:

- Blade dan Livewire menyajikan antarmuka.
- Laravel menjalankan aturan bisnis.
- MySQL menyimpan data transaksi.
- Midtrans memproses pembayaran.
- API peta menghitung jarak pengiriman.
- Queue dan Scheduler menjalankan pekerjaan latar belakang.

### 2.2 Aktor

| Aktor | Hak utama |
|---|---|
| Pelanggan | Melihat katalog, mengelola keranjang, checkout, membayar, dan melacak pesanan |
| Admin | Mengelola toko, produk, stok, jadwal, promo, pesanan, pembayaran, dan laporan |
| Midtrans | Membuat sesi pembayaran dan mengirim status transaksi |
| Penyedia API peta | Mengubah lokasi menjadi koordinat dan menghitung jarak |
| Scheduler | Menangani transaksi dan reservasi yang kedaluwarsa |

### 2.3 Batasan

- Hanya satu toko.
- Hanya satu akun admin.
- Tidak ada akun pelanggan.
- Radius delivery maksimal 10 km.
- Mata uang hanya IDR.
- Pembayaran pengembangan menggunakan Midtrans sandbox.
- Bahasa antarmuka utama adalah Bahasa Indonesia.
- Zona waktu aplikasi mengikuti zona waktu toko.

### 2.4 Asumsi dan Dependensi

- Toko mempunyai koordinat lokasi yang valid.
- Pelanggan mempunyai nomor WhatsApp aktif.
- Server dapat menerima webhook HTTPS ketika diuji di lingkungan publik.
- Layanan Midtrans dan API peta tersedia.
- Admin mengelola kebenaran data produk, stok, jadwal, dan tarif.

## 3. Kebutuhan Fungsional

### 3.1 Katalog

| ID | Requirement | Prioritas | Acceptance criteria |
|---|---|---|---|
| FR-CAT-001 | Sistem menampilkan produk aktif dan dapat dijual. | Must | Produk nonaktif atau diarsipkan tidak muncul di katalog. |
| FR-CAT-002 | Sistem menampilkan produk berdasarkan kategori. | Must | Memilih kategori hanya menampilkan produk dalam kategori tersebut. |
| FR-CAT-003 | Pelanggan dapat mencari produk berdasarkan nama. | Must | Hasil pencarian relevan dan pencarian kosong mengembalikan katalog. |
| FR-CAT-004 | Pelanggan dapat memfilter ready stock dan pre-order. | Should | Hasil sesuai tipe penjualan yang dipilih. |
| FR-CAT-005 | Sistem menampilkan foto, nama, harga awal, promo, dan status stok. | Must | Informasi kartu dan detail konsisten dengan data aktif. |
| FR-CAT-006 | Sistem menyediakan pagination atau pemuatan bertahap. | Should | Katalog tetap dapat digunakan saat jumlah produk bertambah. |

### 3.2 Detail dan Konfigurasi Produk

| ID | Requirement | Prioritas | Acceptance criteria |
|---|---|---|---|
| FR-PRD-001 | Sistem menampilkan deskripsi dan seluruh konfigurasi produk aktif. | Must | Hanya varian, option, dan add-on aktif yang dapat dipilih. |
| FR-PRD-002 | Pelanggan wajib memilih satu varian jika produk memiliki varian. | Must | Item tidak dapat ditambahkan sebelum varian dipilih. |
| FR-PRD-003 | Sistem mendukung option tunggal seperti level gula atau pedas. | Must | Pilihan wajib harus dipilih; pilihan opsional boleh kosong. |
| FR-PRD-004 | Sistem mendukung beberapa add-on. | Must | Add-on terpilih menambah harga item sesuai data server. |
| FR-PRD-005 | Pelanggan dapat menambahkan catatan item. | Must | Catatan tersimpan pada item pesanan dengan batas panjang yang divalidasi. |
| FR-PRD-006 | Sistem menghitung estimasi harga konfigurasi secara langsung. | Must | Harga tampilan berubah saat varian, option berharga, add-on, atau jumlah berubah. |
| FR-PRD-007 | Sistem menolak produk atau varian yang stoknya tidak cukup. | Must | Pelanggan memperoleh pesan yang jelas dan item tidak masuk keranjang. |

### 3.3 Keranjang

| ID | Requirement | Prioritas | Acceptance criteria |
|---|---|---|---|
| FR-CRT-001 | Pelanggan dapat menambah item terkonfigurasi ke keranjang. | Must | Konfigurasi berbeda menjadi baris item berbeda. |
| FR-CRT-002 | Pelanggan dapat mengubah jumlah item. | Must | Subtotal diperbarui dan jumlah tidak boleh melebihi stok. |
| FR-CRT-003 | Pelanggan dapat menghapus item. | Must | Item dan subtotal hilang dari keranjang. |
| FR-CRT-004 | Keranjang disimpan dalam sesi browser. | Must | Keranjang tetap tersedia selama sesi masih berlaku. |
| FR-CRT-005 | Sistem memvalidasi ulang harga dan ketersediaan. | Must | Perubahan ditampilkan sebelum pelanggan melanjutkan checkout. |
| FR-CRT-006 | Sistem menampilkan subtotal keranjang. | Must | Nilai berasal dari harga yang dihitung server. |

### 3.4 Identitas Pelanggan

| ID | Requirement | Prioritas | Acceptance criteria |
|---|---|---|---|
| FR-CUS-001 | Checkout meminta nama pelanggan. | Must | Nama kosong atau tidak valid ditolak. |
| FR-CUS-002 | Checkout meminta nomor WhatsApp. | Must | Nomor dinormalisasi dan divalidasi sebelum pesanan dibuat. |
| FR-CUS-003 | Sistem tidak mewajibkan registrasi atau login pelanggan. | Must | Seluruh alur pembelian dapat selesai sebagai tamu. |
| FR-CUS-004 | Pelanggan dapat mengisi catatan pesanan. | Could | Catatan tersimpan dan terlihat oleh admin. |

### 3.5 Jadwal dan Pre-order

| ID | Requirement | Prioritas | Acceptance criteria |
|---|---|---|---|
| FR-SLT-001 | Admin dapat membuat slot tanggal dan jam. | Must | Slot tersimpan dengan waktu mulai, selesai, dan kuota. |
| FR-SLT-002 | Admin dapat menentukan batas waktu pemesanan slot. | Must | Slot tidak dapat dipilih setelah batas waktu. |
| FR-SLT-003 | Admin dapat menutup slot. | Must | Slot tertutup tidak tersedia bagi pelanggan. |
| FR-SLT-004 | Pelanggan hanya dapat memilih slot yang tersedia. | Must | Slot penuh, lewat, atau tertutup tidak dapat dipilih. |
| FR-SLT-005 | Sistem mencadangkan kuota saat pesanan dibuat. | Must | Dua transaksi bersamaan tidak dapat melampaui kuota. |
| FR-SLT-006 | Sistem melepaskan kuota ketika pembayaran gagal atau kedaluwarsa. | Must | Kuota kembali tersedia tepat satu kali. |

### 3.6 Pickup

| ID | Requirement | Prioritas | Acceptance criteria |
|---|---|---|---|
| FR-PIC-001 | Pelanggan dapat memilih pickup sebagai metode pemenuhan. | Must | Form alamat delivery tidak diwajibkan. |
| FR-PIC-002 | Pelanggan pickup wajib memilih slot tersedia. | Must | Checkout ditolak tanpa slot valid. |
| FR-PIC-003 | Admin dapat mengubah pesanan pickup menjadi siap diambil. | Must | Hanya pesanan `processing` yang dapat menjadi `ready_for_pickup`. |
| FR-PIC-004 | Pesanan pickup dapat diselesaikan admin. | Must | `ready_for_pickup` dapat berpindah menjadi `completed`. |

### 3.7 Delivery dan Ongkir

| ID | Requirement | Prioritas | Acceptance criteria |
|---|---|---|---|
| FR-DLV-001 | Pelanggan dapat memilih delivery lokal. | Must | Form alamat dan titik peta menjadi wajib. |
| FR-DLV-002 | Pelanggan dapat menentukan titik lokasi pada peta. | Must | Latitude dan longitude valid tersimpan bersama alamat. |
| FR-DLV-003 | Sistem menghitung jarak dari toko ke pelanggan. | Must | Jarak diperoleh dari layanan peta dan disimpan pada delivery. |
| FR-DLV-004 | Sistem menolak lokasi di luar 10 km. | Must | Pelanggan tidak dapat membuat transaksi pembayaran. |
| FR-DLV-005 | Sistem menghitung tarif dasar ditambah jarak kali tarif per km. | Must | Hasil menggunakan konfigurasi aktif dan aturan pembulatan konsisten. |
| FR-DLV-006 | Server memvalidasi ulang ongkir saat checkout. | Must | Nilai ongkir dari browser tidak dipercaya. |
| FR-DLV-007 | Admin dapat menandai pesanan sedang dikirim. | Must | Hanya pesanan `processing` yang dapat menjadi `out_for_delivery`. |
| FR-DLV-008 | Admin dapat menyelesaikan pesanan delivery. | Must | `out_for_delivery` dapat berpindah menjadi `completed`. |

### 3.8 Promo Produk

| ID | Requirement | Prioritas | Acceptance criteria |
|---|---|---|---|
| FR-PPM-001 | Admin dapat membuat promo harga produk dengan periode aktif. | Must | Harga promo hanya berlaku dalam periode dan pada produk/varian yang ditetapkan. |
| FR-PPM-002 | Sistem memilih harga valid saat transaksi dibuat. | Must | Harga kedaluwarsa tidak digunakan walaupun masih tampil di browser. |
| FR-PPM-003 | Sistem menampilkan harga normal dan harga promo. | Must | Tampilan tidak ambigu dan subtotal memakai harga promo aktif. |

### 3.9 Voucher

| ID | Requirement | Prioritas | Acceptance criteria |
|---|---|---|---|
| FR-VCH-001 | Admin dapat membuat voucher nominal atau persentase. | Must | Jenis, nilai, periode, kuota, dan status tersimpan. |
| FR-VCH-002 | Admin dapat menentukan minimum belanja. | Must | Voucher ditolak jika subtotal yang memenuhi syarat belum tercapai. |
| FR-VCH-003 | Admin dapat menentukan maksimum diskon persentase. | Must | Diskon tidak melebihi batas. |
| FR-VCH-004 | Sistem membatasi satu voucher per pesanan. | Must | Voucher kedua menggantikan atau ditolak secara jelas. |
| FR-VCH-005 | Sistem memvalidasi periode, status, dan kuota voucher. | Must | Voucher tidak valid tidak mengurangi total. |
| FR-VCH-006 | Penggunaan voucher dicatat setelah pembayaran sah. | Must | Kuota tidak terpakai ganda oleh webhook berulang. |
| FR-VCH-007 | Voucher tidak mengurangi ongkir secara default. | Must | Dasar diskon adalah subtotal item kecuali konfigurasi menyatakan lain. |

### 3.10 Checkout dan Pesanan

| ID | Requirement | Prioritas | Acceptance criteria |
|---|---|---|---|
| FR-ORD-001 | Server memvalidasi ulang seluruh isi checkout. | Must | Harga, stok, slot, promo, voucher, ongkir, dan total tidak dipercaya dari browser. |
| FR-ORD-002 | Sistem membuat kode pesanan unik. | Must | Tidak ada dua pesanan dengan kode sama. |
| FR-ORD-003 | Pembuatan pesanan dan reservasi bersifat atomik. | Must | Kegagalan salah satu operasi membatalkan seluruh perubahan database. |
| FR-ORD-004 | Detail produk disimpan sebagai snapshot. | Must | Riwayat pesanan tidak berubah saat katalog diedit. |
| FR-ORD-005 | Sistem mencatat histori status. | Must | Setiap perubahan mempunyai status asal, tujuan, waktu, dan aktor. |
| FR-ORD-006 | Sistem membatasi transisi status yang valid. | Must | Transisi di luar state machine ditolak. |
| FR-ORD-007 | Admin dapat mencari dan memfilter pesanan. | Must | Filter kode, tanggal, pembayaran, fulfillment, dan status berfungsi. |
| FR-ORD-008 | Admin dapat membatalkan pesanan dengan alasan. | Must | Alasan tersimpan dan reservasi dilepas bila masih berlaku. |

### 3.11 Stok

| ID | Requirement | Prioritas | Acceptance criteria |
|---|---|---|---|
| FR-STK-001 | Admin dapat melihat dan mengubah stok per varian. | Must | Nilai stok terbaru tersimpan dan terlihat di katalog. |
| FR-STK-002 | Sistem melarang stok negatif. | Must | Operasi yang menghasilkan stok negatif dibatalkan. |
| FR-STK-003 | Sistem mencadangkan stok selama pembayaran pending. | Must | Stok tersedia memperhitungkan jumlah yang dicadangkan. |
| FR-STK-004 | Sistem mengesahkan pengurangan setelah pembayaran sah. | Must | Reservasi tidak dilepas setelah pembayaran berhasil. |
| FR-STK-005 | Sistem melepaskan reservasi gagal/kedaluwarsa. | Must | Stok tersedia kembali tepat satu kali. |
| FR-STK-006 | Sistem mencatat mutasi stok. | Must | Perubahan memiliki tipe, jumlah, referensi, waktu, dan saldo. |
| FR-STK-007 | Sistem memberikan notifikasi stok menipis. | Should | Notifikasi muncul saat melewati ambang konfigurasi. |

### 3.12 Pembayaran

| ID | Requirement | Prioritas | Acceptance criteria |
|---|---|---|---|
| FR-PAY-001 | Sistem membuat transaksi Midtrans berdasarkan total server. | Must | Nominal Midtrans sama dengan total pesanan. |
| FR-PAY-002 | Sistem menyimpan referensi dan token pembayaran. | Must | Referensi dapat dipakai untuk audit dan sinkronisasi. |
| FR-PAY-003 | Sistem menerima webhook Midtrans. | Must | Endpoint dapat menerima payload yang didukung. |
| FR-PAY-004 | Sistem memverifikasi keaslian webhook. | Must | Payload tidak valid tidak mengubah pesanan. |
| FR-PAY-005 | Pemrosesan webhook bersifat idempoten. | Must | Webhook berulang menghasilkan satu perubahan bisnis. |
| FR-PAY-006 | Sistem memetakan status Midtrans ke status internal. | Must | Pending, berhasil, gagal, batal, kedaluwarsa, dan refund terpetakan. |
| FR-PAY-007 | Status berhasil ditentukan server, bukan redirect browser. | Must | Menutup browser setelah membayar tidak mencegah pembaruan status. |
| FR-PAY-008 | Admin dapat melihat informasi pembayaran yang disanitasi. | Must | Data sensitif tidak ditampilkan. |
| FR-PAY-009 | Admin dapat meminta sinkronisasi status. | Should | Sistem mengambil status resmi dan mencatat hasilnya. |

### 3.13 Pelacakan Pesanan

| ID | Requirement | Prioritas | Acceptance criteria |
|---|---|---|---|
| FR-TRK-001 | Pelanggan dapat mencari pesanan dengan kode dan nomor WhatsApp. | Must | Hasil hanya muncul jika kombinasi cocok. |
| FR-TRK-002 | Sistem menampilkan ringkasan dan status pesanan. | Must | Item, pembayaran, fulfillment, jadwal, dan status terlihat. |
| FR-TRK-003 | Sistem menyembunyikan data internal dan sensitif. | Must | Payload webhook, catatan internal, dan data admin tidak tampil. |
| FR-TRK-004 | Endpoint pelacakan dibatasi untuk mencegah percobaan massal. | Must | Rate limit diterapkan dan kegagalan tidak membocorkan keberadaan kode. |

### 3.14 Admin dan Pengaturan

| ID | Requirement | Prioritas | Acceptance criteria |
|---|---|---|---|
| FR-ADM-001 | Admin dapat login dengan kredensial valid. | Must | Kredensial salah ditolak tanpa mengungkap detail akun. |
| FR-ADM-002 | Admin dapat logout. | Must | Sesi tidak dapat dipakai kembali setelah logout. |
| FR-ADM-003 | Halaman admin membutuhkan autentikasi. | Must | Pengunjung diarahkan ke login. |
| FR-ADM-004 | Admin dapat mengelola profil dan lokasi toko. | Must | Perubahan dipakai pada halaman publik dan ongkir berikutnya. |
| FR-ADM-005 | Admin dapat mengelola kategori, produk, foto, varian, option, dan add-on. | Must | CRUD dan aktivasi/nonaktivasi berfungsi sesuai validasi. |
| FR-ADM-006 | Data transaksi historis tidak dihapus secara fisik melalui UI. | Must | Produk terkait transaksi hanya dapat diarsipkan. |
| FR-ADM-007 | Admin dapat mengatur tarif dan ambang stok. | Must | Konfigurasi baru berlaku pada transaksi berikutnya. |

### 3.15 Dashboard, Laporan, dan Notifikasi

| ID | Requirement | Prioritas | Acceptance criteria |
|---|---|---|---|
| FR-RPT-001 | Dashboard menampilkan omzet transaksi sah. | Must | Pesanan gagal, batal, dan kedaluwarsa tidak dihitung. |
| FR-RPT-002 | Dashboard menampilkan jumlah pesanan per status. | Must | Nilai sama dengan hasil filter data pesanan. |
| FR-RPT-003 | Dashboard menampilkan tren penjualan per periode. | Must | Rentang tanggal memengaruhi data grafik. |
| FR-RPT-004 | Dashboard menampilkan produk dan varian terlaris. | Must | Peringkat memakai item dari transaksi sah. |
| FR-RPT-005 | Dashboard menampilkan stok menipis dan pesanan terbaru. | Must | Data sesuai kondisi terkini. |
| FR-RPT-006 | Admin dapat mengekspor laporan transaksi. | Must | Ekspor mengikuti filter dan menghasilkan file yang dapat dibuka. |
| FR-NTF-001 | Sistem membuat notifikasi operasional. | Must | Pesanan baru, pembayaran, pembatalan, dan stok menipis dicatat. |
| FR-NTF-002 | Admin dapat menandai notifikasi telah dibaca. | Should | Status baca tersimpan. |

## 4. Aturan Bisnis

| ID | Aturan |
|---|---|
| BR-001 | Satu produk harus mempunyai minimal satu varian yang dapat dijual. |
| BR-002 | Harga dan stok transaksi selalu berasal dari server. |
| BR-003 | Stok tersedia = stok fisik dikurangi stok yang sedang dicadangkan. |
| BR-004 | Satu pesanan hanya memakai satu metode fulfillment. |
| BR-005 | Satu pesanan hanya memakai satu voucher. |
| BR-006 | Radius delivery tidak boleh melebihi 10 km. |
| BR-007 | Jarak dan ongkir disimpan sebagai snapshot pada pesanan. |
| BR-008 | Pembayaran berhasil hanya diakui setelah verifikasi server. |
| BR-009 | Omzet memakai transaksi berstatus `paid`; refund mengurangi angka sesuai nilai refund. |
| BR-010 | Pesanan pickup melewati `ready_for_pickup`; delivery melewati `out_for_delivery`. |
| BR-011 | Status akhir `completed` dan `cancelled` tidak dapat dikembalikan melalui UI biasa. |
| BR-012 | Waktu kedaluwarsa reservasi mengikuti waktu kedaluwarsa transaksi pembayaran. |
| BR-013 | Perubahan katalog tidak mengubah snapshot pesanan. |
| BR-014 | Nomor WhatsApp disimpan dalam format ternormalisasi untuk pencarian. |
| BR-015 | Produk yang sudah memiliki transaksi hanya dapat diarsipkan. |

## 5. State Machine

### 5.1 Status Pembayaran

```text
pending ──> paid
   ├──────> failed
   ├──────> expired
   └──────> cancelled

paid ─────> refunded
```

### 5.2 Status Operasional

```text
awaiting_payment
      │ pembayaran sah
      ▼
  confirmed
      │ admin menerima
      ▼
  processing
    ┌─┴─────────────┐
    ▼               ▼
ready_for_pickup  out_for_delivery
    │               │
    └───────┬───────┘
            ▼
        completed
```

Pesanan dapat menjadi `cancelled` dari status nonfinal selama aturan pembayaran dan pengembalian reservasi dipenuhi.

## 6. Kebutuhan Antarmuka Eksternal

### 6.1 Midtrans

- Sistem menggunakan Snap.
- Server Key hanya tersedia di server.
- Client Key hanya digunakan sesuai kebutuhan antarmuka Snap.
- Webhook diterima melalui HTTPS.
- Signature diverifikasi.
- Timeout dan kegagalan jaringan dicatat tanpa menampilkan kredensial.

### 6.2 API Peta

- Sistem membutuhkan geocoding/pemilihan koordinat dan perhitungan jarak.
- Request mempunyai timeout.
- Checkout dihentikan jika jarak tidak dapat diverifikasi.
- Kunci API dibatasi berdasarkan domain atau server jika penyedia mendukungnya.

### 6.3 Ekspor

- Laporan diekspor minimal dalam CSV.
- Encoding harus dapat dibaca aplikasi spreadsheet umum.
- Kolom sensitif yang tidak diperlukan tidak disertakan.

## 7. Kebutuhan Nonfungsional

### 7.1 Keamanan

| ID | Requirement |
|---|---|
| NFR-SEC-001 | Password admin di-hash menggunakan mekanisme Laravel yang didukung. |
| NFR-SEC-002 | Sesi diregenerasi setelah login dan dihapus saat logout. |
| NFR-SEC-003 | Seluruh request perubahan data memakai perlindungan CSRF. |
| NFR-SEC-004 | Seluruh input divalidasi di server. |
| NFR-SEC-005 | Login, checkout, tracking, dan webhook memiliki rate limit sesuai risiko. |
| NFR-SEC-006 | Secret dan credential tidak disimpan dalam source control. |
| NFR-SEC-007 | Log menyamarkan nomor, alamat, credential, dan payload sensitif. |
| NFR-SEC-008 | File upload dibatasi tipe MIME, ukuran, dan ekstensi aman. |
| NFR-SEC-009 | Otorisasi admin diperiksa pada server untuk seluruh fungsi admin. |
| NFR-SEC-010 | Query menggunakan ORM/query binding untuk mencegah SQL injection. |

### 7.2 Kinerja

| ID | Requirement |
|---|---|
| NFR-PER-001 | Halaman katalog harus memakai pagination atau lazy loading. |
| NFR-PER-002 | Query daftar admin harus dipaginasi dan menghindari N+1 query. |
| NFR-PER-003 | Operasi eksternal menggunakan timeout dan tidak menggantung tanpa batas. |
| NFR-PER-004 | Pekerjaan nonkritis yang lambat dapat dijalankan melalui queue. |

### 7.3 Keandalan dan Konsistensi

| ID | Requirement |
|---|---|
| NFR-REL-001 | Pembuatan order, reservasi stok, dan kuota menggunakan transaksi database. |
| NFR-REL-002 | Webhook dan pelepasan reservasi harus idempoten. |
| NFR-REL-003 | Stok tidak boleh negatif pada kondisi transaksi bersamaan. |
| NFR-REL-004 | Perubahan status dan stok mempunyai audit trail. |
| NFR-REL-005 | Scheduler dapat dijalankan ulang tanpa menggandakan efek bisnis. |

### 7.4 Usability dan Aksesibilitas

| ID | Requirement |
|---|---|
| NFR-UX-001 | Antarmuka pelanggan dirancang mulai dari viewport mobile dan ditingkatkan secara progresif untuk tablet serta desktop. |
| NFR-UX-002 | Seluruh alur pelanggan berfungsi pada lebar viewport 360 piksel tanpa horizontal scrolling, kecuali komponen peta yang memang dapat digeser. |
| NFR-UX-003 | Navigasi utama mobile mudah dijangkau dan tidak mengandalkan hover. |
| NFR-UX-004 | Target sentuh aksi utama berukuran minimal 44 × 44 CSS pixel dan mempunyai jarak memadai. |
| NFR-UX-005 | Tombol aksi utama pada detail produk, keranjang, dan checkout tetap jelas serta mudah dijangkau pada ponsel. |
| NFR-UX-006 | Form menggunakan label jelas, tipe input mobile yang sesuai, dan urutan fokus logis. |
| NFR-UX-007 | Pesan validasi tampil dekat field terkait dan menjelaskan tindakan yang harus dilakukan. |
| NFR-UX-008 | Form dan navigasi tetap dapat digunakan dengan keyboard. |
| NFR-UX-009 | Status tidak dibedakan hanya dengan warna. |
| NFR-UX-010 | Nominal ditampilkan dalam format rupiah dan tanggal dalam format Indonesia. |
| NFR-UX-011 | Pada breakpoint desktop, aplikasi menampilkan layout desktop penuh dengan navigasi, grid, panel berdampingan, dan tabel yang sesuai. |
| NFR-UX-012 | Desktop boleh menyediakan hover state, tooltip, dropdown, dan aksi cepat sebagai peningkatan pengalaman. |
| NFR-UX-013 | Informasi atau fungsi inti tidak boleh hanya tersedia melalui hover agar tetap dapat diakses lewat sentuhan, keyboard, dan perangkat tanpa hover. |
| NFR-UX-014 | Adaptasi desktop tidak boleh mengubah aturan bisnis dan hasil akhir alur pelanggan. |

### 7.5 Maintainability dan Testability

| ID | Requirement |
|---|---|
| NFR-MNT-001 | Aturan harga, stok, promo, ongkir, dan status dipisahkan dari komponen UI. |
| NFR-MNT-002 | Integrasi Midtrans dan peta dibungkus service interface agar dapat di-mock. |
| NFR-MNT-003 | Konfigurasi lingkungan disimpan dalam environment/config. |
| NFR-MNT-004 | Fitur kritis mempunyai unit, feature, atau integration test. |
| NFR-MNT-005 | Penamaan modul dan database konsisten dalam bahasa teknis Inggris. |

### 7.6 Kompatibilitas

| ID | Requirement |
|---|---|
| NFR-CMP-001 | Aplikasi berjalan pada stack versi yang kompatibel dengan Laragon 13. |
| NFR-CMP-002 | Browser utama adalah Chrome dan browser berbasis Chromium pada Android; browser desktop modern menjadi target sekunder. |
| NFR-CMP-003 | Database menggunakan fitur yang tersedia pada versi MySQL terpilih. |
| NFR-CMP-004 | Pengujian UI pelanggan mencakup viewport mobile 360, 390, dan 430 CSS pixel serta viewport desktop 1280 dan 1440 CSS pixel. |
| NFR-CMP-005 | Pengujian desktop memverifikasi bahwa layout berpindah ke pola desktop dan hover state bekerja pada perangkat yang mendukung hover. |

## 8. Validasi Data Utama

| Data | Aturan minimum |
|---|---|
| Nama pelanggan | Wajib, teks wajar, panjang dibatasi |
| Nomor WhatsApp | Wajib, dinormalisasi, hanya pola nomor valid |
| Catatan | Opsional, panjang dibatasi, diperlakukan sebagai teks |
| Harga | Integer rupiah, minimal 0 |
| Stok | Integer, minimal 0 |
| Kuota slot | Integer, minimal 1 |
| Latitude | Angka antara -90 dan 90 |
| Longitude | Angka antara -180 dan 180 |
| Jarak | Angka nonnegatif, maksimal 10 km untuk checkout |
| Voucher | Kode unik, periode logis, nilai sesuai jenis |
| Gambar | JPG, PNG, atau WebP; ukuran dibatasi |

Nilai batas panjang dan ukuran file ditetapkan pada SDD dan konfigurasi aplikasi.

## 9. Penanganan Kegagalan

| Kondisi | Respons sistem |
|---|---|
| Stok berubah | Checkout ditolak dan keranjang diperbarui |
| Slot penuh | Pelanggan diminta memilih slot lain |
| Lokasi di luar radius | Pembayaran tidak dibuat |
| API peta gagal | Checkout dihentikan dengan pesan coba kembali |
| Midtrans gagal membuat transaksi | Pesanan dibatalkan secara aman dan reservasi dilepas |
| Webhook tidak valid | Tidak mengubah data; kejadian dicatat |
| Webhook berulang | Mengembalikan respons sukses tanpa efek bisnis ganda |
| Pembayaran kedaluwarsa | Status diperbarui dan reservasi dilepas |
| Kesalahan internal | Pesan umum ditampilkan; detail hanya masuk log |

## 10. Kebutuhan Pengujian dan Traceability

Setiap requirement `Must` harus memiliki minimal satu pengujian atau langkah verifikasi. Pemetaan rinci requirement ke test case disusun pada test plan.

Skenario wajib:

1. Pelanggan menyelesaikan pickup ready stock.
2. Pelanggan menyelesaikan delivery dalam radius.
3. Delivery di luar 10 km ditolak.
4. Produk pre-order menggunakan slot dan kuota.
5. Dua checkout berebut stok terakhir.
6. Harga berubah setelah item masuk keranjang.
7. Voucher tidak valid atau kuota habis.
8. Pembayaran berhasil melalui webhook.
9. Webhook yang sama diterima berulang.
10. Pembayaran kedaluwarsa melepaskan stok dan slot.
11. Tracking gagal jika nomor WhatsApp tidak cocok.
12. Laporan hanya menghitung transaksi sah.

## 11. Kriteria Penerimaan Sistem

Sistem dapat dinyatakan memenuhi SRS v1.1 jika:

- Seluruh requirement prioritas Must selesai atau diberi keputusan perubahan ruang lingkup yang terdokumentasi.
- Tidak terdapat bug kritis pada checkout, pembayaran, stok, slot, dan otorisasi.
- Seluruh skenario wajib lulus.
- Angka total pesanan, pembayaran, stok, dan laporan konsisten.
- Data pelanggan tidak dapat diakses melalui kombinasi tracking yang salah.
- Aplikasi dapat dijalankan melalui dokumentasi setup pada lingkungan yang ditentukan.

## 12. Requirement di Luar Versi Pertama

- Registrasi dan login pelanggan.
- Multi-admin dan role-based access control.
- Multi-vendor.
- Integrasi WhatsApp API.
- Aplikasi kurir dan pelacakan kurir real-time.
- Stok bahan baku dan resep.
- Integrasi POS.
- Loyalty point.
- Ulasan pelanggan.
- Pembayaran cicilan atau langganan.
