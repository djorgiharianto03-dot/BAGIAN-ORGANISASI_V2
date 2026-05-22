<?php
declare(strict_types=1);

/**
 * Bootstrap schema dev (Laragon) — maksimal sekali per 24 jam, bukan tiap request.
 */
function org_run_dev_database_bootstrap_once(): void
{
    if (!function_exists('org_is_dev_environment') || !org_is_dev_environment()) {
        return;
    }

    $flagDir = ORG_ROOT . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . '.cache';
    if (!is_dir($flagDir)) {
        @mkdir($flagDir, 0755, true);
    }
    $flagFile = $flagDir . DIRECTORY_SEPARATOR . 'dev_users_schema_boot.ok';
    if (is_file($flagFile) && (time() - (int) filemtime($flagFile)) < 86400) {
        return;
    }

    mysqli_report(MYSQLI_REPORT_OFF);
    $__orgDbHost = '127.0.0.1';
    $__orgDbUser = 'root';
    $__orgDbPass = '';
    $__orgDbName = 'db_organisasi';
    $__orgConn = mysqli_connect($__orgDbHost, $__orgDbUser, $__orgDbPass);
    if (!$__orgConn instanceof mysqli) {
        return;
    }

    mysqli_query(
        $__orgConn,
        'CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', $__orgDbName) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
    mysqli_select_db($__orgConn, $__orgDbName);
    $sqlCreateUsers = 'CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(191) NOT NULL DEFAULT \'\',
  `username` VARCHAR(64) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email_google` VARCHAR(255) NOT NULL DEFAULT \'\',
  `level` VARCHAR(64) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    mysqli_query($__orgConn, $sqlCreateUsers);
    mysqli_query($__orgConn, "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `level` VARCHAR(64) NULL DEFAULT NULL");
    mysqli_query($__orgConn, "UPDATE `users` SET `level` = `role` WHERE (`level` IS NULL OR `level` = '') AND `role` IS NOT NULL");
    mysqli_query($__orgConn, "UPDATE `users` SET `level` = 'sub_admin_eorganisasi' WHERE LOWER(TRIM(COALESCE(`level`, ''))) IN ('sub admin', 'sub_admin', 'subadmin')");
    mysqli_query($__orgConn, "UPDATE `users` SET `level` = 'admin' WHERE LOWER(TRIM(COALESCE(`level`, ''))) IN ('admin', 'administrator')");
    mysqli_query($__orgConn, "UPDATE `users` SET `level` = 'super_admin' WHERE LOWER(TRIM(COALESCE(`level`, ''))) IN ('super admin', 'super_admin')");
    mysqli_query($__orgConn, "UPDATE `users` SET `level` = 'admin' WHERE LOWER(`username`) = 'djorgi' AND (`level` IS NULL OR `level` = '')");
    mysqli_query(
        $__orgConn,
        "UPDATE `users` SET `level` = 'kabag_organisasi'
         WHERE LOWER(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(`username`), ' ', ''), '-', ''), '_', ''), '.', '')) = 'kabagorganisasi'
            OR LOWER(TRIM(`username`)) IN ('kabag_organisasi', 'kabag organisasi', 'kabag-organisasi', 'kabag')"
    );
    mysqli_query(
        $__orgConn,
        "UPDATE `users` SET `level` = 'kabag_organisasi'
         WHERE LOWER(TRIM(COALESCE(`nama`, ''))) LIKE '%kabag%'
           AND LOWER(TRIM(COALESCE(`nama`, ''))) LIKE '%organisasi%'"
    );
    $chkNama = mysqli_query($__orgConn, "SHOW COLUMNS FROM `users` LIKE 'nama'");
    $namaColExists = $chkNama && mysqli_num_rows($chkNama) > 0;
    if ($chkNama) {
        mysqli_free_result($chkNama);
    }
    if (!$namaColExists) {
        $chkLegacy = mysqli_query($__orgConn, "SHOW COLUMNS FROM `users` LIKE 'nama_staf'");
        if ($chkLegacy && mysqli_num_rows($chkLegacy) > 0) {
            mysqli_query($__orgConn, 'ALTER TABLE `users` CHANGE `nama_staf` `nama` VARCHAR(191) NOT NULL DEFAULT \'\'');
        }
        if ($chkLegacy) {
            mysqli_free_result($chkLegacy);
        }
    }
    $resCnt = mysqli_query($__orgConn, 'SELECT COUNT(*) AS `cnt` FROM `users`');
    $rowCnt = $resCnt ? mysqli_fetch_assoc($resCnt) : null;
    if ($resCnt) {
        mysqli_free_result($resCnt);
    }
    $userCount = (int) ($rowCnt['cnt'] ?? 0);
    if ($userCount === 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        if ($hash !== false) {
            $stmt = mysqli_prepare(
                $__orgConn,
                'INSERT INTO `users` (`nama`, `username`, `password`, `email_google`, `level`) VALUES (?, ?, ?, ?, ?)'
            );
            if ($stmt) {
                $seedNama = 'Administrator';
                $seedUser = 'admin';
                $seedEmail = '';
                $seedLevel = 'admin';
                mysqli_stmt_bind_param($stmt, 'sssss', $seedNama, $seedUser, $hash, $seedEmail, $seedLevel);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
    mysqli_close($__orgConn);
    @touch($flagFile);
}
