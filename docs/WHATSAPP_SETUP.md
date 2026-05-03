# Setup Notifikasi WhatsApp (Baileys)

Dokumen ini menjelaskan cara menjalankan microservice WhatsApp berbasis Baileys dan integrasinya dengan aplikasi Laravel.

## 1) Konfigurasi Laravel

Isi `.env` Laravel:

```env
WHATSAPP_ENABLED=true
WHATSAPP_SERVICE_URL=http://127.0.0.1:3001
```

Lalu jalankan:

```bash
php artisan config:clear
```

## 2) Jalankan Microservice Baileys

Masuk ke folder service:

```bash
cd whatsapp-service
npm install
node index.js
```

Service berjalan di port `3001`.

## 3) Login WhatsApp (Scan QR Sekali)

Saat pertama dijalankan, QR akan muncul di terminal microservice.
Scan menggunakan WhatsApp akun pengirim notifikasi.

Session tersimpan di folder:

- `whatsapp-service/auth_info_baileys`

Selama folder ini valid, tidak perlu scan ulang.

## 4) Jika Session Expired / Logout

1. Hentikan service (`Ctrl + C`)
2. Hapus folder session:

```bash
# dari folder whatsapp-service
rm -rf auth_info_baileys
```

Windows PowerShell:

```powershell
Remove-Item -Recurse -Force .\auth_info_baileys
```

3. Jalankan lagi `node index.js`
4. Scan QR baru

## 5) Endpoint Microservice

### POST `/send-message`
Body JSON:

```json
{
  "phone": "081234567890",
  "message": "Halo, ini notifikasi"
}
```

### POST `/send-document`
Body JSON (pilih salah satu `fileUrl` atau `filePath`):

```json
{
  "phone": "081234567890",
  "message": "Berikut dokumen Anda",
  "filePath": "C:/full/path/to/file.pdf"
}
```

Atau:

```json
{
  "phone": "081234567890",
  "message": "Berikut dokumen Anda",
  "fileUrl": "https://example.com/file.pdf"
}
```

## 6) Mapping Event yang Sudah Terpasang di Laravel

- Warga ajukan surat → notif ke RT
- RT terima → notif ke warga + notif ke RW
- RT tolak → notif ke warga
- RW terima → notif ke warga + kirim PDF bila ada
- RW tolak → notif ke warga

Implementasi utama ada di:

- `app/Services/WhatsAppService.php`
- `app/Http/Controllers/LetterRequestController.php`
