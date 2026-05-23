<?php

$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_session.php';
org_session_start();

require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_app.php';
if (!defined('ORG_WEB_ROOT')) {
    define('ORG_WEB_ROOT', org_site_web_root());
}

$csrfToken = org_csrf_token();

if (empty($_SESSION['is_admin'])) {
    org_redirect('index.php');
}

require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'staff_users_db.php';
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'saran_kritik_db.php';

$roleNorm = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
if ($roleNorm === 'sub_admin_eorganisasi' || $roleNorm === 'sub_admin_publikasi') {
    org_redirect('admin/dashboard.php');
}

$db = org_db();
if ($db !== null) {
    org_saran_kritik_ensure_table($db);
}
$saranRows = $db !== null ? org_saran_kritik_fetch_all($db, 500) : [];
$tableReady = $db !== null && org_saran_kritik_table_exists($db);

$pageTitle = 'Saran & kritik pengunjung — Admin';
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
        .dash-navbar { background: linear-gradient(135deg, #0c4a6e, #0369a1); box-shadow: 0 2px 12px rgba(8, 47, 73, 0.2); }
        .dash-navbar .nav-link { color: rgba(255,255,255,0.92); font-weight: 500; }
        .dash-navbar .nav-link:hover { color: #fff; }
        .saran-table { font-size: 0.88rem; }
        .saran-table td { vertical-align: top; }
        .saran-pesan { max-width: 28rem; white-space: pre-wrap; word-break: break-word; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg dash-navbar navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo org_href('admin/dashboard.php'); ?>">Dashboard Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navSaran">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navSaran">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="<?php echo org_href('admin/dashboard.php'); ?>">Beranda admin</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars(org_home_url(), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">Situs publik</a></li>
                </ul>
                <span class="navbar-text text-white-50 small me-3"><?php echo $adminName; ?></span>
                <form method="post" action="<?php echo htmlspecialchars(org_home_url(), ENT_QUOTES, 'UTF-8'); ?>" class="d-inline mb-0">
                    <input type="hidden" name="action" value="logout">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h1 class="h4 mb-1">Saran &amp; kritik pengunjung</h1>
                <p class="text-muted small mb-0">Pesan dari formulir footer situs (pengiriman via AJAX).</p>
            </div>
            <a class="btn btn-outline-primary btn-sm" href="<?php echo org_href('admin/dashboard.php'); ?>"><i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Kembali ke dashboard</a>
        </div>

        <?php if ($db === null): ?>
            <div class="alert alert-warning">Tidak dapat terhubung ke database. Periksa <code>config/database.php</code>.</div>
        <?php elseif (!$tableReady): ?>
            <div class="alert alert-warning">Tabel <code>saran_kritik</code> tidak ditemukan. Muat ulang beranda publik sekali atau jalankan <code>cek_db.php</code> / <code>install/saran_kritik.sql</code>.</div>
        <?php elseif (count($saranRows) === 0): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 text-center text-muted">
                    <i class="fa-regular fa-comment-dots fa-2x mb-3 d-block opacity-50" aria-hidden="true"></i>
                    <p class="mb-0">Belum ada saran yang masuk.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover saran-table mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width: 3rem;">#</th>
                                    <th scope="col" style="width: 11rem;">Tanggal kirim</th>
                                    <th scope="col" style="width: 10rem;">Nama</th>
                                    <th scope="col" style="width: 12rem;">Email</th>
                                    <th scope="col">Pesan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($saranRows as $idx => $row): ?>
                                    <tr>
                                        <td><?php echo (int) ($row['id'] ?? 0); ?></td>
                                        <td class="text-nowrap small">
                                            <?php
                                            $ts = (string) ($row['tgl_kirim'] ?? $row['created_at'] ?? '');
                                            echo $ts !== ''
                                                ? htmlspecialchars(date('d/m/Y H:i', strtotime($ts)), ENT_QUOTES, 'UTF-8')
                                                : '—';
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars((string) ($row['nama'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><a class="link-primary text-break" href="mailto:<?php echo htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></a></td>
                                        <td class="saran-pesan"><?php echo htmlspecialchars((string) ($row['pesan'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light small text-muted border-0">
                    Menampilkan <?php echo count($saranRows); ?> entri terbaru.
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
