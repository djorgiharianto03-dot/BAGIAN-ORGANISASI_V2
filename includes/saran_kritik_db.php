<?php

declare(strict_types=1);



require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';



/**

 * @return array<string, true> nama kolom (huruf kecil) => true

 */

function org_saran_kritik_columns(mysqli $db): array

{

    $out = [];

    $r = $db->query('SHOW COLUMNS FROM `saran_kritik`');

    if ($r === false) {

        return $out;

    }

    while ($row = $r->fetch_assoc()) {

        $f = strtolower((string) ($row['Field'] ?? ''));

        if ($f !== '') {

            $out[$f] = true;

        }

    }

    return $out;

}



/**

 * Menambah kolom tgl_kirim bila belum ada (selaras HeidiSQL / urutan admin).

 */

function org_saran_kritik_ensure_tgl_kirim_column(mysqli $db): void

{

    if (!org_saran_kritik_table_exists($db)) {

        return;

    }

    $cols = org_saran_kritik_columns($db);

    if (isset($cols['tgl_kirim'])) {

        return;

    }

    $db->query(

        "ALTER TABLE `saran_kritik` ADD COLUMN `tgl_kirim` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu kirim' AFTER `pesan`"

    );

    if (isset($cols['created_at'])) {

        $db->query('UPDATE `saran_kritik` SET `tgl_kirim` = `created_at`');

    }

}



function org_saran_kritik_ensure_table(mysqli $db): void

{

    $db->query(

        'CREATE TABLE IF NOT EXISTS `saran_kritik` (

          `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

          `nama` VARCHAR(191) NOT NULL,

          `email` VARCHAR(191) NOT NULL,

          `pesan` TEXT NOT NULL,

          `tgl_kirim` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

          PRIMARY KEY (`id`),

          KEY `idx_saran_kritik_tgl` (`tgl_kirim`),

          KEY `idx_saran_kritik_created` (`created_at`)

        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'

    );

    org_saran_kritik_ensure_tgl_kirim_column($db);

}



function org_saran_kritik_table_exists(mysqli $db): bool

{

    $r = $db->query("SHOW TABLES LIKE 'saran_kritik'");

    return $r !== false && $r->num_rows > 0;

}



/**

 * @return list<array<string, string>>

 */

function org_saran_kritik_fetch_all(mysqli $db, int $limit = 500): array

{

    if (!org_saran_kritik_table_exists($db)) {

        return [];

    }

    org_saran_kritik_ensure_tgl_kirim_column($db);

    $limit = max(1, min(2000, $limit));

    $rows = [];

    $cols = org_saran_kritik_columns($db);

    $order = isset($cols['tgl_kirim'])

        ? 'tgl_kirim DESC, id DESC'

        : 'created_at DESC, id DESC';

    $sql = 'SELECT * FROM saran_kritik ORDER BY ' . $order . ' LIMIT ' . (int) $limit;

    $res = $db->query($sql);

    if ($res) {

        while ($row = $res->fetch_assoc()) {

            if (is_array($row)) {

                $rows[] = $row;

            }

        }

    }

    return $rows;

}


