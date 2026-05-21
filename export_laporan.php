<?php
declare(strict_types=1);
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'arsip_kategori_bagian.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'arsip_surat_db.php';
org_require_level_access(['super_admin', 'admin', 'sub_admin_eorganisasi']);

$db = org_db();
$type = strtolower((string) ($_GET['type'] ?? 'all'));

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="laporan_e_organisasi_' . date('Ymd_His') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

echo "<table border='1'>";
echo "<tr><th colspan='8' style='background:#1d4ed8;color:#fff;'>LAPORAN E-ORGANISASI</th></tr>";
echo "<tr><td colspan='8'>Tanggal Export: " . htmlspecialchars(date('d-m-Y H:i:s'), ENT_QUOTES, 'UTF-8') . "</td></tr>";
echo "</table><br>";

if (($type === 'all' || $type === 'tamu') && $db instanceof mysqli) {
    echo "<table border='1'>";
    echo "<tr><th colspan='8' style='background:#dbeafe;'>Ringkasan Buku Tamu per Unit/Tujuan</th></tr>";
    echo "<tr><th>No</th><th colspan='6'>Unit/Tujuan</th><th>Jumlah</th></tr>";
    $tujuanField = 'tujuan_bertamu';
    $colRes = $db->query("SHOW COLUMNS FROM `tamu`");
    $cols = [];
    if ($colRes !== false) {
        while ($c = $colRes->fetch_assoc()) {
            $f = (string) ($c['Field'] ?? '');
            if ($f !== '') {
                $cols[$f] = true;
            }
        }
    }
    if (!isset($cols[$tujuanField])) {
        $tujuanField = isset($cols['unit_tujuan']) ? 'unit_tujuan' : (isset($cols['bidang_tujuan']) ? 'bidang_tujuan' : 'tujuan');
    }
    $group = $db->query("SELECT `$tujuanField` AS tujuan, COUNT(*) AS jml FROM `tamu` GROUP BY `$tujuanField` ORDER BY jml DESC");
    $no = 1;
    if ($group !== false) {
        while ($r = $group->fetch_assoc()) {
            $tujuan = (string) ($r['tujuan'] ?? '-');
            $jml = (int) ($r['jml'] ?? 0);
            echo "<tr><td>{$no}</td><td colspan='6'>" . htmlspecialchars($tujuan !== '' ? $tujuan : '-', ENT_QUOTES, 'UTF-8') . "</td><td>{$jml}</td></tr>";
            $no++;
        }
    }
    echo "</table><br>";

    echo "<table border='1'>";
    echo "<tr><th colspan='8' style='background:#dbeafe;'>Data Mentah Buku Tamu</th></tr>";
    echo "<tr><th>No</th><th>Nama</th><th>Tujuan</th><th>Personel</th><th>Instansi</th><th>Kontak</th><th>Keperluan</th><th>Tanggal</th></tr>";
    $dateField = isset($cols['created_at']) ? 'created_at' : (isset($cols['tanggal']) ? 'tanggal' : 'tanggal_kunjungan');
    $raw = $db->query("SELECT * FROM `tamu` ORDER BY `$dateField` DESC");
    $no = 1;
    if ($raw !== false) {
        while ($g = $raw->fetch_assoc()) {
            $nama = (string) ($g['nama'] ?? $g['nama_tamu'] ?? '-');
            $tujuan = (string) ($g['tujuan_bertamu'] ?? $g['unit_tujuan'] ?? $g['bidang_tujuan'] ?? $g['tujuan'] ?? '-');
            $personel = (string) ($g['nama_personel'] ?? $g['personel'] ?? $g['personnel'] ?? $g['petugas'] ?? '-');
            $instansi = (string) ($g['instansi'] ?? $g['asal_instansi'] ?? '-');
            $kontak = (string) ($g['no_hp'] ?? $g['no_telp'] ?? $g['telepon'] ?? '-');
            $keperluan = (string) ($g['keperluan'] ?? $g['pesan'] ?? '-');
            $tgl = (string) ($g['created_at'] ?? $g['tanggal'] ?? $g['tanggal_kunjungan'] ?? '-');
            echo '<tr><td>' . $no . '</td><td>' . htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars($tujuan, ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars($personel, ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars($instansi, ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars($kontak, ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars($keperluan, ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars($tgl, ENT_QUOTES, 'UTF-8') . '</td></tr>';
            $no++;
        }
    }
    echo "</table><br>";
}

if ($type === 'all' || $type === 'surat') {
    $arsipMetaFile = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'arsip_surat_meta.json';
    $metaMap = [];
    if (is_file($arsipMetaFile)) {
        $raw = file_get_contents($arsipMetaFile);
        if ($raw !== false && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $metaMap = $decoded;
            }
        }
    }
    $dirs = [
        'masuk' => __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'surat_masuk',
        'keluar' => __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'surat_keluar',
    ];
    $rows = [];
    foreach ($dirs as $jenis => $dir) {
        if (!is_dir($dir)) {
            continue;
        }
        $items = scandir($dir);
        if (!is_array($items)) {
            continue;
        }
        foreach ($items as $f) {
            if ($f === '.' || $f === '..') {
                continue;
            }
            $fp = $dir . DIRECTORY_SEPARATOR . $f;
            if (!is_file($fp) || strtolower((string) pathinfo($f, PATHINFO_EXTENSION)) !== 'pdf') {
                continue;
            }
            $rows[] = [
                'jenis' => $jenis,
                'nama_file' => $f,
                'tgl' => date('d-m-Y H:i:s', (int) filemtime($fp)),
                'meta' => $metaMap[$jenis . '|' . $f] ?? [],
            ];
        }
    }
    echo "<table border='1'>";
    echo "<tr><th colspan='9' style='background:#dcfce7;'>Data Mentah Arsip Surat</th></tr>";
    echo "<tr><th>No</th><th>Jenis</th><th>Kategori Bagian</th><th>Nomor Surat</th><th>Nama File</th><th>Instansi Asal</th><th>Instansi Tujuan</th><th>Monitoring Disposisi</th><th>Tanggal Upload</th></tr>";
    $no = 1;
    foreach ($rows as $r) {
        $jenis = (string) ($r['jenis'] ?? '');
        $jenisLabel = $jenis === 'masuk' ? 'Surat Masuk' : 'Surat Keluar';
        $meta = is_array($r['meta'] ?? null) ? $r['meta'] : [];
        $kategoriLabel = org_arsip_kategori_bagian_label($meta);
        $nomor = trim((string) ($meta['nomor_surat'] ?? ''));
        if ($nomor === '') {
            $nomor = '-';
        }
        $asal = (string) ($meta['instansi_asal'] ?? '-');
        $tujuan = (string) ($meta['instansi_tujuan'] ?? '-');
        $mon = '-';
        if ($jenis === 'masuk') {
            $mon = org_arsip_meta_row_syncs_to_monitoring_table('masuk', $meta) ? 'Ya' : 'Tidak';
        }
        echo '<tr><td>' . $no . '</td><td>' . htmlspecialchars($jenisLabel, ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars($kategoriLabel, ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars($nomor, ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars((string) $r['nama_file'], ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars($asal, ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars($tujuan, ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars($mon, ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars((string) $r['tgl'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
        $no++;
    }
    echo '</table>';
}
exit;
