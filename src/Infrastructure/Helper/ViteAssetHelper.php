<?php

declare(strict_types=1);

namespace App\Infrastructure\Helper;

/**
 * Helper pre prácu s Vite.js assets
 */
class ViteAssetHelper
{
    private string $manifestPath;
    private bool $isDev;
    private string $devServerUrl;
    private ?array $manifest = null;
    private array $imageMap = [];
    private array $jsMap = [];
    private array $cssMap = [];

    /**
     * Konštruktor
     *
     * @param string $manifestPath Cesta k manifest.json súboru
     * @param bool $isDev Či je aplikácia v dev móde
     * @param string $devServerUrl URL Vite dev servera
     */
    public function __construct(
        string $manifestPath,
        bool $isDev = false,
        string $devServerUrl = 'http://localhost:5173'
    ) {
        $this->manifestPath = $manifestPath;
        $this->isDev = $isDev;
        $this->devServerUrl = $devServerUrl;
        $this->loadAssetMaps();
    }

    /**
     * Vráti CSS tag pre asset
     *
     * @param string $path Cesta k JS súboru, ktorý importuje CSS
     * @return string HTML tag pre CSS
     */
    public function cssTag(string $path): string
    {
        // V dev móde používame Vite dev server
        if ($this->isDev) {
            return '<link rel="stylesheet" href="' . $this->devServerUrl . '/' . $path . '" />';
        }

        // Hľadanie CSS súboru v mape
        $key = $this->normalizePath($path);
        if (isset($this->cssMap[$key])) {
            return '<link rel="stylesheet" href="/build/assets/' . $this->cssMap[$key] . '" />';
        }

        // V produkčnom móde používame manifest
        $manifest = $this->loadManifest();

        if ($manifest === null || !isset($manifest[$path])) {
            // Fallback, ak manifest neexistuje alebo asset nie je v manifeste
            return '';
        }

        if (!isset($manifest[$path]['css']) || !is_array($manifest[$path]['css'])) {
            return '';
        }

        $tags = '';
        foreach ($manifest[$path]['css'] as $cssPath) {
            $tags .= '<link rel="stylesheet" href="/build/' . $cssPath . '" />';
        }

        return $tags;
    }

    /**
     * Vráti JS tag pre asset
     *
     * @param string $path Cesta k JS súboru
     * @return string HTML tag pre JS
     */
    public function jsTag(string $path): string
    {
        // V dev móde používame Vite dev server
        if ($this->isDev) {
            return '
                <script type="module" src="' . $this->devServerUrl . '/@vite/client"></script>
                <script type="module" src="' . $this->devServerUrl . '/' . $path . '"></script>
            ';
        }

        // Hľadanie JS súboru v mape
        $key = $this->normalizePath($path);
        if (isset($this->jsMap[$key])) {
            return '<script type="module" src="/build/assets/' . $this->jsMap[$key] . '"></script>';
        }

        // V produkčnom móde používame manifest
        $manifest = $this->loadManifest();

        if ($manifest === null || !isset($manifest[$path])) {
            // Fallback, ak manifest neexistuje alebo asset nie je v manifeste
            return '<script type="module" src="/build/' . $path . '"></script>';
        }

        // Pridanie importovaných súborov
        $tags = '';
        if (isset($manifest[$path]['imports']) && is_array($manifest[$path]['imports'])) {
            foreach ($manifest[$path]['imports'] as $import) {
                if (isset($manifest[$import])) {
                    $importPath = $manifest[$import]['file'];
                    $tags .= '<script type="module" src="/build/' . $importPath . '"></script>';
                }
            }
        }

        // Pridanie hlavného súboru
        $tags .= '<script type="module" src="/build/' . $manifest[$path]['file'] . '"></script>';

        return $tags;
    }

