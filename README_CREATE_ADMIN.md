# Cara Membuat 3 Akun Admin

## Akun yang akan dibuat:
- **Username:** akira1, akira2, akira3
- **Password:** akira01 (sama untuk semua)

## Cara 1: Menggunakan File PHP (Direkomendasikan)

1. Buka browser dan akses:
   ```
   http://localhost/pengaduan/create_admin_accounts.php
   ```

2. File akan otomatis membuat 3 akun admin dengan password yang sudah di-hash

3. Setelah selesai, **hapus atau rename file `create_admin_accounts.php`** untuk keamanan

## Cara 2: Menggunakan File SQL

1. Buka phpMyAdmin: `http://localhost/phpmyadmin`

2. Pilih database `pengaduan`

3. Klik tab "SQL"

4. Copy dan paste isi file `create_admin_accounts.sql`

5. Klik "Go" untuk menjalankan

## Setelah Akun Dibuat

Anda bisa login dengan:
- Username: **akira1** | Password: **akira01**
- Username: **akira2** | Password: **akira01**
- Username: **akira3** | Password: **akira01**

Login di: `http://localhost/pengaduan/login_admin.php`

