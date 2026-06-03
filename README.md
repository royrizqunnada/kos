# Sistem Management Kos

Aplikasi web manajemen kos: kamar, penghuni, kontrak, tagihan, pembayaran, reminder jatuh tempo, pengeluaran, dan laporan.

**Stack:** Laravel 13 · PHP 8.4 · Inertia 2 · React 19 · TypeScript · Tailwind CSS v4 · PostgreSQL 17 · Redis
**Arsitektur:** Clean Architecture + DDD (per-context `src/`) · Repository · Service · Action · SOLID.

---

## Struktur Arsitektur

```
app/
  Http/{Controllers,Requests,Middleware}   # Lapisan HTTP (Inertia)
  Providers/                               # Binding repository, gate/policy
src/                                        # Domain (PSR-4 "Src\")
  {Room,Tenant,Lease,Invoice,Payment,Expense,Reminder,Reporting,Identity}/
    Domain/{Models,Enums,Repositories,Data} # Entitas, kontrak repo, DTO
    Application/{Services,Actions}          # Use-case
    Infrastructure/{Repositories,Policies}  # Implementasi Eloquent
  Shared/                                   # Policy generik, Setting
database/{migrations,factories,seeders}
resources/js/{Pages,Components,Layouts,types}  # React + TS
routes/{web.php,console.php}                # Route Inertia + scheduler
```

Aliran: `Controller → Action/Service → RepositoryInterface → EloquentRepository → Model`.
Repository di-bind di `App\Providers\RepositoryServiceProvider`.

---

## Setup

Prasyarat: PHP 8.4, Composer, Node 20+, PostgreSQL 17, Redis.

```bash
composer install
npm install

cp .env.example .env        # sudah otomatis tersalin
php artisan key:generate

# Sesuaikan kredensial DB & Redis di .env, lalu:
php artisan migrate --seed
php artisan storage:link    # agar foto kamar/KTP/bukti transfer bisa diakses

# Jalankan (dua terminal) atau gunakan composer dev:
npm run dev
php artisan serve
# atau sekaligus:
composer dev
```

Buka `http://localhost:8000`.

### Akun Demo (password: `password`)
| Role        | Email                 | Akses                          |
|-------------|-----------------------|--------------------------------|
| Super Admin | superadmin@kos.test   | Penuh                          |
| Pemilik     | owner@kos.test        | Lihat semua data + laporan     |
| Admin       | admin@kos.test        | Operasional harian (tanpa setting) |

---

## Fitur

- **Dashboard** — total/terisi/kosong/maintenance kamar, penghuni aktif, tagihan belum dibayar, pendapatan bulan & tahun berjalan (grafik via Deferred Props).
- **Kamar** — nomor, tipe, harga, status (Kosong/Terisi/Maintenance), fasilitas, foto.
- **Penghuni** — nama, NIK, HP, alamat, foto KTP, kontak darurat.
- **Kontrak** — durasi Bulanan/3/6 Bulanan/Tahunan; otomatis menandai kamar terisi & membuat tagihan pertama.
- **Tagihan** — generate otomatis (manual & terjadwal), nomor invoice berurutan, status Draft/Belum Dibayar/Sebagian/Lunas/Telat.
- **Pembayaran** — input manual, bukti transfer, riwayat, status invoice otomatis terhitung ulang (transaksi + lock).
- **Reminder** — H-7, H-3, H-1, H+1, H+7 via WhatsApp & Email; template dapat diubah admin (idempoten via `reminder_logs`).
- **Pengeluaran** — Listrik/Air/Internet/Perbaikan/Kebersihan/Lain-lain.
- **Laporan** — pendapatan, pengeluaran, laba, piutang, kamar kosong, penghuni aktif + filter periode.

---

## Scheduler (Reminder & Generate Tagihan)

Definisi jadwal ada di `routes/console.php`:
- `generate-monthly-invoices` — tiap awal bulan (`KOS_BILLING_DAY`).
- `send-invoice-reminders` — tiap hari 08:00.

Tambahkan cron di server:
```
* * * * * cd /path-ke-project && php artisan schedule:run >> /dev/null 2>&1
```

Uji manual:
```bash
php artisan tinker
>>> app(\Src\Invoice\Application\Actions\GenerateDueInvoicesAction::class)->execute();
>>> app(\Src\Reminder\Application\Actions\SendInvoiceRemindersAction::class)->execute();
```

---

## WhatsApp

Driver diatur via `WHATSAPP_DRIVER` di `.env`:
- `log` (default dev) — pesan ditulis ke log.
- `fonnte` — isi `WHATSAPP_TOKEN`. Implementasi di `src/Reminder/Application/Services/WhatsApp/`. Untuk gateway lain, buat class baru yang meng-implement `WhatsAppDriver` lalu daftarkan di `RepositoryServiceProvider`.

---

## Testing (Pest)

Tes memakai fitur khusus PostgreSQL (jsonb, ilike), jadi siapkan DB tes:
```bash
createdb kos_test
php artisan test
```

---

## Catatan Versi

- Inertia adapter di-pin `^2.0` / `@inertiajs/react ^2.0` (Deferred Props tersedia di v2). Jika v3 sudah rilis, naikkan versi di `composer.json` & `package.json`.
- Config bawaan Laravel (cache/session/queue/mail/filesystems/logging) memakai default framework; publish bila perlu kustomisasi: `php artisan config:publish`.
- Config Spatie Permission memakai default package; publish bila perlu: `php artisan vendor:publish --tag=permission-config`.
