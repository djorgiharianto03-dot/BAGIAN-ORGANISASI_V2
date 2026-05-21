<?php
declare(strict_types=1);
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'arsip_kategori_bagian.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'arsip_surat_db.php';

$__mdispRoleGate = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
if ($__mdispRoleGate === 'sub_admin_eorganisasi') {
    header('Location: disposisi_awal_kabag.php', true, 302);
    exit;
}
unset($__mdispRoleGate);

org_require_level_access(['super_admin', 'admin', 'staf_disposisi', 'kabag_organisasi']);

$pageTitle = 'Monitoring Disposisi (Role Baru) — Bagian Organisasi';
$navActive = 'e_organisasi';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'page-mdisp mode-eorganisasi';

function mdisp_norm_username(string $username): string
{
    return strtolower((string) preg_replace('/\s+/u', '', trim($username)));
}

function mdisp_is_super_admin_sibos(string $username): bool
{
    return mdisp_norm_username($username) === 'sibos';
}

function mdisp_is_kabag(string $username): bool
{
    return org_staff_username_is_kabag_organisasi($username);
}

/**
 * @param array<string, mixed> $userRow baris users (fetch all / by username)
 */
function mdisp_user_is_staf_disposisi_role(array $userRow): bool
{
    return org_staff_role_normalize((string) ($userRow['level'] ?? '')) === 'staf_disposisi';
}

/** Urutan aman untuk SELECT surat_disposisi (kolom created_at boleh tidak ada). */
function mdisp_surat_disposisi_order_by_clause(mysqli $db): string
{
    $res = $db->query('SHOW COLUMNS FROM `surat_disposisi`');
    if ($res === false) {
        return '`id` DESC';
    }
    $hasCreated = false;
    while ($c = $res->fetch_assoc()) {
        if (strtolower(trim((string) ($c['Field'] ?? ''))) === 'created_at') {
            $hasCreated = true;
            break;
        }
    }
    $res->free();

    return $hasCreated ? '`created_at` DESC' : '`id` DESC';
}

/**
 * Pastikan kolom penanda verifikasi selesai oleh Kabag untuk baris tugas staf ada di surat_disposisi.
 */
function mdisp_ensure_surat_disposisi_kabag_tandai_selesai(mysqli $db): bool
{
    static $state = null;
    if ($state === true) {
        return true;
    }
    if ($state === false) {
        return false;
    }
    $res = $db->query("SHOW COLUMNS FROM `surat_disposisi` LIKE 'kabag_tandai_selesai'");
    if ($res && $res->num_rows > 0) {
        $res->free();
        $state = true;

        return true;
    }
    if ($res) {
        $res->free();
    }
    $alterOk = $db->query("ALTER TABLE `surat_disposisi` ADD COLUMN `kabag_tandai_selesai` TINYINT(1) NOT NULL DEFAULT 0");
    if ($alterOk) {
        $state = true;

        return true;
    }
    $state = false;

    return false;
}

/**
 * Apakah kolom kabag_tandai_selesai ada (hanya baca; tidak ALTER).
 */
function mdisp_surat_disposisi_kabag_tandai_column_exists(mysqli $db): bool
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $res = $db->query("SHOW COLUMNS FROM `surat_disposisi` LIKE 'kabag_tandai_selesai'");
    $cache = ($res !== false && $res->num_rows > 0);
    if ($res) {
        $res->free();
    }

    return $cache;
}

/**
 * Untuk tampilan penerima: sembunyikan baris selesai sampai Kabag menandai verifikasi (kabag_tandai_selesai = 1).
 *
 * @param array<string, mixed> $r
 */
function mdisp_dispo_row_visible_to_penerima(array $r, bool $kabagTandaiColExists): bool
{
    if (!empty($r['__mdisp_legacy'])) {
        return true;
    }
    if (!$kabagTandaiColExists) {
        return true;
    }
    $penerima = (string) ($r['penerima_username'] ?? '');
    if (mdisp_is_kabag($penerima)) {
        return true;
    }
    $st = strtolower(trim((string) ($r['status'] ?? '')));
    if ($st !== 'selesai') {
        return true;
    }

    return (int) ($r['kabag_tandai_selesai'] ?? 0) === 1;
}

/** Urutan untuk tabel legacy dispositions. */
function mdisp_dispositions_order_by_clause(mysqli $db): string
{
    $res = $db->query('SHOW COLUMNS FROM `dispositions`');
    if ($res === false) {
        return '`id` DESC';
    }
    $hasCreated = false;
    while ($c = $res->fetch_assoc()) {
        if (strtolower(trim((string) ($c['Field'] ?? ''))) === 'created_at') {
            $hasCreated = true;
            break;
        }
    }
    $res->free();

    return $hasCreated ? '`created_at` DESC' : '`id` DESC';
}

/**
 * Kumpulkan ID baris disposisi akar dan semua turunan (berantai parent_id).
 *
 * @return list<int>
 */
function mdisp_collect_disposisi_subtree_ids(mysqli $db, int $rootId): array
{
    if ($rootId <= 0) {
        return [];
    }
    $st0 = $db->prepare('SELECT 1 FROM `surat_disposisi` WHERE `id` = ? LIMIT 1');
    if ($st0 === false) {
        return [];
    }
    $st0->bind_param('i', $rootId);
    $st0->execute();
    $r0 = $st0->get_result();
    $exists = $r0 !== false && $r0->num_rows > 0;
    $st0->close();
    if (!$exists) {
        return [];
    }

    $seen = [];
    $queue = [$rootId];
    while ($queue !== []) {
        $cur = (int) array_shift($queue);
        if ($cur <= 0 || isset($seen[$cur])) {
            continue;
        }
        $seen[$cur] = true;
        $st = $db->prepare('SELECT `id` FROM `surat_disposisi` WHERE `parent_id` = ?');
        if ($st === false) {
            break;
        }
        $st->bind_param('i', $cur);
        $st->execute();
        $res = $st->get_result();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $cid = (int) ($row['id'] ?? 0);
                if ($cid > 0 && !isset($seen[$cid])) {
                    $queue[] = $cid;
                }
            }
        }
        $st->close();
    }

    return array_map('intval', array_keys($seen));
}

function mdisp_safe_unlink_bukti_file(string $relPath, string $projectRoot): void
{
    $relPath = trim(str_replace('\\', '/', $relPath));
    if ($relPath === '' || str_contains($relPath, '..')) {
        return;
    }
    if (preg_match('#^uploads/disposisi_bukti/[^/]+$#', $relPath) !== 1) {
        return;
    }
    $full = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
    if (is_file($full)) {
        @unlink($full);
    }
}

/**
 * Hapus baris surat_disposisi untuk himpunan ID tertentu (urutan daun → akar di dalam himpunan).
 *
 * @param list<int> $ids
 */
function mdisp_delete_disposisi_ids_leafwise(mysqli $db, array $ids, string $projectRoot): void
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $x): bool => $x > 0)));
    if ($ids === []) {
        return;
    }

    $remaining = array_flip($ids);
    $safety = 0;
    while ($remaining !== []) {
        ++$safety;
        if ($safety > 5000) {
            throw new RuntimeException('Terlalu banyak iterasi saat menghapus.');
        }
        $idArr = array_keys($remaining);
        $inList = implode(',', array_map(static fn (int $x): string => (string) $x, $idArr));
        $parentsWithChildInSet = [];
        $sqlPc = 'SELECT DISTINCT `parent_id` AS p FROM `surat_disposisi` WHERE `id` IN (' . $inList . ') AND `parent_id` IS NOT NULL AND `parent_id` IN (' . $inList . ')';
        $resP = $db->query($sqlPc);
        if ($resP === false) {
            throw new RuntimeException((string) $db->error);
        }
        while ($rp = $resP->fetch_assoc()) {
            $pv = (int) ($rp['p'] ?? 0);
            if ($pv > 0) {
                $parentsWithChildInSet[$pv] = true;
            }
        }
        $resP->free();

        $leaves = [];
        foreach ($idArr as $id) {
            if (!isset($parentsWithChildInSet[$id])) {
                $leaves[] = $id;
            }
        }
        if ($leaves === []) {
            throw new RuntimeException('Urutan hapus tidak valid (kemungkinan data rusak).');
        }

        foreach ($leaves as $delId) {
            $stF = $db->prepare('SELECT `file_bukti` FROM `surat_disposisi` WHERE `id` = ? LIMIT 1');
            if ($stF === false) {
                throw new RuntimeException((string) $db->error);
            }
            $stF->bind_param('i', $delId);
            $stF->execute();
            $rf = $stF->get_result();
            $rowF = $rf ? $rf->fetch_assoc() : null;
            $stF->close();
            $fb = trim((string) ($rowF['file_bukti'] ?? ''));
            if ($fb !== '') {
                mdisp_safe_unlink_bukti_file($fb, $projectRoot);
            }

            $stD = $db->prepare('DELETE FROM `surat_disposisi` WHERE `id` = ? LIMIT 1');
            if ($stD === false) {
                throw new RuntimeException((string) $db->error);
            }
            $stD->bind_param('i', $delId);
            if (!$stD->execute()) {
                $err = (string) $stD->error;
                $stD->close();
                throw new RuntimeException($err !== '' ? $err : 'Gagal menghapus baris.');
            }
            $stD->close();
            unset($remaining[$delId]);
        }
    }
}

/**
 * @return array{0:bool,1:string,2:string} [sukses, pesan, tipe alert]
 */
function mdisp_admin_delete_disposisi_subtree(mysqli $db, int $rootId, string $projectRoot): array
{
    $ids = mdisp_collect_disposisi_subtree_ids($db, $rootId);
    if ($ids === [] || !in_array($rootId, $ids, true)) {
        return [false, 'Disposisi tidak ditemukan.', 'warning'];
    }

    $db->begin_transaction();
    try {
        mdisp_delete_disposisi_ids_leafwise($db, $ids, $projectRoot);
        $db->commit();

        return [true, 'Baris disposisi beserta turunannya (jika ada) telah dihapus.', 'success'];
    } catch (Throwable $e) {
        $db->rollback();

        return [false, 'Gagal menghapus: ' . $e->getMessage(), 'danger'];
    }
}

/**
 * @return array<string, true>
 */
function mdisp_arsip_surat_column_set(mysqli $db): array
{
    static $cache = null;
    if (is_array($cache)) {
        return $cache;
    }
    $cache = [];
    $res = $db->query('SHOW COLUMNS FROM `arsip_surat`');
    if ($res) {
        while ($c = $res->fetch_assoc()) {
            $f = strtolower(trim((string) ($c['Field'] ?? '')));
            if ($f !== '') {
                $cache[$f] = true;
            }
        }
        $res->free();
    }

    return $cache;
}

function mdisp_row_is_arsip_masuk(array $row): bool
{
    $jenisRaw = strtolower(trim((string) ($row['jenis_surat'] ?? $row['jenis'] ?? $row['tipe'] ?? '')));

    return $jenisRaw === '' || $jenisRaw === 'masuk';
}

function mdisp_row_is_arsip_keluar(array $row): bool
{
    $jenisRaw = strtolower(trim((string) ($row['jenis_surat'] ?? $row['jenis'] ?? $row['tipe'] ?? '')));

    return $jenisRaw === 'keluar';
}

/**
 * @param callable(array):bool $rowAllowed
 * @return array{0:bool,1:string,2:string}
 */
function mdisp_admin_update_arsip_row_metadata(
    mysqli $db,
    int $idArsip,
    string $nomorSurat,
    string $perihal,
    string $instansiAsal,
    string $instansiTujuan,
    callable $rowAllowed,
    string $notFoundLabel,
    ?string $kategoriBagian = null
): array {
    if ($idArsip <= 0) {
        return [false, 'ID arsip tidak valid.', 'warning'];
    }

    $st = $db->prepare('SELECT * FROM `arsip_surat` WHERE `id` = ? LIMIT 1');
    if ($st === false) {
        return [false, 'Gagal memuat data arsip.', 'danger'];
    }
    $st->bind_param('i', $idArsip);
    $st->execute();
    $rs = $st->get_result();
    $row = $rs ? $rs->fetch_assoc() : null;
    $st->close();
    if (!is_array($row) || !$rowAllowed($row)) {
        return [false, $notFoundLabel, 'warning'];
    }

    $cols = mdisp_arsip_surat_column_set($db);
    $sets = [];
    $types = '';
    $params = [];

    $hasNomorCol = isset($cols['nomor_surat']) || isset($cols['nomor']);
    if (!$hasNomorCol) {
        return [false, 'Tabel arsip tidak memiliki kolom nomor surat (nomor_surat atau nomor).', 'warning'];
    }

    $nomorSurat = trim($nomorSurat);
    if ($nomorSurat === '') {
        return [false, 'Nomor surat wajib diisi.', 'warning'];
    }
    if (isset($cols['nomor_surat'])) {
        $sets[] = '`nomor_surat` = ?';
        $types .= 's';
        $params[] = $nomorSurat;
    } elseif (isset($cols['nomor'])) {
        $sets[] = '`nomor` = ?';
        $types .= 's';
        $params[] = $nomorSurat;
    }

    $perihal = trim($perihal);
    if (isset($cols['perihal_ringkasan'])) {
        $sets[] = '`perihal_ringkasan` = ?';
        $types .= 's';
        $params[] = $perihal;
    } elseif (isset($cols['perihal'])) {
        $sets[] = '`perihal` = ?';
        $types .= 's';
        $params[] = $perihal;
    }

    $instansiAsal = trim($instansiAsal);
    if (isset($cols['instansi_asal'])) {
        $sets[] = '`instansi_asal` = ?';
        $types .= 's';
        $params[] = $instansiAsal;
    } elseif (isset($cols['asal_surat'])) {
        $sets[] = '`asal_surat` = ?';
        $types .= 's';
        $params[] = $instansiAsal;
    }

    $instansiTujuan = trim($instansiTujuan);
    if (isset($cols['instansi_tujuan'])) {
        $sets[] = '`instansi_tujuan` = ?';
        $types .= 's';
        $params[] = $instansiTujuan;
    } elseif (isset($cols['tujuan_surat'])) {
        $sets[] = '`tujuan_surat` = ?';
        $types .= 's';
        $params[] = $instansiTujuan;
    }

    if ($kategoriBagian !== null && isset($cols['kategori_bagian'])) {
        $kb = trim($kategoriBagian);
        if ($kb !== '') {
            $allowedKb = org_arsip_kategori_bagian_map();
            if (!array_key_exists($kb, $allowedKb)) {
                return [false, 'Kategori bagian tidak valid.', 'warning'];
            }
            $sets[] = '`kategori_bagian` = ?';
            $types .= 's';
            $params[] = $kb;
        } else {
            $sets[] = '`kategori_bagian` = NULL';
        }
    }

    if ($sets === []) {
        return [false, 'Tabel arsip tidak memiliki kolom metadata yang didukung untuk diedit (nomor/perihal/instansi).', 'warning'];
    }

    $sql = 'UPDATE `arsip_surat` SET ' . implode(', ', $sets) . ' WHERE `id` = ? LIMIT 1';
    $types .= 'i';
    $params[] = $idArsip;
    $stU = $db->prepare($sql);
    if ($stU === false) {
        return [false, 'Gagal menyiapkan pembaruan arsip.', 'danger'];
    }
    $stU->bind_param($types, ...$params);
    if (!$stU->execute()) {
        $err = (string) $stU->error;
        $stU->close();

        return [false, $err !== '' ? $err : 'Gagal memperbarui arsip.', 'danger'];
    }
    $aff = $stU->affected_rows;
    $stU->close();

    return [true, $aff > 0 ? 'Data arsip berhasil diperbarui.' : 'Tidak ada perubahan yang disimpan (nilai sama seperti sebelumnya).', $aff > 0 ? 'success' : 'info'];
}

