#!/usr/bin/env bash

set -Eeuo pipefail
umask 077

readonly BACKUP_DIR="/home/smas/ops/database-backup"
readonly DB_CNF="${BACKUP_DIR}/.db.cnf"
readonly BACKUP_CONFIG="${BACKUP_DIR}/backup-db.conf"
readonly NEXTCLOUD_NETRC="${BACKUP_DIR}/.nextcloud.netrc"
readonly LOCK_FILE="${BACKUP_DIR}/.backup-db.lock"

log() {
    printf '%s %s\n' "$(TZ=Asia/Jakarta date '+%Y-%m-%d %H:%M:%S %Z')" "$*"
}

fail() {
    log "ERROR: $*" >&2
    exit 1
}

for required_command in mariadb-dump gzip sha256sum curl flock; do
    command -v "$required_command" >/dev/null 2>&1 \
        || fail "Program tidak ditemukan: $required_command"
done

[[ -r "$DB_CNF" ]] || fail "File tidak dapat dibaca: $DB_CNF"
[[ -r "$BACKUP_CONFIG" ]] || fail "File tidak dapat dibaca: $BACKUP_CONFIG"
[[ -r "$NEXTCLOUD_NETRC" ]] || fail "File tidak dapat dibaca: $NEXTCLOUD_NETRC"

# shellcheck source=/dev/null
source "$BACKUP_CONFIG"

: "${DB_NAME:?DB_NAME belum diatur}"
: "${NEXTCLOUD_DESTINATION_URL:?NEXTCLOUD_DESTINATION_URL belum diatur}"
: "${LOCAL_RETENTION_DAYS:?LOCAL_RETENTION_DAYS belum diatur}"
: "${REMOTE_RETENTION_DAYS:?REMOTE_RETENTION_DAYS belum diatur}"

[[ "$DB_NAME" =~ ^[A-Za-z0-9_]+$ ]] || fail "DB_NAME tidak valid"
[[ "$NEXTCLOUD_DESTINATION_URL" == https://* ]] || fail "URL Nextcloud wajib HTTPS"
[[ "$LOCAL_RETENTION_DAYS" =~ ^[0-9]+$ ]] || fail "LOCAL_RETENTION_DAYS harus angka"
[[ "$REMOTE_RETENTION_DAYS" =~ ^[0-9]+$ ]] || fail "REMOTE_RETENTION_DAYS harus angka"

exec 9>"$LOCK_FILE"
flock -n 9 || fail "Backup sebelumnya masih berjalan"

backup_date="$(TZ=Asia/Jakarta date +%Y%m%d)"
backup_name="${backup_date}_backup_db_${DB_NAME}.sql.gz"
checksum_name="${backup_name}.sha256"
backup_path="${BACKUP_DIR}/${backup_name}"
checksum_path="${BACKUP_DIR}/${checksum_name}"
temporary_path="$(mktemp "${BACKUP_DIR}/.${backup_name}.XXXXXX")"

cleanup() {
    rm -f -- "$temporary_path"
}
trap cleanup EXIT

log "Mulai backup ${DB_NAME}"
set -o pipefail
mariadb-dump \
    --defaults-extra-file="$DB_CNF" \
    --single-transaction \
    --quick \
    --routines \
    --events \
    --triggers \
    --hex-blob \
    --default-character-set=utf8mb4 \
    "$DB_NAME" \
    | gzip -1 > "$temporary_path"

gzip -t "$temporary_path"
mv -f -- "$temporary_path" "$backup_path"
(
    cd "$BACKUP_DIR"
    sha256sum "$backup_name" > "$checksum_name"
)
chmod 0600 "$backup_path" "$checksum_path"
log "Backup lokal selesai: $backup_name"

destination_url="${NEXTCLOUD_DESTINATION_URL%/}"
propfind_code="$(curl \
    --silent \
    --show-error \
    --output /dev/null \
    --write-out '%{http_code}' \
    --netrc-file "$NEXTCLOUD_NETRC" \
    --connect-timeout 30 \
    --max-time 120 \
    --request PROPFIND \
    --header 'Depth: 0' \
    "$destination_url")"
[[ "$propfind_code" == "207" ]] \
    || fail "Folder Nextcloud tidak dapat diakses; HTTP $propfind_code"

upload_file() {
    local local_path="$1"
    local remote_name="$2"
    local upload_code

    upload_code="$(curl \
        --silent \
        --show-error \
        --output /dev/null \
        --write-out '%{http_code}' \
        --netrc-file "$NEXTCLOUD_NETRC" \
        --connect-timeout 30 \
        --max-time 1800 \
        --retry 3 \
        --retry-delay 10 \
        --upload-file "$local_path" \
        "${destination_url}/${remote_name}")"

    [[ "$upload_code" == "201" || "$upload_code" == "204" ]] \
        || fail "Upload $remote_name gagal; HTTP $upload_code"
}

upload_file "$backup_path" "$backup_name"
upload_file "$checksum_path" "$checksum_name"
log "Upload Nextcloud selesai: $backup_name"

expired_date="$(TZ=Asia/Jakarta date -d "${REMOTE_RETENTION_DAYS} days ago" +%Y%m%d)"
for expired_extension in ".sql.gz" ".sql.gz.sha256"; do
    expired_name="${expired_date}_backup_db_${DB_NAME}${expired_extension}"
    delete_code="$(curl \
        --silent \
        --show-error \
        --output /dev/null \
        --write-out '%{http_code}' \
        --netrc-file "$NEXTCLOUD_NETRC" \
        --connect-timeout 30 \
        --max-time 120 \
        --request DELETE \
        "${destination_url}/${expired_name}")"
    [[ "$delete_code" == "204" || "$delete_code" == "404" ]] \
        || fail "Penghapusan backup lama $expired_name gagal; HTTP $delete_code"
done

find "$BACKUP_DIR" -maxdepth 1 -type f \
    \( -name "????????_backup_db_${DB_NAME}.sql.gz" \
       -o -name "????????_backup_db_${DB_NAME}.sql.gz.sha256" \) \
    -mtime "+${LOCAL_RETENTION_DAYS}" -delete

log "Backup selesai"
