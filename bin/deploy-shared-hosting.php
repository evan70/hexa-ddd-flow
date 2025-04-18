<?php

/**
 * Skript pre nasadenie aplikácie na shared hosting
 * Autor: evan70
 * Dátum: <?= date('Y-m-d') ?>
 */

// Kontrola, či je skript spustený z koreňového adresára projektu
if (!file_exists('composer.json')) {
    echo "ERROR: Skript musí byť spustený z koreňového adresára projektu\n";
    exit(1);
}

// Spustenie PHPStan pre kontrolu kódu
echo "INFO: Spustenie PHPStan pre kontrolu kódu...\n";
$output = [];
$returnVar = 0;

// Skúsime spustiť PHPStan priamo
if (exec('command -v phpstan', $output, $returnCode) && $returnCode === 0) {
    exec('phpstan analyse src --level=5', $output, $returnVar);
} else {
    // Použitie PHPStan cez Composer
    exec('composer phpstan', $output, $returnVar);
}

// Ak PHPStan zlyhal, spýtame sa používateľa, či chce pokračovať
if ($returnVar !== 0) {
    echo "WARNING: PHPStan našiel chyby v kóde alebo nie je dostupný.\n";
    echo implode("\n", $output) . "\n";
    echo "WARNING: Chcete pokračovať aj napriek tomu? (y/n) ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($line) !== 'y') {
        echo "ERROR: Nasadenie bolo prerušené.\n";
        exit(1);
    }
    
    echo "INFO: Pokračujeme v nasadení bez kontroly kódu...\n";
}

// Vytvorenie adresára pre build
echo "INFO: Vytváram adresár pre build...\n";
$buildDir = "build_shared_hosting";
if (is_dir($buildDir)) {
    removeDirectory($buildDir);
}
mkdir($buildDir, 0755, true);

// Kopírovanie composer súborov
echo "INFO: Kopírujem composer súbory...\n";
$files = ['composer.json', 'composer.lock'];
foreach ($files as $file) {
    if (file_exists($file)) {
        copy($file, $buildDir . '/' . $file);
    }
}

// Inštalácia produkčných závislostí
echo "INFO: Inštalujem produkčné závislosti...\n";
$currentDir = getcwd();
chdir($buildDir);
$output = [];
$returnVar = 0;
exec('composer install --no-dev --optimize-autoloader --quiet', $output, $returnVar);
chdir($currentDir);

if ($returnVar !== 0) {
    echo "ERROR: Nepodarilo sa nainštalovať závislosti. Skontrolujte, či máte nainštalovaný Composer.\n";
    removeDirectory($buildDir);
    exit(1);
}

// Kopírovanie potrebných súborov a adresárov
echo "INFO: Kopírujem potrebné súbory a adresáre...\n";
$directories = ['public', 'src', 'config', 'boot', 'resources'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        copyDirectory($dir, $buildDir . '/' . $dir);
    }
}

// Vytvorenie adresárov, ak neexistujú
$optionalDirs = ['var', 'data'];
foreach ($optionalDirs as $dir) {
    if (is_dir($dir)) {
        copyDirectory($dir, $buildDir . '/' . $dir);
    } else {
        mkdir($buildDir . '/' . $dir, 0755, true);
    }
}

// Nastavenie produkčného módu v settings.php
echo "INFO: Nastavujem produkčný mód...\n";
$settingsFile = $buildDir . '/config/settings.php';
if (file_exists($settingsFile)) {
    $settings = file_get_contents($settingsFile);
    $settings = str_replace('"displayErrorDetails" => true', '"displayErrorDetails" => false', $settings);
    $settings = str_replace('"logErrors" => false', '"logErrors" => true', $settings);
    $settings = str_replace('"logErrorDetails" => false', '"logErrorDetails" => true', $settings);
    file_put_contents($settingsFile, $settings);
}

// Vytvorenie .htaccess súboru pre public adresár, ak neexistuje
if (!file_exists($buildDir . '/public/.htaccess')) {
    echo "INFO: Vytváram .htaccess súbor pre public adresár...\n";
    $htaccess = <<<'EOL'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
EOL;
    file_put_contents($buildDir . '/public/.htaccess', $htaccess);
}

