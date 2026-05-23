<?php

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'arsip_surat_db.php';

/**
 * Apakah tabel surat_disposisi ada.
 */
function org_surat_disposisi_table_exists(mysqli $db): bool
{
    $r = $db->query("SHOW TABLES LIKE 'surat_disposisi'");
    if ($r === false) {
        return false;
    }
    $ok = $r->num_rows > 0;
    $r->free();

    return $ok;
}

/**
 * Buat / lengkapi tabel arsip_surat dan surat_disposisi (idempoten).
 */
function org_arsip_surat_disposisi_ensure_tables(mysqli $db): bool
{
    static $done = false;
    if ($done) {
        return org_arsip_surat_table_exists($db) && org_surat_disposisi_table_exists($db);
    }

    $sqlArsip = <<<'SQL'
CREATE TABLE IF NOT EXISTS `arsip_surat` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `jenis_surat` ENUM('masuk', 'keluar') NOT NULL DEFAULT 'masuk',
  `nama_file` VARCHAR(255) NOT NULL,
  `nomor_surat` VARCHAR(128) NOT NULL DEFAULT '',
  `perihal_ringkasan` TEXT NOT NULL,
  `instansi_asal` VARCHAR(255) NOT NULL DEFAULT '',
  `instansi_tujuan` VARCHAR(255) NOT NULL DEFAULT '',
  `kategori_bagian` VARCHAR(64) NOT NULL DEFAULT '',
  `ikut_monitoring_disposisi` TINYINT(1) NOT NULL DEFAULT 1,
  `tanggal_surat` DATE NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_arsip_jenis_nama_file` (`jenis_surat`, `nama_file`(191)),
  KEY `idx_arsip_jenis` (`jenis_surat`),
  KEY `idx_arsip_nomor` (`nomor_surat`),
  KEY `idx_arsip_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;

    $sqlDispo = <<<'SQL'
CREATE TABLE IF NOT EXISTS `surat_disposisi` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_arsip` BIGINT UNSIGNED NOT NULL,
  `parent_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `referensi_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `pengirim_username` VARCHAR(191) NOT NULL,
  `penerima_username` VARCHAR(191) NOT NULL,
  `instruksi` TEXT NOT NULL,
  `file_bukti` VARCHAR(1024) NULL DEFAULT NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'pending',
  `catatan_kabag` TEXT NULL DEFAULT NULL,
  `kabag_tandai_selesai` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sd_id_arsip` (`id_arsip`),
  KEY `idx_sd_parent` (`parent_id`),
  KEY `idx_sd_referensi` (`referensi_id`),
  KEY `idx_sd_pengirim` (`pengirim_username`(64)),
  KEY `idx_sd_penerima` (`penerima_username`(64)),
  KEY `idx_sd_status` (`status`),
  CONSTRAINT `fk_surat_disposisi_arsip`
    FOREIGN KEY (`id_arsip`) REFERENCES `arsip_surat` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_surat_disposisi_parent`
    FOREIGN KEY (`parent_id`) REFERENCES `surat_disposisi` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;

    if (!$db->query($sqlArsip)) {
        return false;
    }
    if (!$db->query($sqlDispo)) {
        return false;
    }

    org_arsip_surat_disposisi_ensure_columns($db);
    $done = true;

    return org_arsip_surat_table_exists($db) && org_surat_disposisi_table_exists($db);
}

/**
 * Tambah kolom yang mungkin belum ada pada instalasi lama (tanpa error fatal).
 */
function org_arsip_surat_disposisi_ensure_columns(mysqli $db): void
{
    if (org_arsip_surat_table_exists($db)) {
        $arsipAlters = [
            'ikut_monitoring_disposisi' => 'ADD COLUMN `ikut_monitoring_disposisi` TINYINT(1) NOT NULL DEFAULT 1 AFTER `kategori_bagian`',
            'tanggal_surat' => 'ADD COLUMN `tanggal_surat` DATE NULL DEFAULT NULL AFTER `ikut_monitoring_disposisi`',
            'created_at' => 'ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'ADD COLUMN `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ];
        foreach ($arsipAlters as $col => $ddl) {
            $chk = $db->query("SHOW COLUMNS FROM `arsip_surat` LIKE '" . $db->real_escape_string($col) . "'");
            $exists = $chk !== false && $chk->num_rows > 0;
            if ($chk) {
                $chk->free();
            }
            if (!$exists) {
                $db->query('ALTER TABLE `arsip_surat` ' . $ddl);
            }
        }
    }

    if (!org_surat_disposisi_table_exists($db)) {
        return;
    }

    $dispoAlters = [
        'referensi_id' => 'ADD COLUMN `referensi_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `parent_id`',
        'catatan_kabag' => 'ADD COLUMN `catatan_kabag` TEXT NULL DEFAULT NULL',
        'kabag_tandai_selesai' => 'ADD COLUMN `kabag_tandai_selesai` TINYINT(1) NOT NULL DEFAULT 0',
        'created_at' => 'ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'ADD COLUMN `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
    ];
    foreach ($dispoAlters as $col => $ddl) {
        $chk = $db->query("SHOW COLUMNS FROM `surat_disposisi` LIKE '" . $db->real_escape_string($col) . "'");
        $exists = $chk !== false && $chk->num_rows > 0;
        if ($chk) {
            $chk->free();
        }
        if (!$exists) {
            $db->query('ALTER TABLE `surat_disposisi` ' . $ddl);
        }
    }
}

/**
 * Sinkron sekali: meta JSON arsip → baris arsip_surat (untuk surat masuk siap didisposisikan).
 *
 * @return int jumlah baris baru
 */
function org_arsip_surat_disposisi_sync_from_meta(mysqli $db, string $orgRoot, int $maxInserts = 350): int
{
    if (!org_arsip_surat_disposisi_ensure_tables($db)) {
        return 0;
    }
    $metaFile = $orgRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'arsip_surat_meta.json';
    $dirMap = [
        'masuk' => $orgRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'surat_masuk',
        'keluar' => $orgRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'surat_keluar',
    ];

    return org_arsip_sync_meta_to_arsip_surat_table($db, $metaFile, $dirMap, $maxInserts);
}
