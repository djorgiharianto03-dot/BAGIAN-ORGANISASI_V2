<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_personnel_sync.php';

$personnelFile = __DIR__ . DIRECTORY_SEPARATOR . 'personnel.json';
$before = json_decode(file_get_contents($personnelFile), true);
$target = $before[count($before) - 1];

echo 'BEFORE: count=' . count($before) . ', mtime=' . date('H:i:s', filemtime($personnelFile)) . PHP_EOL;

$after = $before;
array_splice($after, count($after) - 1, 1);
$ok = org_personnel_write_file($personnelFile, $after);
clearstatcache(true);
$persisted = json_decode(file_get_contents($personnelFile), true);
echo 'WRITE: ok=' . var_export($ok, true) . ', count=' . count($persisted) . ', mtime=' . date('H:i:s', filemtime($personnelFile)) . PHP_EOL;

$stillThere = false;
foreach ($persisted as $r) {
    if (($r['id'] ?? '') === $target['id']) {
        $stillThere = true;
        break;
    }
}
echo 'TARGET PRESENT AFTER DELETE: ' . var_export($stillThere, true) . PHP_EOL;

$restoreOk = org_personnel_write_file($personnelFile, $before);
echo 'RESTORE: ok=' . var_export($restoreOk, true) . ', count=' . count(json_decode(file_get_contents($personnelFile), true)) . PHP_EOL;
