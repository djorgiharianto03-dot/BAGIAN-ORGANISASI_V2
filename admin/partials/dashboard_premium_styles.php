<?php
declare(strict_types=1);
?>
<style>
    :root {
        --adm-primary: #2563eb;
        --adm-primary-dark: #1d4ed8;
        --adm-bg: #f1f5f9;
        --adm-card: #ffffff;
        --adm-text: #0f172a;
        --adm-muted: #64748b;
        --adm-border: #e2e8f0;
        --adm-radius: 20px;
        --adm-radius-sm: 14px;
        --adm-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
        --adm-shadow-hover: 0 20px 48px rgba(37, 99, 235, 0.12);
        --adm-glass: rgba(255, 255, 255, 0.82);
        --adm-nav-h: 64px;
        --adm-ease: cubic-bezier(0.22, 1, 0.36, 1);
    }

    body.dash-premium {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        background: var(--adm-bg);
        color: var(--adm-text);
        min-height: 100vh;
        -webkit-font-smoothing: antialiased;
    }

    /* —— Navbar —— */
    .adm-navbar {
        position: sticky;
        top: 0;
        z-index: 1030;
        min-height: var(--adm-nav-h);
        background: var(--adm-glass);
        backdrop-filter: blur(16px) saturate(1.2);
        -webkit-backdrop-filter: blur(16px) saturate(1.2);
        border-bottom: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 1px 0 rgba(255, 255, 255, 0.8) inset, 0 4px 20px rgba(15, 23, 42, 0.04);
    }
    .adm-navbar__brand {
        font-weight: 700;
        font-size: 1.05rem;
        letter-spacing: -0.02em;
        color: var(--adm-text) !important;
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
    }
    .adm-navbar__brand-icon {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--adm-primary) 0%, #60a5fa 100%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
    }
    .adm-navbar__brand-icon svg { width: 18px; height: 18px; }
    .adm-btn-ghost {
        border: 1px solid var(--adm-border);
        background: #fff;
        color: var(--adm-text);
        font-weight: 600;
        font-size: 0.8125rem;
        border-radius: 999px;
        padding: 0.4rem 1rem;
        transition: background 0.25s var(--adm-ease), border-color 0.25s ease, transform 0.25s var(--adm-ease);
    }
    .adm-btn-ghost:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        transform: translateY(-1px);
        color: var(--adm-text);
    }
    .adm-btn-primary-soft {
        background: linear-gradient(135deg, var(--adm-primary) 0%, #3b82f6 100%);
        border: none;
        color: #fff;
        font-weight: 600;
        font-size: 0.8125rem;
        border-radius: 999px;
        padding: 0.4rem 1rem;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
        transition: transform 0.25s var(--adm-ease), box-shadow 0.25s ease;
    }
    .adm-btn-primary-soft:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 8px 22px rgba(37, 99, 235, 0.4);
    }
    .adm-user-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.65rem 0.25rem 0.25rem;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid var(--adm-border);
    }
    .adm-user-pill__avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e0e7ff, #dbeafe);
        color: var(--adm-primary);
        font-weight: 700;
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* —— Layout —— */
    .adm-shell {
        display: grid;
        grid-template-columns: minmax(0, 260px) minmax(0, 1fr);
        gap: 1.5rem;
        padding: 1.5rem 0 3rem;
        align-items: start;
    }
    .adm-shell > .alert {
        grid-column: 1 / -1;
    }
    .adm-main {
        min-width: 0;
    }
    @media (max-width: 991.98px) {
        .adm-shell { grid-template-columns: 1fr; }
    }

    /* —— Sidebar —— */
    .dash-sidebar.adm-sidebar {
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: var(--adm-radius);
        background: var(--adm-card);
        box-shadow: var(--adm-shadow);
        overflow: hidden;
    }
    .dash-sidebar.adm-sidebar .card-body { padding: 1.1rem !important; }
    .dash-sidebar__group-title {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--adm-muted);
        margin: 0 0 0.5rem;
        padding-left: 0.35rem;
    }
    .dash-sidebar .list-group-item {
        border: 0;
        border-radius: 12px !important;
        font-size: 0.875rem;
        font-weight: 500;
        color: #475569;
        padding: 0.55rem 0.75rem;
        margin-bottom: 2px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background 0.22s ease, color 0.22s ease, transform 0.22s var(--adm-ease);
    }
    .dash-sidebar .list-group-item i,
    .dash-sidebar .list-group-item [data-lucide] {
        width: 18px;
        height: 18px;
        opacity: 0.75;
        flex-shrink: 0;
    }
    .dash-sidebar .list-group-item:hover {
        background: #eff6ff;
        color: var(--adm-primary);
        transform: translateX(2px);
    }
    .dash-sidebar .list-group-item.is-active {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: var(--adm-primary-dark);
        font-weight: 600;
        box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.12);
    }

    /* —— Welcome —— */
    .adm-welcome {
        border-radius: var(--adm-radius);
        padding: clamp(1.35rem, 2vw, 1.85rem);
        background: linear-gradient(135deg, #1e40af 0%, var(--adm-primary) 42%, #60a5fa 100%);
        color: #fff;
        box-shadow: 0 16px 40px rgba(37, 99, 235, 0.28);
        position: relative;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .adm-welcome::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 85% 20%, rgba(255,255,255,0.18), transparent 45%);
        pointer-events: none;
    }
    .adm-welcome__title {
        font-size: clamp(1.35rem, 2.5vw, 1.75rem);
        font-weight: 700;
        letter-spacing: -0.03em;
        margin: 0 0 0.35rem;
        position: relative;
    }
    .adm-welcome__sub {
        margin: 0;
        opacity: 0.92;
        font-size: 0.9375rem;
        max-width: 36rem;
        position: relative;
    }
    .adm-welcome__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
        position: relative;
    }
    .adm-welcome__chip {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.22);
        backdrop-filter: blur(8px);
    }

    /* —— Stat cards (Stripe / Linear / Vercel enterprise) —— */
    .adm-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 240px), 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .adm-stat-card {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        min-height: 148px;
        padding: 1.35rem 1.4rem 1.3rem;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.65);
        overflow: hidden;
        isolation: isolate;
        box-shadow:
            0 1px 2px rgba(15, 23, 42, 0.04),
            0 4px 20px rgba(15, 23, 42, 0.06);
        transition:
            transform 0.32s cubic-bezier(0.22, 1, 0.36, 1),
            box-shadow 0.32s cubic-bezier(0.22, 1, 0.36, 1),
            border-color 0.28s ease;
    }
    .adm-stat-card__glow {
        position: absolute;
        top: -40%;
        right: -20%;
        width: 72%;
        height: 120%;
        border-radius: 50%;
        pointer-events: none;
        opacity: 0.55;
        filter: blur(28px);
        transition: opacity 0.32s ease, transform 0.32s var(--adm-ease);
    }
    .adm-stat-card:hover {
        transform: translateY(-6px);
        border-color: rgba(255, 255, 255, 0.9);
        box-shadow:
            0 2px 4px rgba(15, 23, 42, 0.04),
            0 12px 32px rgba(15, 23, 42, 0.1),
            0 24px 48px rgba(37, 99, 235, 0.08);
    }
    .adm-stat-card:hover .adm-stat-card__glow {
        opacity: 0.85;
        transform: scale(1.08);
    }
    .adm-stat-card:hover .adm-stat-card__icon-wrap {
        transform: scale(1.06) rotate(-2deg);
    }
    .adm-stat-card--blue {
        background: linear-gradient(145deg, #ffffff 0%, #f8fbff 42%, #eef4ff 100%);
    }
    .adm-stat-card--blue .adm-stat-card__glow {
        background: radial-gradient(circle, rgba(37, 99, 235, 0.45) 0%, transparent 70%);
    }
    .adm-stat-card--violet {
        background: linear-gradient(145deg, #ffffff 0%, #faf8ff 42%, #f3efff 100%);
    }
    .adm-stat-card--violet .adm-stat-card__glow {
        background: radial-gradient(circle, rgba(124, 58, 237, 0.4) 0%, transparent 70%);
    }
    .adm-stat-card--emerald {
        background: linear-gradient(145deg, #ffffff 0%, #f7fdfb 42%, #ecfdf5 100%);
    }
    .adm-stat-card--emerald .adm-stat-card__glow {
        background: radial-gradient(circle, rgba(5, 150, 105, 0.38) 0%, transparent 70%);
    }
    .adm-stat-card--amber {
        background: linear-gradient(145deg, #ffffff 0%, #fffdf8 42%, #fff7ed 100%);
    }
    .adm-stat-card--amber .adm-stat-card__glow {
        background: radial-gradient(circle, rgba(217, 119, 6, 0.38) 0%, transparent 70%);
    }
    .adm-stat-card__icon-wrap {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255, 255, 255, 0.75);
        box-shadow:
            0 4px 14px rgba(15, 23, 42, 0.08),
            inset 0 1px 0 rgba(255, 255, 255, 0.85);
        transition: transform 0.32s var(--adm-ease);
        z-index: 2;
    }
    .adm-stat-card__icon-wrap svg {
        width: 28px;
        height: 28px;
        stroke-width: 1.75;
    }
    .adm-stat-card--blue .adm-stat-card__icon-wrap {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.95) 0%, rgba(219, 234, 254, 0.9) 100%);
        color: #2563eb;
    }
    .adm-stat-card--violet .adm-stat-card__icon-wrap {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.95) 0%, rgba(237, 233, 254, 0.9) 100%);
        color: #7c3aed;
    }
    .adm-stat-card--emerald .adm-stat-card__icon-wrap {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.95) 0%, rgba(209, 250, 229, 0.9) 100%);
        color: #059669;
    }
    .adm-stat-card--amber .adm-stat-card__icon-wrap {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.95) 0%, rgba(254, 243, 199, 0.9) 100%);
        color: #d97706;
    }
    .adm-stat-card__body {
        position: relative;
        z-index: 1;
        padding-right: 3.5rem;
        max-width: 100%;
    }
    .adm-stat-card__label {
        margin: 0 0 0.4rem;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #64748b;
        line-height: 1.35;
    }
    .adm-stat-card__value {
        margin: 0;
        font-size: clamp(1.85rem, 2.8vw, 2.35rem);
        font-weight: 800;
        letter-spacing: -0.045em;
        line-height: 1.05;
        color: #0f172a;
        font-variant-numeric: tabular-nums;
    }
    .adm-stat-card__hint {
        margin: 0.5rem 0 0;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #94a3b8;
        line-height: 1.4;
    }

    /* —— Chart & panels —— */
    .adm-panel-grid {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 1199.98px) {
        .adm-panel-grid { grid-template-columns: 1fr; }
    }
    .adm-panel {
        border-radius: var(--adm-radius);
        background: var(--adm-card);
        border: 1px solid var(--adm-border);
        box-shadow: var(--adm-shadow);
        padding: 1.25rem 1.35rem;
    }
    .adm-panel__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .adm-panel__title {
        font-size: 1rem;
        font-weight: 700;
        margin: 0 0 0.2rem;
        letter-spacing: -0.02em;
    }
    .adm-panel__desc {
        font-size: 0.8125rem;
        color: var(--adm-muted);
        margin: 0;
    }
    .adm-panel--chart {
        padding: clamp(1.35rem, 2vw, 1.85rem) clamp(1.35rem, 2.2vw, 2rem);
    }
    .adm-chart-card {
        background: #ffffff;
        box-shadow: 0 4px 28px rgba(15, 23, 42, 0.07);
    }
    .adm-chart-card__head {
        margin-bottom: 1.25rem;
        align-items: center;
    }
    .adm-chart-card__title {
        font-size: 1.0625rem;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.025em;
    }
    .adm-chart-card__subtitle {
        color: #64748b;
        font-size: 0.8125rem;
    }
    .adm-chart-card__badge {
        flex-shrink: 0;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #2563eb;
        background: #eff6ff;
        border: 1px solid #dbeafe;
        border-radius: 999px;
        padding: 0.3rem 0.65rem;
    }
    .adm-chart-card__body {
        margin: 0 -0.25rem;
    }
    .adm-chart {
        min-height: 260px;
        width: 100%;
    }
    .adm-chart-card--empty .adm-chart-card__empty-text {
        font-size: 0.875rem;
        color: #64748b;
        padding: 0.5rem 0 0.25rem;
    }

    /* ApexCharts — override global SaaS theme */
    .dash-premium .apexcharts-canvas {
        font-family: Inter, system-ui, sans-serif !important;
    }
    .dash-premium .apexcharts-gridline {
        stroke: #f1f5f9 !important;
    }
    .dash-premium .apexcharts-xaxis line,
    .dash-premium .apexcharts-yaxis line {
        stroke: transparent !important;
    }
    .dash-premium .apexcharts-legend-text {
        color: #64748b !important;
        font-weight: 500 !important;
        font-size: 12px !important;
    }
    .dash-premium .apexcharts-legend-marker {
        border-radius: 4px !important;
    }
    .dash-premium .apexcharts-tooltip {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    .dash-premium .apexcharts-tooltip-title,
    .dash-premium .apexcharts-tooltip-series-group {
        display: none !important;
    }
    .adm-apex-tooltip {
        padding: 12px 14px;
        min-width: 160px;
        background: rgba(255, 255, 255, 0.88);
        backdrop-filter: blur(14px) saturate(1.35);
        -webkit-backdrop-filter: blur(14px) saturate(1.35);
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 14px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12), 0 0 0 1px rgba(255, 255, 255, 0.6) inset;
        font-family: Inter, system-ui, sans-serif;
    }
    .adm-apex-tooltip__label {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 6px;
        letter-spacing: 0.02em;
    }
    .adm-apex-tooltip__row {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .adm-apex-tooltip__dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb, #60a5fa);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        flex-shrink: 0;
    }
    .adm-apex-tooltip__series {
        font-size: 12px;
        font-weight: 500;
        color: #475569;
        flex: 1;
    }
    .adm-apex-tooltip__value {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.02em;
    }

    /* —— Activity —— */
    .adm-activity-panel {
        margin-top: 0.5rem;
    }
    .adm-activity-list {
        list-style: none;
        margin: 0;
        padding: 0.25rem 0 0;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        gap: 0.65rem;
        min-height: 120px;
        max-height: 320px;
        overflow-y: auto;
        overflow-anchor: none;
        scroll-behavior: smooth;
    }
    .adm-activity-item--latest .adm-activity-item__dot {
        background: #059669;
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.2);
    }
    .adm-activity-item {
        display: flex;
        gap: 0.75rem;
        padding: 0.75rem;
        border-radius: var(--adm-radius-sm);
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        transition: background 0.2s ease, border-color 0.2s ease;
    }
    .adm-activity-item:hover {
        background: #eff6ff;
        border-color: #dbeafe;
    }
    .adm-activity-item__dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--adm-primary);
        margin-top: 0.45rem;
        flex-shrink: 0;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    .adm-activity-item__text {
        font-size: 0.8125rem;
        line-height: 1.45;
        color: #334155;
        margin: 0;
    }
    .adm-activity-item__meta {
        font-size: 0.7rem;
        color: var(--adm-muted);
        margin-top: 0.2rem;
    }

    /* —— Legacy sections upgrade —— */
    .dash-premium .card.dash-section,
    .dash-premium .card.border-0.shadow-sm {
        border: 1px solid var(--adm-border) !important;
        border-radius: var(--adm-radius) !important;
        box-shadow: var(--adm-shadow) !important;
        background: var(--adm-card);
    }
    .dash-premium .section-title {
        font-size: 1.125rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--adm-text);
    }

    /* —— Quick access (luxury cards) —— */
    .adm-quick-access {
        margin-bottom: 1.75rem;
        padding: 1.35rem 1.35rem 1.5rem;
        border-radius: 20px;
        border: 1px solid var(--adm-border);
        background:
            linear-gradient(165deg, rgba(255, 255, 255, 0.92) 0%, rgba(248, 250, 252, 0.88) 100%),
            var(--adm-card);
        box-shadow: var(--adm-shadow);
    }
    .adm-quick-access__head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
    }
    .adm-quick-access__title {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--adm-text);
    }
    .adm-quick-access__subtitle {
        margin: 0.25rem 0 0;
        font-size: 0.8125rem;
        color: var(--adm-muted);
    }
    .adm-quick-access__count {
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--adm-primary);
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        background: #eff6ff;
        border: 1px solid #dbeafe;
        white-space: nowrap;
    }
    .adm-quick-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 220px), 1fr));
        gap: 0.85rem;
    }
    .adm-quick-card {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        padding: 1rem 1rem 1rem 1.05rem;
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.7);
        text-decoration: none !important;
        color: inherit !important;
        overflow: hidden;
        isolation: isolate;
        min-height: 96px;
        box-shadow:
            0 1px 2px rgba(15, 23, 42, 0.04),
            0 4px 16px rgba(15, 23, 42, 0.05);
        transition:
            transform 0.3s cubic-bezier(0.22, 1, 0.36, 1),
            box-shadow 0.3s cubic-bezier(0.22, 1, 0.36, 1),
            border-color 0.25s ease;
    }
    .adm-quick-card:hover,
    .adm-quick-card:focus-visible {
        transform: translateY(-5px);
        border-color: rgba(255, 255, 255, 0.95);
        box-shadow:
            0 2px 4px rgba(15, 23, 42, 0.05),
            0 12px 28px rgba(15, 23, 42, 0.1);
        color: inherit !important;
        text-decoration: none !important;
    }
    .adm-quick-card:focus-visible {
        outline: 2px solid var(--adm-primary);
        outline-offset: 2px;
    }
    .adm-quick-card__glow {
        position: absolute;
        top: -50%;
        right: -25%;
        width: 70%;
        height: 130%;
        border-radius: 50%;
        pointer-events: none;
        opacity: 0.45;
        filter: blur(24px);
        transition: opacity 0.3s ease, transform 0.3s var(--adm-ease);
    }
    .adm-quick-card:hover .adm-quick-card__glow {
        opacity: 0.75;
        transform: scale(1.06);
    }
    .adm-quick-card__icon {
        position: relative;
        z-index: 1;
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.75);
        border: 1px solid rgba(255, 255, 255, 0.9);
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
        transition: transform 0.3s var(--adm-ease);
    }
    .adm-quick-card:hover .adm-quick-card__icon {
        transform: scale(1.05);
    }
    .adm-quick-card__icon svg {
        width: 20px;
        height: 20px;
        stroke-width: 2px;
    }
    .adm-quick-card__content {
        position: relative;
        z-index: 1;
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
        padding-right: 1.25rem;
    }
    .adm-quick-card__label {
        font-size: 0.875rem;
        font-weight: 600;
        letter-spacing: -0.02em;
        color: #0f172a;
        line-height: 1.3;
    }
    .adm-quick-card__desc {
        font-size: 0.75rem;
        line-height: 1.4;
        color: #64748b;
    }
    .adm-quick-card__arrow {
        position: absolute;
        top: 0.85rem;
        right: 0.85rem;
        z-index: 1;
        width: 26px;
        height: 26px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.55);
        border: 1px solid rgba(255, 255, 255, 0.8);
        opacity: 0.65;
        transition: opacity 0.25s ease, transform 0.25s var(--adm-ease), background 0.25s ease;
    }
    .adm-quick-card__arrow svg {
        width: 14px;
        height: 14px;
    }
    .adm-quick-card:hover .adm-quick-card__arrow {
        opacity: 1;
        transform: translate(2px, -2px);
        background: rgba(255, 255, 255, 0.9);
    }
    .adm-quick-card--blue {
        background: linear-gradient(145deg, #ffffff 0%, #f8fbff 50%, #eef4ff 100%);
    }
    .adm-quick-card--blue .adm-quick-card__glow { background: radial-gradient(circle, rgba(37, 99, 235, 0.35) 0%, transparent 70%); }
    .adm-quick-card--blue .adm-quick-card__icon { color: #2563eb; background: linear-gradient(135deg, #eff6ff, #dbeafe); }
    .adm-quick-card--violet {
        background: linear-gradient(145deg, #ffffff 0%, #faf8ff 50%, #f3efff 100%);
    }
    .adm-quick-card--violet .adm-quick-card__glow { background: radial-gradient(circle, rgba(124, 58, 237, 0.32) 0%, transparent 70%); }
    .adm-quick-card--violet .adm-quick-card__icon { color: #7c3aed; background: linear-gradient(135deg, #f5f3ff, #ede9fe); }
    .adm-quick-card--rose {
        background: linear-gradient(145deg, #ffffff 0%, #fff8fb 50%, #fff1f2 100%);
    }
    .adm-quick-card--rose .adm-quick-card__glow { background: radial-gradient(circle, rgba(225, 29, 72, 0.28) 0%, transparent 70%); }
    .adm-quick-card--rose .adm-quick-card__icon { color: #e11d48; background: linear-gradient(135deg, #fff1f2, #ffe4e6); }
    .adm-quick-card--cyan {
        background: linear-gradient(145deg, #ffffff 0%, #f6fcfd 50%, #ecfeff 100%);
    }
    .adm-quick-card--cyan .adm-quick-card__glow { background: radial-gradient(circle, rgba(8, 145, 178, 0.3) 0%, transparent 70%); }
    .adm-quick-card--cyan .adm-quick-card__icon { color: #0891b2; background: linear-gradient(135deg, #ecfeff, #cffafe); }
    .adm-quick-card--indigo {
        background: linear-gradient(145deg, #ffffff 0%, #f8f9ff 50%, #eef2ff 100%);
    }
    .adm-quick-card--indigo .adm-quick-card__glow { background: radial-gradient(circle, rgba(79, 70, 229, 0.32) 0%, transparent 70%); }
    .adm-quick-card--indigo .adm-quick-card__icon { color: #4f46e5; background: linear-gradient(135deg, #eef2ff, #e0e7ff); }
    .adm-quick-card--emerald {
        background: linear-gradient(145deg, #ffffff 0%, #f7fdfb 50%, #ecfdf5 100%);
    }
    .adm-quick-card--emerald .adm-quick-card__glow { background: radial-gradient(circle, rgba(5, 150, 105, 0.3) 0%, transparent 70%); }
    .adm-quick-card--emerald .adm-quick-card__icon { color: #059669; background: linear-gradient(135deg, #ecfdf5, #d1fae5); }
    .adm-quick-card--slate {
        background: linear-gradient(145deg, #ffffff 0%, #f8fafc 50%, #f1f5f9 100%);
    }
    .adm-quick-card--slate .adm-quick-card__glow { background: radial-gradient(circle, rgba(71, 85, 105, 0.25) 0%, transparent 70%); }
    .adm-quick-card--slate .adm-quick-card__icon { color: #475569; background: linear-gradient(135deg, #f8fafc, #e2e8f0); }
    .adm-quick-card--amber {
        background: linear-gradient(145deg, #ffffff 0%, #fffdf8 50%, #fff7ed 100%);
    }
    .adm-quick-card--amber .adm-quick-card__glow { background: radial-gradient(circle, rgba(217, 119, 6, 0.3) 0%, transparent 70%); }
    .adm-quick-card--amber .adm-quick-card__icon { color: #d97706; background: linear-gradient(135deg, #fff7ed, #ffedd5); }

    .dash-premium .feature-card {
        border-radius: var(--adm-radius-sm);
        border: 1px solid var(--adm-border);
        background: var(--adm-card);
        box-shadow: var(--adm-shadow);
        padding: 1.15rem;
        transition: transform 0.28s var(--adm-ease), box-shadow 0.28s var(--adm-ease), border-color 0.28s ease;
    }
    .dash-premium .feature-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--adm-shadow-hover);
        border-color: #bfdbfe;
        color: inherit;
    }
    .dash-premium .feature-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        color: var(--adm-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        margin-bottom: 0.65rem;
    }
    .dash-premium .gallery-wrap {
        border-radius: var(--adm-radius);
        border: 1px solid var(--adm-border);
        box-shadow: var(--adm-shadow);
    }
    .dash-premium .btn-primary {
        background: linear-gradient(135deg, var(--adm-primary) 0%, #3b82f6 100%);
        border: none;
        border-radius: 10px;
        font-weight: 600;
    }
    .dash-premium .btn-primary:hover {
        background: linear-gradient(135deg, var(--adm-primary-dark) 0%, var(--adm-primary) 100%);
    }
    .dash-premium .alert {
        border-radius: var(--adm-radius-sm);
        border: 0;
    }
    .dash-premium .table > thead {
        background: #f8fafc;
    }
    .dash-premium .table > thead th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--adm-muted);
        font-weight: 600;
        border-bottom: 1px solid var(--adm-border);
    }
    .dash-premium .admin-tabs .nav-link.active {
        color: var(--adm-primary);
        background: #eff6ff;
        border-color: #bfdbfe;
        border-radius: 10px;
    }
    .dash-premium .footer-saran {
        border-radius: var(--adm-radius) var(--adm-radius) 0 0;
        margin-top: 2rem;
    }

    @media (prefers-reduced-motion: reduce) {
        .adm-stat-card:hover,
        .adm-stat-card:hover .adm-stat-card__icon-wrap,
        .adm-stat-card:hover .adm-stat-card__glow,
        .adm-quick-card:hover,
        .adm-quick-card:hover .adm-quick-card__icon,
        .adm-quick-card:hover .adm-quick-card__glow,
        .adm-quick-card:hover .adm-quick-card__arrow,
        .feature-card:hover,
        .dash-sidebar .list-group-item:hover {
            transform: none;
        }
    }
</style>
