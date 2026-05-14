# Panduan Menjalankan Suratra dari File ZIP

Dokumen ini khusus untuk menjalankan aplikasi **tanpa Git/clone**, langsung dari file ZIP project.

## 1. Ekstrak ZIP

1. Ekstrak file ZIP project ke folder lokal, misalnya:
   - `C:\suratra`
2. Buka terminal di folder project tersebut.

## 2. Pastikan Prasyarat Terpasang

- PHP 8.2+
- Composer
- Node.js + npm
- MySQL (opsional, jika tidak memakai SQLite)

Cek cepat:

```bash
php -v
composer -V
node -v
npm -v
```

## 3. Siapkan Environment

Jika file `.env` **sudah ada** di ZIP, lanjut ke langkah berikutnya.  
Jika belum ada:

```bash
cp .env.example .env
```

PowerShell:

```powershell
Copy-Item .env.example .env
```

Nilai minimum yang disarankan di `.env`:

```env
APP_NAME=Suratra
APP_URL=http://127.0.0.1:8000
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_TIMEZONE=Asia/Jakarta
```

Atur juga koneksi database Anda (`DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

## 4. Install Dependency

```bash
composer install
npm install
```

## 5. Inisialisasi Aplikasi

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

## 6. Jalankan Aplikasi

```bash
php artisan serve
```

Buka di browser:
- `http://127.0.0.1:8000`

## 7. Akun Demo

- RT: `rt@demo.local` / `12345`
- RW: `rw@demo.local` / `12345`
- Warga: `warga01@demo.local` dst / `12345`

## 8. Fitur Surat PDF (iLovePDF)

Agar proses approval RW bisa menghasilkan PDF, isi `.env`:

```env
ILOVEPDF_PUBLIC_KEY=your_public_key
ILOVEPDF_SECRET_KEY=your_secret_key
```

Lalu:

```bash
php artisan config:clear
```

Catatan: tanpa key iLovePDF, proses generate PDF akan gagal.

## 9. Notifikasi WhatsApp (Opsional)

Jika tidak dibutuhkan saat demo dosen, boleh dilewati.  
Jika ingin diaktifkan:

```env
WHATSAPP_ENABLED=true
WHATSAPP_SERVICE_URL=http://127.0.0.1:3001
```

Jalankan service:

```bash
cd whatsapp-service
npm install
node index.js
```

Scan QR WhatsApp saat pertama kali jalan.

## 10. Pengujian

```bash
php artisan test
```

## 11. Troubleshooting Singkat

- Gagal login akun demo:
  - jalankan `php artisan migrate:fresh --seed`
- Surat gagal saat disetujui RW:
  - pastikan template DOCX tersedia di `storage/app/letter-templates/`
- PDF tidak terbuat:
  - cek key iLovePDF dan koneksi internet
- CSS/asset terasa tidak update:
  - jalankan `npm run dev` pada terminal terpisah
