# Unduh aset CDN ke assets/vendor — jalankan sekali setelah clone/deploy
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$vendor = Join-Path $root 'assets\vendor'

$dirs = @(
    'bootstrap\5.3.3',
    'fontawesome\6.5.1\css',
    'fontawesome\6.5.1\webfonts',
    'chartjs\4.4.1',
    'apexcharts\3.49.1',
    'aos\2.3.4',
    'swiper\11',
    'fancybox\5.0'
)
foreach ($d in $dirs) {
    New-Item -ItemType Directory -Force -Path (Join-Path $vendor $d) | Out-Null
}

$files = @{
    'bootstrap\5.3.3\bootstrap.min.css' = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css'
    'bootstrap\5.3.3\bootstrap.bundle.min.js' = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'
    'chartjs\4.4.1\chart.umd.min.js' = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js'
    'apexcharts\3.49.1\apexcharts.min.js' = 'https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js'
    'aos\2.3.4\aos.css' = 'https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css'
    'aos\2.3.4\aos.js' = 'https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js'
    'swiper\11\swiper-bundle.min.css' = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css'
    'swiper\11\swiper-bundle.min.js' = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js'
    'fancybox\5.0\fancybox.css' = 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css'
    'fancybox\5.0\fancybox.umd.js' = 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js'
    'fontawesome\6.5.1\css\all.min.css' = 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css'
}

foreach ($rel in $files.Keys) {
    $dest = Join-Path $vendor $rel
    Write-Host "GET $rel"
    Invoke-WebRequest -Uri $files[$rel] -OutFile $dest -UseBasicParsing
}

$webfonts = @{
    'fa-solid-900.woff2' = 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/webfonts/fa-solid-900.woff2'
    'fa-regular-400.woff2' = 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/webfonts/fa-regular-400.woff2'
    'fa-brands-400.woff2' = 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/webfonts/fa-brands-400.woff2'
    'fa-v4compatibility.woff2' = 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/webfonts/fa-v4compatibility.woff2'
}
$wfDir = Join-Path $vendor 'fontawesome\6.5.1\webfonts'
foreach ($name in $webfonts.Keys) {
    Write-Host "GET webfonts/$name"
    Invoke-WebRequest -Uri $webfonts[$name] -OutFile (Join-Path $wfDir $name) -UseBasicParsing
}

Write-Host "Done. Run: php deploy/build-beranda-bundle.php"
