````markdown
# PhoneTag - Contact Tag Lookup API

**PhoneTag** adalah API ringan dan cepat berbasis **Workerman** untuk mengambil tag kontak berdasarkan nomor telepon. Cocok untuk integrasi cepat dengan layanan eksternal dalam mengidentifikasi atau mengklasifikasikan nomor telepon.

## Features

-   Phone Number Tag Lookup
-   High-Performance Workerman Server
-   Secure API Authentication (Bearer Token)
-   Automatic Phone Number Normalization
-   Flexible External API Integration

## Installation

1. Install dependencies

```sh
composer install
```

2. Salin dan sesuaikan file konfigurasi lingkungan

```sh
cp .env.example .env
```

## Environment Configuration

Edit file `.env` sesuai konfigurasi:

```env
HOST=
PORT=
API_KEY=
GET_CONTACT_BASE_URL=
GET_CONTACT_TOKEN=
GET_CONTACT_KEY=
```

## Running the Server

### Development Mode

```sh
php server.php start
```

### Production Mode (Daemon)

```sh
php server.php start -d
```

## API Endpoint

### Lookup Contact Tags

-   URL: `/tags`
-   Method: `POST`
-   Authentication: Bearer Token
-   Request Body:

```json
{
	"number": "081234567890"
}
```

### Response Example

#### Successful Response (Multiple Tags):

```json
{
	"data": ["tag1", "tag2", "tag3"]
}
```

## Workerman Commands

-   `php server.php start` — Menjalankan server
-   `php server.php start -d` — Menjalankan server dalam mode daemon
-   `php server.php stop` — Menghentikan server
-   `php server.php restart` — Memulai ulang server
-   `php server.php status` — Menampilkan status server

## Phone Number Normalization

API ini secara otomatis mengubah nomor telepon menjadi format internasional Indonesia (`628`):

| Input          | Normalized Output |
| -------------- | ----------------- |
| 081234567890   | 6281234567890     |
| 81234567890    | 6281234567890     |
| +6281234567890 | 6281234567890     |

## Security

-   Menggunakan autentikasi Bearer Token
-   Memvalidasi semua request masuk
-   Melakukan normalisasi dan sanitasi nomor telepon sebelum diproses
````
