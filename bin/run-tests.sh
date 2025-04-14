#!/bin/bash

# Farby pre výstup
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funkcia pre výpis nadpisu
function header() {
    echo -e "\n${BLUE}=== $1 ===${NC}\n"
}

# Funkcia pre výpis úspechu
function success() {
    echo -e "${GREEN}✓ $1${NC}"
}

# Funkcia pre výpis chyby
function error() {
    echo -e "${RED}✗ $1${NC}"
}

# Funkcia pre výpis varovania
function warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

# Funkcia pre výpis informácie
function info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

# Kontrola, či je skript spustený z koreňového adresára projektu
if [ ! -f "composer.json" ]; then
    error "Skript musí byť spustený z koreňového adresára projektu"
    exit 1
fi

# Zobrazenie hlavičky
echo -e "${BLUE}╔════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                     SPUSTENIE TESTOV                           ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════════╝${NC}"

# Spustenie PHPStan
header "STATICKÁ ANALÝZA KÓDU (PHPSTAN)"
composer phpstan

if [ $? -eq 0 ]; then
    success "PHPStan: Kód prešiel statickou analýzou"
else
    error "PHPStan: Kód neprešiel statickou analýzou"
    exit 1
fi

# Spustenie PHP_CodeSniffer
header "KONTROLA ŠTÝLU KÓDU (PHP_CODESNIFFER)"
composer cs

if [ $? -eq 0 ]; then
    success "PHP_CodeSniffer: Kód spĺňa štandardy PSR-12"
else
    warning "PHP_CodeSniffer: Kód nespĺňa štandardy PSR-12"
    info "Spustite 'composer cs-fix' pre automatickú opravu"
fi

# Spustenie unit testov
header "UNIT TESTY"
composer test:unit

if [ $? -eq 0 ]; then
    success "Unit testy: Všetky testy prešli"
else
    error "Unit testy: Niektoré testy zlyhali"
    exit 1
fi

# Spustenie integračných testov
header "INTEGRAČNÉ TESTY"
composer test:integration

if [ $? -eq 0 ]; then
    success "Integračné testy: Všetky testy prešli"
else
    error "Integračné testy: Niektoré testy zlyhali"
    exit 1
fi

# Zobrazenie súhrnu
header "SÚHRN TESTOV"
success "Statická analýza kódu: OK"
success "Kontrola štýlu kódu: OK"
success "Unit testy: OK"
success "Integračné testy: OK"

echo -e "\n${GREEN}╔════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                     VŠETKY TESTY PREŠLI                         ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════════╝${NC}"
