<?php
// Skript pre zobrazenie JavaScript súborov z adresára build

// Zapnutie zobrazenia chýb
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Získanie cesty k súboru z parametra
$filePath = isset($_GET['path']) ? $_GET['path'] : null;

// Kontrola, či cesta bola zadaná
if (!$filePath) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Missing path parameter';
    exit;
}

// Bezpečnostná kontrola - zabránenie directory traversal
$filePath = str_replace('..', '', $filePath);

// Úplná cesta k súboru
$fullPath = __DIR__ . '/' . $filePath;

// Kontrola, či súbor existuje
if (!file_exists($fullPath)) {
    header('HTTP/1.1 404 Not Found');
    echo 'File not found: ' . htmlspecialchars($filePath);
    exit;
}

// Kontrola, či je súbor čitateľný
if (!is_readable($fullPath)) {
    header('HTTP/1.1 403 Forbidden');
    echo 'File not readable: ' . htmlspecialchars($filePath);
    exit;
}

// Nastavenie správneho Content-Type
$extension = pathinfo($fullPath, PATHINFO_EXTENSION);
if ($extension === 'js') {
    header('Content-Type: application/javascript');
} elseif ($extension === 'css') {
    header('Content-Type: text/css');
} elseif ($extension === 'json') {
    header('Content-Type: application/json');
} else {
    // Získanie MIME typu súboru
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $fullPath);
    finfo_close($finfo);
    header('Content-Type: ' . $mime);
}

// Nastavenie CORS
header('Access-Control-Allow-Origin: *');

// Nastavenie cache
header('Cache-Control: public, max-age=31536000');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');

// Výpis obsahu súboru
readfile($fullPath);
