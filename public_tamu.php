<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$pageTitle = 'Monitor Lobi — Buku Tamu';
$navActive = '';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'mode-eorganisasi';

$guestRows = [];
$db = org_db();
if ($db instanceof mysqli) {
    $tableCheck = $db->query("SHOW TABLES LIKE 'tamu'");
    if ($tableCheck !== false && $tableCheck->num_rows > 0) {
        $res = $db->query('SELECT * FROM `tamu` ORDER BY id DESC LIMIT 50');
        if ($res !== false) {
            while ($row = $res->fetch_assoc()) {
                if (is_array($row)) {
                    $guestRows[] = $row;
                }
            }
        }
    }
}

require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>
<div class="container site-main">
    <section class="section-spacing">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Monitor Lobi (Publik)</h1>
            <span class="small text-muted">Auto-refresh 30 detik</span>
        </div>
        <div class="card section-card">
            <div class="card-body p-3">
                <?php if (count($guestRows) === 0): ?>
                    <p class="text-muted mb-0">Belum ada data tamu.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>Instansi</th>
                                    <th>Tujuan</th>
                                    <th>Personel</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($guestRows as $guest): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string) ($guest['nama'] ?? $guest['nama_tamu'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($guest['instansi'] ?? $guest['asal_instansi'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($guest['tujuan_bertamu'] ?? $guest['unit_tujuan'] ?? $guest['bidang_tujuan'] ?? $guest['tujuan'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($guest['nama_personel'] ?? $guest['personel'] ?? $guest['personnel'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($guest['created_at'] ?? $guest['tanggal'] ?? $guest['tanggal_kunjungan'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>
<script>
setTimeout(function () { window.location.reload(); }, 30000);
</script>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
