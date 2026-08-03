# Dokumen Folder Mapping (Enterprise-Ready)
## Sistem E-Supervisi Klinis Pendidikan — Tahap 11 SDLC

**Versi:** 2.0 — **menggantikan struktur folder awal** yang sempat dibuat sebelum Tahap 9 (Module Breakdown) selesai
**Rujukan:** Module Breakdown Tahap 9 (13 modul), Software Architecture Tahap 5, Database Design Tahap 4

> **Kenapa versi 2, bukan revisi kecil dari struktur sebelumnya?** Struktur folder pertama kami buat sebelum 13 modul di Tahap 9 teridentifikasi secara rinci, dan sudah saya tandai saat itu tidak mengikuti konvensi Laravel. Sekarang, dengan daftar modul lengkap di tangan, saya membangun ulang struktur ini **mengikuti konvensi Laravel yang benar** (`app/`, `resources/`, `routes/`, `database/`, `tests/`, `config/` di root) — sesuai catatan kritis yang saya sampaikan sebelumnya. Ini bukan sekadar mengganti nama folder; ada satu keputusan arsitektural penting yang berubah — dijelaskan di Bagian 10.

Struktur folder sudah benar-benar dibuat (bukan cuma dideskripsikan) dan akan dibagikan sebagai arsip di akhir pesan ini.

---

## 1. Struktur Folder Frontend

```
resources/
├── views/
│   ├── livewire/
│   │   ├── auth/
│   │   ├── rbac/
│   │   ├── sekolah/
│   │   ├── pengguna/
│   │   ├── instrumen/
│   │   ├── perencanaan/
│   │   ├── observasi/
│   │   ├── analisis/
│   │   ├── umpanbalik/
│   │   ├── tindaklanjut/
│   │   ├── pelaporan/
│   │   ├── notifikasi/
│   │   └── auditlog/
│   ├── layouts/
│   └── components/
├── css/
└── js/
```
Setiap subfolder di `livewire/` **namanya persis sama** dengan nama modul di `app/Modules/` (huruf kecil) — konvensi penamaan ini sengaja dijaga 1:1 agar siapa pun (termasuk Anda enam bulan dari sekarang) bisa langsung menebak lokasi file tanpa harus mencari.

## 2. Struktur Folder Backend

```
app/
├── Models/                  ← lihat catatan penting Bagian 10
├── Modules/
│   ├── Auth/
│   │   ├── Http/Controllers/
│   │   ├── Http/Requests/
│   │   └── Services/
│   ├── RBAC/            (struktur sama)
│   ├── Sekolah/         (struktur sama)
│   ├── Pengguna/        (struktur sama)
│   ├── Instrumen/       (struktur sama)
│   ├── Perencanaan/     (struktur sama)
│   ├── Observasi/       (struktur sama)
│   ├── Analisis/        (struktur sama)
│   ├── UmpanBalik/      (struktur sama)
│   ├── TindakLanjut/    (struktur sama)
│   ├── Pelaporan/       (struktur sama)
│   ├── Notifikasi/      (struktur sama)
│   └── AuditLog/        (struktur sama)
├── Http/
│   ├── Middleware/
│   └── Resources/       ← API Resource classes (Tahap 7)
├── Policies/
├── Providers/
├── Notifications/        ← kelas notifikasi Laravel (in-app/email/WA)
└── Support/              ← helper/trait lintas modul (mis. trait audit logging)
```
Tiga belas modul dari Tahap 9 **dipetakan langsung satu-satu** ke `app/Modules/` — tidak ada modul yang digabung atau dipecah tanpa alasan eksplisit dari analisis sebelumnya.

## 3. Struktur Database

```
database/
├── migrations/    ← urutan file mengikuti urutan dependency Tahap 4 (lihat Bagian 9)
├── seeders/       ← RolePermissionSeeder, InstrumenSeeder, dst.
└── factories/     ← untuk kebutuhan testing (Bagian 6)
```

## 4. Struktur Assets

```
public/
└── build/          ← output kompilasi Tailwind/Alpine (hasil `npm run build`, tidak ditulis manual)
resources/
├── css/            ← sumber Tailwind config
└── js/             ← sumber Alpine.js
storage/
└── app/private/bukti-observasi/  ← lampiran bukti observasi (Tahap 7, Bagian 9 — sebelum migrasi ke MinIO)
```
**Catatan penting:** `storage/app/private/` bukan `public/` — konsisten dengan keputusan Tahap 7 bahwa bukti observasi memakai *signed URL*, bukan URL publik permanen.

## 5. Struktur API

```
routes/
├── web.php
├── console.php
└── api/
    └── v1.php      ← seluruh route /api/v1/... (Tahap 7)
app/Http/Resources/  ← transformer response per entitas (redaksi field sensitif, Tahap 7)
docs/
└── api/             ← spesifikasi OpenAPI/Swagger (level proyek, bukan bagian aplikasi Laravel yang di-deploy)
```
`docs/api/` sengaja diletakkan di **root proyek**, bukan di dalam `app/` atau `resources/` — dokumentasi API bukan kode yang dieksekusi, jadi tidak seharusnya ikut ter-bundle saat aplikasi di-build/deploy.

## 6. Struktur Testing

