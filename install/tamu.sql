-- =====================================================================
-- Tabel buku tamu digital (tamu.php).
--
-- Catatan: file ini bersifat cadangan/manual. Aplikasi sudah membuat tabel
-- ini secara otomatis saat halaman Buku Tamu pertama kali dibuka (lihat
-- tamu.php → blok "CREATE TABLE IF NOT EXISTS `tamu`"). Jalankan SQL ini
-- hanya bila user database tidak punya hak CREATE TABLE.
-- =====================================================================

CREATE TABLE IF NOT EXISTS `tamu` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama`            VARCHAR(191) NOT NULL DEFAULT '',
    `no_hp`           VARCHAR(40)  NOT NULL DEFAULT '',
    `instansi`        VARCHAR(191) NOT NULL DEFAULT '',
    `tujuan_bertamu`  VARCHAR(191) NOT NULL DEFAULT '',
    `nama_personel`   VARCHAR(191) NOT NULL DEFAULT '',
    `keperluan`       TEXT NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tamu_created` (`created_at`),
    KEY `idx_tamu_tujuan`  (`tujuan_bertamu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
