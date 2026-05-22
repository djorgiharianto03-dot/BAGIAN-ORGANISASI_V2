<?php
declare(strict_types=1);

/**
 * Fragment HTML beranda (dashboard / target tim) — dimuat lazy dari JS.
 */
if (!defined('ORG_ROOT')) {
    define('ORG_ROOT', __DIR__);
}

require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_beranda_perf.php';
require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_session.php';
org_session_start();

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: private, max-age=90');
header('X-Robots-Tag: noindex');

$section = strtolower(trim((string) ($_GET['section'] ?? '')));
if (!in_array($section, ['dashboard', 'team'], true)) {
    http_response_code(400);
    echo '<!-- invalid section -->';
    exit;
}

require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'dashboard_widgets_db.php';
require_once ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'team_targets_db.php';

$berandaDashboardWidgets = [];
$berandaWidgetDetailsMap = [];
$berandaTeamTargetsTahun = org_team_targets_normalize_tahun($_GET['tahun'] ?? (int) date('Y'));
$berandaTeamTargetsYears = [];
$berandaTeamTargetsGrouped = org_team_targets_empty_grouped();
$berandaTeamTargetsVisible = false;

$db = org_db();
if ($db instanceof mysqli) {
    if ($section === 'dashboard') {
        org_beranda_ensure_table_once($db, 'dashboard_widgets', static function () use ($db): void {
            org_dashboard_widgets_ensure_table($db);
        });
        $dashBundle = org_beranda_fetch_dashboard_bundle($db);
        $berandaDashboardWidgets = $dashBundle['widgets'];
        $berandaWidgetDetailsMap = $dashBundle['details'];
    } else {
        org_beranda_ensure_table_once($db, 'team_targets', static function () use ($db): void {
            org_team_targets_ensure_table($db);
        });
        $teamBundle = org_beranda_fetch_team_targets_bundle($db, $berandaTeamTargetsTahun);
        $berandaTeamTargetsTahun = (int) $teamBundle['tahun'];
        $berandaTeamTargetsYears = $teamBundle['years'];
        $berandaTeamTargetsGrouped = $teamBundle['grouped'];
        $berandaTeamTargetsVisible = (bool) $teamBundle['visible'];
    }
}

if ($section === 'dashboard') {
    require ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_dashboard_widgets.php';
    exit;
}

require ORG_ROOT . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'beranda_team_targets.php';
