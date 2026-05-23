<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'arsip_surat_db.php';
org_require_level_access(['super_admin', 'admin', 'sub_admin_eorganisasi', 'staf_disposisi', 'kabag_organisasi']);

$dptRoleNorm = org_staff_role_normalize((string) ($_SESSION['level'] ?? $_SESSION['admin_role'] ?? ''));

$pageTitle = 'Disposisi dan Surat Masuk terbaru — Bagian Organisasi';
$navActive = 'e_organisasi';
$includePersonnelModals = false;
$includeNewsModals = false;
$bodyClass = 'page-disposisi-terbaru mode-eorganisasi';

/**
 * Bangun query daftar surat masuk terbaru dari `arsip_surat` (nama kolom jenis / tanggal bervariasi).
 */
function dpt_latest_masuk_sql(mysqli $db): ?string
{
    $res = $db->query('SHOW COLUMNS FROM `arsip_surat`');
    if ($res === false) {
        return null;
    }
    $cols = [];
    while ($c = $res->fetch_assoc()) {
        $f = strtolower(trim((string) ($c['Field'] ?? '')));
        if ($f !== '') {
            $cols[$f] = true;
        }
    }
    $res->free();
    $jenisCol = '';
    foreach (['jenis_surat', 'jenis', 'tipe'] as $cand) {
        if (isset($cols[$cand])) {
            $jenisCol = $cand;
            break;
        }
    }
    if ($jenisCol === '') {
        return null;
    }
    $orderCol = 'id';
    foreach (['created_at', 'tanggal', 'tgl_upload', 'updated_at'] as $cand) {
        if (isset($cols[$cand])) {
            $orderCol = $cand;
            break;
        }
    }
    $jSafe = preg_replace('/[^a-z0-9_]/i', '', $jenisCol);
    $oSafe = preg_replace('/[^a-z0-9_]/i', '', $orderCol);
    if ($jSafe === '' || $oSafe === '') {
        return null;
    }

    return 'SELECT * FROM `arsip_surat` WHERE LOWER(TRIM(COALESCE(`' . $jSafe . "`, ''))) = 'masuk' ORDER BY `" . $oSafe . '` DESC LIMIT 40';
}

function dpt_surat_disposisi_order_by_clause(mysqli $db): string
{
    $res = $db->query('SHOW COLUMNS FROM `surat_disposisi`');
    if ($res === false) {
        return 'd.`id` DESC';
    }
    $hasCreated = false;
    while ($c = $res->fetch_assoc()) {
        if (strtolower(trim((string) ($c['Field'] ?? ''))) === 'created_at') {
            $hasCreated = true;
            break;
        }
    }
    $res->free();

    return $hasCreated ? 'd.`created_at` DESC' : 'd.`id` DESC';
}

/**
 * Arsip surat masuk yang belum memiliki baris di surat_disposisi (berdasarkan id_arsip).
 */
function dpt_arsip_masuk_belum_disposisi_sql(mysqli $db): ?string
{
    $rDisp = $db->query("SHOW TABLES LIKE 'surat_disposisi'");
    $hasDispo = $rDisp !== false && $rDisp->num_rows > 0;
    if ($rDisp) {
        $rDisp->free();
    }
    if (!$hasDispo) {
        return null;
    }
    $res = $db->query('SHOW COLUMNS FROM `arsip_surat`');
    if ($res === false) {
        return null;
    }
    $cols = [];
    while ($c = $res->fetch_assoc()) {
        $f = strtolower(trim((string) ($c['Field'] ?? '')));
        if ($f !== '') {
            $cols[$f] = true;
        }
    }
    $res->free();
    $jenisCol = '';
    foreach (['jenis_surat', 'jenis', 'tipe'] as $cand) {
        if (isset($cols[$cand])) {
            $jenisCol = $cand;
            break;
        }
    }
    if ($jenisCol === '') {
        return null;
    }
    $orderCol = 'id';
    foreach (['created_at', 'tanggal', 'tgl_upload', 'updated_at'] as $cand) {
        if (isset($cols[$cand])) {
            $orderCol = $cand;
            break;
        }
    }
    $jSafe = preg_replace('/[^a-z0-9_]/i', '', $jenisCol);
    $oSafe = preg_replace('/[^a-z0-9_]/i', '', $orderCol);
    if ($jSafe === '' || $oSafe === '') {
        return null;
    }

    return 'SELECT a.* FROM `arsip_surat` a WHERE LOWER(TRIM(COALESCE(a.`' . $jSafe . '`, \'\'))) = \'masuk\' AND a.`id` NOT IN (SELECT DISTINCT `id_arsip` FROM `surat_disposisi` WHERE `id_arsip` IS NOT NULL AND `id_arsip` > 0) ORDER BY a.`' . $oSafe . '` DESC LIMIT 40';
}

/**
 * @param array<string, mixed> $row
 * @param array<int, array<string, mixed>|null> $dispTerbaruArsipCache
 *
 * @return array<string, int|string>
 */
