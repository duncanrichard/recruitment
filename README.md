# Sirekrut

Sirekrut adalah aplikasi recruitment berbasis Laravel 12 dan React. Sistem mencakup pendaftaran kandidat, screening administrasi, test Zoom, MMPI, interview, review management, offering letter, laporan, serta pengelolaan role dan permission.

## Kebutuhan sistem

- PHP 8.4 beserta extension PostgreSQL, mbstring, openssl, fileinfo, curl, dan intl
- Composer 2
- Node.js 20 atau lebih baru
- PostgreSQL
- Queue worker untuk pekerjaan asynchronous
- Object storage S3-compatible apabila dokumen tidak disimpan secara lokal

Dependency yang terkunci saat ini membutuhkan PHP 8.4. Pastikan `php -v` menampilkan versi yang sesuai sebelum menjalankan Composer atau Artisan.

## Instalasi lokal

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

Atur koneksi PostgreSQL dan integrasi yang diperlukan di `.env`. Jangan memasukkan `.env`, credential Google, API key WhatsApp, atau dokumen kandidat ke Git.

Untuk development:

```bash
composer run dev
```

Perintah tersebut menjalankan server Laravel, queue worker, log viewer, dan Vite secara bersamaan.

## Konfigurasi utama

### Database

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sirekrut
DB_USERNAME=
DB_PASSWORD=
```

### Penyimpanan dokumen

Gunakan `local` untuk private local storage atau `s3` untuk MinIO/S3-compatible storage.

```dotenv
FILESYSTEM_DISK=local

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_URL=
AWS_ENDPOINT=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

Dokumen kandidat adalah data privat. Bucket production tidak boleh memberikan akses publik langsung.

### WhatsApp

```dotenv

FONNTE_DEVICE_URL=https://api.fonnte.com/device
FONNTE_CONNECT_TIMEOUT=10
FONNTE_TIMEOUT=20
```

Token Fonnte per perusahaan disimpan melalui modul Data Perusahaan. Batasi hak akses untuk melihat atau mengubah credential tersebut.

### Google Calendar

```dotenv
GOOGLE_CALENDAR_ENABLED=false
GOOGLE_CALENDAR_CREDENTIALS=storage/app/private/google-calendar.json
GOOGLE_CALENDAR_ID=primary
GOOGLE_CALENDAR_IMPERSONATE_EMAIL=
GOOGLE_CALENDAR_TIMEZONE=Asia/Jakarta
GOOGLE_CALENDAR_EVENT_DURATION_MINUTES=60
```

File credential harus berada di private storage dan tidak boleh tersedia melalui direktori `public`.

## Proses recruitment

Alur utama sistem:

1. Permintaan kandidat dibuat.
2. Pelamar dibuat dan menerima token pendaftaran.
3. Pelamar melengkapi data diri, keluarga, kesehatan, pekerjaan, dan kesiapan bekerja.
4. Administrasi diperiksa.
5. Kandidat mengikuti test Zoom dan MMPI.
6. Kandidat yang lolos dijadwalkan untuk interview.
7. Hasil interview diproses melalui Review Management.
8. Kandidat yang diterima masuk ke proses Offering Letter.

Endpoint admin menggunakan session authentication dan Spatie Permission. Endpoint publik berbasis token kandidat dilindungi rate limiting. Jangan mengandalkan penyembunyian menu frontend sebagai mekanisme otorisasi.

## Verifikasi sebelum deployment

```bash
php artisan test
php vendor/bin/pint --test
npm run build
php artisan route:list
composer audit
npm audit
```

Selain perintah tersebut, lakukan smoke test terhadap login, pendaftaran kandidat, upload dokumen, perubahan hasil test, interview, review management, offering letter, dan export laporan.

## Deployment production

Gunakan nilai berikut sebagai baseline:

```dotenv
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
QUEUE_CONNECTION=database
```

Sesudah deployment:

```bash
php artisan migrate --force
php artisan optimize
php artisan queue:restart
```

Pastikan process manager menjalankan `php artisan queue:work` dan scheduler menjalankan `php artisan schedule:run` setiap menit. Backup database serta object storage sebelum migration production.

Setelah deployment hardening, jalankan `php artisan db:seed --class=PermissionSeeder`
agar permission report, download dokumen, dan integration alert tersedia. Production
harus mengaktifkan `MALWARE_SCAN_ENABLED=true`, `MALWARE_SCAN_FAIL_CLOSED=true`,
serta menyediakan binary ClamAV sesuai `MALWARE_SCAN_BINARY`. Kegagalan permanen
Fonnte dan Google Calendar dapat diperiksa melalui endpoint
`/admin/integration-alerts` oleh role yang mempunyai permission terkait.
Riwayat perubahan recruitment dapat diperiksa melalui
`/admin/recruitment-audits`; endpoint tersebut mendukung filter, detail,
dan export CSV serta otomatis dibatasi berdasarkan perusahaan user.

## Keamanan dan pemulihan

- Jangan menghapus atau mengganti kolom production sebelum seluruh kode berhenti menggunakannya.
- Backup database dan dokumen kandidat secara terjadwal, lalu uji proses restore.
- Periksa failed jobs serta log kegagalan WhatsApp dan Calendar.
- Jangan menulis token kandidat, credential, atau data kesehatan ke log.
- Batasi export dan akses dokumen berdasarkan role serta perusahaan.
- Jalankan audit dependency secara berkala dan uji upgrade di staging.

## Struktur penting

- `routes/pendaftaran.php`: route publik kandidat
- `routes/web.php`: autentikasi dan kumpulan route admin
- `app/Http/Controllers/PendaftaranController.php`: formulir kandidat
- `app/Http/Controllers/CekTahapanPelamarController.php`: status tahapan kandidat
- `app/Models/DataRiwayatDiri.php`: entitas utama kandidat
- `resources/js/pages/admin`: antarmuka admin React
- `database/migrations`: schema dan histori perubahan database
