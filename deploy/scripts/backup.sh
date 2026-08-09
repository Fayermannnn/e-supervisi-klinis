#!/usr/bin/env bash
#
# Sprint 11 backlog (Infrastruktur/Produksi): "Skrip backup + verifikasi
# checksum ... Backup harian tervalidasi".
#
# Membuat dump PostgreSQL terkompresi, menghitung checksum SHA-256-nya, dan
# merotasi backup lama. Dijalankan lewat cron di host produksi (bukan di
# dalam container) - lihat contoh crontab di bagian bawah file ini.
#
# Bagian dari strategi 3-2-1 (SDD Bagian I.17): skrip ini menghasilkan SATU
# salinan lokal terverifikasi. Menyalin BACKUP_DIR ke media/lokasi kedua
# (mis. rsync ke object storage eksternal, bukan MinIO yang sama dengan
# database produksi) adalah tanggung jawab operasional terpisah - sengaja
# TIDAK dihardcode di sini karena tujuan penyimpanan lepas-situs (offsite)
# bergantung pada infrastruktur nyata yang belum ada (domain/hosting
# produksi masih PENDING, lihat SDD Lampiran A #6/#7).
#
# Environment yang dibutuhkan (baca dari .env produksi, tidak dari argumen):
#   DB_DATABASE, DB_USERNAME
#   COMPOSE_FILE (opsional, default docker-compose.yml)
#   BACKUP_DIR (opsional, default ./backups)
#   BACKUP_RETENTION_DAYS (opsional, default 14)
#
# Penggunaan:
#   ./deploy/scripts/backup.sh

set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$PROJECT_ROOT"

if [ -f .env ]; then
    # shellcheck disable=SC1091
    set -a && source .env && set +a
fi

: "${DB_DATABASE:?DB_DATABASE wajib diset di .env}"
: "${DB_USERNAME:?DB_USERNAME wajib diset di .env}"

COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.yml}"
BACKUP_DIR="${BACKUP_DIR:-$PROJECT_ROOT/backups}"
BACKUP_RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-14}"

mkdir -p "$BACKUP_DIR"

TIMESTAMP="$(date +%Y%m%d-%H%M%S)"
DUMP_FILE="$BACKUP_DIR/esupervisi-klinis-${TIMESTAMP}.sql.gz"
CHECKSUM_FILE="${DUMP_FILE}.sha256"

echo "[backup] Membuat dump ${DB_DATABASE} -> ${DUMP_FILE}"
docker compose -f "$COMPOSE_FILE" exec -T postgres \
    pg_dump -U "$DB_USERNAME" -d "$DB_DATABASE" --no-owner --no-privileges \
    | gzip -9 > "$DUMP_FILE"

if [ ! -s "$DUMP_FILE" ]; then
    echo "[backup] GAGAL: file dump kosong, backup dibatalkan." >&2
    rm -f "$DUMP_FILE"
    exit 1
fi

echo "[backup] Menghitung checksum SHA-256"
sha256sum "$DUMP_FILE" | awk '{print $1}' > "$CHECKSUM_FILE"

echo "[backup] Memverifikasi checksum yang baru ditulis"
COMPUTED="$(sha256sum "$DUMP_FILE" | awk '{print $1}')"
EXPECTED="$(cat "$CHECKSUM_FILE")"
if [ "$COMPUTED" != "$EXPECTED" ]; then
    echo "[backup] GAGAL: checksum tidak cocok segera setelah backup dibuat." >&2
    exit 1
fi

echo "[backup] Merotasi backup lebih tua dari ${BACKUP_RETENTION_DAYS} hari"
find "$BACKUP_DIR" -name 'esupervisi-klinis-*.sql.gz' -mtime "+${BACKUP_RETENTION_DAYS}" -print -delete
find "$BACKUP_DIR" -name 'esupervisi-klinis-*.sql.gz.sha256' -mtime "+${BACKUP_RETENTION_DAYS}" -print -delete

echo "[backup] Selesai: ${DUMP_FILE} (checksum ${COMPUTED})"

# Contoh crontab (jalankan sebagai user yang punya akses ke docker compose):
# 0 2 * * * cd /path/ke/proyek && ./deploy/scripts/backup.sh >> /var/log/esupervisi-backup.log 2>&1
