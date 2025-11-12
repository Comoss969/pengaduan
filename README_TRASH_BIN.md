# 📋 Dokumentasi Fitur Trash Bin (Soft Delete + Auto Purge)

## 🎯 Overview

Fitur Trash Bin memungkinkan sistem untuk:
- **Soft Delete**: Postingan tidak benar-benar dihapus dari database, hanya ditandai sebagai terhapus
- **Trash Management**: Admin dapat melihat dan mengelola postingan yang dihapus
- **Auto Purge**: Postingan yang sudah 30 hari di trash akan otomatis terhapus permanen
- **Manual Purge**: Admin dapat menghapus permanen secara manual tanpa menunggu 30 hari

## 📁 Struktur File

### 1. Migration Scripts
- `migration_add_soft_delete.sql` - SQL script untuk menambahkan kolom `deleted_at`
- `migration_add_soft_delete.php` - PHP script untuk menjalankan migration (via web)

### 2. Core Files
- `admin_trash.php` - Halaman admin untuk melihat dan mengelola postingan terhapus
- `auto_purge.php` - Script untuk menghapus permanen postingan yang sudah 30 hari

### 3. Updated Files
- `user_dashboard.php` - Update untuk soft delete saat user menghapus postingan
- `admin_dashboard.php` - Update untuk soft delete dan filter postingan aktif
- `includes/header.php` - Tambahan link ke halaman Trash

## 🗄️ Struktur Database

### Kolom Baru di Tabel `posts`

```sql
ALTER TABLE posts 
ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL 
AFTER profanity_count;

CREATE INDEX idx_posts_deleted_at ON posts(deleted_at);
```

**Penjelasan:**
- `deleted_at IS NULL` = Postingan masih aktif (ditampilkan)
- `deleted_at IS NOT NULL` = Postingan sudah dihapus (soft delete)
- Index membantu mempercepat query untuk mencari postingan terhapus

## 🚀 Instalasi

### Step 1: Jalankan Migration

**Opsi A: Via Web Browser (Recommended)**
```
http://localhost/pengaduan/migration_add_soft_delete.php
```

**Opsi B: Via SQL (phpMyAdmin)**
```sql
-- Buka phpMyAdmin, pilih database pengaduan, jalankan:
USE pengaduan;
ALTER TABLE posts ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER profanity_count;
CREATE INDEX idx_posts_deleted_at ON posts(deleted_at);
```

### Step 2: Verifikasi Migration

Cek apakah kolom `deleted_at` sudah ditambahkan:
```sql
SHOW COLUMNS FROM posts LIKE 'deleted_at';
```

### Step 3: Setup Auto Purge (Cron Job)

#### Linux (crontab)
```bash
# Edit crontab
crontab -e

# Tambahkan baris berikut (jalankan setiap hari pukul 02:00)
0 2 * * * /usr/bin/php /path/to/pengaduan/auto_purge.php >> /path/to/pengaduan/logs/auto_purge_cron.log 2>&1
```

#### Windows (Task Scheduler)
1. Buka Task Scheduler
2. Create Basic Task
3. Set trigger: Daily, 02:00 AM
4. Action: Start a program
5. Program: `C:\xampp\php\php.exe`
6. Arguments: `C:\xampp\htdocs\pengaduan\auto_purge.php`
7. Start in: `C:\xampp\htdocs\pengaduan`

#### Manual Testing
Akses via browser untuk test:
```
http://localhost/pengaduan/auto_purge.php
```

## 🔧 Penggunaan

### User: Menghapus Postingan

Saat user menghapus postingan:
1. Postingan **tidak benar-benar dihapus** dari database
2. Kolom `deleted_at` di-set dengan timestamp saat ini
3. Postingan **tidak ditampilkan** lagi di timeline user
4. Postingan **masih bisa dilihat** oleh admin di halaman Trash

**Query yang dijalankan:**
```sql
UPDATE posts SET deleted_at = NOW() WHERE id = ? AND user_id = ?
```

### Admin: Melihat Postingan Terhapus

1. Buka halaman **Admin Dashboard**
2. Klik tombol **"🗑️ Postingan Terhapus"** atau akses `admin_trash.php`
3. Lihat semua postingan yang sudah dihapus
4. Setiap postingan menampilkan:
   - Tanggal diposting
   - Tanggal dihapus
   - Countdown hari sampai auto purge (30 hari)
   - Status expired (jika sudah 30 hari)

### Admin: Restore Postingan

1. Di halaman Trash, klik tombol **"↻ Restore"**
2. Postingan akan dikembalikan ke aktif (`deleted_at` di-set NULL)
3. Postingan akan muncul lagi di timeline

**Query yang dijalankan:**
```sql
UPDATE posts SET deleted_at = NULL WHERE id = ?
```

### Admin: Hapus Permanen

1. Di halaman Trash, klik tombol **"🗑️ Hapus Permanen"**
2. Postingan akan **benar-benar dihapus** dari database
3. **Tindakan ini tidak bisa dibatalkan!**

**Query yang dijalankan:**
```sql
DELETE FROM posts WHERE id = ?
```

### Admin: Bulk Actions

