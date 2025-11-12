# 📝 Sistem Pengaduan (Complaint Management System)

Sistem web untuk pengajuan keluhan/pengaduan dengan fitur filtering konten, trash bin, dan dashboard admin yang komprehensif.

## 🚀 Fitur Utama

### Untuk User
- ✅ Registrasi dan login akun pengguna
- ✅ Membuat postingan keluhan dengan opsi anonim
- ✅ Upload foto/gambar pada postingan
- ✅ Memberikan komentar pada postingan keluhan
- ✅ Melihat profil dan mengedit akun
- ✅ Soft delete (menghapus postingan yang masih bisa dipulihkan)
- ✅ Dashboard untuk melihat keluhan dan aktivitas

### Untuk Admin
- ✅ Dashboard admin untuk monitoring semua keluhan
- ✅ Fitur trash bin untuk melihat postingan terhapus
- ✅ Restore/hapus permanen postingan dari trash
- ✅ Edit akun pengguna lain
- ✅ Profanity filter - deteksi dan sensor kata-kata tidak pantas
- ✅ Activity logs untuk audit trail
- ✅ Auto-purge untuk menghapus permanen postingan setelah 30 hari

## 💻 Teknologi yang Digunakan

- **Backend**: PHP 7+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap (implied dari struktur)
- **Authentication**: Session-based dengan password hashing (bcrypt)

## 📋 Persyaratan Sistem

- XAMPP (PHP, MySQL, Apache)
- Browser modern (Chrome, Firefox, Edge, Safari)
- Koneksi internet (untuk awalnya, dapat offline setelah setup)

## 🔧 Instalasi

### 1. Setup Database

**Cara 1: Menggunakan phpMyAdmin (Recommended)**
1. Buka `http://localhost/phpmyadmin`
2. Klik "New" untuk membuat database baru
3. Nama database: `pengaduan`
4. Klik "Create"
5. Pilih database `pengaduan` → Tab "SQL"
6. Buka dan copy isi file `db.sql`
7. Paste di textarea SQL dan klik "Go"

**Cara 2: Menggunakan Command Line**
```bash
mysql -u root -p < db.sql
```

### 2. Konfigurasi Koneksi Database

Edit file `config.php`:
```php
$host = 'localhost';
$dbname = 'pengaduan';
$username = 'root';        // Username MySQL Anda
$password = '';            // Password MySQL Anda (kosong untuk XAMPP default)
```

### 3. Buat Akun Admin

**Cara 1: Menggunakan PHP Script (Recommended)**
1. Akses: `http://localhost/pengaduan/create_admin_accounts.php`
2. Script akan otomatis membuat 3 akun admin
3. **PENTING**: Hapus/rename file `create_admin_accounts.php` setelah selesai untuk keamanan

**Cara 2: Menggunakan SQL**
1. Buka phpMyAdmin
2. Pilih database `pengaduan` → Tab "SQL"
3. Copy isi file `create_admin_accounts.sql`
4. Paste dan klik "Go"

**Default Admin Credentials** (jika ada di db.sql):
- Username: `admin`
- Password: `admin123`

Akun yang dibuat oleh script:
- Username: `akira1`, Password: `akira01`
- Username: `akira2`, Password: `akira01`
- Username: `akira3`, Password: `akira01`

### 4. Setup Folder Permissions

Pastikan folder ini writable:
```
uploads/        - Untuk menyimpan file upload
logs/           - Untuk activity logs dan auto-purge logs
```

## 🗂️ Struktur Folder

