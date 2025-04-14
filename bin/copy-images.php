<?php

/**
 * Skript pre kopírovanie obrázkov z resources do public/build
 * 
 * Tento skript kopíruje všetky obrázky z adresára resources/images do adresára public/build
 * 
 * Použitie:
 * php bin/copy-images.php
 */

// Adresáre
$sourceDir = __DIR__ . '/../resources/images';
$targetDir = __DIR__ . '/../public/build';

// Kontrola, či zdrojový adresár existuje
if (!is_dir($sourceDir)) {
    echo "Zdrojový adresár $sourceDir neexistuje.\n";
    exit(1);
}

// Vytvorenie cieľového adresára, ak neexistuje
if (!is_dir($targetDir)) {
    if (!mkdir($targetDir, 0755, true)) {
        echo "Nepodarilo sa vytvoriť cieľový adresár $targetDir.\n";
        exit(1);
    }
}

// Funkcia pre rekurzívne kopírovanie adresára
function copyDirectory($source, $target) {
    // Vytvorenie cieľového adresára, ak neexistuje
    if (!is_dir($target)) {
        if (!mkdir($target, 0755, true)) {
            echo "Nepodarilo sa vytvoriť adresár $target.\n";
            return false;
        }
    }

    // Kopírovanie súborov
    $dir = opendir($source);
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $sourcePath = $source . '/' . $file;
            $targetPath = $target . '/' . $file;

            if (is_dir($sourcePath)) {
                // Rekurzívne kopírovanie podadresárov
                copyDirectory($sourcePath, $targetPath);
            } else {
                // Kopírovanie súborov
                if (copy($sourcePath, $targetPath)) {
                    echo "Skopírovaný súbor: $file\n";
                } else {
                    echo "Nepodarilo sa skopírovať súbor: $file\n";
                }
            }
        }
    }
    closedir($dir);
    return true;
}

// Kopírovanie obrázkov
echo "Kopírovanie obrázkov z $sourceDir do $targetDir...\n";
copyDirectory($sourceDir, $targetDir);
echo "Hotovo.\n";
