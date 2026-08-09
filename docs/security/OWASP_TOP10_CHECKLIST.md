# Checklist OWASP Top 10 (2021)
## Sistem E-Supervisi Klinis Pendidikan — Sprint 11 (QA & Hardening)

**Tanggal:** 2026-08-09
**Metode:** Code review manual terhadap `app/`, `routes/`, `config/`, `resources/views/` — bukan pemindaian otomatis (tidak ada layanan scanning eksternal tersedia di lingkungan ini). Setiap temuan menyertakan file/baris konkret, bukan asumsi umum.
**Status:** ✅ Aman/tertangani — 🟡 Sebagian, ada catatan — 🔴 Gap, perlu tindakan sebelum pilot deployment (Sprint 12)

---

### A01:2021 — Broken Access Control — ✅ Aman

- RBAC granular (spatie/laravel-permission) + Policy per objek untuk setiap dari 10 model yang butuh otorisasi (`app/Policies/*.php`), defense-in-depth sesuai SDD Bagian I.13.
- Middleware `RequirePermission` (gerbang modul) + Policy (scoping per objek) dua lapis konsisten di seluruh 14 modul.
- BR-05 ("akses data individual dibatasi guru/supervisor terkait/kepsek/Admin Dinas") diverifikasi eksplisit lewat test Sprint 11 ini untuk `SesiSupervisi` dan `UmpanBalik` — termasuk kasus negatif kepala sekolah lintas sekolah (`SesiSupervisiControllerTest::test_kepala_sekolah_can_view_sesi_di_sekolahnya_tapi_tidak_sekolah_lain`, `UmpanBalikControllerTest::test_kepala_sekolah_bisa_melihat_umpan_balik_sekolahnya_tapi_tidak_sekolah_lain`).
- `audit-log.manage` (Sprint 10) adalah contoh permission yang sengaja dikecualikan dari admin_dinas — dibuktikan `RoleAndPermissionSeederTest::test_hanya_super_admin_bisa_reach_audit_log`.
- IDOR: seluruh route memakai UUID sebagai identifier (bukan ID auto-increment berurutan), dan setiap `show`/`view` melewati Policy sebelum data dikembalikan — tidak ditemukan endpoint yang mengembalikan data berdasarkan ID tanpa Policy check.

**Catatan (bukan gap, hasil temuan Sprint 11 yang sudah ditindaklanjuti):** BR-05's separuh "dengan log" (mencatat SETIAP akses individual, bukan hanya penolakan) belum diimplementasikan untuk kasus umum — lihat A09.

---

### A02:2021 — Cryptographic Failures — 🟡 Sebagian

- ✅ Password di-hash bcrypt (`'password' => 'hashed'` cast, `app/Models/Pengguna.php`).
- ✅ `two_factor_secret` dan `two_factor_recovery_codes` dienkripsi Laravel `encrypted`/`encrypted:array` cast at-rest (`app/Models/Pengguna.php:43-44`).
- ✅ Field sensitif (`password`, `two_factor_secret`, `two_factor_recovery_codes`) di-`$hidden` dari serialisasi JSON.
- 🔴 **Belum ada TLS** — `docker-compose.yml` (produksi) hanya expose HTTP polos di nginx, tidak ada certbot/Let's Encrypt (ini justru item Sprint 11 backlog sendiri "Reverse proxy Nginx + Let's Encrypt SSL", **diblokir domain publik yang masih PENDING #7 di SDD Lampiran A** — tidak bisa diselesaikan tanpa domain nyata).
- 🟡 `APP_DEBUG=true` di `.env`/`env/.env.example` — benar untuk lokal, **wajib `false` di produksi** (checklist pra-deploy Sprint 12, belum ada file `.env.production` terpisah untuk memastikan ini).

---

### A03:2021 — Injection — ✅ Aman

