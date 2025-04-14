<?php
// Diagnostický skript pre kontrolu súborov v adresári build

// Zapnutie zobrazenia chýb
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Funkcia pre výpis informácií o súbore
function fileInfo($path) {
    $result = [
        'path' => $path,
        'exists' => file_exists($path),
        'readable' => is_readable($path),
        'size' => file_exists($path) ? filesize($path) : null,
        'permissions' => file_exists($path) ? substr(sprintf('%o', fileperms($path)), -4) : null,
        'mime' => file_exists($path) ? (function_exists('mime_content_type') ? mime_content_type($path) : 'mime_content_type not available') : null,
    ];
    
    return $result;
}

// Funkcia pre výpis adresára
function dirInfo($path) {
    $result = [
        'path' => $path,
        'exists' => is_dir($path),
        'readable' => is_dir($path) && is_readable($path),
        'writable' => is_dir($path) && is_writable($path),
        'permissions' => is_dir($path) ? substr(sprintf('%o', fileperms($path)), -4) : null,
        'contents' => [],
    ];
    
    if (is_dir($path) && is_readable($path)) {
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $filePath = $path . '/' . $file;
                if (is_dir($filePath)) {
                    $result['contents'][$file] = [
                        'type' => 'directory',
                        'readable' => is_readable($filePath),
                        'writable' => is_writable($filePath),
                        'permissions' => substr(sprintf('%o', fileperms($filePath)), -4),
                    ];
                } else {
                    $result['contents'][$file] = [
                        'type' => 'file',
                        'size' => filesize($filePath),
                        'readable' => is_readable($filePath),
                        'writable' => is_writable($filePath),
                        'permissions' => substr(sprintf('%o', fileperms($filePath)), -4),
                        'mime' => function_exists('mime_content_type') ? mime_content_type($filePath) : 'mime_content_type not available',
                    ];
                }
            }
        }
    }
    
    return $result;
}

// Kontrola súborov
$filesToCheck = [
    'build/assets/app-F1B1iBRr.js',
    'build/docs-hero.jpg',
    'build/testimonial-1.jpg',
    'build/testimonial-2.jpg',
    'build/testimonial-3.jpg',
];

$results = [];
foreach ($filesToCheck as $file) {
    $results['files'][$file] = fileInfo(__DIR__ . '/' . $file);
}

// Kontrola adresárov
$dirsToCheck = [
    'build',
    'build/assets',
    'build/js',
];

foreach ($dirsToCheck as $dir) {
    $results['directories'][$dir] = dirInfo(__DIR__ . '/' . $dir);
}

// Kontrola .htaccess súborov
$htaccessFiles = [
    'build/.htaccess',
    'build/assets/.htaccess',
    'build/js/.htaccess',
];

foreach ($htaccessFiles as $file) {
    $results['htaccess'][$file] = fileInfo(__DIR__ . '/' . $file);
}

// Kontrola manifestu
$manifestPath = __DIR__ . '/build/.vite/manifest.json';
$results['manifest'] = [
    'path' => $manifestPath,
    'exists' => file_exists($manifestPath),
    'readable' => is_readable($manifestPath),
    'content' => file_exists($manifestPath) && is_readable($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : null,
];

// Výpis výsledkov
header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT);
