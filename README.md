# Hobi API

API untuk manajemen data User dan Hobi menggunakan Laravel 11 dengan sistem autentikasi JWT.

## Daftar Isi
- [Tech Stack](#tech-stack)
- [Arsitektur & Struktur](#arsitektur--struktur)
- [Instalasi](#instalasi)
- [Akun Default (Seeder)](#akun-default-seeder)
- [Fitur & Role](#fitur--role)
- [Dokumentasi API](#dokumentasi-api)
- [Pengujian (Testing)](#pengujian-testing)

---

## Tech Stack
- **Framework:** [Laravel 12](https://laravel.com)
- **Authentication:** [JWT-Auth](https://github.com/tymondesigns/jwt-auth) (Tymon)
- **Database:** MySQL
- **Language:** PHP 8.2+

---

## Arsitektur & Struktur
Project ini mengikuti pola **Service-Controller-Resource** untuk menjaga kode tetap bersih, teruji, dan modular.

### Struktur Folder Penting:
- `app/Http/Controllers/Api`: Menangani request HTTP dan mengembalikan response.
- `app/Services`: Berisi logika bisnis utama (dipisahkan dari controller).
- `app/Http/Requests`: Validasi data input menggunakan Form Request.
- `app/Http/Resources`: Transformasi model Eloquent ke format JSON yang konsisten.
- `app/Models`: Model database (User, Hobby).
- `docs/`: Berisi file spesifikasi API dalam format JSON (OpenAPI 3.0).
- `routes/api.php`: Definisi semua endpoint API.

---

## Instalasi

1. **Clone Project**
   ```bash
   git clone <repository-url>
   cd hobi-api
   ```

2. **Install Dependensi**
   ```bash
   composer install
   ```

3. **Konfigurasi Environment**
   Salin file `.env.example` ke `.env` dan sesuaikan pengaturan database Anda.
   ```bash
   cp .env.example .env
   ```

4. **Generate Keys**
   Jalankan perintah berikut untuk meng-generate Application Key dan JWT Secret.
   ```bash
   php artisan key:generate
   php artisan jwt:secret
   ```

5. **Migrasi & Seeding**
   Jalankan migrasi database beserta data awal (seeder).
   ```bash
   php artisan migrate --seed
   ```

---

## Akun Default (Seeder)
Gunakan akun berikut untuk mencoba API setelah menjalankan seeder:

- **Admin:** `admin@mail.com` | Password: `password`
- **Regular User:** `user@mail.com` | Password: `password`

---

## Fitur & Role

### 1. Autentikasi (JWT)
- **Login:** Mendapatkan access token.
- **Logout:** Meng-invalidate token yang sedang digunakan.
- **Refresh:** Memperbarui token yang akan expired.

### 2. Manajemen User (Khusus Admin)
- Admin memiliki akses penuh (CRUD) ke data semua user.
- Diproteksi menggunakan `AdminMiddleware`.

### 3. Manajemen Hobi
- **User Biasa:** Hanya dapat mengelola (CRUD) hobi milik mereka sendiri.
- **Admin:** Dapat mengelola hobi mereka sendiri DAN hobi user lain melalui endpoint spesifik `/api/users/{user}/hobbies`.

---

## Dokumentasi API
Dokumentasi lengkap dalam format OpenAPI 3.0 tersedia di folder `docs/`:
- [Auth API Specification](docs/auth_api_spec.json)
- [User API Specification](docs/user_api_spec.json)
- [Hobby API Specification](docs/hobby_api_spec.json)

---

## Pengujian (Testing)
Project ini dilengkapi dengan pengujian fitur (Feature Testing) yang mencakup skenario sukses dan berbagai skenario error (validasi, hak akses, data tidak ditemukan).

### Menjalankan Semua Test
```bash
php artisan test
```

### Cakupan Test:
- **`AuthTest.php`**: Login (sukses/gagal), Logout, dan Refresh.
- **`UserTest.php`**: CRUD User dengan validasi role Admin.
- **`HobbyTest.php`**: CRUD Hobi untuk User biasa dan Admin, termasuk batasan hak akses lintas user.

*Catatan: Setiap menjalankan test, database akan di-refresh dan di-seed secara otomatis menggunakan trait `RefreshDatabase` dan method `setUp()`.*
