<?php
?>
    /* Bagan Organisasi & Personel — Smart Governance (ringan, institusional) */
    .page-profil-org .profil-structure {
        --ps-navy: var(--sg-navy, #081c3a);
        --ps-navy-soft: var(--sg-royal, #123a6d);
        --ps-muted: var(--sg-muted, #5b6f8c);
        --ps-text: #334155;
        --ps-radius: var(--sg-radius-xl, 20px);
        --ps-ease: cubic-bezier(0.22, 1, 0.36, 1);
        --ps-shadow: var(--sg-shadow, 0 2px 4px rgba(8, 28, 58, 0.04), 0 12px 32px rgba(8, 28, 58, 0.06));
        --ps-shadow-hover: 0 8px 24px rgba(8, 28, 58, 0.08), 0 16px 36px rgba(37, 99, 235, 0.1);
        position: relative;
        isolation: isolate;
        margin-left: calc(-1 * var(--bs-gutter-x, 0.75rem));
        margin-right: calc(-1 * var(--bs-gutter-x, 0.75rem));
        padding: clamp(2.5rem, 2rem + 2.5vw, 4rem) clamp(0.85rem, 2vw, 1.5rem);
        overflow: hidden;
        border-radius: var(--ps-radius);
        border: 1px solid var(--sg-border, rgba(8, 28, 58, 0.08));
        background: linear-gradient(180deg, #f8fafc 0%, var(--sg-surface, #f4f7fb) 48%, #ffffff 100%);
        contain: layout style;
    }
    .profil-structure__ambient {
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 0;
        background:
            radial-gradient(ellipse 70% 45% at 100% 0%, rgba(37, 99, 235, 0.06), transparent 55%),
            radial-gradient(ellipse 55% 40% at 0% 100%, rgba(16, 185, 129, 0.05), transparent 50%);
    }
    .profil-structure__orb,
    .profil-structure__grain,
    .profil-org-chart__chief-glow {
        display: none !important;
    }
    .profil-structure__container {
        position: relative;
        z-index: 1;
        max-width: 72rem;
        margin: 0 auto;
    }
    .profil-structure__head {
        text-align: center;
        margin-bottom: clamp(2rem, 1.5rem + 2vw, 3rem);
    }
    .profil-structure__accent {
        display: block;
        width: 3rem;
        height: 3px;
        margin: 0 auto 1rem;
        border-radius: 999px;
        background: linear-gradient(90deg, transparent, #2563eb, #123a6d, transparent);
    }
    .profil-structure__eyebrow {
        margin: 0 0 0.5rem;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: #2563eb;
        opacity: 0.88;
    }
    .profil-structure__title {
        margin: 0 0 0.75rem;
        font-size: clamp(1.65rem, 1.25rem + 1.4vw, 2.35rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.15;
        color: var(--ps-navy);
    }
    .profil-structure__lead {
        margin: 0 auto;
        max-width: 42rem;
        font-size: clamp(0.94rem, 0.9rem + 0.15vw, 1.02rem);
        line-height: 1.75;
        color: var(--ps-muted);
        opacity: 0.92;
    }
    .profil-structure__block {
        margin-bottom: clamp(2.25rem, 1.75rem + 1.5vw, 3.25rem);
    }
    .profil-structure__block-title {
        margin: 0 0 0.35rem;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: var(--ps-navy-soft);
    }
    .profil-structure__block-desc {
        margin: 0 0 1.25rem;
        font-size: 0.9rem;
        line-height: 1.65;
        color: var(--ps-muted);
    }

    /* Organization chart */
    .profil-org-chart {
        position: relative;
        padding: clamp(1.5rem, 1.2rem + 1vw, 2.25rem);
        border-radius: var(--ps-radius);
        background: #ffffff;
        border: 1px solid rgba(8, 28, 58, 0.1);
        box-shadow: var(--ps-shadow);
        content-visibility: auto;
        contain-intrinsic-size: auto 320px;
    }
    .profil-org-chart::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: inherit;
        padding: 1px;
        background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(59,130,246,0.2), rgba(16,185,129,0.15));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
    }
    .profil-org-chart__tree {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0;
    }
    .profil-org-chart__apex {
        width: min(100%, 680px);
        z-index: 2;
    }
    .profil-org-chart__chief {
        position: relative;
        border-radius: 16px;
        border: 1px solid rgba(8, 28, 58, 0.12);
        box-shadow: var(--ps-shadow);
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .profil-org-chart__chief:hover {
        border-color: rgba(37, 99, 235, 0.28);
        box-shadow: var(--ps-shadow-hover);
    }
    .profil-org-chart__chief-inner {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.85rem;
        padding: 1.35rem 1.5rem;
        border-radius: 15px;
        background: linear-gradient(165deg, var(--ps-navy) 0%, var(--ps-navy-soft) 100%);
        overflow: hidden;
        text-align: center;
    }
    .profil-org-chart__chief-body {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        text-align: center;
    }
    .profil-org-chart__chief-inner::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #2563eb, #38bdf8, #10b981);
        opacity: 0.9;
    }
    .profil-org-chart__chief-icon {
        flex-shrink: 0;
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--ps-navy);
        background: linear-gradient(145deg, #fef3c7, #fbbf24);
        border: 1px solid rgba(255, 255, 255, 0.35);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    .profil-org-chart__chief-icon svg {
        width: 1.5rem;
        height: 1.5rem;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .profil-org-chart__chief-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 0.4rem;
        padding: 0.22rem 0.6rem;
        border-radius: 999px;
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #e2e8f0;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.16);
    }
    .profil-org-chart__status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--sg-success, #10b981);
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
    }
    .profil-org-chart__chief-title {
        margin: 0;
        width: 100%;
        text-align: center;
        font-size: clamp(1.1rem, 1rem + 0.4vw, 1.35rem);
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.25;
        color: #f8fafc;
    }
    .profil-org-chart__connector-v {
        width: 2px;
        height: 28px;
        margin: 0.5rem 0;
        border-radius: 999px;
        background: linear-gradient(180deg, #94a3b8, #2563eb);
    }
    .profil-org-chart__connector-h {
        width: min(92%, 600px);
        height: 2px;
        margin-bottom: 0.5rem;
        border-radius: 999px;
        background: linear-gradient(90deg, #cbd5e1, #2563eb 50%, #cbd5e1);
    }
    .profil-org-chart__branches {
        width: min(100%, 920px);
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem;
    }
    .profil-org-chart__node {
        position: relative;
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.15rem 1.2rem;
        min-height: 104px;
        border-radius: 14px;
        background: #ffffff;
        border: 1px solid rgba(8, 28, 58, 0.1);
        box-shadow: 0 4px 16px rgba(8, 28, 58, 0.05);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        overflow: hidden;
    }
    .profil-org-chart__node::before {
        content: "";
        position: absolute;
        top: -24px;
        left: 50%;
        width: 2px;
        height: 24px;
        transform: translateX(-50%);
        background: linear-gradient(180deg, rgba(148, 163, 184, 0.5), #2563eb);
    }
    .profil-org-chart__node::after {
        display: none;
    }
    .profil-org-chart__node:hover {
        border-color: rgba(37, 99, 235, 0.22);
        box-shadow: var(--ps-shadow-hover);
    }
    .profil-org-chart__node--fungsional {
        background: linear-gradient(145deg, rgba(245, 243, 255, 0.9), rgba(255, 255, 255, 0.75));
    }
    .profil-org-chart__node--pelaksana {
        background: linear-gradient(145deg, rgba(236, 253, 245, 0.9), rgba(255, 255, 255, 0.75));
    }
    .profil-org-chart__node-icon {
        flex-shrink: 0;
        width: 3rem;
        height: 3rem;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    .profil-org-chart__node--fungsional .profil-org-chart__node-icon {
        color: #5b21b6;
        background: linear-gradient(145deg, #ede9fe, #c4b5fd);
        box-shadow: 0 6px 18px rgba(124, 58, 237, 0.22);
    }
    .profil-org-chart__node--pelaksana .profil-org-chart__node-icon {
        color: #047857;
        background: linear-gradient(145deg, #d1fae5, #6ee7b7);
        box-shadow: 0 6px 18px rgba(5, 150, 105, 0.22);
    }
    .profil-org-chart__node-meta {
        display: block;
        margin-bottom: 0.2rem;
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--ps-muted);
    }
    .profil-org-chart__node-label {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.3;
        color: var(--ps-navy);
    }
    .profil-structure__text-block {
        padding: 1.25rem 1.35rem;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.55);
        border: 1px solid rgba(148, 163, 184, 0.15);
        line-height: 1.75;
        color: var(--ps-text);
        font-size: 0.95rem;
    }
    .profil-structure__img-wrap {
        margin-top: 1rem;
    }
    .profil-structure__img-wrap img {
        border-radius: 16px;
        border: 1px solid rgba(148, 163, 184, 0.2);
        box-shadow: var(--ps-shadow);
    }

    /* Personnel directory */
    .profil-personnel__toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .profil-personnel__add-btn {
        padding: 0.55rem 1.15rem;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 600;
        border: none;
        color: #fff;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35);
        transition: transform 0.25s var(--ps-ease), box-shadow 0.25s ease;
    }
    .profil-personnel__add-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(37, 99, 235, 0.42);
        color: #fff;
    }
    .profil-personnel__grid--staff {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1.25rem;
        align-items: stretch;
    }
    @media (min-width: 576px) {
        .profil-personnel__grid--staff {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 992px) {
        .profil-personnel__grid--staff {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.35rem;
        }
    }
    @media (min-width: 1200px) {
        .profil-personnel__grid--staff {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1.5rem;
        }
    }
    .profil-person-exec {
        position: relative;
        height: 100%;
        border-radius: var(--ps-radius);
        border: 1px solid rgba(8, 28, 58, 0.1);
        background: #ffffff;
        box-shadow: 0 4px 18px rgba(8, 28, 58, 0.06);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        overflow: hidden;
    }
    .profil-person-exec::after {
        display: none;
    }
    .profil-person-exec:hover {
        border-color: rgba(37, 99, 235, 0.2);
        box-shadow: var(--ps-shadow-hover);
    }
    .profil-person-exec__inner {
        height: 100%;
        display: flex;
        flex-direction: column;
        border-radius: calc(var(--ps-radius) - 1px);
        background: #ffffff;
        overflow: hidden;
    }
    .profil-person-exec--chief {
        border-color: rgba(8, 28, 58, 0.14);
        box-shadow: var(--ps-shadow);
    }
    .profil-person-exec--chief .profil-person-exec__inner {
        flex-direction: row;
        align-items: stretch;
        background: linear-gradient(165deg, #f8fafc 0%, #ffffff 55%);
    }
    .profil-person-exec--staff .profil-person-exec__inner {
        min-height: 100%;
    }
    .profil-person-exec--staff .profil-person-exec__body {
        min-height: 7.5rem;
    }
    .profil-person-exec__photo {
        position: relative;
        width: 100%;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        aspect-ratio: 3 / 4;
        min-height: 200px;
        max-height: 280px;
        overflow: hidden;
        background: #f1f5f9;
    }
    .profil-person-exec--staff .profil-person-exec__photo {
        aspect-ratio: 3 / 4;
        min-height: 220px;
        max-height: 300px;
    }
    .profil-person-exec--chief .profil-person-exec__photo {
        width: 100%;
        max-width: none;
        min-height: 220px;
        max-height: 320px;
        flex-shrink: 0;
        border-radius: 0;
        aspect-ratio: 3 / 4;
    }
    @media (min-width: 768px) {
        .profil-person-exec--chief .profil-person-exec__inner {
            flex-direction: row !important;
            align-items: stretch;
        }
        .profil-person-exec--chief .profil-person-exec__photo {
            width: min(240px, 38%);
            max-width: 240px;
            min-height: 240px;
            max-height: 320px;
            aspect-ratio: 3 / 4;
        }
    }
    .profil-person-exec__photo-frame {
        position: absolute;
        inset: 0;
        pointer-events: none;
        border: 1px solid rgba(148, 163, 184, 0.25);
        box-shadow: none;
    }
    .profil-person-exec__photo img,
    .profil-person-exec__photo-img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
        object-position: center top;
        display: block;
    }
    .profil-person-exec__photo-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 2.5rem;
    }
    .profil-person-exec__body {
        padding: 1.15rem 1.2rem 1.2rem;
        text-align: center;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .profil-person-exec--chief .profil-person-exec__body {
        text-align: left;
        justify-content: center;
        padding: 1.35rem 1.5rem;
    }
    .profil-person-exec__rank {
        display: inline-block;
        margin-bottom: 0.45rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #1e3a5f;
        background: linear-gradient(135deg, #fef9c3, #fde68a);
        border: 1px solid rgba(180, 83, 9, 0.2);
    }
    .profil-person-exec__name {
        margin: 0 0 0.35rem;
        font-size: clamp(0.95rem, 0.9rem + 0.15vw, 1.05rem);
        font-weight: 800;
        line-height: 1.3;
        color: var(--ps-navy);
        letter-spacing: -0.02em;
    }
    .profil-person-exec--chief .profil-person-exec__name {
        font-size: clamp(1.1rem, 1rem + 0.35vw, 1.28rem);
    }
    .profil-person-exec__nip {
        margin: 0 0 0.4rem;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #334155;
        letter-spacing: 0.02em;
        font-variant-numeric: tabular-nums;
        line-height: 1.45;
    }
    .profil-person-exec__role {
        margin: 0 0 0.75rem;
        font-size: 0.8125rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-transform: none;
        color: #1e293b;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .profil-person-exec__actions {
        display: flex;
        gap: 0.5rem;
        margin-top: auto;
        justify-content: center;
    }
    .profil-person-exec--chief .profil-person-exec__actions {
        justify-content: flex-start;
    }
    .profil-person-exec__action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        min-width: 2.25rem;
        height: 2.25rem;
        padding: 0 0.65rem;
        border-radius: 10px;
        font-size: 0.72rem;
        font-weight: 600;
        border: 1px solid rgba(148, 163, 184, 0.35);
        background: rgba(255, 255, 255, 0.65);
        color: var(--ps-navy-soft);
        cursor: pointer;
        text-decoration: none;
        transition: border-color 0.25s ease, box-shadow 0.25s ease, color 0.25s ease, background 0.25s ease;
    }
    .profil-person-exec__action svg {
        width: 0.95rem;
        height: 0.95rem;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
        flex-shrink: 0;
    }
    .profil-person-exec__action:hover {
        border-color: rgba(37, 99, 235, 0.45);
        color: #1d4ed8;
        background: rgba(239, 246, 255, 0.95);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }
    .profil-person-exec__action--danger:hover {
        border-color: rgba(220, 38, 38, 0.4);
        color: #b91c1c;
        background: rgba(254, 242, 242, 0.95);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    .profil-person-exec__action-form {
        margin: 0;
    }
    .personnel-chief-stack .row {
        justify-content: flex-start;
    }
    .profil-personnel__subsection {
        margin-top: 2rem;
    }
    .profil-personnel__empty {
        padding: 2rem;
        text-align: center;
        border-radius: var(--ps-radius);
        background: rgba(255, 255, 255, 0.5);
        color: var(--ps-muted);
        font-size: 0.92rem;
    }
    @media (max-width: 767.98px) {
        .profil-org-chart__branches {
            grid-template-columns: 1fr;
        }
        .profil-org-chart__node::before {
            display: none;
        }
        .profil-org-chart__connector-h {
            width: 3px;
            height: 24px;
            background: linear-gradient(180deg, #3b82f6, #8b5cf6);
        }
        .profil-person-exec--chief .profil-person-exec__inner {
            flex-direction: column;
        }
        .profil-person-exec--chief .profil-person-exec__photo {
            width: 100%;
            max-width: none;
            min-height: 200px;
            max-height: 360px;
        }
        .profil-person-exec--staff .profil-person-exec__photo {
            min-height: 200px;
            max-height: 360px;
        }
        .profil-person-exec--chief .profil-person-exec__body {
            text-align: center;
        }
        .profil-person-exec--chief .profil-person-exec__actions {
            justify-content: center;
        }
        .profil-structure {
            margin-left: 0;
            margin-right: 0;
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .profil-structure,
        .profil-org-chart {
            content-visibility: visible;
        }
    }
