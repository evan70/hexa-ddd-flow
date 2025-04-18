#!/bin/bash

# Skript pre nasadenie aplikácie na shared hosting
# Autor: evan70
# Dátum: $(date +%Y-%m-%d)

# Farby pre výstup
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funkcia pre výpis správy
function log() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

# Funkcia pre výpis chyby
function error() {
    echo -e "${RED}[ERROR]${NC} $1"
    exit 1
}

# Funkcia pre výpis úspechu
function success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

# Funkcia pre výpis varovania
function warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Kontrola, či je skript spustený z koreňového adresára projektu
if [ ! -f "composer.json" ]; then
    error "Skript musí byť spustený z koreňového adresára projektu"
fi

# Spustenie PHPStan pre kontrolu kódu
log "Spustenie PHPStan pre kontrolu kódu..."

# Kontrola, či je PHPStan dostupný
if command -v phpstan &> /dev/null; then
    phpstan analyse src --level=5
    PHPSTAN_RESULT=$?
else
    # Použitie PHPStan cez Composer
    composer phpstan
    PHPSTAN_RESULT=$?
fi

# Ak PHPStan zlyhal, ukončíme skript
if [ $PHPSTAN_RESULT -ne 0 ]; then
    warning "PHPStan našiel chyby v kóde alebo nie je dostupný."
    warning "Chcete pokračovať aj napriek tomu? (y/n)"
    read -r answer
    if [ "$answer" != "y" ] && [ "$answer" != "Y" ]; then
        error "Nasadenie bolo prerušené."
        exit 1
    fi
    success "Pokračujeme v nasadení bez kontroly kódu..."
fi

# Vytvorenie adresára pre build
log "Vytváram adresár pre build..."
BUILD_DIR="build_shared_hosting"
rm -rf $BUILD_DIR
mkdir -p $BUILD_DIR

# Inštalácia produkčných závislostí
log "Inštalujem produkčné závislosti..."
cp composer.json $BUILD_DIR/
cp composer.lock $BUILD_DIR/
cd $BUILD_DIR
composer install --no-dev --optimize-autoloader --quiet
cd ..

# Kopírovanie potrebných súborov a adresárov
log "Kopírujem potrebné súbory a adresáre..."
cp -r public $BUILD_DIR/
cp -r src $BUILD_DIR/
cp -r config $BUILD_DIR/
cp -r boot $BUILD_DIR/
cp -r resources $BUILD_DIR/
# vendor už je nainštalovaný v predchádzajúcom kroku
mkdir -p $BUILD_DIR/var
mkdir -p $BUILD_DIR/data
cp .htaccess $BUILD_DIR/ 2>/dev/null || touch $BUILD_DIR/.htaccess

# Nastavenie produkčného módu v settings.php
log "Nastavujem produkčný mód..."
if [ -f "$BUILD_DIR/config/settings.php" ]; then
    sed -i 's/"displayErrorDetails" => true/"displayErrorDetails" => false/g' "$BUILD_DIR/config/settings.php"
    sed -i 's/"logErrors" => false/"logErrors" => true/g' "$BUILD_DIR/config/settings.php"
    sed -i 's/"logErrorDetails" => false/"logErrorDetails" => true/g' "$BUILD_DIR/config/settings.php"
fi

# Vytvorenie .htaccess súboru pre public adresár, ak neexistuje
if [ ! -f "$BUILD_DIR/public/.htaccess" ]; then
    log "Vytváram .htaccess súbor pre public adresár..."
    cat > $BUILD_DIR/public/.htaccess << 'EOL'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
EOL
fi

# Vytvorenie .htaccess súboru pre koreňový adresár, ak neexistuje
if [ ! -f "$BUILD_DIR/.htaccess" ]; then
    log "Vytváram .htaccess súbor pre koreňový adresár..."
    cat > $BUILD_DIR/.htaccess << 'EOL'
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
EOL
fi

# Vytvorenie index.php súboru pre koreňový adresár, ak neexistuje
if [ ! -f "$BUILD_DIR/index.php" ]; then
    log "Vytváram index.php súbor pre koreňový adresár..."
    cat > $BUILD_DIR/index.php << 'EOL'
<?php
header('Location: public/');
EOL
fi

# Upravenie public/index.php pre shared hosting
log "Upravujem public/index.php pre shared hosting..."
cat > $BUILD_DIR/public/index.php << 'EOL'
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
EOL

# Vytvorenie súboru s inštrukciami pre nasadenie
log "Vytváram súbor s inštrukciami pre nasadenie..."
cat > $BUILD_DIR/README_DEPLOY.txt << 'EOL'
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
EOL

# Vytvorenie ZIP archívu pre Windows používateľov
log "Vytváram ZIP archív..."
cd $BUILD_DIR
zip -r "../${BUILD_DIR}.zip" . > /dev/null
cd ..

# Vytvorenie TAR.GZ archívu pre Linux používateľov (zachováva oprávnenia)
log "Vytváram TAR.GZ archív..."
cd $BUILD_DIR
tar -czf "../${BUILD_DIR}.tar.gz" . > /dev/null
cd ..

# Vyčistenie
log "Čistím..."
rm -rf $BUILD_DIR

success "Build pre shared hosting bol úspešne vytvorený:"
success "- ZIP: ${BUILD_DIR}.zip (pre Windows)"
success "- TAR.GZ: ${BUILD_DIR}.tar.gz (pre Linux, zachováva oprávnenia)"
echo ""
echo "Inštrukcie pre nasadenie:"
echo "1. Rozbaľte archív na vašom počítači (ZIP pre Windows, TAR.GZ pre Linux)"
echo "2. Nahrajte všetky súbory a adresáre na váš hosting pomocou FTP klienta"
echo "3. Uistite sa, že adresáre 'var' a 'data' majú práva na zápis"
echo "4. Navštívte vašu doménu v prehliadači"
echo ""
echo "Pre viac informácií si prečítajte README_DEPLOY.txt v archíve"
echo ""
echo "Poznámka: TAR.GZ archív zachováva Unix oprávnenia, symlinky a ďalšie metadáta,"
echo "          ktoré ZIP nemusí správne zachovať. Odporúčame použiť TAR.GZ pre Linux."
