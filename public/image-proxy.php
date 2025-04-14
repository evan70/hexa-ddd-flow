<?php
// Skript pre zobrazenie obrázkov z adresára build

// Zapnutie zobrazenia chýb
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Získanie cesty k obrázku z parametra
$imagePath = isset($_GET['path']) ? $_GET['path'] : null;

// Kontrola, či cesta bola zadaná
if (!$imagePath) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Missing path parameter';
    exit;
}

// Bezpečnostná kontrola - zabránenie directory traversal
$imagePath = str_replace('..', '', $imagePath);

// Úplná cesta k súboru
$fullPath = __DIR__ . '/' . $imagePath;

// Kontrola, či súbor existuje
if (!file_exists($fullPath)) {
    header('HTTP/1.1 404 Not Found');
    echo 'File not found: ' . htmlspecialchars($imagePath);
    exit;
}

// Kontrola, či je súbor čitateľný
if (!is_readable($fullPath)) {
    header('HTTP/1.1 403 Forbidden');
    echo 'File not readable: ' . htmlspecialchars($imagePath);
    exit;
}

// Získanie MIME typu súboru
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $fullPath);
finfo_close($finfo);

// Nastavenie správneho Content-Type
header('Content-Type: ' . $mime);

// Nastavenie cache
header('Cache-Control: public, max-age=31536000');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');

// Výpis obsahu súboru
readfile($fullPath);
