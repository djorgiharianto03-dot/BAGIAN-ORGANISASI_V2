<?php

/**
 * Hero ringkas halaman dalam portal — Tailwind component.
 */
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'org_tailwind_assets.php';
org_tailwind_bootstrap();
org_component('hero', [
    'portalHeroEyebrow' => $portalHeroEyebrow ?? '',
    'portalHeroTitle' => $portalHeroTitle ?? '',
    'portalHeroLead' => $portalHeroLead ?? '',
    'portalHeroStats' => $portalHeroStats ?? [],
]);
