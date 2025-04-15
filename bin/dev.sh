#!/bin/bash

# Skript pre spustenie vývojového prostredia
# Spustí Vite dev server a PHP server súčasne

# Farby pre výstup
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Funkcia pre ukončenie všetkých procesov pri ukončení skriptu
function cleanup {
    echo -e "${YELLOW}Ukončujem všetky procesy...${NC}"
    kill $VITE_PID $PHP_PID 2>/dev/null
    exit
}

# Zachytenie signálov pre ukončenie
trap cleanup SIGINT SIGTERM

# Spustenie Vite dev servera
echo -e "${GREEN}Spúšťam Vite dev server...${NC}"
pnpm run dev &
VITE_PID=$!

# Počkáme, kým sa Vite dev server spustí
sleep 2

# Spustenie PHP servera
echo -e "${GREEN}Spúšťam PHP server...${NC}"
php -S localhost:8080 -t public &
PHP_PID=$!

# Výpis informácií
echo -e "${GREEN}Vývojové prostredie je spustené:${NC}"
echo -e "${YELLOW}Vite dev server:${NC} http://localhost:5173"
echo -e "${YELLOW}PHP server:${NC} http://localhost:8080"
echo -e "${YELLOW}Pre ukončenie stlačte Ctrl+C${NC}"

# Čakáme na ukončenie
wait $VITE_PID $PHP_PID
