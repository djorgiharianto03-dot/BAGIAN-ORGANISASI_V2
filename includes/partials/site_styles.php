    <style>
        :root {
            --surface: #ffffff;
            --page-bg: #f9fafb;
            --card-border: #e5e7eb;
            --brand-navy: #0c4a6e;
            --brand-navy-deep: #003366;
            --text-main: #374151;
            --text-muted: #64748b;
            --accent: #0369a1;
            --header-gradient: linear-gradient(180deg, #041e3f 0%, #0a2f63 38%, #0d3d7a 72%, rgba(0, 51, 102, 0.92) 100%);
            --header-nav-surface: linear-gradient(135deg, #1e3a5f 0%, #27496d 100%);
            --header-nav-overlay: var(--header-nav-surface);
            --header-nav-glass-blur: 6px;
            --header-nav-glass-blur-scroll: 18px;
            --header-nav-border: rgba(255, 255, 255, 0.14);
            --header-nav-shadow: 0 4px 20px rgba(2, 20, 47, 0.14);
            --header-nav-shadow-elevated: 0 10px 32px rgba(2, 16, 40, 0.24), 0 2px 0 rgba(255, 255, 255, 0.07) inset;
            --header-nav-active: #f5d78e;
            --header-nav-active-glow: rgba(245, 215, 142, 0.55);
            --header-title-size: clamp(1.12rem, 1.9vw + 0.52rem, 1.72rem);
            --header-subtitle-size: clamp(0.74rem, 0.55vw + 0.58rem, 0.9rem);
            --header-font-display: 'Public Sans', 'Inter', system-ui, sans-serif;
            --layout-max-width: 1200px;
            --pi-card-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.06);
            --font-sans: 'Inter', system-ui, -apple-system, 'Segoe UI', Arial, sans-serif;
            --org-bp-tablet: 768px;
            --org-bp-desktop: 1024px;
            --org-touch: 44px;
            --org-space-x: clamp(1rem, 4.2vw, 1.25rem);
        }
        body.mode-publikasi {
            --brand-navy: #1d4ed8;
            --brand-navy-deep: #1e40af;
            --accent: #2563eb;
            /* Samakan warna header dengan beranda agar konsisten. */
            --header-gradient: linear-gradient(180deg, #0a2f63 0%, #103f82 100%);
            --header-nav-surface: linear-gradient(135deg, #1e3a5f 0%, #27496d 100%);
            --header-nav-overlay: var(--header-nav-surface);
            --page-bg: #f5f8ff;
        }
        body.mode-eorganisasi {
            --brand-navy: #0c4a6e;
            --brand-navy-deep: #003366;
            --accent: #0369a1;
            /* Header identik beranda (:root) — hanya konten halaman yang memakai aksen modul. */
            --header-gradient: linear-gradient(180deg, #041e3f 0%, #0a2f63 38%, #0d3d7a 72%, rgba(0, 51, 102, 0.92) 100%);
            --header-nav-surface: linear-gradient(135deg, #1e3a5f 0%, #27496d 100%);
            --header-nav-overlay: var(--header-nav-surface);
            --header-nav-glass-blur: 6px;
            --header-nav-glass-blur-scroll: 18px;
            --header-nav-border: rgba(255, 255, 255, 0.14);
            --header-nav-active: #f5d78e;
            --header-nav-active-glow: rgba(245, 215, 142, 0.55);
            --page-bg: #f0fdfa;
        }
        body.mode-publikasi .section-card,
        body.mode-publikasi .card.section-card {
            border: 1px solid #dbe7ff;
            box-shadow: 0 10px 26px rgba(30, 64, 175, 0.08);
        }
        body.mode-eorganisasi .section-card,
        body.mode-eorganisasi .card.section-card {
            border: 1px solid #cceee9;
            box-shadow: 0 10px 26px rgba(15, 118, 110, 0.08);
        }
        body.mode-publikasi .btn-primary {
            background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
            border-color: #1d4ed8;
        }
        body.mode-publikasi .btn-primary:hover {
            background: #1e40af;
            border-color: #1e40af;
        }
        body.mode-eorganisasi .btn-primary {
            background: linear-gradient(180deg, #0f766e 0%, #115e59 100%);
            border-color: #115e59;
        }
        body.mode-eorganisasi .btn-primary:hover {
            background: #134e4a;
            border-color: #134e4a;
        }
        body.mode-publikasi .badge.bg-primary {
            background: #1d4ed8 !important;
        }
        body.mode-eorganisasi .badge.bg-primary {
            background: #0f766e !important;
        }
        body.mode-publikasi .site-header__nav a:hover,
        body.mode-eorganisasi .site-header__nav a:hover {
            color: #ffffff;
        }
        html,
        body {
            overflow-y: scroll;
            /* auto: stable mempersempit body vs header position:fixed → celah putih kanan beranda */
            scrollbar-gutter: auto;
        }
        * {
            box-sizing: border-box;
        }
        .container {
            max-width: var(--layout-max-width);
            margin-left: auto;
            margin-right: auto;
            padding-left: 20px;
            padding-right: 20px;
        }
        .site-header .container.site-header__inner,
        .container.site-main {
            max-width: var(--layout-max-width);
        }
        body {
            background: var(--page-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            scroll-behavior: smooth;
            font-family: var(--font-sans);
            font-size: 16px;
            line-height: 1.6;
            color: var(--text-main);
        }
        .site-layout-main {
            flex: 1;
        }
        p,
        li,
        .form-text {
            line-height: 1.6;
        }
        .small,
        small {
            font-size: 0.875rem;
            line-height: 1.5;
        }
        .lead {
            font-size: 1.0625rem;
            line-height: 1.65;
            color: #4b5563;
        }
        :is(input, select, textarea, button, .btn, .navbar, .site-header, .site-footer, .form-control, .form-select) {
            font-family: var(--font-sans);
        }
        .btn {
            font-size: 0.9375rem;
            font-weight: 600;
            line-height: 1.45;
            letter-spacing: 0.01em;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-sm {
            font-size: 0.875rem;
            line-height: 1.4;
        }
        .btn-lg {
            font-size: 1rem;
            line-height: 1.4;
        }
        .site-header {
            position: relative;
            z-index: 1030;
        }
        .site-header__gradient {
            background: var(--header-gradient);
            box-shadow: 0 6px 28px rgba(2, 16, 40, 0.18);
        }
        /* Tema Hari Besar (scoped pada body class agar aman) */
        body[class*="theme-"] .site-header__gradient {
            background: var(--holiday-header-gradient, var(--header-gradient));
            box-shadow: var(--holiday-header-shadow, 0 8px 32px rgba(2, 16, 40, 0.22));
            transition: background 0.35s ease, box-shadow 0.35s ease;
        }
        body.theme-idul-fitri .site-header__gradient,
        body.theme-idul-adha .site-header__gradient,
        body.theme-pancasila .site-header__gradient,
        body.theme-korpri .site-header__gradient {
            --holiday-header-shadow: 0 10px 40px rgba(5, 46, 22, 0.45), 0 0 0 1px rgba(251, 191, 36, 0.12) inset;
        }
        body.theme-kemerdekaan .site-header__gradient,
        body.theme-pahlawan .site-header__gradient,
        body.theme-kesaktian-pancasila .site-header__gradient,
        body.theme-natal .site-header__gradient {
            --holiday-header-shadow: 0 10px 40px rgba(127, 29, 29, 0.4), 0 0 0 1px rgba(254, 202, 202, 0.15) inset;
        }
        body.theme-kartini .site-header__gradient,
        body.theme-ibu .site-header__gradient {
            --holiday-header-shadow: 0 10px 40px rgba(157, 23, 77, 0.38), 0 0 0 1px rgba(251, 207, 232, 0.14) inset;
        }
        body.theme-tahun-baru .site-header__gradient {
            --holiday-header-shadow: 0 10px 40px rgba(49, 46, 129, 0.45), 0 0 0 1px rgba(251, 191, 36, 0.14) inset;
        }
        body.theme-hardiknas .site-header__gradient,
        body.theme-guru .site-header__gradient,
        body.theme-pers-nasional .site-header__gradient,
        body.theme-kebangkitan-nasional .site-header__gradient,
        body.theme-sumpah-pemuda .site-header__gradient {
            --holiday-header-shadow: 0 10px 40px rgba(30, 58, 138, 0.42), 0 0 0 1px rgba(191, 219, 254, 0.12) inset;
        }
        body[class*="theme-"] .site-header__nav-wrap {
            background: var(--holiday-nav-surface, var(--header-nav-surface));
        }
        body[class*="theme-"] .site-header__nav a,
        body[class*="theme-"] .site-header__title,
        body[class*="theme-"] .site-header__subtitle {
            color: var(--holiday-header-text, #ffffff);
            transition: color 0.35s ease;
        }
        body[class*="theme-"] .btn-primary,
        body[class*="theme-"] .btn-site-footer-submit,
        body[class*="theme-"] .library-doc-search-header__submit {
            background: var(--holiday-btn-bg, linear-gradient(180deg, #0a57b7 0%, #084ea7 100%));
            border-color: var(--holiday-btn-border, #084ea7);
            color: var(--holiday-btn-text, #ffffff) !important;
            transition: background 0.28s ease, border-color 0.28s ease, color 0.28s ease;
        }
        body[class*="theme-"] .btn-primary:hover,
        body[class*="theme-"] .btn-site-footer-submit:hover,
        body[class*="theme-"] .library-doc-search-header__submit:hover {
            background: var(--holiday-btn-bg-hover, #084ea7);
            border-color: var(--holiday-btn-border-hover, #084ea7);
            color: var(--holiday-btn-text-hover, #ffffff) !important;
        }
        body.theme-natal {
            --holiday-header-gradient: linear-gradient(165deg, #450a0a 0%, #991b1b 45%, #b91c1c 55%, #450a0a 100%);
            --holiday-nav-surface: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%);
            --holiday-header-text: #ffffff;
            --holiday-deco-bg: linear-gradient(145deg, #fecaca 0%, #ef4444 100%);
            --holiday-deco-fg: #7f1d1d;
            --holiday-ucapan-bg: linear-gradient(105deg, #450a0a 0%, #991b1b 50%, #450a0a 100%);
            --holiday-ucapan-gold: linear-gradient(90deg, #fff7ed 0%, #fecaca 45%, #ffffff 50%, #fecaca 55%, #fff7ed 100%);
            --holiday-ucapan-sub: rgba(255, 247, 237, 0.9);
        }
        body.theme-idul-adha {
            --holiday-header-gradient: linear-gradient(165deg, #022c15 0%, #14532d 50%, #022c15 100%);
            --holiday-nav-surface: linear-gradient(135deg, #0f3d24 0%, #166534 100%);
            --holiday-btn-bg: linear-gradient(180deg, #ca8a04 0%, #a16207 100%);
            --holiday-btn-border: #92400e;
            --holiday-btn-text: #fffbeb;
            --holiday-btn-bg-hover: linear-gradient(180deg, #eab308 0%, #ca8a04 100%);
            --holiday-btn-border-hover: #b45309;
            --holiday-btn-text-hover: #ffffff;
            --holiday-deco-bg: linear-gradient(145deg, #fde68a 0%, #f59e0b 100%);
            --holiday-deco-fg: #052e16;
            --holiday-ucapan-bg: linear-gradient(105deg, #022c15 0%, #14532d 50%, #022c15 100%);
            --holiday-ucapan-gold: linear-gradient(90deg, #fef3c7 0%, #fbbf24 45%, #fffbeb 50%, #fbbf24 55%, #fef3c7 100%);
            --holiday-ucapan-sub: rgba(254, 243, 199, 0.88);
        }
        body.theme-idul-fitri {
            --holiday-header-gradient: linear-gradient(165deg, #022c15 0%, #14532d 22%, #15803d 48%, #14532d 78%, #022c15 100%);
            --holiday-nav-surface: linear-gradient(135deg, #0f3d24 0%, #166534 42%, #0f3d24 100%);
            --holiday-btn-bg: linear-gradient(180deg, #ca8a04 0%, #a16207 100%);
            --holiday-btn-border: #92400e;
            --holiday-btn-text: #fffbeb;
            --holiday-btn-bg-hover: linear-gradient(180deg, #eab308 0%, #ca8a04 100%);
            --holiday-btn-border-hover: #b45309;
            --holiday-btn-text-hover: #ffffff;
            --holiday-deco-bg: linear-gradient(145deg, #fde68a 0%, #f59e0b 38%, #d97706 100%);
            --holiday-deco-fg: #052e16;
            --holiday-ucapan-bg: linear-gradient(105deg, #022c15 0%, #14532d 18%, #166534 50%, #14532d 82%, #022c15 100%);
            --holiday-ucapan-gold: linear-gradient(90deg, #fef3c7 0%, #fbbf24 35%, #fffbeb 50%, #fbbf24 65%, #fef3c7 100%);
            --holiday-ucapan-text: #fffbeb;
            --holiday-ucapan-sub: rgba(254, 243, 199, 0.88);
        }
        body.theme-pancasila,
        body.theme-korpri {
            --holiday-header-gradient: linear-gradient(165deg, #022c15 0%, #14532d 50%, #022c15 100%);
            --holiday-nav-surface: linear-gradient(135deg, #0f3d24 0%, #166534 100%);
            --holiday-btn-bg: linear-gradient(180deg, #ca8a04 0%, #a16207 100%);
            --holiday-btn-border: #92400e;
            --holiday-btn-text: #fffbeb;
            --holiday-btn-bg-hover: linear-gradient(180deg, #eab308 0%, #ca8a04 100%);
            --holiday-btn-border-hover: #b45309;
            --holiday-btn-text-hover: #ffffff;
            --holiday-deco-bg: linear-gradient(145deg, #fde68a 0%, #f59e0b 100%);
            --holiday-deco-fg: #052e16;
            --holiday-ucapan-bg: linear-gradient(105deg, #022c15 0%, #166534 50%, #022c15 100%);
            --holiday-ucapan-gold: linear-gradient(90deg, #fef9c3 0%, #fde047 45%, #fffbeb 50%, #fde047 55%, #fef9c3 100%);
            --holiday-ucapan-sub: rgba(254, 243, 199, 0.88);
        }
        body.theme-kemerdekaan,
        body.theme-pahlawan,
        body.theme-kesaktian-pancasila {
            --holiday-header-gradient: linear-gradient(165deg, #450a0a 0%, #991b1b 48%, #b91c1c 52%, #450a0a 100%);
            --holiday-nav-surface: linear-gradient(135deg, #991b1b 0%, #b91c1c 100%);
            --holiday-header-text: #ffffff;
            --holiday-deco-bg: linear-gradient(145deg, #fecaca 0%, #f87171 100%);
            --holiday-deco-fg: #7f1d1d;
            --holiday-ucapan-bg: linear-gradient(105deg, #450a0a 0%, #991b1b 50%, #450a0a 100%);
            --holiday-ucapan-gold: linear-gradient(90deg, #fff7ed 0%, #fecaca 45%, #ffffff 50%, #fecaca 55%, #fff7ed 100%);
            --holiday-ucapan-sub: rgba(255, 247, 237, 0.9);
        }
        body.theme-kartini,
        body.theme-ibu {
            --holiday-header-gradient: linear-gradient(165deg, #500724 0%, #9d174d 48%, #be185d 52%, #500724 100%);
            --holiday-nav-surface: linear-gradient(135deg, #9d174d 0%, #be185d 100%);
            --holiday-header-text: #ffffff;
            --holiday-deco-bg: linear-gradient(145deg, #fbcfe8 0%, #ec4899 100%);
            --holiday-deco-fg: #831843;
            --holiday-ucapan-bg: linear-gradient(105deg, #500724 0%, #be185d 50%, #500724 100%);
            --holiday-ucapan-gold: linear-gradient(90deg, #fdf2f8 0%, #f9a8d4 45%, #ffffff 50%, #f9a8d4 55%, #fdf2f8 100%);
            --holiday-ucapan-sub: rgba(252, 231, 243, 0.92);
        }
        body.theme-tahun-baru {
            --holiday-header-gradient: linear-gradient(165deg, #1e1b4b 0%, #312e81 38%, #854d0e 72%, #1e1b4b 100%);
            --holiday-nav-surface: linear-gradient(135deg, #312e81 0%, #4338ca 100%);
            --holiday-header-text: #ffffff;
            --holiday-btn-bg: linear-gradient(180deg, #eab308 0%, #ca8a04 100%);
            --holiday-btn-border: #a16207;
            --holiday-btn-text: #fffbeb;
            --holiday-deco-bg: linear-gradient(145deg, #fde68a 0%, #fbbf24 100%);
            --holiday-deco-fg: #312e81;
            --holiday-ucapan-bg: linear-gradient(105deg, #1e1b4b 0%, #4338ca 50%, #1e1b4b 100%);
            --holiday-ucapan-gold: linear-gradient(90deg, #fef3c7 0%, #fbbf24 40%, #ffffff 50%, #fbbf24 60%, #fef3c7 100%);
            --holiday-ucapan-sub: rgba(254, 243, 199, 0.9);
        }
        body.theme-hardiknas,
        body.theme-guru,
        body.theme-pers-nasional,
        body.theme-kebangkitan-nasional,
        body.theme-sumpah-pemuda {
            --holiday-header-gradient: linear-gradient(165deg, #0c1e42 0%, #1e3a8a 48%, #1d4ed8 52%, #0c1e42 100%);
            --holiday-nav-surface: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
            --holiday-header-text: #ffffff;
            --holiday-btn-bg: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
            --holiday-btn-border: #1e40af;
            --holiday-btn-text: #eff6ff;
            --holiday-deco-bg: linear-gradient(145deg, #bfdbfe 0%, #60a5fa 100%);
            --holiday-deco-fg: #1e3a8a;
            --holiday-ucapan-bg: linear-gradient(105deg, #0c1e42 0%, #1e40af 50%, #0c1e42 100%);
            --holiday-ucapan-gold: linear-gradient(90deg, #dbeafe 0%, #93c5fd 42%, #ffffff 50%, #93c5fd 58%, #dbeafe 100%);
            --holiday-ucapan-sub: rgba(219, 234, 254, 0.92);
        }
        .site-header__inner {
            padding: 1.15rem 0 1.35rem;
        }
        .site-header__topbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-start;
            gap: 0.85rem 1.25rem;
        }
        .site-header__search-wrap {
            flex: 0 1 auto;
            min-width: 0;
            max-width: min(22rem, 100%);
            margin-left: auto;
            align-self: center;
        }
        @media (max-width: 575.98px) {
            .site-header__search-wrap {
                flex: 1 1 100%;
                width: 100%;
                max-width: none;
                margin-left: 0;
            }
        }
        .site-header-doc-search--navbar {
            margin-left: auto;
        }
        @media (max-width: 575.98px) {
            .site-header-doc-search--navbar {
                margin-left: 0;
                max-width: none;
            }
        }
        .site-header__brand-row {
            display: flex;
            align-items: flex-start;
            gap: 1rem 1.35rem;
            flex-wrap: wrap;
            flex: 1 1 auto;
            min-width: min(100%, 12rem);
        }
        .site-header__brand-row > a:first-child {
            flex-shrink: 0;
            align-self: flex-start;
        }
        .site-header__logo {
            max-height: 76px;
            width: auto;
            max-width: min(150px, 32vw);
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 10px rgba(0, 0, 0, 0.22));
        }
        .site-header__holiday-deco {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.95rem;
            height: 1.95rem;
            border-radius: 999px;
            margin-left: -0.35rem;
            margin-top: 0.15rem;
            font-size: 0.82rem;
            flex-shrink: 0;
            border: 1px solid rgba(255, 255, 255, 0.35);
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.24);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            position: relative;
            z-index: 1;
        }
        .site-header__holiday-deco--premium {
            width: 2.45rem;
            height: 2.45rem;
            font-size: 1rem;
            margin-left: -0.15rem;
            border: 1px solid rgba(255, 255, 255, 0.45);
            box-shadow:
                0 0 0 2px rgba(251, 191, 36, 0.28),
                0 10px 22px rgba(5, 46, 22, 0.35);
        }
        .site-header__holiday-deco-ring {
            position: absolute;
            inset: -4px;
            border-radius: inherit;
            border: 1px solid rgba(251, 191, 36, 0.45);
            opacity: 0.85;
            animation: holiday-deco-ring-pulse 2.8s ease-in-out infinite;
        }
        @keyframes holiday-deco-ring-pulse {
            0%, 100% { transform: scale(1); opacity: 0.55; }
            50% { transform: scale(1.08); opacity: 0.95; }
        }
        body[class*="theme-"] .site-header__holiday-deco {
            background: var(--holiday-deco-bg, linear-gradient(135deg, #dbeafe 0%, #93c5fd 100%));
            color: var(--holiday-deco-fg, #1e3a8a);
        }
        body.theme-natal .site-header__holiday-deco,
        body.theme-kemerdekaan .site-header__holiday-deco,
        body.theme-pahlawan .site-header__holiday-deco,
        body.theme-kesaktian-pancasila .site-header__holiday-deco {
            --holiday-deco-bg: linear-gradient(135deg, #fca5a5 0%, #ef4444 100%);
            --holiday-deco-fg: #fff7ed;
        }
        body.theme-idul-adha .site-header__holiday-deco,
        body.theme-idul-fitri .site-header__holiday-deco,
        body.theme-korpri .site-header__holiday-deco,
        body.theme-pancasila .site-header__holiday-deco,
        body.theme-tahun-baru .site-header__holiday-deco {
            --holiday-deco-bg: linear-gradient(135deg, #f5d27a 0%, #eab308 100%);
            --holiday-deco-fg: #14532d;
        }
        body.theme-hardiknas .site-header__holiday-deco,
        body.theme-guru .site-header__holiday-deco,
        body.theme-pers-nasional .site-header__holiday-deco,
        body.theme-kebangkitan-nasional .site-header__holiday-deco,
        body.theme-sumpah-pemuda .site-header__holiday-deco {
            --holiday-deco-bg: linear-gradient(135deg, #bfdbfe 0%, #60a5fa 100%);
            --holiday-deco-fg: #1e3a8a;
        }
        body.theme-kartini .site-header__holiday-deco,
        body.theme-ibu .site-header__holiday-deco {
            --holiday-deco-bg: linear-gradient(135deg, #f9a8d4 0%, #ec4899 100%);
            --holiday-deco-fg: #831843;
        }
        .site-header__holiday-ucapan {
            position: relative;
            overflow: hidden;
            background: var(--holiday-ucapan-bg, linear-gradient(90deg, #0a2f63 0%, #103f82 50%, #0a2f63 100%));
            color: var(--holiday-ucapan-text, #ffffff);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.1);
        }
        .site-header__holiday-ucapan--premium::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 120% at 10% 50%, rgba(251, 191, 36, 0.14) 0%, transparent 55%),
                radial-gradient(ellipse 70% 100% at 90% 50%, rgba(251, 191, 36, 0.12) 0%, transparent 50%);
            pointer-events: none;
        }
        .site-header__holiday-ucapan-shimmer {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                105deg,
                transparent 0%,
                transparent 40%,
                rgba(255, 255, 255, 0.07) 48%,
                rgba(251, 191, 36, 0.18) 50%,
                rgba(255, 255, 255, 0.07) 52%,
                transparent 60%,
                transparent 100%
            );
            transform: translateX(-120%);
            animation: holiday-ucapan-shimmer 5.5s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes holiday-ucapan-shimmer {
            0%, 72% { transform: translateX(-120%); }
            100% { transform: translateX(120%); }
        }
        .site-header__holiday-ucapan-border {
            position: absolute;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--holiday-ucapan-gold, linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.35), transparent));
            opacity: 0.9;
            pointer-events: none;
        }
        .site-header__holiday-ucapan-border--top { top: 0; }
        .site-header__holiday-ucapan-border--bottom { bottom: 0; }
        .site-header__holiday-ucapan-inner {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem 1rem;
            padding: 0.55rem 0.75rem 0.65rem;
            text-align: center;
        }
        .site-header__holiday-ucapan-ornament {
            color: var(--holiday-ucapan-ornament, rgba(255, 255, 255, 0.55));
            font-size: 0.72rem;
            flex-shrink: 0;
            opacity: 0.85;
        }
        .site-header__holiday-ucapan-ornament--end {
            transform: scaleX(-1);
        }
        .site-header__holiday-ucapan-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.45rem;
            max-width: min(52rem, 100%);
        }
        .site-header__holiday-ucapan-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.85rem;
            border-radius: 999px;
            font-family: var(--header-font-display);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--holiday-badge-text, #052e16);
            background: var(--holiday-badge-bg, linear-gradient(180deg, #fde68a 0%, #fbbf24 55%, #f59e0b 100%));
            border: 1px solid var(--holiday-badge-border, rgba(255, 255, 255, 0.55));
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
        }
        body.theme-idul-fitri,
        body.theme-idul-adha,
        body.theme-pancasila,
        body.theme-korpri,
        body.theme-tahun-baru {
            --holiday-badge-bg: linear-gradient(180deg, #fde68a 0%, #fbbf24 55%, #f59e0b 100%);
            --holiday-badge-text: #052e16;
            --holiday-badge-border: rgba(255, 255, 255, 0.55);
        }
        body.theme-hardiknas,
        body.theme-guru,
        body.theme-pers-nasional,
        body.theme-kebangkitan-nasional,
        body.theme-sumpah-pemuda {
            --holiday-badge-bg: linear-gradient(180deg, #dbeafe 0%, #93c5fd 55%, #60a5fa 100%);
            --holiday-badge-text: #1e3a8a;
            --holiday-badge-border: rgba(255, 255, 255, 0.5);
        }
        body.theme-kemerdekaan,
        body.theme-pahlawan,
        body.theme-kesaktian-pancasila,
        body.theme-natal {
            --holiday-badge-bg: linear-gradient(180deg, #fecaca 0%, #f87171 55%, #ef4444 100%);
            --holiday-badge-text: #7f1d1d;
            --holiday-badge-border: rgba(255, 255, 255, 0.45);
        }
        body.theme-kartini,
        body.theme-ibu {
            --holiday-badge-bg: linear-gradient(180deg, #fbcfe8 0%, #f472b6 55%, #ec4899 100%);
            --holiday-badge-text: #831843;
            --holiday-badge-border: rgba(255, 255, 255, 0.5);
        }
        .site-header__holiday-ucapan-body {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.85rem 1rem;
            flex-wrap: wrap;
        }
        .site-header__holiday-ucapan-medallion {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.65rem;
            height: 2.65rem;
            border-radius: 999px;
            flex-shrink: 0;
            font-size: 1.15rem;
            color: var(--holiday-deco-fg, #14532d);
            background: var(--holiday-deco-bg, linear-gradient(145deg, #fde68a 0%, #f59e0b 100%));
            border: 2px solid rgba(255, 255, 255, 0.65);
            box-shadow:
                0 0 0 3px rgba(251, 191, 36, 0.22),
                0 8px 20px rgba(0, 0, 0, 0.2);
        }
        .site-header__holiday-ucapan-copy {
            min-width: 0;
        }
        .site-header__holiday-ucapan-title {
            font-family: var(--header-font-display);
            font-size: clamp(0.92rem, 1.8vw + 0.25rem, 1.22rem);
            font-weight: 700;
            letter-spacing: 0.03em;
            line-height: 1.35;
            background: var(--holiday-ucapan-gold, linear-gradient(90deg, #ffffff 0%, #fef3c7 50%, #ffffff 100%));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: none;
        }
        .site-header__holiday-ucapan-sub {
            margin-top: 0.28rem;
            font-family: var(--header-font-display);
            font-size: clamp(0.74rem, 1.1vw + 0.2rem, 0.9rem);
            font-weight: 500;
            font-style: italic;
            letter-spacing: 0.04em;
            color: var(--holiday-ucapan-sub, rgba(255, 255, 255, 0.88));
        }
        body[class*="theme-"] .site-header__holiday-ucapan {
            --holiday-ucapan-ornament: rgba(255, 255, 255, 0.55);
        }
        body.theme-idul-fitri .site-header__holiday-ucapan,
        body.theme-idul-adha .site-header__holiday-ucapan,
        body.theme-pancasila .site-header__holiday-ucapan,
        body.theme-korpri .site-header__holiday-ucapan {
            --holiday-ucapan-ornament: rgba(254, 243, 199, 0.7);
        }
        body.theme-tahun-baru .site-header__holiday-ucapan {
            --holiday-ucapan-ornament: rgba(254, 243, 199, 0.75);
        }
        body.theme-hardiknas .site-header__holiday-ucapan,
        body.theme-guru .site-header__holiday-ucapan,
        body.theme-pers-nasional .site-header__holiday-ucapan,
        body.theme-kebangkitan-nasional .site-header__holiday-ucapan,
        body.theme-sumpah-pemuda .site-header__holiday-ucapan {
            --holiday-ucapan-ornament: rgba(191, 219, 254, 0.8);
        }
        body.theme-natal .site-header__holiday-ucapan,
        body.theme-kemerdekaan .site-header__holiday-ucapan,
        body.theme-pahlawan .site-header__holiday-ucapan,
        body.theme-kesaktian-pancasila .site-header__holiday-ucapan {
            --holiday-ucapan-ornament: rgba(254, 202, 202, 0.75);
        }
        body.theme-kartini .site-header__holiday-ucapan,
        body.theme-ibu .site-header__holiday-ucapan {
            --holiday-ucapan-ornament: rgba(249, 168, 212, 0.8);
        }
        @media (prefers-reduced-motion: reduce) {
            .site-header__holiday-ucapan-shimmer,
            .site-header__holiday-deco-ring {
                animation: none !important;
            }
        }
        @media (max-width: 576px) {
            .site-header__logo {
                max-height: 58px;
                max-width: 36vw;
            }
            .site-header__holiday-deco {
                width: 1.7rem;
                height: 1.7rem;
                font-size: 0.74rem;
                margin-left: -0.2rem;
            }
            .site-header__holiday-deco--premium {
                width: 2rem;
                height: 2rem;
                font-size: 0.85rem;
            }
            .site-header__holiday-ucapan-inner {
                flex-direction: column;
                gap: 0.5rem;
                padding: 0.75rem 0.5rem 0.85rem;
            }
            .site-header__holiday-ucapan-ornament {
                display: none;
            }
            .site-header__holiday-ucapan-medallion {
                width: 2.2rem;
                height: 2.2rem;
                font-size: 0.95rem;
            }
        }
        .site-header__titles {
            flex: 1;
            min-width: min(100%, 220px);
        }
        .site-header__title {
            font-family: var(--header-font-display);
            font-weight: 800;
            font-size: var(--header-title-size);
            letter-spacing: 0.1em;
            color: #fff;
            margin: 0;
            line-height: 1.12;
            text-shadow: 0 2px 18px rgba(0, 0, 0, 0.32);
            text-transform: uppercase;
        }
        .site-header__title-mobile {
            display: none;
        }
        .site-header__subtitle {
            margin: 0.4rem 0 0;
            font-size: var(--header-subtitle-size);
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            line-height: 1.45;
            letter-spacing: 0.02em;
        }
        @keyframes site-header-nav-reveal {
            from {
                opacity: 0;
                translate: 0 -14px;
                filter: blur(8px);
            }
            to {
                opacity: 1;
                translate: 0 0;
                filter: blur(0);
            }
        }
        @keyframes site-header-nav-reveal-fallback {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        .site-header__holiday-ucapan--inline {
            flex-shrink: 0;
        }
        .site-header__nav-wrap {
            margin-top: 1rem;
            margin-left: calc(-1 * min(20px, 2vw));
            margin-right: calc(-1 * min(20px, 2vw));
            padding: 0.85rem min(20px, 2vw) 0.95rem;
            border-top: none;
            border-bottom: 1px solid var(--header-nav-border);
            background: var(--header-nav-surface);
            background-color: #243f5f;
            border-radius: 0;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 0.85rem 1rem;
            position: sticky;
            top: 0;
            z-index: 1040;
            isolation: isolate;
            backdrop-filter: blur(var(--header-nav-glass-blur)) saturate(1.12);
            -webkit-backdrop-filter: blur(var(--header-nav-glass-blur)) saturate(1.12);
            box-shadow: var(--header-nav-shadow);
            transition:
                box-shadow 0.3s cubic-bezier(0.22, 1, 0.36, 1),
                border-color 0.3s ease,
                border-radius 0.3s ease,
                backdrop-filter 0.35s ease,
                -webkit-backdrop-filter 0.35s ease,
                background 0.35s ease;
            opacity: 0;
            animation: site-header-nav-reveal 1.05s cubic-bezier(0.22, 1, 0.36, 1) 0.22s both;
        }
        /* Saat scroll: glass blur halus, warna navy tetap */
        .site-header__nav-wrap.is-nav-scrolled,
        .site-header__nav-wrap.is-nav-elevated,
        .site-header__nav-wrap.is-features-fixed {
            background: linear-gradient(135deg, rgba(30, 58, 95, 0.9) 0%, rgba(39, 73, 109, 0.9) 100%);
            background-color: rgba(36, 63, 95, 0.92);
            backdrop-filter: blur(var(--header-nav-glass-blur-scroll)) saturate(1.14);
            -webkit-backdrop-filter: blur(var(--header-nav-glass-blur-scroll)) saturate(1.14);
        }
        @supports not (translate: 0) {
            .site-header__nav-wrap {
                animation-name: site-header-nav-reveal-fallback;
            }
        }
        .site-header__nav-wrap.is-nav-elevated,
        .site-header__nav-wrap.is-features-fixed {
            box-shadow: var(--header-nav-shadow-elevated);
            border-bottom-color: var(--header-nav-border);
            filter: none;
            opacity: 1;
        }
        body[class*="theme-"] .site-header__nav-wrap.is-nav-scrolled,
        body[class*="theme-"] .site-header__nav-wrap.is-nav-elevated,
        body[class*="theme-"] .site-header__nav-wrap.is-features-fixed {
            background: var(--holiday-nav-surface, var(--header-nav-surface));
            background-color: transparent;
            backdrop-filter: blur(var(--header-nav-glass-blur-scroll)) saturate(1.14);
            -webkit-backdrop-filter: blur(var(--header-nav-glass-blur-scroll)) saturate(1.14);
        }
        .site-header__nav-wrap.is-nav-scrolled .site-header__nav a,
        .site-header__nav-wrap.is-nav-elevated .site-header__nav a,
        .site-header__nav-wrap.is-features-fixed .site-header__nav a,
        .site-header__nav-wrap.is-nav-scrolled .site-header__nav-toggle,
        .site-header__nav-wrap.is-nav-elevated .site-header__nav-toggle,
        .site-header__nav-wrap.is-features-fixed .site-header__nav-toggle {
            color: rgba(255, 255, 255, 0.92);
        }
        .site-header__nav-wrap.is-nav-scrolled .site-header__nav a:hover,
        .site-header__nav-wrap.is-nav-elevated .site-header__nav a:hover,
        .site-header__nav-wrap.is-features-fixed .site-header__nav a:hover {
            color: #ffffff;
        }
        .site-header__nav-wrap.is-nav-scrolled .site-header__nav a.is-active,
        .site-header__nav-wrap.is-nav-elevated .site-header__nav a.is-active,
        .site-header__nav-wrap.is-features-fixed .site-header__nav a.is-active {
            color: var(--header-nav-active);
            text-shadow: 0 0 14px var(--header-nav-active-glow);
        }
        .site-header__nav-wrap.is-features-fixed {
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: min(var(--layout-max-width), calc(100% - 40px));
            margin-top: 0;
            margin-left: 0;
            margin-right: 0;
            padding-left: min(20px, 2vw);
            padding-right: min(20px, 2vw);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-top: none;
            border-radius: 0 0 14px 14px;
            z-index: 1055;
        }
        .site-header__nav-wrap-spacer {
            display: none;
        }
        .site-header__nav-wrap-spacer.is-active {
            display: block;
        }
        .site-header__nav-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            min-height: 44px;
            border: 1px solid rgba(255, 255, 255, 0.38);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            font-size: 0.9375rem;
            font-weight: 600;
            line-height: 1.3;
            transition: background 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .site-header__nav-toggle:hover {
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.55);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }
        .site-header__nav-toggle:focus-visible {
            outline: 2px solid rgba(255, 255, 255, 0.9);
            outline-offset: 2px;
        }
        .site-header__hamburger {
            display: inline-flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 5px;
            width: 1.35rem;
            height: 1.1rem;
        }
        .site-header__hamburger-line {
            display: block;
            width: 100%;
            height: 2px;
            border-radius: 999px;
            background: #ffffff;
            transition: transform 0.32s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.22s ease, width 0.22s ease;
        }
        .site-header__nav-toggle[aria-expanded="true"] .site-header__hamburger-line:nth-child(1) {
            transform: translateY(7px) rotate(45deg);
        }
        .site-header__nav-toggle[aria-expanded="true"] .site-header__hamburger-line:nth-child(2) {
            opacity: 0;
            width: 0;
        }
        .site-header__nav-toggle[aria-expanded="true"] .site-header__hamburger-line:nth-child(3) {
            transform: translateY(-7px) rotate(-45deg);
        }
        .site-header__nav-collapse {
            width: 100%;
        }
        .site-header__nav-close-wrap {
            display: flex;
            justify-content: flex-end;
            padding: 0.25rem 0.45rem 0;
        }
        .site-header__nav-close {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            min-height: 36px;
            padding: 0.35rem 0.7rem;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.38);
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1.2;
            transition: background 0.2s ease, border-color 0.2s ease;
        }
        .site-header__nav-close:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.6);
        }
        .site-header__nav-close:focus-visible {
            outline: 2px solid rgba(255, 255, 255, 0.9);
            outline-offset: 2px;
        }
        .site-header-doc-search {
            width: 100%;
            max-width: min(22rem, 100%);
            margin: 0;
        }
        .site-header__search-wrap .site-header-doc-search {
            max-width: 22rem;
        }
        @media (max-width: 575.98px) {
            .site-header__search-wrap .site-header-doc-search {
                max-width: none;
            }
        }
        .site-header-doc-search__field {
            position: relative;
            display: flex;
            align-items: center;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            border: 2px solid rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.14), inset 0 1px 0 rgba(255, 255, 255, 0.15);
        }
        .site-header-doc-search__field:focus-within {
            border-color: rgba(255, 255, 255, 0.85);
            box-shadow:
                0 0 0 3px rgba(255, 255, 255, 0.18),
                0 6px 24px rgba(0, 0, 0, 0.22);
        }
        .site-header-doc-search__input {
            flex: 1 1 auto;
            min-width: 0;
            width: 100%;
            border: none;
            background: transparent;
            color: #fff;
            font-size: 0.875rem;
            line-height: 1.35;
            padding: 0.5rem 2.75rem 0.5rem 1.05rem;
            border-radius: 999px;
        }
        .site-header-doc-search__input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        .site-header-doc-search__input:focus {
            outline: none;
        }
        .site-header-doc-search__submit {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            width: 2rem;
            height: 2rem;
            padding: 0;
            border: none;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
            color: var(--brand-navy-deep);
            background: rgba(255, 255, 255, 0.96);
            cursor: pointer;
            transition: background 0.2s ease, transform 0.15s ease, color 0.2s ease;
        }
        .site-header-doc-search__submit:hover {
            background: #fff;
            color: var(--brand-navy);
            transform: translateY(-50%) scale(1.05);
        }
        .site-header__nav-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-start;
            gap: 0.75rem 1.25rem;
            width: 100%;
        }
        .site-header__nav {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem 1.35rem;
            list-style: none;
            margin: 0;
            padding: 0;
            flex: 1 1 auto;
            min-width: 0;
        }
        .site-header__nav a {
            position: relative;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-family: var(--header-font-display);
            font-weight: 600;
            font-size: clamp(0.68rem, 0.42vw + 0.52rem, 0.78rem);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 0.42rem 0.15rem 0.5rem;
            transition: color 0.28s ease, text-shadow 0.28s ease, background 0.28s ease;
        }
        .site-header__nav a::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0.1rem;
            height: 2px;
            border-radius: 999px;
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.95), rgba(224, 242, 254, 0.85));
            transform: scaleX(0);
            transform-origin: left center;
            transition: transform 0.32s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .site-header__nav a:hover {
            color: #ffffff;
        }
        .site-header__nav a:hover::after {
            transform: scaleX(1);
        }
        .site-header__nav a.is-active {
            color: var(--header-nav-active);
            text-shadow: 0 0 14px var(--header-nav-active-glow);
        }
        .site-header__nav a.is-active::after {
            transform: scaleX(1);
            background: linear-gradient(90deg, var(--header-nav-active), #67e8f9);
            box-shadow: 0 0 12px var(--header-nav-active-glow);
        }
        .site-header__nav a,
        .site-header__actions-end .btn {
            min-height: 44px;
        }
        .site-header__actions {
            --site-header-control-h: 2.375rem;
            --site-header-control-line: 1.25;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem 0.75rem;
            margin-left: auto;
            flex: 0 0 auto;
        }
        .site-header__actions form {
            margin-bottom: 0;
        }
        .site-search-form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1 1 220px;
            max-width: 400px;
        }
        .site-search {
            position: relative;
            flex: 1;
            display: flex;
            align-items: center;
            min-width: 0;
        }
        .site-search__icon {
            position: absolute;
            left: 14px;
            width: 18px;
            height: 18px;
            pointer-events: none;
            color: #94a3b8;
            z-index: 1;
        }
        .site-search__input {
            width: 100%;
            border: none;
            border-radius: 999px;
            height: var(--site-header-control-h);
            min-height: var(--site-header-control-h);
            line-height: var(--site-header-control-line);
            padding: 0 1rem 0 2.65rem;
            font-size: 0.875rem;
            box-shadow: 0 2px 14px rgba(0, 0, 0, 0.14);
            box-sizing: border-box;
        }
        .site-search__input:focus {
            outline: 2px solid rgba(255, 255, 255, 0.65);
            outline-offset: 2px;
        }
        .site-search__btn {
            border-radius: 999px;
            height: var(--site-header-control-h);
            min-height: var(--site-header-control-h);
            line-height: var(--site-header-control-line);
            padding: 0 1.15rem;
            font-size: 0.875rem;
            font-weight: 600;
            border: none;
            background: #fff;
            color: var(--accent);
            box-shadow: 0 2px 14px rgba(0, 0, 0, 0.14);
            white-space: nowrap;
            box-sizing: border-box;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .site-search__btn:hover {
            background: #f8fafc;
            color: #0c4a6e;
        }
        .site-header__actions-end {
            display: flex;
            align-items: center;
            flex-wrap: nowrap;
            gap: 0.5rem;
            flex-shrink: 0;
            min-width: 0;
            justify-content: flex-end;
        }
        .site-header__actions-end .btn-header-dashboard,
        .site-header__actions-end .btn-header-logout,
        .site-header__actions-end .btn-header-login {
            height: var(--site-header-control-h);
            min-height: var(--site-header-control-h);
            line-height: var(--site-header-control-line);
            padding: 0 1.05rem;
            border-radius: 999px;
            font-size: 0.8125rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            box-sizing: border-box;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            border: none;
            white-space: nowrap;
            transition: transform 0.22s ease, box-shadow 0.28s ease, background 0.28s ease, color 0.22s ease;
        }
        .site-header__actions-end .btn-header-dashboard {
            color: #ffffff;
            background: linear-gradient(135deg, #003366 0%, #1d6fd4 52%, #38bdf8 100%);
            box-shadow: 0 4px 16px rgba(29, 111, 212, 0.35);
        }
        .site-header__actions-end .btn-header-dashboard:hover {
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow:
                0 6px 22px rgba(56, 189, 248, 0.45),
                0 0 18px rgba(56, 189, 248, 0.35);
        }
        .site-header__actions-end .btn-header-logout,
        .site-header__actions-end .btn-header-login {
            color: #0f2744;
            background: linear-gradient(180deg, #ffffff 0%, #e8f2fc 100%);
            box-shadow: 0 3px 12px rgba(15, 39, 68, 0.14);
        }
        .site-header__actions-end .btn-header-logout:hover,
        .site-header__actions-end .btn-header-login:hover {
            color: #003366;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(255, 255, 255, 0.28);
        }
        .site-header__actions-end .btn-header-dashboard i,
        .site-header__actions-end .btn-header-logout i,
        .site-header__actions-end .btn-header-login i {
            font-size: 0.78rem;
            opacity: 0.95;
        }
        .site-header__logout-form {
            display: flex;
            align-items: center;
            margin: 0;
        }
        @media (max-width: 991.98px) {
            .site-header__actions-end {
                min-width: 0;
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .site-header__nav-wrap {
                animation: none;
                opacity: 1;
                filter: none;
                translate: none;
            }
            .site-header__hamburger-line,
            .site-header__nav a::after,
            .site-header__nav-wrap,
            .site-header__actions-end .btn-header-dashboard,
            .site-header__actions-end .btn-header-logout,
            .site-header__actions-end .btn-header-login {
                transition: none;
            }
            .site-header__nav-toggle[aria-expanded="true"] .site-header__hamburger-line:nth-child(1),
            .site-header__nav-toggle[aria-expanded="true"] .site-header__hamburger-line:nth-child(3) {
                transform: none;
            }
            .site-header__nav-toggle[aria-expanded="true"] .site-header__hamburger-line:nth-child(2) {
                opacity: 1;
                width: 100%;
            }
        }
        .site-main {
            padding-top: 2.75rem;
            padding-bottom: 4.5rem;
        }
        .section-spacing {
            margin-bottom: 4rem;
        }
        @media (max-width: 575.98px) {
            .container {
                padding-left: 14px;
                padding-right: 14px;
            }
        }
        /* Beranda — grid galeri 3 kolom, rasio 16:9 */
        .beranda-galeri-grid {
            display: grid;
            gap: 1.25rem;
            grid-template-columns: minmax(0, 1fr);
        }
        @media (min-width: 576px) {
            .beranda-galeri-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 992px) {
            .beranda-galeri-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        /* Beranda — pratinjau galeri kegiatan (hover: blur + teks putih) */
        a.beranda-galeri-item {
            display: block;
            text-decoration: none;
            color: inherit;
        }
        a.beranda-galeri-item:hover {
            color: inherit;
        }
        .beranda-galeri-item__frame {
            position: relative;
            display: block;
            border-radius: 16px;
            overflow: hidden;
            aspect-ratio: 4 / 3;
            background: #eef2f6;
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        a.beranda-galeri-item:hover .beranda-galeri-item__frame {
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.16);
            transform: translateY(-3px);
        }
        .beranda-galeri-item__img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.45s ease, filter 0.38s ease;
        }
        a.beranda-galeri-item:hover .beranda-galeri-item__img {
            transform: scale(1.04);
            filter: blur(8px);
        }
        .beranda-galeri-item__glass {
            position: absolute;
            inset: 0;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 1rem 1.1rem 1.15rem;
            pointer-events: none;
            opacity: 0;
            background: rgba(15, 23, 42, 0.28);
            backdrop-filter: blur(0);
            -webkit-backdrop-filter: blur(0);
            transition: opacity 0.32s ease, backdrop-filter 0.38s ease, background 0.32s ease;
        }
        a.beranda-galeri-item:hover .beranda-galeri-item__glass {
            opacity: 1;
            background: rgba(15, 23, 42, 0.48);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }
        .beranda-galeri-item__hover-title {
            display: block;
            color: #ffffff;
            font-size: clamp(0.88rem, 0.8rem + 0.25vw, 1rem);
            font-weight: 800;
            line-height: 1.4;
            letter-spacing: 0.01em;
            text-shadow: 0 2px 14px rgba(0, 0, 0, 0.35);
        }
        .beranda-galeri-item__hover-date {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            margin-top: 0.45rem;
            color: rgba(255, 255, 255, 0.92);
            font-size: 0.78rem;
            font-weight: 500;
            letter-spacing: 0.03em;
        }
        .beranda-galeri-item__hover-date i {
            font-size: 0.72rem;
            opacity: 0.95;
        }
        .beranda-galeri-item__zoom {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            bottom: auto;
            z-index: 3;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 50%;
            background: #ffffff;
            color: #1e3a8a;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.18);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
            pointer-events: none;
            opacity: 0;
            transform: scale(0.88);
            transition: opacity 0.28s ease, transform 0.3s ease;
        }
        a.beranda-galeri-item:hover .beranda-galeri-item__zoom {
            opacity: 1;
            transform: scale(1);
        }
        @media (prefers-reduced-motion: reduce) {
            .beranda-galeri-item__img {
                transition: none;
            }
            a.beranda-galeri-item:hover .beranda-galeri-item__img {
                transform: none;
                filter: none;
            }
            a.beranda-galeri-item:hover .beranda-galeri-item__glass {
                opacity: 1;
                backdrop-filter: none;
            }
            .beranda-galeri-item__zoom {
                opacity: 1;
                transform: none;
            }
        }
        .beranda-galeri-empty {
            box-shadow: 0 2px 12px rgba(15, 23, 42, 0.04);
        }
        .section-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
            background: var(--surface);
        }
        .profile-minimal {
            border-left: 4px solid var(--accent);
        }
        .profile-minimal .profile-line {
            font-size: 0.88rem;
            line-height: 1.55;
            color: var(--text-main);
            margin-bottom: 0.45rem;
        }
        .profile-minimal .profile-line:last-child {
            margin-bottom: 0;
        }
        .profile-minimal .profile-label {
            font-weight: 600;
            color: #0f172a;
            margin-right: 0.25rem;
        }
        .profile-minimal .profile-rich {
            display: inline;
        }
        .profile-minimal .profile-rich p {
            display: inline;
            margin: 0;
        }
        .news-card,
        .profile-minimal {
            transition: transform 0.22s ease, box-shadow 0.22s ease;
        }
        .person-card {
            border: 0;
            border-radius: 0.75rem;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-align: center;
        }
        .person-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(15, 23, 42, 0.12);
        }
        /* Foto pas 3:4, tinggi seragam, tidak distorsi */
        .person-photo-wrap {
            width: 126px;
            max-width: 100%;
            height: 168px;
            margin: 0.65rem auto 0.35rem;
            border-radius: 0.5rem;
            overflow: hidden;
            background: #e2e8f0;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.06);
        }
        .person-photo-wrap .person-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center top;
            display: block;
            border-radius: 0.5rem;
        }
        .person-name {
            font-weight: 700;
            font-size: 0.86rem;
            margin: 0.35rem 0 0.15rem;
            line-height: 1.25;
            color: #0f172a;
        }
        .person-nip {
            font-size: 0.72rem;
            color: #475569;
            margin: 0 0 0.2rem;
            line-height: 1.3;
            letter-spacing: 0.02em;
        }
        .person-position {
            font-size: 0.7rem;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.35;
        }
        .person-card .card-body {
            padding: 0.5rem 0.6rem 0.65rem;
        }
        .news-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.12);
        }
        .gallery-activity-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
            background: var(--surface);
            overflow: hidden;
            height: 100%;
            transition: transform 0.22s ease, box-shadow 0.22s ease;
        }
        .gallery-activity-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.12);
        }
        .gallery-activity-card__link {
            display: block;
            text-decoration: none;
            color: inherit;
            height: 100%;
        }
        .gallery-activity-card__link:hover {
            color: inherit;
        }
        .gallery-activity-thumb-wrap {
            position: relative;
            width: 100%;
            aspect-ratio: 4 / 3;
            background: #e2e8f0;
            overflow: hidden;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: box-shadow 0.25s ease;
        }
        .gallery-activity-thumb {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
            border-radius: 12px;
            transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .gallery-activity-card:hover .gallery-activity-thumb {
            transform: scale(1.05);
        }
        .gallery-activity-card:hover .gallery-activity-thumb-wrap {
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.14);
        }
        .gallery-activity-card .card-body {
            padding: 1rem 1.1rem 1.15rem;
        }
        .news-cover {
            height: 180px;
            object-fit: cover;
        }
        .admin-doc-search-wrap {
            position: relative;
        }
        .admin-doc-search-wrap .admin-doc-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 17px;
            height: 17px;
            color: #94a3b8;
            pointer-events: none;
        }
        .admin-doc-search-wrap .form-control {
            padding-left: 2.35rem;
            border-radius: 0.65rem;
        }
        .btn {
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }
        .btn:hover {
            transform: translateY(-1px);
        }
        h1, h2, h3, h4, h5, h6, .navbar, .modal-title {
            font-family: var(--font-sans);
        }
        .beranda-section__title,
        .digital-library__title--hero,
        .digital-library__title:not(.digital-library__title--hero) {
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        /* Bagan organisasi (struktur.php) */
        .org-chart-wrap {
            overflow-x: auto;
            padding: 0.5rem 0 1.5rem;
        }
        .org-chart {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            min-width: min(100%, 520px);
        }
        .org-chart__level {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.75rem;
        }
        .org-chart__node {
            background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
            border: 1px solid #cbd5e1;
            border-radius: 0.65rem;
            padding: 0.65rem 1.15rem;
            font-size: 0.82rem;
            font-weight: 600;
            color: #0f172a;
            text-align: center;
            max-width: 280px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
        }
        .org-chart__node--head {
            background: linear-gradient(135deg, #0c4a6e, #0369a1);
            color: #fff;
            border-color: #075985;
            font-size: 0.9rem;
        }
        .org-chart__connector {
            width: 2px;
            height: 12px;
            background: #94a3b8;
            border-radius: 1px;
        }
        /* Footer — otoritas: navy gelap + kartu putih */
        .site-footer.site-footer--modern {
            padding: 0;
            border: none;
            background: #001a2e;
            box-shadow: none;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
        }
        .site-footer__cta-band {
            background:
                radial-gradient(560px 220px at 8% 4%, rgba(56, 189, 248, 0.22), transparent 70%),
                radial-gradient(560px 220px at 92% 8%, rgba(196, 181, 253, 0.2), transparent 70%),
                linear-gradient(162deg, #012847 0%, #011d38 55%, #011729 100%);
            color: rgba(255, 255, 255, 0.92);
            padding: clamp(28px, 4vw, 48px) 0;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.07);
        }
        .site-footer-card {
            position: relative;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            padding-block: clamp(1.3rem, 3vw, 1.75rem);
            padding-inline: clamp(1.2rem, 3vw, 1.7rem);
            box-shadow:
                0 14px 34px -14px rgba(15, 23, 42, 0.28),
                inset 0 1px 0 rgba(255, 255, 255, 0.75);
            border: 1px solid rgba(203, 213, 225, 0.55);
            backdrop-filter: blur(7px);
            -webkit-backdrop-filter: blur(7px);
            color: #1e293b;
            transition:
                transform 0.22s cubic-bezier(0.22, 1, 0.36, 1),
                box-shadow 0.22s ease,
                border-color 0.22s ease;
            will-change: transform;
        }
        .site-footer-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #0f62cf 0%, #7c3aed 55%, #0f766e 100%);
            opacity: 0.95;
        }
        .site-footer-card:hover {
            transform: translateY(-3px);
            box-shadow:
                0 18px 32px -10px rgba(15, 23, 42, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            border-color: rgba(148, 163, 184, 0.5);
        }
        @media (prefers-reduced-motion: reduce) {
            .site-footer-card {
                transition: box-shadow 0.2s ease, border-color 0.2s ease;
                will-change: auto;
            }
            .site-footer-card:hover {
                transform: none;
            }
        }
        .site-footer-card__title {
            margin: 0 0 0.65rem;
            font-size: clamp(18px, 1rem + 0.45vw, 20px);
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.3;
            color: #0f172a;
        }
        .site-footer-card__lead {
            margin: 0 0 1.15rem;
            font-size: clamp(14px, 0.88rem + 0.2vw, 16px);
            font-weight: 400;
            line-height: 1.55;
            color: #64748b;
        }
        .site-footer-card__lead--emphasis {
            color: #475569;
            font-weight: 500;
        }
        .site-footer-form .site-footer-form__control {
            min-height: 44px;
            padding: 11px 14px;
            font-size: 16px;
            border-radius: 10px;
            border: 1px solid #d5e0ec;
            background: rgba(255, 255, 255, 0.95);
            color: #0f172a;
            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                background-color 0.2s ease;
        }
        .site-footer-form__label {
            display: inline-block;
            margin-bottom: 0.45rem;
            font-size: 0.9375rem;
            font-weight: 600;
            color: #111827;
            line-height: 1.4;
        }
        .site-footer-form .site-footer-form__control::placeholder {
            color: #94a3b8;
        }
        .site-footer-form .site-footer-form__control:focus {
            border-color: #0f62cf;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(15, 98, 207, 0.16);
            outline: none;
        }
        .site-footer-form__textarea {
            min-height: 122px;
            resize: vertical;
            line-height: 1.55;
        }
        .btn-site-footer-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: auto;
            min-height: 42px;
            padding: 10px 22px;
            font-size: 0.88rem;
            line-height: 1.2;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            white-space: nowrap;
            color: #fff !important;
            -webkit-text-fill-color: #fff;
            background: linear-gradient(180deg, #0f62cf 0%, #0a56b6 100%);
            border: none;
            border-radius: 10px;
            box-shadow: 0 10px 22px rgba(15, 98, 207, 0.32);
            transition:
                background 0.22s ease,
                box-shadow 0.22s ease,
                filter 0.22s ease,
                transform 0.18s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .btn-site-footer-submit:hover {
            background: #084ea7;
            box-shadow: 0 12px 24px rgba(8, 78, 167, 0.34);
            filter: brightness(1.02);
            color: #fff !important;
            transform: translateY(-2px);
        }
        .btn-site-footer-submit:active {
            transform: translateY(0);
            filter: brightness(0.98);
            box-shadow: 0 3px 10px rgba(0, 38, 77, 0.32);
        }
        @media (prefers-reduced-motion: reduce) {
            .btn-site-footer-submit {
                transition: background 0.2s ease, filter 0.2s ease, box-shadow 0.2s ease;
            }
            .btn-site-footer-submit:hover {
                transform: none;
            }
        }
        .btn-site-footer-submit:disabled {
            opacity: 0.75;
            transform: none;
            cursor: not-allowed;
        }
        .site-footer-form__status {
            min-height: 1.35rem;
        }
        .footer-contact.footer-contact--boxed {
            gap: 0.75rem;
            margin-top: 0.25rem;
        }
        .footer-contact.footer-contact--boxed .footer-contact__row {
            border-radius: 12px;
            padding: 0.62rem 0.7rem;
            background: rgba(241, 245, 249, 0.7);
            border: 1px solid rgba(203, 213, 225, 0.45);
            font-size: clamp(14px, 0.88rem + 0.15vw, 16px);
            line-height: 1.55;
            color: #334155;
            transition: all 0.2s ease;
        }
        .footer-contact.footer-contact--boxed .footer-contact__row:hover {
            color: #0f4c75;
            border-color: rgba(56, 189, 248, 0.4);
            background: rgba(236, 251, 255, 0.88);
        }
        .footer-contact.footer-contact--boxed .footer-contact__icon {
            width: 2rem;
            height: 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: linear-gradient(135deg, #e8f1ff 0%, #d6e8ff 100%);
            color: #0f62cf;
            font-size: 0.95rem;
            margin-top: 0;
        }
        .footer-contact.footer-contact--boxed .footer-contact__label {
            font-size: 0.7rem;
            letter-spacing: 0.06em;
            color: #64748b;
            margin-bottom: 0.25rem;
        }
        .site-footer__links-band {
            background: #001428;
            border-top: 1px solid rgba(255, 255, 255, 0.07);
            box-shadow: none;
        }
        .site-footer__nav-wrap {
            margin-bottom: 1.25rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .site-footer__heading {
            color: #0c4a6e;
            font-weight: 700;
        }
        .site-footer__lead {
            color: #64748b;
        }
        .site-footer__nav {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem 1.35rem;
            align-items: center;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .site-footer__nav a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: clamp(14px, 0.88rem + 0.12vw, 15px);
            font-weight: 500;
            border-bottom: 1px solid transparent;
            transition: color 0.2s ease, border-color 0.2s ease;
        }
        .site-footer__nav a:hover {
            color: #ffffff;
            border-bottom-color: rgba(255, 255, 255, 0.45);
        }
        .footer-contact {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            min-width: min(100%, 280px);
        }
        .footer-contact__row {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            color: #334155;
            font-size: 0.95rem;
            line-height: 1.5;
            text-decoration: none;
        }
        .footer-contact__row:hover {
            color: #0c4a6e;
        }
        .footer-contact__icon {
            flex-shrink: 0;
            width: 1.35rem;
            text-align: center;
            color: #003366;
            font-size: 1.1rem;
            line-height: 1.45;
            margin-top: 0.08rem;
        }
        .footer-contact__text {
            flex: 1;
            min-width: 0;
        }
        .footer-contact__label {
            display: block;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 0.2rem;
        }
        .site-footer__copyright {
            margin-top: 0;
            padding: 24px 0;
            border-top: 2px solid rgba(255, 255, 255, 0.22);
            text-align: center;
            background: #000f1f;
        }
        .site-footer__copyright-inner {
            display: block;
        }
        .site-footer__copyright-text {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Arial, sans-serif;
            font-size: 13.5px;
            font-weight: 400;
            color: #cbd5e1;
            line-height: 1.5;
            letter-spacing: 0;
        }

        /* Kursor dekoratif: titik biru laut + ring transparan (identitas Aru) */
        .cursor-follower {
            pointer-events: none;
            position: fixed;
            inset: 0;
            z-index: 100050;
            overflow: hidden;
            opacity: 0;
            transition: opacity 0.35s ease;
        }
        .cursor-follower.is-ready {
            opacity: 1;
        }
        .cursor-follower.is-hidden {
            opacity: 0 !important;
        }
        .cursor-follower__ring-slot {
            position: fixed;
            left: 0;
            top: 0;
            width: 0;
            height: 0;
            pointer-events: none;
            will-change: transform;
        }
        .cursor-follower__ring {
            position: absolute;
            left: 0;
            top: 0;
            width: 34px;
            height: 34px;
            margin: 0;
            border: 1.5px solid rgba(3, 105, 161, 0.38);
            background: transparent;
            box-shadow: 0 0 0 1px rgba(14, 165, 233, 0.12);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition:
                width 0.28s cubic-bezier(0.22, 1, 0.36, 1),
                height 0.28s cubic-bezier(0.22, 1, 0.36, 1),
                border-color 0.28s ease,
                box-shadow 0.28s ease;
        }
        .cursor-follower__dl {
            position: absolute;
            left: 0;
            top: 0;
            transform: translate(-50%, -50%);
            font-size: 0.95rem;
            color: #0369a1;
            opacity: 0;
            transition: opacity 0.22s ease, transform 0.22s ease;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.9);
            pointer-events: none;
            line-height: 1;
        }
        .cursor-follower__dot {
            position: fixed;
            left: 0;
            top: 0;
            pointer-events: none;
            will-change: transform;
            border-radius: 50%;
            width: 6px;
            height: 6px;
            margin: 0;
            background: radial-gradient(circle at 30% 30%, #38bdf8, #0369a1 55%, #0c4a6e);
            box-shadow:
                0 0 0 1px rgba(255, 255, 255, 0.35),
                0 1px 6px rgba(8, 47, 73, 0.35);
            transform: translate3d(0, 0, 0) translate(-50%, -50%);
            transition: opacity 0.22s ease, transform 0.22s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .cursor-follower.is-hover .cursor-follower__ring {
            width: 52px;
            height: 52px;
            border-color: rgba(3, 105, 161, 0.22);
            box-shadow: 0 0 0 1px rgba(14, 165, 233, 0.08);
        }
        .cursor-follower.is-hover .cursor-follower__dot {
            opacity: 0;
            transform: translate3d(0, 0, 0) translate(-50%, -50%) scale(0.2);
        }
        .cursor-follower.is-download-hover .cursor-follower__ring {
            width: 56px;
            height: 56px;
            border-color: rgba(3, 105, 161, 0.32);
        }
        .cursor-follower.is-download-hover .cursor-follower__dl {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1.05);
        }
        .cursor-follower.is-download-hover .cursor-follower__dot {
            opacity: 0;
            transform: translate3d(0, 0, 0) translate(-50%, -50%) scale(0.15);
        }
        @media (pointer: coarse) {
            .cursor-follower {
                display: none !important;
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .cursor-follower {
                display: none !important;
            }
        }

        /* Digital Library — halaman dokumen */
        .digital-library {
            font-family: var(--font-sans);
        }
        .digital-library--intl {
            background-color: #f8f9fa;
        }
        /* Beranda — hero Perpustakaan Digital */
        .digital-library.digital-library--beranda-hero.digital-library--intl {
            background: linear-gradient(180deg, #eef4fc 0%, #f4f8fd 45%, #ffffff 100%);
        }
        .digital-library--beranda-hero .digital-library__hero--intl {
            padding: clamp(72px, 10vw, 110px) 1rem clamp(40px, 5vw, 64px);
            margin-bottom: 0;
            background: transparent;
        }
        .digital-library--beranda-hero .digital-library__title--hero {
            font-size: clamp(1.12rem, 0.78rem + 2.1vw, 3.75rem);
            font-weight: 800;
            letter-spacing: clamp(0.01em, 0.35vw, 0.07em);
            color: #003366;
            margin: 0 0 0.65rem;
            line-height: 1.12;
            white-space: nowrap;
            text-wrap: nowrap;
        }
        .digital-library--beranda-hero .digital-library__subtitle {
            font-weight: 400;
            color: #343a40;
            font-size: clamp(15px, 0.9rem + 0.35vw, 17px);
            margin: 0 auto 2rem;
            max-width: 44rem;
        }
        .digital-library--beranda-hero .digital-library__divider {
            display: none;
        }
        .digital-library--beranda-hero .library-doc-search-shell {
            width: 80%;
            max-width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
        @media (max-width: 767.98px) {
            .digital-library--beranda-hero .library-doc-search-shell {
                width: 100%;
                max-width: 100%;
            }
        }
        /* Kolom pencarian hero — pill + tombol CARI (palet gambar 1) */
        .digital-library--beranda-hero.digital-library--intl .library-doc-search-header__combo {
            --lib-search-icon: #003366;
            --lib-search-btn: #0d50a1;
            --lib-search-btn-hover: #0c4792;
            --lib-search-btn-active: #0a3f82;
            --lib-search-border: rgba(13, 80, 161, 0.14);
            --lib-search-placeholder: #8b95a8;
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            align-items: center;
            gap: 0;
            width: 100%;
            max-width: min(100%, 68rem);
            margin-left: auto;
            margin-right: auto;
            padding: 6px 6px 6px 8px;
            background: #ffffff;
            border: 1px solid var(--lib-search-border);
            border-radius: 999px;
            overflow: hidden;
            box-shadow:
                0 2px 8px rgba(13, 80, 161, 0.08),
                0 12px 32px rgba(13, 80, 161, 0.1);
            transition: border-color 0.25s ease, box-shadow 0.28s ease;
        }
        .digital-library--beranda-hero.digital-library--intl .library-doc-search-header__combo:focus-within {
            border-color: rgba(13, 80, 161, 0.28);
            box-shadow:
                0 0 0 3px rgba(13, 80, 161, 0.12),
                0 2px 8px rgba(13, 80, 161, 0.08),
                0 14px 36px rgba(13, 80, 161, 0.14);
        }
        .digital-library--beranda-hero.digital-library--intl .library-doc-search-header__field {
            flex: 1 1 auto;
            min-width: 0;
            background: transparent;
            border: none;
            box-shadow: none;
        }
        .digital-library--beranda-hero.digital-library--intl .library-doc-search-header__icon {
            color: var(--lib-search-icon, #003366);
            font-size: 1.35rem;
            padding: 0 0.35rem 0 clamp(0.85rem, 2.5vw, 1.25rem);
            opacity: 1;
        }
        @media (min-width: 768px) {
            .digital-library--beranda-hero.digital-library--intl .library-doc-search-header__icon {
                font-size: 1.55rem;
                padding-left: 1.35rem;
            }
        }
        .digital-library--beranda-hero.digital-library--intl .library-doc-search-header__icon svg {
            stroke: var(--lib-search-icon, #003366);
        }
        .digital-library--beranda-hero.digital-library--intl .library-doc-search-header__input {
            padding: 12px 12px 12px 6px;
            font-size: 1rem;
            background: transparent;
            color: #111827;
        }
        @media (min-width: 768px) {
            .digital-library--beranda-hero.digital-library--intl .library-doc-search-header__input {
                padding: 14px 18px 14px 8px;
                font-size: 1.02rem;
            }
        }
        .digital-library--beranda-hero.digital-library--intl .library-doc-search-header__input::placeholder {
            color: var(--lib-search-placeholder, #8b95a8);
        }
        .digital-library--beranda-hero.digital-library--intl .library-doc-search-header__submit {
            flex: 0 0 auto;
            align-self: center;
            margin: 0;
            padding: 0.72rem clamp(1.35rem, 3.5vw, 2rem);
            min-height: 44px;
            border: none;
            border-left: none;
            border-radius: 999px;
            background: var(--lib-search-btn, #0d50a1);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.8125rem;
            letter-spacing: 0.14em;
            line-height: 1.2;
            cursor: pointer;
            white-space: nowrap;
            box-shadow: none;
            transition: background 0.22s ease;
        }
        .digital-library--beranda-hero.digital-library--intl .library-doc-search-header__submit:hover {
            background: var(--lib-search-btn-hover, #0c4792);
            color: #ffffff;
            transform: none;
        }
        .digital-library--beranda-hero.digital-library--intl .library-doc-search-header__submit:active {
            background: var(--lib-search-btn-active, #0a3f82);
        }
        @media (max-width: 575.98px) {
            .digital-library--beranda-hero.digital-library--intl .library-doc-search-header__combo {
                flex-direction: row;
                flex-wrap: nowrap;
                border-radius: 999px;
                padding: 5px 5px 5px 6px;
            }
            .digital-library--beranda-hero.digital-library--intl .library-doc-search-header__field {
                min-height: 2.85rem;
            }
            .digital-library--beranda-hero.digital-library--intl .library-doc-search-header__input {
                padding: 10px 8px 10px 4px;
                font-size: 0.9rem;
            }
            .digital-library--beranda-hero.digital-library--intl .library-doc-search-header__submit {
                width: auto;
                min-width: 4.5rem;
                min-height: 40px;
                padding: 0.6rem 1rem;
                border-radius: 999px;
                border-top: none;
                font-size: 0.75rem;
                letter-spacing: 0.1em;
            }
        }
        .digital-library--beranda-hero .card.section-card {
            border: 1px solid #e9ecef;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
        }
        .digital-library--beranda-hero .card .h5.text-secondary {
            color: #003366 !important;
            font-weight: 700;
            font-size: clamp(17px, 1rem + 0.4vw, 19px);
        }
        .digital-library__hero--intl {
            background: transparent;
            border: none;
            border-radius: 0;
            box-shadow: none;
            padding: 60px 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        @media (max-width: 575.98px) {
            .digital-library__hero--intl {
                padding: 40px 0.85rem;
            }
        }
        .digital-library__hero-head {
            max-width: 52rem;
            margin-left: auto;
            margin-right: auto;
        }
        .digital-library__title--hero {
            font-weight: 800;
            font-size: clamp(1.5rem, 3.5vw + 0.75rem, 2.35rem);
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #1a1d21;
            margin: 0 0 0.85rem;
            line-height: 1.15;
        }
        .digital-library__subtitle {
            font-weight: 300;
            font-size: clamp(0.9375rem, 1.5vw + 0.5rem, 1.125rem);
            color: #495057;
            margin: 0 auto 1.75rem;
            line-height: 1.55;
            max-width: 42rem;
        }
        .digital-library__divider {
            height: 1px;
            width: 100%;
            max-width: min(42rem, 100%);
            margin: 0 auto 2rem;
            background: linear-gradient(90deg, transparent, #ced4da 15%, #ced4da 85%, transparent);
            border: 0;
        }
        .digital-library__hero:not(.digital-library__hero--intl) {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 45%, #f8fafc 100%);
            border: 1px solid rgba(3, 105, 161, 0.12);
            border-radius: 1rem;
            padding: 1.75rem 1.5rem 1.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 32px rgba(8, 47, 73, 0.06);
        }
        @media (min-width: 768px) {
            .digital-library__hero:not(.digital-library__hero--intl) {
                padding: 2rem 2.25rem 2rem;
            }
        }
        .digital-library__badge {
            display: inline-block;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #0369a1;
            background: rgba(3, 105, 161, 0.1);
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            margin-bottom: 0.75rem;
        }
        .digital-library__title:not(.digital-library__title--hero) {
            font-weight: 700;
            font-size: clamp(1.35rem, 2.5vw + 0.5rem, 1.85rem);
            color: #0c4a6e;
            letter-spacing: -0.02em;
            margin: 0 0 0.5rem;
            line-height: 1.2;
        }
        .digital-library__lead {
            margin: 0;
            font-size: 0.95rem;
            color: #64748b;
            max-width: 42rem;
            line-height: 1.55;
        }
        .digital-library__table-wrap {
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.04);
        }
        .digital-library__table {
            margin: 0;
            font-size: 0.9375rem;
            vertical-align: middle;
        }
        .digital-library__table thead th {
            font-weight: 600;
            font-size: 0.84rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #475569;
            border-bottom: 2px solid rgba(0, 51, 102, 0.12);
            background: #f9fafb;
            padding: 1rem 1.25rem;
            white-space: nowrap;
        }
        .digital-library__table tbody td {
            padding: 1.1rem 1.25rem;
            border-color: #f1f5f9;
        }
        .digital-library__table tbody tr > td {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }
        .digital-library__table tbody tr {
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }
        .digital-library__table tbody tr:hover {
            background-color: #f8fafc;
            box-shadow: inset 0 1px 0 rgba(0, 51, 102, 0.06), inset 0 -1px 0 rgba(0, 51, 102, 0.06);
        }
        .digital-library__table tbody tr:hover > td {
            background-color: #f8fafc;
        }
        .digital-library__table tbody tr:hover > td:first-child {
            box-shadow: inset 3px 0 0 rgba(0, 51, 102, 0.22);
        }
        .digital-library__doc-title {
            font-weight: 600;
            color: #1e293b;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .digital-library__doc-icon {
            flex-shrink: 0;
            font-size: 1.15rem;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.15rem;
        }
        .digital-library__doc-icon .fa-file-pdf,
        .digital-library__doc-icon .fa-file-pdf-o {
            color: #dc2626;
        }
        .digital-library__cat {
            display: inline-block;
            font-size: 0.8rem;
            font-weight: 500;
            padding: 0.2rem 0.55rem;
            border-radius: 0.35rem;
            background: #f1f5f9;
            color: #475569;
        }
        .digital-library__cat--kelembagaan {
            background: #dbeafe;
            color: #1e3a8a;
        }
        .digital-library__cat--pelayanan {
            background: #dcfce7;
            color: #166534;
        }
        .digital-library__cat--sakip {
            background: #fef3c7;
            color: #92400e;
        }
        .digital-library__size {
            font-variant-numeric: tabular-nums;
            color: #64748b;
            font-size: 0.88rem;
        }
        .digital-library__actions .btn {
            font-size: 0.8rem;
        }
        .digital-library--intl .library-doc-search-shell {
            width: 100%;
            margin-top: 0;
            padding: 0;
            background: transparent;
            border: none;
            border-radius: 0;
            box-shadow: none;
        }
        .library-doc-search-shell {
            width: 100%;
            margin-top: 1.25rem;
            padding: clamp(1.25rem, 3.5vw, 2rem) clamp(1rem, 3.2vw, 2.25rem);
            background: linear-gradient(165deg, #f8fafc 0%, #eef2f7 48%, #f4f6f9 100%);
            border: 1px solid rgba(148, 163, 184, 0.28);
            border-radius: 1.25rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75);
        }
        .library-doc-search-header {
            scroll-margin-top: 6rem;
        }
        .library-doc-search-header--prominent {
            width: 100%;
            max-width: 100%;
            margin-top: 0;
            margin-left: auto;
            margin-right: auto;
        }
        .library-doc-category-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .library-doc-category-filter__btn {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 600;
            padding: 0.35rem 0.8rem;
            line-height: 1.2;
            transition: all 0.2s ease;
        }
        .library-doc-category-filter__btn:hover {
            border-color: #0c4a6e;
            color: #0c4a6e;
        }
        .library-doc-category-filter__btn.is-active {
            background: #0c4a6e;
            border-color: #0c4a6e;
            color: #ffffff;
        }
        .library-doc-search-header__combo {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            align-items: stretch;
            gap: 0;
            width: 100%;
            max-width: min(100%, 52rem);
            margin-left: auto;
            margin-right: auto;
            background: #ffffff;
            border: 2px solid rgba(0, 51, 102, 0.22);
            border-radius: 999px;
            overflow: hidden;
            box-shadow:
                0 2px 4px rgba(15, 23, 42, 0.04),
                0 8px 24px rgba(15, 23, 42, 0.08),
                0 18px 48px -12px rgba(15, 23, 42, 0.12);
            transition: border-color 0.25s ease, box-shadow 0.28s ease;
        }
        .library-doc-search-header__combo:focus-within {
            border-color: rgba(14, 165, 233, 0.75);
            box-shadow:
                0 0 0 4px rgba(14, 165, 233, 0.2),
                0 4px 12px rgba(15, 23, 42, 0.08),
                0 16px 40px -8px rgba(15, 23, 42, 0.14),
                0 24px 56px -16px rgba(0, 51, 102, 0.12);
        }
        .digital-library--intl .library-doc-search-header__combo {
            max-width: min(100%, 68rem);
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            box-shadow: none;
            background: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .digital-library--intl .library-doc-search-header__combo:focus-within {
            border-color: #0d2847;
            box-shadow: 0 0 0 2px rgba(13, 40, 71, 0.18);
        }
        .library-doc-search-header__field {
            position: relative;
            display: flex;
            align-items: center;
            flex: 1 1 auto;
            min-width: 0;
            background: #ffffff;
            border: none;
            border-radius: 0;
            box-shadow: none;
        }
        .digital-library--intl .library-doc-search-header__field {
            background: #ffffff;
        }
        .library-doc-search-header__icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            padding: 0 0.35rem 0 clamp(1rem, 3vw, 1.35rem);
            color: #003366;
            font-size: 1.35rem;
            line-height: 1;
            pointer-events: none;
            opacity: 0.92;
        }
        @media (min-width: 768px) {
            .library-doc-search-header__icon {
                font-size: 1.55rem;
                padding-left: 1.5rem;
            }
        }
        .digital-library--intl .library-doc-search-header__icon {
            font-size: 1rem;
            padding: 0 0.25rem 0 0.85rem;
            color: #6c757d;
            opacity: 1;
        }
        @media (min-width: 768px) {
            .digital-library--intl .library-doc-search-header__icon {
                font-size: 1.05rem;
                padding-left: 1rem;
            }
        }
        .library-doc-search-header__input {
            flex: 1;
            min-width: 0;
            border: 0;
            background: transparent;
            padding: 14px 14px 14px 8px;
            font-size: 1rem;
            font-family: 'Inter', system-ui, sans-serif;
            color: #111827;
            outline: none;
            -webkit-appearance: none;
            appearance: none;
        }
        .library-doc-search-header__input::-webkit-search-decoration,
        .library-doc-search-header__input::-webkit-search-cancel-button,
        .library-doc-search-header__input::-webkit-search-results-button {
            -webkit-appearance: none;
            appearance: none;
            display: none;
        }
        .library-doc-search-header__input::-ms-clear {
            display: none;
            width: 0;
            height: 0;
        }
        @media (min-width: 768px) {
            .library-doc-search-header__input {
                padding: 15px 20px 15px 10px;
                font-size: 1.02rem;
            }
        }
        .digital-library--intl .library-doc-search-header__input {
            padding: 8px 12px 8px 6px;
            font-size: 0.9375rem;
            background: #ffffff;
        }
        @media (min-width: 768px) {
            .digital-library--intl .library-doc-search-header__input {
                padding: 9px 16px 9px 8px;
                font-size: 0.96875rem;
            }
        }
        .library-doc-search-header__input::placeholder {
            color: #9ca3af;
            font-family: 'Inter', system-ui, sans-serif;
        }
        .library-doc-search-header__input:focus::placeholder {
            color: transparent;
        }
        .library-doc-search-header__clear {
            flex-shrink: 0;
            margin: 0 0.65rem 0 0;
            width: 2rem;
            height: 2rem;
            border: 0;
            border-radius: 999px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 1.25rem;
            line-height: 1;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .library-doc-search-header__clear:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .library-doc-search-header__submit {
            flex: 0 0 auto;
            align-self: stretch;
            margin: 0;
            padding: 14px clamp(1.25rem, 4vw, 2rem);
            border: none;
            border-radius: 0;
            background: #003366;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.8125rem;
            letter-spacing: 0.12em;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.22s ease, box-shadow 0.22s ease;
        }
        .library-doc-search-header__submit:hover {
            background: #00254d;
            color: #ffffff;
        }
        .library-doc-search-header__submit:active {
            background: #001f3d;
        }
        .digital-library--intl .library-doc-search-header__submit {
            padding: 8px 1.35rem;
            font-weight: 600;
            font-size: 0.8125rem;
            letter-spacing: 0.08em;
            border-radius: 0 8px 8px 0;
            border-left: 1.5px solid #cbd5e1;
            background: #152238;
            align-self: stretch;
        }
        .digital-library--intl .library-doc-search-header__submit:hover {
            background: #0f1829;
            color: #ffffff;
        }
        .digital-library--intl .library-doc-search-header__submit:active {
            background: #0a111c;
        }
        @media (max-width: 575.98px) {
            .library-doc-search-shell {
                padding: 1.1rem 0.85rem;
                border-radius: 1rem;
            }
            .digital-library--intl .library-doc-search-shell {
                padding: 0;
                border-radius: 0;
            }
            .library-doc-search-header__combo {
                flex-direction: column;
                flex-wrap: nowrap;
                border-radius: 1rem;
                max-width: 100%;
            }
            .digital-library--intl .library-doc-search-header__combo {
                border-radius: 8px;
            }
            .library-doc-search-header__field {
                width: 100%;
                min-height: 3.25rem;
            }
            .digital-library--intl .library-doc-search-header__field {
                min-height: 2.75rem;
            }
            .library-doc-search-header__submit {
                width: 100%;
                padding: 14px 1rem;
                border-radius: 0;
                letter-spacing: 0.1em;
            }
            .digital-library--intl .library-doc-search-header__submit {
                width: 100%;
                border-radius: 0 0 8px 8px;
                border-left: none;
                border-top: 1.5px solid #cbd5e1;
                padding: 10px 1rem;
            }
        }
        .library-doc-hit {
            padding: 0 0.12em;
            margin: 0 -0.02em;
            background-color: rgba(255, 237, 153, 0.95);
            color: inherit;
            border-radius: 2px;
        }

        .library-doc-empty-filter {
            background: #fafbfc;
        }
        .library-doc-empty-filter p {
            font-size: 0.95rem;
            letter-spacing: 0.01em;
        }

        /* Pusat Informasi & Pengumuman — grid kartu portal */
        .pi-portal-grid {
            --pi-card-radius: 12px;
        }
        a.pi-portal-card-link {
            color: inherit;
            text-decoration: none;
            outline: none;
        }
        a.pi-portal-card-link:focus-visible .pi-portal-card {
            outline: 2px solid #38bdf8;
            outline-offset: 2px;
        }
        .pi-portal-card {
            overflow: hidden;
            border-radius: var(--pi-card-radius);
            background: #ffffff;
            border: 1px solid var(--card-border);
            box-shadow: var(--pi-card-shadow);
            transition: all 0.3s ease;
        }
        .pi-portal-card-link:hover .pi-portal-card {
            transform: translateY(-5px);
            border-color: #d1d5db;
            box-shadow:
                0 18px 36px rgba(15, 23, 42, 0.18),
                0 8px 16px rgba(15, 23, 42, 0.12);
        }
        .pi-portal-card__media {
            position: relative;
            aspect-ratio: 16 / 9;
            background: linear-gradient(155deg, #e2e8f0 0%, #f1f5f9 100%);
            overflow: hidden;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: box-shadow 0.25s ease;
        }
        .pi-portal-card__media::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            opacity: 0;
            background: linear-gradient(to top, rgba(15, 23, 42, 0.82) 0%, rgba(15, 23, 42, 0.35) 45%, transparent 72%);
            transition: opacity 0.32s ease;
        }
        .pi-portal-card-link:hover .pi-portal-card__media::after {
            opacity: 1;
        }
        .pi-portal-card__badge {
            position: absolute;
            top: 0.65rem;
            right: 0.65rem;
            z-index: 3;
            font-size: 0.625rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            padding: 0.45em 0.78em;
            border-radius: 8px;
            box-shadow: 0 1px 8px rgba(15, 23, 42, 0.12);
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: opacity 0.25s ease, transform 0.25s ease;
        }
        /* Berita & pengumuman — label merah di depan */
        .pi-portal-card__badge--berita,
        .pi-portal-card__badge--pengumuman {
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%) !important;
            border: 1px solid rgba(127, 29, 29, 0.5) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 10px rgba(185, 28, 28, 0.32);
        }
        /* Featured / berita & pengumuman utama — badge seperti stiker */
        .pi-portal-card__badge--utama {
            background-color: #d32f2f !important;
            border: 1px solid rgba(183, 28, 28, 0.95) !important;
            color: #ffffff !important;
            font-weight: 700;
            letter-spacing: 0.065em;
            padding: 5px 11px !important;
            border-radius: 4px !important;
            box-shadow:
                0 1px 2px rgba(0, 0, 0, 0.18),
                0 4px 12px rgba(211, 47, 47, 0.42),
                inset 0 1px 0 rgba(255, 255, 255, 0.22);
        }
        .pi-portal-card__badge--utama i {
            font-size: 0.62rem;
            opacity: 1;
            color: #ffffff;
        }
        .pi-portal-card--headline {
            border-color: rgba(211, 47, 47, 0.22);
        }
        .pi-portal-card__img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 12px;
            transition: transform 0.55s cubic-bezier(0.22, 1, 0.36, 1), filter 0.35s ease;
        }
        .pi-portal-card-link:hover .pi-portal-card__img {
            transform: scale(1.04);
            filter: blur(8px);
        }
        .pi-portal-card-link:hover .pi-portal-card__media {
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.14);
        }
        .pi-portal-card-link:hover .pi-portal-card__badge {
            opacity: 0;
            transform: translateY(-4px);
            transition: opacity 0.25s ease, transform 0.25s ease;
        }
        .pi-portal-card__hover-panel {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 4;
            padding: 1rem 1.1rem 1.15rem;
            pointer-events: none;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.32s ease, transform 0.35s ease;
        }
        .pi-portal-card-link:hover .pi-portal-card__hover-panel {
            opacity: 1;
            transform: translateY(0);
        }
        .pi-portal-card__hover-title {
            margin: 0;
            color: #ffffff;
            font-size: clamp(0.9rem, 0.82rem + 0.3vw, 1.05rem);
            font-weight: 800;
            line-height: 1.4;
            letter-spacing: 0.01em;
            text-shadow: 0 2px 14px rgba(0, 0, 0, 0.35);
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .pi-portal-card__hover-date {
            margin: 0.45rem 0 0;
            color: rgba(255, 255, 255, 0.92);
            font-size: 0.78rem;
            font-weight: 500;
            letter-spacing: 0.03em;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .pi-portal-card__hover-date i {
            font-size: 0.72rem;
        }
        .pi-portal-card-link:hover .pi-portal-card__body {
            opacity: 0.45;
            transition: opacity 0.28s ease;
        }
        @media (prefers-reduced-motion: reduce) {
            .pi-portal-card-link:hover .pi-portal-card__img {
                filter: none;
                transform: none;
            }
            .pi-portal-card-link:hover .pi-portal-card__hover-panel,
            .pi-portal-card-link:hover .pi-portal-card__media::after {
                opacity: 1;
                transform: none;
            }
            .pi-portal-card-link:hover .pi-portal-card__badge {
                opacity: 1;
                transform: none;
            }
            .pi-portal-card-link:hover .pi-portal-card__body {
                opacity: 1;
            }
        }
        .pi-portal-card__img--placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 2.25rem;
        }
        .pi-portal-card__body {
            padding: 1.15rem 1.2rem 1.15rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            gap: 0;
        }
        .pi-portal-card__meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.35rem 0.5rem;
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
            line-height: 1.35;
        }
        .pi-portal-card__meta-date {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .pi-portal-card__meta-date i {
            font-size: 0.72rem;
            opacity: 0.88;
        }
        .pi-portal-card__meta-sep {
            opacity: 0.45;
            user-select: none;
        }
        .pi-portal-card__meta-cat {
            font-weight: 700;
            color: #b91c1c;
            text-transform: capitalize;
        }
        .pi-portal-card__meta-date,
        .pi-portal-card__meta-date i {
            color: #991b1b;
        }
        .pi-portal-card__title {
            color: #111827;
            font-weight: 600;
            font-size: 1.05rem;
            line-height: 1.35;
            margin: 0 0 0.55rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .pi-portal-card__excerpt {
            font-size: 0.875rem;
            line-height: 1.6;
            color: #4b5563;
            margin: 0 0 0.85rem;
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .pi-portal-card__footer {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-top: auto;
            padding-top: 0.15rem;
        }
        .pi-portal-card__read-more {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--brand-navy);
            letter-spacing: 0.01em;
        }
        .pi-portal-card__read-more i {
            font-size: 0.72rem;
            transition: transform 0.22s ease;
        }
        .pi-portal-card-link:hover .pi-portal-card__read-more {
            color: var(--brand-navy-deep);
        }
        .pi-portal-card-link:hover .pi-portal-card__read-more i {
            transform: translateX(3px);
        }
        .pi-portal-empty {
            border-radius: var(--pi-card-radius, 0.75rem);
            background: #ffffff;
            border: 1px solid var(--card-border);
            box-shadow: var(--pi-card-shadow);
        }
        .pi-portal-empty__icon {
            font-size: 2.5rem;
            opacity: 0.85;
        }
        @media (max-width: 767.98px) {
            .container {
                padding-left: 16px;
                padding-right: 16px;
            }
            .site-header .container.site-header__inner {
                padding-left: 20px;
                padding-right: 20px;
            }
            .site-main {
                padding-top: 1.75rem;
                padding-bottom: 3rem;
            }
            .section-spacing {
                margin-bottom: 40px;
            }
            .site-header__title {
                font-size: 1.2rem;
            }
            .site-header__subtitle {
                font-size: 0.8rem;
            }
            .beranda-portal-strip__title,
            .digital-library--beranda-hero .digital-library__title--hero {
                font-size: clamp(1.55rem, 6.2vw, 1.9rem);
            }
            .beranda-section__title,
            .digital-library__title--hero {
                font-size: clamp(1.2rem, 4.8vw, 1.65rem);
                letter-spacing: 0.06em;
            }
            .site-header__search-wrap {
                margin-top: 0.45rem;
                margin-bottom: 0.45rem;
            }
            .site-header__nav-wrap {
                gap: 0.65rem;
            }
            .site-header__nav-toggle {
                border-radius: 8px;
                box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
                font-size: 1rem;
            }
            .site-header__nav-panel,
            .site-header__nav-collapse {
                margin-top: 0.6rem;
                border-radius: 14px;
                background: var(--header-nav-surface);
                background-color: #243f5f;
                border: 1px solid var(--header-nav-border);
                backdrop-filter: blur(var(--header-nav-glass-blur)) saturate(1.12);
                -webkit-backdrop-filter: blur(var(--header-nav-glass-blur)) saturate(1.12);
                overflow: hidden;
            }
            .site-header__nav-panel.is-open,
            .site-header__nav-collapse.show {
                display: block !important;
                height: auto !important;
                visibility: visible !important;
            }

            .site-header__nav-panel.is-open,
            .site-header__nav-collapse.show {
                position: fixed;
                left: 0;
                right: 0;
                top: var(--site-header-mobile-top, 120px);
                bottom: auto;
                z-index: 1080;
                max-height: var(--site-header-mobile-max-height, calc(100vh - 120px));
                overflow-y: auto;
                overflow-x: hidden;
                -webkit-overflow-scrolling: touch;
                margin-top: 0;
                box-sizing: border-box;
            }

            .sg-portal-page .site-header__nav-panel.is-open,
            .sg-portal-page .site-header__nav-collapse.show {
                border-radius: 0 0 14px 14px;
                border: 0;
                border-top: 1px solid var(--header-nav-border);
                padding: 0.65rem 1rem max(1rem, env(safe-area-inset-bottom));
                background: var(--header-nav-surface);
                background-color: #243f5f;
                backdrop-filter: blur(var(--header-nav-glass-blur)) saturate(1.12);
                -webkit-backdrop-filter: blur(var(--header-nav-glass-blur)) saturate(1.12);
                box-shadow: var(--header-nav-shadow-elevated);
            }

            .site-header__nav-panel.is-open .site-header__nav-row,
            .site-header__nav-panel.is-open .site-header__nav,
            .site-header__nav-collapse.show .site-header__nav-row,
            .site-header__nav-collapse.show .site-header__nav {
                display: flex !important;
                visibility: visible !important;
            }
            .site-header__nav-panel.is-open .site-header__nav-close-wrap,
            .site-header__nav-collapse.show .site-header__nav-close-wrap {
                padding: 0 0 0.6rem;
            }
            .site-header__nav-row {
                flex-direction: column;
                align-items: stretch;
                gap: 0;
                padding: 0.45rem;
            }
            .site-header__nav {
                flex-direction: column;
                gap: 0.25rem;
                width: 100%;
            }
            .site-header__nav li {
                width: 100%;
            }
            .site-header__nav a {
                width: 100%;
                padding: 0.72rem 0.9rem;
                border-radius: 10px;
                font-size: 0.78rem;
            }
            .site-header__nav a::after {
                display: none;
            }
            .site-header__nav a:hover {
                background: rgba(255, 255, 255, 0.12);
            }
            .site-header__nav a.is-active {
                background: rgba(245, 215, 142, 0.14);
                color: var(--header-nav-active);
                text-shadow: 0 0 12px var(--header-nav-active-glow);
            }
            .site-header__actions {
                width: 100%;
                padding-top: 0.35rem;
                justify-content: flex-end;
            }
            .site-header__actions-end {
                width: 100%;
                justify-content: flex-end;
            }
            .site-header__actions-end .btn,
            .site-header__actions-end form {
                width: auto;
            }
            .library-doc-search-shell,
            .library-doc-search-header--prominent,
            .library-doc-search-header__combo {
                width: 100%;
                max-width: 100%;
            }
            .digital-library__table-wrap {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .digital-library__table {
                min-width: 760px;
            }
            .digital-library__table thead th,
            .digital-library__table tbody td {
                padding-left: 0.55rem;
                padding-right: 0.55rem;
            }
            .digital-library__table thead th:first-child,
            .digital-library__table tbody td:first-child {
                width: 2rem !important;
                text-align: left !important;
                padding-left: 0.35rem;
                padding-right: 0.3rem;
            }
            .digital-library__table thead th:nth-child(2),
            .digital-library__table tbody td:nth-child(2) {
                padding-left: 0.35rem;
            }
            .digital-library__table thead th:nth-child(3),
            .digital-library__table tbody td:nth-child(3) {
                width: 5.25rem !important;
                white-space: nowrap;
                padding-left: 0.35rem;
                padding-right: 0.35rem;
            }
            .digital-library__table thead th:nth-child(4),
            .digital-library__table tbody td:nth-child(4) {
                width: 4.5rem !important;
                white-space: nowrap;
                padding-left: 0.35rem;
                padding-right: 0.35rem;
            }
            .beranda-exec-grid,
            .pi-portal-grid,
            .beranda-galeri-grid {
                grid-template-columns: minmax(0, 1fr);
            }
            .beranda-exec-grid > .col,
            .pi-portal-grid > [class*="col-"] {
                width: 100%;
            }
            .pi-portal-card__media,
            .beranda-galeri-item__frame,
            .gallery-activity-thumb-wrap {
                width: 100%;
                aspect-ratio: 16 / 9;
            }
            .pi-portal-card__img,
            .beranda-galeri-item__img,
            .gallery-activity-thumb,
            .news-cover {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .news-cover {
                aspect-ratio: 16 / 9;
            }
            .digital-library__doc-title {
                font-size: 13px;
                line-height: 1.4;
                gap: 0.28rem;
                align-items: center;
            }
            .digital-library__doc-icon {
                font-size: 0.95rem;
                min-width: 0.95rem;
            }
            .digital-library__doc-title .js-lib-doc-title-text {
                padding-left: 0.05rem;
                line-height: 1.4;
            }
        }
        @media (max-width: 768px) {
            .site-header__inner {
                padding-top: 0.25rem;
                padding-bottom: 0.3rem;
            }
            .site-header__logo {
                max-height: 32px;
                max-width: 22vw;
            }
            .site-header__title {
                font-size: 0.96rem;
                font-weight: 700;
                line-height: 1.1;
            }
            .site-header__title-full {
                display: none;
            }
            .site-header__title-mobile {
                display: inline;
            }
            .site-header__subtitle {
                display: block;
                margin-top: 0.12rem;
                font-size: 0.56rem;
                line-height: 1.2;
                font-weight: 500;
                color: rgba(255, 255, 255, 0.9);
                white-space: normal;
            }
            .site-header__topbar {
                display: flex;
                flex-wrap: nowrap;
                justify-content: space-between;
                align-items: center;
                gap: 0.2rem;
                min-height: 42px;
            }
            .site-header__brand-row {
                flex: 1 1 0;
                min-width: 0;
                flex-wrap: nowrap;
                gap: 0.35rem;
                align-items: center;
                margin-right: 0.3rem;
            }
            .site-header__titles {
                min-width: 0;
                margin-left: 0.2rem;
            }
            .site-header__title {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .site-header__search-wrap {
                display: none;
            }
            .site-header-doc-search {
                width: auto;
                max-width: none;
            }
            .site-header-doc-search__field {
                min-height: 32px;
                width: 32px;
                border-width: 1px;
                border-radius: 999px;
                padding: 0;
            }
            .site-header-doc-search__input {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                white-space: nowrap;
                border: 0;
            }
            .site-header-doc-search__submit {
                position: static;
                transform: none;
                width: 32px;
                height: 32px;
                font-size: 0.68rem;
                right: auto;
            }
            .site-header__nav-wrap {
                margin-top: 0;
                padding-top: 0;
                border-top: none;
                gap: 0;
            }
            .site-header__nav-toggle {
                min-height: 38px;
                min-width: 38px;
                width: auto;
                font-size: 12px;
                padding: 6px 10px;
                gap: 0;
                border-radius: 999px;
                box-shadow: 0 3px 12px rgba(0, 0, 0, 0.16);
            }
            .site-header__nav-toggle-label {
                display: none;
            }
            .site-header__hamburger {
                width: 1.15rem;
                height: 0.95rem;
                gap: 4px;
            }
            .site-header__nav a {
                min-height: 38px;
                padding-top: 0.45rem;
                padding-bottom: 0.45rem;
            }
        }
        /* Redesign khusus halaman dokumen (daftar/tabel; hero mengikuti beranda via .digital-library--beranda-hero) */
        .page-digital-library {
            background: linear-gradient(180deg, #eef4fc 0%, #f6f9fd 38%, #ffffff 100%);
        }
        .page-digital-library .digital-library.digital-library--beranda-hero.digital-library--intl {
            background: linear-gradient(180deg, #eef4fc 0%, #f4f8fd 45%, #ffffff 100%);
        }
        .page-digital-library .digital-library:not(.digital-library--beranda-hero) .digital-library__hero--intl {
            background: radial-gradient(120% 120% at 50% 0%, #eef4ff 0%, #e7f0fc 42%, #f5f8fc 100%);
            border: 1px solid #e4ebf5;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: clamp(42px, 6.5vw, 78px) clamp(16px, 3vw, 28px);
            margin-bottom: clamp(20px, 2vw, 34px);
        }
        .page-digital-library .digital-library:not(.digital-library--beranda-hero) .digital-library__hero-head {
            max-width: 60rem;
        }
        .page-digital-library .digital-library:not(.digital-library--beranda-hero) .digital-library__title--hero {
            font-size: clamp(2rem, 1.3rem + 2.2vw, 3.15rem);
            letter-spacing: 0.02em;
            line-height: 1.1;
            color: #0b3f74;
            text-transform: none;
            white-space: normal;
        }
        .page-digital-library .digital-library:not(.digital-library--beranda-hero) .digital-library__subtitle {
            max-width: 48rem;
            margin: 0.55rem auto 1rem;
            color: #526071;
            font-size: clamp(0.95rem, 0.86rem + 0.35vw, 1.08rem);
        }
        .page-digital-library .digital-library:not(.digital-library--beranda-hero) .library-doc-search-shell {
            width: min(100%, 700px);
            max-width: min(100%, 700px);
            margin-left: auto;
            margin-right: auto;
            margin-top: 0.15rem;
        }
        .page-digital-library .digital-library:not(.digital-library--beranda-hero):not(.digital-library--doc-center) .library-doc-search-header__combo {
            border-radius: 16px;
            background: #ffffff;
            border: 1px solid #d9e3f1;
            box-shadow:
                0 2px 8px rgba(37, 99, 235, 0.08),
                0 10px 24px rgba(37, 99, 235, 0.12);
            padding: 0.28rem;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }
        .page-digital-library .digital-library:not(.digital-library--beranda-hero):not(.digital-library--doc-center) .library-doc-search-header__combo:focus-within {
            border-color: #9fc4ef;
            box-shadow:
                0 2px 8px rgba(37, 99, 235, 0.08),
                0 10px 24px rgba(37, 99, 235, 0.12),
                0 0 0 3px rgba(26, 103, 181, 0.14);
        }
        .page-digital-library .digital-library:not(.digital-library--beranda-hero):not(.digital-library--doc-center) .library-doc-search-header__field {
            min-height: 56px;
        }
        .page-digital-library .digital-library:not(.digital-library--beranda-hero):not(.digital-library--doc-center) .library-doc-search-header__input {
            font-size: 1.02rem;
        }
        .page-digital-library .digital-library:not(.digital-library--beranda-hero):not(.digital-library--doc-center) .library-doc-search-header__icon {
            color: #5f7087;
        }
        .page-digital-library .digital-library:not(.digital-library--beranda-hero):not(.digital-library--doc-center) .library-doc-search-header__submit {
            min-width: 120px;
            min-height: 50px;
            border-radius: 999px;
            font-weight: 700;
            letter-spacing: 0.01em;
            background: linear-gradient(135deg, #0b3f74 0%, #1a67b5 100%);
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .page-digital-library .digital-library:not(.digital-library--beranda-hero):not(.digital-library--doc-center) .library-doc-search-header__submit:hover {
            transform: translateY(-4px);
        }
        .page-digital-library .library-doc-category-filter {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.9rem;
        }
        .page-digital-library .library-doc-category-filter__btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            text-align: left;
            border-radius: 16px;
            border: 1px solid #dbe7f4;
            background: #ffffff;
            color: #103a64;
            padding: 0.95rem 1rem;
            min-height: 88px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .page-digital-library .library-doc-category-filter__btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-color: #b8cde4;
        }
        .page-digital-library .library-doc-category-filter__btn.is-active {
            background: linear-gradient(135deg, #0f467d 0%, #1f67af 100%);
            color: #ffffff;
            border-color: transparent;
        }
        .page-digital-library .library-doc-category-filter__icon {
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ecf2fa;
            color: #0f4f8d;
            flex-shrink: 0;
        }
        .page-digital-library .library-doc-category-filter__btn.is-active .library-doc-category-filter__icon {
            background: rgba(255, 255, 255, 0.22);
            color: #ffffff;
        }
        .page-digital-library .library-doc-category-filter__text-wrap {
            display: flex;
            flex-direction: column;
            gap: 0.12rem;
            min-width: 0;
        }
        .page-digital-library .library-doc-category-filter__label {
            font-size: 0.9rem;
            font-weight: 700;
            line-height: 1.3;
        }
        .page-digital-library .library-doc-category-filter__count {
            font-size: 0.78rem;
            color: #64748b;
            line-height: 1.2;
        }
        .page-digital-library .library-doc-category-filter__btn.is-active .library-doc-category-filter__count {
            color: rgba(255, 255, 255, 0.88);
        }
        .page-digital-library .card.section-card {
            border-radius: 16px;
            border: 1px solid #e2eaf4;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .page-digital-library .digital-library__table-wrap {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: inset 0 0 0 1px #edf2f7;
            background: #ffffff;
        }
        .page-digital-library .digital-library__table {
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-top: -10px;
        }
        .page-digital-library .digital-library__table thead th {
            background: #f4f8fd;
            border-bottom-color: #dbe7f4;
            border-top: none;
            border-bottom: none;
            font-weight: 700;
            font-size: 0.9rem;
            color: #153f67;
        }
        .page-digital-library .digital-library__table tbody td {
            background: #ffffff;
            border-top: 1px solid #e7edf6;
            border-bottom: 1px solid #e7edf6;
            padding-top: 0.95rem;
            padding-bottom: 0.95rem;
            vertical-align: middle;
        }
        .page-digital-library .digital-library__table tbody td:first-child {
            border-left: 1px solid #e7edf6;
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }
        .page-digital-library .digital-library__table tbody td:last-child {
            border-right: 1px solid #e7edf6;
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }
        .page-digital-library .digital-library__table tbody tr {
            transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }
        .page-digital-library .digital-library__table tbody tr:hover {
            background: #f8fbff;
            transform: translateY(-4px);
        }
        .page-digital-library .digital-library__actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 0.45rem;
            white-space: nowrap;
        }
        .page-digital-library .digital-library__actions .btn {
            border-radius: 999px;
            min-height: 38px;
            padding-left: 0.9rem;
            padding-right: 0.9rem;
            width: auto;
            min-width: 82px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .page-digital-library .digital-library__actions .btn i {
            width: 1em;
            text-align: center;
            line-height: 1;
        }
        .page-digital-library .digital-library__actions .btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .page-digital-library .digital-library__actions .btn-primary {
            background: linear-gradient(135deg, #0f467d 0%, #1f67af 100%);
            border-color: transparent;
        }
        .page-digital-library .library-doc-pagination-wrap {
            display: flex;
            justify-content: center;
        }
        .page-digital-library .library-doc-pagination {
            list-style: none;
            padding: 0.45rem;
            margin: 0;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: #f5f8fc;
            border-radius: 999px;
            border: 1px solid #dbe7f4;
        }
        .page-digital-library .library-doc-pagination__item {
            margin: 0;
        }
        .page-digital-library .library-doc-pagination__btn {
            border: none;
            background: transparent;
            color: #174c7f;
            border-radius: 999px;
            min-width: 40px;
            height: 40px;
            font-weight: 600;
            padding: 0 0.8rem;
            transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease;
        }
        .page-digital-library .library-doc-pagination__btn:hover:not(:disabled) {
            background: #e5eef9;
            transform: translateY(-1px);
        }
        .page-digital-library .library-doc-pagination__btn.is-active {
            background: #0f467d;
            color: #ffffff;
        }
        .page-digital-library .library-doc-pagination__btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }
        .page-digital-library .site-footer {
            margin-top: clamp(2rem, 3vw, 4rem) !important;
        }
        .page-digital-library .site-footer__cta-band {
            background: #f6f9fc;
        }
        .page-digital-library .site-footer-card {
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        @media (max-width: 991.98px) {
            .page-digital-library .library-doc-category-filter {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 767.98px) {
            .page-digital-library .site-header__inner {
                padding-top: 0.55rem;
                padding-bottom: 0.55rem;
            }
            .page-digital-library .site-header__nav-row {
                flex-wrap: wrap;
            }
            .page-digital-library .library-doc-category-filter {
                grid-template-columns: 1fr;
            }
            .page-digital-library .digital-library:not(.digital-library--beranda-hero) .library-doc-search-header__combo {
                border-radius: 18px;
            }
            .page-digital-library .digital-library:not(.digital-library--beranda-hero) .library-doc-search-header__field {
                min-height: 52px;
            }
            .page-digital-library .digital-library:not(.digital-library--beranda-hero) .library-doc-search-header__submit {
                min-height: 46px;
                min-width: 96px;
            }
        }
    </style>
