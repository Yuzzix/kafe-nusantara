# Kafe Nusantara — Sistem Pemesanan Restoran/Kafe

Aplikasi web **Sistem Pemesanan Restoran/Kafe (Relasi Menu – Kategori Menu – Pesanan)** menggunakan **Laravel 13**, dibuat untuk proyek Ujian Tengah Semester (UTS) mata kuliah **Pemrograman Web Tingkat Mahir (PG184)**.

Dokumentasi skema database lengkap (ERD, relasi Eloquent, contoh query *Eager Loading*, dan helper untuk Blade UI) tersedia di **[DATABASE.md](./DATABASE.md)**.

---

## Tech Stack

- **Backend:** PHP 8.3+, Laravel 13
- **Database:** MySQL / MariaDB (`restoran_db`)
- **Frontend:** Laravel Blade + Tailwind CSS v4 (Vite)

---

## Panduan Setup untuk Anggota Tim (Setelah `git clone`)

Pastikan sudah menginstal **PHP 8.3+**, **Composer**, **Node.js & NPM**, serta **MySQL** (XAMPP / Laragon).

### 1. Clone repository & masuk ke folder proyek

```bash
git clone <URL_REPO_GITHUB> restoran-app
cd restoran-app
```
   
### 2. Nyalakan MySQL & buat database

Nyalakan modul **MySQL** di XAMPP / Laragon, lalu buat database kosong bernama:

```sql
CREATE DATABASE restoran_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Install dependency & jalankan setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

> **Catatan Windows PowerShell / CMD:** Jika `cp` tidak dikenali di CMD, gunakan `copy .env.example .env`. Sesuaikan `DB_USERNAME` / `DB_PASSWORD` di `.env` jika MySQL lokal memakai password.

### 4. Jalankan migrasi & seeder database

```bash
php artisan migrate:fresh --seed
```

*(Atau gunakan shortcut `composer fresh` kapan saja ingin mereset isi database ke data awal).*

### 5. Install asset frontend & jalankan server lokal

```bash
npm install
npm run dev
```

Buka terminal kedua di folder yang sama, lalu jalankan server Laravel:

```bash
php artisan serve
```

Aplikasi dapat diakses di `http://localhost:8000`.

---

## Akun Staf Demo (Hasil Seeder)

Semua akun staf menggunakan password: **`password`**

| Email | Role | Status |
|---|---|---|
| `admin@kafe.test` | Admin | Aktif |
| `kasir@kafe.test`, `kasir2@kafe.test` | Kasir | Aktif |
| `pelayan@kafe.test`, `pelayan2@kafe.test` | Pelayan | Aktif |
| `dapur@kafe.test` | Dapur | Aktif |
| `joko@kafe.test` | Pelayan | Nonaktif |

---

## Alur Kerja Git Kelompok (Cheat Sheet)

Agar kode Backend dan Frontend/UI tidak saling menimpa:

1. **Selalu ambil update terbaru sebelum mulai ngoding:**
   ```bash
   git checkout main
   git pull origin main
   ```
2. **Buat branch sesuai tugas masing-masing:**
   ```bash
   # Contoh untuk UI/UX:
   git checkout -b feat/ui-menu
   # Contoh untuk Backend:
   git checkout -b feat/order-controller
   ```
3. **Simpan perubahan (commit) & kirim ke GitHub (push):**
   ```bash
   git status
   git add .
   git commit -m "feat: tambah halaman daftar menu dan komponen kartu"
   git push -u origin feat/ui-menu
   ```
4. **Gabungkan ke `main` lewat Pull Request (PR) di GitHub**, lalu setelah di-merge, anggota tim lain cukup menjalankan:
   ```bash
   git checkout main
   git pull origin main
   composer install
   php artisan migrate:fresh --seed
   ```