    /**
     * Vráti URL pre asset
     *
     * @param string $path Cesta k assetu
     * @return string URL assetu
     */
    public function asset(string $path): string
    {
        // V dev móde používame Vite dev server
        if ($this->isDev) {
            return $this->devServerUrl . '/' . $path;
        }

        // Pre statické súbory (obrázky)
        if (preg_match('/\\.(jpg|jpeg|png|gif|svg|webp)$/i', $path)) {
            return $this->image(basename($path));
        }

        // Pre JavaScript súbory
        if (preg_match('/\\.js$/i', $path)) {
            $key = $this->normalizePath($path);
            if (isset($this->jsMap[$key])) {
                return '/build/assets/' . $this->jsMap[$key];
            }
        }

        // Pre CSS súbory
        if (preg_match('/\\.css$/i', $path)) {
            $key = $this->normalizePath($path);
            if (isset($this->cssMap[$key])) {
                return '/build/assets/' . $this->cssMap[$key];
            }
        }

        // V produkčnom móde používame manifest
        $manifest = $this->loadManifest();

        if ($manifest === null || !isset($manifest[$path])) {
            // Fallback, ak manifest neexistuje alebo asset nie je v manifeste
            return '/build/' . $path;
        }

        return '/build/' . $manifest[$path]['file'];
    }

    /**
     * Vráti URL pre obrázok
     *
     * @param string $path Cesta k obrázku
     * @return string URL obrázku
     */
    public function image(string $path): string
    {
        // V dev móde používame Vite dev server
        if ($this->isDev) {
            return $this->devServerUrl . '/' . $path;
        }

        // Hľadanie obrázka v mape
        $filename = basename($path);
        if (isset($this->imageMap[$filename])) {
            return '/build/assets/' . $this->imageMap[$filename];
        }

        // Fallback, ak obrázok nie je v mape
        return '/build/' . $path;
    }

    /**
     * Načíta manifest.json
     *
     * @return array|null Manifest alebo null, ak manifest neexistuje
     */
    private function loadManifest(): ?array
    {
        if ($this->manifest !== null) {
            return $this->manifest;
        }

        if (!file_exists($this->manifestPath)) {
            return null;
        }

        $this->manifest = json_decode(file_get_contents($this->manifestPath), true);

        return $this->manifest;
    }

    /**
     * Načíta mapy assetov z manifest.json
     */
    private function loadAssetMaps(): void
    {
        $manifest = $this->loadManifest();
        if ($manifest === null) {
            return;
        }

        // Prehľadávanie manifestu pre assety
        foreach ($manifest as $key => $value) {
            if (!isset($value['file'])) {
                continue;
            }

            $file = $value['file'];
            $normalizedKey = $this->normalizePath($key);

            // Obrázky
            if (preg_match('/assets\/(.*?)\.(jpg|jpeg|png|gif|svg|webp)/i', $file, $matches)) {
                $originalName = basename($key);
                $hashedName = basename($file);
                $this->imageMap[$originalName] = $hashedName;
            }

            // JavaScript
            if (preg_match('/assets\/(.*?)\.js$/i', $file)) {
                $this->jsMap[$normalizedKey] = basename($file);
            }

            // CSS
            if (preg_match('/assets\/(.*?)\.css$/i', $file)) {
                $this->cssMap[$normalizedKey] = basename($file);
            }
        }

        // Prehľadávanie adresára assets pre obrázky
        $assetsDir = dirname($this->manifestPath) . '/assets';
        if (is_dir($assetsDir)) {
            $files = scandir($assetsDir);
            foreach ($files as $file) {
                // Obrázky
                if (preg_match('/(.*?)-(.*?)\.(jpg|jpeg|png|gif|svg|webp)/i', $file, $matches)) {
                    $originalName = $matches[1] . '.' . $matches[3];
                    $this->imageMap[$originalName] = $file;
                }

                // JavaScript
                if (preg_match('/(.*?)-(.*?)\.js$/i', $file, $matches)) {
                    $originalName = $matches[1] . '.js';
                    $this->jsMap[$originalName] = $file;
                    $this->jsMap['js/' . $originalName] = $file;
                    $this->jsMap['resources/js/' . $originalName] = $file;
                }

                // CSS
                if (preg_match('/(.*?)-(.*?)\.css$/i', $file, $matches)) {
                    $originalName = $matches[1] . '.css';
                    $this->cssMap[$originalName] = $file;
                    $this->cssMap['css/' . $originalName] = $file;
                    $this->cssMap['resources/css/' . $originalName] = $file;
                }
            }
        }
    }

    /**
     * Normalizuje cestu k assetu
     *
     * @param string $path Cesta k assetu
     * @return string Normalizovaná cesta
     */
    private function normalizePath(string $path): string
    {
        // Odstránenie resources/ z cesty
        $path = preg_replace('/^resources\//', '', $path);

        // Odstránenie ./ z cesty
        $path = preg_replace('/^\.\//', '', $path);

        return $path;
    }
}
