-- Script untuk menghapus semua akun admin dan membuat 3 akun admin baru
-- Username: akira, estelle, mustofa
-- Password: akira123, estelle123, mustofa123

USE pengaduan;

-- Langkah 1: Hapus semua akun admin yang ada
DELETE FROM users WHERE role = 'admin';

-- Langkah 2: Buat 3 akun admin baru
-- Password akan di-hash menggunakan password_hash() di PHP
-- Untuk keamanan, gunakan file update_admin_accounts.php yang sudah menghash password secara otomatis

-- Catatan: Hash password di bawah ini adalah contoh, sebaiknya gunakan file PHP untuk generate hash yang benar
-- Atau gunakan password_hash('akira123', PASSWORD_DEFAULT) di PHP

-- Jika ingin menggunakan SQL langsung, uncomment baris di bawah dan ganti hash dengan yang benar:
-- INSERT INTO users (username, password, role) VALUES 
-- ('akira', '$2y$10$...', 'admin'),
-- ('estelle', '$2y$10$...', 'admin'),
-- ('mustofa', '$2y$10$...', 'admin');

-- RECOMMENDED: Gunakan file update_admin_accounts.php untuk keamanan yang lebih baik

