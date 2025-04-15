<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ViteExtension extends AbstractExtension
{
    private $manifest = null;
    private $manifestPath;
    private $publicPath;
    private $devServerRunning = false;
    private $devServerUrl = 'http://localhost:5173';
    private $publicPathDev = '/build/';

    public function __construct(string $manifestPath = null, string $publicPath = '/build/')
    {
        $this->manifestPath = $manifestPath ?? __DIR__ . '/../../public/build/.vite/manifest.json';
        $this->publicPath = $publicPath;

        // Check if dev server is running
        $this->devServerRunning = $this->isDevServerRunning();
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('vite_asset', [$this, 'getAssetUrl']),
            new TwigFunction('vite_entry', [$this, 'renderEntry'], ['is_safe' => ['html']]),
        ];
    }

    public function getAssetUrl(string $path): string
    {
        // If dev server is running, return dev server URL
        if ($this->devServerRunning) {
            // For development, we need to use the Vite dev server URL
            return $this->devServerUrl . '/' . $path;
        }

        // Load manifest if not already loaded
        $this->loadManifest();

        // If manifest doesn't exist or is empty, return the path as is
        if (!$this->manifest) {
            return $this->publicPath . $path;
        }

        // If the path is not in the manifest, return the path as is
        if (!isset($this->manifest[$path])) {
            return $this->publicPath . $path;
        }

        // Return the hashed file path
        return $this->publicPath . $this->manifest[$path]['file'];
    }

    public function renderEntry(string $entry): string
    {
        // If dev server is running, return dev server script
        if ($this->devServerRunning) {
            return sprintf(
                '<script type="module" src="%s/@vite/client"></script>' .
                '<script type="module" src="%s/%s"></script>',
                $this->devServerUrl,
                $this->devServerUrl,
                $entry
            );
        }

        // Load manifest if not already loaded
        $this->loadManifest();

        // If manifest doesn't exist or is empty, return empty string
        if (!$this->manifest) {
            return '';
        }

        // If the entry is not in the manifest, return empty string
        if (!isset($this->manifest[$entry])) {
            return '';
        }

        $entryData = $this->manifest[$entry];
        $html = '';

        // Add CSS files
        if (isset($entryData['css']) && is_array($entryData['css'])) {
            foreach ($entryData['css'] as $cssFile) {
                $html .= sprintf(
                    '<link rel="stylesheet" href="%s">',
                    $this->publicPath . $cssFile
                );
            }
        }

        // Add JS file
        $html .= sprintf(
            '<script type="module" src="%s"></script>',
            $this->publicPath . $entryData['file']
        );

        return $html;
    }

    private function loadManifest(): void
    {
        // If manifest is already loaded, return
        if ($this->manifest !== null) {
            return;
        }

        // If manifest file doesn't exist, set manifest to empty array
        if (!file_exists($this->manifestPath)) {
            $this->manifest = [];
            return;
        }

        // Load manifest file
        $manifestContent = file_get_contents($this->manifestPath);
        $this->manifest = json_decode($manifestContent, true) ?: [];
    }

    private function isDevServerRunning(): bool
    {
        // V produkčnom prostredí nikdy nepoužívame dev server
        if (getenv('APP_ENV') === 'production') {
            return false;
        }

        // Check if Vite dev server is running by making a request to the server
        $ch = curl_init($this->devServerUrl . '/@vite/client');
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Ak je dev server spustený, vrátime true
        $isRunning = $responseCode >= 200 && $responseCode < 300;

        // Pre vývoj môžeme použiť aj environment premennú
        if (getenv('VITE_DEV_SERVER') === 'true') {
            $isRunning = true;
        }

        // Ak je dev server spustený, nastavíme publicPathDev na cestu k dev serveru
        if ($isRunning) {
            // Pre vývoj používame cestu k dev serveru
            $this->publicPathDev = '/build/';
        }

        return $isRunning;
    }
}
