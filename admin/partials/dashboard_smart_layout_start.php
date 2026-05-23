<?php
/** @var string $adminName @var string $adminRoleLabel @var string $csrfToken */
/** @var bool $isSubAdminEorgActor @var bool $isSubAdminPublikasiActor @var bool $isSubAdminActor @var bool $auditRiwayatVisible @var bool $canManagePerpustakaanDokumen */
$sgInitials = htmlspecialchars(mb_strtoupper(mb_substr(strip_tags($adminName), 0, 2, 'UTF-8')), ENT_QUOTES, 'UTF-8');
?>
<div class="sg-app" id="sgApp">
    <header class="sg-topbar" role="banner">
        <div class="sg-topbar__left">
            <button type="button" class="sg-icon-btn d-lg-none" id="sgSidebarToggle" aria-label="Buka menu">
                <i data-lucide="menu"></i>
            </button>
            <button type="button" class="sg-icon-btn d-none d-lg-inline-flex" id="sgSidebarCollapse" aria-label="Ciutkan sidebar">
                <i data-lucide="panel-left"></i>
            </button>
            <nav class="sg-breadcrumb d-none d-md-flex" aria-label="Breadcrumb">
                <span class="sg-breadcrumb__root">Smart Governance</span>
                <i data-lucide="chevron-right" aria-hidden="true"></i>
                <span class="sg-breadcrumb__current" id="sgBreadcrumbCurrent">Monitoring</span>
            </nav>
        </div>
        <div class="sg-topbar__search d-none d-md-block">
            <i data-lucide="search" aria-hidden="true"></i>
            <input type="search" class="sg-topbar__search-input" id="sgGlobalSearch" placeholder="Cari menu atau modul…" autocomplete="off">
        </div>
        <div class="sg-topbar__right">
            <span class="sg-status-pill"><span class="sg-status-pill__dot"></span><span class="d-none d-sm-inline">Online</span></span>
            <div class="sg-clock d-none d-xl-flex" id="sgRealtimeClock" aria-live="polite">
                <span class="sg-clock__time">--:--</span>
                <span class="sg-clock__date">—</span>
            </div>
            <button type="button" class="sg-icon-btn sg-icon-btn--notify" id="sgNotifyBtn" aria-label="Notifikasi">
                <i data-lucide="bell"></i><span class="sg-icon-btn__badge"></span>
            </button>
            <button type="button" class="sg-icon-btn" id="sgThemeToggle" aria-label="Ganti tema">
                <i data-lucide="moon" class="sg-theme-icon-dark"></i>
                <i data-lucide="sun" class="sg-theme-icon-light d-none"></i>
            </button>
            <a class="sg-btn-ghost d-none d-sm-inline-flex" href="<?php echo htmlspecialchars(org_home_url(), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener"><i data-lucide="external-link"></i> Situs</a>
            <div class="dropdown">
                <button class="sg-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <span class="sg-profile-btn__avatar"><?php echo $sgInitials; ?></span>
                    <span class="sg-profile-btn__meta d-none d-sm-flex">
                        <span class="sg-profile-btn__name"><?php echo $adminName; ?></span>
                        <span class="sg-profile-btn__role"><?php echo $adminRoleLabel; ?></span>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end sg-dropdown shadow border-0">
                    <li><span class="dropdown-item-text small text-muted"><?php echo $adminRoleLabel; ?></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" data-sg-module="monitoring"><i data-lucide="activity" class="me-2"></i>Monitoring</a></li>
                    <li>
                        <form method="post" action="<?php echo htmlspecialchars(org_home_url(), ENT_QUOTES, 'UTF-8'); ?>" class="px-3 py-1">
                            <input type="hidden" name="action" value="logout">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" class="btn btn-sm btn-primary w-100">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <div class="sg-backdrop" id="sgSidebarBackdrop" hidden></div>

    <div class="sg-body">
        <aside class="sg-sidebar sg-sidebar--navy" id="sgSidebar" aria-label="Navigasi modul">
            <div class="sg-sidebar__brand">
                <span class="sg-sidebar__logo"><i data-lucide="radar"></i></span>
                <div class="sg-sidebar__brand-text">
                    <strong>Bagian Organisasi</strong>
                    <span>Monitoring Center</span>
                </div>
            </div>
            <nav class="sg-sidebar__nav" id="sgSidebarNav">
                <p class="sg-sidebar__section">Monitoring</p>
                <a class="sg-nav-item is-active" href="#" data-sg-module="monitoring" data-sg-label="Dashboard">
                    <i data-lucide="layout-dashboard"></i><span>Dashboard</span>
                </a>
                <a class="sg-nav-item" href="#" data-sg-module="monitoring" data-sg-scroll="sgSectionAnalytics" data-sg-label="Analytics">
                    <i data-lucide="bar-chart-2"></i><span>Analytics</span>
                </a>
                <a class="sg-nav-item" href="#" data-sg-module="monitoring" data-sg-scroll="sgSectionMonitoring" data-sg-label="Monitoring">
                    <i data-lucide="activity"></i><span>Monitoring</span>
                </a>

                <?php if (!$isSubAdminEorgActor): ?>
                <p class="sg-sidebar__section">Operasional</p>
                <a class="sg-nav-item" href="#" data-sg-module="layanan" data-sg-label="Layanan Publik">
                    <i data-lucide="globe"></i><span>Layanan Publik</span>
                </a>
                <?php endif; ?>
                <?php if ($canManagePerpustakaanDokumen): ?>
                <a class="sg-nav-item" href="#" data-sg-module="dokumen" data-sg-label="Dokumen">
                    <i data-lucide="folder-open"></i><span>Dokumen</span>
                </a>
                <?php endif; ?>
                <?php if (!$isSubAdminActor): ?>
                <a class="sg-nav-item" href="#" data-sg-module="pegawai" data-sg-label="Pegawai">
                    <i data-lucide="users"></i><span>Pegawai</span>
                </a>
                <a class="sg-nav-item" href="<?php echo org_href('admin/kelola_team_targets.php'); ?>" data-sg-label="Tim Kerja">
                    <i data-lucide="target"></i><span>Tim Kerja</span>
                </a>
                <?php endif; ?>
                <?php if ($auditRiwayatVisible): ?>
                <a class="sg-nav-item" href="#" data-sg-module="audit" data-sg-label="Audit">
                    <i data-lucide="shield-check"></i><span>Audit</span>
                </a>
                <?php endif; ?>
                <?php if (!$isSubAdminActor): ?>
                <a class="sg-nav-item" href="#" data-sg-module="pengaturan" data-sg-label="Pengaturan">
                    <i data-lucide="settings"></i><span>Pengaturan</span>
                </a>
                <?php endif; ?>
            </nav>
        </aside>
        <main class="sg-main" id="sgMain">