/**
 * @return array{0:bool,1:string,2:string}
 */
function mdisp_admin_update_arsip_masuk_metadata(
    mysqli $db,
    int $idArsip,
    string $nomorSurat,
    string $perihal,
    string $instansiAsal,
    string $instansiTujuan,
    ?string $kategoriBagian = null
): array {
    return mdisp_admin_update_arsip_row_metadata(
        $db,
        $idArsip,
        $nomorSurat,
        $perihal,
        $instansiAsal,
        $instansiTujuan,
        static fn (array $row): bool => mdisp_row_is_arsip_masuk($row),
        'Arsip surat masuk tidak ditemukan.',
        $kategoriBagian
    );
}

/**
 * @return array{0:bool,1:string,2:string}
 */
function mdisp_admin_update_arsip_keluar_metadata(
    mysqli $db,
    int $idArsip,
    string $nomorSurat,
    string $perihal,
    string $instansiAsal,
    string $instansiTujuan,
    ?string $kategoriBagian = null
): array {
    return mdisp_admin_update_arsip_row_metadata(
        $db,
        $idArsip,
        $nomorSurat,
        $perihal,
        $instansiAsal,
        $instansiTujuan,
        static fn (array $row): bool => mdisp_row_is_arsip_keluar($row),
        'Arsip surat keluar tidak ditemukan.',
        $kategoriBagian
    );
}

/**
 * @return array{0:bool,1:string,2:string}
 */
function mdisp_admin_delete_arsip_masuk(mysqli $db, int $idArsip, string $projectRoot): array
{
    if ($idArsip <= 0) {
        return [false, 'ID arsip tidak valid.', 'warning'];
    }

    $st = $db->prepare('SELECT * FROM `arsip_surat` WHERE `id` = ? LIMIT 1');
    if ($st === false) {
        return [false, 'Gagal memuat data arsip.', 'danger'];
    }
    $st->bind_param('i', $idArsip);
    $st->execute();
    $rs = $st->get_result();
    $row = $rs ? $rs->fetch_assoc() : null;
    $st->close();
    if (!is_array($row) || !mdisp_row_is_arsip_masuk($row)) {
        return [false, 'Hanya arsip surat masuk yang dapat dihapus dari daftar ini.', 'warning'];
    }

    $dispoIds = [];
    $stL = $db->prepare('SELECT `id` FROM `surat_disposisi` WHERE `id_arsip` = ?');
    if ($stL === false) {
        return [false, 'Gagal memuat disposisi terkait.', 'danger'];
    }
    $stL->bind_param('i', $idArsip);
    $stL->execute();
    $rL = $stL->get_result();
    if ($rL) {
        while ($lr = $rL->fetch_assoc()) {
            $ix = (int) ($lr['id'] ?? 0);
            if ($ix > 0) {
                $dispoIds[] = $ix;
            }
        }
    }
    $stL->close();

    $base = org_arsip_surat_row_display_filename($row);
    $pdfPath = $projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'surat_masuk' . DIRECTORY_SEPARATOR . $base;

    $db->begin_transaction();
    try {
        mdisp_delete_disposisi_ids_leafwise($db, $dispoIds, $projectRoot);

        $stD = $db->prepare('DELETE FROM `arsip_surat` WHERE `id` = ? LIMIT 1');
        if ($stD === false) {
            throw new RuntimeException((string) $db->error);
        }
        $stD->bind_param('i', $idArsip);
        if (!$stD->execute() || $stD->affected_rows < 1) {
            $err = (string) $stD->error;
            $stD->close();
            throw new RuntimeException($err !== '' ? $err : 'Gagal menghapus baris arsip.');
        }
        $stD->close();

        $db->commit();

        if ($base !== '' && $base !== '.' && $base !== '..' && is_file($pdfPath)) {
            @unlink($pdfPath);
        }

        return [true, 'Arsip surat masuk dan seluruh disposisi terkait telah dihapus.', 'success'];
    } catch (Throwable $e) {
        $db->rollback();

        return [false, 'Gagal menghapus arsip: ' . $e->getMessage(), 'danger'];
    }
}

/**
 * @return array{0:bool,1:string,2:string}
 */
function mdisp_admin_delete_arsip_keluar(mysqli $db, int $idArsip, string $projectRoot): array
{
    if ($idArsip <= 0) {
        return [false, 'ID arsip tidak valid.', 'warning'];
    }

    $st = $db->prepare('SELECT * FROM `arsip_surat` WHERE `id` = ? LIMIT 1');
    if ($st === false) {
        return [false, 'Gagal memuat data arsip.', 'danger'];
    }
    $st->bind_param('i', $idArsip);
    $st->execute();
    $rs = $st->get_result();
    $row = $rs ? $rs->fetch_assoc() : null;
    $st->close();
    if (!is_array($row) || !mdisp_row_is_arsip_keluar($row)) {
        return [false, 'Hanya arsip surat keluar yang dapat dihapus dari daftar ini.', 'warning'];
    }

    $dispoIds = [];
    $stL = $db->prepare('SELECT `id` FROM `surat_disposisi` WHERE `id_arsip` = ?');
    if ($stL === false) {
        return [false, 'Gagal memuat disposisi terkait.', 'danger'];
    }
    $stL->bind_param('i', $idArsip);
    $stL->execute();
    $rL = $stL->get_result();
    if ($rL) {
        while ($lr = $rL->fetch_assoc()) {
            $ix = (int) ($lr['id'] ?? 0);
            if ($ix > 0) {
                $dispoIds[] = $ix;
            }
        }
    }
    $stL->close();

    $base = org_arsip_surat_row_display_filename($row);
    $pdfPath = $projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'surat_keluar' . DIRECTORY_SEPARATOR . $base;

    $db->begin_transaction();
    try {
        mdisp_delete_disposisi_ids_leafwise($db, $dispoIds, $projectRoot);

        $stD = $db->prepare('DELETE FROM `arsip_surat` WHERE `id` = ? LIMIT 1');
        if ($stD === false) {
            throw new RuntimeException((string) $db->error);
        }
        $stD->bind_param('i', $idArsip);
        if (!$stD->execute() || $stD->affected_rows < 1) {
            $err = (string) $stD->error;
            $stD->close();
            throw new RuntimeException($err !== '' ? $err : 'Gagal menghapus baris arsip.');
        }
        $stD->close();

        $db->commit();

        if ($base !== '' && $base !== '.' && $base !== '..' && is_file($pdfPath)) {
            @unlink($pdfPath);
        }

        return [true, 'Arsip surat keluar dan seluruh disposisi terkait telah dihapus.', 'success'];
    } catch (Throwable $e) {
        $db->rollback();

        return [false, 'Gagal menghapus arsip: ' . $e->getMessage(), 'danger'];
    }
}

function mdisp_monitoring_collapse_dom_id(string $bucketKey): string
{
    $safe = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $bucketKey);
    if ($safe === '') {
        $safe = 'grp';
    }

    return 'mdispMonCollapse-' . $safe;
}

/**
 * @return array{label: string, class: string}
 */
function mdisp_status_badge_meta(string $status): array
{
    $st = strtolower(trim($status));
    $map = [
        'selesai' => ['label' => 'Selesai', 'class' => 'mdisp-badge mdisp-badge--selesai'],
        'fix' => ['label' => 'Selesai', 'class' => 'mdisp-badge mdisp-badge--selesai'],
        'pending' => ['label' => 'Pending', 'class' => 'mdisp-badge mdisp-badge--pending'],
        'diterima' => ['label' => 'Proses', 'class' => 'mdisp-badge mdisp-badge--proses'],
        'dikerjakan' => ['label' => 'Proses', 'class' => 'mdisp-badge mdisp-badge--proses'],
        'revisi' => ['label' => 'Proses', 'class' => 'mdisp-badge mdisp-badge--proses'],
    ];
    if (isset($map[$st])) {
        return $map[$st];
    }

    return [
        'label' => $st !== '' ? ucfirst($st) : '—',
        'class' => 'mdisp-badge mdisp-badge--neutral',
    ];
}

function mdisp_pdf_pill_link(string $href, string $label = 'Lihat PDF arsip'): string
{
    if ($href === '') {
        return '';
    }

    return '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8')
        . '" target="_blank" rel="noopener" class="mdisp-pdf-pill">'
        . '<i class="fa-solid fa-file-pdf" aria-hidden="true"></i>'
        . '<span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span></a>';
}

/**
 * @param list<array<string, mixed>> $dispoRows
 *
 * @return list<array{key: string, id_arsip: int, rows: list<array<string, mixed>>}>
 */
function mdisp_monitoring_build_dispo_groups(array $dispoRows): array
{
    $buckets = [];
    foreach ($dispoRows as $d) {
        if (!is_array($d)) {
            continue;
        }
        $isLegacy = !empty($d['__mdisp_legacy']);
        $rid = (int) ($d['id'] ?? 0);
        $idArsip = (int) ($d['id_arsip'] ?? 0);
        if ($isLegacy) {
            $key = 'legacy-' . ($rid > 0 ? $rid : 'x');
        } elseif ($idArsip > 0) {
            $key = 'arsip-' . $idArsip;
        } else {
            $key = 'row-' . ($rid > 0 ? $rid : 'x');
        }
        if (!isset($buckets[$key])) {
            $buckets[$key] = ['key' => $key, 'id_arsip' => $idArsip, 'rows' => []];
        }
        $buckets[$key]['rows'][] = $d;
        if (($buckets[$key]['id_arsip'] ?? 0) <= 0 && $idArsip > 0) {
            $buckets[$key]['id_arsip'] = $idArsip;
        }
    }
    foreach ($buckets as &$b) {
        usort($b['rows'], static function (array $a, array $b): int {
            return (int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0);
        });
    }
    unset($b);
    $list = array_values($buckets);
    usort($list, static function (array $a, array $b): int {
        $maxA = 0;
        foreach ($a['rows'] as $r) {
            $maxA = max($maxA, (int) ($r['id'] ?? 0));
        }
        $maxB = 0;
        foreach ($b['rows'] as $r) {
            $maxB = max($maxB, (int) ($r['id'] ?? 0));
        }

        return $maxB <=> $maxA;
    });

    return $list;
}

/**
 * Satu baris data disposisi di tab Monitoring (dipakai di tabel dalam collapse).
 *
 * @param array<string, mixed> $d
 * @param array<int|string, array<string, mixed>> $arsipById
 */
