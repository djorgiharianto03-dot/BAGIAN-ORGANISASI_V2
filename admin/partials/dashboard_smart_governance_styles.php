<?php
declare(strict_types=1);
?>
<style>
    :root {
        --sg-primary: #1D4ED8;
        --sg-secondary: #2563EB;
        --sg-dark: #0F172A;
        --sg-bg: #F1F5F9;
        --sg-success: #10B981;
        --sg-warning: #F59E0B;
        --sg-danger: #EF4444;
        --sg-card: rgba(255, 255, 255, 0.88);
        --sg-glass: rgba(255, 255, 255, 0.72);
        --sg-border: rgba(226, 232, 240, 0.95);
        --sg-radius: 20px;
        --sg-radius-lg: 24px;
        --sg-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
        --sg-shadow-hover: 0 24px 48px rgba(29, 78, 216, 0.14);
        --sg-ease: cubic-bezier(0.22, 1, 0.36, 1);
        --sg-sidebar-w: 272px;
        --sg-sidebar-collapsed: 84px;
        --sg-topbar-h: 68px;
    }

    [data-theme="dark"] {
        --sg-bg: #0b1220;
        --sg-dark: #f1f5f9;
        --sg-card: rgba(15, 23, 42, 0.85);
        --sg-glass: rgba(15, 23, 42, 0.72);
        --sg-border: rgba(51, 65, 85, 0.8);
        --sg-shadow: 0 4px 32px rgba(0, 0, 0, 0.35);
        --sg-shadow-hover: 0 24px 56px rgba(37, 99, 235, 0.2);
    }

    body.sg-dashboard {
        font-family: 'Inter', 'Poppins', system-ui, sans-serif;
        background: var(--sg-bg);
        color: var(--sg-dark);
        overflow-x: hidden;
        margin: 0;
    }

    body.sg-dashboard .adm-shell { display: none !important; }

    .sg-legacy-hidden { display: none !important; }

    .sg-app {
        min-height: 100vh;
        padding: 0.75rem 0.75rem 1.5rem;
        max-width: 1920px;
        margin: 0 auto;
    }

    @media (min-width: 992px) {
        .sg-app { padding: 1rem 1.25rem 2rem; }
    }

    /* Topbar */
    .sg-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-height: var(--sg-topbar-h);
        padding: 0.65rem 1rem 0.65rem 1.15rem;
        margin-bottom: 0.85rem;
        border-radius: var(--sg-radius);
        background: var(--sg-glass);
        backdrop-filter: blur(20px) saturate(1.25);
        -webkit-backdrop-filter: blur(20px) saturate(1.25);
        border: 1px solid var(--sg-border);
        box-shadow: var(--sg-shadow);
        position: sticky;
        top: 0.5rem;
        z-index: 1040;
    }

    .sg-topbar__left,
    .sg-topbar__right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-shrink: 0;
    }

    .sg-topbar__search {
        flex: 1;
        max-width: 420px;
        position: relative;
        margin: 0 0.5rem;
    }

    .sg-topbar__search i,
    .sg-topbar__search svg {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        color: #94a3b8;
        pointer-events: none;
    }

    .sg-topbar__search-input {
        width: 100%;
        border: 1px solid var(--sg-border);
        background: rgba(241, 245, 249, 0.6);
        border-radius: 14px;
        padding: 0.55rem 1rem 0.55rem 2.65rem;
        font-size: 0.875rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    [data-theme="dark"] .sg-topbar__search-input {
        background: rgba(30, 41, 59, 0.5);
        color: #e2e8f0;
    }

    .sg-topbar__search-input:focus {
        outline: none;
        border-color: var(--sg-secondary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .sg-breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.8125rem;
        color: #64748b;
        margin-left: 0.35rem;
    }

    .sg-breadcrumb__root { font-weight: 500; }
    .sg-breadcrumb__current { font-weight: 700; color: var(--sg-dark); }
    .sg-breadcrumb svg { width: 14px; height: 14px; opacity: 0.5; }

    .sg-icon-btn {
        width: 40px;
        height: 40px;
        border: 1px solid var(--sg-border);
        border-radius: 12px;
        background: var(--sg-card);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--sg-dark);
        transition: transform 0.25s var(--sg-ease), box-shadow 0.25s, background 0.25s;
        position: relative;
    }

    .sg-icon-btn:hover {
        transform: translateY(-2px) scale(1.03);
        box-shadow: 0 8px 20px rgba(29, 78, 216, 0.12);
    }

    .sg-icon-btn svg { width: 18px; height: 18px; }

    .sg-icon-btn--notify .sg-icon-btn__badge {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 8px;
        height: 8px;
        background: var(--sg-danger);
        border-radius: 50%;
        border: 2px solid #fff;
    }

    .sg-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: rgba(16, 185, 129, 0.12);
        color: #047857;
    }

    [data-theme="dark"] .sg-status-pill { color: #6ee7b7; }

    .sg-status-pill__dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--sg-success);
        animation: sg-pulse 2s ease infinite;
    }

    @keyframes sg-pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(0.9); }
    }

    .sg-clock {
        flex-direction: column;
        align-items: flex-end;
        line-height: 1.15;
        padding: 0 0.5rem;
    }

    .sg-clock__time {
        font-weight: 700;
        font-size: 0.95rem;
        letter-spacing: -0.02em;
        color: var(--sg-dark);
    }

    .sg-clock__date {
        font-size: 0.7rem;
        color: #64748b;
    }

    .sg-btn-ghost {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.85rem;
        border-radius: 12px;
        border: 1px solid var(--sg-border);
        background: var(--sg-card);
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--sg-dark);
        text-decoration: none;
        transition: transform 0.2s var(--sg-ease);
    }

    .sg-btn-ghost:hover { transform: translateY(-1px); color: var(--sg-dark); }

    .sg-profile-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        border: 1px solid var(--sg-border);
        background: var(--sg-card);
        border-radius: 14px;
        padding: 0.35rem 0.65rem 0.35rem 0.35rem;
    }

    .sg-profile-btn::after { margin-left: 0.15rem; }

    .sg-profile-btn__avatar {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--sg-primary), #60a5fa);
        color: #fff;
        font-weight: 700;
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sg-profile-btn__meta { text-align: left; line-height: 1.2; }
    .sg-profile-btn__name { font-size: 0.8125rem; font-weight: 700; display: block; max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sg-profile-btn__role { font-size: 0.7rem; color: #64748b; }

    .sg-dropdown { border-radius: 16px !important; padding: 0.35rem; }

    /* Body layout */
    .sg-body {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.85rem;
        align-items: start;
    }

    @media (min-width: 992px) {
        .sg-body {
            grid-template-columns: var(--sg-sidebar-w) 1fr;
        }
        .sg-app.is-sidebar-collapsed .sg-body {
            grid-template-columns: var(--sg-sidebar-collapsed) 1fr;
        }
    }

    /* Floating sidebar */
    .sg-sidebar {
        border-radius: var(--sg-radius-lg);
        background: var(--sg-glass);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--sg-border);
        box-shadow: var(--sg-shadow);
        padding: 1rem 0.75rem;
        position: sticky;
        top: calc(var(--sg-topbar-h) + 1.25rem);
        max-height: calc(100vh - var(--sg-topbar-h) - 2.5rem);
        overflow-y: auto;
        transition: width 0.35s var(--sg-ease), transform 0.35s var(--sg-ease);
    }

    @media (max-width: 991.98px) {
        .sg-sidebar {
            position: fixed;
            left: 0.75rem;
            top: calc(var(--sg-topbar-h) + 1rem);
            width: min(300px, calc(100vw - 1.5rem));
            z-index: 1050;
            transform: translateX(calc(-100% - 1rem));
            max-height: calc(100vh - var(--sg-topbar-h) - 1.5rem);
        }
        .sg-app.is-sidebar-open .sg-sidebar {
            transform: translateX(0);
        }
    }

    .sg-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        z-index: 1045;
        backdrop-filter: blur(4px);
    }

    .sg-sidebar__brand {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.25rem 0.5rem 1rem;
        border-bottom: 1px solid var(--sg-border);
        margin-bottom: 0.75rem;
    }

    .sg-sidebar__logo {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--sg-primary), #60a5fa);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        box-shadow: 0 8px 20px rgba(29, 78, 216, 0.35);
        flex-shrink: 0;
    }

    .sg-sidebar__logo svg { width: 22px; height: 22px; }

    .sg-sidebar__brand-text strong {
        display: block;
        font-size: 0.9rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .sg-sidebar__brand-text span {
        font-size: 0.7rem;
        color: #64748b;
        font-weight: 500;
    }

    .sg-app.is-sidebar-collapsed .sg-sidebar__brand-text,
    .sg-app.is-sidebar-collapsed .sg-nav-item span,
    .sg-app.is-sidebar-collapsed .sg-sidebar__section {
        display: none;
    }

    .sg-app.is-sidebar-collapsed .sg-sidebar {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    .sg-app.is-sidebar-collapsed .sg-nav-item {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
    }

    .sg-sidebar__section {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #94a3b8;
        padding: 0.85rem 0.65rem 0.35rem;
        margin: 0;
    }

    .sg-sidebar__nav { display: flex; flex-direction: column; gap: 0.15rem; }

    .sg-nav-item {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.6rem 0.75rem;
        border-radius: 14px;
        font-size: 0.875rem;
        font-weight: 500;
        color: #475569;
        text-decoration: none;
        transition: background 0.25s var(--sg-ease), color 0.25s, transform 0.2s, box-shadow 0.25s;
    }

    .sg-nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }

    .sg-nav-item:hover {
        background: rgba(37, 99, 235, 0.08);
        color: var(--sg-primary);
        transform: translateX(3px);
    }

    .sg-nav-item.is-active {
        background: linear-gradient(135deg, rgba(29, 78, 216, 0.14), rgba(37, 99, 235, 0.08));
        color: var(--sg-primary);
        font-weight: 600;
        box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.2), 0 8px 24px rgba(29, 78, 216, 0.15);
    }

    .sg-main {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    /* Smart overview */
    .sg-fade-in {
        animation: sgFadeIn 0.55s var(--sg-ease) both;
    }

    @keyframes sgFadeIn {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .sg-hero {
        border-radius: var(--sg-radius-lg);
        padding: 1.5rem 1.75rem;
        background: linear-gradient(135deg, #1e3a8a 0%, var(--sg-primary) 45%, #3b82f6 100%);
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(29, 78, 216, 0.35);
    }

    .sg-hero::after {
        content: '';
        position: absolute;
        right: -10%;
        top: -40%;
        width: 50%;
        height: 180%;
        background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 65%);
        pointer-events: none;
    }

    .sg-hero__eyebrow {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        opacity: 0.85;
        margin-bottom: 0.35rem;
    }

    .sg-hero__title {
        font-size: clamp(1.35rem, 2.5vw, 1.85rem);
        font-weight: 700;
        letter-spacing: -0.03em;
        margin: 0 0 0.35rem;
        position: relative;
        z-index: 1;
    }

    .sg-hero__sub {
        font-size: 0.9rem;
        opacity: 0.9;
        max-width: 52ch;
        margin: 0;
        position: relative;
        z-index: 1;
    }

    .sg-hero__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
        position: relative;
        z-index: 1;
    }

    .sg-hero__chip {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.25);
    }

    .sg-kpi-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 1rem;
    }

    @media (min-width: 576px) {
        .sg-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (min-width: 1200px) {
        .sg-kpi-grid { grid-template-columns: repeat(4, 1fr); }
    }

    .sg-kpi-card {
        border-radius: var(--sg-radius);
        background: var(--sg-card);
        border: 1px solid var(--sg-border);
        box-shadow: var(--sg-shadow);
        padding: 1.15rem 1.25rem;
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        transition: transform 0.3s var(--sg-ease), box-shadow 0.3s var(--sg-ease);
        animation: sgFadeIn 0.5s var(--sg-ease) both;
    }

    .sg-kpi-card:hover {
        transform: translateY(-4px) scale(1.01);
        box-shadow: var(--sg-shadow-hover);
    }

    .sg-kpi-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sg-kpi-card__icon svg { width: 22px; height: 22px; color: #fff; }

    .sg-kpi-card--blue .sg-kpi-card__icon { background: linear-gradient(135deg, #1d4ed8, #60a5fa); }
    .sg-kpi-card--violet .sg-kpi-card__icon { background: linear-gradient(135deg, #6d28d9, #a78bfa); }
    .sg-kpi-card--emerald .sg-kpi-card__icon { background: linear-gradient(135deg, #047857, #34d399); }
    .sg-kpi-card--amber .sg-kpi-card__icon { background: linear-gradient(135deg, #b45309, #fbbf24); }

    .sg-kpi-card__label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
        margin: 0 0 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .sg-kpi-card__value {
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-weight: 700;
        letter-spacing: -0.03em;
        line-height: 1.1;
        margin: 0;
        color: var(--sg-dark);
    }

    .sg-kpi-card__hint {
        font-size: 0.78rem;
        color: #94a3b8;
        margin: 0.35rem 0 0;
    }

    .sg-metrics-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    @media (min-width: 768px) {
        .sg-metrics-row { grid-template-columns: 1fr 1fr; }
    }

    @media (min-width: 1200px) {
        .sg-metrics-row--triple {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            align-items: stretch;
        }
    }

    .sg-glass-panel {
        border-radius: var(--sg-radius);
        background: var(--sg-card);
        border: 1px solid var(--sg-border);
        box-shadow: var(--sg-shadow);
        padding: 1.25rem;
        transition: box-shadow 0.3s var(--sg-ease);
    }

    .sg-glass-panel:hover { box-shadow: var(--sg-shadow-hover); }

    .sg-panel-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .sg-panel-head__title {
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
        letter-spacing: -0.02em;
    }

    .sg-panel-head__sub {
        font-size: 0.8125rem;
        color: #64748b;
        margin: 0.2rem 0 0;
    }

    .sg-badge {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.3rem 0.65rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.1);
        color: var(--sg-primary);
    }

    .sg-chart { min-height: 260px; }

    /* Metric cards — layout rapi */
    .sg-metric-card {
        display: flex;
        flex-direction: column;
        min-height: 100%;
        padding: 1.35rem 1.4rem !important;
    }

    .sg-metric-card__body {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        flex: 1;
    }

    @media (min-width: 992px) {
        .sg-metric-card__body:not(.sg-metric-card__body--center):not(.sg-metric-card__body--list) {
            flex-direction: row;
            align-items: center;
            gap: 1.5rem;
        }
        .sg-metric-card__body:not(.sg-metric-card__body--center):not(.sg-metric-card__body--list) .sg-gauge-wrap {
            flex: 0 0 132px;
        }
        .sg-metric-card__details {
            flex: 1;
            min-width: 0;
        }
    }

    .sg-metric-card__body--center {
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 0.5rem 0 0.25rem;
    }

    .sg-metric-card__body--list {
        gap: 0.15rem;
        padding-top: 0.25rem;
    }

    .sg-metric-card__desc {
        font-size: 0.8125rem;
        color: #64748b;
        margin: 0 0 0.75rem;
        line-height: 1.45;
    }

    .sg-metric-card__desc--center { margin-bottom: 0; }

    .sg-metric-card__footnote {
        font-size: 0.75rem;
        color: #94a3b8;
        margin: 0.5rem 0 0;
    }

    .sg-metric-card__total {
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: -0.03em;
        color: var(--sg-primary);
        line-height: 1;
    }

    .sg-metric-card--empty {
        align-items: center;
        justify-content: center;
        text-align: center;
        min-height: 220px;
    }

    .sg-badge--live {
        background: rgba(16, 185, 129, 0.12);
        color: #047857;
    }

    [data-theme="dark"] .sg-badge--live { color: #6ee7b7; }

    /* CSS gauge — tanpa label overlap */
    .sg-gauge-wrap {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-shrink: 0;
    }

    .sg-gauge {
        --sg-pct: 0;
        --sg-gauge-color: #1D4ED8;
        width: 132px;
        height: 132px;
        border-radius: 50%;
        background: conic-gradient(
            var(--sg-gauge-color) 0deg,
            var(--sg-gauge-color) calc(var(--sg-pct) * 3.6deg),
            #e2e8f0 calc(var(--sg-pct) * 3.6deg),
            #e2e8f0 360deg
        );
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        box-shadow: 0 8px 24px rgba(29, 78, 216, 0.12);
        transition: background 1s cubic-bezier(0.22, 1, 0.36, 1);
    }

    [data-theme="dark"] .sg-gauge {
        background: conic-gradient(
            var(--sg-gauge-color) 0deg,
            var(--sg-gauge-color) calc(var(--sg-pct) * 3.6deg),
            #334155 calc(var(--sg-pct) * 3.6deg),
            #334155 360deg
        );
    }

    .sg-gauge::before {
        content: '';
        position: absolute;
        inset: 11px;
        border-radius: 50%;
        background: var(--sg-card);
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.06);
    }

    .sg-gauge--emerald { --sg-gauge-color: #10B981; box-shadow: 0 8px 24px rgba(16, 185, 129, 0.15); }

    .sg-gauge__inner {
        position: relative;
        z-index: 1;
        text-align: center;
    }

    .sg-gauge__value {
        font-size: 2rem;
        font-weight: 700;
        letter-spacing: -0.04em;
        color: var(--sg-dark);
        line-height: 1;
    }

    .sg-gauge__unit {
        font-size: 1.1rem;
        font-weight: 600;
        color: #64748b;
        margin-left: 1px;
    }

    .sg-progress-bar__fill--success {
        background: linear-gradient(90deg, #10B981, #34d399) !important;
    }

    .sg-progress-item { margin-bottom: 0.85rem; }
    .sg-progress-item:last-child { margin-bottom: 0; }

    .sg-progress-item__head {
        display: flex;
        justify-content: space-between;
        font-size: 0.8125rem;
        margin-bottom: 0.35rem;
        font-weight: 600;
    }

    .sg-progress-bar {
        height: 8px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }

    [data-theme="dark"] .sg-progress-bar { background: #334155; }

    .sg-progress-bar__fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--sg-primary), #60a5fa);
        width: 0;
        transition: width 1.2s var(--sg-ease);
    }

    .sg-activity-live {
        max-height: 280px;
        overflow-y: auto;
        padding-right: 0.25rem;
    }

    .sg-activity-live__item {
        display: flex;
        gap: 0.75rem;
        padding: 0.65rem 0;
        border-bottom: 1px solid var(--sg-border);
        font-size: 0.8125rem;
        animation: sgFadeIn 0.4s ease both;
    }

    .sg-activity-live__item:last-child { border-bottom: 0; }

    .sg-activity-live__pulse {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--sg-success);
        margin-top: 0.35rem;
        flex-shrink: 0;
        animation: sg-pulse 2s ease infinite;
    }

    .sg-quick-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    @media (min-width: 768px) {
        .sg-quick-grid { grid-template-columns: repeat(4, 1fr); }
    }

    .sg-quick-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.5rem;
        padding: 1rem 0.75rem;
        border-radius: 16px;
        border: 1px solid var(--sg-border);
        background: var(--sg-card);
        text-decoration: none;
        color: var(--sg-dark);
        font-size: 0.78rem;
        font-weight: 600;
        transition: transform 0.25s var(--sg-ease), box-shadow 0.25s;
    }

    .sg-quick-btn:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: var(--sg-shadow-hover);
        color: var(--sg-primary);
    }

    .sg-quick-btn__icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(29,78,216,0.12), rgba(96,165,250,0.2));
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sg-quick-btn__icon svg { width: 20px; height: 20px; color: var(--sg-primary); }

  /* Map legacy panels inside sg-main */
    .sg-main .card,
    .sg-main .adm-panel,
    .sg-main .dash-section {
        border-radius: var(--sg-radius) !important;
    }

    .sg-main .alert { border-radius: 14px; }

    .sg-skeleton {
        background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
        background-size: 200% 100%;
        animation: sgShimmer 1.2s ease infinite;
        border-radius: 8px;
        min-height: 1rem;
    }

    @keyframes sgShimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    [data-theme="dark"] body.sg-dashboard { color: #e2e8f0; }
    [data-theme="dark"] .sg-hero { box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4); }
    [data-theme="dark"] .sg-kpi-card__value,
    [data-theme="dark"] .sg-breadcrumb__current,
    [data-theme="dark"] .sg-nav-item.is-active { color: #f1f5f9; }
    [data-theme="dark"] .sg-nav-item { color: #94a3b8; }
    [data-theme="dark"] .sg-icon-btn,
    [data-theme="dark"] .sg-profile-btn,
    [data-theme="dark"] .sg-btn-ghost { color: #e2e8f0; }

    .sg-nav-item.sg-search-hidden { display: none !important; }

    /* Navy floating sidebar */
    .sg-sidebar--navy {
        background: linear-gradient(165deg, rgba(15, 23, 42, 0.94) 0%, rgba(30, 41, 59, 0.88) 100%);
        backdrop-filter: blur(24px) saturate(1.3);
        -webkit-backdrop-filter: blur(24px) saturate(1.3);
        border: 1px solid rgba(148, 163, 184, 0.12);
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.35);
    }

    .sg-sidebar--navy .sg-sidebar__brand-text strong { color: #f8fafc; }
    .sg-sidebar--navy .sg-sidebar__brand-text span { color: #94a3b8; }
    .sg-sidebar--navy .sg-sidebar__section { color: #64748b; }
    .sg-sidebar--navy .sg-nav-item {
        color: #cbd5e1;
    }
    .sg-sidebar--navy .sg-nav-item:hover {
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
        transform: translateX(4px);
        box-shadow: none;
    }
    .sg-sidebar--navy .sg-nav-item.is-active {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.35), rgba(59, 130, 246, 0.15));
        color: #fff;
        box-shadow: 0 0 0 1px rgba(96, 165, 250, 0.35), 0 8px 24px rgba(37, 99, 235, 0.25);
    }

    .sg-sidebar--navy .sg-sidebar__logo {
        background: linear-gradient(135deg, #3b82f6, #60a5fa);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
    }

    /* Views: monitoring vs workspace */
    .sg-view[hidden] { display: none !important; }

    .sg-main { gap: 0; }

    .sg-workspace-head {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--sg-border);
    }

    .sg-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border: 1px solid var(--sg-border);
        background: var(--sg-card);
        border-radius: 12px;
        padding: 0.45rem 0.85rem;
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--sg-dark);
        cursor: pointer;
        transition: transform 0.2s var(--sg-ease);
    }

    .sg-back-btn:hover { transform: translateY(-1px); }

    .sg-workspace-head__title {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
        letter-spacing: -0.02em;
    }

    .sg-workspace-head__sub {
        font-size: 0.8125rem;
        color: #64748b;
        margin: 0.2rem 0 0;
    }

    .sg-workspace-body > .card,
    .sg-workspace-body > .dash-section,
    .sg-workspace-body > #sg-op-pengaturan {
        display: none !important;
    }

    .sg-workspace-body.sg-ws-mod-layanan #panel-konten-tabs { display: block !important; }
    .sg-workspace-body.sg-ws-mod-dokumen #panel-unggah-dokumen,
    .sg-workspace-body.sg-ws-mod-dokumen #panel-kelola-dokumen,
    .sg-workspace-body.sg-ws-mod-dokumen #panel-digital-library-stats { display: block !important; }
    .sg-workspace-body.sg-ws-mod-pegawai #panel-manajemen-staf { display: block !important; }
    .sg-workspace-body.sg-ws-mod-audit #panel-audit { display: block !important; }
    .sg-workspace-body.sg-ws-mod-pengaturan #sg-op-pengaturan { display: block !important; }

    /* Monitoring dashboard */
    .sg-monitor { display: flex; flex-direction: column; gap: 1.75rem; }

    .sg-monitor-hero {
        border-radius: var(--sg-radius-lg);
        padding: 1.75rem 2rem;
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #2563eb 100%);
        color: #fff;
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1.25rem;
        box-shadow: 0 24px 56px rgba(29, 78, 216, 0.28);
    }

    .sg-monitor-hero__eyebrow {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        opacity: 0.85;
        margin: 0 0 0.35rem;
    }

    .sg-monitor-hero__title {
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-weight: 700;
        margin: 0;
        letter-spacing: -0.03em;
    }

    .sg-monitor-hero__sub {
        margin: 0.5rem 0 0;
        opacity: 0.9;
        max-width: 56ch;
        font-size: 0.9rem;
    }

    .sg-monitor-hero__meta { display: flex; flex-wrap: wrap; gap: 0.5rem; }

    .sg-monitor-hero__chip {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .sg-monitor-kpis {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 1rem;
    }

    @media (min-width: 576px) { .sg-monitor-kpis { grid-template-columns: repeat(2, 1fr); } }
    @media (min-width: 1200px) {
        .sg-monitor-kpis { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
    }

    .sg-monitor-kpi {
        border-radius: var(--sg-radius);
        background: var(--sg-card);
        border: 1px solid var(--sg-border);
        box-shadow: var(--sg-shadow);
        padding: 1.15rem;
        display: flex;
        gap: 0.85rem;
        align-items: flex-start;
        transition: transform 0.25s var(--sg-ease), box-shadow 0.25s;
    }

    .sg-monitor-kpi:hover {
        transform: translateY(-3px);
        box-shadow: var(--sg-shadow-hover);
    }

    .sg-monitor-kpi__icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sg-monitor-kpi__icon svg { width: 20px; height: 20px; color: #fff; }

    .sg-monitor-kpi--blue .sg-monitor-kpi__icon { background: linear-gradient(135deg, #1d4ed8, #60a5fa); }
    .sg-monitor-kpi--indigo .sg-monitor-kpi__icon { background: linear-gradient(135deg, #4338ca, #818cf8); }
    .sg-monitor-kpi--violet .sg-monitor-kpi__icon { background: linear-gradient(135deg, #6d28d9, #a78bfa); }
    .sg-monitor-kpi--cyan .sg-monitor-kpi__icon { background: linear-gradient(135deg, #0891b2, #22d3ee); }
    .sg-monitor-kpi--emerald .sg-monitor-kpi__icon { background: linear-gradient(135deg, #047857, #34d399); }

    .sg-monitor-kpi__label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin: 0 0 0.2rem;
    }

    .sg-monitor-kpi__value {
        font-size: 1.75rem;
        font-weight: 700;
        letter-spacing: -0.03em;
        margin: 0;
        line-height: 1.1;
    }

    .sg-monitor-kpi__hint { font-size: 0.75rem; color: #94a3b8; margin: 0.25rem 0 0; }

    .sg-monitor-charts {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }

    @media (min-width: 992px) {
        .sg-monitor-charts {
            grid-template-columns: repeat(2, 1fr);
        }
        .sg-monitor-chart--wide { grid-column: 1 / -1; }
    }

    .sg-monitor-chart {
        border-radius: var(--sg-radius);
        background: var(--sg-card);
        border: 1px solid var(--sg-border);
        box-shadow: var(--sg-shadow);
        padding: 1.25rem 1.35rem;
    }

    .sg-monitor-chart__head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .sg-monitor-chart__title {
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
        letter-spacing: -0.02em;
    }

    .sg-monitor-chart__sub {
        font-size: 0.8125rem;
        color: #64748b;
        margin: 0.2rem 0 0;
    }

    .sg-chart-box { min-height: 280px; }
    .sg-chart-box--donut { min-height: 300px; }

    .sg-monitor-panels {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }

    @media (min-width: 992px) {
        .sg-monitor-panels { grid-template-columns: 1.2fr 1fr; }
    }

    .sg-monitor-panel {
        border-radius: var(--sg-radius);
        background: var(--sg-card);
        border: 1px solid var(--sg-border);
        box-shadow: var(--sg-shadow);
        padding: 1.25rem 1.35rem;
    }

    .sg-monitor-panel__head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .sg-monitor-panel__title { font-size: 1rem; font-weight: 700; margin: 0; }
    .sg-monitor-panel__sub { font-size: 0.8125rem; color: #64748b; margin: 0.2rem 0 0; }

    .sg-activity-stream {
        max-height: 320px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .sg-activity-stream__item {
        display: flex;
        gap: 0.75rem;
        padding: 0.7rem 0;
        border-bottom: 1px solid var(--sg-border);
        font-size: 0.8125rem;
    }

    .sg-activity-stream__item:last-child { border-bottom: 0; }

    .sg-activity-stream__dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--sg-success);
        margin-top: 0.35rem;
        flex-shrink: 0;
        animation: sg-pulse 2s ease infinite;
    }

    .sg-insight-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .sg-insight-list__item {
        display: flex;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border-radius: 14px;
        border: 1px solid var(--sg-border);
        background: rgba(241, 245, 249, 0.5);
    }

    [data-theme="dark"] .sg-insight-list__item {
        background: rgba(30, 41, 59, 0.5);
    }

    .sg-insight-list__item svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 2px; }
    .sg-insight-list__item--success svg { color: var(--sg-success); }
    .sg-insight-list__item--warning svg { color: var(--sg-warning); }
    .sg-insight-list__item--info svg { color: var(--sg-secondary); }

    .sg-insight-list__item p {
        margin: 0.25rem 0 0;
        font-size: 0.8125rem;
        color: #64748b;
        line-height: 1.45;
    }

    .sg-settings-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 1.15rem;
        border-radius: 16px;
        border: 1px solid var(--sg-border);
        background: var(--sg-card);
        text-decoration: none;
        color: var(--sg-dark);
        font-weight: 600;
        font-size: 0.875rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .sg-settings-link:hover {
        transform: translateY(-2px);
        box-shadow: var(--sg-shadow-hover);
        color: var(--sg-primary);
    }

    .sg-settings-link svg { width: 20px; height: 20px; color: var(--sg-primary); }

    .mode-publikasi .sg-monitor-kpis { grid-template-columns: repeat(2, 1fr); }

    /* Formula / rumus penjelasan indikator */
    .sg-formula-guide {
        margin-bottom: 1.25rem;
        border: 1px solid var(--sg-border);
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.06), rgba(99, 102, 241, 0.04));
        overflow: hidden;
    }

    .sg-formula-guide__toggle {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.85rem 1.15rem;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--sg-primary);
        list-style: none;
    }

    .sg-formula-guide__toggle::-webkit-details-marker { display: none; }

    .sg-formula-guide__toggle svg { width: 18px; height: 18px; flex-shrink: 0; }

    .sg-formula-guide__body {
        padding: 0 1.15rem 1rem;
        font-size: 0.8125rem;
        color: #64748b;
        border-top: 1px solid var(--sg-border);
    }

    .sg-formula-tag {
        display: inline-block;
        padding: 0.15rem 0.5rem;
        border-radius: 6px;
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        background: rgba(37, 99, 235, 0.12);
        color: var(--sg-primary);
        margin-right: 0.35rem;
    }

    .sg-formula-tag--chart {
        background: rgba(16, 185, 129, 0.12);
        color: var(--sg-success);
    }

    .sg-formula-details {
        margin-top: 0.65rem;
        border: 1px dashed var(--sg-border);
        border-radius: 10px;
        background: rgba(241, 245, 249, 0.45);
        font-size: 0.75rem;
    }

    [data-theme="dark"] .sg-formula-details {
        background: rgba(30, 41, 59, 0.45);
    }

    .sg-formula-details__toggle {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.65rem;
        cursor: pointer;
        color: var(--sg-primary);
        font-weight: 600;
        list-style: none;
    }

    .sg-formula-details__toggle::-webkit-details-marker { display: none; }

    .sg-formula-details__toggle svg { width: 14px; height: 14px; flex-shrink: 0; }

    .sg-formula-details__body {
        padding: 0 0.65rem 0.55rem;
        color: #64748b;
        line-height: 1.5;
    }

    .sg-formula-details__list {
        padding-left: 1.1rem;
        margin: 0;
    }

    .sg-formula-details__list li { margin-bottom: 0.25rem; }

    .sg-formula-details__list code {
        font-size: 0.7rem;
        padding: 0.1rem 0.3rem;
        border-radius: 4px;
        background: rgba(15, 23, 42, 0.06);
    }

    .sg-formula-details__note {
        margin: 0.4rem 0 0;
        padding-top: 0.4rem;
        border-top: 1px solid var(--sg-border);
        font-style: italic;
        font-size: 0.7rem;
        color: #94a3b8;
    }

    .sg-monitor-formula-banner {
        display: flex;
        gap: 1rem;
        padding: 1.15rem 1.25rem;
        margin-bottom: 1.25rem;
        border-radius: 16px;
        border: 1px solid var(--sg-border);
        background: var(--sg-card);
        box-shadow: var(--sg-shadow);
        grid-column: 1 / -1;
    }

    .sg-monitor-formula-banner .sg-monitor-kpi__icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 99, 235, 0.1);
        color: var(--sg-primary);
        flex-shrink: 0;
    }

    .sg-monitor-formula-banner .sg-monitor-kpi__label {
        font-weight: 700;
        font-size: 0.9375rem;
        margin: 0 0 0.15rem;
    }

    .sg-formula-breakdown-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 0.5rem 1rem;
        font-size: 0.8125rem;
        color: #475569;
    }

    .sg-formula-breakdown-total {
        grid-column: 1 / -1;
        font-weight: 600;
        color: var(--sg-primary);
        padding-top: 0.35rem;
        border-top: 1px dashed var(--sg-border);
    }

    .sg-monitor-chart .sg-formula-details,
    .sg-monitor-panel .sg-formula-details {
        margin-top: 0.75rem;
    }
</style>
