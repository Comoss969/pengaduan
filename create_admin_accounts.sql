-- Script untuk membuat 3 akun admin
-- Username: akira1, akira2, akira3
-- Password: akira01

USE pengaduan;

-- Hash password untuk akira01: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- Password hash dihasilkan menggunakan password_hash('akira01', PASSWORD_DEFAULT)

-- Insert akun admin akira1
INSERT INTO users (username, password, role) 
VALUES ('akira1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE username = username;

-- Insert akun admin akira2
INSERT INTO users (username, password, role) 
VALUES ('akira2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE username = username;

-- Insert akun admin akira3
INSERT INTO users (username, password, role) 
VALUES ('akira3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE username = username;