- Seluruh query database lewat Eloquent Query Builder (parameterized secara otomatis) — `grep` untuk `DB::statement|DB::raw|whereRaw|selectRaw` di `app/` menghasilkan **nol match**. Satu-satunya `DB::statement` di seluruh proyek ada di migration DDL (CHECK constraint, trigger append-only) dengan literal string tetap, bukan input pengguna.
- Blade auto-escape (`{{ }}`) dipakai konsisten. Hanya satu penggunaan `{!! !!}` di seluruh `resources/views/`: `setup-two-factor.blade.php:33` (`$qrCodeSvg`) — **diverifikasi aman**, SVG dihasilkan server-side oleh `bacon/bacon-qr-code` dari secret 2FA yang di-generate sistem (bukan input bebas pengguna), sesuai larangan eksplisit Contributing Guide ("jangan nonaktifkan Blade auto-escape tanpa verifikasi eksplisit").
- Seluruh input request tervalidasi lewat Form Request class (`app/Modules/*/Http/Requests/`), bukan `request()->all()` langsung ke Model.

---

### A04:2021 — Insecure Design — ✅ Ditutup sesi ini

- ✅ Seluruh Business Rule (BR-01 s.d. BR-10) ditegakkan di Service layer, bukan Controller/Livewire (Contributing Guide Bagian 4, dipatuhi konsisten selama 11 sprint).
- ✅ Redaksi BR-08 eksplisit (`"redacted": true`), bukan penyembunyian diam-diam — mencegah asumsi salah developer di masa depan bahwa field kosong berarti data memang tidak ada.
- ✅ **[Sprint 11] Rate limiting umum ditambahkan.** Sebelumnya hanya `LoginRateLimiter` kustom (5 percobaan/15 menit, Sprint 1) yang membatasi laju - endpoint API lain (`sesi-supervisi`, `laporan`, dst.) tidak dibatasi sama sekali, risiko *resource exhaustion* (OWASP API4:2023) terutama pada `LaporanAgregatQuery` (query berat) dan job WhatsApp/email (bisa dipicu berulang lewat drill-down BR-08). Ditutup dengan `throttle:60,1` pada grup `auth:sanctum` di `routes/api/v1.php`.

---

### A05:2021 — Security Misconfiguration — 🟡 Sebagian (header ditutup, TLS/HSTS masih diblokir domain)

