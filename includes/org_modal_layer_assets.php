<?php

function org_modal_layer_asset_base(): string
{
    if (!defined('ORG_WEB_ROOT')) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'org_database.php';
        define('ORG_WEB_ROOT', org_site_web_root());
    }
    $base = ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');

    return $base === '' ? '' : $base . '/';
}

function org_modal_layer_stylesheet_link(): string
{
    $base = org_modal_layer_asset_base();
    $collapseFix = $base . 'assets/css/org-bootstrap-collapse-fix.css?v=1';
    $modalLayer = $base . 'assets/css/org-modal-layer.css?v=3';

    return '<link rel="stylesheet" href="' . htmlspecialchars($collapseFix, ENT_QUOTES, 'UTF-8') . '">' . "\n"
        . '<link rel="stylesheet" href="' . htmlspecialchars($modalLayer, ENT_QUOTES, 'UTF-8') . '">' . "\n";
}