```
pengaduan/
├── index.php                    # Halaman redirect ke login user
├── config.php                   # Konfigurasi database dan session
│
├── Authentikasi
├── login_user.php              # Login untuk user
├── login_admin.php             # Login untuk admin
├── register.php                # Registrasi user baru
├── logout.php                  # Logout
│
├── Dashboard & Profile
├── user_dashboard.php          # Dashboard user
├── admin_dashboard.php         # Dashboard admin
├── edit_profile.php            # Edit profil user
├── edit_account.php            # Edit akun user (admin)
│
├── Fitur Trash Bin
├── admin_trash.php             # Halaman trash bin admin
├── includes/trash_functions.php # Fungsi helper trash bin
├── migration_add_soft_delete.php # Migration script
├── auto_purge.php              # Auto-delete postingan 30 hari
│
├── Manajemen Postingan & Komentar
├── delete_comment.php          # Hapus komentar
│
├── Fitur Keamanan
├── includes/profanity_filter.php # Filter kata-kata tidak pantas
├── create_profanity_table.php  # Setup tabel profanity
├── create_activity_logs_table.php # Setup tabel activity logs
│
├── Admin Tools
├── create_admin_accounts.php   # Buat akun admin
├── update_admin_accounts.php   # Update akun admin
├── fix_username_constraint.php # Fix constraint database
│
├── Dokumentasi SQL
├── db.sql                      # Database schema
├── create_admin_accounts.sql   # Insert admin accounts
├── update_admin_accounts.sql   # Update admin accounts
├── update_comments_table.sql   # Update tabel comments
├── migration_add_soft_delete.sql # Migration soft delete
│
├── Konfigurasi Cron
├── cron_setup_example.sh       # Contoh setup cron job
│
├── Assets
├── assets/
│   ├── css/
│   │   └── style.css          # Stylesheet utama
│   ├── images/                # Gambar project
│   └── js/
│       └── script.js          # JavaScript utama
│
├── Includes
├── includes/
│   ├── header.php             # Header/navbar
│   ├── footer.php             # Footer
│   ├── profanity_filter.php   # Filter kata-kata tidak pantas
│   └── trash_functions.php    # Fungsi trash bin
│
├── Storage
├── uploads/                   # Folder upload file/foto
├── logs/                      # Folder logs
│
└── Dokumentasi
    ├── README.md              # Dokumentasi utama (file ini)
    ├── README_CREATE_ADMIN.md # Panduan membuat admin
    ├── README_TRASH_BIN.md    # Dokumentasi fitur trash bin
    ├── TRASH_BIN_SUMMARY.md   # Ringkasan implementasi trash bin
    └── TODO.md                # Task list pengembangan
```

## 🔐 Fitur Keamanan

### 1. Authentication & Authorization
- Password hashing menggunakan bcrypt (`password_hash()` dan `password_verify()`)
- Session-based authentication
- Role-based access control (Admin vs User)

### 2. Input Validation
- Filter input untuk mencegah SQL injection
- Sanitasi data sebelum disimpan ke database

### 3. Profanity Filter
- Deteksi kata-kata tidak pantas/profanity
- Sensor otomatis dengan `[SENSOR]`
- Tabel `profanity_list` untuk database kata-kata terlarang

### 4. Activity Logging
- Log semua aktivitas penting pengguna
- Tersimpan di tabel `activity_logs`
- Berguna untuk audit trail dan keamanan

## 🗑️ Fitur Trash Bin

### Konsep Soft Delete
Postingan yang dihapus user tidak langsung hilang dari database, tetapi ditandai dengan kolom `deleted_at`. Hal ini memungkinkan:

- **User**: Tidak melihat postingan yang sudah dihapus
- **Admin**: Dapat melihat, restore, atau hapus permanen

### Cara Kerja
1. User menghapus postingan → `deleted_at` di-set ke waktu saat ini
2. Postingan tidak tampil di timeline user
3. Admin dapat lihat di halaman Trash (`admin_trash.php`)
4. Admin bisa restore atau permanent delete
5. Auto-purge (30 hari) otomatis hapus permanen

### Menggunakan Trash Bin

**Untuk User:**
- Cukup klik tombol hapus pada postingan di dashboard

**Untuk Admin:**
1. Login sebagai admin
2. Klik "Trash Bin" di navbar
3. Lihat daftar postingan terhapus
4. Opsi: Restore atau Permanent Delete

### Setup Auto-Purge dengan Cron Job

**Linux/Mac:**
```bash
# Edit crontab
crontab -e

# Tambahkan baris ini untuk menjalankan setiap jam (hapus postingan 30+ hari)
0 * * * * /usr/bin/php /path/to/auto_purge.php

# Untuk menjalankan setiap hari pukul 2 pagi
0 2 * * * /usr/bin/php /path/to/auto_purge.php
```

**Windows:**
- Gunakan Task Scheduler
- Lihat file `cron_setup_example.sh` untuk contoh

**Manual Trigger:**
- Akses: `http://localhost/pengaduan/auto_purge.php`
- Atau jalankan via PHP CLI: `php auto_purge.php`

## 📊 Struktur Database

### Tabel `users`
```sql
id (INT) - Primary Key
username (VARCHAR 50) - Unique
password (VARCHAR 255) - Hashed
role (ENUM) - 'admin' atau 'user'
profile_picture (VARCHAR 255) - Path foto profil
```

### Tabel `posts`
```sql
id (INT) - Primary Key
user_id (INT) - FK users.id
nama (VARCHAR 100) - Nama pengguna (opsional untuk anonim)
keluhan (TEXT) - Isi keluhan
censored_keluhan (TEXT) - Keluhan dengan filter profanity
foto (VARCHAR 255) - Path foto
tanggal_post (DATETIME) - Waktu posting
is_anonim (BOOLEAN) - Flag anonim
profanity_count (INT) - Jumlah kata profanity
deleted_at (DATETIME) - Soft delete timestamp (nullable)
```

