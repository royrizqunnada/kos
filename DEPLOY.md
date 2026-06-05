# Deploy ke Ploi

Panduan deploy **Sistem Management Kos** (Laravel 13 · Inertia 3 · React · PostgreSQL · Redis) menggunakan **Ploi**.

> Ploi men-deploy dengan menarik kode dari GitHub lalu menjalankan **Deploy Script**.
> Pastikan branch berisi `composer.lock` & `package-lock.json` sudah ter-push ke GitHub.

---

## 0. Prasyarat server (cek di Ploi)

| Komponen | Keterangan |
|----------|------------|
| **PHP 8.4** | Wajib, dengan ekstensi **`bcmath`**, `pdo_pgsql`, `redis`, `mbstring`, `intl`. Cek di Ploi → *Server → PHP → Extensions*. `bcmath` dipakai untuk perhitungan tagihan. |
| **PostgreSQL** | Buat database + user di Ploi → *Server → Databases*. (PG 14+ cukup; fitur `jsonb`/`ilike` tersedia.) |
| **Redis** | Untuk cache, session, queue. Install via Ploi → *Server → Services* bila belum ada. |
| **Node 20+** | Untuk build aset frontend (`npm run build`). Install via Ploi → *Server → Node*. |

---

## 1. Site & repository

1. Ploi → *Sites* → buat site dengan domain Anda, **web directory** = `/public`.
2. *Repository* → hubungkan ke `royrizqunnada/kos`, pilih **branch** yang akan dideploy
   (disarankan **`main`** setelah branch fitur di-merge — lihat §6).
3. Aktifkan **Quick Deploy** bila ingin auto-deploy setiap push.

---

## 2. Environment (.env)

Ploi → site → *Environment*. Salin dari **`.env.production.example`** di repo ini dan isi nilainya
(DB, Redis, Mail, WhatsApp, `APP_URL`). Lalu generate kunci aplikasi sekali:

```bash
php artisan key:generate --force
```

Pastikan: `APP_ENV=production`, `APP_DEBUG=false`.

---

## 3. Deploy Script

Ploi → site → *Deploy Script*. Ganti isinya dengan:

```bash
cd {SITE_DIRECTORY}

git pull origin {BRANCH}

# Dependensi PHP (mode produksi)
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Build aset frontend (Inertia + React)
npm ci
npm run build

# Migrasi database
php artisan migrate --force

# Symlink storage (idempoten) — agar foto kamar/KTP/bukti transfer terakses
[ -L public/storage ] || php artisan storage:link

# Optimalkan & cache konfigurasi
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart worker queue agar memuat kode terbaru
php artisan queue:restart

# Reload PHP-FPM
echo "" | sudo -S service php8.4-fpm reload
```

> Ganti `{BRANCH}` dengan branch yang dipilih (mis. `main`). `{SITE_DIRECTORY}`
> adalah placeholder bawaan Ploi — biarkan apa adanya.

---

## 4. Queue Worker

Reminder & pekerjaan latar dikirim via Redis queue. Ploi → site → *Queue* (atau *Daemons*) → tambah worker:

- **Command:** `php8.4 artisan queue:work redis --sleep=3 --tries=3 --max-time=3600`
- **Directory:** direktori site
- **Processes:** `1` (naikkan bila perlu)

---

## 5. Scheduler (cron)

Definisi jadwal ada di `routes/console.php`:
- `generate-monthly-invoices` — tiap `KOS_BILLING_DAY` pukul 02:00.
- `send-invoice-reminders` — tiap hari 08:00.

Ploi → site → *Cron Jobs* → **Add Laravel Scheduler** (one-click), atau tambah manual:

```
* * * * * cd {SITE_DIRECTORY} && php8.4 artisan schedule:run >> /dev/null 2>&1
```

---

## 6. Branch produksi

Pekerjaan ada di `claude/clever-hopper-kPkqk`. Untuk produksi, **merge ke `main`** lalu
arahkan Ploi ke `main` (lebih rapi daripada deploy branch fitur):

```bash
git checkout main
git merge --no-ff claude/clever-hopper-kPkqk
git push origin main
```

---

## 7. Deploy pertama

1. Pastikan §0–§5 sudah dikonfigurasi.
2. Ploi → site → **Deploy now**.
3. Setelah sukses, seed data awal **sekali** (role/permission + akun demo) lewat Ploi *Commands*:
   ```bash
   php artisan db:seed --force
   ```
4. Buka domain → login dengan akun dari `README.md`.
5. **Ganti password akun demo** & nonaktifkan yang tak terpakai.

---

## 8. HTTPS

Ploi → site → *SSL* → **Let's Encrypt** → Activate. Aktifkan juga *Force HTTPS*.
