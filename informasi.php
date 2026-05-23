<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'portal_page_helpers.php';

$id = (int) ($_GET['id'] ?? 0);
$post = null;
if ($id > 0) {
    $dbInf = org_db();
    if ($dbInf instanceof mysqli) {
        org_pusat_informasi_ensure_table($dbInf);
        $post = org_pusat_informasi_fetch_by_id($dbInf, $id);
    }
}

if ($post === null) {
    http_response_code(404);
    $pageTitle = 'Tidak ditemukan — Bagian Organisasi';
    $navActive = 'beranda';
} else {
    $pageTitle = htmlspecialchars((string) ($post['judul'] ?? 'Informasi'), ENT_QUOTES, 'UTF-8') . ' — Pusat Informasi';
    $navActive = 'berita';
}

$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'mode-publikasi page-informasi-detail';
$extraHeadMarkup = '';
$extraFooterMarkup = '';
org_portal_apply_assets($bodyClass, $extraHeadMarkup, $extraFooterMarkup);

if ($post === null) {
    org_portal_set_hero(
        'Informasi tidak ditemukan',
        '',
        'Pusat Informasi',
        'fa-circle-exclamation',
        []
    );
} else {
    $pk = (string) ($post['kategori'] ?? 'berita');
    org_portal_set_hero(
        (string) ($post['judul'] ?? 'Detail Informasi'),
        '',
        'Pusat Informasi',
        $pk === 'pengumuman' ? 'fa-bullhorn' : 'fa-newspaper',
        []
    );
}

require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'portal_subpage_hero.php'; ?>

<div class="sg-portal-main-inner">
    <div class="container-global site-main section-spacing">
        <?php if ($post === null): ?>
            <div class="alert alert-warning">Informasi tidak ditemukan atau telah dihapus.</div>
            <p class="mb-0"><a href="<?php echo org_href('berita.php'); ?>">Kembali ke Pusat Informasi</a></p>
        <?php else: ?>
            <?php
            $pk = (string) ($post['kategori'] ?? 'berita');
            $isPeng = ($pk === 'pengumuman');
            $badgeClass = 'bg-danger';
            $badgeLabel = $isPeng ? 'Pengumuman' : 'Berita';
            $gf = trim((string) ($post['nama_gambar'] ?? ''));
            $imgU = $gf !== '' ? org_pusat_informasi_upload_web_prefix() . rawurlencode($gf) : '';
            $tgl = (string) ($post['created_at'] ?? '');
            $tglFmt = $tgl !== '' ? date('d/m/Y H:i', strtotime($tgl)) : '';
            ?>
            <nav class="mb-3" aria-label="Breadcrumb">
                <a class="small text-decoration-none" href="<?php echo org_href('berita.php'); ?>">&larr; Kembali ke Pusat Informasi &amp; Pengumuman</a>
            </nav>
            <article class="card border-0 shadow-sm overflow-hidden">
                <?php if ($imgU !== ''): ?>
                    <img src="<?php echo htmlspecialchars($imgU, ENT_QUOTES, 'UTF-8'); ?>" class="w-100 object-fit-cover" style="max-height: 22rem;" alt="">
                <?php endif; ?>
                <div class="card-body p-4 p-lg-5">
                    <span class="badge <?php echo htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8'); ?> mb-2"><?php echo htmlspecialchars($badgeLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if ($tglFmt !== ''): ?>
                        <p class="text-muted small mb-3"><?php echo htmlspecialchars($tglFmt, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                    <h1 class="h3 mb-4 text-dark"><?php echo htmlspecialchars((string) ($post['judul'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h1>
                    <div class="pi-detail-body text-secondary" style="line-height: 1.7;"><?php echo nl2br(htmlspecialchars((string) ($post['isi_teks'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></div>
                </div>
            </article>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