function dpt_map_surat_disposisi_row_to_card_item(array $row, mysqli $dbDisp, bool $hasArsipSurat, array &$dispTerbaruArsipCache): array
{
    $created = trim((string) ($row['created_at'] ?? ''));
    $ts = $created !== '' ? strtotime($created) : false;
    $instr = trim((string) ($row['instruksi'] ?? ''));
    $dispoRowIdTitle = (int) ($row['mdispo_surat_disposisi_id'] ?? $row['id'] ?? 0);
    $judul = 'Disposisi #' . ($dispoRowIdTitle > 0 ? $dispoRowIdTitle : (int) ($row['id'] ?? 0));
    if ($instr !== '') {
        $one = preg_replace('/\s+/u', ' ', $instr);
        if (is_string($one) && $one !== '') {
            $snippet = function_exists('mb_substr') ? mb_substr($one, 0, 72, 'UTF-8') : substr($one, 0, 72);
            $lenOne = function_exists('mb_strlen') ? mb_strlen($one, 'UTF-8') : strlen($one);
            if ($lenOne > 72) {
                $snippet .= '…';
            }
            if (trim((string) $snippet) !== '') {
                $judul = (string) $snippet;
            }
        }
    }
    $jenis = (string) ($row['jenis_surat'] ?? $row['jenis'] ?? $row['tipe'] ?? '');
    $namaFile = (string) ($row['nama_file'] ?? $row['file_surat'] ?? $row['file_pdf'] ?? $row['surat_file'] ?? '');
    $nomorSurat = trim((string) ($row['nomor_surat'] ?? $row['nomor'] ?? $row['surat_meta'] ?? ''));
    $idArsipRef = (int) ($row['id_arsip'] ?? 0);
    $ref = $nomorSurat !== '' ? $nomorSurat : ($namaFile !== '' ? $namaFile : ('arsip#' . $idArsipRef));
    $pengirim = (string) ($row['pengirim_username'] ?? $row['pengirjm_username'] ?? $row['pengirim'] ?? '-');
    $penerima = (string) ($row['penerima_username'] ?? $row['penerlma_username'] ?? $row['penerima'] ?? '-');
    $instruksiOut = (string) ($row['instruksi'] ?? $row['instruksii'] ?? '');
    $statusOut = (string) ($row['status'] ?? '-');
    $catatanOut = (string) ($row['catatan_kabag'] ?? $row['catatan'] ?? '');
    $buktiOut = (string) ($row['file_bukti'] ?? $row['file_revisi'] ?? $row['file'] ?? '');

    $ringkas = 'Status: ' . $statusOut;
    $suratLabel = $jenis === 'keluar' ? 'Keluar' : ($jenis === 'masuk' ? 'Masuk' : 'Terkait');
    $ringkas .= "\nSurat: " . $suratLabel . ' — ' . ($namaFile !== '' ? $namaFile : ('ID Arsip ' . $idArsipRef));
    $ringkas .= "\nPengirim: " . $pengirim . ' → Penerima: ' . $penerima;
    if (trim($catatanOut) !== '') {
        $ringkas .= "\n\nCatatan Kabag:\n" . trim($catatanOut);
    }
    if (trim($instruksiOut) !== '') {
        $ringkas .= "\n\nInstruksi:\n" . trim($instruksiOut);
    }
    $fb = trim($buktiOut);
    $link = '';
    if ($fb !== '') {
        $link = preg_match('#^https?://#i', $fb) === 1 ? $fb : $fb;
    }
    $dispoRowId = (int) ($row['mdispo_surat_disposisi_id'] ?? $row['id'] ?? 0);
    $readKey = $dispoRowId > 0 ? 'dispo_' . $dispoRowId : ('h_' . substr(hash('sha256', $judul . '|' . $ref . '|' . $created), 0, 24));
    $berkasLabel = $namaFile !== '' ? $namaFile : ($idArsipRef > 0 ? 'Arsip #' . $idArsipRef : '—');
    $catatanTrim = trim($catatanOut);
    $instruksiTrim = trim($instruksiOut);

    $pdfArsipHref = null;
    if ($hasArsipSurat && org_arsip_surat_table_exists($dbDisp) && $idArsipRef > 0) {
        $rowPdf = $row;
        if (org_arsip_surat_row_display_filename($row) === '') {
            if (!array_key_exists($idArsipRef, $dispTerbaruArsipCache)) {
                $dispTerbaruArsipCache[$idArsipRef] = null;
                $stAr = $dbDisp->prepare('SELECT * FROM `arsip_surat` WHERE `id` = ? LIMIT 1');
                if ($stAr !== false) {
                    $stAr->bind_param('i', $idArsipRef);
                    $stAr->execute();
                    $rAr = $stAr->get_result();
                    $dispTerbaruArsipCache[$idArsipRef] = $rAr ? $rAr->fetch_assoc() : null;
                    $stAr->close();
                }
            }
            $cachedAr = $dispTerbaruArsipCache[$idArsipRef];
            if (is_array($cachedAr)) {
                $rowPdf = $cachedAr;
            }
        }
        $pdfArsipHref = org_arsip_surat_row_pdf_web_path($rowPdf);
    }
    $monitoringTlUrl = $dispoRowId > 0
        ? ('monitoring_disposisi.php?tab=monitoring&id_disp=' . $dispoRowId)
        : '';

    $statusNorm = strtolower(trim($statusOut));

    return [
        'tanggal' => ($ts !== false && (int) $ts > 0) ? date('Y-m-d', (int) $ts) : '',
        'tanggal_ts' => $ts !== false ? (int) $ts : 0,
        'judul' => $judul,
        'nomor' => $ref,
        'dari' => $pengirim,
        'ringkasan' => $ringkas,
        'link' => $link,
        'read_key' => $readKey,
        'layout' => 'db',
        'dispo_row_id' => $dispoRowId,
        'hide_monitoring_tl' => false,
        'needs_perbaikan' => $statusNorm === 'revisi',
        'meta_status' => $statusOut,
        'meta_penerima' => $penerima,
        'meta_surat_jenis' => $suratLabel,
        'meta_berkas' => $berkasLabel,
        'meta_catatan' => $catatanTrim,
        'meta_instruksi' => $instruksiTrim,
        'pdf_arsip_href' => $pdfArsipHref ?? '',
        'monitoring_tl_url' => $monitoringTlUrl,
    ];
}

/**
 * ID disposisi (induk) yang sudah punya baris lain dengan pengirim = user saat ini dan parent_id / referensi_id menunjuk ke ID tersebut.
 *
 * @param list<int> $candidateIds
 *
 * @return array<int, true>
 */
