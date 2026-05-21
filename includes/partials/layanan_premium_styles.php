<?php
declare(strict_types=1);
?>
<style>
.page-layanan-premium {
    --layanan-glass: rgba(255, 255, 255, 0.72);
    --layanan-radius: 15px;
}
.page-layanan-premium .site-main {
    background: linear-gradient(180deg, #f8fbff 0%, #f1f5f9 42%, #ffffff 100%);
}
.layanan-premium-hero {
    position: relative;
    overflow: hidden;
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, 0.55);
    background:
        radial-gradient(520px 180px at 0% 0%, rgba(37, 99, 235, 0.12), transparent 70%),
        radial-gradient(480px 180px at 100% 0%, rgba(14, 165, 233, 0.1), transparent 70%),
        linear-gradient(135deg, rgba(255, 255, 255, 0.92) 0%, rgba(248, 251, 255, 0.88) 100%);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
}
.layanan-premium-hero::after {
    content: "";
    display: block;
    height: 3px;
    background: linear-gradient(90deg, #1d4ed8, #0ea5e9, #22c55e);
}
.layanan-premium-hero__inner {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    padding: clamp(1.35rem, 3vw, 2rem) clamp(1.2rem, 3vw, 1.85rem);
}
.layanan-premium-hero__mark {
    width: 3.1rem;
    height: 3.1rem;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #1d4ed8, #38bdf8);
    color: #fff;
    font-size: 1.3rem;
    box-shadow: 0 8px 22px rgba(29, 78, 216, 0.35);
}
.layanan-premium-hero__title {
    margin: 0;
    font-family: 'Public Sans', 'Inter', system-ui, sans-serif;
    font-weight: 800;
    font-size: clamp(1.25rem, 1rem + 0.9vw, 1.75rem);
    letter-spacing: -0.02em;
    color: #0f2748;
}
.layanan-premium-hero__lead {
    margin: 0.35rem 0 0;
    font-size: 0.92rem;
    color: #64748b;
    max-width: 42rem;
}
.layanan-premium-section {
    border-radius: 18px;
    border: 1px solid rgba(226, 232, 240, 0.9);
    background: rgba(255, 255, 255, 0.55);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    box-shadow: 0 10px 32px rgba(15, 23, 42, 0.06);
    padding: 1.15rem 1.15rem 1.35rem;
}
@media (min-width: 992px) {
    .layanan-premium-section {
        padding: 1.25rem 1.45rem 1.5rem;
    }
}
.layanan-premium-section__head {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    margin-bottom: 1.15rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.85);
}
.layanan-premium-section__icon {
    width: 2.85rem;
    height: 2.85rem;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.15rem;
    flex-shrink: 0;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.16);
}
.layanan-premium-section__title {
    margin: 0;
    font-family: 'Public Sans', 'Inter', system-ui, sans-serif;
    font-weight: 800;
    font-size: clamp(1.05rem, 0.9rem + 0.5vw, 1.35rem);
    color: #071427;
    letter-spacing: -0.02em;
}
.layanan-premium-section--kelembagaan .layanan-premium-section__icon {
    background: linear-gradient(145deg, #3b82f6, #1d4ed8);
}
.layanan-premium-section--pelayanan .layanan-premium-section__icon {
    background: linear-gradient(145deg, #14b8a6, #0f766e);
}
.layanan-premium-section--sakip .layanan-premium-section__icon {
    background: linear-gradient(145deg, #8b5cf6, #6d28d9);
}
.layanan-premium-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 1.15rem;
    align-items: stretch;
}
.layanan-premium-grid > [data-aos] {
    display: flex;
    min-height: 0;
}
.layanan-premium-grid > [data-aos] .layanan-premium-card {
    width: 100%;
}
@media (min-width: 768px) {
    .layanan-premium-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (min-width: 1200px) {
    .layanan-premium-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem;
    }
}
.layanan-premium-card {
    --layanan-card-glow: rgba(37, 99, 235, 0.32);
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 100%;
    padding: 1rem 1.05rem 1.1rem;
    border-radius: var(--layanan-radius);
    border: 1px solid rgba(255, 255, 255, 0.65);
    background: var(--layanan-glass);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    box-shadow: 0 8px 26px rgba(15, 23, 42, 0.07);
    transition: transform 0.32s ease, box-shadow 0.35s ease, border-color 0.3s ease;
    overflow: hidden;
}
.layanan-premium-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 18px 42px var(--layanan-card-glow);
    border-color: rgba(255, 255, 255, 0.9);
}
.layanan-premium-card--kelembagaan { --layanan-card-glow: rgba(37, 99, 235, 0.38); }
.layanan-premium-card--pelayanan { --layanan-card-glow: rgba(13, 148, 136, 0.38); }
.layanan-premium-card--sakip { --layanan-card-glow: rgba(124, 58, 237, 0.38); }
.layanan-premium-card__status {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    z-index: 3;
    padding: 0.28rem 0.62rem;
    border-radius: 999px;
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    line-height: 1.2;
    border: 1px solid transparent;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.1);
}
.layanan-premium-card__status--digital {
    color: #065f46;
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    border-color: rgba(16, 185, 129, 0.35);
}
.layanan-premium-card__status--manual {
    color: #7c2d12;
    background: linear-gradient(135deg, #ffedd5, #fed7aa);
    border-color: rgba(234, 88, 12, 0.28);
}
.layanan-premium-card__headline {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    gap: 0.4rem 0.55rem;
    margin: 0 2.5rem 0.55rem 0;
    padding-right: 0.25rem;
}
.layanan-premium-card__title {
    margin: 0;
    flex: 1 1 8rem;
    min-width: 0;
    font-family: 'Public Sans', 'Inter', system-ui, sans-serif;
    font-size: clamp(1rem, 0.92rem + 0.35vw, 1.2rem);
    font-weight: 800;
    line-height: 1.3;
    letter-spacing: -0.02em;
    color: #0f172a;
}
.layanan-premium-card__pin {
    display: inline-flex;
    align-items: center;
    padding: 0.18rem 0.5rem;
    border-radius: 6px;
    font-size: 0.64rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #422006;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border: 1px solid rgba(180, 83, 9, 0.3);
}
.layanan-premium-card__desc {
    margin: 0 0 0.75rem;
    font-size: 0.86rem;
    line-height: 1.6;
    color: #64748b;
    flex-grow: 0;
}
.layanan-premium-card__lower {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 0;
    margin-top: auto;
}
.layanan-premium-card__media {
    position: relative;
    border-radius: var(--layanan-radius);
    overflow: hidden;
    background: linear-gradient(155deg, #e8eef6, #f1f5f9);
    aspect-ratio: 16 / 10;
    margin-top: auto;
}
.layanan-premium-card__media-link {
    display: block;
    width: 100%;
    height: 100%;
    text-decoration: none;
}
.layanan-premium-card__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: var(--layanan-radius);
    transition: transform 0.45s ease, filter 0.35s ease;
}
.layanan-premium-card:hover .layanan-premium-card__img {
    transform: scale(1.04);
}
.layanan-premium-card__media-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: 2.25rem;
}
.layanan-premium-card__fabs {
    position: absolute;
    right: 0.55rem;
    bottom: 0.55rem;
    z-index: 4;
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    justify-content: flex-end;
}
.layanan-premium-fab {
    width: 2.15rem;
    height: 2.15rem;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    color: #fff;
    text-decoration: none;
    border: 1px solid rgba(255, 255, 255, 0.35);
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.22);
    transition: transform 0.22s ease, box-shadow 0.28s ease;
}
.layanan-premium-fab:hover {
    color: #fff;
    transform: translateY(-2px) scale(1.06);
}
.layanan-premium-fab--image {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
}
.layanan-premium-fab--doc {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}
.layanan-premium-fab--link {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
}
.layanan-premium-fab--primary {
    background: linear-gradient(135deg, #003366, #1d6fd4, #38bdf8);
    box-shadow: 0 6px 20px rgba(29, 111, 212, 0.4);
}
.layanan-premium-empty {
    text-align: center;
    padding: 2.5rem 1.25rem;
    border-radius: var(--layanan-radius);
    border: 1px dashed rgba(148, 163, 184, 0.55);
    background: rgba(255, 255, 255, 0.45);
}
.layanan-premium-empty__svg {
    width: min(100%, 200px);
    height: auto;
    margin: 0 auto 1rem;
    opacity: 0.9;
}
.layanan-premium-empty__title {
    margin: 0 0 0.35rem;
    font-weight: 800;
    font-size: 1.05rem;
    color: #334155;
}
.layanan-premium-empty__text {
    margin: 0;
    font-size: 0.88rem;
    color: #64748b;
}
@media (prefers-reduced-motion: reduce) {
    .layanan-premium-card,
    .layanan-premium-card__img,
    .layanan-premium-fab {
        transition: none;
    }
    .layanan-premium-card:hover {
        transform: none;
    }
    .layanan-premium-card:hover .layanan-premium-card__img {
        transform: none;
    }
}
</style>
