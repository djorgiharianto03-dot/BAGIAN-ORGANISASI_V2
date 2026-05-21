<?php

declare(strict_types=1);



/** @var bool $isSubAdminActor */

/** @var bool $auditRiwayatVisible */



$admQuickItems = [];



if (!$isSubAdminActor) {

    $admQuickItems[] = [

        'href' => '#panel-manajemen-staf',

        'nav' => 'section',

        'icon' => 'users',

        'title' => 'Manajemen staf',

        'desc' => 'Email Google & reset password akun',

        'tone' => 'blue',

    ];

    $admQuickItems[] = [

        'href' => '#tab-konten',

        'nav' => 'tab',

        'icon' => 'pen-line',

        'title' => 'Konten halaman',

        'desc' => 'Visi, misi, dan teks situs',

        'tone' => 'violet',

    ];

    $admQuickItems[] = [

        'href' => '#tab-pusat',

        'nav' => 'tab',

        'icon' => 'megaphone',

        'title' => 'Pusat informasi',

        'desc' => 'Berita & pengumuman beranda',

        'tone' => 'rose',

    ];

    $admQuickItems[] = [

        'href' => '#panel-unggah-dokumen',

        'nav' => 'section',

        'icon' => 'cloud-upload',

        'title' => 'Unggah dokumen',

        'desc' => 'Tambah ke perpustakaan digital',

        'tone' => 'cyan',

    ];

    $admQuickItems[] = [

        'href' => '#panel-kelola-dokumen',

        'nav' => 'section',

        'icon' => 'folder-open',

        'title' => 'Kelola dokumen',

        'desc' => 'Filter, buka, dan hapus file',

        'tone' => 'indigo',

    ];

    $admQuickItems[] = [

        'href' => '../berita.php',

        'nav' => 'external',

        'icon' => 'newspaper',

        'title' => 'Halaman publik informasi',

        'desc' => 'Berita & pengumuman untuk pengunjung',

        'tone' => 'blue',

    ];

    $admQuickItems[] = [

        'href' => '#tab-layanan',

        'nav' => 'tab',

        'icon' => 'handshake',

        'title' => 'Manajemen layanan',

        'desc' => 'Daftar layanan & deskripsi SOP',

        'tone' => 'emerald',

    ];

    $admQuickItems[] = [

        'href' => '../profil.php',

        'nav' => 'external',

        'icon' => 'landmark',

        'title' => 'Profil publik',

        'desc' => 'Pratinjau halaman profil',

        'tone' => 'slate',

    ];

    if ($auditRiwayatVisible) {

        $admQuickItems[] = [

            'href' => '#panel-audit',

            'nav' => 'section',

            'icon' => 'clipboard-list',

            'title' => 'Audit trail',

            'desc' => 'Riwayat perubahan konten',

            'tone' => 'amber',

        ];

    }

    $admQuickItems[] = [

        'href' => '#panel-digital-library-stats',

        'nav' => 'section',

        'icon' => 'bar-chart-3',

        'title' => 'Statistik unduhan',

        'desc' => 'Dokumen paling sering diunduh',

        'tone' => 'cyan',

    ];

}



$admQuickItems[] = [

    'href' => '#tab-galeri',

    'nav' => 'tab',

    'icon' => 'images',

    'title' => 'Galeri kegiatan',

    'desc' => 'Unggah foto untuk halaman publik',

    'tone' => 'violet',

];

$admQuickItems[] = [

    'href' => '../galeri.php',

    'nav' => 'external',

    'icon' => 'eye',

    'title' => 'Lihat galeri publik',

    'desc' => 'Tampilan untuk pengunjung',

    'tone' => 'rose',

];

?>

<section class="adm-quick-access dash-section" id="panel-akses-cepat" aria-labelledby="adm-quick-access-title">

    <header class="adm-quick-access__head">

        <div>

            <h2 id="adm-quick-access-title" class="adm-quick-access__title">Akses cepat</h2>

            <p class="adm-quick-access__subtitle">Navigasi singkat ke modul yang paling sering digunakan</p>

        </div>

        <span class="adm-quick-access__count"><?php echo count($admQuickItems); ?> modul</span>

    </header>

    <div class="adm-quick-grid">

        <?php foreach ($admQuickItems as $item): ?>

            <?php

            $tone = preg_replace('/[^a-z0-9_-]/', '', (string) ($item['tone'] ?? 'blue'));

            $href = (string) ($item['href'] ?? '#');

            $nav = (string) ($item['nav'] ?? 'section');

            if (!in_array($nav, ['section', 'tab', 'external'], true)) {

                $nav = 'section';

            }

            $target = '';

            if ($nav !== 'external' && str_starts_with($href, '#')) {

                $target = substr($href, 1);

            }

            ?>

            <a

                class="adm-quick-card adm-quick-card--<?php echo htmlspecialchars($tone, ENT_QUOTES, 'UTF-8'); ?>"

                href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>"

                data-adm-quick-nav="<?php echo htmlspecialchars($nav, ENT_QUOTES, 'UTF-8'); ?>"

                <?php if ($nav === 'section' && $target !== ''): ?>

                    data-adm-section="<?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8'); ?>"

                <?php elseif ($nav === 'tab' && $target !== ''): ?>

                    data-adm-tab="<?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8'); ?>"

                <?php endif; ?>

                <?php if ($nav === 'external'): ?>

                    target="_blank"

                    rel="noopener noreferrer"

                <?php endif; ?>

            >

                <span class="adm-quick-card__glow" aria-hidden="true"></span>

                <span class="adm-quick-card__icon" aria-hidden="true">

                    <i data-lucide="<?php echo htmlspecialchars((string) ($item['icon'] ?? 'zap'), ENT_QUOTES, 'UTF-8'); ?>"></i>

                </span>

                <span class="adm-quick-card__content">

                    <span class="adm-quick-card__label"><?php echo htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>

                    <span class="adm-quick-card__desc"><?php echo htmlspecialchars((string) ($item['desc'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>

                </span>

                <span class="adm-quick-card__arrow" aria-hidden="true">

                    <i data-lucide="arrow-up-right"></i>

                </span>

            </a>

        <?php endforeach; ?>

    </div>

</section>

