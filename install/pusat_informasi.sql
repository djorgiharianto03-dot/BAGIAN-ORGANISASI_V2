-- Pusat Informasi & Pengumuman (beranda; gambar di uploads/pusat_informasi/)



CREATE TABLE IF NOT EXISTS `pusat_informasi` (

  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,

  `judul` VARCHAR(255) NOT NULL DEFAULT '',

  `kategori` VARCHAR(32) NOT NULL DEFAULT 'berita' COMMENT 'berita | pengumuman',

  `isi_teks` TEXT NOT NULL,

  `nama_gambar` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Berkas di uploads/pusat_informasi/',

  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),

  KEY `idx_pusat_informasi_created` (`created_at`),

  KEY `idx_pusat_informasi_kategori` (`kategori`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


