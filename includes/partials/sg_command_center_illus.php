<?php

/**
 * Governance command center — node interaktif (4 sudut + hub tengah).
 *
 * @var string $sgGovHubHref
 */
$sgGovHubHref = 'index.php#beranda-dashboard-widgets';

$sgGovNodes = [
    [
        'class' => 'sg-command-center__node-btn--1',
        'href' => 'index.php#beranda-dashboard-widgets',
        'icon' => 'fa-chart-line',
        'label' => 'Dashboard kinerja — monitoring indikator OPD',
        'tooltip' => 'Dashboard Kinerja',
    ],
    [
        'class' => 'sg-command-center__node-btn--2',
        'href' => 'index.php#beranda-pusat-informasi',
        'icon' => 'fa-bullhorn',
        'label' => 'Pengumuman terbaru — informasi resmi',
        'tooltip' => 'Pengumuman',
    ],
    [
        'class' => 'sg-command-center__node-btn--3',
        'href' => 'profil.php#profil-struktur-organisasi',
        'icon' => 'fa-sitemap',
        'label' => 'Struktur organisasi — visi dan tata kelola',
        'tooltip' => 'Struktur',
    ],
    [
        'class' => 'sg-command-center__node-btn--4',
        'href' => 'e_organisasi.php',
        'icon' => 'fa-network-wired',
        'label' => 'E-Organisasi — akses internal',
        'tooltip' => 'E-Organisasi',
    ],
];
if (empty($canAccessEOrganisasi)) {
    $sgGovNodes[3]['href'] = '#loginModal';
    $sgGovNodes[3]['open_login_modal'] = true;
}
?>
<nav
    class="sg-command-center sg-command-center--interactive"
    aria-label="Governance hub interaktif"
>
    <svg class="sg-command-center__mesh" viewBox="0 0 480 480" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
        <g class="sg-command-center__mesh-lines" stroke="rgba(34,211,238,0.45)" stroke-width="1" fill="none">
            <path d="M240 48 L392 142 L392 338 L240 432 L88 338 L88 142 Z"/>
            <circle cx="240" cy="240" r="88" stroke-dasharray="5 7" opacity="0.65"/>
        </g>
        <circle cx="240" cy="240" r="4" fill="rgba(56,189,248,0.9)" aria-hidden="true"/>
    </svg>

    <?php foreach ($sgGovNodes as $node): ?>
        <a
            href="<?php echo htmlspecialchars((string) $node['href'], ENT_QUOTES, 'UTF-8'); ?>"
            class="sg-command-center__node-btn <?php echo htmlspecialchars((string) $node['class'], ENT_QUOTES, 'UTF-8'); ?>"
            aria-label="<?php echo htmlspecialchars((string) $node['label'], ENT_QUOTES, 'UTF-8'); ?>"
            <?php if (!empty($node['open_login_modal'])): ?> data-bs-toggle="modal" data-bs-target="#loginModal" role="button"<?php endif; ?>
        >
            <span class="sg-command-center__node-btn__icon" aria-hidden="true">
                <i class="fa-solid <?php echo htmlspecialchars((string) $node['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
            </span>
            <span class="sg-command-center__tooltip"><?php echo htmlspecialchars((string) $node['tooltip'], ENT_QUOTES, 'UTF-8'); ?></span>
        </a>
    <?php endforeach; ?>

    <a
        href="<?php echo htmlspecialchars($sgGovHubHref, ENT_QUOTES, 'UTF-8'); ?>"
        class="sg-command-center__hub-btn"
        aria-label="Pusat Governance — indikator dan statistik utama"
        title="Pusat Governance"
    >
        <span class="sg-command-center__hub-ring" aria-hidden="true"></span>
        <span class="sg-command-center__hub-core">
            <i class="fa-solid fa-building-columns" aria-hidden="true"></i>
            <span class="sg-command-center__hub-label">Pusat Governance</span>
        </span>
        <span class="sg-command-center__hub-tooltip" aria-hidden="true">Pusat Governance</span>
    </a>
</nav>
