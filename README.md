# Suratra

Suratra adalah aplikasi persuratan RT/RW berbasis Laravel.
Fitur utama:
- login berbasis role (`warga`, `pengurus_rt`, `pengurus_rw`)
- pengajuan surat oleh warga
- verifikasi bertahap RT lalu RW
- arsip surat dan unduh dokumen (PDF/DOCX)
- notifikasi WhatsApp (opsional)

## Tech Stack
- PHP 8.2+
- Laravel 11
- MySQL atau SQLite
- Bootstrap 5
- Node.js 18+ (untuk tooling frontend/Vite)
- Microservice Node.js (opsional, untuk WhatsApp)

## 1) Setup Aplikasi Laravel

### Prasyarat
Pastikan sudah terpasang:
- `php` 8.2+
- `composer`
- `node` dan `npm`
- database server (jika tidak pakai SQLite)

### Instalasi
1. Install dependency PHP:

```bash
composer install
```

2. Install dependency Node:

```bash
npm install
```

3. Siapkan env:

```bash
cp .env.example .env
```

Jika memakai PowerShell:

```powershell
Copy-Item .env.example .env
```

4. Atur konfigurasi utama di `.env`:
- `APP_NAME=Suratra`
- `APP_URL=http://127.0.0.1:8000`
- `APP_LOCALE=id`
- `APP_FALLBACK_LOCALE=id`
- `APP_TIMEZONE=Asia/Jakarta`
- koneksi database (`DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)

5. Generate app key:

```bash
php artisan key:generate
```

6. Migrasi dan seeding:

```bash
php artisan migrate --seed
```

7. Buat symbolic link storage publik:

```bash
php artisan storage:link
```

8. Jalankan aplikasi:

```bash
php artisan serve
```

Akses di `http://127.0.0.1:8000`.

## 2) Template DOCX (Wajib untuk Approval RW)

Aplikasi membutuhkan template DOCX untuk generate surat.
Folder target:

`storage/app/letter-templates/`

Nama file yang harus tersedia:
- `surat_pengantar_umum.docx`
- `surat_keterangan_domisili.docx`
- `surat_pengantar_skck.docx`
- `surat_keterangan_usaha.docx`

Jika file ini tidak ada, proses approval RW akan gagal saat generate dokumen.

## 3) Konversi DOCX ke PDF via iLovePDF (Wajib untuk PDF)

Tambahkan API key iLovePDF di `.env`:

```env
ILOVEPDF_PUBLIC_KEY=your_public_key
ILOVEPDF_SECRET_KEY=your_secret_key
```

Lalu clear config:

```bash
php artisan config:clear
```

Dokumentasi tambahan: `docs/ILovePDF_SETUP.md`.

## 4) Notifikasi WhatsApp (Opsional)

### Aktifkan di Laravel
Set di `.env`:

```env
WHATSAPP_ENABLED=true
WHATSAPP_SERVICE_URL=http://127.0.0.1:3001
```

Lalu:

```bash
php artisan config:clear
```

### Jalankan microservice

```bash
cd whatsapp-service
npm install
node index.js
```

Saat pertama kali jalan, scan QR WhatsApp di terminal.

Dokumentasi tambahan: `docs/WHATSAPP_SETUP.md`.

## 5) Akun Demo (setelah `--seed`)

- RT: `rt@demo.local` / `12345`
- RW: `rw@demo.local` / `12345`
- Warga: `warga01@demo.local` s.d. `wargaXX@demo.local` / `12345`

Catatan: password demo hanya untuk lokal/development.

## 6) Menjalankan Test

```bash
php artisan test
```

## 7) Catatan Frontend

Saat ini layout memuat stylesheet dari `public/css/app.css`.
Vite tetap bisa dijalankan bila ingin pengembangan aset dari folder `resources`:

```bash
npm run dev
```

## 8) Troubleshooting Singkat

- Gagal login akun demo: jalankan ulang `php artisan migrate:fresh --seed`.
- Surat gagal dibuat saat RW approve: cek template DOCX di `storage/app/letter-templates`.
- PDF tidak terbentuk: cek key iLovePDF dan koneksi internet server.
- Notifikasi WA tidak terkirim: pastikan service WhatsApp hidup dan status `/health` connected.
