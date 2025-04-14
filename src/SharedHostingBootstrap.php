<?php

namespace App;

/**
 * Bootstrap trieda pre shared hosting
 * 
 * Táto trieda pomáha nájsť správne cesty k autoloaderu a boot súboru
 * na rôznych typoch shared hostingov.
 */
class SharedHostingBootstrap
{
    /**
     * Nájde cestu k autoloaderu
     * 
     * @return string|null Cesta k autoloaderu alebo null, ak sa nenašla
     */
    public static function findAutoloader(): ?string
    {
        // Možné cesty k autoloaderu
        $vendorPaths = [
            __DIR__ . '/../vendor/autoload.php',           // Štandardná cesta
            __DIR__ . '/../../vendor/autoload.php',         // O úroveň vyššie
            __DIR__ . '/../../../vendor/autoload.php',      // O dve úrovne vyššie
            __DIR__ . '/vendor/autoload.php',               // V aktuálnom adresári
            __DIR__ . '/../app/vendor/autoload.php',        // V app adresári
            dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php', // Vedľa document root
            $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php',      // Vedľa document root (alternatívna cesta)
            $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php',         // V document root
        ];

        // Hľadáme existujúcu cestu
        foreach ($vendorPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Nájde cestu k boot súboru
     * 
     * @return string|null Cesta k boot súboru alebo null, ak sa nenašla
     */
    public static function findBootFile(): ?string
    {
        // Možné cesty k boot súboru
        $bootPaths = [
            __DIR__ . '/../boot/app.php',           // Štandardná cesta
            __DIR__ . '/../../boot/app.php',         // O úroveň vyššie
            __DIR__ . '/../../../boot/app.php',      // O dve úrovne vyššie
            __DIR__ . '/boot/app.php',               // V aktuálnom adresári
            __DIR__ . '/../app/boot/app.php',        // V app adresári
            dirname($_SERVER['DOCUMENT_ROOT']) . '/boot/app.php', // Vedľa document root
            $_SERVER['DOCUMENT_ROOT'] . '/../boot/app.php',      // Vedľa document root (alternatívna cesta)
            $_SERVER['DOCUMENT_ROOT'] . '/boot/app.php',         // V document root
        ];

        // Hľadáme existujúcu cestu
        foreach ($bootPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Vytvorí diagnostický výpis
     * 
     * @return string HTML s diagnostickými informáciami
     */
    public static function getDiagnostics(): string
    {
        $output = '<h1>Shared Hosting Diagnostics</h1>';
        
        // Základné informácie
        $output .= '<h2>Server Information</h2>';
        $output .= '<ul>';
        $output .= '<li>PHP Version: ' . phpversion() . '</li>';
        $output .= '<li>Document Root: ' . $_SERVER['DOCUMENT_ROOT'] . '</li>';
        $output .= '<li>Current Script: ' . $_SERVER['SCRIPT_FILENAME'] . '</li>';
        $output .= '<li>Current Directory: ' . __DIR__ . '</li>';
        $output .= '</ul>';
        
        // Cesty k autoloaderu
        $output .= '<h2>Autoloader Paths</h2>';
        $output .= '<ul>';
        $vendorPaths = [
            __DIR__ . '/../vendor/autoload.php',           // Štandardná cesta
            __DIR__ . '/../../vendor/autoload.php',         // O úroveň vyššie
            __DIR__ . '/../../../vendor/autoload.php',      // O dve úrovne vyššie
            __DIR__ . '/vendor/autoload.php',               // V aktuálnom adresári
            __DIR__ . '/../app/vendor/autoload.php',        // V app adresári
            dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php', // Vedľa document root
            $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php',      // Vedľa document root (alternatívna cesta)
            $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php',         // V document root
        ];
        foreach ($vendorPaths as $path) {
            $output .= '<li>' . $path . ' - ' . (file_exists($path) ? '<span style="color:green">EXISTS</span>' : '<span style="color:red">NOT FOUND</span>') . '</li>';
        }
        $output .= '</ul>';
        
        // Cesty k boot súboru
        $output .= '<h2>Boot File Paths</h2>';
        $output .= '<ul>';
        $bootPaths = [
            __DIR__ . '/../boot/app.php',           // Štandardná cesta
            __DIR__ . '/../../boot/app.php',         // O úroveň vyššie
            __DIR__ . '/../../../boot/app.php',      // O dve úrovne vyššie
            __DIR__ . '/boot/app.php',               // V aktuálnom adresári
            __DIR__ . '/../app/boot/app.php',        // V app adresári
            dirname($_SERVER['DOCUMENT_ROOT']) . '/boot/app.php', // Vedľa document root
            $_SERVER['DOCUMENT_ROOT'] . '/../boot/app.php',      // Vedľa document root (alternatívna cesta)
            $_SERVER['DOCUMENT_ROOT'] . '/boot/app.php',         // V document root
        ];
        foreach ($bootPaths as $path) {
            $output .= '<li>' . $path . ' - ' . (file_exists($path) ? '<span style="color:green">EXISTS</span>' : '<span style="color:red">NOT FOUND</span>') . '</li>';
        }
        $output .= '</ul>';
        
        // Obsah adresárov
        $output .= '<h2>Directory Contents</h2>';
        
        // Document root
        $output .= '<h3>Document Root</h3>';
        $output .= '<ul>';
        if (is_dir($_SERVER['DOCUMENT_ROOT'])) {
            $files = scandir($_SERVER['DOCUMENT_ROOT']);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $path = $_SERVER['DOCUMENT_ROOT'] . '/' . $file;
                    $output .= '<li>' . $file . ' - ' . (is_dir($path) ? 'directory' : 'file') . ' - ' . 
                             (is_file($path) ? filesize($path) . ' bytes' : '') . ' - ' . 
                             substr(sprintf('%o', fileperms($path)), -4) . '</li>';
                }
            }
        } else {
            $output .= '<li>Cannot access document root</li>';
        }
        $output .= '</ul>';
        
        // Parent of document root
        $output .= '<h3>Parent of Document Root</h3>';
        $output .= '<ul>';
        $parentDir = dirname($_SERVER['DOCUMENT_ROOT']);
        if (is_dir($parentDir)) {
            $files = scandir($parentDir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $path = $parentDir . '/' . $file;
                    $output .= '<li>' . $file . ' - ' . (is_dir($path) ? 'directory' : 'file') . ' - ' . 
                             (is_file($path) ? filesize($path) . ' bytes' : '') . ' - ' . 
                             substr(sprintf('%o', fileperms($path)), -4) . '</li>';
                }
            }
        } else {
            $output .= '<li>Cannot access parent of document root</li>';
        }
        $output .= '</ul>';
        
        return $output;
    }
}