### Tabel `comments`
```sql
id (INT) - Primary Key
post_id (INT) - FK posts.id
user_id (INT) - FK users.id
komentar (TEXT) - Isi komentar
tanggal (DATETIME) - Waktu komentar
is_admin (BOOLEAN) - Flag komentar dari admin
```

### Tabel `activity_logs`
```sql
id (INT) - Primary Key
user_id (INT) - FK users.id
action (VARCHAR 100) - Aksi yang dilakukan
description (TEXT) - Detail aksi
created_at (DATETIME) - Waktu aksi
```

### Tabel `profanity_list`
```sql
id (INT) - Primary Key
word (VARCHAR 100) - Kata profanity
category (VARCHAR 50) - Kategori
created_at (DATETIME) - Waktu ditambahkan
```

## 👨‍💻 Penggunaan

### Untuk User Baru

1. **Registrasi**
   - Akses: `http://localhost/pengaduan/register.php`
   - Isi username dan password
   - Klik "Register"

2. **Login**
   - Akses: `http://localhost/pengaduan/login_user.php`
   - Masukkan username dan password

3. **Buat Postingan**
   - Di dashboard user, klik "Buat Postingan"
   - Isi keluhan (opsional: upload foto)
   - Centang "Post Anonim" jika ingin anonymous
   - Klik "Submit"

4. **Komentar**
   - Klik postingan untuk melihat detail
   - Tulis komentar dan submit

### Untuk Admin

1. **Login Admin**
   - Akses: `http://localhost/pengaduan/login_admin.php`
   - Gunakan akun admin (lihat bagian Instalasi)

2. **Monitor Keluhan**
   - Dashboard menampilkan semua postingan
   - Bisa lihat detail dan komentar

3. **Manage Trash**
   - Klik "Trash Bin" di navbar
   - Lihat postingan terhapus
   - Restore atau permanent delete

4. **Edit Profil User**
   - Klik "Manage Users" atau menu user
   - Edit informasi pengguna lain

## 🐛 Troubleshooting

### Database Connection Error
```
Connection failed: could not find driver
```
**Solusi:**
- Pastikan XAMPP MySQL sudah running
- Check `config.php` - pastikan host, username, password benar
- Pastikan database `pengaduan` sudah dibuat

### File Upload Tidak Bekerja
**Solusi:**
- Pastikan folder `uploads/` ada dan writable
- Cek permission folder: `chmod 755 uploads/`
- Pastikan file size tidak melebihi batas PHP

### Session Tidak Tersimpan
**Solusi:**
- Pastikan cookies enabled di browser
- Check `session_start()` ada di `config.php`
- Pastikan folder `sessions` writable (jika custom session handler)

### Profanity Filter Tidak Bekerja
**Solusi:**
- Pastikan tabel `profanity_list` sudah dibuat
- Jalankan: `http://localhost/pengaduan/create_profanity_table.php`
- Cek apakah data profanity list sudah dimasukkan

### Auto-Purge Tidak Berjalan
**Solusi:**
- Jalankan manual: `http://localhost/pengaduan/auto_purge.php`
- Check file `logs/auto_purge.log` untuk error details
- Pastikan folder `logs/` writable

## 📝 Notes & Best Practices

1. **Setelah Setup**
   - Hapus file `create_admin_accounts.php` untuk keamanan
   - Ubah password admin default jika ada
   - Setup `logs/.htaccess` untuk proteksi logs

2. **Security Recommendations**
   - Gunakan HTTPS di production
   - Implement rate limiting untuk login
   - Regular backup database
   - Update PHP ke versi terbaru
   - Disable file upload untuk tipe file tertentu

3. **Maintenance**
   - Monitor `logs/` folder secara berkala
   - Bersihkan `uploads/` dari file yang tidak terpakai
   - Check database untuk optimize tabel besar
   - Review activity logs untuk mencari aktivitas mencurigakan

## 📚 Referensi Dokumentasi Tambahan

- `README_CREATE_ADMIN.md` - Panduan detail membuat akun admin
- `README_TRASH_BIN.md` - Dokumentasi lengkap fitur trash bin
- `TRASH_BIN_SUMMARY.md` - Ringkasan implementasi dan status
- `TODO.md` - Task list pengembangan berkelanjutan

## 🤝 Support & Kontribusi

Jika menemukan bug atau ingin berkontribusi:
1. Dokumentasikan bug dengan detail
2. Sertakan screenshot atau error message
3. Buat pull request dengan penjelasan yang jelas

## 📄 Lisensi

Project ini dibuat untuk keperluan internal/pembelajaran.

---

**Last Updated**: November 2025
**Version**: 1.0