function dpt_dispo_parent_ids_matched_by_child_pengirim(mysqli $db, string $sessionUser, array $candidateIds): array
{
    $uniq = [];
    foreach ($candidateIds as $x) {
        $i = (int) $x;
        if ($i > 0) {
            $uniq[$i] = true;
        }
    }
    $ids = array_keys($uniq);
    if ($ids === [] || trim($sessionUser) === '') {
        return [];
    }
    $inList = implode(',', $ids);
    $cols = [];
    $r = $db->query('SHOW COLUMNS FROM `surat_disposisi`');
    if ($r) {
        while ($c = $r->fetch_assoc()) {
            $f = strtolower(trim((string) ($c['Field'] ?? '')));
            if ($f !== '') {
                $cols[$f] = true;
            }
        }
        $r->free();
    }
    $matched = [];
    $user = trim($sessionUser);

    $run = static function (mysqli $db, string $sql, string $userBind) use (&$matched): void {
        $st = $db->prepare($sql);
        if ($st === false) {
            return;
        }
        $st->bind_param('s', $userBind);
        $st->execute();
        $rs = $st->get_result();
        if ($rs) {
            while ($row = $rs->fetch_assoc()) {
                $x = (int) ($row['x'] ?? 0);
                if ($x > 0) {
                    $matched[$x] = true;
                }
            }
        }
        $st->close();
    };

    if (isset($cols['parent_id'])) {
        $sql = 'SELECT DISTINCT d.`parent_id` AS x FROM `surat_disposisi` d
            WHERE d.`parent_id` IN (' . $inList . ') AND d.`parent_id` IS NOT NULL
            AND LOWER(TRIM(COALESCE(d.`pengirim_username`, \'\'))) = LOWER(?)';
        $run($db, $sql, $user);
    }
    if (isset($cols['referensi_id'])) {
        $sql = 'SELECT DISTINCT d.`referensi_id` AS x FROM `surat_disposisi` d
            WHERE d.`referensi_id` IN (' . $inList . ') AND d.`referensi_id` IS NOT NULL
            AND LOWER(TRIM(COALESCE(d.`pengirim_username`, \'\'))) = LOWER(?)';
        $run($db, $sql, $user);
    }

    return $matched;
}

/**
 * Set `hide_monitoring_tl` pada kartu layout db: status selesai / diteruskan / diproses atau sudah ada turunan dari user (Kabag).
 *
 * @param list<array<string, int|string|bool>> $items
 */
function dpt_enrich_dispo_hide_monitoring_tl(mysqli $db, string $sessionUser, array &$items): void
{
    $candidates = [];
    foreach ($items as $it) {
        if (($it['layout'] ?? '') !== 'db') {
            continue;
        }
        $id = (int) ($it['dispo_row_id'] ?? 0);
        if ($id > 0) {
            $candidates[] = $id;
        }
    }
    if ($candidates === []) {
        return;
    }
    $byChild = dpt_dispo_parent_ids_matched_by_child_pengirim($db, $sessionUser, $candidates);
    foreach ($items as $k => $it) {
        if (($it['layout'] ?? '') !== 'db') {
            continue;
        }
        $id = (int) ($it['dispo_row_id'] ?? 0);
        $st = strtolower(trim((string) ($it['meta_status'] ?? '')));
        $hideStatus = in_array($st, ['selesai', 'diteruskan', 'diproses'], true);
        $hideChild = $id > 0 && isset($byChild[$id]);
        $items[$k]['hide_monitoring_tl'] = $hideStatus || $hideChild;
    }
}

/**
 * @param list<array<string, int|string>> $itemsList
 */
function dpt_render_dispo_cards_html(array $itemsList): void
{
    foreach ($itemsList as $it) {
        $tglLabel = '';
        if (($it['tanggal_ts'] ?? 0) > 0) {
            $tglLabel = date('d/m/Y', (int) $it['tanggal_ts']);
        } elseif (($it['tanggal'] ?? '') !== '') {
            $tglLabel = (string) $it['tanggal'];
        }
        $link = (string) ($it['link'] ?? '');
        $linkOk = $link !== '';
        $linkExternal = $linkOk && preg_match('#^https?://#i', $link) === 1;
        $readKey = (string) ($it['read_key'] ?? '');
        if ($readKey === '') {
            $readKey = 'x_' . substr(hash('sha256', ($it['judul'] ?? '') . '|' . ($it['nomor'] ?? '') . '|' . ($it['tanggal'] ?? '')), 0, 24);
        }
        $layoutDb = (($it['layout'] ?? '') === 'db');
        $needsPerbaikan = !empty($it['needs_perbaikan']);
        $articleClasses = 'card disp-card' . ($needsPerbaikan ? ' disp-card--revisi' : ' disp-card--unread');
        $ariaLabel = $needsPerbaikan
            ? 'Kartu disposisi — instruksi perbaikan dari Kabag (status revisi)'
            : 'Kartu disposisi — klik untuk menandai sudah dibaca';
        echo '<li>';
        echo '<article class="', htmlspecialchars($articleClasses, ENT_QUOTES, 'UTF-8'), '" data-dispo-read-key="', htmlspecialchars($readKey, ENT_QUOTES, 'UTF-8'), '" data-dispo-perbaikan="', $needsPerbaikan ? '1' : '0', '" tabindex="0" role="button" aria-label="', htmlspecialchars($ariaLabel, ENT_QUOTES, 'UTF-8'), '">';
        echo '<div class="card-body p-3 p-md-4">';
        echo '<div class="disp-card__top">';
        if ($tglLabel !== '') {
            echo '<p class="disp-card__date mb-0"><i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>', htmlspecialchars($tglLabel, ENT_QUOTES, 'UTF-8'), '</p>';
        } else {
            echo '<span></span>';
        }
        if ($needsPerbaikan) {
            echo '<span class="badge disp-perbaikan-badge" role="status">Instruksi perbaikan</span>';
        } else {
            echo '<span class="badge bg-warning text-dark disp-new-badge" data-dispo-new-badge hidden>Baru</span>';
        }
        echo '</div>';
        echo '<h2 class="disp-card__title mb-0">', htmlspecialchars((string) ($it['judul'] ?? ''), ENT_QUOTES, 'UTF-8'), '</h2>';
        if ($layoutDb) {
            $stChip = strtolower(trim((string) ($it['meta_status'] ?? '')));
            $chipRevisiClass = $stChip === 'revisi' ? ' disp-chip--revisi' : '';
            echo '<div class="disp-chip-row mt-2">';
            echo '<span class="disp-chip disp-chip--status', htmlspecialchars($chipRevisiClass, ENT_QUOTES, 'UTF-8'), '">', htmlspecialchars((string) ($it['meta_status'] ?? '-'), ENT_QUOTES, 'UTF-8'), '</span>';
            if (trim((string) ($it['meta_surat_jenis'] ?? '')) !== '') {
                echo '<span class="disp-chip disp-chip--surat">', htmlspecialchars((string) $it['meta_surat_jenis'], ENT_QUOTES, 'UTF-8'), '</span>';
            }
            echo '</div>';
            echo '<dl class="disp-meta mb-0">';
            echo '<dt>Nomor surat / referensi</dt><dd>', htmlspecialchars((string) ($it['nomor'] ?? '—'), ENT_QUOTES, 'UTF-8'), '</dd>';
            echo '<dt>Berkas arsip</dt><dd>', htmlspecialchars((string) ($it['meta_berkas'] ?? '—'), ENT_QUOTES, 'UTF-8'), '</dd>';
            echo '<dt>Pengirim</dt><dd>', htmlspecialchars((string) ($it['dari'] ?? '—'), ENT_QUOTES, 'UTF-8'), '</dd>';
            echo '<dt>Penerima</dt><dd>', htmlspecialchars((string) ($it['meta_penerima'] ?? '—'), ENT_QUOTES, 'UTF-8'), '</dd>';
            echo '</dl>';
            if (trim((string) ($it['meta_catatan'] ?? '')) !== '') {
                echo '<div class="disp-block"><div class="disp-block__label">Catatan Kabag</div><div class="mb-0">', nl2br(htmlspecialchars((string) $it['meta_catatan'], ENT_QUOTES, 'UTF-8')), '</div></div>';
            }
            if (trim((string) ($it['meta_instruksi'] ?? '')) !== '') {
                echo '<div class="disp-block"><div class="disp-block__label">Instruksi</div><div class="mb-0">', nl2br(htmlspecialchars((string) $it['meta_instruksi'], ENT_QUOTES, 'UTF-8')), '</div></div>';
            }
        } else {
            if (($it['nomor'] ?? '') !== '') {
                echo '<dl class="disp-meta mt-2 mb-0"><dt>Nomor</dt><dd>', htmlspecialchars((string) $it['nomor'], ENT_QUOTES, 'UTF-8'), '</dd>';
                if (($it['dari'] ?? '') !== '') {
                    echo '<dt>Dari</dt><dd>', htmlspecialchars((string) $it['dari'], ENT_QUOTES, 'UTF-8'), '</dd>';
                }
                echo '</dl>';
            } elseif (($it['dari'] ?? '') !== '') {
                echo '<p class="small text-muted mt-2 mb-0">Dari: ', htmlspecialchars((string) $it['dari'], ENT_QUOTES, 'UTF-8'), '</p>';
            }
            if (($it['ringkasan'] ?? '') !== '') {
                echo '<div class="disp-block mt-2 mb-0"><div class="disp-block__label">Ringkasan</div><div class="disp-json-body mb-0">', nl2br(htmlspecialchars((string) $it['ringkasan'], ENT_QUOTES, 'UTF-8')), '</div></div>';
            }
        }
        $pdfArsip = trim((string) ($it['pdf_arsip_href'] ?? ''));
        $monTl = trim((string) ($it['monitoring_tl_url'] ?? ''));
        $hideMonTl = !empty($it['hide_monitoring_tl']);
        $showMonBtn = $monTl !== '' && !$hideMonTl;
        $showForwardedLabel = $monTl !== '' && $hideMonTl;
        $showActions = $linkOk || $pdfArsip !== '' || $showMonBtn || $showForwardedLabel;
        if ($showActions) {
            echo '<div class="disp-card__actions mb-0">';
            if ($pdfArsip !== '') {
                echo '<a class="btn btn-sm btn-outline-primary" href="', htmlspecialchars($pdfArsip, ENT_QUOTES, 'UTF-8'), '" target="_blank" rel="noopener noreferrer">PDF surat (arsip)</a>';
            }
            if ($showMonBtn) {
                $monBtnLabel = str_contains($monTl, 'disposisi_awal_kabag') ? 'Disposisi & status Kabag' : 'Monitoring — tindak lanjut';
                echo '<a class="btn btn-sm btn-success" href="', htmlspecialchars($monTl, ENT_QUOTES, 'UTF-8'), '">', htmlspecialchars($monBtnLabel, ENT_QUOTES, 'UTF-8'), '</a>';
            }
        if ($showForwardedLabel) {
            $stMeta = strtolower(trim((string) ($it['meta_status'] ?? '')));
            $tlDoneLabel = $stMeta === 'selesai' ? 'Selesai' : 'Sudah Diteruskan';
            echo '<span class="dpt-ml-diteruskan text-success fw-semibold" role="status">', htmlspecialchars($tlDoneLabel, ENT_QUOTES, 'UTF-8'), '</span>';
        }
            if ($linkOk) {
                echo '<a class="btn btn-sm btn-primary" href="', htmlspecialchars($link, ENT_QUOTES, 'UTF-8'), '"';
                echo $linkExternal ? ' target="_blank" rel="noopener noreferrer"' : '';
                echo '>Bukti disposisi</a>';
            }
            echo '</div>';
        }
        echo '</div></article></li>';
    }
}

/**
 * @param list<array<string, int|string>> $list
 */
function dpt_render_arsip_masuk_belum_dispo_cards_html(array $list): void
{
    foreach ($list as $sm) {
        $tglSm = '';
        if (($sm['tanggal_ts'] ?? 0) > 0) {
            $tglSm = date('d/m/Y', (int) $sm['tanggal_ts']);
        } elseif (($sm['tanggal'] ?? '') !== '') {
            $tglSm = (string) $sm['tanggal'];
        }
        $rkSm = (string) ($sm['read_key'] ?? '');
        $pdfSmH = trim((string) ($sm['pdf_arsip_href'] ?? ''));
        $monSm = trim((string) ($sm['monitoring_masuk_url'] ?? ''));
        echo '<li>';
        echo '<article class="card disp-card disp-card--arsip-masuk disp-card--unread" data-dispo-read-key="', htmlspecialchars($rkSm, ENT_QUOTES, 'UTF-8'), '" tabindex="0" role="button" aria-label="Surat masuk — klik untuk menandai sudah dibaca">';
        echo '<div class="card-body p-3 p-md-4">';
        echo '<div class="disp-card__top">';
        if ($tglSm !== '') {
            echo '<p class="disp-card__date mb-0"><i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>', htmlspecialchars($tglSm, ENT_QUOTES, 'UTF-8'), '</p>';
        } else {
            echo '<span></span>';
        }
        echo '<span class="badge bg-success disp-new-badge" data-dispo-new-badge hidden>Baru</span>';
        echo '</div>';
        echo '<h2 class="disp-card__title mb-0">', htmlspecialchars((string) ($sm['judul'] ?? 'Surat masuk'), ENT_QUOTES, 'UTF-8'), '</h2>';
        echo '<div class="disp-chip-row mt-2">';
        echo '<span class="disp-chip disp-chip--surat">Surat masuk</span>';
        echo '<span class="disp-chip disp-chip--status">Belum ada disposisi</span>';
        if (trim((string) ($sm['ikut_label'] ?? '')) !== '') {
            echo '<span class="disp-chip disp-chip--status">', htmlspecialchars((string) $sm['ikut_label'], ENT_QUOTES, 'UTF-8'), '</span>';
        }
        echo '</div>';
        echo '<dl class="disp-meta mb-0">';
        echo '<dt>Nomor</dt><dd>', htmlspecialchars((string) ($sm['nomor'] ?? '—'), ENT_QUOTES, 'UTF-8'), '</dd>';
        echo '<dt>Berkas</dt><dd>', htmlspecialchars((string) ($sm['berkas'] ?? '—'), ENT_QUOTES, 'UTF-8'), '</dd>';
        if (trim((string) ($sm['asal'] ?? '')) !== '') {
            echo '<dt>Asal</dt><dd>', htmlspecialchars((string) $sm['asal'], ENT_QUOTES, 'UTF-8'), '</dd>';
        }
        if (trim((string) ($sm['perihal'] ?? '')) !== '') {
            echo '<dt>Perihal</dt><dd>', nl2br(htmlspecialchars((string) $sm['perihal'], ENT_QUOTES, 'UTF-8')), '</dd>';
        }
        echo '</dl>';
        echo '<div class="disp-card__actions mb-0">';
        if ($pdfSmH !== '') {
            echo '<a class="btn btn-sm btn-outline-primary" href="', htmlspecialchars($pdfSmH, ENT_QUOTES, 'UTF-8'), '" target="_blank" rel="noopener noreferrer">PDF surat</a>';
        }
        if ($monSm !== '') {
            $smBtnLabel = str_contains($monSm, 'disposisi_awal_kabag') ? 'Input disposisi awal' : 'Monitoring — Surat Masuk';
            echo '<a class="btn btn-sm btn-success" href="', htmlspecialchars($monSm, ENT_QUOTES, 'UTF-8'), '">', htmlspecialchars($smBtnLabel, ENT_QUOTES, 'UTF-8'), '</a>';
        }
        echo '<a class="btn btn-sm btn-light border" href="<?php echo org_href('arsip.php'); ?>">Arsip</a>';
        echo '</div></div></article></li>';
    }
}

$dptTab = strtolower(trim((string) ($_GET['tab'] ?? 'masuk')));
if (!in_array($dptTab, ['masuk', 'ke_staf', 'surat'], true)) {
    $dptTab = 'masuk';
}

/**
 * @return list<array<string, int|string>>
 */
$itemsMasuk = [];

$dbDisp = org_db();
$hasSuratDisposisi = false;
$hasArsipSurat = false;
$hasDispositionsLegacy = false;
$hasSuratLegacy = false;
if ($dbDisp instanceof mysqli) {
    $r1 = $dbDisp->query("SHOW TABLES LIKE 'surat_disposisi'");
    $r2 = $dbDisp->query("SHOW TABLES LIKE 'arsip_surat'");
    $r3 = $dbDisp->query("SHOW TABLES LIKE 'dispositions'");
    $r4 = $dbDisp->query("SHOW TABLES LIKE 'surat'");
    $hasSuratDisposisi = $r1 !== false && $r1->num_rows > 0;
    $hasArsipSurat = $r2 !== false && $r2->num_rows > 0;
    $hasDispositionsLegacy = $r3 !== false && $r3->num_rows > 0;
    $hasSuratLegacy = $r4 !== false && $r4->num_rows > 0;
    if ($r1) {
        $r1->free();
    }
    if ($r2) {
        $r2->free();
    }
    if ($r3) {
        $r3->free();
    }
    if ($r4) {
        $r4->free();
    }

}

$dptKabagTandaiColExists = false;
if ($dbDisp instanceof mysqli && $hasSuratDisposisi) {
    $rKabCol = $dbDisp->query("SHOW COLUMNS FROM `surat_disposisi` LIKE 'kabag_tandai_selesai'");
    if ($rKabCol !== false && $rKabCol->num_rows > 0) {
        $dptKabagTandaiColExists = true;
    }
    if ($rKabCol) {
        $rKabCol->free();
    }
}

$itemsKeStaf = [];
$suratMasukItems = [];
$dptSessionUser = trim((string) ($_SESSION['admin_username'] ?? ''));

if ($dbDisp instanceof mysqli) {
    $dispTerbaruArsipCache = [];
    if ($hasSuratDisposisi && $dptSessionUser !== '') {
        $ord = dpt_surat_disposisi_order_by_clause($dbDisp);
        if ($hasArsipSurat) {
            $sqlMasuk = 'SELECT d.*, a.*, d.`id` AS `mdispo_surat_disposisi_id`
                FROM `surat_disposisi` d
                LEFT JOIN `arsip_surat` a ON a.`id` = d.`id_arsip`
                WHERE LOWER(TRIM(d.`penerima_username`)) = LOWER(?)
                ORDER BY ' . $ord . ' LIMIT 50';
            $sqlKeStaf = 'SELECT d.*, a.*, d.`id` AS `mdispo_surat_disposisi_id`
                FROM `surat_disposisi` d
                LEFT JOIN `arsip_surat` a ON a.`id` = d.`id_arsip`
                WHERE LOWER(TRIM(d.`pengirim_username`)) = LOWER(?)
                ORDER BY ' . $ord . ' LIMIT 50';
        } else {
            $sqlMasuk = 'SELECT d.*, d.`id` AS `mdispo_surat_disposisi_id`
                FROM `surat_disposisi` d
                WHERE LOWER(TRIM(d.`penerima_username`)) = LOWER(?)
                ORDER BY ' . $ord . ' LIMIT 50';
            $sqlKeStaf = 'SELECT d.*, d.`id` AS `mdispo_surat_disposisi_id`
                FROM `surat_disposisi` d
                WHERE LOWER(TRIM(d.`pengirim_username`)) = LOWER(?)
                ORDER BY ' . $ord . ' LIMIT 50';
        }
        $stM = $dbDisp->prepare($sqlMasuk);
        if ($stM !== false) {
            $stM->bind_param('s', $dptSessionUser);
            $stM->execute();
            $resM = $stM->get_result();
            if ($resM) {
                while ($row = $resM->fetch_assoc()) {
                    if (!is_array($row)) {
                        continue;
                    }
                    if ($dptKabagTandaiColExists) {
                        $stRow = strtolower(trim((string) ($row['status'] ?? '')));
                        $penNorm = strtolower((string) preg_replace('/\s+/u', '', trim((string) ($row['penerima_username'] ?? ''))));
                        if ($stRow === 'selesai'
                            && (int) ($row['kabag_tandai_selesai'] ?? 0) !== 1
                            && $penNorm !== 'kabag_organisasi') {
                            continue;
                        }
                    }
                    $itemsMasuk[] = dpt_map_surat_disposisi_row_to_card_item($row, $dbDisp, $hasArsipSurat, $dispTerbaruArsipCache);
                }
            }
            $stM->close();
        }
        $stK = $dbDisp->prepare($sqlKeStaf);
        if ($stK !== false) {
            $stK->bind_param('s', $dptSessionUser);
            $stK->execute();
            $resK = $stK->get_result();
            if ($resK) {
                while ($row = $resK->fetch_assoc()) {
                    if (is_array($row)) {
                        $itemsKeStaf[] = dpt_map_surat_disposisi_row_to_card_item($row, $dbDisp, $hasArsipSurat, $dispTerbaruArsipCache);
                    }
                }
            }
            $stK->close();
        }
    }

    if ($hasArsipSurat && org_arsip_surat_table_exists($dbDisp)) {
        $sqlSm = dpt_arsip_masuk_belum_disposisi_sql($dbDisp);
        if (is_string($sqlSm) && $sqlSm !== '') {
            $resSm = $dbDisp->query($sqlSm);
            if ($resSm) {
                while ($ar = $resSm->fetch_assoc()) {
                    if (!is_array($ar)) {
                        continue;
                    }
                    $aid = (int) ($ar['id'] ?? 0);
                    if ($aid <= 0) {
                        continue;
                    }
                    $createdSm = trim((string) ($ar['created_at'] ?? $ar['tanggal'] ?? $ar['tgl_upload'] ?? ''));
                    $tsSm = $createdSm !== '' ? strtotime($createdSm) : false;
                    $nomorSm = trim((string) ($ar['nomor_surat'] ?? $ar['nomor'] ?? ''));
                    $perihalSm = trim((string) ($ar['perihal_ringkasan'] ?? $ar['perihal'] ?? ''));
                    $asalSm = trim((string) ($ar['instansi_asal'] ?? $ar['asal_surat'] ?? ''));
                    $judulSm = $nomorSm !== '' ? $nomorSm : ('Surat masuk #' . $aid);
                    $pdfSm = org_arsip_surat_row_pdf_web_path($ar);
                    $ikutLabel = '';
                    if (array_key_exists('ikut_monitoring_disposisi', $ar)) {
                        $iv = $ar['ikut_monitoring_disposisi'];
                        $on = (is_bool($iv) && $iv)
                            || (is_int($iv) && $iv !== 0)
                            || (is_string($iv) && in_array(strtolower(trim($iv)), ['1', 'true', 'yes', 'on'], true));
                        $ikutLabel = $on ? 'Ya (bisa di-input disposisi di Monitoring)' : 'Tidak (hanya arsip)';
                    }
                    $suratMasukItems[] = [
                        'tanggal_ts' => $tsSm !== false && (int) $tsSm > 0 ? (int) $tsSm : 0,
                        'tanggal' => ($tsSm !== false && (int) $tsSm > 0) ? date('Y-m-d', (int) $tsSm) : '',
                        'read_key' => 'arsip_' . $aid,
                        'judul' => $judulSm,
                        'nomor' => $nomorSm !== '' ? $nomorSm : '—',
                        'perihal' => $perihalSm,
                        'asal' => $asalSm,
                        'berkas' => org_arsip_surat_row_display_filename($ar),
                        'pdf_arsip_href' => $pdfSm !== null && $pdfSm !== '' ? $pdfSm : '',
                        'monitoring_masuk_url' => 'monitoring_disposisi.php?tab=masuk&id_arsip=' . $aid,
                        'ikut_label' => $ikutLabel,
                        'arsip_id' => $aid,
                    ];
                }
                $resSm->free();
            }
        }
    }
}

if ($itemsMasuk === [] && !$hasSuratDisposisi && !$hasDispositionsLegacy) {
    $dataFile = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'disposisi_terbaru.json';
    if (is_file($dataFile)) {
        $raw = file_get_contents($dataFile);
        if ($raw !== false && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $judul = trim((string) ($row['judul'] ?? ''));
                    if ($judul === '') {
                        continue;
                    }
                    $tanggal = trim((string) ($row['tanggal'] ?? ''));
                    $ts = $tanggal !== '' ? strtotime($tanggal . ' 12:00:00') : false;
                    $readKeyJ = 'json_' . substr(hash('sha256', $judul . '|' . $tanggal . '|' . trim((string) ($row['nomor'] ?? ''))), 0, 24);
                    $itemsMasuk[] = [
                        'tanggal' => $tanggal,
                        'tanggal_ts' => $ts !== false ? (int) $ts : 0,
                        'judul' => $judul,
                        'nomor' => trim((string) ($row['nomor'] ?? '')),
                        'dari' => trim((string) ($row['dari'] ?? '')),
                        'ringkasan' => trim((string) ($row['ringkasan'] ?? '')),
                        'link' => trim((string) ($row['link'] ?? '')),
                        'read_key' => $readKeyJ,
                        'layout' => 'json',
                    ];
                }
            }
        }
    }
}

if ($dbDisp instanceof mysqli && $hasSuratDisposisi && $dptSessionUser !== '') {
    dpt_enrich_dispo_hide_monitoring_tl($dbDisp, $dptSessionUser, $itemsMasuk);
    dpt_enrich_dispo_hide_monitoring_tl($dbDisp, $dptSessionUser, $itemsKeStaf);
}

if ($dptRoleNorm === 'sub_admin_eorganisasi') {
    foreach ($itemsMasuk as &$dptItMu) {
        if (!empty($dptItMu['monitoring_tl_url'])) {
            $dptItMu['monitoring_tl_url'] = 'disposisi_awal_kabag.php';
        }
    }
    unset($dptItMu);
    foreach ($itemsKeStaf as &$dptItKs) {
        if (!empty($dptItKs['monitoring_tl_url'])) {
            $dptItKs['monitoring_tl_url'] = 'disposisi_awal_kabag.php';
        }
    }
    unset($dptItKs);
    foreach ($suratMasukItems as &$dptItSm) {
        if (!empty($dptItSm['monitoring_masuk_url'])) {
            $dptItSm['monitoring_masuk_url'] = 'disposisi_awal_kabag.php';
        }
    }
    unset($dptItSm);
}

$dptSortTs = static function (array $a, array $b): int {
    return ($b['tanggal_ts'] ?? 0) <=> ($a['tanggal_ts'] ?? 0);
};
usort($itemsMasuk, $dptSortTs);
usort($itemsKeStaf, $dptSortTs);
usort($suratMasukItems, $dptSortTs);

$dptCountMasuk = count($itemsMasuk);
$dptCountKeStaf = count($itemsKeStaf);
$dptCountSurat = count($suratMasukItems);
$dptShowAny = $dptCountMasuk > 0 || $dptCountKeStaf > 0 || $dptCountSurat > 0;
$extraHeadMarkup = <<<'HTML'
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
.page-disposisi-terbaru { font-family: 'Poppins', system-ui, sans-serif; background: #eef2f9; }
.page-disposisi-terbaru .site-main { max-width: 48rem; }
.dpt-nav-tabs { flex-wrap: nowrap; overflow-x: auto; border-bottom-color: rgba(15, 23, 42, 0.1); }
.dpt-nav-tabs .nav-link {
    font-weight: 500;
    color: #64748b;
    white-space: nowrap;
    border: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
}
.dpt-nav-tabs .nav-link:hover { color: #0f172a; border-bottom-color: rgba(59, 130, 246, 0.35); }
.dpt-nav-tabs .nav-link.active { color: #0f172a; border-bottom-color: #2563eb; background: transparent; }
.dpt-nav-tabs .badge { font-size: 0.65rem; font-weight: 600; vertical-align: middle; }
.dpt-tab-lead { max-width: 40rem; }
.dpt-ml-diteruskan { font-size: 0.8125rem; letter-spacing: 0.01em; }
.disp-hero {
    border-radius: 16px;
    border: 1px solid #dbe4f3;
    background: linear-gradient(165deg, #ffffff 0%, #f4f8ff 100%);
    box-shadow: 0 10px 32px rgba(15, 23, 42, 0.06);
}
.disp-hero__title { letter-spacing: -0.02em; }
.disp-hero__toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem 1rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(15, 23, 42, 0.06);
}
.disp-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 1rem;
    flex: 1 1 12rem;
    align-items: center;
}
.disp-legend__item {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.8125rem;
    color: #475569;
    padding: 0.25rem 0.6rem;
    background: rgba(255, 255, 255, 0.75);
    border-radius: 999px;
    border: 1px solid rgba(15, 23, 42, 0.08);
}
.disp-legend-swatch {
    width: 0.65rem;
    height: 0.65rem;
    border-radius: 50%;
    flex-shrink: 0;
}
.disp-legend-swatch--unread { background: #f97316; box-shadow: 0 0 0 2px #fff, 0 0 0 3px #ea580c; }
.disp-legend-swatch--revisi { background: #ea580c; box-shadow: 0 0 0 2px #fff, 0 0 0 3px #c2410c; }
.disp-legend-swatch--read { background: #cbd5e1; box-shadow: 0 0 0 2px #fff, 0 0 0 3px #94a3b8; }
.disp-list { gap: 1rem !important; }
.disp-card {
    border: 1px solid rgba(15, 23, 42, 0.06);
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
    border-left: 4px solid #3b82f6;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, transform 0.15s ease;
}
.disp-card:hover {
    box-shadow: 0 8px 28px rgba(15, 23, 42, 0.09);
}
.disp-card--unread {
    border-left-color: #f97316;
    border-left-width: 5px;
    background: linear-gradient(108deg, #fff7ed 0%, #ffedd5 42%, #fffdfb 88%, #ffffff 100%);
    border-color: rgba(249, 115, 22, 0.32);
    box-shadow: 0 6px 24px rgba(249, 115, 22, 0.14), 0 2px 8px rgba(234, 88, 12, 0.08);
}
.disp-card--unread:hover {
    box-shadow: 0 10px 32px rgba(249, 115, 22, 0.18), 0 4px 12px rgba(234, 88, 12, 0.1);
    border-color: rgba(249, 115, 22, 0.4);
}
.disp-card--revisi {
    border-left-color: #ea580c !important;
    border-left-width: 6px;
    background: linear-gradient(108deg, #ffedd5 0%, #fdba74 32%, #fff7ed 72%, #ffffff 100%);
    border-color: rgba(234, 88, 12, 0.42);
    box-shadow: 0 8px 28px rgba(234, 88, 12, 0.22), 0 2px 10px rgba(194, 65, 12, 0.1);
}
.disp-card--revisi:hover {
    box-shadow: 0 12px 34px rgba(234, 88, 12, 0.26), 0 4px 14px rgba(194, 65, 12, 0.12);
    border-color: rgba(234, 88, 12, 0.52);
}
.disp-perbaikan-badge {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: linear-gradient(180deg, #fb923c 0%, #ea580c 100%) !important;
    color: #fff !important;
    border: 1px solid rgba(154, 52, 18, 0.35);
    box-shadow: 0 2px 8px rgba(234, 88, 12, 0.35);
}
.disp-chip--revisi {
    background: linear-gradient(180deg, #fb923c 0%, #ea580c 100%) !important;
    color: #fff !important;
    border: 1px solid rgba(154, 52, 18, 0.25);
}
.disp-card--read {
    border-left-color: #94a3b8;
    background: #fafbfc;
    border-color: rgba(15, 23, 42, 0.05);
}
article[data-dispo-read-key] { cursor: pointer; }
article[data-dispo-read-key]:focus-visible {
    outline: 2px solid #2563eb;
    outline-offset: 3px;
}
.disp-card__top {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}
.disp-card__date { font-size: 0.8125rem; color: #64748b; margin: 0; }
.disp-new-badge {
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.disp-card__title { font-size: 1.05rem; font-weight: 600; color: #0f172a; line-height: 1.35; margin-bottom: 0.75rem; }
.disp-meta {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 0.35rem 0.75rem;
    font-size: 0.8125rem;
    margin-bottom: 0.75rem;
    padding: 0.65rem 0.75rem;
    background: rgba(241, 245, 249, 0.85);
    border-radius: 8px;
    border: 1px solid rgba(15, 23, 42, 0.05);
}
.disp-meta + .disp-block { margin-top: 0.65rem; }
.disp-chip-row + .disp-meta { margin-top: 0.35rem; }
.disp-meta dd { margin: 0; color: #1e293b; word-break: break-word; }
.disp-chip-row { display: flex; flex-wrap: wrap; gap: 0.35rem; margin-bottom: 0.65rem; }
.disp-chip { font-size: 0.7rem; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 6px; }
.disp-chip--status { background: #e0e7ff; color: #3730a3; }
.disp-chip--surat { background: #dbeafe; color: #1e40af; }
.disp-block {
    font-size: 0.875rem;
    line-height: 1.55;
    color: #334155;
    margin-bottom: 0.65rem;
    padding: 0.65rem 0.75rem;
    background: #fff;
    border-radius: 8px;
    border: 1px solid rgba(15, 23, 42, 0.06);
}
.disp-block:last-of-type { margin-bottom: 0; }
.disp-block__label { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 0.35rem; }
.disp-json-body { font-size: 0.875rem; line-height: 1.6; color: #475569; white-space: pre-wrap; word-break: break-word; }
.disp-card__actions { margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid rgba(15, 23, 42, 0.06); }
.disp-card__actions .btn { margin-right: 0.35rem; margin-bottom: 0.25rem; }
.disp-empty { border-radius: 12px; border: 1px dashed #cbd5e1; background: #fafbfc; }
.dpt-section-title { font-weight: 600; color: #0f172a; letter-spacing: -0.01em; }
.dpt-section-lead { max-width: 40rem; }
.disp-card--arsip-masuk { border-left-color: #059669; }
.disp-card--arsip-masuk.disp-card--unread {
    border-left-color: #10b981;
    border-left-width: 5px;
    background: linear-gradient(108deg, #d1fae5 0%, #a7f3d0 40%, #ecfdf5 85%, #ffffff 100%);
    border-color: rgba(16, 185, 129, 0.35);
    box-shadow: 0 6px 24px rgba(16, 185, 129, 0.14), 0 2px 8px rgba(5, 150, 105, 0.08);
}
.disp-card--arsip-masuk.disp-card--unread:hover {
    box-shadow: 0 10px 32px rgba(16, 185, 129, 0.18), 0 4px 12px rgba(5, 150, 105, 0.1);
    border-color: rgba(16, 185, 129, 0.45);
}
</style>
HTML;

require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>
<div class="container site-main section-spacing">
    <nav class="mb-3" aria-label="Navigasi E-Organisasi">
        <a class="small text-decoration-none" href="<?php echo org_href('e_organisasi.php'); ?>">&larr; Kembali ke E-Organisasi</a>
    </nav>
    <div class="disp-hero p-4 p-lg-4 mb-4">
        <h1 class="h4 mb-3 text-dark disp-hero__title">Disposisi dan Surat Masuk terbaru</h1>
        <div class="disp-hero__toolbar">
            <div class="disp-legend" role="note">
                <span class="disp-legend__item"><span class="disp-legend-swatch disp-legend-swatch--revisi" aria-hidden="true"></span>Instruksi perbaikan</span>
                <span class="disp-legend__item"><span class="disp-legend-swatch disp-legend-swatch--unread" aria-hidden="true"></span>Belum dibaca</span>
                <span class="disp-legend__item"><span class="disp-legend-swatch disp-legend-swatch--read" aria-hidden="true"></span>Sudah dibaca</span>
            </div>
            <button type="button" class="btn btn-sm btn-light border" id="dispoMarkAllRead">Tandai semua sudah dibaca</button>
        </div>
    </div>
    <?php
    $dptTabPaneMasuk = $dptTab === 'masuk' ? 'show active' : '';
    $dptTabPaneKeStaf = $dptTab === 'ke_staf' ? 'show active' : '';
    $dptTabPaneSurat = $dptTab === 'surat' ? 'show active' : '';
    $dptNavMasukActive = $dptTab === 'masuk' ? 'active' : '';
    $dptNavKeStafActive = $dptTab === 'ke_staf' ? 'active' : '';
    $dptNavSuratActive = $dptTab === 'surat' ? 'active' : '';
    ?>
    <?php if (!$dptShowAny): ?>
        <div class="card border-0 disp-empty">
            <div class="card-body p-4 text-center text-muted">
                <p class="mb-2">Belum ada data di ketiga kategori untuk akun Anda.</p>
                <?php if (!empty($isAdmin)): ?>
                    <p class="small mb-1">Pastikan tabel <code>surat_disposisi</code> dan <code>arsip_surat</code> tersedia, username login cocok dengan kolom pengirim/penerima, dan ada arsip masuk tanpa baris disposisi untuk tab Surat Masuk. Cadangan JSON (tab Disposisi Masuk): <code>data/disposisi_terbaru.json</code>.</p>
                    <p class="small mb-0"><a href="<?php echo $dptRoleNorm === 'sub_admin_eorganisasi' ? 'disposisi_awal_kabag.php' : 'monitoring_disposisi.php'; ?>"><?php echo $dptRoleNorm === 'sub_admin_eorganisasi' ? 'Disposisi awal &amp; tanda terima Kabag' : 'Monitoring Disposisi'; ?></a> · <a href="<?php echo org_href('arsip.php'); ?>">Arsip</a></p>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <ul class="nav nav-tabs dpt-nav-tabs mb-3 flex-nowrap" role="tablist">
            <li class="nav-item" role="presentation">
                <a id="dpt-tab-masuk" class="nav-link <?php echo htmlspecialchars($dptNavMasukActive, ENT_QUOTES, 'UTF-8'); ?>" href="<?php echo org_href('disposisi_terbaru.php', 'tab=masuk'); ?>" role="tab">Disposisi Masuk <span class="badge rounded-pill text-bg-primary"><?php echo (int) $dptCountMasuk; ?></span></a>
            </li>
            <li class="nav-item" role="presentation">
                <a id="dpt-tab-ke-staf" class="nav-link <?php echo htmlspecialchars($dptNavKeStafActive, ENT_QUOTES, 'UTF-8'); ?>" href="<?php echo org_href('disposisi_terbaru.php', 'tab=ke_staf'); ?>" role="tab">Disposisi Ke Staf <span class="badge rounded-pill text-bg-secondary"><?php echo (int) $dptCountKeStaf; ?></span></a>
            </li>
            <li class="nav-item" role="presentation">
                <a id="dpt-tab-surat" class="nav-link <?php echo htmlspecialchars($dptNavSuratActive, ENT_QUOTES, 'UTF-8'); ?>" href="<?php echo org_href('disposisi_terbaru.php', 'tab=surat'); ?>" role="tab">Surat Masuk <span class="badge rounded-pill text-bg-success"><?php echo (int) $dptCountSurat; ?></span></a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade <?php echo htmlspecialchars($dptTabPaneMasuk, ENT_QUOTES, 'UTF-8'); ?>" id="dpt-pane-masuk" role="tabpanel" aria-labelledby="dpt-tab-masuk" tabindex="0">
                <p class="small text-muted mb-3 dpt-tab-lead">Baris dari <code class="small">surat_disposisi</code> di mana <strong>penerima</strong> adalah Anda (<code class="small"><?php echo htmlspecialchars($dptSessionUser !== '' ? $dptSessionUser : '(belum login)', ENT_QUOTES, 'UTF-8'); ?></code>). Untuk tugas ke staf: entri berstatus <strong>selesai</strong> yang belum diverifikasi Kabag disembunyikan sampai Kabag menandainya di Monitoring.</p>
                <?php if ($itemsMasuk === []): ?>
                    <div class="card border-0 disp-empty mb-0"><div class="card-body p-3 text-muted small">Tidak ada disposisi masuk untuk akun ini.</div></div>
                <?php else: ?>
                    <ul class="list-unstyled d-flex flex-column disp-list mb-0"><?php dpt_render_dispo_cards_html($itemsMasuk); ?></ul>
                <?php endif; ?>
            </div>
            <div class="tab-pane fade <?php echo htmlspecialchars($dptTabPaneKeStaf, ENT_QUOTES, 'UTF-8'); ?>" id="dpt-pane-ke-staf" role="tabpanel" aria-labelledby="dpt-tab-ke-staf" tabindex="0">
                <p class="small text-muted mb-3 dpt-tab-lead">Baris dari <code class="small">surat_disposisi</code> di mana <strong>pengirim</strong> adalah Anda — memantau yang sudah diteruskan (misalnya ke staf).</p>
                <?php if ($itemsKeStaf === []): ?>
                    <div class="card border-0 disp-empty mb-0"><div class="card-body p-3 text-muted small">Belum ada baris dengan Anda sebagai pengirim.</div></div>
                <?php else: ?>
                    <ul class="list-unstyled d-flex flex-column disp-list mb-0"><?php dpt_render_dispo_cards_html($itemsKeStaf); ?></ul>
                <?php endif; ?>
            </div>
            <div class="tab-pane fade <?php echo htmlspecialchars($dptTabPaneSurat, ENT_QUOTES, 'UTF-8'); ?>" id="dpt-pane-surat" role="tabpanel" aria-labelledby="dpt-tab-surat" tabindex="0">
                <p class="small text-muted mb-3 dpt-tab-lead">Surat masuk di <code class="small">arsip_surat</code> yang <strong>belum</strong> memiliki entri di <code class="small">surat_disposisi</code> (berdasarkan <code class="small">id_arsip</code>). Gunakan Monitoring untuk input disposisi awal bila surat diarsipkan untuk alur tersebut.</p>
                <?php if ($suratMasukItems === []): ?>
                    <div class="card border-0 disp-empty mb-0"><div class="card-body p-3 text-muted small">Tidak ada surat masuk tanpa disposisi, atau tabel/kolom arsip belum mendukung filter ini.</div></div>
                <?php else: ?>
                    <ul class="list-unstyled d-flex flex-column disp-list mb-0"><?php dpt_render_arsip_masuk_belum_dispo_cards_html($suratMasukItems); ?></ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php if ($itemsMasuk !== [] || $itemsKeStaf !== [] || $suratMasukItems !== []): ?>
<script>
(function () {
    var STORAGE_KEY = 'bo_disposisi_terbaru_dibaca';
    function readMap() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            var o = raw ? JSON.parse(raw) : {};
            return o && typeof o === 'object' ? o : {};
        } catch (e) {
            return {};
        }
    }
    function writeMap(obj) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(obj));
        } catch (e) {}
    }
    function isRead(key) {
        return !!readMap()[key];
    }
    function markRead(key) {
        var o = readMap();
        o[key] = 1;
        writeMap(o);
    }
    function applyCard(article) {
        var key = article.getAttribute('data-dispo-read-key');
        if (!key) {
            return;
        }
        var card = article.classList.contains('disp-card') ? article : article.closest('.disp-card');
        if (!card) {
            return;
        }
        var isPerbaikan = article.getAttribute('data-dispo-perbaikan') === '1';
        if (isPerbaikan) {
            card.classList.add('disp-card--revisi');
            card.classList.remove('disp-card--unread', 'disp-card--read');
            return;
        }
        var read = isRead(key);
        card.classList.toggle('disp-card--unread', !read);
        card.classList.toggle('disp-card--read', read);
        card.querySelectorAll('[data-dispo-new-badge]').forEach(function (badge) {
            if (read) {
                badge.setAttribute('hidden', 'hidden');
            } else {
                badge.removeAttribute('hidden');
            }
        });
    }
    function onMark(article) {
        var key = article.getAttribute('data-dispo-read-key');
        if (!key || isRead(key)) {
            return;
        }
        markRead(key);
        applyCard(article);
    }
    document.querySelectorAll('[data-dispo-read-key]').forEach(function (article) {
        applyCard(article);
        article.addEventListener('click', function () {
            onMark(article);
        });
        article.addEventListener('keydown', function (ev) {
            if (ev.key === 'Enter' || ev.key === ' ') {
                ev.preventDefault();
                onMark(article);
            }
        });
    });
    var btn = document.getElementById('dispoMarkAllRead');
    if (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('[data-dispo-read-key]').forEach(function (article) {
                var key = article.getAttribute('data-dispo-read-key');
                if (key) {
                    markRead(key);
                }
                applyCard(article);
            });
        });
    }
})();
</script>
<?php endif; ?>
<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