- ✅ `composer audit` dan `npm audit` bersih (diverifikasi ulang setiap dependency baru ditambahkan sepanjang Sprint 9-11: predis/predis, chart.js, laravel/dusk — nol kerentanan tiap kali).
- ✅ `.env` tidak pernah masuk git (`.gitignore` dikonfirmasi), `env/` hanya berisi template.
- ✅ **[Sprint 11] Header keamanan HTTP yang tidak bergantung TLS ditambahkan** (`docker/nginx/default.conf`): `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: strict-origin-when-cross-origin`.
- 🔴 **`Strict-Transport-Security` (HSTS) SENGAJA belum ditambahkan** — mengaktifkan HSTS pada origin yang baru melayani HTTP polos (belum ada domain publik/Let's Encrypt, SDD Lampiran A #7 masih PENDING) berisiko mengunci browser dari mengakses situs sampai TLS benar-benar aktif. Tambahkan bersamaan dengan konfigurasi TLS, bukan mendahuluinya.
- 🟡 `config/cors.php` **tidak ada** (belum pernah di-publish) — aplikasi berjalan dengan default framework. Untuk MVP saat ini (server-rendered Blade+Livewire, bukan SPA terpisah) risikonya rendah, tapi begitu ada domain publik (Sprint 12) sebaiknya di-publish dan dibatasi eksplisit ke origin yang benar-benar dipakai.

---

### A06:2021 — Vulnerable and Outdated Components — ✅ Aman

- `composer audit`: nol kerentanan (diverifikasi ulang saat ini, Sprint 11).
- `npm audit` (root package.json): nol kerentanan.
- Semua dependency baru sepanjang Sprint 9-11 (predis/predis, chart.js, laravel/dusk, php-webdriver/webdriver, selenium image) ditambahkan lewat `composer require`/`npm install` resmi, bukan disalin manual — versi & checksum terverifikasi oleh package manager.

---

### A07:2021 — Identification and Authentication Failures — ✅ Aman

- 2FA wajib untuk `admin_dinas`/`super_admin` (Sprint 2, `EnsureTwoFactorIsConfigured` middleware pada grup `web`).
- Rate limiting login (5 percobaan/15 menit, `LoginRateLimiter`).
- Sanctum token-based API auth, session database-backed untuk web.
- Tidak ditemukan credential hardcode di manapun (`SuperAdminSeeder` men-generate password acak yang hanya dicetak ke terminal saat seeding, tidak pernah ditulis ke kode).

---

### A08:2021 — Software and Data Integrity Failures — ✅ Aman untuk cakupan MVP

- `audit_log` append-only ditegakkan di level DB (trigger PL/pgSQL `BEFORE UPDATE/DELETE` + `REVOKE UPDATE, DELETE ... FROM PUBLIC`, Sprint 2) — bukan sekadar konvensi aplikasi yang bisa dilewati.
- Tidak ada auto-update/pulling kode dari sumber eksternal saat runtime.
- CI (`.github/workflows/`) menjalankan test sebelum merge — mencegah kode yang gagal test masuk ke `staging`/`main` (meski belum ada gate untuk PR nyata di proyek ini karena workflow branch merge belum dilakukan, lihat handoff sesi sebelumnya).

---

### A09:2021 — Security Logging and Monitoring Failures — 🟡 Sebagian

- ✅ Setiap respons 403 tercatat ke `audit_log` (`AksesLogging` middleware, Sprint 2) dengan `correlation_id` yang konsisten lintas request (`CorrelationId` middleware).
- ✅ BR-08 drill-down (Sprint 9/11) mencatat akses individual berjustifikasi secara eksplisit.
- 🔴 **BR-05 ("dengan log") belum mencatat akses individual yang BERHASIL secara umum** — hanya penolakan (403) dan drill-down BR-08 yang tercatat. Seorang guru/supervisor/kepsek yang membuka `UmpanBalikPolicy::view()`/`SesiSupervisiPolicy::view()` secara sah tidak meninggalkan jejak audit. Ini ditemukan saat menulis test BR-05 Sprint 11 (lihat commit ini) — **bukan bug baru, melapisan implementasi BR-05 yang memang belum lengkap sejak Sprint 3/7**. Menutup gap ini butuh keputusan desain (log setiap view individual akan membanjiri `audit_log` untuk aktivitas rutin guru/supervisor harian) — direkomendasikan dibahas sebagai item backlog terpisah, bukan diputuskan sepihak di sini.
- Tidak ada agregasi/alerting terpusat (Sentry, log aggregation) — wajar untuk skala MVP kabupaten, tapi layak dicatat sebagai item Sprint 12+ jika volume produksi signifikan.

---

### A10:2021 — Server-Side Request Forgery (SSRF) — ✅ Risiko rendah

- Satu-satunya panggilan HTTP keluar di seluruh `app/` adalah `WhatsAppService::kirim()` (Sprint 10) ke `config('services.fonnte.url')` — **URL tetap dari konfigurasi**, tidak pernah dibentuk dari input pengguna. Tidak ada endpoint yang menerima URL dari klien lalu melakukan fetch ke sana.

---

## Ringkasan

| Kategori | Status |
|---|---|
| A01 Broken Access Control | ✅ |
| A02 Cryptographic Failures | 🟡 (TLS diblokir domain PENDING) |
| A03 Injection | ✅ |
| A04 Insecure Design | ✅ (rate limiting ditambahkan sesi ini) |
| A05 Security Misconfiguration | 🟡 (header ditambahkan; HSTS/CORS menunggu domain) |
| A06 Vulnerable Components | ✅ |
| A07 Auth Failures | ✅ |
| A08 Data Integrity | ✅ |
| A09 Logging/Monitoring | 🟡 (BR-05 log akses individual) |
| A10 SSRF | ✅ |

**Tidak ada celah kritis** (sesuai DoD Sprint 11: "Tidak ada celah kritis") — seluruh temuan 🔴 di atas bersifat *hardening* untuk kesiapan produksi (Sprint 12), bukan kerentanan yang bisa dieksploitasi pada environment MVP/lokal saat ini. Dua dari tiga temuan yang tidak diblokir dependency eksternal **sudah ditutup di sesi ini** (rate limiting A04, header keamanan A05). Sisa item:

1. TLS/Let's Encrypt + HSTS (A02/A05) — diblokir domain publik (SDD PENDING #7), tindak lanjut Anda.
2. BR-05 logging akses individual (A09) — sengaja **tidak** ditutup di sesi ini, perlu keputusan desain (cakupan/volume log) dari Anda, bukan asumsi sepihak.
3. `config/cors.php` eksplisit (A05) — layak dikerjakan begitu domain produksi diketahui, supaya origin yang di-allow sudah benar sejak awal.
