<?php
?>
<style>
.page-publikasi-premium {
    --pub-royal: #1e3a8a;
    --pub-emerald: #059669;
    --pub-gold: #d4a853;
    --pub-radius: 16px;
    --pub-layout-max: 1200px;
    --pub-float: 0 14px 42px rgba(15, 23, 42, 0.08), 0 4px 14px rgba(15, 23, 42, 0.04);
    --pub-card-shadow: 0 12px 36px rgba(15, 23, 42, 0.07), 0 2px 8px rgba(15, 23, 42, 0.04);
    --pub-card-shadow-hover: 0 20px 48px rgba(15, 23, 42, 0.11), 0 6px 18px rgba(15, 23, 42, 0.06);
    background-color: #f8f9fa;
}
.page-publikasi-premium .container.site-main {
    max-width: var(--pub-layout-max);
}
.page-publikasi-premium .site-main {
    background: transparent;
}
.page-publikasi-premium .pub-page-hero {
    padding: 1.35rem 1.25rem;
    margin-bottom: 0;
    border-radius: var(--pub-radius);
    border: 1px solid rgba(226, 232, 240, 0.9);
    background: #ffffff;
    box-shadow: var(--pub-float);
}
.page-publikasi-premium .pub-page-hero__title {
    margin: 0;
    font-size: clamp(1.35rem, 1.1rem + 0.8vw, 1.85rem);
    font-weight: 800;
    color: var(--pub-royal);
    letter-spacing: 0.02em;
}
.page-publikasi-premium .pub-page-hero__lead {
    margin: 0.35rem 0 0;
    font-size: 0.92rem;
    line-height: 1.65;
    letter-spacing: 0.03em;
    color: #6b7280;
}
.page-publikasi-premium .pub-float-panel {
    border-radius: var(--pub-radius);
    box-shadow: var(--pub-float);
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.85);
    padding: 1.15rem;
}
@media (min-width: 768px) {
    .page-publikasi-premium .pub-float-panel {
        padding: 1.35rem 1.4rem 1.5rem;
    }
}
.pub-pi-grid-shell {
    width: 100%;
}
.pub-pi-grid {
    display: grid;
    gap: 1.25rem;
    align-items: stretch;
    grid-template-columns: minmax(0, 1fr);
}
@media (min-width: 768px) {
    .pub-pi-grid--duo,
    .pub-pi-grid--trio {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (min-width: 992px) {
    .pub-pi-grid--trio {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
.pub-pi-grid__cell {
    display: flex;
    min-height: 0;
}
.pub-pi-grid__cell .pub-pi-card-link {
    width: 100%;
}
.pub-pi-card-link { display: flex; width: 100%; height: 100%; text-decoration: none; color: inherit; }
.pub-pi-card {
    display: flex; flex-direction: column; width: 100%; height: 100%;
    border-radius: var(--pub-radius); overflow: hidden;
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.9);
    box-shadow: var(--pub-card-shadow);
    transition: transform 0.35s ease, box-shadow 0.38s ease;
}
.pub-pi-card-link:hover .pub-pi-card { transform: translateY(-5px); box-shadow: var(--pub-card-shadow-hover); }
.pub-pi-grid--solo .pub-pi-card--solo {
    border-width: 1px;
    border-color: rgba(30, 58, 138, 0.18);
    box-shadow: 0 18px 50px rgba(30, 58, 138, 0.12), 0 6px 20px rgba(15, 23, 42, 0.06);
}
.pub-pi-grid--solo .pub-pi-card--solo .pub-pi-card__media {
    aspect-ratio: 21 / 9;
    min-height: 220px;
}
@media (min-width: 768px) {
    .pub-pi-grid--solo .pub-pi-card--solo .pub-pi-card__media {
        min-height: 280px;
    }
}
.pub-pi-grid--solo .pub-pi-card--solo .pub-pi-card__title-overlay {
    font-size: clamp(1.2rem, 1rem + 1.2vw, 1.75rem);
    line-height: 1.3;
}
.pub-pi-grid--solo .pub-pi-card--solo .pub-pi-card__excerpt {
    font-size: 0.95rem;
    line-height: 1.65;
    color: #475569;
}
.pub-pi-grid--solo .pub-pi-card--solo .pub-pi-card__glass {
    padding: 1.15rem 1.35rem 1.35rem;
}
.pub-pi-grid--solo .pub-pi-card-link:hover .pub-pi-card--solo {
    transform: translateY(-6px);
    box-shadow: 0 24px 56px rgba(30, 58, 138, 0.16), 0 8px 24px rgba(15, 23, 42, 0.08);
}
.pub-pi-card--featured { border-color: rgba(212, 168, 83, 0.4); }
.pub-pi-card__media { position: relative; aspect-ratio: 16/10; overflow: hidden; background: #e2e8f0; }
.pub-pi-card__media::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 2;
    pointer-events: none;
    opacity: 0;
    background: rgba(15, 23, 42, 0.18);
    backdrop-filter: blur(0);
    -webkit-backdrop-filter: blur(0);
    transition: opacity 0.35s ease, backdrop-filter 0.4s ease;
}
.pub-pi-card-link:hover .pub-pi-card__media::before {
    opacity: 1;
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
}
.pub-pi-card__img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.55s ease, filter 0.4s ease; }
.pub-pi-card-link:hover .pub-pi-card__img { transform: scale(1.06); filter: blur(6px); }
.pub-pi-card__img--placeholder { display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: #94a3b8; }
.pub-pi-card__media-gradient {
    position: absolute; inset: 0; z-index: 1; pointer-events: none;
    background: linear-gradient(to top, rgba(0, 20, 45, 0.92) 0%, rgba(15, 23, 42, 0.45) 40%, transparent 70%);
}
.pub-pi-card__badge {
    position: absolute; top: 0.85rem; left: 0.85rem; z-index: 4;
    padding: 0.28rem 0.65rem; border-radius: 999px;
    font-size: 0.62rem; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase;
}
.pub-pi-card__badge--penting,
.pub-pi-card__badge--info,
.pub-pi-card__badge--berita,
.pub-pi-card__badge--pengumuman,
.pub-pi-card__badge--utama {
    background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
    color: #fff;
    border: 1px solid rgba(127, 29, 29, 0.45);
    box-shadow: 0 2px 10px rgba(185, 28, 28, 0.35);
}
.pub-pi-card__badge--utama {
    letter-spacing: 0.08em;
}
.pub-pi-card__overlay { position: absolute; left: 0; right: 0; bottom: 0; z-index: 4; padding: 1rem 1.1rem; }
.pub-pi-card__title-overlay { margin: 0; font-size: 1.05rem; font-weight: 800; line-height: 1.35; color: #fff; text-shadow: 0 2px 14px rgba(0,0,0,0.4); }
.pub-pi-card__cta {
    display: inline-flex; align-items: center; gap: 0.4rem; margin-top: 0.5rem;
    padding: 0.45rem 0.85rem; border-radius: 999px; font-size: 0.78rem; font-weight: 700;
    color: var(--pub-royal); background: rgba(255,255,255,0.95);
    opacity: 0; transform: translateY(14px); transition: opacity 0.35s ease, transform 0.4s ease;
}
.pub-pi-card-link:hover .pub-pi-card__cta { opacity: 1; transform: translateY(0); color: var(--pub-emerald); }
.pub-pi-card__glass { padding: 1rem 1.15rem 1.2rem; border-top: 1px solid rgba(255,255,255,0.4); }
.pub-pi-card__meta {
    margin: 0 0 0.4rem;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #b91c1c;
}
.pub-pi-card__meta i { color: #dc2626; }
.pub-pi-card__meta-cat {
    display: block;
    margin: 0 0 0.35rem;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #b91c1c;
}
.pub-pi-card__excerpt { margin: 0; font-size: 0.86rem; line-height: 1.65; letter-spacing: 0.02em; color: #6b7280; }
.pub-pi-swiper__pagination { bottom: 0.25rem !important; }
.pub-pi-swiper__pagination .swiper-pagination-bullet { background: rgba(30,58,138,0.25); opacity: 1; }
.pub-pi-swiper__pagination .swiper-pagination-bullet-active { width: 22px; border-radius: 999px; background: linear-gradient(90deg, var(--pub-royal), var(--pub-emerald)); }
.pub-pi-swiper__nav {
    width: 2.4rem; height: 2.4rem; border-radius: 50%;
    background: rgba(255,255,255,0.95); border: 1px solid rgba(30,58,138,0.12);
    box-shadow: var(--pub-float); color: var(--pub-royal);
}
.pub-galeri-grid {
    display: grid;
    gap: 1.25rem;
    align-items: stretch;
    grid-template-columns: minmax(0, 1fr);
}
@media (min-width: 768px) {
    .pub-galeri-grid--duo,
    .pub-galeri-grid--trio {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (min-width: 992px) {
    .pub-galeri-grid--trio {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
.pub-galeri-grid__item {
    display: block;
    border-radius: var(--pub-radius);
    overflow: hidden;
    text-decoration: none;
    box-shadow: var(--pub-card-shadow);
    transition: transform 0.35s ease, box-shadow 0.38s ease;
}
.pub-galeri-grid__item:hover {
    transform: translateY(-5px);
    box-shadow: var(--pub-card-shadow-hover);
}
.pub-galeri-grid__frame {
    position: relative;
    display: block;
    overflow: hidden;
    border-radius: var(--pub-radius);
    background: #e2e8f0;
    aspect-ratio: 4 / 3;
}
.pub-galeri-grid--solo .pub-galeri-grid__item--solo .pub-galeri-grid__frame {
    aspect-ratio: 21 / 9;
    min-height: 240px;
}
@media (min-width: 768px) {
    .pub-galeri-grid--solo .pub-galeri-grid__item--solo .pub-galeri-grid__frame {
        min-height: 320px;
    }
}
.pub-galeri-grid__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    border-radius: var(--pub-radius);
    transition: transform 0.5s ease, filter 0.38s ease;
}
.pub-galeri-grid__item:hover .pub-galeri-grid__img {
    transform: scale(1.05);
    filter: blur(6px);
}
.pub-galeri-grid__frame::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 1;
    pointer-events: none;
    opacity: 0;
    background: rgba(255, 255, 255, 0.06);
    backdrop-filter: blur(0);
    -webkit-backdrop-filter: blur(0);
    transition: opacity 0.32s ease, backdrop-filter 0.38s ease;
}
.pub-galeri-grid__item:hover .pub-galeri-grid__frame::before {
    opacity: 1;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}
.pub-galeri-grid__glass {
    position: absolute;
    inset: 0;
    z-index: 2;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 1.1rem;
    background: rgba(15, 23, 42, 0.2);
    backdrop-filter: blur(0);
    -webkit-backdrop-filter: blur(0);
    opacity: 0;
    transition: opacity 0.32s ease, backdrop-filter 0.38s ease, background 0.32s ease;
}
.pub-galeri-grid__item:hover .pub-galeri-grid__glass {
    opacity: 1;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
}
.pub-galeri-grid--solo .pub-galeri-grid__item--solo .pub-galeri-grid__title {
    font-size: clamp(1.05rem, 0.9rem + 0.8vw, 1.35rem);
}
.pub-galeri-grid__title {
    display: block;
    font-size: 0.96rem;
    font-weight: 800;
    color: #fff;
    line-height: 1.4;
    margin-bottom: 0.25rem;
    text-shadow: 0 2px 12px rgba(0, 0, 0, 0.35);
}
.pub-galeri-grid__date {
    display: block;
    font-size: 0.78rem;
    color: rgba(255, 255, 255, 0.92);
    letter-spacing: 0.04em;
}
.pub-galeri-grid__zoom {
    position: absolute;
    top: 0.85rem;
    right: 0.85rem;
    z-index: 3;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--pub-royal);
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.18);
    opacity: 0;
    transform: scale(0.88);
    transition: opacity 0.28s ease, transform 0.3s ease;
}
.pub-galeri-grid__item:hover .pub-galeri-grid__zoom {
    opacity: 1;
    transform: scale(1);
}
.pub-galeri-grid--solo .pub-galeri-grid__item--solo {
    box-shadow: 0 18px 50px rgba(30, 58, 138, 0.12), 0 6px 20px rgba(15, 23, 42, 0.06);
}
.pub-galeri-grid--solo .pub-galeri-grid__item--solo:hover {
    box-shadow: 0 24px 56px rgba(30, 58, 138, 0.16), 0 8px 24px rgba(15, 23, 42, 0.08);
}
.pi-search-highlight { background-color: #fff3a3; padding: 0 0.1em; border-radius: 0.2em; }
.page-publikasi-premium .berita-list-section,
.page-publikasi-premium .section-spacing {
    margin-bottom: 2.5rem;
}
@media (prefers-reduced-motion: reduce) {
    .pub-pi-card-link:hover .pub-pi-card__img, .pub-galeri-grid__item:hover .pub-galeri-grid__img,
    .pub-pi-card-link:hover .pub-pi-card, .pub-galeri-grid__item:hover { transform: none; filter: none; }
    .pub-pi-card-link:hover .pub-pi-card__media::before,
    .pub-galeri-grid__item:hover .pub-galeri-grid__frame::before { opacity: 0; backdrop-filter: none; }
    .pub-galeri-grid__item:hover .pub-galeri-grid__glass { backdrop-filter: none; }
    .pub-pi-card__cta { opacity: 1; transform: none; }
}

/* —— Berita: modern news portal —— */
.page-berita-premium {
    --np-font-display: 'Public Sans', var(--font-sans);
    --np-ink: #0f172a;
    --np-ink-muted: #64748b;
    --np-ink-soft: #475569;
    --np-surface: #ffffff;
    --np-border: rgba(226, 232, 240, 0.95);
    --np-shadow: 0 4px 24px rgba(15, 23, 42, 0.06), 0 1px 3px rgba(15, 23, 42, 0.04);
    --np-shadow-hover: 0 20px 48px rgba(15, 23, 42, 0.12), 0 8px 20px rgba(15, 23, 42, 0.06);
    --np-radius: 18px;
    --np-radius-sm: 12px;
    --np-transition: 0.38s cubic-bezier(0.22, 1, 0.36, 1);
    --np-berita: #1d4ed8;
    --np-pengumuman: #b45309;
    --np-featured: #7c3aed;
}
.page-berita-premium .news-portal-section {
    margin-top: 0.5rem;
    margin-bottom: 3rem;
}
.page-berita-premium .sg-portal-toolbar {
    margin-bottom: 1.75rem;
}
.page-berita-premium .news-portal {
    display: flex;
    flex-direction: column;
    gap: 2.75rem;
}

/* Hero / featured */
.page-berita-premium .np-hero__label {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    margin-bottom: 1rem;
    font-family: var(--np-font-display);
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--np-berita);
}
.page-berita-premium .np-hero__label-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--np-berita), #38bdf8);
    box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.15);
}
.page-berita-premium .np-hero__link {
    display: block;
    text-decoration: none;
    color: inherit;
    outline: none;
}
.page-berita-premium .np-hero__link:focus-visible .np-hero__card {
    outline: 3px solid #38bdf8;
    outline-offset: 3px;
}
.page-berita-premium .np-hero__card {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    border-radius: var(--np-radius);
    overflow: hidden;
    background: var(--np-surface);
    border: 1px solid var(--np-border);
    box-shadow: var(--np-shadow);
    transition: transform var(--np-transition), box-shadow var(--np-transition);
}
@media (min-width: 768px) {
    .page-berita-premium .np-hero__card {
        grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
        min-height: 320px;
    }
}
.page-berita-premium .np-hero__card--featured {
    border-color: rgba(124, 58, 237, 0.22);
    box-shadow: 0 8px 32px rgba(124, 58, 237, 0.08), var(--np-shadow);
}
.page-berita-premium .np-hero__link:hover .np-hero__card {
    transform: translateY(-6px);
    box-shadow: var(--np-shadow-hover);
}
.page-berita-premium .np-hero__media {
    position: relative;
    aspect-ratio: 16 / 10;
    overflow: hidden;
    background: linear-gradient(145deg, #e2e8f0, #f1f5f9);
}
@media (min-width: 768px) {
    .page-berita-premium .np-hero__media {
        aspect-ratio: auto;
        min-height: 100%;
    }
}
.page-berita-premium .np-hero__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform var(--np-transition);
}
.page-berita-premium .np-hero__img--placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 220px;
    font-size: 3rem;
    color: #94a3b8;
}
.page-berita-premium .np-hero__link:hover .np-hero__img {
    transform: scale(1.05);
}
.page-berita-premium .np-hero__badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    z-index: 2;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #fff;
    background: linear-gradient(135deg, var(--np-berita), #2563eb);
    box-shadow: 0 4px 14px rgba(29, 78, 216, 0.35);
}
.page-berita-premium .np-card__badge--utama {
    background: linear-gradient(135deg, var(--np-featured), #6d28d9);
    box-shadow: 0 4px 14px rgba(124, 58, 237, 0.35);
}
.page-berita-premium .np-card__badge--pengumuman {
    background: linear-gradient(135deg, var(--np-pengumuman), #d97706);
    box-shadow: 0 4px 14px rgba(180, 83, 9, 0.32);
}
.page-berita-premium .np-hero__body {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 1.35rem 1.5rem 1.5rem;
    gap: 0.65rem;
}
@media (min-width: 768px) {
    .page-berita-premium .np-hero__body {
        padding: 2rem 2.25rem;
    }
}
.page-berita-premium .np-hero__meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem 1rem;
}
.page-berita-premium .np-hero__title {
    margin: 0;
    font-family: var(--np-font-display);
    font-size: clamp(1.35rem, 1.1rem + 1.2vw, 2rem);
    font-weight: 800;
    line-height: 1.25;
    letter-spacing: -0.02em;
    color: var(--np-ink);
}
.page-berita-premium .np-hero__excerpt {
    margin: 0;
    font-size: clamp(0.92rem, 0.88rem + 0.2vw, 1.02rem);
    line-height: 1.7;
    color: var(--np-ink-soft);
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.page-berita-premium .np-hero__cta {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    margin-top: 0.35rem;
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--np-berita);
    transition: gap var(--np-transition), color var(--np-transition);
}
.page-berita-premium .np-hero__link:hover .np-hero__cta {
    gap: 0.65rem;
    color: #1e40af;
}

/* Meta: category + date */
.page-berita-premium .np-card__cat {
    display: inline-flex;
    align-items: center;
    padding: 0.22rem 0.62rem;
    border-radius: 6px;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}
.page-berita-premium .np-card__cat--berita {
    color: var(--np-berita);
    background: rgba(29, 78, 216, 0.1);
}
.page-berita-premium .np-card__cat--pengumuman {
    color: var(--np-pengumuman);
    background: rgba(180, 83, 9, 0.12);
}
.page-berita-premium .np-card__date {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--np-ink-muted);
    letter-spacing: 0.02em;
}
.page-berita-premium .np-card__date i {
    font-size: 0.76rem;
    opacity: 0.85;
}
.page-berita-premium .np-card__meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.45rem 0.85rem;
    margin-bottom: 0.55rem;
}

/* Secondary grid */
.page-berita-premium .np-latest__head {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    justify-content: space-between;
    gap: 0.35rem 1rem;
    margin-bottom: 1.35rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid var(--np-border);
}
.page-berita-premium .np-latest__title {
    margin: 0;
    font-family: var(--np-font-display);
    font-size: 1.2rem;
    font-weight: 800;
    color: var(--np-ink);
    letter-spacing: -0.01em;
}
.page-berita-premium .np-latest__count {
    margin: 0;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--np-ink-muted);
}
.page-berita-premium .np-grid {
    display: grid;
    gap: 1.5rem;
    grid-template-columns: minmax(0, 1fr);
}
@media (min-width: 640px) {
    .page-berita-premium .np-grid--duo,
    .page-berita-premium .np-grid--multi {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (min-width: 1024px) {
    .page-berita-premium .np-grid--multi {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
.page-berita-premium .np-grid__cell {
    display: flex;
    min-height: 0;
}
.page-berita-premium .np-card__link {
    display: flex;
    width: 100%;
    text-decoration: none;
    color: inherit;
    outline: none;
}
.page-berita-premium .np-card__link:focus-visible .np-card {
    outline: 3px solid #38bdf8;
    outline-offset: 2px;
}
.page-berita-premium .np-card {
    display: flex;
    flex-direction: column;
    width: 100%;
    border-radius: var(--np-radius-sm);
    overflow: hidden;
    background: var(--np-surface);
    border: 1px solid var(--np-border);
    box-shadow: var(--np-shadow);
    transition: transform var(--np-transition), box-shadow var(--np-transition);
}
.page-berita-premium .np-card--featured {
    border-color: rgba(124, 58, 237, 0.2);
}
.page-berita-premium .np-card__link:hover .np-card {
    transform: translateY(-5px);
    box-shadow: var(--np-shadow-hover);
}
.page-berita-premium .np-card__media {
    position: relative;
    aspect-ratio: 3 / 2;
    overflow: hidden;
    background: linear-gradient(145deg, #e2e8f0, #f8fafc);
}
.page-berita-premium .np-card__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform var(--np-transition);
}
.page-berita-premium .np-card__img--placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.25rem;
    color: #94a3b8;
}
.page-berita-premium .np-card__link:hover .np-card__img {
    transform: scale(1.06);
}
.page-berita-premium .np-card__badge {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    z-index: 2;
    padding: 0.28rem 0.62rem;
    border-radius: 999px;
    font-size: 0.58rem;
    font-weight: 800;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    color: #fff;
    background: linear-gradient(135deg, var(--np-berita), #2563eb);
    box-shadow: 0 3px 10px rgba(29, 78, 216, 0.28);
}
.page-berita-premium .np-card__body {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    padding: 1.1rem 1.2rem 1.25rem;
    gap: 0;
}
.page-berita-premium .np-card__title {
    margin: 0 0 0.5rem;
    font-family: var(--np-font-display);
    font-size: 1.05rem;
    font-weight: 700;
    line-height: 1.35;
    letter-spacing: -0.01em;
    color: var(--np-ink);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.25s ease;
}
.page-berita-premium .np-card__link:hover .np-card__title {
    color: var(--np-berita);
}
.page-berita-premium .np-card__excerpt {
    margin: 0 0 0.85rem;
    flex-grow: 1;
    font-size: 0.875rem;
    line-height: 1.65;
    color: var(--np-ink-soft);
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.page-berita-premium .np-card__more {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    margin-top: auto;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--np-berita);
    transition: gap var(--np-transition);
}
.page-berita-premium .np-card__link:hover .np-card__more {
    gap: 0.55rem;
}
.page-berita-premium .np-card__more i {
    font-size: 0.72rem;
    transition: transform var(--np-transition);
}
.page-berita-premium .np-card__link:hover .np-card__more i {
    transform: translateX(3px);
}

/* Empty state */
.page-berita-premium .np-empty {
    text-align: center;
    padding: 3rem 1.5rem;
    border-radius: var(--np-radius);
    background: var(--np-surface);
    border: 1px dashed var(--np-border);
}
.page-berita-premium .np-empty__icon {
    font-size: 2.5rem;
    color: #94a3b8;
    margin-bottom: 1rem;
}
.page-berita-premium .np-empty__text {
    font-size: 0.95rem;
    color: var(--np-ink-muted);
}

.page-berita-premium .pi-search-highlight {
    background: #fef08a;
    padding: 0 0.12em;
    border-radius: 0.2em;
}

@media (prefers-reduced-motion: reduce) {
    .page-berita-premium .np-hero__link:hover .np-hero__card,
    .page-berita-premium .np-card__link:hover .np-card,
    .page-berita-premium .np-hero__link:hover .np-hero__img,
    .page-berita-premium .np-card__link:hover .np-card__img {
        transform: none;
    }
}

/* —— Galeri: modern interactive gallery —— */
.page-galeri-premium {
    --gl-radius: 16px;
    --gl-gap: 1.15rem;
    --gl-ink: #0f172a;
    --gl-muted: #64748b;
    --gl-accent: #1d4ed8;
    --gl-surface: #ffffff;
    --gl-border: rgba(226, 232, 240, 0.95);
    --gl-shadow: 0 8px 28px rgba(15, 23, 42, 0.08);
    --gl-shadow-hover: 0 22px 48px rgba(15, 23, 42, 0.14);
    --gl-transition: 0.4s cubic-bezier(0.22, 1, 0.36, 1);
}
.page-galeri-premium .galeri-portal-section {
    margin-bottom: 3rem;
    min-height: 12rem;
}
.page-galeri-premium .gl-portal,
.page-galeri-premium .gl-empty--page {
    opacity: 1 !important;
    transform: none !important;
    visibility: visible !important;
}

/* Toolbar */
.page-galeri-premium .gl-toolbar {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1.75rem;
    padding: 1.15rem 1.25rem;
    border-radius: var(--gl-radius);
    background: var(--gl-surface);
    border: 1px solid var(--gl-border);
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
}
@media (min-width: 768px) {
    .page-galeri-premium .gl-toolbar {
        flex-direction: row;
        flex-wrap: wrap;
        align-items: center;
        padding: 1.25rem 1.5rem;
    }
}
.page-galeri-premium .gl-toolbar__search {
    position: relative;
    flex: 1 1 220px;
    min-width: min(100%, 260px);
}
.page-galeri-premium .gl-toolbar__search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gl-muted);
    font-size: 0.9rem;
    pointer-events: none;
}
.page-galeri-premium .gl-toolbar__input {
    width: 100%;
    padding: 0.72rem 2.75rem 0.72rem 2.65rem;
    border-radius: 999px;
    border: 1px solid var(--gl-border);
    font-size: 0.92rem;
    color: var(--gl-ink);
    background: #f8fafc;
    transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
}
.page-galeri-premium .gl-toolbar__input:focus {
    outline: none;
    border-color: rgba(29, 78, 216, 0.45);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
}
.page-galeri-premium .gl-toolbar__clear {
    position: absolute;
    right: 0.45rem;
    top: 50%;
    transform: translateY(-50%);
    width: 2rem;
    height: 2rem;
    border: none;
    border-radius: 50%;
    background: transparent;
    color: var(--gl-muted);
    cursor: pointer;
    transition: color 0.2s ease, background 0.2s ease;
}
.page-galeri-premium .gl-toolbar__clear:hover {
    color: var(--gl-ink);
    background: rgba(15, 23, 42, 0.06);
}
.page-galeri-premium .gl-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    flex: 1 1 auto;
}
.page-galeri-premium .gl-filters__tab {
    padding: 0.45rem 1rem;
    border-radius: 999px;
    border: 1px solid var(--gl-border);
    background: #fff;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    color: var(--gl-muted);
    cursor: pointer;
    transition: var(--gl-transition);
}
.page-galeri-premium .gl-filters__tab:hover {
    border-color: rgba(29, 78, 216, 0.35);
    color: var(--gl-accent);
}
.page-galeri-premium .gl-filters__tab.is-active {
    background: linear-gradient(135deg, var(--gl-accent), #2563eb);
    border-color: transparent;
    color: #fff;
    box-shadow: 0 4px 14px rgba(29, 78, 216, 0.32);
}
.page-galeri-premium .gl-toolbar__meta {
    margin: 0;
    width: 100%;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--gl-muted);
}
@media (min-width: 768px) {
    .page-galeri-premium .gl-toolbar__meta {
        width: auto;
        margin-left: auto;
        text-align: right;
    }
}

/* Masonry */
.page-galeri-premium .gl-masonry {
    column-count: 2;
    column-gap: var(--gl-gap);
}
@media (min-width: 640px) {
    .page-galeri-premium .gl-masonry {
        column-count: 3;
    }
}
@media (min-width: 1024px) {
    .page-galeri-premium .gl-masonry {
        column-count: 4;
    }
}
.page-galeri-premium .gl-item {
    display: inline-block;
    width: 100%;
    margin: 0 0 var(--gl-gap);
    break-inside: avoid;
    text-decoration: none;
    color: inherit;
    border-radius: var(--gl-radius);
    overflow: hidden;
    box-shadow: var(--gl-shadow);
    transition: transform var(--gl-transition), box-shadow var(--gl-transition), opacity 0.3s ease;
    opacity: 1;
    transform: none;
}
.page-galeri-premium .gl-masonry .gl-item[data-aos] {
    opacity: 1;
    transform: none;
}
.page-galeri-premium .gl-item--hidden {
    display: none !important;
}
.page-galeri-premium .gl-item:hover {
    transform: translateY(-4px);
    box-shadow: var(--gl-shadow-hover);
}
.page-galeri-premium .gl-item:focus-visible {
    outline: 3px solid #38bdf8;
    outline-offset: 3px;
}
.page-galeri-premium .gl-item__frame {
    position: relative;
    margin: 0;
    aspect-ratio: 4 / 3;
    overflow: hidden;
    border-radius: var(--gl-radius);
    background: linear-gradient(145deg, #e2e8f0, #f1f5f9);
}
.page-galeri-premium .gl-item--tall .gl-item__frame {
    aspect-ratio: 3 / 4;
}
.page-galeri-premium .gl-item--wide .gl-item__frame {
    aspect-ratio: 16 / 10;
}
.page-galeri-premium .gl-item__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform var(--gl-transition);
}
.page-galeri-premium .gl-item:hover .gl-item__img {
    transform: scale(1.08);
}
.page-galeri-premium .gl-item__overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    gap: 0.35rem;
    padding: 1rem 1.05rem;
    margin: 0;
    background: linear-gradient(to top, rgba(15, 23, 42, 0.88) 0%, rgba(15, 23, 42, 0.35) 48%, transparent 72%);
    opacity: 0;
    transition: opacity var(--gl-transition);
}
.page-galeri-premium .gl-item:hover .gl-item__overlay,
.page-galeri-premium .gl-item:focus-visible .gl-item__overlay {
    opacity: 1;
}
.page-galeri-premium .gl-item__overlay-icon {
    position: absolute;
    top: 0.85rem;
    right: 0.85rem;
    width: 2.35rem;
    height: 2.35rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.95);
    color: var(--gl-accent);
    font-size: 0.9rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    transform: scale(0.9);
    transition: transform var(--gl-transition);
}
.page-galeri-premium .gl-item:hover .gl-item__overlay-icon {
    transform: scale(1);
}
.page-galeri-premium .gl-item__title {
    font-size: 0.92rem;
    font-weight: 700;
    line-height: 1.35;
    color: #fff;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.35);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.page-galeri-premium .gl-item__date {
    font-size: 0.78rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.92);
    letter-spacing: 0.02em;
}
.page-galeri-premium .gl-item__date i {
    margin-right: 0.25rem;
    opacity: 0.9;
}

/* Empty */
.page-galeri-premium .gl-empty {
    text-align: center;
    padding: 2.5rem 1.5rem;
    border-radius: var(--gl-radius);
    background: var(--gl-surface);
    border: 1px dashed var(--gl-border);
    margin-bottom: 1.5rem;
}
.page-galeri-premium .gl-empty--page {
    box-shadow: var(--gl-shadow);
    border-style: solid;
}
.page-galeri-premium .gl-empty__hint code {
    font-size: 0.85em;
}
.page-galeri-premium .gl-empty--hidden {
    display: none;
}
.page-galeri-premium .gl-empty__icon {
    font-size: 2.5rem;
    color: #94a3b8;
    margin-bottom: 0.85rem;
}
.page-galeri-premium .gl-empty__text {
    font-size: 0.95rem;
    color: var(--gl-muted);
}

@media (prefers-reduced-motion: reduce) {
    .page-galeri-premium .gl-item:hover,
    .page-galeri-premium .gl-item:hover .gl-item__img {
        transform: none;
    }
    .page-galeri-premium .gl-item__overlay {
        opacity: 1;
    }
}
</style>
