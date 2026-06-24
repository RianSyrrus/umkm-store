# Panduan Setup Development Lokal (UMKM Store)

Dokumen ini berisi panduan untuk memasang dan menjalankan aplikasi UMKM Store di lingkungan lokal Anda.

## Prasyarat Sistem
Pastikan komputer Anda sudah terpasang:
- **PHP 8.3** atau versi lebih baru
- **Composer** (PHP dependency manager)
- **Node.js** & **NPM** (untuk frontend compilation)
- **SQLite** (opsi database default lokal)

---

## Langkah Instalasi

1. **Clone & Masuk ke Folder Project:**
   ```bash
   cd c:/laragon/www/umkm-store
   ```

2. **Instal Dependensi PHP (Composer):**
   ```bash
   composer install
   ```

3. **Instal Dependensi Frontend (NPM):**
   ```bash
   npm install
   ```

4. **Salin Environment Configuration:**
   Jika belum ada, salin file `.env.example` menjadi `.env`:
   ```bash
   copy .env.example .env
   ```

5. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

6. **Migrasi & Seed Database:**
   Gunakan perintah ini untuk memigrasi tabel database SQLite dan mengisi data demo produk/toko secara otomatis:
   ```bash
   php artisan migrate:fresh --seed
   ```

---

## Menjalankan Aplikasi

Jalankan dua perintah berikut di terminal terpisah untuk memulai server development:

1. **Jalankan Backend Laravel:**
   ```bash
   php artisan serve
   ```
   Aplikasi Anda dapat diakses di: **`http://localhost:8000`**

2. **Jalankan Asset Compiler (Vite):**
   ```bash
   npm run dev
   ```

---

## Kredensial Login Admin

Gunakan informasi berikut untuk masuk ke halaman Dashboard Admin:
- **Halaman Login:** `http://localhost:8000/admin/login`
- **Email:** `admin@umkm.test`
- **Password:** `change-me-now`
