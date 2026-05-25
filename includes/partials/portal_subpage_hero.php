<?php

/**
 * Hero ringkas halaman dalam portal — Tailwind component.
 *
 * Variabel hero di-set oleh org_portal_set_hero() via `global ...`.
 * Tarik eksplisit dari global scope di sini supaya tetap terbaca meski
 * partial ini di-include dari konteks yang tidak otomatis mewarisi global
 * (mis. OPcache state lama, file di-include via wrapper, atau perilaku
 * scope PHP yang berbeda antar SAPI/version). Sama polanya dengan fix
 * $smartPortalNav di includes/header.php.
 */
global $portalHeroEyebrow, $portalHeroTitle, $portalHeroTitleHtml,
       $portalHeroLead, $portalHeroIcon, $portalHeroStats, $portalHeroBreadcrumb;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'org_tailwind_assets.php';
org_tailwind_bootstrap();
org_component('hero', [
    'portalHeroEyebrow' => $portalHeroEyebrow ?? '',
    'portalHeroTitle' => $portalHeroTitle ?? '',
    'portalHeroTitleHtml' => $portalHeroTitleHtml ?? '',
    'portalHeroLead' => $portalHeroLead ?? '',
    'portalHeroStats' => $portalHeroStats ?? [],
    'portalHeroBreadcrumb' => $portalHeroBreadcrumb ?? '',
]);
