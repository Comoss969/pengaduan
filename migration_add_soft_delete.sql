-- Migration Script: Menambahkan Soft Delete ke Tabel Posts
-- Fitur: Trash Bin dengan Auto Purge setelah 30 hari
-- 
-- Kolom yang ditambahkan:
-- - deleted_at: Menyimpan timestamp kapan postingan dihapus (NULL = tidak terhapus)
--
-- Logika:
-- - Jika deleted_at IS NULL, postingan masih aktif (ditampilkan)
-- - Jika deleted_at IS NOT NULL, postingan sudah dihapus (soft delete)
-- - Auto purge akan menghapus permanen postingan dengan deleted_at > 30 hari

USE pengaduan;

-- Tambahkan kolom deleted_at ke tabel posts
-- Kolom ini akan menyimpan timestamp kapan postingan dihapus
-- NULL berarti postingan masih aktif, NOT NULL berarti sudah dihapus (soft delete)
ALTER TABLE posts 
ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL 
AFTER profanity_count;

-- Tambahkan index pada deleted_at untuk mempercepat query
-- Index ini membantu query untuk mencari postingan yang terhapus
CREATE INDEX idx_posts_deleted_at ON posts(deleted_at);

-- Tambahkan komentar pada kolom untuk dokumentasi
ALTER TABLE posts 
MODIFY COLUMN deleted_at DATETIME NULL DEFAULT NULL 
COMMENT 'Timestamp kapan postingan dihapus (soft delete). NULL = aktif, NOT NULL = terhapus. Auto purge setelah 30 hari.';

-- Verifikasi perubahan
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'pengaduan' 
AND TABLE_NAME = 'posts' 
AND COLUMN_NAME = 'deleted_at';

