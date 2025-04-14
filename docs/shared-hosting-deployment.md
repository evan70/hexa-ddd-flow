# Nasadenie aplikácie na shared hosting

Tento dokument popisuje, ako nasadiť aplikáciu na shared hosting a otestovať ju.

## Príprava nasadenia

Pre nasadenie aplikácie na shared hosting môžete použiť jeden z dvoch skriptov:

1. **Bash skript** (pre Linux/Mac):
   ```bash
   chmod +x bin/deploy-shared-hosting.sh
   ./bin/deploy-shared-hosting.sh
   ```

2. **PHP skript** (pre Windows alebo ak nemôžete používať Bash):
   ```bash
   php bin/deploy-shared-hosting.php
   ```

Bash skript vytvorí dva archívy:
- `build_shared_hosting.zip` (pre Windows používateľov)
- `build_shared_hosting.tar.gz` (pre Linux používateľov, zachováva Unix oprávnenia)

PHP skript vytvorí len ZIP archív `build_shared_hosting.zip`.

Oba archívy obsahujú všetky potrebné súbory pre nasadenie na shared hosting, pričom:

- Obsahujú len produkčné závislosti (bez vývojových balíkov)
- Majú optimalizovaný autoloader pre lepší výkon
- Majú nastavený produkčný mód (vypnuté zobrazovanie chýb, zapnuté logovanie)

## Produkčné nastavenia

Skripty automaticky nastavia nasledujúce produkčné nastavenia:

1. **Optimalizácia závislostí**
   - Inštalácia len produkčných závislostí (`--no-dev`)
   - Optimalizácia autoloadera (`--optimize-autoloader`)

2. **Nastavenie produkčného módu**
   - Vypnutie zobrazovania chýb (`displayErrorDetails => false`)
   - Zapnutie logovania chýb (`logErrors => true`)
   - Zapnutie logovania detailov chýb (`logErrorDetails => true`)

## Obsah archívu

Archív (ZIP alebo TAR.GZ) obsahuje nasledujúce súbory a adresáre:

- `public/` - verejne dostupné súbory
- `src/` - zdrojový kód aplikácie
- `config/` - konfiguračné súbory
- `boot/` - bootstrap súbory
- `resources/` - šablóny a assets
- `vendor/` - závislosti
- `var/` - dočasné súbory (cache, logy)
- `data/` - dáta aplikácie (SQLite databázy)
- `composer.json` a `composer.lock` - definícia závislostí
- `.htaccess` - konfigurácia Apache
- `index.php` - presmerovanie na public/index.php
- `README_DEPLOY.txt` - inštrukcie pre nasadenie

## Kroky nasadenia

1. **Rozbaľte archív** na vašom počítači
   - Pre Windows použite ZIP archív
   - Pre Linux odporúčame použiť TAR.GZ archív, ktorý zachováva Unix oprávnenia
2. **Nahrajte všetky súbory a adresáre** na váš hosting pomocou FTP klienta
   - Môžete použiť FileZilla, WinSCP alebo iný FTP klient
   - Nahrajte súbory do koreňového adresára vášho hostingu (napr. `public_html`, `www` alebo `htdocs`)
3. **Nastavte práva na zápis** pre adresáre `var` a `data`
   - Použite FTP klienta na nastavenie práv (chmod) na 755 alebo 777
4. **Upravte konfiguračné súbory** podľa potreby
   - Ak používate databázu, upravte prístupové údaje v `config/settings.php`
   - Ak je potrebné, upravte ďalšie nastavenia v `config/settings.php`

## Testovanie aplikácie

1. **Navštívte vašu doménu** v prehliadači
   - Napríklad: `http://vasa-domena.sk`
2. **Otestujte základné funkcie** aplikácie
   - Prihlásenie/odhlásenie
   - Vytvorenie/úprava/odstránenie článkov
   - Zobrazenie článkov
3. **Skontrolujte logy** na hostingu, ak sa vyskytnú problémy
   - Logy sa zvyčajne nachádzajú v adresári `var/log` alebo v adresári definovanom hostingom

## Riešenie problémov

### Chyba 500 (Internal Server Error)
- Skontrolujte práva na súbory a adresáre
- Skontrolujte, či váš hosting podporuje PHP 8.0+
- Skontrolujte logy na hostingu

### Chyba 404 (Not Found)
- Skontrolujte, či je mod_rewrite povolený na vašom hostingu
- Skontrolujte konfiguráciu .htaccess
- Skontrolujte, či sú všetky súbory správne nahrané

### Problémy s databázou
- Skontrolujte prístupové údaje v `config/settings.php`
- Skontrolujte, či váš hosting podporuje SQLite alebo MySQL
- Skontrolujte, či má adresár `data` práva na zápis

## Špecifické nastavenia pre rôzne hostingy

### Hosting s podporou PHP 8.0+
- Nie sú potrebné žiadne špeciálne nastavenia

### Hosting s PHP 7.4
- Aplikácia vyžaduje PHP 8.0+, ale môžete skúsiť upraviť `composer.json` a znížiť požiadavku na PHP verziu
- Niektoré funkcie nemusia fungovať správne

### Hosting bez podpory mod_rewrite
- Upravte `.htaccess` súbory podľa dokumentácie vášho hostingu
- Môže byť potrebné použiť alternatívne riešenie pre pekné URL

## Záver

Nasadenie aplikácie na shared hosting je relatívne jednoduché, ak váš hosting podporuje PHP 8.0+ a mod_rewrite. Ak sa vyskytnú problémy, skontrolujte logy a dokumentáciu vášho hostingu.

Pre ďalšie otázky alebo pomoc kontaktujte podporu.
