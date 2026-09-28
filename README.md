# FitTrack Backend (Laravel REST API)

15 tabel sesuai dokumen *Spesifikasi File Strava*, auth token pakai Laravel Sanctum.

## Setup (dari nol)

```bash
composer create-project laravel/laravel fittrack-backend
cd fittrack-backend
php artisan install:api          # pasang Sanctum + bikin routes/api.php
```

Lalu **copy isi folder ini ke project Laravel** (timpa kalau ditanya):

| Dari folder ini            | Ke project Laravel           |
|----------------------------|------------------------------|
| `database/migrations/*`    | `database/migrations/`       |
| `database/seeders/*`       | `database/seeders/`          |
| `app/Models/*`             | `app/Models/`                |
| `app/Http/Controllers/Api/*` | `app/Http/Controllers/Api/` |
| `routes/api.php`           | `routes/api.php`             |

**Hapus migration bawaan users** (bentrok sama migration users kita):

```bash
del database\migrations\0001_01_01_000000_create_users_table.php    # Windows
```

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fittrack
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file      # tabel sessions bawaan ikut terhapus di langkah atas
```

Buat database `fittrack` di MySQL (XAMPP/Laragon), lalu:

```bash
php artisan migrate --seed
php artisan serve --host=0.0.0.0     # biar bisa diakses dari HP
```

## Endpoint

Semua (kecuali register/login) butuh header `Authorization: Bearer <token>`.

| Method | URL | Fungsi |
|---|---|---|
| POST | `/api/register` | Daftar, balikin `user` + `token` |
| POST | `/api/login` | Login pakai `login` (email/username) + `password` |
| POST | `/api/logout` | Hapus token aktif |
| GET | `/api/me` | Profil sendiri |
| GET/PUT/DELETE | `/api/users`, `/api/users/{id}` | Cari user, lihat profil, edit/hapus akun sendiri |
| CRUD | `/api/activity-types` | Jenis aktivitas |
| CRUD | `/api/activities` | Aktivitas milik sendiri (`visibility`: public/followers/private) |
| GET | `/api/feed` | Aktivitas sendiri + user yang di-follow |
| GET/POST/PUT/DELETE | `/api/activity-streams` | Titik GPS per aktivitas (`?activity_id=`) |
| POST | `/api/activity-streams/bulk` | Upload seluruh rute GPS sekaligus |
| CRUD | `/api/follows` | Follow (pending) → user tujuan `PUT status=accepted` |
| GET/POST/DELETE | `/api/kudos` | Kasih/cabut kudos (`?activity_id=`) |
| CRUD | `/api/comments`, `/api/photos` | Komentar & foto per aktivitas |
| CRUD | `/api/segments`, GET `/api/segments/{id}/leaderboard` | Segment + papan peringkat |
| CRUD | `/api/segment-efforts` | Catatan waktu di segment |
| CRUD | `/api/clubs`, `/api/club-members` | Klub (pembuat otomatis admin), gabung/keluar/ubah peran |
| CRUD | `/api/gears` | Sepatu/sepeda milik sendiri |
| CRUD | `/api/achievements` | Master achievement |
| GET/POST/DELETE | `/api/user-achievements` | Achievement yang diraih |

Semua list pakai pagination (`?per_page=20&page=1`).

### Contoh cepat

```bash
curl -X POST http://localhost:8000/api/register -H "Accept: application/json" \
  -d "name=Gilang&username=gilang&email=g@x.com&password=rahasia123"

curl http://localhost:8000/api/me -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN_DARI_LOGIN"
```

> Selalu kirim header `Accept: application/json` supaya error validasi balik sebagai JSON (bukan redirect HTML).
