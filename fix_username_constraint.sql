-- Script untuk memperbaiki constraint username
-- Memungkinkan admin dan user menggunakan username yang sama
-- Tapi sesama user atau sesama admin tidak bisa menggunakan username yang sama

USE pengaduan;

-- Hapus constraint UNIQUE pada kolom username
ALTER TABLE users DROP INDEX username;

-- Buat composite unique key pada (username, role)
-- Ini memungkinkan username yang sama untuk role yang berbeda
-- Tapi username harus unik untuk role yang sama
ALTER TABLE users ADD UNIQUE KEY unique_username_role (username, role);

