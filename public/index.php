<?php

declare(strict_types=1);

// Nastavenie správnych MIME typov pre statické súbory
// $path = $_SERVER['REQUEST_URI'];
// if (preg_match('/\.(js|css|jpg|jpeg|png|gif|svg|webp)$/i', $path, $matches)) {
//     $extension = strtolower($matches[1]);
//     $mimeTypes = [
//         'js' => 'application/javascript',
//         'css' => 'text/css',
//         'jpg' => 'image/jpeg',
//         'jpeg' => 'image/jpeg',
//         'png' => 'image/png',
//         'gif' => 'image/gif',
//         'svg' => 'image/svg+xml',
//         'webp' => 'image/webp'
//     ];

//     if (isset($mimeTypes[$extension])) {
//         $filePath = __DIR__ . $path;
//         if (file_exists($filePath)) {
//             header('Content-Type: ' . $mimeTypes[$extension]);
//             readfile($filePath);
//             exit;
//         }
//     }
// }

// Načítanie autoloadera
require __DIR__ . '/../vendor/autoload.php';

// Načítanie a spustenie aplikácie z boot/app.php
$app = require __DIR__ . '/../boot/app.php';

// Spustenie aplikácie
$app->run();
