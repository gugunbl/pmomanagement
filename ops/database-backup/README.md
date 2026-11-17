# Backup database dengan cron

Skrip menghasilkan file dengan pola `YYYYMMDD_backup_db_NAMADATABASE.sql.gz`
di `/home/smas/ops/database-backup`, memvalidasi hasilnya, lalu mengunggah file
beserta checksum SHA-256 ke Nextcloud.

## Instalasi di Production2

Salin seluruh file ke `/home/smas/ops/database-backup`, kemudian:

```bash
cd /home/smas/ops/database-backup
cp backup-db.conf.example backup-db.conf
cp db.cnf.example .db.cnf
cp nextcloud.netrc.example .nextcloud.netrc
chmod 700 backup-db.sh
chmod 600 backup-db.conf .db.cnf .nextcloud.netrc
```

Edit ketiga file konfigurasi tanpa membagikan password ke chat:

```bash
nano backup-db.conf
nano .db.cnf
nano .nextcloud.netrc
```

Hasil backup diunggah ke folder `Documents` pada root akun Nextcloud. Gunakan app
password khusus Nextcloud, bukan password login utama.

Tes manual:

```bash
bash -n /home/smas/ops/database-backup/backup-db.sh
/home/smas/ops/database-backup/backup-db.sh
```

Jika server menggunakan UTC, pukul 02.00 WIB adalah pukul 19.00 UTC pada hari
sebelumnya. Tambahkan ke `crontab -e` milik user `smas`:

```cron
0 19 * * * /home/smas/ops/database-backup/backup-db.sh >> /home/smas/ops/database-backup/backup-db.log 2>&1
```

Periksa jadwal dan log:

```bash
crontab -l
tail -n 100 /home/smas/ops/database-backup/backup-db.log
```
