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
        <defs>
            <linearGradient id="sgMeshGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#22d3ee" stop-opacity="0.55"/>
                <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.2"/>
            </linearGradient>
            <filter id="sgMeshGlow" x="-20%" y="-20%" width="140%" height="140%">
                <feGaussianBlur stdDeviation="2" result="b"/>
                <feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge>
            </filter>
        </defs>
        <g
            class="sg-command-center__mesh-lines"
            stroke="url(#sgMeshGrad)"
            stroke-width="0.75"
            fill="none"
            opacity="0.65"
            filter="url(#sgMeshGlow)"
        >
            <path d="M240 40 L400 140 L400 340 L240 440 L80 340 L80 140 Z"/>
            <path d="M240 40 L240 440"/>
            <path d="M80 140 L400 140"/>
            <path d="M80 340 L400 340"/>
            <path d="M160 90 L320 390"/>
            <path d="M320 90 L160 390"/>
            <circle cx="240" cy="240" r="72" stroke-dasharray="4 6"/>
            <circle cx="240" cy="240" r="120" stroke-dasharray="2 8" opacity="0.5"/>
        </g>
        <g fill="#22d3ee" aria-hidden="true">
            <circle class="sg-command-center__pulse" cx="240" cy="240" r="5"/>
        </g>
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
        aria-label="Pusat governance portal — indikator dan statistik utama"
    >
        <span class="sg-command-center__hub-ring" aria-hidden="true"></span>
        <span class="sg-command-center__hub-core" aria-hidden="true">
            <i class="fa-solid fa-building-columns"></i>
        </span>
        <span class="sg-command-center__hub-tooltip">Pusat Governance</span>
    </a>
</nav>
