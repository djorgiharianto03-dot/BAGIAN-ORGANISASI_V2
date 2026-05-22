# Vendor assets (lokal)

Aset ini di-commit agar halaman tidak bergantung CDN eksternal.

## Isi

| Paket | Path |
|-------|------|
| Bootstrap 5.3.3 | `bootstrap/5.3.3/` |
| Font Awesome 6.5.1 | `fontawesome/6.5.1/` |
| Chart.js 4.4.1 | `chartjs/4.4.1/` |
| ApexCharts 3.49.1 | `apexcharts/3.49.1/` |
| AOS 2.3.4 | `aos/2.3.4/` |
| Swiper 11 | `swiper/11/` |
| Fancybox 5.0 | `fancybox/5.0/` |

## Unduh ulang

```powershell
.\deploy\download-vendor-assets.ps1
```

## CSS beranda (gabungan)

```bash
php deploy/build-beranda-bundle.php
```

Hasil: `assets/css/beranda.bundle.min.css`