// Vytvorenie .htaccess súboru pre koreňový adresár, ak neexistuje
if (!file_exists($buildDir . '/.htaccess')) {
    echo "INFO: Vytváram .htaccess súbor pre koreňový adresár...\n";
    $htaccess = <<<'EOL'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^$ public/ [L]
    RewriteRule (.*) public/$1 [L]
</IfModule>

# Zabezpečenie prístupu k citlivým súborom
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "^(composer\.json|composer\.lock|package\.json|package-lock\.json|webpack\.config\.js|vite\.config\.js)$">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "^(\.env|\.env\.example|phpunit\.xml|phpstan\.neon)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Zabezpečenie prístupu k adresárom
<DirectoryMatch "^\./(src|config|boot|vendor|node_modules|tests)">
    Order allow,deny
    Deny from all
</DirectoryMatch>
EOL;
    file_put_contents($buildDir . '/.htaccess', $htaccess);
}

// Vytvorenie index.php súboru pre koreňový adresár, ak neexistuje
if (!file_exists($buildDir . '/index.php')) {
    echo "INFO: Vytváram index.php súbor pre koreňový adresár...\n";
    $index = <<<'EOL'
<?php
header('Location: public/');
EOL;
    file_put_contents($buildDir . '/index.php', $index);
}

// Upravenie public/index.php pre shared hosting
echo "INFO: Upravujem public/index.php pre shared hosting...\n";
$indexContent = <<<'EOL'
<?php

declare(strict_types=1);

// Zapnutie zobrazenia chýb pre debugovanie (odkomentujte v prípade problémov)
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

// Pokus o načítanie SharedHostingBootstrap triedy
try {
    // Skúsime nájsť SharedHostingBootstrap.php na rôznych cestách
    $bootstrapPaths = [
        __DIR__ . '/../src/SharedHostingBootstrap.php',
        __DIR__ . '/../../src/SharedHostingBootstrap.php',
        __DIR__ . '/../../../src/SharedHostingBootstrap.php',
        __DIR__ . '/src/SharedHostingBootstrap.php',
        __DIR__ . '/../app/src/SharedHostingBootstrap.php',
    ];
    
    $bootstrapPath = null;
    foreach ($bootstrapPaths as $path) {
        if (file_exists($path)) {
            $bootstrapPath = $path;
            break;
        }
    }
    
    if ($bootstrapPath) {
        // Načítame SharedHostingBootstrap.php
        require_once $bootstrapPath;
        
        // Použijeme SharedHostingBootstrap na nájdenie autoloadera
        $autoloadPath = \App\SharedHostingBootstrap::findAutoloader();
        if ($autoloadPath) {
            require $autoloadPath;
            
            // Použijeme SharedHostingBootstrap na nájdenie boot súboru
            $bootPath = \App\SharedHostingBootstrap::findBootFile();
            if ($bootPath) {
                // Načítanie a spustenie aplikácie z boot/app.php
                $app = require $bootPath;
                
                // Spustenie aplikácie
                $app->run();
                exit;
            } else {
                throw new \Exception('Boot file not found');
            }
        } else {
            throw new \Exception('Autoloader not found');
        }
    }
} catch (\Exception $e) {
    // Ak SharedHostingBootstrap zlyhalo, použijeme záložný spôsob
}

// Záložný spôsob - priama detekcia ciest

// Detekcia cesty k vendor adresáru
$vendorPaths = [
    __DIR__ . '/../vendor/autoload.php',           // Štandardná cesta
    __DIR__ . '/../../vendor/autoload.php',         // O úroveň vyššie
    __DIR__ . '/../../../vendor/autoload.php',      // O dve úrovne vyššie
    __DIR__ . '/vendor/autoload.php',               // V public adresári
    __DIR__ . '/../app/vendor/autoload.php',        // V app adresári
    dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php', // Vedľa document root
    $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php',      // Vedľa document root (alternatívna cesta)
    $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php',         // V document root
];

$autoloadPath = null;
foreach ($vendorPaths as $path) {
    if (file_exists($path)) {
        $autoloadPath = $path;
        break;
    }
}

if ($autoloadPath === null) {
    die('Autoloader not found. Please run "composer install" in the project root.');
}

// Načítanie autoloadera
require $autoloadPath;

// Detekcia cesty k boot/app.php
$bootPaths = [
    __DIR__ . '/../boot/app.php',           // Štandardná cesta
    __DIR__ . '/../../boot/app.php',         // O úroveň vyššie
    __DIR__ . '/../../../boot/app.php',      // O dve úrovne vyššie
    __DIR__ . '/boot/app.php',               // V public adresári
    __DIR__ . '/../app/boot/app.php',        // V app adresári
    dirname($_SERVER['DOCUMENT_ROOT']) . '/boot/app.php', // Vedľa document root
    $_SERVER['DOCUMENT_ROOT'] . '/../boot/app.php',      // Vedľa document root (alternatívna cesta)
    $_SERVER['DOCUMENT_ROOT'] . '/boot/app.php',         // V document root
];

