# Inštalačná príručka

Táto príručka vás prevedie procesom inštalácie a nasadenia aplikácie MarkCMS na váš server.

## Obsah

1. [Systémové požiadavky](#systémové-požiadavky)
2. [Inštalácia](#inštalácia)
3. [Konfigurácia](#konfigurácia)
4. [Nasadenie na produkciu](#nasadenie-na-produkciu)
5. [Aktualizácia](#aktualizácia)
6. [Riešenie problémov](#riešenie-problémov)

## Systémové požiadavky

### Minimálne požiadavky

- PHP 8.0 alebo novší
- SQLite 3
- Webový server (Apache, Nginx)
- Composer

### Odporúčané požiadavky

- PHP 8.3 alebo novší
- SQLite 3 alebo MySQL 8.0
- Webový server (Apache, Nginx)
- Composer
- Node.js 18+ a npm/pnpm (pre frontend)
- 512 MB RAM
- 1 GB voľného miesta na disku

### PHP rozšírenia

- PDO
- pdo_sqlite (alebo pdo_mysql pre MySQL)
- json
- mbstring
- openssl
- tokenizer
- xml
- fileinfo
- gd (pre spracovanie obrázkov)

## Inštalácia

### Inštalácia pomocou Composer

1. Vytvorte nový projekt pomocou Composer:

```bash
composer create-project evan70/hexa-ddd-flow my-project
```

2. Prejdite do adresára projektu:

```bash
cd my-project
```

3. Spustite inštalačný skript:

```bash
composer init-db
```

### Manuálna inštalácia

1. Stiahnite najnovšiu verziu z [GitHub Releases](https://github.com/evan70/hexa-ddd-flow/releases)
2. Rozbaľte archív na váš server
3. Prejdite do adresára projektu
4. Nainštalujte závislosti:

```bash
composer install
```

5. Spustite inštalačný skript:

```bash
composer init-db
```

## Konfigurácia

### Základná konfigurácia

Základná konfigurácia sa nachádza v súbore `config/settings.php`. Tu môžete upraviť nastavenia databázy, cache, session a ďalšie.

```php
return [
    'displayErrorDetails' => true, // Nastavte na false v produkcii
    'logErrors' => true,
    'logErrorDetails' => true,
    'database' => [
        'articles' => [
            'path' => __DIR__ . '/../data/articles.sqlite'
        ],
        'users' => [
            'path' => __DIR__ . '/../data/users.sqlite'
        ],
        'settings' => [
            'path' => __DIR__ . '/../data/settings.sqlite'
        ]
    ],
    // Ďalšie nastavenia...
];
```

### Konfigurácia webového servera

#### Apache

Vytvorte súbor `.htaccess` v koreňovom adresári projektu:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^$ public/ [L]
    RewriteRule (.*) public/$1 [L]
</IfModule>
```

A súbor `.htaccess` v adresári `public`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/my-project/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### Konfigurácia databázy

Aplikácia predvolene používa SQLite, ktorý nevyžaduje žiadnu dodatočnú konfiguráciu. Ak chcete použiť MySQL, upravte súbor `config/settings.php`:

```php
'database' => [
    'articles' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'database' => 'markcms_articles',
        'username' => 'root',
        'password' => 'password',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ],
    // Podobne pre users a settings...
],
```

## Nasadenie na produkciu

### Príprava na nasadenie

1. Nastavte produkčné hodnoty v `config/settings.php`:

```php
'displayErrorDetails' => false,
'logErrors' => true,
'logErrorDetails' => true,
```

2. Optimalizujte autoloader:

```bash
composer install --no-dev --optimize-autoloader
```

3. Skompilujte frontend assets (ak používate Node.js):

```bash
npm run build
# alebo
pnpm build
```

### Nasadenie na shared hosting

Pre nasadenie na shared hosting môžete použiť pripravené skripty:

```bash
# Pre Linux používateľov (zachováva Unix oprávnenia)
./bin/deploy-shared-hosting.sh

# Pre Windows používateľov
php bin/deploy-shared-hosting.php
```

Tieto skripty vytvoria archív (`build_shared_hosting.zip` alebo `build_shared_hosting.tar.gz`), ktorý obsahuje všetky potrebné súbory pre nasadenie na shared hosting.

### Nasadenie pomocou FTP

1. Nahrajte všetky súbory na váš hosting pomocou FTP klienta
2. Nastavte práva na zápis pre adresáre `var` a `data` (chmod 755 alebo 777)
3. Navštívte vašu doménu v prehliadači

### Nasadenie pomocou Git

1. Pripojte sa na server cez SSH
2. Naklonujte repozitár:

```bash
git clone https://github.com/evan70/hexa-ddd-flow.git
```

3. Prejdite do adresára projektu:

```bash
cd hexa-ddd-flow
```

4. Nainštalujte závislosti:

```bash
composer install --no-dev --optimize-autoloader
```

5. Spustite inštalačný skript:

```bash
composer init-db
```

## Aktualizácia

### Aktualizácia pomocou Composer

```bash
composer update
```

### Aktualizácia pomocou Git

```bash
git pull
composer install --no-dev --optimize-autoloader
```

### Aktualizácia databázy

Po aktualizácii môže byť potrebné aktualizovať štruktúru databázy:

```bash
php bin/update-db.php
```

## Riešenie problémov

### Bežné problémy

#### Chyba 500 (Internal Server Error)

- Skontrolujte práva na súbory a adresáre
- Skontrolujte, či váš hosting podporuje PHP 8.0+
- Skontrolujte logy na hostingu

#### Chyba 404 (Not Found)

- Skontrolujte, či je mod_rewrite povolený na vašom hostingu
- Skontrolujte konfiguráciu .htaccess
- Skontrolujte, či sú všetky súbory správne nahrané

#### Problémy s databázou

- Skontrolujte prístupové údaje v `config/settings.php`
- Skontrolujte, či váš hosting podporuje SQLite alebo MySQL
- Skontrolujte, či má adresár `data` práva na zápis

### Logovanie

Logy aplikácie sa nachádzajú v adresári `var/log`. Ak máte problémy, skontrolujte tieto súbory:

- `var/log/app.log` - Aplikačné logy
- `var/log/error.log` - Chybové logy

### Kontakt na podporu

Ak máte problémy, ktoré nedokážete vyriešiť, kontaktujte nás na:
- E-mail: support@markcms.com
- GitHub Issues: [https://github.com/evan70/hexa-ddd-flow/issues](https://github.com/evan70/hexa-ddd-flow/issues)
