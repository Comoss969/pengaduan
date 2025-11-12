#!/bin/bash
# Contoh Script untuk Setup Cron Job Auto Purge
# 
# Cara menggunakan:
# 1. Edit path sesuai dengan lokasi instalasi
# 2. Jalankan: chmod +x cron_setup_example.sh
# 3. Jalankan: ./cron_setup_example.sh
# 4. Atau copy command ke crontab manual

# Path ke PHP CLI (sesuaikan dengan instalasi Anda)
PHP_PATH="/usr/bin/php"
# Path ke script auto_purge.php (sesuaikan dengan lokasi file)
SCRIPT_PATH="/path/to/pengaduan/auto_purge.php"
# Path ke log file
LOG_PATH="/path/to/pengaduan/logs/auto_purge_cron.log"

# Buat folder logs jika belum ada
mkdir -p "$(dirname "$LOG_PATH")"

# Tambahkan ke crontab (jalankan setiap hari pukul 02:00)
# Uncomment baris di bawah untuk menambahkan ke crontab
# (crontab -l 2>/dev/null; echo "0 2 * * * $PHP_PATH $SCRIPT_PATH >> $LOG_PATH 2>&1") | crontab -

echo "Cron job setup completed!"
echo "Cron job akan menjalankan auto_purge.php setiap hari pukul 02:00"
echo ""
echo "Untuk menambahkan manual, jalankan:"
echo "crontab -e"
echo ""
echo "Kemudian tambahkan baris berikut:"
echo "0 2 * * * $PHP_PATH $SCRIPT_PATH >> $LOG_PATH 2>&1"

