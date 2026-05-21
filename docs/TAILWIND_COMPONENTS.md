# Tailwind UI Components — Bagian Organisasi V2

## Build

```bash
npm install
npm run build:css
```

Watch mode: `npm run watch:css`

Output: `assets/css/org-tailwind.css` (loaded after `site_styles.php`).

## Design system

| Token | Light | Dark (`data-theme="dark"`) |
|-------|-------|------------------------------|
| Background | `#f9fafb` | `#0f172a` |
| Surface | `#ffffff` | `#111827` |
| Card | `#ffffff` | `#1e293b` |
| Primary | `#38bdf8` | `#38bdf8` |
| Text | `#0f172a` | `#f8fafc` |
| Muted | `#64748b` | `#cbd5e1` |

Typography: `text-org-xs` … `text-org-display` · Spacing: `org-1` … `org-section` · Layout: `org-container` (max 1200px).

## PHP usage

```php
require_once __DIR__ . '/includes/org_tailwind_assets.php';
org_tailwind_bootstrap();

// Include a component
org_component('card', [
    'cardTitle' => 'Judul',
    'cardBodyHtml' => '<p>Konten</p>',
    'cardVariant' => 'interactive',
]);

// Button helper (no duplicated utility classes)
echo org_ui_button('Simpan', [
    'variant' => 'primary',
    'icon' => 'fa-solid fa-check',
    'type' => 'submit',
]);
```

## Components (`includes/components/`)

| File | Semantic classes | Notes |
|------|------------------|-------|
| `navbar.php` | `org-navbar` | Wired via `header.php`; keeps `site-header*` for JS |
| `hero.php` | `org-hero`, `org-hero--sub` | Portal sub-pages via `portal_subpage_hero.php` |
| `card.php` | `org-card` | Generic article card |
| `org_ui.php` | `org-btn`, helpers | `org_ui_button()`, `org_ui_card_open()` |
| `table.php` | `org-table` | Pass `$tableHeaders`, `$tableRows` |
| `gallery_item.php` | `org-gallery__*` | Optional; galeri uses toolbar/grid classes |
| `dashboard_stat.php` | `org-dash-stat` | E-Organisasi KPI tiles |
| `footer.php` | `org-footer` | Wired via `footer.php` |

## Rules

1. **PHP templates** use semantic `org-*` classes only — utilities live in `resources/css/org-tailwind.css` via `@apply`.
2. **Legacy hooks** (`site-header`, `gl-masonry`, `eo-dash`) remain where JavaScript or old CSS still depends on them.
3. **New UI** should extend `org-tailwind.css` components, not add one-off utility strings in PHP.
