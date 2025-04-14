#!/bin/bash

# Skript pre nasadenie aplikácie na shared hosting
# Autor: Augment Agent
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

# Vytvorenie adresára pre build
log "Vytváram adresár pre build..."
BUILD_DIR="build_shared_hosting"
rm -rf $BUILD_DIR
mkdir -p $BUILD_DIR

# Kopírovanie potrebných súborov a adresárov
log "Kopírujem potrebné súbory a adresáre..."
cp -r public $BUILD_DIR/
cp -r src $BUILD_DIR/
cp -r config $BUILD_DIR/
cp -r boot $BUILD_DIR/
cp -r resources $BUILD_DIR/
cp -r vendor $BUILD_DIR/
cp -r var $BUILD_DIR/ 2>/dev/null || mkdir -p $BUILD_DIR/var
cp -r data $BUILD_DIR/ 2>/dev/null || mkdir -p $BUILD_DIR/data
cp composer.json $BUILD_DIR/
cp composer.lock $BUILD_DIR/
cp .htaccess $BUILD_DIR/ 2>/dev/null || touch $BUILD_DIR/.htaccess

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
zip -r "${BUILD_DIR}.zip" $BUILD_DIR > /dev/null

# Vytvorenie TAR.GZ archívu pre Linux používateľov (zachováva oprávnenia)
log "Vytváram TAR.GZ archív..."
tar -czf "${BUILD_DIR}.tar.gz" $BUILD_DIR > /dev/null

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
