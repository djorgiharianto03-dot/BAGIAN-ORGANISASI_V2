<?php
declare(strict_types=1);
?>
    /* E-Organisasi — Premium Government Workspace */
    body.page-eorg-hub {
        --eo-navy: #0c2340;
        --eo-navy-soft: #1e3a5f;
        --eo-muted: #64748b;
        --eo-text: #334155;
        --eo-radius: 24px;
        --eo-glass: rgba(255, 255, 255, 0.62);
        --eo-glass-border: rgba(255, 255, 255, 0.78);
        --eo-shadow-1: 0 1px 2px rgba(15, 23, 42, 0.04);
        --eo-shadow-2: 0 10px 28px rgba(15, 23, 42, 0.07);
        --eo-shadow-3: 0 24px 48px rgba(15, 23, 42, 0.09);
        --eo-shadow-hover: 0 8px 20px rgba(37, 99, 235, 0.14), 0 24px 44px rgba(15, 23, 42, 0.1);
        --eo-ease: cubic-bezier(0.22, 1, 0.36, 1);
        --eo-blue: #2563eb;
        --eo-violet: #7c3aed;
        --eo-emerald: #059669;
        --eo-amber: #d97706;
        --eo-slate: #475569;
        --page-bg: #f0f6fc;
        font-family: 'Inter', 'Public Sans', 'Poppins', system-ui, sans-serif;
    }

    /* Header: gunakan gaya global site_styles (sama beranda), tanpa override khusus. */

    /* —— Main workspace —— */
    body.page-eorg-hub .site-main {
        max-width: 1180px;
        padding-top: 1.25rem;
    }
    .eorg-hub--premium {
        position: relative;
        isolation: isolate;
        margin-left: calc(-1 * var(--bs-gutter-x, 0.75rem));
        margin-right: calc(-1 * var(--bs-gutter-x, 0.75rem));
        padding: clamp(2rem, 1.5rem + 2.5vw, 3.5rem) clamp(0.85rem, 2vw, 1.5rem);
        overflow: hidden;
        border-radius: var(--eo-radius);
        background: linear-gradient(165deg, #ffffff 0%, #f0f7ff 40%, #e8f2fc 75%, #f8fafc 100%);
    }
    .eorg-hub__ambient {
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 0;
    }
    .eorg-hub__orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        opacity: 0.5;
        animation: eorgOrbFloat 20s ease-in-out infinite alternate;
    }
    .eorg-hub__orb--tr {
        top: -10%;
        right: -5%;
        width: min(440px, 58vw);
        height: min(440px, 58vw);
        background: radial-gradient(circle, rgba(59, 130, 246, 0.38) 0%, transparent 68%);
    }
    .eorg-hub__orb--bl {
        bottom: -15%;
        left: -8%;
        width: min(400px, 52vw);
        height: min(400px, 52vw);
        background: radial-gradient(circle, rgba(14, 165, 233, 0.25) 0%, transparent 70%);
        animation-delay: -8s;
    }
    @keyframes eorgOrbFloat {
        0% { transform: translate(0, 0); }
        100% { transform: translate(14px, -18px); }
    }
    .eorg-hub__grain {
        position: absolute;
        inset: 0;
        opacity: 0.32;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.05'/%3E%3C/svg%3E");
        background-size: 200px 200px;
        mix-blend-mode: multiply;
    }
    .eorg-hub__container {
        position: relative;
        z-index: 1;
    }
    .eorg-hub__head {
        text-align: center;
        margin-bottom: clamp(2rem, 1.5rem + 2vw, 3rem);
    }
    .eorg-hub__badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 1rem;
        padding: 0.35rem 0.9rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--eo-blue);
        background: rgba(255, 255, 255, 0.75);
        border: 1px solid rgba(37, 99, 235, 0.18);
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 16px rgba(37, 99, 235, 0.1);
    }
    .eorg-hub__badge-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #22c55e;
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.5);
        animation: eoPulseDot 2s ease-in-out infinite;
    }
    @keyframes eoPulseDot {
        0%, 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.45); }
        50% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
    }
    .eorg-hub__title-wrap {
        position: relative;
        display: inline-block;
        margin-bottom: 0.85rem;
    }
    .eorg-hub__title-glow {
        position: absolute;
        left: 50%;
        top: 55%;
        transform: translate(-50%, -50%);
        width: 120%;
        height: 140%;
        background: radial-gradient(ellipse, rgba(59, 130, 246, 0.22) 0%, transparent 65%);
        pointer-events: none;
        filter: blur(20px);
    }
    .eorg-hub__title {
        position: relative;
        margin: 0;
        font-size: clamp(2rem, 1.5rem + 2vw, 3rem);
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.03em;
        background: linear-gradient(135deg, #0c2340 0%, #1d4ed8 45%, #0e7490 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    .eorg-hub__lead {
        margin: 0 auto;
        max-width: 40rem;
        font-size: clamp(0.95rem, 0.9rem + 0.2vw, 1.05rem);
        line-height: 1.75;
        color: var(--eo-muted);
        opacity: 0.9;
    }

    /* Smart info panel */
    .eorg-smart-panel {
        position: relative;
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.25rem;
        padding: clamp(1.35rem, 1.1rem + 0.8vw, 1.75rem);
        margin-bottom: clamp(2rem, 1.5rem + 1.5vw, 2.75rem);
        border-radius: var(--eo-radius);
        background: var(--eo-glass);
        border: 1px solid var(--eo-glass-border);
        backdrop-filter: blur(20px) saturate(1.35);
        -webkit-backdrop-filter: blur(20px) saturate(1.35);
        box-shadow: var(--eo-shadow-1), var(--eo-shadow-2), var(--eo-shadow-3);
        transition: transform 0.3s var(--eo-ease), box-shadow 0.3s var(--eo-ease);
        overflow: hidden;
    }
    .eorg-smart-panel::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: inherit;
        padding: 1px;
        background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(59,130,246,0.25), rgba(16,185,129,0.2));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
    }
    .eorg-smart-panel:hover {
        transform: translateY(-3px);
        box-shadow: var(--eo-shadow-hover);
    }
    @media (min-width: 992px) {
        .eorg-smart-panel {
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.4fr);
            align-items: stretch;
        }
    }
    .eorg-smart-panel__clock-block {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding-right: 0.5rem;
    }
    .eorg-smart-panel__label {
        display: block;
        margin-bottom: 0.4rem;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--eo-muted);
    }
    .eorg-smart-panel__time {
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        font-size: clamp(2.25rem, 1.8rem + 2vw, 3.25rem);
        font-weight: 700;
        line-height: 1.05;
        letter-spacing: 0.06em;
        color: var(--eo-navy);
        font-variant-numeric: tabular-nums;
    }
    .eorg-smart-panel__date-line {
        margin: 0.65rem 0 0;
        font-size: 0.92rem;
        font-weight: 600;
        color: var(--eo-navy-soft);
        text-transform: capitalize;
    }
    .eorg-smart-panel__metrics {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }
    @media (min-width: 576px) {
        .eorg-smart-panel__metrics {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    .eorg-metric {
        padding: 0.75rem 0.85rem;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.55);
        border: 1px solid rgba(148, 163, 184, 0.15);
        transition: background 0.25s ease, transform 0.25s var(--eo-ease);
    }
    .eorg-metric:hover {
        background: rgba(255, 255, 255, 0.82);
        transform: translateY(-2px);
    }
    .eorg-metric--wide {
        grid-column: 1 / -1;
    }
    @media (min-width: 576px) {
        .eorg-metric--wide {
            grid-column: span 3;
        }
    }
    .eorg-metric__label {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.35rem;
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--eo-muted);
    }
    .eorg-metric__value {
        font-size: 0.95rem;
        font-weight: 700;
        line-height: 1.35;
        color: var(--eo-navy);
    }
    .eorg-metric__value--online {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        color: #047857;
    }
    .eorg-status-pulse {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #22c55e;
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.5);
        animation: eoPulseDot 2s ease-in-out infinite;
    }
    .eorg-server-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #22c55e;
        margin-right: 0.35rem;
        vertical-align: middle;
    }
    .eorg-progress {
        height: 6px;
        margin-top: 0.5rem;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.2);
        overflow: hidden;
    }
    .eorg-progress__fill {
        height: 100%;
        width: 0%;
        border-radius: inherit;
        background: linear-gradient(90deg, #3b82f6, #06b6d4, #10b981);
        transition: width 1.2s var(--eo-ease);
    }
    .eorg-progress__pct {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--eo-blue);
        font-variant-numeric: tabular-nums;
    }

    /* Feature cards */
    .eorg-hub-grid {
        --bs-gutter-x: 1.35rem;
        --bs-gutter-y: 1.35rem;
    }
    @media (min-width: 992px) {
        .eorg-hub-grid {
            --bs-gutter-x: 1.65rem;
            --bs-gutter-y: 1.65rem;
        }
    }
    .eorg-hub-card {
        position: relative;
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 188px;
        padding: 1.5rem 1.35rem 1.35rem;
        border-radius: var(--eo-radius);
        text-decoration: none;
        color: inherit;
        background: var(--eo-glass);
        border: 1px solid var(--eo-glass-border);
        backdrop-filter: blur(18px) saturate(1.3);
        -webkit-backdrop-filter: blur(18px) saturate(1.3);
        box-shadow: var(--eo-shadow-1), var(--eo-shadow-2);
        overflow: hidden;
        transition: transform 0.3s var(--eo-ease), box-shadow 0.3s var(--eo-ease), border-color 0.3s ease;
    }
    .eorg-hub-card::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: inherit;
        padding: 1px;
        background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(148,163,184,0.12));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
        z-index: 2;
    }
    .eorg-hub-card::after {
        content: "";
        position: absolute;
        top: 0;
        left: -130%;
        width: 50%;
        height: 100%;
        background: linear-gradient(105deg, transparent, rgba(255,255,255,0.4), transparent);
        transform: skewX(-18deg);
        opacity: 0;
        pointer-events: none;
        z-index: 3;
    }
    .eorg-hub-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--eo-shadow-hover);
        color: inherit;
        border-color: rgba(255, 255, 255, 0.95);
    }
    .eorg-hub-card:hover::after {
        opacity: 1;
        animation: eorgCardShimmer 0.75s var(--eo-ease) forwards;
    }
    @keyframes eorgCardShimmer {
        0% { left: -130%; }
        100% { left: 140%; }
    }
    .eorg-hub-card__badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        min-width: 1.5rem;
        height: 1.5rem;
        padding: 0 0.4rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 800;
        color: #fff;
        z-index: 4;
        animation: eoBadgePulse 2.5s ease-in-out infinite;
    }
    @keyframes eoBadgePulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.06); }
    }
    .eorg-hub-card__badge--danger {
        background: linear-gradient(135deg, #f87171, #dc2626);
        box-shadow: 0 4px 14px rgba(220, 38, 38, 0.4);
    }
    .eorg-hub-card__badge--info {
        background: linear-gradient(135deg, #38bdf8, #2563eb);
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
    }
    .eorg-hub-card__icon {
        position: relative;
        width: 3.65rem;
        height: 3.65rem;
        margin-bottom: 1.1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        font-size: 1.55rem;
        z-index: 1;
    }
    .eorg-hub-card__icon::before {
        content: "";
        position: absolute;
        inset: -6px;
        border-radius: 22px;
        opacity: 0.45;
        filter: blur(12px);
        z-index: -1;
    }
    .eorg-hub-card__title {
        position: relative;
        z-index: 1;
        margin: 0 0 0.45rem;
        font-size: 1.05rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        line-height: 1.35;
        color: var(--eo-navy);
    }
    .eorg-hub-card__desc {
        position: relative;
        z-index: 1;
        margin: 0;
        font-size: 0.86rem;
        line-height: 1.7;
        color: var(--eo-muted);
    }
    .eorg-hub-card--dashboard .eorg-hub-card__icon {
        color: #1d4ed8;
        background: linear-gradient(145deg, #dbeafe, #93c5fd);
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.28);
    }
    .eorg-hub-card--dashboard .eorg-hub-card__icon::before { background: rgba(37, 99, 235, 0.5); }
    .eorg-hub-card--tamu .eorg-hub-card__icon,
    .eorg-hub-card--arsip .eorg-hub-card__icon {
        color: #047857;
        background: linear-gradient(145deg, #d1fae5, #6ee7b7);
        box-shadow: 0 8px 24px rgba(5, 150, 105, 0.28);
    }
    .eorg-hub-card--tamu .eorg-hub-card__icon::before,
    .eorg-hub-card--arsip .eorg-hub-card__icon::before { background: rgba(5, 150, 105, 0.45); }
    .eorg-hub-card--monitoring .eorg-hub-card__icon {
        color: #b45309;
        background: linear-gradient(145deg, #fef3c7, #fcd34d);
        box-shadow: 0 8px 24px rgba(217, 119, 6, 0.28);
    }
    .eorg-hub-card--monitoring .eorg-hub-card__icon::before { background: rgba(217, 119, 6, 0.4); }
    .eorg-hub-card--disposisi .eorg-hub-card__icon {
        color: #5b21b6;
        background: linear-gradient(145deg, #ede9fe, #c4b5fd);
        box-shadow: 0 8px 24px rgba(124, 58, 237, 0.28);
    }
    .eorg-hub-card--disposisi .eorg-hub-card__icon::before { background: rgba(124, 58, 237, 0.4); }

    @media (prefers-reduced-motion: reduce) {
        .eorg-hub__orb,
        .eorg-hub__badge-dot,
        .eorg-status-pulse,
        .eorg-hub-card__badge,
        .eorg-hub-card:hover,
        .eorg-smart-panel:hover,
        .eo-module-card:hover,
        .eo-stat-card:hover {
            transform: none;
        }
        .eorg-hub-card:hover::after {
            animation: none;
            opacity: 0;
        }
    }

    /* —— Enterprise dashboard (E-Organisasi) —— */
    body.page-eorg-enterprise {
        --eo-dash-radius: 16px;
        --eo-dash-gap: 1.25rem;
        --eo-surface: #ffffff;
        --eo-border: #e2e8f0;
        --eo-shadow: 0 1px 3px rgba(15, 23, 42, 0.06), 0 8px 24px rgba(15, 23, 42, 0.06);
        --eo-shadow-hover: 0 12px 32px rgba(15, 23, 42, 0.1);
        --eo-ease: cubic-bezier(0.22, 1, 0.36, 1);
    }
    body.page-eorg-enterprise .site-main {
        max-width: 1280px;
        padding-top: 0.5rem;
    }
    .eorg-hub--enterprise {
        margin-left: calc(-1 * var(--bs-gutter-x, 0.75rem));
        margin-right: calc(-1 * var(--bs-gutter-x, 0.75rem));
        padding: clamp(1.25rem, 2vw, 2rem) clamp(0.75rem, 1.5vw, 1.25rem) clamp(2rem, 3vw, 2.75rem);
        border-radius: var(--eo-dash-radius);
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid var(--eo-border);
    }
    .eorg-hub--enterprise .eorg-hub__container {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .eo-dash__top {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1.25rem 2rem;
    }
    .eo-dash__eyebrow {
        margin: 0 0 0.35rem;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--eo-blue);
    }
    .eo-dash__heading {
        margin: 0 0 0.4rem;
        font-size: clamp(1.35rem, 1.1rem + 0.8vw, 1.75rem);
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--eo-navy);
    }
    .eo-dash__sub {
        margin: 0;
        max-width: 36rem;
        font-size: 0.92rem;
        line-height: 1.6;
        color: var(--eo-muted);
    }
    .eo-dash__clock {
        padding: 1rem 1.2rem;
        border-radius: var(--eo-dash-radius);
        background: var(--eo-surface);
        border: 1px solid var(--eo-border);
        box-shadow: var(--eo-shadow);
        min-width: min(100%, 220px);
    }
    .eo-dash__clock-label {
        display: block;
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--eo-muted);
        margin-bottom: 0.35rem;
    }
    .eo-dash__clock-time {
        display: block;
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        font-size: clamp(1.5rem, 1.2rem + 1vw, 2rem);
        font-weight: 700;
        color: var(--eo-navy);
        font-variant-numeric: tabular-nums;
    }
    .eo-dash__clock-date {
        margin: 0.4rem 0 0;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--eo-navy-soft);
        text-transform: capitalize;
    }

    .eo-dash__stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: var(--eo-dash-gap);
    }
    @media (min-width: 992px) {
        .eo-dash__stats {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    .eo-stat-card {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.15rem 1.2rem;
        border-radius: var(--eo-dash-radius);
        background: var(--eo-surface);
        border: 1px solid var(--eo-border);
        box-shadow: var(--eo-shadow);
        transition: transform 0.28s var(--eo-ease), box-shadow 0.28s var(--eo-ease);
    }
    .eo-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--eo-shadow-hover);
    }
    .eo-stat-card__icon {
        flex-shrink: 0;
        width: 2.75rem;
        height: 2.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.1rem;
    }
    .eo-stat-card--blue .eo-stat-card__icon { background: #dbeafe; color: #1d4ed8; }
    .eo-stat-card--violet .eo-stat-card__icon { background: #ede9fe; color: #6d28d9; }
    .eo-stat-card--emerald .eo-stat-card__icon { background: #d1fae5; color: #047857; }
    .eo-stat-card--amber .eo-stat-card__icon { background: #fef3c7; color: #b45309; }
    .eo-stat-card__label {
        margin: 0 0 0.2rem;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--eo-muted);
    }
    .eo-stat-card__value {
        margin: 0;
        font-size: 1.65rem;
        font-weight: 800;
        line-height: 1.1;
        color: var(--eo-navy);
        font-variant-numeric: tabular-nums;
    }
    .eo-stat-card__value--sm {
        font-size: 1.15rem;
    }
    .eo-stat-card__hint {
        margin: 0.35rem 0 0;
        font-size: 0.78rem;
        color: var(--eo-muted);
    }

    .eo-dash__widgets {
        display: grid;
        gap: var(--eo-dash-gap);
        grid-template-columns: minmax(0, 1fr);
    }
    @media (min-width: 1200px) {
        .eo-dash__widgets {
            grid-template-columns: 1.4fr 1fr 1fr;
            grid-template-rows: auto auto;
        }
        .eo-widget--chart {
            grid-row: span 2;
        }
    }
    @media (min-width: 768px) and (max-width: 1199.98px) {
        .eo-dash__widgets {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .eo-widget--chart {
            grid-column: span 2;
        }
    }
    .eo-widget {
        display: flex;
        flex-direction: column;
        border-radius: var(--eo-dash-radius);
        background: var(--eo-surface);
        border: 1px solid var(--eo-border);
        box-shadow: var(--eo-shadow);
        overflow: hidden;
        transition: box-shadow 0.28s var(--eo-ease);
    }
    .eo-widget:hover {
        box-shadow: var(--eo-shadow-hover);
    }
    .eo-widget__head {
        padding: 1rem 1.15rem 0.75rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .eo-widget__title {
        margin: 0;
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--eo-navy);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .eo-widget__title i {
        color: var(--eo-blue);
        font-size: 0.9rem;
    }
    .eo-widget__body {
        padding: 1rem 1.15rem 1.15rem;
        flex: 1;
        min-height: 0;
    }
    .eo-widget__body--stack {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .eo-widget--chart .eo-widget__body {
        min-height: 240px;
        position: relative;
    }
    .eo-widget--chart canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .eo-progress-row__meta {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        margin-bottom: 0.4rem;
        font-size: 0.82rem;
    }
    .eo-progress-row__icon {
        width: 1.75rem;
        height: 1.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 0.75rem;
    }
    .eo-progress-row__icon--emerald { background: #d1fae5; color: #047857; }
    .eo-progress-row__icon--violet { background: #ede9fe; color: #6d28d9; }
    .eo-progress-row__icon--amber { background: #fef3c7; color: #b45309; }
    .eo-progress-row__label {
        flex: 1;
        font-weight: 600;
        color: var(--eo-text);
    }
    .eo-progress-row__count {
        font-weight: 800;
        color: var(--eo-navy);
        font-variant-numeric: tabular-nums;
    }
    .eo-progress-row__track {
        height: 6px;
        border-radius: 999px;
        background: #f1f5f9;
        overflow: hidden;
    }
    .eo-progress-row__fill {
        display: block;
        height: 100%;
        border-radius: inherit;
        transition: width 0.9s var(--eo-ease);
    }
    .eo-progress-row__fill--emerald { background: linear-gradient(90deg, #34d399, #059669); }
    .eo-progress-row__fill--violet { background: linear-gradient(90deg, #a78bfa, #7c3aed); }
    .eo-progress-row__fill--amber { background: linear-gradient(90deg, #fcd34d, #d97706); }
    .eo-progress-overall {
        margin-top: 0.25rem;
        padding-top: 0.85rem;
        border-top: 1px dashed var(--eo-border);
    }
    .eo-progress-overall__label {
        display: flex;
        justify-content: space-between;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--eo-muted);
        margin-bottom: 0.45rem;
    }
    .eo-progress-overall__label strong {
        color: var(--eo-blue);
    }
    .eo-progress-overall__track {
        height: 8px;
        border-radius: 999px;
        background: #f1f5f9;
        overflow: hidden;
    }
    .eo-progress-overall__fill {
        height: 100%;
        width: 0%;
        border-radius: inherit;
        background: linear-gradient(90deg, #3b82f6, #06b6d4, #10b981);
        transition: width 1.1s var(--eo-ease);
    }

    .eo-feed {
        list-style: none;
        margin: 0;
        padding: 0;
        max-height: 320px;
        overflow-y: auto;
    }
    .eo-feed__item {
        display: flex;
        gap: 0.75rem;
        padding: 0.65rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .eo-feed__item:last-child {
        border-bottom: none;
    }
    .eo-feed__dot {
        flex-shrink: 0;
        width: 8px;
        height: 8px;
        margin-top: 0.45rem;
        border-radius: 50%;
        background: var(--eo-blue);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    .eo-feed__text {
        margin: 0 0 0.2rem;
        font-size: 0.84rem;
        font-weight: 600;
        line-height: 1.45;
        color: var(--eo-navy);
    }
    .eo-feed__meta {
        margin: 0;
        font-size: 0.74rem;
        color: var(--eo-muted);
    }
    .eo-feed__empty {
        font-size: 0.88rem;
        color: var(--eo-muted);
        padding: 0.5rem 0;
    }

    .eo-modules__head {
        margin-bottom: 1.15rem;
    }
    .eo-modules__title {
        margin: 0 0 0.35rem;
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--eo-navy);
    }
    .eo-modules__lead {
        margin: 0;
        font-size: 0.88rem;
        color: var(--eo-muted);
    }
    .eo-modules__grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: minmax(0, 1fr);
    }
    @media (min-width: 640px) {
        .eo-modules__grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1200px) {
        .eo-modules__grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    .eo-module-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.1rem 1.15rem;
        border-radius: var(--eo-dash-radius);
        text-decoration: none;
        color: inherit;
        background: var(--eo-surface);
        border: 1px solid var(--eo-border);
        box-shadow: var(--eo-shadow);
        transition: transform 0.28s var(--eo-ease), box-shadow 0.28s var(--eo-ease), border-color 0.25s ease;
    }
    .eo-module-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--eo-shadow-hover);
        border-color: rgba(37, 99, 235, 0.25);
        color: inherit;
    }
    .eo-module-card__badge {
        position: absolute;
        top: 0.65rem;
        right: 0.65rem;
        min-width: 1.35rem;
        height: 1.35rem;
        padding: 0 0.35rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        font-size: 0.65rem;
        font-weight: 800;
        color: #fff;
    }
    .eo-module-card__badge--danger { background: #dc2626; }
    .eo-module-card__badge--warning { background: #d97706; }
    .eo-module-card__badge--info { background: #2563eb; }
    .eo-module-card__icon {
        flex-shrink: 0;
        width: 2.85rem;
        height: 2.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.15rem;
        transition: transform 0.28s var(--eo-ease);
    }
    .eo-module-card:hover .eo-module-card__icon {
        transform: scale(1.06);
    }
    .eo-module-card--dashboard .eo-module-card__icon { background: #dbeafe; color: #1d4ed8; }
    .eo-module-card--tamu .eo-module-card__icon,
    .eo-module-card--arsip .eo-module-card__icon { background: #d1fae5; color: #047857; }
    .eo-module-card--tugas .eo-module-card__icon { background: #e0e7ff; color: #4338ca; }
    .eo-module-card--monitoring .eo-module-card__icon { background: #fef3c7; color: #b45309; }
    .eo-module-card--disposisi .eo-module-card__icon { background: #ede9fe; color: #6d28d9; }
    .eo-module-card__body {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
        padding-right: 1.5rem;
    }
    .eo-module-card__title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--eo-navy);
        line-height: 1.3;
    }
    .eo-module-card__desc {
        font-size: 0.8rem;
        line-height: 1.5;
        color: var(--eo-muted);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .eo-module-card__arrow {
        flex-shrink: 0;
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #f1f5f9;
        color: var(--eo-muted);
        font-size: 0.75rem;
        transition: background 0.25s ease, color 0.25s ease, transform 0.25s var(--eo-ease);
    }
    .eo-module-card:hover .eo-module-card__arrow {
        background: var(--eo-blue);
        color: #fff;
        transform: translateX(2px);
    }
