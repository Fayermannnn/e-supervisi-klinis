#!/usr/bin/env bash
#
# Sprint 11 backlog: "Uji restore drill ... Siklus penuh berhasil di
# staging" dan "Uji Disaster Recovery penuh ... RTO tercapai".
#
# SENGAJA mewajibkan --target-db eksplisit (bukan otomatis memakai
# DB_DATABASE dari .env) - restore adalah operasi destruktif (database
# target di-drop lalu dibuat ulang), dan default diam-diam ke database
# produksi adalah tepat jenis kesalahan yang paling berbahaya untuk skrip
# semacam ini. Untuk drill/DR test, arahkan ke database staging terpisah,
# BUKAN production.
#
# Penggunaan:
#   ./deploy/scripts/restore.sh --file backups/esupervisi-klinis-XXXX.sql.gz --target-db esupervisi_klinis_staging [--force]

set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$PROJECT_ROOT"

if [ -f .env ]; then
    # shellcheck disable=SC1091
    set -a && source .env && set +a
fi

: "${DB_USERNAME:?DB_USERNAME wajib diset di .env}"

COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.yml}"
DUMP_FILE=""
TARGET_DB=""
FORCE=0

while [ $# -gt 0 ]; do
    case "$1" in
        --file) DUMP_FILE="$2"; shift 2 ;;
        --target-db) TARGET_DB="$2"; shift 2 ;;
        --force) FORCE=1; shift ;;
        *) echo "Argumen tidak dikenal: $1" >&2; exit 2 ;;
    esac
done

if [ -z "$DUMP_FILE" ] || [ -z "$TARGET_DB" ]; then
    echo "Penggunaan: $0 --file <path.sql.gz> --target-db <nama_database> [--force]" >&2
    exit 2
fi

if [ "$TARGET_DB" == "${DB_DATABASE:-}" ] && [ "$FORCE" -ne 1 ]; then
    echo "[restore] TARGET_DB (${TARGET_DB}) sama dengan DB_DATABASE produksi di .env." >&2
    echo "[restore] Ini kemungkinan besar bukan yang Anda inginkan untuk drill/DR test." >&2
    echo "[restore] Tambahkan --force jika Anda benar-benar bermaksud menimpa database ini." >&2
    exit 1
fi

echo "[restore] Memverifikasi checksum sebelum restore"
"$PROJECT_ROOT/deploy/scripts/verify-backup.sh" "$DUMP_FILE"

if [ "$FORCE" -ne 1 ]; then
    read -r -p "[restore] Akan DROP dan membuat ulang database '${TARGET_DB}', lalu restore dari ${DUMP_FILE}. Lanjutkan? [y/N] " CONFIRM
    if [ "$CONFIRM" != "y" ] && [ "$CONFIRM" != "Y" ]; then
        echo "[restore] Dibatalkan."
        exit 0
    fi
fi

echo "[restore] Drop database '${TARGET_DB}' jika ada"
docker compose -f "$COMPOSE_FILE" exec -T postgres \
    psql -U "$DB_USERNAME" -d postgres -c "DROP DATABASE IF EXISTS \"${TARGET_DB}\";"

echo "[restore] Membuat database '${TARGET_DB}'"
docker compose -f "$COMPOSE_FILE" exec -T postgres \
    psql -U "$DB_USERNAME" -d postgres -c "CREATE DATABASE \"${TARGET_DB}\";"

echo "[restore] Me-restore dump ke '${TARGET_DB}'"
gunzip -c "$DUMP_FILE" | docker compose -f "$COMPOSE_FILE" exec -T postgres \
    psql -U "$DB_USERNAME" -d "$TARGET_DB"

echo "[restore] Selesai. Verifikasi jumlah baris beberapa tabel kunci sebagai sanity check:"
docker compose -f "$COMPOSE_FILE" exec -T postgres \
    psql -U "$DB_USERNAME" -d "$TARGET_DB" -c \
    "SELECT 'sekolah' AS tabel, COUNT(*) FROM sekolah
     UNION ALL SELECT 'pengguna', COUNT(*) FROM pengguna
     UNION ALL SELECT 'sesi_supervisi', COUNT(*) FROM sesi_supervisi
     UNION ALL SELECT 'audit_log', COUNT(*) FROM audit_log;"
