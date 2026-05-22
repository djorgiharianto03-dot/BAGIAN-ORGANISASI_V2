<?php
?>
    /* Profil Organisasi — Premium Government Digital UI */
    .page-profil-org {
        --po-navy: #0c2340;
        --po-navy-soft: #1e3a5f;
        --po-slate: #475569;
        --po-muted: #64748b;
        --po-text: #334155;
        --po-radius: 24px;
        --po-glass: rgba(255, 255, 255, 0.62);
        --po-glass-border: rgba(255, 255, 255, 0.75);
        --po-shadow-1: 0 1px 2px rgba(15, 23, 42, 0.04);
        --po-shadow-2: 0 8px 24px rgba(15, 23, 42, 0.06);
        --po-shadow-3: 0 24px 48px rgba(15, 23, 42, 0.08);
        --po-shadow-hover: 0 4px 8px rgba(15, 23, 42, 0.05), 0 20px 40px rgba(37, 99, 235, 0.12);
        --po-blue: #2563eb;
        --po-violet: #7c3aed;
        --po-emerald: #059669;
        --po-ease: cubic-bezier(0.22, 1, 0.36, 1);
    }
    .page-profil-org .site-main {
        background: #f8fafc;
    }
    .profil-org--premium {
        position: relative;
        isolation: isolate;
        margin-left: calc(-1 * var(--bs-gutter-x, 0.75rem));
        margin-right: calc(-1 * var(--bs-gutter-x, 0.75rem));
        padding: clamp(2.75rem, 2rem + 3vw, 4.5rem) clamp(1rem, 2vw, 2rem);
        overflow: hidden;
        background:
            linear-gradient(165deg, #ffffff 0%, #f0f7ff 38%, #e8f2fc 72%, #f8fafc 100%);
    }
    .profil-org__ambient {
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 0;
    }
    .profil-org__orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(72px);
        opacity: 0.55;
        animation: profilOrgOrbFloat 18s ease-in-out infinite alternate;
    }
    .profil-org__orb--tr {
        top: -8%;
        right: -4%;
        width: min(420px, 55vw);
        height: min(420px, 55vw);
        background: radial-gradient(circle, rgba(59, 130, 246, 0.35) 0%, rgba(59, 130, 246, 0) 68%);
    }
    .profil-org__orb--bl {
        bottom: -12%;
        left: -6%;
        width: min(380px, 50vw);
        height: min(380px, 50vw);
        background: radial-gradient(circle, rgba(16, 185, 129, 0.22) 0%, rgba(16, 185, 129, 0) 70%);
        animation-delay: -6s;
    }
    @keyframes profilOrgOrbFloat {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(12px, -16px) scale(1.05); }
    }
    .profil-org__grain {
        position: absolute;
        inset: 0;
        opacity: 0.35;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
        background-size: 180px 180px;
        mix-blend-mode: multiply;
    }
    .profil-org__container {
        position: relative;
        z-index: 1;
        max-width: 56rem;
        margin: 0 auto;
    }
    .profil-org__page-head {
        text-align: center;
        margin-bottom: clamp(2rem, 1.5rem + 2vw, 3rem);
    }
    .profil-org__accent-line {
        display: block;
        width: 3rem;
        height: 3px;
        margin: 0 auto 1.1rem;
        border-radius: 999px;
        background: linear-gradient(90deg, transparent, var(--po-blue), var(--po-violet), transparent);
        box-shadow: 0 0 16px rgba(37, 99, 235, 0.35);
    }
    .profil-org__eyebrow {
        margin: 0 0 0.65rem;
        font-family: 'Inter', 'Public Sans', system-ui, sans-serif;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: var(--po-blue);
        opacity: 0.85;
    }
    .profil-org__page-title {
        margin: 0 0 0.85rem;
        font-family: 'Inter', 'Public Sans', system-ui, sans-serif;
        font-size: clamp(1.85rem, 1.35rem + 1.6vw, 2.65rem);
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -0.03em;
        color: var(--po-navy);
    }
    .profil-org__page-lead {
        margin: 0 auto;
        max-width: 38rem;
        font-size: clamp(0.95rem, 0.9rem + 0.2vw, 1.05rem);
        font-weight: 400;
        line-height: 1.75;
        color: var(--po-muted);
        opacity: 0.88;
    }
    .profil-org__stack {
        display: flex;
        flex-direction: column;
        gap: clamp(1.35rem, 1rem + 1vw, 1.85rem);
    }
    .profil-org-glass {
        position: relative;
        border-radius: var(--po-radius);
        overflow: hidden;
        background: var(--po-glass);
        border: 1px solid var(--po-glass-border);
        backdrop-filter: blur(20px) saturate(1.35);
        -webkit-backdrop-filter: blur(20px) saturate(1.35);
        box-shadow: var(--po-shadow-1), var(--po-shadow-2), var(--po-shadow-3);
        transition:
            transform 0.3s var(--po-ease),
            box-shadow 0.3s var(--po-ease),
            border-color 0.3s ease;
    }
    .profil-org-glass::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: inherit;
        padding: 1px;
        background: linear-gradient(
            135deg,
            rgba(255, 255, 255, 0.95) 0%,
            rgba(148, 163, 184, 0.15) 40%,
            rgba(59, 130, 246, 0.2) 100%
        );
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
        z-index: 2;
    }
    .profil-org-glass::after {
        content: "";
        position: absolute;
        top: 0;
        left: -120%;
        width: 55%;
        height: 100%;
        background: linear-gradient(
            105deg,
            transparent 0%,
            rgba(255, 255, 255, 0.45) 48%,
            transparent 100%
        );
        transform: skewX(-18deg);
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
        z-index: 3;
    }
    .profil-org-glass:hover {
        transform: translateY(-5px);
        box-shadow: var(--po-shadow-hover);
        border-color: rgba(255, 255, 255, 0.9);
    }
    .profil-org-glass:hover::after {
        opacity: 1;
        animation: profilOrgShimmer 0.85s var(--po-ease) forwards;
    }
    @keyframes profilOrgShimmer {
        0% { left: -120%; }
        100% { left: 140%; }
    }
    .profil-org-glass--visi {
        background: linear-gradient(
            145deg,
            rgba(255, 255, 255, 0.78) 0%,
            rgba(239, 246, 255, 0.65) 50%,
            rgba(255, 255, 255, 0.72) 100%
        );
    }
    .profil-org-glass--misi {
        background: linear-gradient(
            145deg,
            rgba(255, 255, 255, 0.76) 0%,
            rgba(245, 243, 255, 0.58) 50%,
            rgba(255, 255, 255, 0.74) 100%
        );
    }
    .profil-org-glass--ringkasan {
        background: linear-gradient(
            145deg,
            rgba(255, 255, 255, 0.76) 0%,
            rgba(236, 253, 245, 0.52) 50%,
            rgba(255, 255, 255, 0.74) 100%
        );
    }
    .profil-org-glass__inner {
        position: relative;
        z-index: 1;
        padding: clamp(1.5rem, 1.2rem + 1vw, 2rem) clamp(1.35rem, 1.1rem + 0.8vw, 1.85rem);
    }
    .profil-org-card__head {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.15rem;
    }
    .profil-org-card__icon {
        position: relative;
        flex-shrink: 0;
        width: 3.25rem;
        height: 3.25rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .profil-org-card__icon::before {
        content: "";
        position: absolute;
        inset: -4px;
        border-radius: inherit;
        opacity: 0.55;
        filter: blur(10px);
        z-index: -1;
    }
    .profil-org-card__icon--visi {
        color: #1d4ed8;
        background: linear-gradient(145deg, #dbeafe 0%, #93c5fd 100%);
        box-shadow: 0 4px 20px rgba(37, 99, 235, 0.28);
    }
    .profil-org-card__icon--visi::before {
        background: rgba(37, 99, 235, 0.45);
    }
    .profil-org-card__icon--misi {
        color: #6d28d9;
        background: linear-gradient(145deg, #ede9fe 0%, #c4b5fd 100%);
        box-shadow: 0 4px 20px rgba(124, 58, 237, 0.28);
    }
    .profil-org-card__icon--misi::before {
        background: rgba(124, 58, 237, 0.4);
    }
    .profil-org-card__icon--ringkasan {
        color: #047857;
        background: linear-gradient(145deg, #d1fae5 0%, #6ee7b7 100%);
        box-shadow: 0 4px 20px rgba(5, 150, 105, 0.28);
    }
    .profil-org-card__icon--ringkasan::before {
        background: rgba(5, 150, 105, 0.4);
    }
    .profil-org-card__lucide {
        width: 1.35rem;
        height: 1.35rem;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
        fill: none;
    }
    .profil-org-vision__label,
    .profil-org-mission__label,
    .profil-org-summary__label {
        margin: 0;
        font-family: 'Inter', 'Public Sans', system-ui, sans-serif;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.24em;
        text-transform: uppercase;
        color: var(--po-navy-soft);
    }
    .profil-org-vision__body {
        line-height: 1.75;
        font-style: normal;
        text-transform: none;
    }
    .profil-org-vision__body p,
    .profil-org-vision__text {
        margin: 0;
        font-size: clamp(1.02rem, 0.96rem + 0.15vw, 1.12rem);
        font-weight: 400;
        line-height: 1.75;
        color: var(--po-text);
        letter-spacing: 0.01em;
    }
    .profil-org-vision__body p + p {
        margin-top: 0.9rem;
    }
    .profil-org-vision__body em,
    .profil-org-vision__body i {
        font-style: normal;
    }
    .profil-org-vision__text--empty,
    .profil-org-mission__empty,
    .profil-org-summary__empty {
        font-size: 0.94rem;
        line-height: 1.65;
        color: var(--po-muted);
    }
    .profil-org-mission__list {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        margin: 0;
        padding: 0;
    }
    .profil-org-mission__item {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
    }
    .profil-org-mission__bullet {
        flex-shrink: 0;
        width: 1.5rem;
        height: 1.5rem;
        margin-top: 0.2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #fff;
        background: linear-gradient(135deg, #a78bfa 0%, var(--po-violet) 100%);
        box-shadow: 0 3px 12px rgba(124, 58, 237, 0.3);
    }
    .profil-org-mission__bullet .profil-org-card__lucide {
        width: 0.8rem;
        height: 0.8rem;
        stroke-width: 2.5;
    }
    .profil-org-mission__text {
        font-size: 0.98rem;
        line-height: 1.7;
        color: var(--po-text);
    }
    .profil-org-summary__text {
        margin: 0;
        font-size: 0.98rem;
        font-weight: 400;
        line-height: 1.75;
        color: var(--po-text);
    }
    .profil-org__admin-hint {
        position: relative;
        z-index: 1;
        margin-top: clamp(1.75rem, 1.25rem + 1.5vw, 2.5rem) !important;
        font-size: 0.875rem;
        color: var(--po-muted) !important;
    }
    .profil-org__admin-hint a {
        color: var(--po-blue);
        font-weight: 600;
        text-decoration: none;
    }
    .profil-org__admin-hint a:hover {
        text-decoration: underline;
    }
    @media (max-width: 575.98px) {
        .profil-org--premium {
            margin-left: 0;
            margin-right: 0;
            padding-left: 0.15rem;
            padding-right: 0.15rem;
        }
        .profil-org-card__head {
            gap: 0.75rem;
        }
        .profil-org-card__icon {
            width: 2.85rem;
            height: 2.85rem;
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .profil-org__orb {
            animation: none;
        }
        .profil-org-glass:hover {
            transform: none;
        }
        .profil-org-glass:hover::after {
            animation: none;
            opacity: 0;
        }
    }
