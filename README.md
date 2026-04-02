# Tiket Backend

Backend API untuk platform pemesanan tiket event berbasis Laravel.

## Tech Stack

- Laravel 12
- Laravel Sanctum
- MySQL
- Midtrans Snap
- Groq API

## Fitur

- Auth register, login, logout, dan `me`
- Role `admin` dan `user`
- CRUD event
- Status event: `draft`, `published`, `sold_out`, `ended`
- Metadata event: kategori, venue, kota, organizer, highlights, terms
- Checkout tiket dengan Midtrans
- Membership discount
- Voucher preview dan voucher discount
- Payment method toggle
- Admin analytics
- Scanner check-in tiket
- Chatbot proxy via backend

## Struktur API Utama

- `/api/register`
- `/api/login`
- `/api/tickets`
- `/api/checkout`
- `/api/transactions`
- `/api/admin/analytics`
- `/api/admin/payment-methods`
- `/api/admin/vouchers`
- `/api/vouchers/preview`
- `/api/chatbot`

## Setup

```bash
cd tiket-backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Default backend jalan di:

```txt
http://localhost:8000
```

## Environment

Isi minimal `.env`:

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tiket_db
DB_USERNAME=root
DB_PASSWORD=

MIDTRANS_SERVER_KEY=your_midtrans_server_key
MIDTRANS_CLIENT_KEY=your_midtrans_client_key
MIDTRANS_IS_PRODUCTION=false

GROQ_API_KEY=your_groq_api_key
GROQ_MODEL=llama-3.3-70b-versatile
GROQ_BASE_URL=https://api.groq.com/openai/v1
```

## Seeder

Seeder yang sudah disiapkan:

- admin default
- event demo sesuai fitur terbaru
- voucher demo aktif, expired, dan inactive

Jalankan ulang seeder:

```bash
php artisan db:seed
```

Kalau mau reset total:

```bash
php artisan migrate:fresh --seed
```

## Akun Admin Default

```txt
email: admin@tiket.com
password: password123
```

## Script Berguna

```bash
php artisan serve
php artisan migrate
php artisan db:seed
php artisan route:list
php artisan test
```

## Catatan

- Secret payment dan chatbot disimpan di backend, bukan frontend.
- Frontend mengakses backend lewat path relatif `/api`.
- Untuk production, idealnya frontend dan backend disajikan dalam origin/domain yang sama.