1. Centang beberapa postingan di halaman Trash
2. Klik **"✅ Restore Selected"** atau **"🗑️ Hapus Permanen Selected"**
3. Semua postingan yang dipilih akan diproses sekaligus

## 🔄 Auto Purge

### Cara Kerja

1. Script `auto_purge.php` mencari semua postingan dengan:
   - `deleted_at IS NOT NULL` (sudah dihapus)
   - `DATEDIFF(NOW(), deleted_at) >= 30` (sudah 30 hari)

2. Postingan yang memenuhi kriteria akan dihapus permanen

3. Log hasil akan disimpan di `logs/auto_purge.log`

### Query Auto Purge

```sql
SELECT id, deleted_at, nama, keluhan 
FROM posts 
WHERE deleted_at IS NOT NULL 
AND DATEDIFF(NOW(), deleted_at) >= 30
ORDER BY deleted_at ASC;
```

### Manual Run

Jalankan script secara manual:
```bash
php auto_purge.php
```

atau akses via browser:
```
http://localhost/pengaduan/auto_purge.php
```

## 📊 Query Examples

### 1. Menampilkan Postingan Aktif (User & Admin Dashboard)

```sql
SELECT * FROM posts 
WHERE deleted_at IS NULL 
ORDER BY tanggal_post DESC;
```

### 2. Menampilkan Postingan Terhapus (Admin Trash)

```sql
SELECT * FROM posts 
WHERE deleted_at IS NOT NULL 
ORDER BY deleted_at DESC;
```

### 3. Menghitung Jumlah Postingan Terhapus

```sql
SELECT COUNT(*) as count 
FROM posts 
WHERE deleted_at IS NOT NULL;
```

### 4. Mencari Postingan yang Akan Expired (Kurang dari 7 hari lagi)

```sql
SELECT id, nama, deleted_at, 
       DATEDIFF(NOW(), deleted_at) as days_in_trash,
       (30 - DATEDIFF(NOW(), deleted_at)) as days_remaining
FROM posts 
WHERE deleted_at IS NOT NULL 
AND DATEDIFF(NOW(), deleted_at) >= 23
AND DATEDIFF(NOW(), deleted_at) < 30;
```

### 5. Restore Postingan

```sql
UPDATE posts 
SET deleted_at = NULL 
WHERE id = ?;
```

### 6. Soft Delete Postingan

```sql
UPDATE posts 
SET deleted_at = NOW() 
WHERE id = ?;
```

### 7. Hard Delete (Permanent Delete)

```sql
DELETE FROM posts 
WHERE id = ?;
```

## 🔒 Keamanan

### 1. Migration Script
- **Hapus atau rename** file `migration_add_soft_delete.php` setelah migration selesai
- Jangan biarkan file migration bisa diakses publik

### 2. Auto Purge Script
- Untuk produksi, **batasi akses** ke `auto_purge.php`
- Gunakan `.htaccess` untuk memblokir akses web, hanya jalankan via cron job
- Atau tambahkan authentication token

### 3. Log Files
- File log di `logs/auto_purge.log` bisa berisi informasi sensitif
- Pastikan folder `logs/` tidak bisa diakses via web browser
- Tambahkan `.htaccess` di folder logs untuk memblokir akses

## 🐛 Troubleshooting

### Problem: Kolom `deleted_at` tidak ada

**Solusi:**
```bash
# Jalankan migration
php migration_add_soft_delete.php
# atau
# Akses via browser: http://localhost/pengaduan/migration_add_soft_delete.php
```

### Problem: Auto purge tidak berjalan

**Solusi:**
1. Cek apakah cron job sudah setup dengan benar
2. Test manual: `php auto_purge.php`
3. Cek file log: `logs/auto_purge.log`
4. Pastikan PHP CLI path benar di cron job

### Problem: Postingan terhapus tidak muncul di Trash

**Solusi:**
1. Cek apakah kolom `deleted_at` sudah ada
2. Cek query: `SELECT * FROM posts WHERE deleted_at IS NOT NULL;`
3. Pastikan user memiliki role admin

### Problem: Postingan tidak terhapus setelah 30 hari

**Solusi:**
1. Cek apakah cron job berjalan
2. Test manual script `auto_purge.php`
3. Cek log file untuk error
4. Pastikan `DATEDIFF` function bekerja dengan benar

## 📝 Notes

- **Soft Delete** lebih aman karena data tidak benar-benar hilang
- **Auto Purge** membantu membersihkan database secara otomatis
- **Restore** memungkinkan admin mengembalikan postingan yang terhapus tidak sengaja
- **30 hari** adalah waktu standar, bisa disesuaikan dengan kebutuhan

## 🔗 Related Files

- `user_dashboard.php` - User dashboard dengan soft delete
- `admin_dashboard.php` - Admin dashboard dengan filter postingan aktif
- `admin_trash.php` - Halaman trash bin untuk admin
- `auto_purge.php` - Script auto purge
- `migration_add_soft_delete.php` - Migration script
- `includes/header.php` - Header dengan link ke Trash

## 📞 Support

Jika ada masalah atau pertanyaan, silakan cek:
1. File log: `logs/auto_purge.log`
2. Error log PHP
3. Database query log
4. Dokumentasi ini

---

**Last Updated:** 2024
**Version:** 1.0.0

