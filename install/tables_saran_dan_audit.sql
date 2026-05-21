-- =============================================================================
-- Saran pengunjung & audit trail
-- =============================================================================
-- Tujuan : membuat tabel `saran_pengunjung` dan `audit_logs` jika belum ada.
-- Catatan: sesuaikan nama basis data di bawah (atau hapus baris USE dan pilih
--          basis data lewat phpMyAdmin / klien MySQL Anda).
--          Struktur kolom mengikuti kode PHP (api/saran.php, site_content_db).
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 1) Pilih basis data tempat tabel akan dibuat (ubah nama jika perlu)
-- -----------------------------------------------------------------------------
USE `db_organisasi`;

-- -----------------------------------------------------------------------------
-- 2) Tabel audit_logs — menyimpan riwayat aksi admin (id, nama, deskripsi)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key log',
  `id_admin` VARCHAR(64) NOT NULL COMMENT 'Identitas login admin (username/id)',
  `nama_admin` VARCHAR(191) NOT NULL COMMENT 'Nama tampilan admin',
  `aksi` VARCHAR(512) NOT NULL COMMENT 'Ringkasan perubahan yang dilakukan',
  `waktu` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu kejadian server',
  PRIMARY KEY (`id`),
  KEY `idx_waktu` (`waktu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Audit trail perubahan konten / aktivitas admin';

-- -----------------------------------------------------------------------------
-- 3) Tabel saran_pengunjung — menyimpan saran & kritik dari formulir publik
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `saran_pengunjung` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary key saran',
  `nama` VARCHAR(191) NOT NULL COMMENT 'Nama pengirim',
  `email` VARCHAR(191) NOT NULL COMMENT 'Email pengirim',
  `pesan` TEXT NOT NULL COMMENT 'Isi saran atau kritik',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pengiriman',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Kotak saran pengunjung (AJAX dari halaman publik / dashboard)';

-- =============================================================================
-- Selesai. Tidak mengubah tabel lain (site_content, dll.) jika sudah ada.
-- =============================================================================
