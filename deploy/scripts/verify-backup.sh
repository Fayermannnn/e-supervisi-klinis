#!/usr/bin/env bash
#
# Sprint 11 backlog: "verifikasi checksum" - dijalankan terpisah dari
# backup.sh supaya bisa dipakai untuk audit berkala (mis. cron mingguan
# yang memverifikasi ULANG seluruh backup tersimpan, bukan hanya saat
# backup baru dibuat) tanpa perlu membuat dump baru.
#
# Penggunaan:
#   ./deploy/scripts/verify-backup.sh <path-ke-file.sql.gz>
#   ./deploy/scripts/verify-backup.sh --all   # verifikasi seluruh backup di BACKUP_DIR

set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
BACKUP_DIR="${BACKUP_DIR:-$PROJECT_ROOT/backups}"

verify_one() {
    local dump_file="$1"
    local checksum_file="${dump_file}.sha256"

    if [ ! -f "$dump_file" ]; then
        echo "[verify] GAGAL: ${dump_file} tidak ditemukan." >&2
        return 1
    fi
    if [ ! -f "$checksum_file" ]; then
        echo "[verify] GAGAL: ${checksum_file} tidak ditemukan (backup tanpa checksum)." >&2
        return 1
    fi

    local computed expected
    computed="$(sha256sum "$dump_file" | awk '{print $1}')"
    expected="$(cat "$checksum_file")"

    if [ "$computed" == "$expected" ]; then
        echo "[verify] OK: $(basename "$dump_file")"
        return 0
    else
        echo "[verify] GAGAL: $(basename "$dump_file") - checksum tidak cocok (kemungkinan rusak/berubah)." >&2
        return 1
    fi
}

if [ "${1:-}" == "--all" ]; then
    STATUS=0
    shopt -s nullglob
    for dump_file in "$BACKUP_DIR"/esupervisi-klinis-*.sql.gz; do
        verify_one "$dump_file" || STATUS=1
    done
    exit "$STATUS"
elif [ -n "${1:-}" ]; then
    verify_one "$1"
else
    echo "Penggunaan: $0 <path-ke-file.sql.gz> | --all" >&2
    exit 2
fi
