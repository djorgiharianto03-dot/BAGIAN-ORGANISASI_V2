<?php

/**
 * Navbar — semantic org-navbar + legacy site-header hooks for JS.
 *
 * @var string $navActive
 * @var string $logoWebPath
 * @var string $searchQuery
 * @var bool $hideHeaderDocSearch
 * @var bool $hideHeaderSubtitle
 * @var bool $isAdmin
 * @var bool $canAccessPublikasi
 * @var bool $canAccessEOrganisasi
 * @var bool $smartPortalNav
 * @var string $holidayUcapan
 * @var string $holidayUcapanMain
 * @var string $holidayUcapanSub
 * @var string $holidayBadge
 * @var string $holidayDecoIcon
 * @var string $siteLogoAlt
 */

org_tailwind_bootstrap();

$siteLogoAlt = isset($siteLogoAlt) && is_string($siteLogoAlt) && $siteLogoAlt !== ''
    ? $siteLogoAlt
    : 'Logo Bagian Organisasi Setda Kabupaten Kepulauan Aru';

$smartPortalNav = !empty($smartPortalNav);
$headerClass = org_ui_class(
    'org-navbar',
    'site-header',
    $smartPortalNav ? 'site-header--sg-portal' : ''
);
?>
<header class="<?php echo $headerClass; ?>">
    <div class="org-navbar__gradient site-header__gradient">