function mdisp_render_monitoring_dispo_data_row(
    array $d,
    array $arsipById,
    string $sessionUser,
    bool $isDisposisiOnlyUser,
    bool $isKabag,
    bool $omitSuratDetailColumn
): void {
    $did = (int) ($d['id'] ?? 0);
    $idArsip = (int) ($d['id_arsip'] ?? 0);
    $isLegacyDispo = !empty($d['__mdisp_legacy']);
    $arsipRef = $arsipById[$idArsip] ?? [];
    $st = trim((string) ($d['status'] ?? ''));
    $pengirim = trim((string) ($d['pengirim_username'] ?? ''));
    $penerima = trim((string) ($d['penerima_username'] ?? ''));
    $isMyTask = $isDisposisiOnlyUser && strcasecmp($penerima, $sessionUser) === 0;
    $canTerimaOrUploadBukti = !$isLegacyDispo && strcasecmp($penerima, $sessionUser) === 0 && ($isDisposisiOnlyUser || $isKabag);
    $kabagVerif = (int) ($d['kabag_tandai_selesai'] ?? 0) === 1;
    $isStafDispoRow = $penerima !== '' && !mdisp_is_kabag($penerima) && !mdisp_is_super_admin_sibos($penerima);
    $statusMeta = mdisp_status_badge_meta($st);
    $jenisLabel = strtolower((string) ($arsipRef['jenis_surat'] ?? $arsipRef['jenis'] ?? ''));
    $namaFile = org_arsip_surat_row_display_filename($arsipRef);
    $nomorArsip = trim((string) ($arsipRef['nomor_surat'] ?? $arsipRef['nomor'] ?? ''));
    $perihalFull = trim((string) ($arsipRef['perihal_ringkasan'] ?? $arsipRef['perihal'] ?? ''));
    $asalArsip = trim((string) ($arsipRef['instansi_asal'] ?? $arsipRef['asal_surat'] ?? ''));
    $perihalShort = $perihalFull;
    if ($perihalShort !== '') {
        $maxP = 160;
        if (function_exists('mb_strlen') && mb_strlen($perihalShort, 'UTF-8') > $maxP) {
            $perihalShort = mb_substr($perihalShort, 0, $maxP - 1, 'UTF-8') . '…';
        } elseif (strlen($perihalShort) > $maxP) {
            $perihalShort = substr($perihalShort, 0, $maxP - 3) . '...';
        }
    }
    $pdfArsipHref = $arsipRef !== [] ? org_arsip_surat_row_pdf_web_path($arsipRef) : null;
    echo '<tr id="mdisp-dispo-', (string) $did, '" class="mdisp-dispo-row mdisp-dispo-row--child', ($st === 'revisi' ? ' mdisp-dispo-row--revisi' : ''), '">';
    echo '<td>', (string) $did, '</td>';
    if ($omitSuratDetailColumn) {
        echo '<td class="small text-muted">Ringkasan surat ada di baris induk di atas.</td>';
    } else {
        echo '<td class="small" style="min-width: 14rem; max-width: 22rem;">';
        echo '<div class="d-flex flex-wrap align-items-center gap-1 mb-1">';
        echo '<span class="badge text-bg-light text-dark border">', htmlspecialchars($jenisLabel !== '' ? $jenisLabel : '-', ENT_QUOTES, 'UTF-8'), '</span>';
        echo '<span class="fw-medium">', htmlspecialchars($namaFile !== '' ? $namaFile : ('Arsip #' . $idArsip), ENT_QUOTES, 'UTF-8'), '</span>';
        echo '</div>';
        if ($nomorArsip !== '') {
            echo '<div class="text-muted mb-0"><span class="text-uppercase" style="font-size: 0.7rem;">Nomor</span> ', htmlspecialchars($nomorArsip, ENT_QUOTES, 'UTF-8'), '</div>';
        }
        if ($perihalShort !== '') {
            echo '<div class="text-body-secondary mb-0" title="', htmlspecialchars($perihalFull, ENT_QUOTES, 'UTF-8'), '">', htmlspecialchars($perihalShort, ENT_QUOTES, 'UTF-8'), '</div>';
        }
        if ($asalArsip !== '') {
            echo '<div class="text-muted mb-0" style="font-size: 0.8rem;">Asal: ', htmlspecialchars($asalArsip, ENT_QUOTES, 'UTF-8'), '</div>';
        }
        if ($pdfArsipHref !== null && $pdfArsipHref !== '') {
            echo '<div class="mt-2">', mdisp_pdf_pill_link($pdfArsipHref), '</div>';
        }
        if ($idArsip > 0 && $arsipRef === []) {
            echo '<div class="text-warning small mt-1">Meta arsip tidak ditemukan (ID ', (string) $idArsip, ').</div>';
        }
        echo '</td>';
    }
    echo '<td>', htmlspecialchars($pengirim, ENT_QUOTES, 'UTF-8'), '</td>';
    echo '<td>', htmlspecialchars($penerima, ENT_QUOTES, 'UTF-8'), '</td>';
    echo '<td class="small">', nl2br(htmlspecialchars((string) ($d['instruksi'] ?? ''), ENT_QUOTES, 'UTF-8')), '</td>';
    echo '<td><span class="', htmlspecialchars($statusMeta['class'], ENT_QUOTES, 'UTF-8'), '">', htmlspecialchars($statusMeta['label'], ENT_QUOTES, 'UTF-8'), '</span>';
    if ($kabagVerif) {
        echo '<div class="small text-success fw-semibold mt-1" role="status">Selesai diverifikasi Kabag</div>';
    } elseif ($st === 'selesai' && $isStafDispoRow && !$kabagVerif) {
        echo '<div class="small text-muted mt-1" role="status">';
        if ($isKabag) {
            echo 'Staf menunggu penanda selesai dari Anda (tombol di kolom Aksi).';
        } elseif ($isMyTask) {
            echo 'Menunggu tanda selesai dari Kabag.';
        } else {
            echo 'Menunggu verifikasi penutupan Kabag.';
        }
        echo '</div>';
    }
    echo '</td>';
    echo '<td class="small">', nl2br(htmlspecialchars((string) ($d['catatan_kabag'] ?? '-'), ENT_QUOTES, 'UTF-8')), '</td>';
    $fb = trim((string) ($d['file_bukti'] ?? ''));
    echo '<td>';
    if ($fb !== '') {
        echo '<a href="', htmlspecialchars($fb, ENT_QUOTES, 'UTF-8'), '" target="_blank" rel="noopener">Lihat</a>';
    } else {
        echo '—';
    }
    echo '</td>';
    echo '<td class="text-end text-nowrap">';
    $kabagSebagaiPenerima = mdisp_is_kabag($penerima);
    if ($isKabag && !$isLegacyDispo) {
        $showTlKeStaf = $kabagSebagaiPenerima;
        $showTandaiSelesaiStaf = $st === 'selesai' && $isStafDispoRow && !$kabagVerif;
        $showMintaRevisi = $isStafDispoRow && in_array($st, ['selesai', 'diterima', 'dikerjakan'], true);
        if ($showTlKeStaf || $showTandaiSelesaiStaf || $showMintaRevisi) {
            echo '<div class="d-inline-flex flex-column align-items-end gap-1">';
            if ($showTlKeStaf) {
                echo '<button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalTL', (string) $did, '">Tindak Lanjuti ke Staf</button>';
            }
            if ($showTandaiSelesaiStaf) {
                echo '<form method="post" class="mb-0" onsubmit="return confirm(\'Tandai tugas ini selesai diverifikasi Kabag? Staf akan melihat tanda pada daftar monitoring.\');">';
                echo '<input type="hidden" name="csrf_token" value="', htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'), '">';
                echo '<input type="hidden" name="mdisp_action" value="mdisp_kabag_tandai_selesai_staf">';
                echo '<input type="hidden" name="id_disp" value="', (string) $did, '">';
                echo '<button type="submit" class="btn btn-sm btn-success">Tandai selesai ke staf</button></form>';
            }
            if ($showMintaRevisi) {
                echo '<button class="btn btn-sm btn-outline-warning" type="button" data-bs-toggle="modal" data-bs-target="#modalRevisi', (string) $did, '">Minta perbaikan</button>';
            }
            echo '</div>';
        }
    }
    if ($canTerimaOrUploadBukti) {
        if ($st === 'pending') {
            echo '<form method="post" class="d-inline">';
            echo '<input type="hidden" name="csrf_token" value="', htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'), '">';
            echo '<input type="hidden" name="mdisp_action" value="mdisp_terima">';
            echo '<input type="hidden" name="id_disp" value="', (string) $did, '">';
            echo '<button type="submit" class="btn btn-sm btn-success">Terima</button></form>';
        }
        if (in_array($st, ['diterima', 'dikerjakan', 'revisi'], true)) {
            echo '<button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalUP', (string) $did, '">Upload Bukti</button>';
        }
    }
    echo '</td></tr>';
}

$db = org_db();
$sessionUser = trim((string) ($_SESSION['admin_username'] ?? ''));
$sessionRoleNorm = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));
$isSuper = mdisp_is_super_admin_sibos($sessionUser);
$isKabag = mdisp_is_kabag($sessionUser);
$isDisposisiOnlyUser = $sessionRoleNorm === 'staf_disposisi';
/** Input disposisi awal surat masuk: Sub Admin E-Organisasi, Admin, Super Admin — bukan akun sibos/Kabag, bukan Staf Disposisi. */
$canInputDisposisiAwal = !mdisp_is_super_admin_sibos($sessionUser)
    && !mdisp_is_kabag($sessionUser)
    && in_array($sessionRoleNorm, ['admin', 'super_admin'], true);
$canAdminDeleteDispo = in_array($sessionRoleNorm, ['super_admin', 'admin'], true);
$showMasukAksiCol = $canInputDisposisiAwal || $canAdminDeleteDispo;
$showKeluarAksiCol = $canAdminDeleteDispo;

$tab = strtolower(trim((string) ($_GET['tab'] ?? 'monitoring')));
if (!in_array($tab, ['masuk', 'keluar', 'monitoring'], true)) {
    $tab = 'monitoring';
}
if ($isDisposisiOnlyUser) {
    $tab = 'monitoring';
}

$mdispScrollIdDisp = (int) ($_GET['id_disp'] ?? 0);
$mdispScrollIdArsip = (int) ($_GET['id_arsip'] ?? 0);

$message = '';
$messageType = 'info';

$hasArsipTable = false;
$hasDispoTable = false;
$hasDispositionsLegacy = false;
$hasSuratLegacy = false;
if ($db instanceof mysqli) {
    $rA = $db->query("SHOW TABLES LIKE 'arsip_surat'");
    $rD = $db->query("SHOW TABLES LIKE 'surat_disposisi'");
    $rLeg = $db->query("SHOW TABLES LIKE 'dispositions'");
    $rSur = $db->query("SHOW TABLES LIKE 'surat'");
    $hasArsipTable = $rA !== false && $rA->num_rows > 0;
    $hasDispoTable = $rD !== false && $rD->num_rows > 0;
    $hasDispositionsLegacy = $rLeg !== false && $rLeg->num_rows > 0;
    $hasSuratLegacy = $rSur !== false && $rSur->num_rows > 0;
    if ($rA) {
        $rA->free();
    }
    if ($rD) {
        $rD->free();
    }
    if ($rLeg) {
        $rLeg->free();
    }
    if ($rSur) {
        $rSur->free();
    }
}

$tablesOk = $hasArsipTable && $hasDispoTable && $db instanceof mysqli;
if ($tablesOk && $db instanceof mysqli) {
    mdisp_ensure_surat_disposisi_kabag_tandai_selesai($db);
}

$mdispArsipHasKategoriBagian = false;
if ($db instanceof mysqli && $hasArsipTable) {
    $mdispArsipHasKategoriBagian = isset(mdisp_arsip_surat_column_set($db)['kategori_bagian']);
}

$buktiDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'disposisi_bukti';
$buktiWeb = 'uploads/disposisi_bukti/';
if (!is_dir($buktiDir)) {
    @mkdir($buktiDir, 0777, true);
}

