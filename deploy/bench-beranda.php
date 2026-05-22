<?php

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
chdir(dirname(__DIR__));
$start = microtime(true);
ob_start();
include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'index.php';
$html = ob_get_clean();
$ms = (microtime(true) - $start) * 1000;
echo 'TTFB simulation: ' . round($ms, 1) . " ms\n";
echo 'HTML size: ' . round(strlen($html) / 1024, 1) . " KB\n";