```
tests/
├── Unit/                  ← pengujian murni Service/Business Rule, tanpa HTTP/DB
├── Feature/
│   ├── Auth/
│   ├── RBAC/
│   ├── Sekolah/
│   ├── Pengguna/
│   ├── Instrumen/
│   ├── Perencanaan/
│   ├── Observasi/
│   ├── Analisis/
│   ├── UmpanBalik/
│   ├── TindakLanjut/
│   ├── Pelaporan/
│   ├── Notifikasi/
│   └── AuditLog/
└── Browser/                ← Laravel Dusk, alur kritis (mis. form observasi mobile)
```
`tests/Feature/` **mengikuti struktur modul yang identik** dengan `app/Modules/` — bukan kebetulan, ini memudahkan menemukan test yang relevan saat sebuah modul diubah, dan memudahkan mengukur coverage per modul secara terpisah.

## 7. Struktur Configuration

```
config/           ← file konfigurasi Laravel (*.php), AMAN di-commit (tanpa kredensial)
env/              ← template .env.example per environment (staging, production)
```
**Catatan tegas (mengulang Tahap 5 karena ini bagian paling rawan disepelekan):** folder `env/` hanya berisi **template**, bukan file `.env` sungguhan berisi kredensial produksi. File `.env` asli tidak pernah ada di folder ini maupun di repository manapun.

## 8. Struktur Deployment

```
docker/
├── nginx/          ← konfigurasi reverse proxy + TLS
└── php/             ← Dockerfile PHP-FPM
.github/
└── workflows/       ← CI: test otomatis + deploy (Tahap 5, Tahap 10 Sprint 0)
deploy/
└── scripts/         ← skrip backup terjadwal, restore drill, migrasi produksi
```

---

## 9. Fungsi Setiap Folder (Ringkasan)

| Folder | Fungsi |
|---|---|
| `app/Models/` | Representasi tabel database (Tahap 4) — **dibagikan lintas modul**, lihat Bagian 10 |
| `app/Modules/{Nama}/` | Logika bisnis satu modul (Controller, Request validation, Service) — batas kapabilitas bisnis, bukan batas data |
| `app/Policies/` | Aturan otorisasi berbasis objek, dipakai lintas modul yang mengakses Model yang sama |
| `resources/views/livewire/{nama}/` | Tampilan UI per modul, 1:1 dengan `app/Modules/{Nama}/` |
| `database/migrations/` | Definisi skema tabel, urutan dependency eksplisit |
| `routes/api/v1.php` | Kontrak REST API versi 1 (Tahap 7) |
| `tests/Feature/{Nama}/` | Uji end-to-end per modul |
| `docs/api/` | Dokumentasi API level proyek, terpisah dari kode aplikasi |
| `env/` | Template konfigurasi environment — bukan kredensial sungguhan |
| `docker/`, `deploy/` | Segala hal untuk menjalankan & memelihara aplikasi di server |

---

## 10. Alasan Pemisahan Folder — Termasuk Satu Keputusan yang Berubah dari Tahap 5

**Prinsip utama: pemisahan berdasarkan *kapabilitas bisnis* (`app/Modules/`), bukan murni berdasarkan *jenis file teknis*.** Ini konsisten dengan Modular Monolith + Clean Architecture yang diputuskan sejak Tahap 5 — seseorang yang mengerjakan Modul RTL cukup bekerja di satu folder `app/Modules/TindakLanjut/`, tanpa harus melompat ke folder Controller-global, folder Service-global, dst.

**Satu keputusan yang saya revisi secara sadar dari Tahap 5:** dokumen Arsitektur Tahap 5 sempat menyiratkan setiap modul juga memiliki *Domain layer*-nya sendiri (termasuk Model). **Setelah Module Breakdown Tahap 9 selesai, ini terbukti tidak realistis** — entitas `SesiSupervisi` misalnya **dipakai langsung oleh lima modul berbeda** (Perencanaan membuatnya, Observasi & Analisis mengubahnya, UmpanBalik dan TindakLanjut menambahkan data ke dalamnya, Pelaporan membacanya). Memaksa `SesiSupervisi` "dimiliki" satu modul saja akan memicu *duplikasi model* atau *dependency melingkar* antar-modul — dua hal yang justru merusak tujuan modularitas itu sendiri.

**Solusinya:** `app/Models/` **disentralisasi** (satu model = satu tabel = satu lokasi, sesuai Data Dictionary Tahap 4), sementara `app/Modules/` tetap modular untuk **lapisan Controller-Request-Service** yang memang benar-benar spesifik per kapabilitas bisnis. Ini adalah pola *hybrid* yang jujur mengakui realita data relasional kita, dibanding memaksakan "kemurnian" modular yang justru akan menyulitkan implementasi nyata.

**Kesiapan enterprise/scalable:** struktur ini memberi jalur migrasi jika suatu saat satu modul (mis. Pelaporan, karena beban query-nya paling berat) perlu diekstrak menjadi layanan terpisah — batasnya sudah jelas di `app/Modules/Pelaporan/`, hanya `app/Models/` yang perlu strategi *shared database* atau duplikasi terbatas saat itu. Ini **bukan rencana untuk sekarang** (tetap modular monolith sesuai Tahap 5), tapi struktur ini tidak menutup jalan ke sana jika kebutuhan itu benar-benar muncul di masa depan.
