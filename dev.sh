#!/bin/bash

# Spustenie Vite v watch móde na pozadí
echo "Spúšťam Vite v watch móde..."
pnpm watch &
VITE_PID=$!

# Spustenie PHP servera
echo "Spúšťam PHP server..."
php -S localhost:8080 -t public

# Pri ukončení skriptu ukončiť aj Vite
trap "kill $VITE_PID" EXIT

# Čakanie na ukončenie PHP servera
wait
