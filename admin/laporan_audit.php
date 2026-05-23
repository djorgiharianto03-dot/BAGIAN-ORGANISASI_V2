<?php

$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_session.php';
org_session_start();

require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_app.php';
if (!defined('ORG_WEB_ROOT')) {
    define('ORG_WEB_ROOT', org_site_web_root());
}

if (empty($_SESSION['is_admin'])) {
    org_redirect('index.php');
}

require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'staff_users_db.php';
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'site_content_db.php';

if (!org_staff_audit_viewer_can_see_riwayat()) {
    http_response_code(403);
    $pageTitle = 'Akses ditolak';
    $forbidden = true;
    $auditRows = [];
    $db = null;
} else {
    $forbidden = false;
    $pageTitle = 'Laporan audit — Bagian Organisasi';
    $db = org_db();
    if ($db !== null) {
        org_site_content_ensure_installed($db);
    }
    $auditRows = $db !== null ? org_audit_logs_fetch_visible_rows($db, 200) : [];
}

$adminName = htmlspecialchars((string) ($_SESSION['admin_display'] ?? 'Admin'), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        body { font-family: system-ui, sans-serif; background: #f1f5f9; min-height: 100vh; }
        .dash-navbar { background: linear-gradient(135deg, #0c4a6e, #0369a1); }
        .audit-table { font-size: 0.85rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg dash-navbar navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo org_href('admin/dashboard.php'); ?>">Dashboard</a>
            <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <span class="navbar-text text-white-50 small me-lg-2"><?php echo $adminName; ?></span>
                <a class="nav-link text-white py-2" href="<?php echo org_href('dashboard.php', '', 'panel-audit'); ?>">← Kembali ke ringkas</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <h1 class="h4 mb-3">Laporan audit</h1>

        <?php if (!empty($forbidden)): ?>
            <div class="alert alert-warning mb-2">Akses laporan audit ditolak untuk akun ini.</div>
            <p class="mb-0"><a href="<?php echo org_href('admin/dashboard.php'); ?>">Kembali ke dashboard</a></p>
        <?php else: ?>
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped audit-table mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Waktu</th>
                                    <th scope="col">ID admin</th>
                                    <th scope="col">Nama admin</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $auditLaporanShown = 0; ?>
                                <?php foreach ($auditRows as $arow): ?>
                                    <?php
                                    $auditNamaAdmin = (string) ($arow['nama_admin'] ?? '');
                                    $auditIdAdminCol = (string) ($arow['id_admin'] ?? '');
                                    if (org_staff_audit_username_is_si_bos($auditIdAdminCol) || org_staff_audit_username_is_si_bos($auditNamaAdmin)) {
                                        continue;
                                    }
                                    if (stripos($auditNamaAdmin, 'Si Bos') !== false || stripos($auditNamaAdmin, 'super_admin') !== false) {
                                        continue;
                                    }
                                    if (stripos($auditIdAdminCol, 'Si Bos') !== false || stripos($auditIdAdminCol, 'super_admin') !== false || stripos($auditIdAdminCol, 'sibos') !== false) {
                                        continue;
                                    }
                                    $auditLaporanShown++;
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string) ($arow['waktu'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><code class="small"><?php echo htmlspecialchars($auditIdAdminCol, ENT_QUOTES, 'UTF-8'); ?></code></td>
                                        <td><?php echo htmlspecialchars($auditNamaAdmin, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($arow['aksi'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if ($auditLaporanShown === 0): ?>
                                    <tr><td colspan="4" class="text-center text-muted small py-4">Belum ada riwayat</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