$bootPath = null;
foreach ($bootPaths as $path) {
    if (file_exists($path)) {
        $bootPath = $path;
        break;
    }
}

if ($bootPath === null) {
    die('Boot file not found. Please check your installation.');
}

// Načítanie a spustenie aplikácie z boot/app.php
$app = require $bootPath;

// Spustenie aplikácie
$app->run();
EOL;
file_put_contents($buildDir . '/public/index.php', $indexContent);

// Vytvorenie súboru s inštrukciami pre nasadenie
echo "INFO: Vytváram súbor s inštrukciami pre nasadenie...\n";
$readme = <<<'EOL'
# Inštrukcie pre nasadenie na shared hosting

## Príprava
1. Nahrajte všetky súbory a adresáre na váš hosting pomocou FTP klienta
2. Uistite sa, že adresáre `var` a `data` majú práva na zápis (chmod 755 alebo 777)
3. Ak váš hosting podporuje PHP 8.0+, nie sú potrebné žiadne ďalšie úpravy

## Konfigurácia
1. Ak je potrebné, upravte súbor `config/settings.php` podľa vášho hostingu
2. Ak používate databázu, upravte prístupové údaje v `config/settings.php`

## Testovanie
1. Navštívte vašu doménu v prehliadači
2. Ak vidíte chybu, skontrolujte logy na hostingu
3. Uistite sa, že mod_rewrite je povolený na vašom hostingu

## Riešenie problémov
1. Ak vidíte chybu 500, skontrolujte práva na súbory a adresáre
2. Ak vidíte chybu 404, skontrolujte konfiguráciu .htaccess
3. Ak máte problémy s databázou, skontrolujte prístupové údaje

## Kontakt
Ak máte akékoľvek otázky, kontaktujte nás na [email@example.com]
EOL;
file_put_contents($buildDir . '/README_DEPLOY.txt', $readme);

// Vytvorenie ZIP archívu
echo "INFO: Vytváram ZIP archív...\n";
$zip = new ZipArchive();
if ($zip->open($buildDir . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    // Prejdeme do adresára build_shared_hosting a pridáme všetky súbory priamo (bez adresára build_shared_hosting)
    $currentDir = getcwd();
    chdir($buildDir);
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator('.'),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, 2); // Odstránime './' zo začiatku cesty
            $zip->addFile($filePath, $relativePath);
        }
    }
    
    $zip->close();
    chdir($currentDir);
    echo "SUCCESS: ZIP archív bol úspešne vytvorený: {$buildDir}.zip\n";
} else {
    echo "ERROR: Nepodarilo sa vytvoriť ZIP archív\n";
}

// Vyčistenie
echo "INFO: Čistím...\n";
removeDirectory($buildDir);

echo "\nInštrukcie pre nasadenie:\n";
echo "1. Rozbaľte ZIP archív na vašom počítači\n";
echo "2. Nahrajte všetky súbory a adresáre na váš hosting pomocou FTP klienta\n";
echo "3. Uistite sa, že adresáre 'var' a 'data' majú práva na zápis\n";
echo "4. Navštívte vašu doménu v prehliadači\n\n";
echo "Pre viac informácií si prečítajte README_DEPLOY.txt v ZIP archíve\n";

/**
 * Pomocné funkcie
 */

/**
 * Kopíruje adresár rekurzívne
 */
function copyDirectory($source, $destination) {
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    $dir = opendir($source);
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $srcFile = $source . '/' . $file;
            $destFile = $destination . '/' . $file;

            if (is_dir($srcFile)) {
                copyDirectory($srcFile, $destFile);
            } else {
                copy($srcFile, $destFile);
            }
        }
    }
    closedir($dir);
}

/**
 * Odstraňuje adresár rekurzívne
 */
function removeDirectory($dir) {
    if (!is_dir($dir)) {
        return;
    }

    $objects = scandir($dir);
    foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
            if (is_dir($dir . "/" . $object)) {
                removeDirectory($dir . "/" . $object);
            } else {
                unlink($dir . "/" . $object);
            }
        }
    }
    rmdir($dir);
}

/**
 * Pridáva adresár do ZIP archívu rekurzívne
 */
function addDirToZip($zip, $dir, $basePath) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($basePath) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }
}