<?php if ($smartPortalNav): ?>
        <div class="container-global site-header__rail header-inner">
            <div class="org-navbar__top site-header__topbar">
                <div class="org-navbar__brand site-header__brand-row">
                    <a href="<?php echo htmlspecialchars(function_exists('org_home_url') ? org_home_url() : 'index.php', ENT_QUOTES, 'UTF-8'); ?>" class="site-header__brand-link no-underline">
                        <?php if (($logoWebPath ?? '') !== ''): ?>
                            <img src="<?php echo htmlspecialchars($logoWebPath, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($siteLogoAlt, ENT_QUOTES, 'UTF-8'); ?>" class="org-navbar__logo site-header__logo" width="112" height="56" loading="eager" decoding="async"<?php echo $smartPortalNav ? ' fetchpriority="high"' : ''; ?>>
                        <?php endif; ?>
                        <span class="site-header__brand-text" aria-hidden="false">
                            <span class="site-header__brand-title">BAGIAN ORGANISASI</span>
                            <span class="site-header__brand-sub">SEKRETARIAT DAERAH KABUPATEN KEPULAUAN ARU</span>
                        </span>
                    </a>
                </div>
                <?php if (empty($hideHeaderDocSearch)): ?>
                    <div class="org-navbar__search site-header__search-wrap">
                        <form method="get" id="headerDocSearchForm" class="site-header-doc-search site-header-doc-search--navbar" action="<?php echo org_href('dokumen.php'); ?>" role="search" aria-label="Pencarian dokumen">
                            <label class="org-sr-only visually-hidden" for="headerDocSearch">Cari dokumen di perpustakaan</label>
                            <div class="org-navbar__search-field site-header-doc-search__field">
                                <input type="search" id="headerDocSearch" name="q" class="org-navbar__search-input site-header-doc-search__input" placeholder="Cari dokumen…" autocomplete="off" value="<?php echo htmlspecialchars((string) ($searchQuery ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="org-navbar__search-submit site-header-doc-search__submit" aria-label="Cari dokumen">
                                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                <button
                    class="org-navbar__toggle site-header__nav-toggle d-lg-none"
                    type="button"
                    aria-controls="siteHeaderNavPanel"
                    aria-expanded="false"
                    aria-label="Buka menu navigasi"
                >
                    <span class="site-header__hamburger" aria-hidden="true">
                        <span class="site-header__hamburger-line"></span>
                        <span class="site-header__hamburger-line"></span>
                        <span class="site-header__hamburger-line"></span>
                    </span>
                    <span class="site-header__nav-toggle-label">Menu</span>
                </button>
            </div>
            <div class="navbar-wrapper org-navbar__nav-shell">
            <nav class="navbar-panel org-navbar__nav-wrap site-header__nav-wrap" aria-label="Navigasi utama">
                <?php if (($holidayUcapan ?? '') !== ''): ?>
                    <div class="site-header__holiday-ucapan site-header__holiday-ucapan--inline" role="complementary" aria-label="Ucapan hari besar">
                        <div class="site-header__holiday-ucapan-inline">
                            <?php if (($holidayBadge ?? '') !== ''): ?>
                                <span class="site-header__holiday-ucapan-badge"><?php echo htmlspecialchars($holidayBadge, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                            <div class="site-header__holiday-ucapan-copy">
                                <p class="site-header__holiday-ucapan-title mb-0"><?php echo htmlspecialchars($holidayUcapanMain ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php if (($holidayUcapanSub ?? '') !== ''): ?>
                                    <p class="site-header__holiday-ucapan-sub mb-0 d-none d-md-block"><?php echo htmlspecialchars($holidayUcapanSub, ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="site-header__nav-panel" id="siteHeaderNavPanel">
                    <div class="site-header__nav-close-wrap d-lg-none">
                        <button class="site-header__nav-close" type="button" aria-label="Tutup menu navigasi">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                            <span>Tutup</span>
                        </button>
                    </div>
                    <div class="org-navbar__nav-row site-header__nav-row">
                        <ul class="org-navbar__nav site-header__nav">
                            <li><a href="<?php echo htmlspecialchars(function_exists('org_home_url') ? org_home_url() : 'index.php', ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo ($navActive ?? '') === 'beranda' ? 'is-active' : ''; ?>">Beranda</a></li>
                            <li><a href="<?php echo org_href('profil.php'); ?>" class="<?php echo ($navActive ?? '') === 'profil' ? 'is-active' : ''; ?>">Profil</a></li>
                            <li><a href="<?php echo org_href('layanan.php'); ?>" class="<?php echo ($navActive ?? '') === 'layanan' ? 'is-active' : ''; ?>">Layanan</a></li>
                            <li><a href="<?php echo org_href('dokumen.php'); ?>" class="<?php echo ($navActive ?? '') === 'dokumen' ? 'is-active' : ''; ?>">Dokumen</a></li>
                            <?php if (empty($isAdmin) || !empty($canAccessPublikasi)): ?>
                                <li><a href="<?php echo org_href('berita.php'); ?>" class="<?php echo ($navActive ?? '') === 'berita' ? 'is-active' : ''; ?>" title="Pusat Informasi &amp; Pengumuman">Informasi</a></li>
                                <li><a href="<?php echo org_href('galeri.php'); ?>" class="<?php echo ($navActive ?? '') === 'galeri' ? 'is-active' : ''; ?>">Galeri</a></li>
                            <?php endif; ?>
                            <?php if (!empty($isAdmin) && !empty($canAccessEOrganisasi)): ?>
                                <li><a href="<?php echo org_href('e_organisasi.php'); ?>" class="<?php echo ($navActive ?? '') === 'e_organisasi' ? 'is-active' : ''; ?>">E-Organisasi</a></li>
                            <?php endif; ?>
                        </ul>
                        <div class="org-navbar__actions site-header__actions site-header__actions--end">
                            <div class="org-navbar__actions-end site-header__actions-end">
                                <button type="button" class="org-theme-toggle" id="orgThemeToggle" aria-pressed="false" aria-label="Aktifkan mode gelap" title="Mode gelap">
                                    <span class="org-theme-toggle__track" aria-hidden="true">
                                        <i class="fa-solid fa-sun org-theme-toggle__icon org-theme-toggle__icon--light"></i>
                                        <i class="fa-solid fa-moon org-theme-toggle__icon org-theme-toggle__icon--dark"></i>
                                        <span class="org-theme-toggle__thumb"></span>
                                    </span>
                                </button>
                                <?php if (!empty($isAdmin)): ?>
                                    <?php echo org_ui_button('Dashboard', ['variant' => 'header-dashboard', 'size' => 'sm', 'href' => org_page_url('admin/dashboard.php'), 'icon' => 'fa-solid fa-chart-line', 'class' => 'btn btn-sm btn-header-dashboard']); ?>
                                    <form method="post" class="site-header__logout-form mb-0">
                                        <input type="hidden" name="action" value="logout">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo org_ui_button('Logout', ['variant' => 'header-logout', 'size' => 'sm', 'type' => 'submit', 'icon' => 'fa-solid fa-sign-out-alt', 'class' => 'btn btn-sm btn-header-logout']); ?>
                                    </form>
                                <?php else: ?>
                                    <button type="button" class="org-btn org-btn--header org-btn--sm btn btn-sm btn-header-login" data-bs-toggle="modal" data-bs-target="#loginModal">
                                        <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                                        <span>Login</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
            </div>
        </div>
<?php else: ?>
        <div class="org-navbar__inner org-container site-header__inner container">
            <div class="org-navbar__top site-header__topbar">
                <div class="org-navbar__brand site-header__brand-row">
                    <a href="<?php echo htmlspecialchars(function_exists('org_home_url') ? org_home_url() : 'index.php', ENT_QUOTES, 'UTF-8'); ?>" class="site-header__brand-link no-underline">
                        <?php if (($logoWebPath ?? '') !== ''): ?>
                            <img src="<?php echo htmlspecialchars($logoWebPath, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($siteLogoAlt, ENT_QUOTES, 'UTF-8'); ?>" class="org-navbar__logo site-header__logo" width="112" height="56" loading="eager" decoding="async"<?php echo $smartPortalNav ? ' fetchpriority="high"' : ''; ?>>
                        <?php endif; ?>
                        <span class="site-header__brand-text">
                            <span class="site-header__brand-title">BAGIAN ORGANISASI</span>
                            <span class="site-header__brand-sub">SEKRETARIAT DAERAH KABUPATEN KEPULAUAN ARU</span>
                        </span>
                    </a>
                </div>
                <?php if (empty($hideHeaderDocSearch)): ?>
                    <div class="org-navbar__search site-header__search-wrap">
                        <form method="get" id="headerDocSearchForm" class="site-header-doc-search site-header-doc-search--navbar" action="<?php echo org_href('dokumen.php'); ?>" role="search" aria-label="Pencarian dokumen">
                            <label class="org-sr-only visually-hidden" for="headerDocSearch">Cari dokumen di perpustakaan</label>
                            <div class="org-navbar__search-field site-header-doc-search__field">
                                <input type="search" id="headerDocSearch" name="q" class="org-navbar__search-input site-header-doc-search__input" placeholder="Cari dokumen…" autocomplete="off" value="<?php echo htmlspecialchars((string) ($searchQuery ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="org-navbar__search-submit site-header-doc-search__submit" aria-label="Cari dokumen">
                                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                <button
                    class="org-navbar__toggle site-header__nav-toggle d-lg-none"
                    type="button"
                    aria-controls="siteHeaderNavPanel"
                    aria-expanded="false"
                    aria-label="Buka menu navigasi"
                >
                    <span class="site-header__hamburger" aria-hidden="true">
                        <span class="site-header__hamburger-line"></span>
                        <span class="site-header__hamburger-line"></span>
                        <span class="site-header__hamburger-line"></span>
                    </span>
                    <span class="site-header__nav-toggle-label">Menu</span>
                </button>
            </div>
            <div class="org-navbar__nav-wrap site-header__nav-wrap">
                <div class="site-header__nav-panel" id="siteHeaderNavPanel">
                    <div class="site-header__nav-close-wrap d-lg-none">
                        <button class="site-header__nav-close" type="button" aria-label="Tutup menu navigasi">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                            <span>Tutup</span>
                        </button>
                    </div>
                    <div class="org-navbar__nav-row site-header__nav-row">
                        <ul class="org-navbar__nav site-header__nav">
                            <li><a href="<?php echo htmlspecialchars(function_exists('org_home_url') ? org_home_url() : 'index.php', ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo ($navActive ?? '') === 'beranda' ? 'is-active' : ''; ?>">Beranda</a></li>
                            <li><a href="<?php echo org_href('profil.php'); ?>" class="<?php echo ($navActive ?? '') === 'profil' ? 'is-active' : ''; ?>">Profil</a></li>
                            <li><a href="<?php echo org_href('layanan.php'); ?>" class="<?php echo ($navActive ?? '') === 'layanan' ? 'is-active' : ''; ?>">Layanan</a></li>
                            <li><a href="<?php echo org_href('dokumen.php'); ?>" class="<?php echo ($navActive ?? '') === 'dokumen' ? 'is-active' : ''; ?>">Dokumen</a></li>
                            <?php if (empty($isAdmin) || !empty($canAccessPublikasi)): ?>
                                <li><a href="<?php echo org_href('berita.php'); ?>" class="<?php echo ($navActive ?? '') === 'berita' ? 'is-active' : ''; ?>" title="Pusat Informasi &amp; Pengumuman">Informasi</a></li>
                                <li><a href="<?php echo org_href('galeri.php'); ?>" class="<?php echo ($navActive ?? '') === 'galeri' ? 'is-active' : ''; ?>">Galeri</a></li>
                            <?php endif; ?>
                            <?php if (!empty($isAdmin) && !empty($canAccessEOrganisasi)): ?>
                                <li><a href="<?php echo org_href('e_organisasi.php'); ?>" class="<?php echo ($navActive ?? '') === 'e_organisasi' ? 'is-active' : ''; ?>">E-Organisasi</a></li>
                            <?php endif; ?>
                        </ul>
                        <div class="org-navbar__actions site-header__actions site-header__actions--end">
                            <div class="org-navbar__actions-end site-header__actions-end">
                                <button type="button" class="org-theme-toggle" id="orgThemeToggle" aria-pressed="false" aria-label="Aktifkan mode gelap" title="Mode gelap">
                                    <span class="org-theme-toggle__track" aria-hidden="true">
                                        <i class="fa-solid fa-sun org-theme-toggle__icon org-theme-toggle__icon--light"></i>
                                        <i class="fa-solid fa-moon org-theme-toggle__icon org-theme-toggle__icon--dark"></i>
                                        <span class="org-theme-toggle__thumb"></span>
                                    </span>
                                </button>
                                <?php if (!empty($isAdmin)): ?>
                                    <?php echo org_ui_button('Dashboard', ['variant' => 'header-dashboard', 'size' => 'sm', 'href' => org_page_url('admin/dashboard.php'), 'icon' => 'fa-solid fa-chart-line', 'class' => 'btn btn-sm btn-header-dashboard']); ?>
                                    <form method="post" class="site-header__logout-form mb-0">
                                        <input type="hidden" name="action" value="logout">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(org_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo org_ui_button('Logout', ['variant' => 'header-logout', 'size' => 'sm', 'type' => 'submit', 'icon' => 'fa-solid fa-sign-out-alt', 'class' => 'btn btn-sm btn-header-logout']); ?>
                                    </form>
                                <?php else: ?>
                                    <button type="button" class="org-btn org-btn--header org-btn--sm btn btn-sm btn-header-login" data-bs-toggle="modal" data-bs-target="#loginModal">
                                        <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                                        <span>Login</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php endif; ?>
    </div>
    <?php if (($holidayUcapan ?? '') !== '' && !$smartPortalNav): ?>
        <div class="site-header__holiday-ucapan site-header__holiday-ucapan--premium" role="complementary" aria-label="Ucapan hari besar">
            <span class="site-header__holiday-ucapan-shimmer" aria-hidden="true"></span>
            <span class="site-header__holiday-ucapan-border site-header__holiday-ucapan-border--top" aria-hidden="true"></span>
            <span class="site-header__holiday-ucapan-border site-header__holiday-ucapan-border--bottom" aria-hidden="true"></span>
            <div class="org-container container site-header__holiday-ucapan-inner">
                <span class="site-header__holiday-ucapan-ornament" aria-hidden="true"><i class="fa-solid fa-sparkles"></i></span>
                <div class="site-header__holiday-ucapan-card">
                    <?php if (($holidayBadge ?? '') !== ''): ?>
                        <span class="site-header__holiday-ucapan-badge"><?php echo htmlspecialchars($holidayBadge, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                    <div class="site-header__holiday-ucapan-body">
                        <?php if (($holidayDecoIcon ?? '') !== ''): ?>
                            <span class="site-header__holiday-ucapan-medallion" aria-hidden="true">
                                <i class="fa-solid <?php echo htmlspecialchars($holidayDecoIcon, ENT_QUOTES, 'UTF-8'); ?>"></i>
                            </span>
                        <?php endif; ?>
                        <div class="site-header__holiday-ucapan-copy">
                            <p class="site-header__holiday-ucapan-title mb-0"><?php echo htmlspecialchars($holidayUcapanMain ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php if (($holidayUcapanSub ?? '') !== ''): ?>
                                <p class="site-header__holiday-ucapan-sub mb-0"><?php echo htmlspecialchars($holidayUcapanSub, ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <span class="site-header__holiday-ucapan-ornament site-header__holiday-ucapan-ornament--end" aria-hidden="true"><i class="fa-solid fa-sparkles"></i></span>
            </div>
        </div>
    <?php endif; ?>
</header>