if ($tablesOk && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mdisp_action'])) {
    $action = (string) $_POST['mdisp_action'];
    if (!org_csrf_validate((string) ($_POST['csrf_token'] ?? ''))) {
        $message = 'Sesi keamanan tidak valid.';
        $messageType = 'danger';
    } elseif (!($db instanceof mysqli)) {
        $message = 'Database tidak tersedia.';
        $messageType = 'danger';
    } else {
        $idDisp = (int) ($_POST['id_disp'] ?? 0);

        if ($action === 'mdisp_input_staf' && $canInputDisposisiAwal) {
            $idArsip = (int) ($_POST['id_arsip'] ?? 0);
            $pengirimDipilih = strtolower(trim((string) ($_POST['pengirim_disposisi'] ?? '')));
            $instruksi = trim((string) ($_POST['instruksi_awal'] ?? ''));
            $tujuanAwal = 'Kabag_organisasi';
            $mapPengirim = [
                'bupati' => 'Bupati',
                'sekda' => 'Sekda',
                'asisten3' => 'Asisten III',
            ];
            if ($idArsip <= 0 || $instruksi === '' || !isset($mapPengirim[$pengirimDipilih])) {
                $message = 'Lengkapi surat masuk, pengirim disposisi, dan instruksi.';
                $messageType = 'warning';
            } else {
                $stA = $db->prepare('SELECT * FROM `arsip_surat` WHERE `id` = ? LIMIT 1');
                if ($stA === false) {
                    $message = 'Gagal memvalidasi data arsip.';
                    $messageType = 'danger';
                } else {
                    $stA->bind_param('i', $idArsip);
                    $stA->execute();
                    $rA = $stA->get_result();
                    $aRow = $rA ? $rA->fetch_assoc() : null;
                    $stA->close();
                    if (!is_array($aRow)) {
                        $message = 'Arsip surat tidak ditemukan.';
                        $messageType = 'warning';
                    } else {
                        $jenisArsip = strtolower(trim((string) ($aRow['jenis_surat'] ?? $aRow['jenis'] ?? $aRow['tipe'] ?? '')));
                        if ($jenisArsip !== '' && $jenisArsip !== 'masuk') {
                            $message = 'Input disposisi awal hanya diperbolehkan untuk arsip surat masuk.';
                            $messageType = 'warning';
                        } else {
                            $stDup = $db->prepare('SELECT 1 FROM `surat_disposisi` WHERE `id_arsip` = ? LIMIT 1');
                            if ($stDup === false) {
                                $message = 'Gagal memvalidasi status disposisi.';
                                $messageType = 'danger';
                            } else {
                                $stDup->bind_param('i', $idArsip);
                                $stDup->execute();
                                $rDup = $stDup->get_result();
                                $sudahAda = $rDup !== false && $rDup->num_rows > 0;
                                $stDup->close();
                                if ($sudahAda) {
                                    $message = 'Surat ini sudah didisposisikan. Satu arsip surat masuk hanya memerlukan satu input disposisi awal ke Kabag_organisasi.';
                                    $messageType = 'warning';
                                    $tab = 'masuk';
                                } else {
                            $pengirimAwal = $mapPengirim[$pengirimDipilih];
                            $parentNull = null;
                            $catatanAwal = 'Diinput oleh: ' . $sessionUser . ' (' . org_staff_role_label($sessionRoleNorm) . ')';
                            $stI = $db->prepare(
                                'INSERT INTO `surat_disposisi` (`id_arsip`, `parent_id`, `pengirim_username`, `penerima_username`, `instruksi`, `file_bukti`, `status`, `catatan_kabag`)
                                 VALUES (?, ?, ?, ?, ?, NULL, \'pending\', ?)'
                            );
                            if ($stI === false) {
                                $message = 'Gagal menyiapkan penyimpanan disposisi awal.';
                                $messageType = 'danger';
                            } else {
                                $stI->bind_param('iissss', $idArsip, $parentNull, $pengirimAwal, $tujuanAwal, $instruksi, $catatanAwal);
                                if ($stI->execute()) {
                                    $message = 'Input disposisi dari surat masuk berhasil dikirim ke Kabag_organisasi.';
                                    $messageType = 'success';
                                    $tab = 'masuk';
                                } else {
                                    $message = 'Gagal menyimpan disposisi awal.';
                                    $messageType = 'danger';
                                }
                                $stI->close();
                            }
                                }
                            }
                        }
                    }
                }
            }
        } elseif ($action === 'mdisp_tindak_lanjut' && $isKabag) {
            $penerima = trim((string) ($_POST['penerima_username'] ?? ''));
            $instruksi = trim((string) ($_POST['instruksi_baru'] ?? ''));
            if ($idDisp <= 0 || $penerima === '' || $instruksi === '') {
                $message = 'Lengkapi disposisi, penerima staf, dan instruksi.';
                $messageType = 'warning';
            } elseif (mdisp_is_super_admin_sibos($penerima) || mdisp_is_kabag($penerima)) {
                $message = 'Penerima tindak lanjut harus akun Staf Disposisi (bukan Kabag atau akun khusus).';
                $messageType = 'warning';
            } else {
                $penerimaStafOk = true;
                if (org_staff_users_table_exists($db)) {
                    $uPenerima = org_staff_users_fetch_by_username($db, $penerima);
                    $penerimaStafOk = is_array($uPenerima) && mdisp_user_is_staf_disposisi_role($uPenerima);
                }
                if (!$penerimaStafOk) {
                    $message = 'Penerima harus dipilih dari akun Staf Disposisi.';
                    $messageType = 'warning';
                } else {
                    $st0 = $db->prepare('SELECT `id_arsip`, `penerima_username` FROM `surat_disposisi` WHERE `id` = ? LIMIT 1');
                if ($st0 === false) {
                    $message = 'Gagal memproses data sumber disposisi.';
                    $messageType = 'danger';
                } else {
                    $st0->bind_param('i', $idDisp);
                    $st0->execute();
                    $r0 = $st0->get_result();
                    $src = $r0 ? $r0->fetch_assoc() : null;
                    $st0->close();
                    $idArsip = is_array($src) ? (int) ($src['id_arsip'] ?? 0) : 0;
                    $penerimaBarisSrc = is_array($src) ? trim((string) ($src['penerima_username'] ?? '')) : '';
                    if ($idArsip <= 0) {
                        $message = 'Disposisi asal tidak ditemukan.';
                        $messageType = 'warning';
                    } elseif (!mdisp_is_kabag($penerimaBarisSrc)) {
                        $message = 'Tindak lanjut ke staf hanya diperbolehkan untuk baris yang penerimanya adalah Kabag_organisasi.';
                        $messageType = 'warning';
                    } else {
                        $pengirim = $sessionUser;
                        $st = $db->prepare(
                            'INSERT INTO `surat_disposisi` (`id_arsip`, `parent_id`, `pengirim_username`, `penerima_username`, `instruksi`, `file_bukti`, `status`, `catatan_kabag`)
                             VALUES (?, ?, ?, ?, ?, NULL, \'pending\', NULL)'
                        );
                        if ($st === false) {
                            $message = 'Gagal menyiapkan penyimpanan disposisi lanjutan.';
                            $messageType = 'danger';
                        } else {
                            $st->bind_param('iisss', $idArsip, $idDisp, $pengirim, $penerima, $instruksi);
                            if ($st->execute()) {
                                $message = 'Disposisi berhasil ditindaklanjuti ke staf.';
                                $messageType = 'success';
                            } else {
                                $message = 'Gagal menyimpan disposisi lanjutan.';
                                $messageType = 'danger';
                            }
                            $st->close();
                        }
                    }
                }
                }
            }
        } elseif ($action === 'mdisp_terima' && ($isDisposisiOnlyUser || $isKabag)) {
            $st = $db->prepare(
                'UPDATE `surat_disposisi`
                 SET `status` = \'diterima\'
                 WHERE `id` = ?
                   AND LOWER(TRIM(`penerima_username`)) = LOWER(?)
                   AND `status` = \'pending\''
            );
            if ($st !== false) {
                $st->bind_param('is', $idDisp, $sessionUser);
                if ($st->execute() && $st->affected_rows > 0) {
                    $message = 'Disposisi diterima.';
                    $messageType = 'success';
                } else {
                    $message = 'Disposisi tidak dapat diterima (bukan penerima atau status bukan pending).';
                    $messageType = 'warning';
                }
                $st->close();
            }
        } elseif ($action === 'mdisp_upload_bukti' && ($isDisposisiOnlyUser || $isKabag)) {
            if ($idDisp <= 0 || !isset($_FILES['bukti_file']) || (int) ($_FILES['bukti_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $message = 'Pilih file bukti yang valid.';
                $messageType = 'warning';
            } else {
                $f = $_FILES['bukti_file'];
                $maxB = 20 * 1024 * 1024;
                if ((int) ($f['size'] ?? 0) > $maxB) {
                    $message = 'Ukuran file maksimal 20 MB.';
                    $messageType = 'warning';
                } else {
                    $tmp = (string) ($f['tmp_name'] ?? '');
                    $orig = basename((string) ($f['name'] ?? 'bukti'));
                    $ext = strtolower((string) pathinfo($orig, PATHINFO_EXTENSION));
                    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array($ext, $allowed, true)) {
                        $message = 'Format file harus PDF atau gambar (jpg/jpeg/png/webp).';
                        $messageType = 'warning';
                    } else {
                        $stPrev = $db->prepare(
                            'SELECT `file_bukti` FROM `surat_disposisi`
                             WHERE `id` = ?
                               AND LOWER(TRIM(`penerima_username`)) = LOWER(?)
                               AND `status` IN (\'diterima\', \'dikerjakan\', \'revisi\')
                             LIMIT 1'
                        );
                        $prevBukti = '';
                        if ($stPrev !== false) {
                            $stPrev->bind_param('is', $idDisp, $sessionUser);
                            $stPrev->execute();
                            $rp = $stPrev->get_result();
                            $rowP = $rp ? $rp->fetch_assoc() : null;
                            $stPrev->close();
                            if (is_array($rowP)) {
                                $prevBukti = trim((string) ($rowP['file_bukti'] ?? ''));
                            }
                        }
                        $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($orig, PATHINFO_FILENAME));
                        if ($safe === '') {
                            $safe = 'bukti';
                        }
                        $fn = $safe . '_' . $idDisp . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        $dest = $buktiDir . DIRECTORY_SEPARATOR . $fn;
                        if (move_uploaded_file($tmp, $dest)) {
                            $rel = $buktiWeb . $fn;
                            $kabagColOk = mdisp_ensure_surat_disposisi_kabag_tandai_selesai($db);
                            $sqlUp = $kabagColOk
                                ? 'UPDATE `surat_disposisi`
                                     SET `file_bukti` = ?, `status` = \'selesai\', `kabag_tandai_selesai` = 0
                                     WHERE `id` = ?
                                       AND LOWER(TRIM(`penerima_username`)) = LOWER(?)
                                       AND `status` IN (\'diterima\', \'dikerjakan\', \'revisi\')'
                                : 'UPDATE `surat_disposisi`
                                     SET `file_bukti` = ?, `status` = \'selesai\'
                                     WHERE `id` = ?
                                       AND LOWER(TRIM(`penerima_username`)) = LOWER(?)
                                       AND `status` IN (\'diterima\', \'dikerjakan\', \'revisi\')';
                            $st = $db->prepare($sqlUp);
                            if ($st !== false) {
                                $st->bind_param('sis', $rel, $idDisp, $sessionUser);
                                if ($st->execute() && $st->affected_rows > 0) {
                                    if ($prevBukti !== '' && $prevBukti !== $rel) {
                                        mdisp_safe_unlink_bukti_file($prevBukti, __DIR__);
                                    }
                                    $message = 'Bukti berhasil diunggah. Status menjadi selesai.';
                                    $messageType = 'success';
                                } else {
                                    @unlink($dest);
                                    $message = 'Upload gagal: status belum sesuai atau Anda bukan penerima.';
                                    $messageType = 'warning';
                                }
                                $st->close();
                            }
                        } else {
                            $message = 'Gagal menyimpan file bukti ke server.';
                            $messageType = 'danger';
                        }
                    }
                }
            }
        } elseif ($action === 'mdisp_kabag_tandai_selesai_staf' && $isKabag) {
            if (!mdisp_ensure_surat_disposisi_kabag_tandai_selesai($db)) {
                $message = 'Kolom verifikasi Kabag belum tersedia. Minta admin database menjalankan: ALTER TABLE `surat_disposisi` ADD COLUMN `kabag_tandai_selesai` TINYINT(1) NOT NULL DEFAULT 0;';
                $messageType = 'danger';
            } elseif ($idDisp <= 0) {
                $message = 'ID disposisi tidak valid.';
                $messageType = 'warning';
            } else {
                $stSel = $db->prepare('SELECT `penerima_username`, `status`, COALESCE(`kabag_tandai_selesai`, 0) AS `kv` FROM `surat_disposisi` WHERE `id` = ? LIMIT 1');
                if ($stSel === false) {
                    $message = 'Gagal memuat data disposisi.';
                    $messageType = 'danger';
                } else {
                    $stSel->bind_param('i', $idDisp);
                    $stSel->execute();
                    $rSel = $stSel->get_result();
                    $rowSel = $rSel ? $rSel->fetch_assoc() : null;
                    $stSel->close();
                    if (!is_array($rowSel)) {
                        $message = 'Baris disposisi tidak ditemukan.';
                        $messageType = 'warning';
                    } else {
                        $puSel = trim((string) ($rowSel['penerima_username'] ?? ''));
                        $statSel = trim((string) ($rowSel['status'] ?? ''));
                        $already = (int) ($rowSel['kv'] ?? 0) === 1;
                        if ($puSel === '' || mdisp_is_kabag($puSel) || mdisp_is_super_admin_sibos($puSel)) {
                            $message = 'Tindakan ini hanya untuk baris tugas staf (bukan penerima Kabag).';
                            $messageType = 'warning';
                        } elseif ($statSel !== 'selesai') {
                            $message = 'Hanya baris berstatus selesai (staf sudah mengunggah bukti) yang dapat ditandai ke staf.';
                            $messageType = 'warning';
                        } elseif ($already) {
                            $message = 'Baris ini sudah ditandai selesai oleh Kabag.';
                            $messageType = 'info';
                        } else {
                            $stU = $db->prepare('UPDATE `surat_disposisi` SET `kabag_tandai_selesai` = 1 WHERE `id` = ? AND `status` = \'selesai\' AND COALESCE(`kabag_tandai_selesai`, 0) = 0 LIMIT 1');
                            if ($stU === false) {
                                $message = 'Gagal memperbarui penanda verifikasi.';
                                $messageType = 'danger';
                            } else {
                                $stU->bind_param('i', $idDisp);
                                if ($stU->execute() && $stU->affected_rows > 0) {
                                    $message = 'Tanda selesai oleh Kabag disimpan. Staf melihat notifikasi pada kolom status.';
                                    $messageType = 'success';
                                } else {
                                    $message = 'Tidak dapat menandai (data berubah atau sudah ditandai).';
                                    $messageType = 'warning';
                                }
                                $stU->close();
                            }
                        }
                    }
                }
            }
        } elseif ($action === 'mdisp_kabag_minta_revisi' && $isKabag) {
            $catRevisi = trim((string) ($_POST['catatan_revisi'] ?? ''));
            if ($idDisp <= 0) {
                $message = 'ID disposisi tidak valid.';
                $messageType = 'warning';
            } elseif ($catRevisi === '') {
                $message = 'Isi catatan perbaikan untuk staf.';
                $messageType = 'warning';
            } else {
                $kabagColOkR = mdisp_ensure_surat_disposisi_kabag_tandai_selesai($db);
                $stSel = $db->prepare('SELECT `penerima_username`, `status`, `file_bukti` FROM `surat_disposisi` WHERE `id` = ? LIMIT 1');
                if ($stSel === false) {
                    $message = 'Gagal memuat data disposisi.';
                    $messageType = 'danger';
                } else {
                    $stSel->bind_param('i', $idDisp);
                    $stSel->execute();
                    $rSel = $stSel->get_result();
                    $rowSel = $rSel ? $rSel->fetch_assoc() : null;
                    $stSel->close();
                    if (!is_array($rowSel)) {
                        $message = 'Baris disposisi tidak ditemukan.';
                        $messageType = 'warning';
                    } else {
                        $puSel = trim((string) ($rowSel['penerima_username'] ?? ''));
                        $statSel = trim((string) ($rowSel['status'] ?? ''));
                        $fbSel = trim((string) ($rowSel['file_bukti'] ?? ''));
                        if ($puSel === '' || mdisp_is_kabag($puSel) || mdisp_is_super_admin_sibos($puSel)) {
                            $message = 'Minta perbaikan hanya untuk baris tugas staf.';
                            $messageType = 'warning';
                        } elseif (!in_array($statSel, ['selesai', 'diterima', 'dikerjakan'], true)) {
                            $message = 'Status baris ini tidak dapat dikembalikan ke perbaikan (gunakan baris diterima, dikerjakan, atau selesai).';
                            $messageType = 'warning';
                        } else {
                            if ($fbSel !== '') {
                                mdisp_safe_unlink_bukti_file($fbSel, __DIR__);
                            }
                            $sqlRv = $kabagColOkR
                                ? 'UPDATE `surat_disposisi` SET `status` = \'revisi\', `file_bukti` = NULL, `catatan_kabag` = ?, `kabag_tandai_selesai` = 0 WHERE `id` = ? LIMIT 1'
                                : 'UPDATE `surat_disposisi` SET `status` = \'revisi\', `file_bukti` = NULL, `catatan_kabag` = ? WHERE `id` = ? LIMIT 1';
                            $stU = $db->prepare($sqlRv);
                            if ($stU === false) {
                                $message = 'Gagal memperbarui disposisi.';
                                $messageType = 'danger';
                            } else {
                                $stU->bind_param('si', $catRevisi, $idDisp);
                                if ($stU->execute() && $stU->affected_rows > 0) {
                                    $message = 'Disposisi dikembalikan ke staf untuk perbaikan. Bukti lama dihapus dari server; staf dapat mengunggah bukti baru.';
                                    $messageType = 'success';
                                } else {
                                    $message = 'Tidak dapat mengubah baris (data mungkin sudah berubah).';
                                    $messageType = 'warning';
                                }
                                $stU->close();
                            }
                        }
                    }
                }
            }
        } elseif ($action === 'mdisp_admin_edit_arsip_masuk' && $canAdminDeleteDispo) {
            $idArsipPost = (int) ($_POST['id_arsip'] ?? 0);
            $nomorEdit = trim((string) ($_POST['arsip_nomor_surat'] ?? ''));
            $perihalEdit = trim((string) ($_POST['arsip_perihal'] ?? ''));
            $asalEdit = trim((string) ($_POST['arsip_instansi_asal'] ?? ''));
            $tujuanEdit = trim((string) ($_POST['arsip_instansi_tujuan'] ?? ''));
            $kbEditM = $mdispArsipHasKategoriBagian ? trim((string) ($_POST['arsip_kategori_bagian'] ?? '')) : null;
            if ($idArsipPost <= 0) {
                $message = 'ID arsip tidak valid.';
                $messageType = 'warning';
            } else {
                [$okEdit, $msgEdit, $typeEdit] = mdisp_admin_update_arsip_masuk_metadata(
                    $db,
                    $idArsipPost,
                    $nomorEdit,
                    $perihalEdit,
                    $asalEdit,
                    $tujuanEdit,
                    $kbEditM
                );
                $message = $msgEdit;
                $messageType = $typeEdit;
                $tab = 'masuk';
            }
        } elseif ($action === 'mdisp_admin_edit_arsip_keluar' && $canAdminDeleteDispo) {
            $idArsipPost = (int) ($_POST['id_arsip'] ?? 0);
            $nomorEdit = trim((string) ($_POST['arsip_nomor_surat'] ?? ''));
            $perihalEdit = trim((string) ($_POST['arsip_perihal'] ?? ''));
            $asalEdit = trim((string) ($_POST['arsip_instansi_asal'] ?? ''));
            $tujuanEdit = trim((string) ($_POST['arsip_instansi_tujuan'] ?? ''));
            $kbEditK = $mdispArsipHasKategoriBagian ? trim((string) ($_POST['arsip_kategori_bagian'] ?? '')) : null;
            if ($idArsipPost <= 0) {
                $message = 'ID arsip tidak valid.';
                $messageType = 'warning';
            } else {
                [$okEditK, $msgEditK, $typeEditK] = mdisp_admin_update_arsip_keluar_metadata(
                    $db,
                    $idArsipPost,
                    $nomorEdit,
                    $perihalEdit,
                    $asalEdit,
                    $tujuanEdit,
                    $kbEditK
                );
                $message = $msgEditK;
                $messageType = $typeEditK;
                $tab = 'keluar';
            }
        } elseif ($action === 'mdisp_admin_hapus_arsip_keluar' && $canAdminDeleteDispo) {
            $idArsipDelK = (int) ($_POST['id_arsip'] ?? 0);
            if ($idArsipDelK <= 0) {
                $message = 'ID arsip tidak valid.';
                $messageType = 'warning';
            } else {
                [$okArK, $msgArK, $typeArK] = mdisp_admin_delete_arsip_keluar($db, $idArsipDelK, __DIR__);
                $message = $msgArK;
                $messageType = $typeArK;
                $tab = 'keluar';
            }
        } elseif ($action === 'mdisp_admin_hapus_arsip_masuk' && $canAdminDeleteDispo) {
            $idArsipDel = (int) ($_POST['id_arsip'] ?? 0);
            if ($idArsipDel <= 0) {
                $message = 'ID arsip tidak valid.';
                $messageType = 'warning';
            } else {
                [$okAr, $msgAr, $typeAr] = mdisp_admin_delete_arsip_masuk($db, $idArsipDel, __DIR__);
                $message = $msgAr;
                $messageType = $typeAr;
                $tab = 'masuk';
            }
        } elseif ($action === 'mdisp_admin_hapus' && $canAdminDeleteDispo) {
            if ($idDisp <= 0) {
                $message = 'ID disposisi tidak valid.';
                $messageType = 'warning';
            } else {
                [$okDel, $msgDel, $typeDel] = mdisp_admin_delete_disposisi_subtree($db, $idDisp, __DIR__);
                $message = $msgDel;
                $messageType = $typeDel;
            }
        } else {
            $message = 'Aksi tidak diizinkan untuk role Anda.';
            $messageType = 'danger';
        }
    }
}

$arsipRows = [];
$suratMasuk = [];
$suratKeluar = [];
$arsipById = [];
$dispoRows = [];
$mdispMonGrouped = [];
$staffOptions = [];
$mdispDispoListDbError = '';
$mdispMergedLegacyDisposisi = false;
$mdispArsipIdsWithDisposisi = [];

if ($tablesOk && $db instanceof mysqli) {
    $mdispArsipMetaFile = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'arsip_surat_meta.json';
    $mdispArsipDirMap = [
        'masuk' => __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'surat_masuk',
        'keluar' => __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'surat_keluar',
    ];
    org_arsip_sync_meta_to_arsip_surat_table($db, $mdispArsipMetaFile, $mdispArsipDirMap, 350);

    $resA = $db->query('SELECT * FROM `arsip_surat` LIMIT 1000');
    if ($resA) {
        while ($r = $resA->fetch_assoc()) {
            if (is_array($r)) {
                $arsipRows[] = $r;
            }
        }
        $resA->free();
    }

    foreach ($arsipRows as $a) {
        $idA = (int) ($a['id'] ?? 0);
        if ($idA > 0) {
            $arsipById[$idA] = $a;
        }
        $jenisRaw = strtolower(trim((string) ($a['jenis_surat'] ?? $a['jenis'] ?? $a['tipe'] ?? '')));
        if ($jenisRaw === 'masuk') {
            $suratMasuk[] = $a;
        } elseif ($jenisRaw === 'keluar') {
            $suratKeluar[] = $a;
        } else {
            $suratMasuk[] = $a;
        }
    }

    $mdispArsipIdsWithDisposisi = [];
    $idsMasukForDispo = [];
    foreach ($suratMasuk as $smRow) {
        $iax = (int) ($smRow['id'] ?? 0);
        if ($iax > 0) {
            $idsMasukForDispo[$iax] = true;
        }
    }
    $idListMasuk = array_keys($idsMasukForDispo);
    if ($idListMasuk !== []) {
        $inListMasuk = implode(',', array_map(static fn (int $x): int => $x, $idListMasuk));
        $rdMasuk = $db->query('SELECT DISTINCT `id_arsip` FROM `surat_disposisi` WHERE `id_arsip` IN (' . $inListMasuk . ')');
        if ($rdMasuk) {
            while ($xr = $rdMasuk->fetch_assoc()) {
                $kia = (int) ($xr['id_arsip'] ?? 0);
                if ($kia > 0) {
                    $mdispArsipIdsWithDisposisi[$kia] = true;
                }
            }
            $rdMasuk->free();
        }
    }

    $dispoOrderMain = mdisp_surat_disposisi_order_by_clause($db);
    $mdispKabagTandaiColExists = mdisp_surat_disposisi_kabag_tandai_column_exists($db);
    if ($isDisposisiOnlyUser) {
        $sqlStaf = 'SELECT * FROM `surat_disposisi` WHERE LOWER(TRIM(`penerima_username`)) = LOWER(?) ORDER BY ' . $dispoOrderMain . ' LIMIT 1000';
        $stD = $db->prepare($sqlStaf);
        if ($stD === false) {
            $mdispDispoListDbError = (string) $db->error;
        } else {
            $stD->bind_param('s', $sessionUser);
            $stD->execute();
            $resD = $stD->get_result();
            if ($resD) {
                while ($r = $resD->fetch_assoc()) {
                    if (is_array($r) && mdisp_dispo_row_visible_to_penerima($r, $mdispKabagTandaiColExists)) {
                        $dispoRows[] = $r;
                    }
                }
            }
            $stD->close();
        }
    } else {
        $resD = $db->query('SELECT * FROM `surat_disposisi` ORDER BY ' . $dispoOrderMain . ' LIMIT 1000');
        if ($resD === false) {
            $mdispDispoListDbError = (string) $db->error;
        } else {
            while ($r = $resD->fetch_assoc()) {
                if (is_array($r)) {
                    $dispoRows[] = $r;
                }
            }
            $resD->free();
        }
    }

    if ($dispoRows === [] && $hasDispositionsLegacy) {
        $ordL = mdisp_dispositions_order_by_clause($db);
        $mdispMergedLegacyDisposisi = true;
        if ($isDisposisiOnlyUser) {
            $sqlL = 'SELECT * FROM `dispositions` WHERE LOWER(TRIM(`penerima_username`)) = LOWER(?) ORDER BY ' . $ordL . ' LIMIT 1000';
            $stL = $db->prepare($sqlL);
            if ($stL !== false) {
                $stL->bind_param('s', $sessionUser);
                $stL->execute();
                $resL = $stL->get_result();
                if ($resL) {
                    while ($r = $resL->fetch_assoc()) {
                        if (is_array($r)) {
                            $r['__mdisp_legacy'] = true;
                            $dispoRows[] = $r;
                        }
                    }
                }
                $stL->close();
            }
        } else {
            $resL = $db->query('SELECT * FROM `dispositions` ORDER BY ' . $ordL . ' LIMIT 1000');
            if ($resL) {
                while ($r = $resL->fetch_assoc()) {
                    if (is_array($r)) {
                        $r['__mdisp_legacy'] = true;
                        $dispoRows[] = $r;
                    }
                }
                $resL->free();
            }
        }
    }

    if ($hasSuratLegacy && $dispoRows !== []) {
        $needIds = [];
        foreach ($dispoRows as $dRow) {
            $ia = (int) ($dRow['id_arsip'] ?? 0);
            if ($ia > 0 && !isset($arsipById[$ia])) {
                $needIds[$ia] = true;
            }
        }
        $idInts = array_keys($needIds);
        $idInts = array_values(array_filter($idInts, static fn (int $x): bool => $x > 0));
        if ($idInts !== []) {
            $inList = implode(',', $idInts);
            $rsS = $db->query('SELECT * FROM `surat` WHERE `id` IN (' . $inList . ')');
            if ($rsS) {
                while ($s = $rsS->fetch_assoc()) {
                    if (!is_array($s)) {
                        continue;
                    }
                    $sid = (int) ($s['id'] ?? 0);
                    if ($sid <= 0) {
                        continue;
                    }
                    $arsipById[$sid] = [
                        'id' => $sid,
                        'jenis_surat' => (string) ($s['jenis'] ?? 'masuk'),
                        'jenis' => (string) ($s['jenis'] ?? 'masuk'),
                        'nama_file' => (string) ($s['nama_file'] ?? ''),
                        'file_surat' => (string) ($s['nama_file'] ?? ''),
                        'nomor_surat' => (string) ($s['meta_key'] ?? ''),
                    ];
                }
                $rsS->free();
            }
        }
    }

    if ($isKabag && org_staff_users_table_exists($db)) {
        foreach (org_staff_users_fetch_all($db) as $u) {
            if (!is_array($u)) {
                continue;
            }
            $un = trim((string) ($u['username'] ?? ''));
            if ($un === '' || mdisp_is_super_admin_sibos($un) || mdisp_is_kabag($un)) {
                continue;
            }
            if (!mdisp_user_is_staf_disposisi_role($u)) {
                continue;
            }
            $staffOptions[] = $u;
        }
    }

    $mdispMonGrouped = mdisp_monitoring_build_dispo_groups($dispoRows);
}

$mdispKabagStafHint = '';
if ($isKabag) {
    if (!($db instanceof mysqli) || !org_staff_users_table_exists($db)) {
        $mdispKabagStafHint = 'Tabel users tidak terdeteksi di basis data, sehingga daftar Staf Disposisi tidak dapat dimuat.';
    } elseif ($staffOptions === []) {
        $mdispKabagStafHint = 'Belum ada akun Staf Disposisi untuk dituju: di tabel users setidaknya satu baris harus memiliki role staf_disposisi (boleh di kolom level atau role; nilai seperti staff_disposisi juga dikenali). Tambahkan atau perbarui akun staf melalui menu admin.';
    }
}

$extraHeadMarkup = <<<'HTML'
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
.page-mdisp { font-family: 'Poppins', sans-serif; background: #f8f9fa; min-height: 100vh; }
.page-mdisp .site-main { max-width: 1280px; }
.mdisp-page-hero { padding: 0.25rem 0 0.15rem; border-bottom: 1px solid rgba(37, 99, 235, 0.12); }
.mdisp-page-hero h1 { letter-spacing: -0.02em; }
.mdisp-page-hero-badge {
    display: inline-block;
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    vertical-align: middle;
    margin-left: 0.35rem;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%);
    color: #9a3412;
    border: 1px solid rgba(234, 88, 12, 0.35);
}
.mdisp-nav-pills-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
    border: 1px solid rgba(148, 163, 184, 0.25);
    padding: 0.4rem;
}
.mdisp-nav-pills-card .nav { gap: 0.25rem; }
.mdisp-nav-pills-card .nav-link {
    font-weight: 600;
    color: #475569;
    border-radius: 10px;
    padding: 0.5rem 1rem;
    border: 1px solid transparent;
}
.mdisp-nav-pills-card .nav-link:hover { background: #f8fafc; color: #0f172a; }
.mdisp-nav-pills-card .nav-link.active {
    color: #0f172a;
    background: linear-gradient(135deg, #dbeafe 0%, #f8fafc 55%, #fff 100%);
    border-color: #93c5fd;
    box-shadow: 0 1px 0 rgba(255, 255, 255, 0.9) inset;
}
.page-mdisp .table-wrap { overflow-x: auto; }
.mdisp-table-float {
    border-radius: 12px;
    background: #fff;
    border: 1px solid rgba(148, 163, 184, 0.22);
    box-shadow: 0 12px 40px rgba(15, 23, 42, 0.07), 0 2px 6px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}
.mdisp-mon-card {
    border-radius: 14px !important;
    box-shadow: 0 8px 30px rgba(15, 23, 42, 0.07) !important;
    border: 1px solid rgba(148, 163, 184, 0.2) !important;
    overflow: hidden;
}
.mdisp-mon-card > .card-body { padding: 1.35rem 1.5rem; }
.mdisp-mon-toolbar { margin-bottom: 1.35rem !important; }
.mdisp-mon-search-panel {
    background: linear-gradient(155deg, #ffffff 0%, #f8fafc 52%, #f1f5f9 100%);
    border: 1px solid rgba(148, 163, 184, 0.28);
    border-radius: 16px;
    padding: 1rem 1.2rem 1.15rem;
    box-shadow: 0 6px 28px rgba(15, 23, 42, 0.06), inset 0 1px 0 rgba(255, 255, 255, 0.95);
}
.mdisp-mon-search-header {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.65rem 1rem;
    margin-bottom: 0.8rem;
}
.mdisp-mon-search-label {
    display: flex;
    align-items: flex-start;
    gap: 0.7rem;
    margin: 0;
    cursor: text;
}
.mdisp-mon-search-label-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.35rem;
    height: 2.35rem;
    border-radius: 11px;
    background: linear-gradient(145deg, #dbeafe 0%, #eff6ff 55%, #fff 100%);
    color: #2563eb;
    font-size: 0.92rem;
    box-shadow: 0 3px 12px rgba(37, 99, 235, 0.14);
    flex-shrink: 0;
}
.mdisp-mon-search-label-title {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    color: #1e293b;
    letter-spacing: -0.015em;
    line-height: 1.3;
}
.mdisp-mon-search-label-sub {
    display: block;
    font-size: 0.72rem;
    font-weight: 500;
    color: #64748b;
    margin-top: 0.12rem;
    line-height: 1.35;
}
.mdisp-mon-search-stat {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.38rem 0.8rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
    color: #475569;
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid rgba(148, 163, 184, 0.32);
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.05);
    white-space: nowrap;
    transition: background 0.25s ease, border-color 0.25s ease, color 0.25s ease;
}
.mdisp-mon-search-stat::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #94a3b8;
    flex-shrink: 0;
}
.mdisp-mon-search-stat.is-active {
    color: #1d4ed8;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-color: #93c5fd;
}
.mdisp-mon-search-stat.is-active::before { background: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25); }
.mdisp-mon-search-shell {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    min-height: 3.05rem;
    padding: 0.38rem 0.5rem 0.38rem 0.45rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 13px;
    box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04), 0 2px 10px rgba(15, 23, 42, 0.03);
    transition: border-color 0.28s ease, box-shadow 0.28s ease, transform 0.22s ease;
}
.mdisp-mon-search-shell:focus-within {
    border-color: #60a5fa;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2), 0 0 22px rgba(59, 130, 246, 0.14), inset 0 1px 2px rgba(15, 23, 42, 0.03);
    transform: translateY(-1px);
}
.mdisp-mon-search-leading {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.15rem;
    height: 2.15rem;
    margin-left: 0.12rem;
    border-radius: 10px;
    color: #94a3b8;
    font-size: 0.95rem;
    flex-shrink: 0;
    transition: color 0.22s ease, background 0.22s ease;
}
.mdisp-mon-search-shell:focus-within .mdisp-mon-search-leading {
    color: #2563eb;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
}
.mdisp-mon-search-input {
    flex: 1 1 auto;
    min-width: 0;
    border: 0 !important;
    background: transparent !important;
    padding: 0.55rem 0.4rem;
    font-size: 0.9rem;
    font-weight: 500;
    color: #0f172a;
    box-shadow: none !important;
    outline: none;
}
.mdisp-mon-search-input::placeholder {
    color: #94a3b8;
    font-weight: 400;
}
.mdisp-mon-search-input:focus { box-shadow: none !important; }
.mdisp-mon-search-clear {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.85rem;
    height: 1.85rem;
    padding: 0;
    border: none;
    border-radius: 999px;
    background: #f1f5f9;
    color: #64748b;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease;
}
.mdisp-mon-search-clear:hover {
    background: #fee2e2;
    color: #dc2626;
    transform: scale(1.06);
}
.mdisp-mon-search-clear[hidden] { display: none !important; }
.mdisp-mon-search-divider {
    width: 1px;
    height: 1.65rem;
    background: linear-gradient(180deg, transparent, #cbd5e1 18%, #cbd5e1 82%, transparent);
    flex-shrink: 0;
    margin: 0 0.2rem;
}
.mdisp-mon-filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.42rem;
    padding: 0.52rem 1rem;
    border: 1px solid rgba(148, 163, 184, 0.25);
    border-radius: 10px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    color: #475569;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.22s ease, color 0.22s ease, border-color 0.22s ease, box-shadow 0.22s ease;
}
.mdisp-mon-filter-btn:hover,
.mdisp-mon-filter-btn:focus-visible {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-color: #93c5fd;
    color: #1d4ed8;
    box-shadow: 0 3px 12px rgba(59, 130, 246, 0.16);
    outline: none;
}
@media (max-width: 575.98px) {
    .mdisp-mon-search-panel { padding: 0.85rem 0.9rem 0.95rem; border-radius: 14px; }
    .mdisp-mon-filter-btn span { display: none; }
    .mdisp-mon-filter-btn { padding: 0.52rem 0.72rem; }
}
#mdispMonDispoTable thead th {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #374151;
    border-bottom-width: 1px;
    white-space: nowrap;
    background-color: #f9fafb !important;
}
.mdisp-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.3rem 0.7rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    line-height: 1.2;
    white-space: nowrap;
}
.mdisp-badge--selesai {
    background: linear-gradient(135deg, #d1fae5 0%, #ecfdf5 100%);
    color: #047857;
    border: 1px solid rgba(16, 185, 129, 0.35);
}
.mdisp-badge--proses {
    background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%);
    color: #b45309;
    border: 1px solid rgba(245, 158, 11, 0.4);
}
.mdisp-badge--pending {
    background: linear-gradient(135deg, #fee2e2 0%, #fef2f2 100%);
    color: #b91c1c;
    border: 1px solid rgba(248, 113, 113, 0.45);
}
.mdisp-badge--neutral {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid rgba(148, 163, 184, 0.35);
}
.mdisp-pdf-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.28rem 0.75rem;
    font-size: 0.78rem;
    font-weight: 600;
    border-radius: 999px;
    background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    text-decoration: none;
    transition: background 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
}
.mdisp-pdf-pill:hover {
    background: #dbeafe;
    color: #1e40af;
    box-shadow: 0 3px 10px rgba(59, 130, 246, 0.18);
    transform: translateY(-1px);
}
.mdisp-pdf-pill i { font-size: 0.82rem; opacity: 0.9; }
.mdisp-mon-parent > td { vertical-align: middle; }
.mdisp-mon-parent > * {
    background-color: #f1f5f9 !important;
    box-shadow: inset 0 -1px 0 rgba(15, 23, 42, 0.06);
}
.mdisp-mon-parent > td:first-child + td { border-left: 3px solid #3b82f6; padding-left: 0.85rem; }
.mdisp-mon-parent:hover > * { background-color: #eef6ff !important; }
.mdisp-mon-collapse-btn { border-radius: 8px !important; border-color: #cbd5e1 !important; }
.mdisp-mon-collapse-btn:hover { background: #eff6ff !important; border-color: #93c5fd !important; color: #1d4ed8 !important; }
.mdisp-mon-chevron {
    font-size: 0.72rem;
    transition: transform 0.32s cubic-bezier(0.4, 0, 0.2, 1);
}
.mdisp-mon-collapse-btn[aria-expanded="true"] .mdisp-mon-chevron { transform: rotate(180deg); }
.mdisp-mon-collapse.collapsing {
    transition: height 0.38s cubic-bezier(0.4, 0, 0.2, 1) !important;
}
.mdisp-mon-collapse.show .mdisp-mon-children-inner {
    animation: mdispMonExpandIn 0.38s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}
@keyframes mdispMonExpandIn {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
.mdisp-mon-children-inner { border-bottom: 1px solid rgba(15, 23, 42, 0.08); }
.mdisp-mon-children-inner .table > :not(caption) > * > * { background-color: #fff; }
.mdisp-mon-children-inner .table tbody tr:nth-child(even) > * {
    background-color: #f8fafc !important;
}
.mdisp-mon-children-inner .table tbody tr:hover > * {
    background-color: #f1f5f9 !important;
}
.mdisp-mon-children-inner .table tbody tr.mdisp-dispo-row--revisi > * {
    background: linear-gradient(90deg, rgba(255, 237, 213, 0.98) 0%, rgba(255, 247, 237, 0.95) 55%, rgba(255, 255, 255, 0.92) 100%) !important;
    box-shadow: inset 5px 0 0 #ea580c;
}
.mdisp-dispo-row--child { border-left: 3px solid #93c5fd; }
.mdisp-dispo-row--child.mdisp-dispo-row--revisi {
    border-left: 5px solid #ea580c;
    outline: 1px solid rgba(234, 88, 12, 0.2);
    outline-offset: -1px;
}
.mdisp-dispo-row--flash {
    animation: mdispDispoFlash 2.2s ease 1;
}
@keyframes mdispDispoFlash {
    0% { background-color: #bfdbfe; box-shadow: inset 3px 0 0 #2563eb; }
    35% { background-color: #dbeafe; }
    100% { background-color: transparent; box-shadow: none; }
}
</style>
HTML;

require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';

$tabMasukActive = $tab === 'masuk' ? 'active' : '';
$tabKeluarActive = $tab === 'keluar' ? 'active' : '';
$tabMonActive = $tab === 'monitoring' ? 'active' : '';
?>
<div class="container site-main section-spacing page-mdisp">
    <nav class="mb-3" aria-label="Navigasi">
        <a class="small text-decoration-none link-secondary" href="e_organisasi.php">&larr; Kembali ke E-Organisasi</a>
    </nav>

    <header class="mdisp-page-hero mb-4">
        <h1 class="h3 mb-2 text-dark fw-semibold">Monitoring Disposisi <span class="mdisp-page-hero-badge">Role baru</span></h1>
        <p class="text-muted small mb-0">Lacak surat masuk/keluar, disposisi awal, dan tindak lanjut ke staf dalam satu halaman.</p>
    </header>

    <?php if ($message !== ''): ?>
        <div class="alert alert-<?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <?php if (!$tablesOk): ?>
        <div class="alert alert-warning">
            Tabel <code>arsip_surat</code> dan/atau <code>surat_disposisi</code> belum terdeteksi.
        </div>
    <?php else: ?>
        <div class="mdisp-nav-pills-card mb-4" role="navigation" aria-label="Tab halaman">
            <ul class="nav nav-pills flex-wrap" role="tablist">
                <?php if (!$isDisposisiOnlyUser): ?>
                    <li class="nav-item"><a class="nav-link <?php echo $tabMasukActive; ?>" href="monitoring_disposisi.php?tab=masuk">Surat Masuk</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo $tabKeluarActive; ?>" href="monitoring_disposisi.php?tab=keluar">Surat Keluar</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link <?php echo $tabMonActive; ?>" href="monitoring_disposisi.php?tab=monitoring">Monitoring Disposisi</a></li>
            </ul>
        </div>

        <?php if ($tab === 'masuk'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">Surat Masuk</h2>
                    <?php if ($canAdminDeleteDispo && !$canInputDisposisiAwal): ?>
                        <p class="small text-muted mb-3">Sebagai <strong>Admin / Super Admin</strong> Anda dapat <strong>mengubah metadata</strong> atau <strong>menghapus</strong> arsip surat masuk beserta disposisi terkait.</p>
                    <?php endif; ?>
                    <div class="table-wrap">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light"><tr><th>ID</th><th>Nomor</th><?php if ($mdispArsipHasKategoriBagian): ?><th>Kategori</th><?php endif; ?><th>Nama File</th><th>Tanggal</th><?php if ($showMasukAksiCol): ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead>
                            <tbody>
                                <?php foreach ($suratMasuk as $r): ?>
                                    <?php
                                    $idRow = (int) ($r['id'] ?? 0);
                                    $nomorShow = (string) ($r['nomor_surat'] ?? $r['nomor'] ?? '');
                                    $perihalShow = trim((string) ($r['perihal_ringkasan'] ?? $r['perihal'] ?? ''));
                                    $asalShow = trim((string) ($r['instansi_asal'] ?? $r['asal_surat'] ?? ''));
                                    $tujuanShow = trim((string) ($r['instansi_tujuan'] ?? $r['tujuan_surat'] ?? ''));
                                    $fnameDisp = org_arsip_surat_row_display_filename($r);
                                    ?>
                                    <tr id="mdisp-arsip-masuk-<?php echo $idRow; ?>" class="mdisp-arsip-masuk-row">
                                        <td><?php echo $idRow; ?></td>
                                        <td><?php echo htmlspecialchars($nomorShow !== '' ? $nomorShow : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <?php if ($mdispArsipHasKategoriBagian): ?>
                                            <td class="small"><?php echo htmlspecialchars(org_arsip_kategori_bagian_label($r), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo htmlspecialchars($fnameDisp !== '' ? $fnameDisp : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($r['created_at'] ?? $r['tanggal'] ?? $r['tgl_upload'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <?php if ($showMasukAksiCol): ?>
                                            <td class="text-end text-nowrap">
                                                <?php if ($canInputDisposisiAwal): ?>
                                                    <?php if (!empty($mdispArsipIdsWithDisposisi[$idRow])): ?>
                                                        <span class="badge text-bg-success">Sudah didisposisikan</span>
                                                        <a class="btn btn-sm btn-outline-primary ms-1" href="monitoring_disposisi.php?tab=monitoring&amp;id_arsip=<?php echo $idRow; ?>">Monitoring</a>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalInputAwal<?php echo $idRow; ?>">Input Disposisi</button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <?php if ($canAdminDeleteDispo): ?>
                                                    <button class="btn btn-sm btn-outline-secondary<?php echo $canInputDisposisiAwal ? ' ms-1' : ''; ?>" type="button" data-bs-toggle="modal" data-bs-target="#modalEditArsip<?php echo $idRow; ?>">Edit</button>
                                                    <form method="post" class="d-inline ms-1" onsubmit="return confirm('Hapus arsip surat masuk ID <?php echo $idRow; ?> beserta semua disposisi terkait? File PDF di folder surat masuk (jika ada) juga akan dihapus. Tindakan ini tidak dapat dibatalkan.');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                                        <input type="hidden" name="mdisp_action" value="mdisp_admin_hapus_arsip_masuk">
                                                        <input type="hidden" name="id_arsip" value="<?php echo $idRow; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if ($suratMasuk === []): ?><tr><td colspan="<?php echo (int) (4 + ($mdispArsipHasKategoriBagian ? 1 : 0) + ($showMasukAksiCol ? 1 : 0)); ?>" class="text-muted">Belum ada data.</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif ($tab === 'keluar'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">Surat Keluar</h2>
                    <div class="table-wrap">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light"><tr><th>ID</th><th>Nomor</th><?php if ($mdispArsipHasKategoriBagian): ?><th>Kategori</th><?php endif; ?><th>Nama File</th><th>Tanggal</th><?php if ($showKeluarAksiCol): ?><th class="text-end">Aksi</th><?php endif; ?></tr></thead>
                            <tbody>
                                <?php foreach ($suratKeluar as $r): ?>
                                    <?php
                                    $idKel = (int) ($r['id'] ?? 0);
                                    $fnameKel = org_arsip_surat_row_display_filename($r);
                                    ?>
                                    <tr>
                                        <td><?php echo $idKel; ?></td>
                                        <td><?php echo htmlspecialchars((string) ($r['nomor_surat'] ?? $r['nomor'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <?php if ($mdispArsipHasKategoriBagian): ?>
                                            <td class="small"><?php echo htmlspecialchars(org_arsip_kategori_bagian_label($r), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo htmlspecialchars($fnameKel !== '' ? $fnameKel : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($r['created_at'] ?? $r['tanggal'] ?? $r['tgl_upload'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <?php if ($showKeluarAksiCol): ?>
                                            <td class="text-end text-nowrap">
                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalEditArsipKeluar<?php echo $idKel; ?>">Edit</button>
                                                <form method="post" class="d-inline ms-1" onsubmit="return confirm('Hapus arsip surat keluar ID <?php echo $idKel; ?> beserta semua disposisi terkait? File PDF di folder surat keluar (jika ada) juga akan dihapus. Tindakan ini tidak dapat dibatalkan.');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="mdisp_action" value="mdisp_admin_hapus_arsip_keluar">
                                                    <input type="hidden" name="id_arsip" value="<?php echo $idKel; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if ($suratKeluar === []): ?><tr><td colspan="<?php echo (int) (4 + ($mdispArsipHasKategoriBagian ? 1 : 0) + ($showKeluarAksiCol ? 1 : 0)); ?>" class="text-muted">Belum ada data.</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm mdisp-mon-card">
                <div class="card-body">
                    <?php if ($tab === 'monitoring' && $mdispKabagStafHint !== ''): ?>
                        <div class="alert alert-warning small mb-3" role="status">
                            <?php echo htmlspecialchars($mdispKabagStafHint, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($mdispDispoListDbError !== ''): ?>
                        <div class="alert alert-danger small mb-3" role="alert">
                            Gagal memuat daftar disposisi dari database: <?php echo htmlspecialchars($mdispDispoListDbError, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($mdispMergedLegacyDisposisi): ?>
                        <div class="alert alert-info small mb-3" role="alert">
                            Menampilkan data dari tabel <code>dispositions</code> (skema lama) karena <code>surat_disposisi</code> belum berisi baris. Aksi hapus / tindak lanjut / terima hanya untuk baris di <code>surat_disposisi</code>; pertimbangkan migrasi data ke tabel baru.
                        </div>
                    <?php endif; ?>
                    <?php if ($dispoRows === [] && $mdispDispoListDbError === ''): ?>
                        <div class="alert alert-light border small mb-3" role="status">
                            Belum ada baris disposisi di database. Untuk mengisi: di halaman <strong>Arsip</strong> unggah surat masuk dan centang <strong>akan didisposisikan</strong>, lalu <strong>Sub Admin E-Organisasi</strong> memakai <a class="alert-link" href="disposisi_awal_kabag.php">Disposisi awal &amp; tanda terima Kabag</a>, atau <strong>Admin / Super Admin</strong> di tab <a class="alert-link" href="monitoring_disposisi.php?tab=masuk">Surat Masuk</a> (Monitoring). Surat masuk tanpa centang tersebut hanya tersimpan di Arsip, tidak masuk daftar ini.
                        </div>
                    <?php endif; ?>

                    <?php if ($tab === 'monitoring' && $mdispMonGrouped !== []): ?>
                        <div class="mdisp-mon-toolbar">
                            <div class="mdisp-mon-search-panel">
                                <div class="mdisp-mon-search-header">
                                    <label for="mdispMonSearch" class="mdisp-mon-search-label">
                                        <span class="mdisp-mon-search-label-icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></span>
                                        <span class="mdisp-mon-search-label-text">
                                            <span class="mdisp-mon-search-label-title">Cari di tabel monitoring</span>
                                            <span class="mdisp-mon-search-label-sub">Nomor surat, perihal, pengirim, penerima, instruksi, status…</span>
                                        </span>
                                    </label>
                                    <span id="mdispMonSearchCount" class="mdisp-mon-search-stat" role="status" aria-live="polite"></span>
                                </div>
                                <div class="mdisp-mon-search-shell" role="search">
                                    <span class="mdisp-mon-search-leading" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></span>
                                    <input type="search" id="mdispMonSearch" class="mdisp-mon-search-input" placeholder="Ketik kata kunci untuk memfilter grup surat…" autocomplete="off">
                                    <button type="button" class="mdisp-mon-search-clear" id="mdispMonSearchClear" title="Hapus pencarian" aria-label="Hapus pencarian" hidden>
                                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                    </button>
                                    <span class="mdisp-mon-search-divider" aria-hidden="true"></span>
                                    <button type="button" class="mdisp-mon-filter-btn" id="mdispMonFilterBtn" title="Fokus kolom pencarian" aria-label="Filter pencarian">
                                        <i class="fa-solid fa-filter" aria-hidden="true"></i>
                                        <span>Filter</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="table-wrap mdisp-table-float">
                        <table id="mdispMonDispoTable" class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 3rem" scope="col"><span class="visually-hidden">Buka atau tutup detail</span></th>
                                    <th>Surat</th>
                                    <th>Pengirim</th>
                                    <th>Penerima</th>
                                    <th>Instruksi</th>
                                    <th>Status</th>
                                    <th>Catatan</th>
                                    <th>Bukti</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mdispMonGrouped as $grp): ?>
                                    <?php
                                    $collapseCid = mdisp_monitoring_collapse_dom_id($grp['key']);
                                    $idArsipG = (int) ($grp['id_arsip'] ?? 0);
                                    if ($idArsipG <= 0 && $grp['rows'] !== []) {
                                        $idArsipG = (int) (($grp['rows'][0]['id_arsip'] ?? 0));
                                    }
                                    $firstD = $grp['rows'][0] ?? [];
                                    $isLegacyGroup = !empty($firstD['__mdisp_legacy']);
                                    $arsipRefParent = $idArsipG > 0 ? ($arsipById[$idArsipG] ?? []) : [];
                                    $nChild = count($grp['rows']);
                                    $jenisLabelP = strtolower((string) ($arsipRefParent['jenis_surat'] ?? $arsipRefParent['jenis'] ?? ''));
                                    $namaFileP = org_arsip_surat_row_display_filename($arsipRefParent);
                                    $nomorArsipP = trim((string) ($arsipRefParent['nomor_surat'] ?? $arsipRefParent['nomor'] ?? ''));
                                    $perihalFullP = trim((string) ($arsipRefParent['perihal_ringkasan'] ?? $arsipRefParent['perihal'] ?? ''));
                                    $asalArsipP = trim((string) ($arsipRefParent['instansi_asal'] ?? $arsipRefParent['asal_surat'] ?? ''));
                                    $perihalShortP = $perihalFullP;
                                    if ($perihalShortP !== '') {
                                        $maxPP = 160;
                                        if (function_exists('mb_strlen') && mb_strlen($perihalShortP, 'UTF-8') > $maxPP) {
                                            $perihalShortP = mb_substr($perihalShortP, 0, $maxPP - 1, 'UTF-8') . '…';
                                        } elseif (strlen($perihalShortP) > $maxPP) {
                                            $perihalShortP = substr($perihalShortP, 0, $maxPP - 3) . '...';
                                        }
                                    }
                                    $pdfArsipHrefP = $arsipRefParent !== [] ? org_arsip_surat_row_pdf_web_path($arsipRefParent) : null;
                                    $mdispMonSearchParts = [];
                                    foreach ([(string) $idArsipG, $nomorArsipP, $namaFileP, $perihalFullP, $asalArsipP, $jenisLabelP] as $sp) {
                                        $t = trim((string) $sp);
                                        if ($t !== '') {
                                            $mdispMonSearchParts[] = $t;
                                        }
                                    }
                                    foreach ($grp['rows'] as $dr) {
                                        if (!is_array($dr)) {
                                            continue;
                                        }
                                        foreach (['id', 'pengirim_username', 'penerima_username', 'instruksi', 'status', 'catatan_kabag', 'file_bukti'] as $k) {
                                            $t = trim((string) ($dr[$k] ?? ''));
                                            if ($t !== '') {
                                                $mdispMonSearchParts[] = $t;
                                            }
                                        }
                                    }
                                    $mdispMonSearchHay = strtolower(preg_replace('/\s+/u', ' ', trim(implode(' ', $mdispMonSearchParts))));
                                    $mdispMonSearchHayAttr = htmlspecialchars($mdispMonSearchHay, ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <tr class="mdisp-mon-parent" data-mdisp-search="<?php echo $mdispMonSearchHayAttr; ?>">
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-secondary mdisp-mon-collapse-btn d-inline-flex align-items-center justify-content-center p-0" style="width: 2rem; height: 2rem;" data-bs-toggle="collapse" data-bs-target="#<?php echo htmlspecialchars($collapseCid, ENT_QUOTES, 'UTF-8'); ?>" aria-expanded="false" aria-controls="<?php echo htmlspecialchars($collapseCid, ENT_QUOTES, 'UTF-8'); ?>">
                                                <i class="fa-solid fa-chevron-down mdisp-mon-chevron" aria-hidden="true"></i>
                                                <span class="visually-hidden">Buka atau tutup tindak lanjut disposisi</span>
                                            </button>
                                        </td>
                                        <td colspan="8" class="small" style="min-width: 14rem; max-width: 22rem;">
                                            <?php if ($isLegacyGroup): ?>
                                                <span class="badge text-bg-secondary mb-1">Skema lama (dispositions)</span>
                                                <div class="fw-medium mb-0">Disposisi tanpa pengelompokan arsip baru</div>
                                                <div class="text-muted small mt-1"><?php echo (int) $nChild; ?> baris dari tabel <code>dispositions</code><?php if ($nChild === 1): ?>
                                                    <?php $fd0 = $grp['rows'][0]; ?> — ID <?php echo (int) ($fd0['id'] ?? 0); ?>, <?php echo htmlspecialchars(trim((string) ($fd0['pengirim_username'] ?? '')) !== '' ? (string) $fd0['pengirim_username'] : '-', ENT_QUOTES, 'UTF-8'); ?> → <?php echo htmlspecialchars(trim((string) ($fd0['penerima_username'] ?? '')) !== '' ? (string) $fd0['penerima_username'] : '-', ENT_QUOTES, 'UTF-8'); ?>
                                                <?php endif; ?></div>
                                            <?php else: ?>
                                                <div class="d-flex flex-wrap align-items-center gap-1 mb-1">
                                                    <span class="badge text-bg-light text-dark border"><?php echo htmlspecialchars($jenisLabelP !== '' ? $jenisLabelP : '-', ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <span class="fw-medium"><?php echo htmlspecialchars($namaFileP !== '' ? $namaFileP : ($idArsipG > 0 ? 'Arsip #' . $idArsipG : 'Tanpa arsip'), ENT_QUOTES, 'UTF-8'); ?></span>
                                                </div>
                                                <?php if ($nomorArsipP !== ''): ?>
                                                    <div class="text-muted mb-0"><span class="text-uppercase" style="font-size: 0.7rem;">Nomor</span> <?php echo htmlspecialchars($nomorArsipP, ENT_QUOTES, 'UTF-8'); ?></div>
                                                <?php endif; ?>
                                                <?php if ($perihalShortP !== ''): ?>
                                                    <div class="text-body-secondary mb-0" title="<?php echo htmlspecialchars($perihalFullP, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($perihalShortP, ENT_QUOTES, 'UTF-8'); ?></div>
                                                <?php endif; ?>
                                                <?php if ($asalArsipP !== ''): ?>
                                                    <div class="text-muted mb-0" style="font-size: 0.8rem;">Asal: <?php echo htmlspecialchars($asalArsipP, ENT_QUOTES, 'UTF-8'); ?></div>
                                                <?php endif; ?>
                                                <?php if ($pdfArsipHrefP !== null && $pdfArsipHrefP !== ''): ?>
                                                    <div class="mt-2"><?php echo mdisp_pdf_pill_link($pdfArsipHrefP); ?></div>
                                                <?php endif; ?>
                                                <?php if ($idArsipG > 0 && $arsipRefParent === []): ?>
                                                    <div class="text-warning small mt-1">Meta arsip tidak ditemukan (ID <?php echo $idArsipG; ?>).</div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr class="mdisp-mon-wrap p-0 border-0">
                                        <td colspan="9" class="p-0 border-0">
                                            <div id="<?php echo htmlspecialchars($collapseCid, ENT_QUOTES, 'UTF-8'); ?>" class="collapse mdisp-mon-collapse">
                                                <div class="mdisp-mon-children-inner px-2 py-3 bg-light">
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered align-middle mb-0 bg-white">
                                                            <thead class="table-secondary">
                                                                <tr>
                                                                    <th>ID</th>
                                                                    <th>Surat</th>
                                                                    <th>Pengirim</th>
                                                                    <th>Penerima</th>
                                                                    <th>Instruksi</th>
                                                                    <th>Status</th>
                                                                    <th>Catatan</th>
                                                                    <th>Bukti</th>
                                                                    <th class="text-end">Aksi</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($grp['rows'] as $d): ?>
                                                                    <?php mdisp_render_monitoring_dispo_data_row($d, $arsipById, $sessionUser, $isDisposisiOnlyUser, $isKabag, true); ?>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if ($mdispMonGrouped !== []): ?>
                                    <tr id="mdispMonSearchEmptyRow" class="d-none">
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <span class="d-inline-block me-2" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></span>
                                            Tidak ada grup surat yang cocok. Ubah kata kunci atau kosongkan pencarian.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($mdispMonGrouped === []): ?><tr><td colspan="9" class="text-muted">Belum ada disposisi.</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($canInputDisposisiAwal): ?>
            <?php foreach ($suratMasuk as $r): ?>
                <?php
                $idArsipRow = (int) ($r['id'] ?? 0);
                if ($idArsipRow <= 0 || !empty($mdispArsipIdsWithDisposisi[$idArsipRow])) {
                    continue;
                }
                ?>
                <div class="modal fade" id="modalInputAwal<?php echo $idArsipRow; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="post">
                                <div class="modal-header">
                                    <h2 class="modal-title h5">Input Disposisi Awal</h2>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="mdisp_action" value="mdisp_input_staf">
                                    <input type="hidden" name="id_arsip" value="<?php echo $idArsipRow; ?>">
                                    <p class="small mb-2"><span class="text-muted">Tujuan:</span> <strong>Kabag_organisasi</strong></p>
                                    <p class="small mb-3"><span class="text-muted">Nomor:</span> <?php echo htmlspecialchars((string) ($r['nomor_surat'] ?? $r['nomor'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></p>
                                    <div class="mb-3">
                                        <label class="form-label">Pengirim disposisi</label>
                                        <select class="form-select" name="pengirim_disposisi" required>
                                            <option value="">— Pilih pengirim —</option>
                                            <option value="bupati">Bupati</option>
                                            <option value="sekda">Sekda</option>
                                            <option value="asisten3">Asisten III</option>
                                        </select>
                                    </div>
                                    <label class="form-label">Instruksi</label>
                                    <textarea class="form-control" name="instruksi_awal" rows="4" required maxlength="20000" placeholder="Tuliskan isi disposisi dari surat masuk"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan & Kirim ke Kabag</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($canAdminDeleteDispo): ?>
            <?php foreach ($suratMasuk as $r): ?>
                <?php
                $idArsipEdit = (int) ($r['id'] ?? 0);
                $vNomor = (string) ($r['nomor_surat'] ?? $r['nomor'] ?? '');
                $vPerihal = trim((string) ($r['perihal_ringkasan'] ?? $r['perihal'] ?? ''));
                $vAsal = trim((string) ($r['instansi_asal'] ?? ''));
                $vTujuan = trim((string) ($r['instansi_tujuan'] ?? ''));
                $vKb = trim((string) ($r['kategori_bagian'] ?? ''));
                $vNamaFile = org_arsip_surat_row_display_filename($r) ?: '-';
                ?>
                <div class="modal fade" id="modalEditArsip<?php echo $idArsipEdit; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="post">
                                <div class="modal-header">
                                    <h2 class="modal-title h5">Edit arsip surat masuk #<?php echo $idArsipEdit; ?></h2>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="mdisp_action" value="mdisp_admin_edit_arsip_masuk">
                                    <input type="hidden" name="id_arsip" value="<?php echo $idArsipEdit; ?>">
                                    <p class="small text-muted mb-3">Nama berkas: <strong><?php echo htmlspecialchars($vNamaFile, ENT_QUOTES, 'UTF-8'); ?></strong> (ubah file PDF lewat unggah ulang di modul Arsip jika diperlukan).</p>
                                    <div class="mb-3">
                                        <label class="form-label" for="arsip_nomor_surat_<?php echo $idArsipEdit; ?>">Nomor surat <span class="text-danger">*</span></label>
                                        <input class="form-control" id="arsip_nomor_surat_<?php echo $idArsipEdit; ?>" name="arsip_nomor_surat" value="<?php echo htmlspecialchars($vNomor, ENT_QUOTES, 'UTF-8'); ?>" required maxlength="191" autocomplete="off">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="arsip_perihal_<?php echo $idArsipEdit; ?>">Perihal / ringkasan</label>
                                        <textarea class="form-control" id="arsip_perihal_<?php echo $idArsipEdit; ?>" name="arsip_perihal" rows="3" maxlength="20000"><?php echo htmlspecialchars($vPerihal, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                    <?php if ($mdispArsipHasKategoriBagian): ?>
                                        <div class="mb-3">
                                            <label class="form-label" for="arsip_kategori_bagian_<?php echo $idArsipEdit; ?>">Kategori bagian</label>
                                            <select class="form-select" id="arsip_kategori_bagian_<?php echo $idArsipEdit; ?>" name="arsip_kategori_bagian">
                                                <option value="" <?php echo $vKb === '' ? 'selected' : ''; ?>>Belum ditentukan</option>
                                                <?php foreach (org_arsip_kategori_bagian_map() as $kbVal => $kbLabel): ?>
                                                    <option value="<?php echo htmlspecialchars($kbVal, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $vKb === $kbVal ? 'selected' : ''; ?>><?php echo htmlspecialchars($kbLabel, ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <label class="form-label" for="arsip_instansi_asal_<?php echo $idArsipEdit; ?>">Instansi asal</label>
                                        <input class="form-control" id="arsip_instansi_asal_<?php echo $idArsipEdit; ?>" name="arsip_instansi_asal" value="<?php echo htmlspecialchars($vAsal, ENT_QUOTES, 'UTF-8'); ?>" maxlength="255" autocomplete="off">
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label" for="arsip_instansi_tujuan_<?php echo $idArsipEdit; ?>">Instansi tujuan</label>
                                        <input class="form-control" id="arsip_instansi_tujuan_<?php echo $idArsipEdit; ?>" name="arsip_instansi_tujuan" value="<?php echo htmlspecialchars($vTujuan, ENT_QUOTES, 'UTF-8'); ?>" maxlength="255" autocomplete="off">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($canAdminDeleteDispo): ?>
            <?php foreach ($suratKeluar as $r): ?>
                <?php
                $idArsipKelEdit = (int) ($r['id'] ?? 0);
                $vNomorK = (string) ($r['nomor_surat'] ?? $r['nomor'] ?? '');
                $vPerihalK = trim((string) ($r['perihal_ringkasan'] ?? $r['perihal'] ?? ''));
                $vAsalK = trim((string) ($r['instansi_asal'] ?? ''));
                $vTujuanK = trim((string) ($r['instansi_tujuan'] ?? ''));
                $vKbK = trim((string) ($r['kategori_bagian'] ?? ''));
                $vNamaFileK = org_arsip_surat_row_display_filename($r) ?: '-';
                ?>
                <div class="modal fade" id="modalEditArsipKeluar<?php echo $idArsipKelEdit; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="post">
                                <div class="modal-header">
                                    <h2 class="modal-title h5">Edit arsip surat keluar #<?php echo $idArsipKelEdit; ?></h2>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="mdisp_action" value="mdisp_admin_edit_arsip_keluar">
                                    <input type="hidden" name="id_arsip" value="<?php echo $idArsipKelEdit; ?>">
                                    <p class="small text-muted mb-3">Nama berkas: <strong><?php echo htmlspecialchars($vNamaFileK, ENT_QUOTES, 'UTF-8'); ?></strong> (ubah file PDF lewat unggah ulang di modul Arsip jika diperlukan).</p>
                                    <div class="mb-3">
                                        <label class="form-label" for="arsip_kel_nomor_<?php echo $idArsipKelEdit; ?>">Nomor surat <span class="text-danger">*</span></label>
                                        <input class="form-control" id="arsip_kel_nomor_<?php echo $idArsipKelEdit; ?>" name="arsip_nomor_surat" value="<?php echo htmlspecialchars($vNomorK, ENT_QUOTES, 'UTF-8'); ?>" required maxlength="191" autocomplete="off">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="arsip_kel_perihal_<?php echo $idArsipKelEdit; ?>">Perihal / ringkasan</label>
                                        <textarea class="form-control" id="arsip_kel_perihal_<?php echo $idArsipKelEdit; ?>" name="arsip_perihal" rows="3" maxlength="20000"><?php echo htmlspecialchars($vPerihalK, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                    <?php if ($mdispArsipHasKategoriBagian): ?>
                                        <div class="mb-3">
                                            <label class="form-label" for="arsip_kel_kategori_bagian_<?php echo $idArsipKelEdit; ?>">Kategori bagian</label>
                                            <select class="form-select" id="arsip_kel_kategori_bagian_<?php echo $idArsipKelEdit; ?>" name="arsip_kategori_bagian">
                                                <option value="" <?php echo $vKbK === '' ? 'selected' : ''; ?>>Belum ditentukan</option>
                                                <?php foreach (org_arsip_kategori_bagian_map() as $kbVal => $kbLabel): ?>
                                                    <option value="<?php echo htmlspecialchars($kbVal, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $vKbK === $kbVal ? 'selected' : ''; ?>><?php echo htmlspecialchars($kbLabel, ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <label class="form-label" for="arsip_kel_asal_<?php echo $idArsipKelEdit; ?>">Instansi asal</label>
                                        <input class="form-control" id="arsip_kel_asal_<?php echo $idArsipKelEdit; ?>" name="arsip_instansi_asal" value="<?php echo htmlspecialchars($vAsalK, ENT_QUOTES, 'UTF-8'); ?>" maxlength="255" autocomplete="off">
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label" for="arsip_kel_tujuan_<?php echo $idArsipKelEdit; ?>">Instansi tujuan</label>
                                        <input class="form-control" id="arsip_kel_tujuan_<?php echo $idArsipKelEdit; ?>" name="arsip_instansi_tujuan" value="<?php echo htmlspecialchars($vTujuanK, ENT_QUOTES, 'UTF-8'); ?>" maxlength="255" autocomplete="off">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($isKabag): ?>
            <?php foreach ($dispoRows as $d): ?>
                <?php if (!empty($d['__mdisp_legacy'])) {
                    continue;
                } ?>
                <?php $did = (int) ($d['id'] ?? 0); ?>
                <?php
                $dpenKabModal = trim((string) ($d['penerima_username'] ?? ''));
                if (mdisp_is_kabag($dpenKabModal)):
                ?>
                <div class="modal fade" id="modalTL<?php echo $did; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="post">
                                <div class="modal-header">
                                    <h2 class="modal-title h5">Tindak Lanjuti #<?php echo $did; ?></h2>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="mdisp_action" value="mdisp_tindak_lanjut">
                                    <input type="hidden" name="id_disp" value="<?php echo $did; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Pilih Staf Disposisi (penerima tugas)</label>
                                        <select name="penerima_username" class="form-select" required>
                                            <option value="">— Pilih staf —</option>
                                            <?php foreach ($staffOptions as $s): ?>
                                                <option value="<?php echo htmlspecialchars((string) ($s['username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php echo htmlspecialchars((string) ($s['nama'] ?? $s['username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Instruksi ke staf</label>
                                        <textarea class="form-control" name="instruksi_baru" rows="4" required maxlength="20000"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Kirim</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php
                $dstKab = trim((string) ($d['status'] ?? ''));
                $dpenKab = trim((string) ($d['penerima_username'] ?? ''));
                $dstfKab = $dpenKab !== '' && !mdisp_is_kabag($dpenKab) && !mdisp_is_super_admin_sibos($dpenKab);
                ?>
                <?php if ($dstfKab && in_array($dstKab, ['selesai', 'diterima', 'dikerjakan'], true)): ?>
                    <div class="modal fade" id="modalRevisi<?php echo $did; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post">
                                    <div class="modal-header">
                                        <h2 class="modal-title h5">Minta perbaikan #<?php echo $did; ?></h2>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="mdisp_action" value="mdisp_kabag_minta_revisi">
                                        <input type="hidden" name="id_disp" value="<?php echo $did; ?>">
                                        <p class="small text-muted mb-2">Status menjadi <strong>revisi</strong>. Jika ada bukti yang diunggah staf, berkas tersebut dihapus dari server agar tidak menumpuk; staf mengunggah bukti baru setelah perbaikan.</p>
                                        <label class="form-label" for="catatan_revisi_<?php echo $did; ?>">Catatan perbaikan untuk staf <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="catatan_revisi_<?php echo $did; ?>" name="catatan_revisi" rows="4" required maxlength="20000" placeholder="Jelaskan bagian yang perlu diperbaiki"></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-warning">Kirim ke staf</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($isDisposisiOnlyUser || $isKabag): ?>
            <?php foreach ($dispoRows as $d): ?>
                <?php if (!empty($d['__mdisp_legacy'])) {
                    continue;
                } ?>
                <?php
                $did = (int) ($d['id'] ?? 0);
                $penerima = trim((string) ($d['penerima_username'] ?? ''));
                $st = trim((string) ($d['status'] ?? ''));
                $canUpModal = strcasecmp($penerima, $sessionUser) === 0 && ($isDisposisiOnlyUser || $isKabag);
                ?>
                <?php if ($canUpModal && in_array($st, ['diterima', 'dikerjakan', 'revisi'], true)): ?>
                    <div class="modal fade" id="modalUP<?php echo $did; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post" enctype="multipart/form-data">
                                    <div class="modal-header">
                                        <h2 class="modal-title h5">Upload Bukti #<?php echo $did; ?></h2>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="mdisp_action" value="mdisp_upload_bukti">
                                        <input type="hidden" name="id_disp" value="<?php echo $did; ?>">
                                        <label class="form-label">File bukti (PDF/JPG/PNG/WEBP, maks 20MB)</label>
                                        <input type="file" class="form-control" name="bukti_file" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Unggah</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php if (($tab === 'monitoring' && $mdispScrollIdDisp > 0) || ($tab === 'masuk' && $mdispScrollIdArsip > 0)): ?>
<script>
(function () {
    var idDisp = <?php echo (int) ($tab === 'monitoring' ? $mdispScrollIdDisp : 0); ?>;
    var idArsip = <?php echo (int) ($tab === 'masuk' ? $mdispScrollIdArsip : 0); ?>;
    function scrollFlash(el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        el.classList.add('mdisp-dispo-row--flash');
        window.setTimeout(function () {
            el.classList.remove('mdisp-dispo-row--flash');
        }, 2300);
    }
    function go() {
        var el = null;
        if (idDisp) {
            el = document.getElementById('mdisp-dispo-' + idDisp);
        }
        if (!el && idArsip) {
            el = document.getElementById('mdisp-arsip-masuk-' + idArsip);
        }
        if (!el) {
            return;
        }
        var panel = el.closest('.mdisp-mon-collapse');
        if (panel && !panel.classList.contains('show')) {
            var Coll = window.bootstrap && window.bootstrap.Collapse;
            if (Coll) {
                var inst = Coll.getOrCreateInstance(panel, { toggle: false });
                panel.addEventListener('shown.bs.collapse', function onShown() {
                    panel.removeEventListener('shown.bs.collapse', onShown);
                    scrollFlash(el);
                });
                inst.show();
                return;
            }
        }
        scrollFlash(el);
    }
    if (document.readyState === 'complete') {
        window.setTimeout(go, 100);
    } else {
        window.addEventListener('load', function () {
            window.setTimeout(go, 100);
        });
    }
})();
</script>
<?php endif; ?>
<?php if ($tab === 'monitoring' && $tablesOk): ?>
<script src="https://code.jquery.com/jquery-3.7.1.slim.min.js" crossorigin="anonymous"></script>
<script>
jQuery(function ($) {
    $('#mdispMonFilterBtn').on('click', function () {
        var el = document.getElementById('mdispMonSearch');
        if (el) {
            el.focus();
            el.select();
        }
    });

    var $searchInp = $('#mdispMonSearch');
    var $searchClear = $('#mdispMonSearchClear');
    var $emptyRow = $('#mdispMonSearchEmptyRow');
    var $monTable = $('#mdispMonDispoTable');
    if ($searchInp.length && $monTable.length) {
        function mdispNormSearch(s) {
            return String(s || '').toLowerCase().replace(/\s+/g, ' ').trim();
        }
        function mdispToggleSearchClear() {
            if (!$searchClear.length) {
                return;
            }
            $searchClear.prop('hidden', mdispNormSearch($searchInp.val()) === '');
        }
        $searchClear.on('click', function () {
            $searchInp.val('').trigger('input').focus();
        });
        function applyMonSearch() {
            var q = mdispNormSearch($searchInp.val());
            mdispToggleSearchClear();
            var $parents = $monTable.find('tr.mdisp-mon-parent');
            var total = $parents.length;
            var visible = 0;
            $parents.each(function () {
                var $tr = $(this);
                var hay = mdispNormSearch($tr.attr('data-mdisp-search') || '');
                var $wrap = $tr.next('tr.mdisp-mon-wrap');
                var match = q === '' || hay.indexOf(q) !== -1;
                if (match) {
                    visible += 1;
                    $tr.removeClass('d-none');
                    $wrap.removeClass('d-none');
                } else {
                    $tr.addClass('d-none');
                    $wrap.addClass('d-none');
                }
            });
            var $count = $('#mdispMonSearchCount');
            if ($count.length) {
                $count.toggleClass('is-active', q !== '');
                if (total === 0) {
                    $count.text('');
                } else if (q === '') {
                    $count.text(total === 1 ? '1 grup surat' : total + ' grup surat');
                } else {
                    $count.text('Menampilkan ' + visible + ' dari ' + total + ' grup');
                }
            }
            if ($emptyRow.length) {
                if (total > 0 && visible === 0 && q !== '') {
                    $emptyRow.removeClass('d-none');
                } else {
                    $emptyRow.addClass('d-none');
                }
            }
        }
        $searchInp.on('input', applyMonSearch);
        applyMonSearch();
    }
});
</script>
<?php endif; ?>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
