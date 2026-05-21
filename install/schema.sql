-- =============================================================================
-- install/schema.sql
-- Hilangkan pesan admin: «Impor install/schema.sql» / tabel site_content belum ada.
--
-- Cara impor (phpMyAdmin):
--   1) Pastikan nama database sama dengan config/database.php (default: db_organisasi).
--   2) Pilih database tersebut di panel kiri, lalu tab Impor → pilih berkas ini,
--      atau tab SQL → tempel seluruh isi berkas ini → Kirim.
--
-- Tabel site_content menyimpan baris tunggal (id = 1):
--   profile_visi = Visi, profile_misi = Misi, profile_struktur = Struktur singkat,
--   struktur_blurb & organisasi_intro = teks pengantar halaman lain,
--   pengumuman = kolom pengumuman.
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `db_organisasi` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_organisasi`;

CREATE TABLE IF NOT EXISTS `site_content` (
  `id` TINYINT UNSIGNED NOT NULL COMMENT 'Kunci tetap 1 = satu set konten situs',
  `profile_visi` MEDIUMTEXT NOT NULL COMMENT 'Visi (HTML ringan diperbolehkan)',
  `profile_misi` MEDIUMTEXT NOT NULL COMMENT 'Misi (HTML ringan diperbolehkan)',
  `profile_struktur` TEXT NOT NULL COMMENT 'Struktur singkat / ringkasan',
  `struktur_blurb` TEXT NOT NULL COMMENT 'Pengantar halaman struktur organisasi',
  `organisasi_intro` TEXT NOT NULL COMMENT 'Paragraf pengantar beranda / organisasi',
  `pengumuman` TEXT NOT NULL COMMENT 'Teks pengumuman (boleh kosong)',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_content` (
  `id`,
  `profile_visi`,
  `profile_misi`,
  `profile_struktur`,
  `struktur_blurb`,
  `organisasi_intro`,
  `pengumuman`
) VALUES (
  1,
  'Menjadi bagian organisasi yang profesional, transparan, dan adaptif dalam pelayanan informasi.',
  'Mengelola data dan dokumen organisasi secara efektif, akurat, dan mudah diakses oleh pihak terkait.',
  'Kepala Bagian, Subbag Umum, Subbag Dokumentasi, dan Tim Dukungan Administrasi.',
  'Daftar personel Bagian Organisasi ditampilkan secara dinamis. Foto akan otomatis diambil dari folder uploads, dan memakai placeholder jika file belum tersedia.',
  'Terima kasih telah mengunjungi website organisasi kami. Halaman ini dibuat untuk memudahkan anggota dalam menerima informasi dan mengelola dokumen.',
  ''
) ON DUPLICATE KEY UPDATE `id` = `id`;

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_admin` VARCHAR(64) NOT NULL,
  `nama_admin` VARCHAR(191) NOT NULL,
  `aksi` VARCHAR(512) NOT NULL,
  `waktu` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_waktu` (`waktu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `saran_pengunjung` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(191) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `pesan` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
