#!/bin/bash

# Vytvorenie adresára pre databázy, ak neexistuje
mkdir -p "$(dirname "$(dirname "$0")")/data"

# Spustenie PHP skriptu pre inicializáciu databázy
php "$(dirname "$0")/init-db.php"
