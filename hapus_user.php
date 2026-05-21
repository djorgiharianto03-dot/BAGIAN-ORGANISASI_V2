<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_session.php';
org_session_start();

if (empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'staff_users_db.php';

$actorLevel = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
if (!in_array($actorLevel, ['super_admin', 'admin'], true)) {
    $_SESSION['flash_message'] = 'Akses Ditolak';
    $_SESSION['flash_type'] = 'danger';
    header('Location: admin/dashboard.php#panel-manajemen-staf');
    exit;
}

$userId = (int) ($_POST['user_id'] ?? $_GET['user_id'] ?? 0);
$db = org_db();
if (!($db instanceof mysqli) || $userId < 1) {
    $_SESSION['flash_message'] = 'Permintaan hapus tidak valid.';
    $_SESSION['flash_type'] = 'warning';
    header('Location: admin/dashboard.php#panel-manajemen-staf');
    exit;
}

$row = org_staff_users_fetch_by_id($db, $userId);
if ($row === null) {
    $_SESSION['flash_message'] = 'User tidak ditemukan.';
    $_SESSION['flash_type'] = 'warning';
    header('Location: admin/dashboard.php#panel-manajemen-staf');
    exit;
}

$targetLevel = org_staff_role_normalize((string) ($row['level'] ?? ''));
if ($targetLevel === 'super_admin') {
    $_SESSION['flash_message'] = 'User super_admin tidak dapat dihapus.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: admin/dashboard.php#panel-manajemen-staf');
    exit;
}

if (org_staff_users_delete_by_id($db, $userId)) {
    $_SESSION['flash_message'] = 'User berhasil dihapus.';
    $_SESSION['flash_type'] = 'success';
} else {
    $_SESSION['flash_message'] = 'Gagal menghapus user.';
    $_SESSION['flash_type'] = 'danger';
}

header('Location: admin/dashboard.php#panel-manajemen-staf');
exit;
