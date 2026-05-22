<?php

/**
 * Breakdown angka untuk penjelasan rumus di dashboard monitoring.
 *
 * @return array<string, mixed>
 */
function admin_dashboard_formula_breakdown(array $dashMetrics, int $saranTotal = 0): array
{
    $docTotal = max(1, (int) ($dashMetrics['dokumen_total'] ?? 0));
    $unduh = (int) ($dashMetrics['unduhan_total'] ?? 0);
    $tamuWeek = (int) ($dashMetrics['tamu_minggu'] ?? 0);
    $infoTotal = (int) ($dashMetrics['berita_total'] ?? 0) + (int) ($dashMetrics['pengumuman_total'] ?? 0);
    $staf = (int) ($dashMetrics['staf_total'] ?? 0);
    $galeri = (int) ($dashMetrics['galeri_total'] ?? 0);

    $kpiUnduh = (int) round(($unduh / $docTotal) * 35);
    $kpiTamu = (int) round(min(100, $tamuWeek * 8) * 0.35);
    $kpiInfo = (int) round(min(100, $infoTotal * 5) * 0.3);
    $kpiPelayanan = (int) ($dashMetrics['kpi_pelayanan'] ?? 0);

    $orgStaf = (int) round(min(100, $staf * 12) * 0.2);
    $orgInfo = (int) round(min(100, $infoTotal * 8) * 0.25);
    $orgGaleri = (int) round(min(100, $galeri * 15) * 0.15);
    $orgKpi = (int) round($kpiPelayanan * 0.4);

    $skorSaran = max(0, 100 - min(40, $saranTotal * 4));
    $kepuasanKpi = (int) round($kpiPelayanan * 0.55);
    $kepuasanSaran = (int) round($skorSaran * 0.45);

    $teamTotal = (int) ($dashMetrics['team_total'] ?? 0);
    $teamSelesai = (int) ($dashMetrics['team_selesai'] ?? 0);
    $teamPct = (int) ($dashMetrics['team_progress_pct'] ?? 0);
    $kinerjaFallback = (int) min(100, $staf * 15);
    $kinerjaUsesTeam = $teamTotal > 0;

    return [
        'doc_total' => $docTotal,
        'unduh' => $unduh,
        'tamu_week' => $tamuWeek,
        'tamu_hari' => (int) ($dashMetrics['tamu_hari_ini'] ?? 0),
        'info_total' => $infoTotal,
        'berita' => (int) ($dashMetrics['berita_total'] ?? 0),
        'pengumuman' => (int) ($dashMetrics['pengumuman_total'] ?? 0),
        'staf' => $staf,
        'galeri' => $galeri,
        'layanan' => (int) ($dashMetrics['layanan_total'] ?? 0),
        'saran' => $saranTotal,
        'kpi_unduh' => $kpiUnduh,
        'kpi_tamu' => $kpiTamu,
        'kpi_info' => $kpiInfo,
        'kpi_pelayanan' => $kpiPelayanan,
        'org_staf' => $orgStaf,
        'org_info' => $orgInfo,
        'org_galeri' => $orgGaleri,
        'org_kpi' => $orgKpi,
        'org_score' => (int) ($dashMetrics['org_score'] ?? 0),
        'skor_saran' => $skorSaran,
        'kepuasan_kpi' => $kepuasanKpi,
        'kepuasan_saran' => $kepuasanSaran,
        'kepuasan' => (int) ($dashMetrics['kepuasan_publik'] ?? ($kepuasanKpi + $kepuasanSaran)),
        'team_total' => $teamTotal,
        'team_selesai' => $teamSelesai,
        'team_pct' => $teamPct,
        'kinerja' => $kinerjaUsesTeam ? $teamPct : $kinerjaFallback,
        'kinerja_uses_team' => $kinerjaUsesTeam,
    ];
}

/**
 * @param array<int, string> $lines
 */
function sg_render_formula_details(string $summary, array $lines, string $note = ''): void
{
    ?>
    <details class="sg-formula-details">
        <summary class="sg-formula-details__toggle">
            <i data-lucide="info" aria-hidden="true"></i>
            <span><?php echo htmlspecialchars($summary, ENT_QUOTES, 'UTF-8'); ?></span>
        </summary>
        <div class="sg-formula-details__body">
            <ul class="sg-formula-details__list mb-0">
                <?php foreach ($lines as $line): ?>
                <li><?php echo $line; ?></li>
                <?php endforeach; ?>
            </ul>
            <?php if ($note !== ''): ?>
            <p class="sg-formula-details__note"><?php echo htmlspecialchars($note, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
        </div>
    </details>
    <?php
}
