# MarkCMS - Hexagonálna architektúra so Slim 4, SQLite, Twig, Ramsey UUID, TailwindCSS a GSAP

MarkCMS je moderný systém na správu obsahu implementovaný pomocou hexagonálnej architektúry (ports and adapters) a Domain-Driven Design princípov. Systém poskytuje správu používateľov a článkov s podporou SEO friendly URL, tmavého režimu a moderného responzívneho dizajnu.

## Štruktúra projektu

```
📂 src
├── 📂 Domain          <- Doménové triedy
│   ├── User.php      (trieda s konštantami pre role používateľov)
│   ├── Article.php   (trieda s konštantami pre typy článkov)
│   ├── UserFactory.php    (Factory pre vytváranie používateľov)
│   ├── ArticleFactory.php (Factory pre vytváranie článkov)
│   ├── UuidGenerator.php  (Generátor UUID pomocou Ramsey/UUID - deprecated)
│   └── 📂 ValueObject
│       └── Uuid.php       (Value Object pre UUID)
│
├── 📂 Application     <- Aplikačná vrstva
│   └── 📂 Service
│       ├── ArticleService.php    (Služba pre prácu s článkami)
│       └── UserService.php       (Služba pre prácu s používateľmi)
│
├── 📂 Ports          <- Rozhrania pre služby
│   ├── UserRepositoryInterface.php
│   └── ArticleRepositoryInterface.php
│
└── 📂 Infrastructure <- Implementácie portov
    ├── 📂 Persistence
    │   ├── DatabaseUserRepository.php       (implementuje UserRepositoryInterface)
    │   └── DatabaseArticleRepository.php    (implementuje ArticleRepositoryInterface)
    ├── 📂 External
    │   └── ApiArticleRepository.php        (ukážková implementácia - nepoužíva sa)
    ├── 📂 Controller
    │   ├── AbstractController.php         (abstraktná základná trieda pre kontroléry)
    │   ├── UserController.php              (controller pre používateľov)
    │   └── ArticleController.php           (controller pre články)
    ├── 📂 Helper
    │   └── ViteAssetHelper.php            (Helper pre Vite.js assets)
    ├── 📂 Twig
    │   └── UuidExtension.php              (Twig extension pre UUID)
    └── 📂 Middleware
        ├── ErrorHandlerMiddleware.php      (spracovanie chýb vrátane 404)
        └── UuidValidatorMiddleware.php     (validácia UUID v požiadavkách)

📂 config
├── settings.php      <- Konfigurácia aplikácie
├── dependencies.php  <- Definície závislostí (DI)
└── routes.php        <- Definície rout

📂 public
├── index.php         <- Vstupný bod aplikácie
├── .htaccess         <- Konfigurácia Apache
└── 📂 build          <- Skompilované assets (generované Vite.js)

📂 resources          <- Zdrojové súbory pre frontend
├── 📂 css
│   └── app.css       <- Hlavný CSS súbor s TailwindCSS
├── 📂 js
│   ├── main.js       <- Hlavný JS súbor s lazy loadingom
│   └── 📂 components <- JavaScript komponenty
│       ├── darkMode.js   (Komponent pre tmavý režim)
│       └── animations.js (Komponent pre animácie)
├── 📂 images         <- Obrázky
├── 📂 fonts          <- Fonty
└── 📂 views          <- Twig šablóny
    ├── layout.twig       <- Základná šablóna
    ├── home.twig         <- Domovská stránka
    ├── 📂 errors
    │   ├── 404.twig      <- Šablóna pre 404 chybu
    │   └── 500.twig      <- Šablóna pre 500 chybu
    ├── 📂 articles
    │   ├── list.twig     <- Zoznam článkov
    │   └── detail.twig   <- Detail článku
    └── 📂 users
        └── list.twig     <- Zoznam používateľov

📂 data
├── users.sqlite      <- SQLite databáza pre používateľov
└── articles.sqlite   <- SQLite databáza pre články

📂 var
└── 📂 cache
    └── 📂 twig       <- Cache pre Twig šablóny

📂 bin
├── init-db.php                <- PHP skript pre inicializáciu databázy
├── init-db.sh                 <- Shell skript pre spustenie inicializácie
├── list-routes.php            <- Farebný výpis všetkých dostupných rout
├── list-routes-simple.php      <- Jednoduchý výpis rout pre shared hosting
├── add-slug-column.php         <- Skript pre pridanie stĺpca slug do tabuľky articles
├── deploy-shared-hosting.sh    <- Skript pre nasadenie na shared hosting (Linux)
└── deploy-shared-hosting.php   <- Skript pre nasadenie na shared hosting (Windows)

📂 public
├── index.php         <- Vstupný bod aplikácie
├── .htaccess         <- Konfigurácia Apache
├── 📂 build          <- Skompilované assets (generované Vite.js)
└── 📂 debug          <- Nástroje pre debugovanie
    ├── routes.php      <- Webový výpis dostupných rout
    └── .htaccess       <- Zabezpečenie adresára debug

📂 docs               <- Dokumentácia
├── frontend-optimization.md    <- Dokumentácia optimalizácie frontendu
├── architecture-refactoring.md  <- Dokumentácia refaktoringu architektúry
├── recent-improvements.md      <- Zoznam nedávnych vylepšení
├── optimization-plan.md        <- Plán optimalizácie projektu
├── shared-hosting-deployment.md <- Návod na nasadenie na shared hosting
├── release-process.md          <- Proces vytvorenia release
├── testing.md                  <- Návod na spúšťanie testov
├── test-results.md             <- Výsledky testov
├── debugging.md                <- Nástroje pre debugovanie
├── user-guide.md               <- Používateľská príručka
├── api-reference.md            <- API referencia
└── installation-guide.md       <- Inštalačná príručka
```

## Čistenie kódu

V rámci auditu a čistenia kódu boli vykonané tieto zmeny:

1. **Odstránené nepoužívané súbory**
   - Odstránený `test-enum.php` - testovací súbor, ktorý nebol súčasťou aplikácie
   - Odstránený `public/build/index.php` - duplicitný súbor v build adresári

2. **Označené ukážkové kódy**
   - `ApiArticleRepository.php` - označený ako ukážkový kód, ktorý nie je používaný v aplikácii

3. **Pridané konfiguračné súbory**
   - Vytvorený `.gitignore` - pre ignorovanie súborov, ktoré nemajú byť verzované
   - Vytvorený `.env.example` - ukážkový súbor s konfiguráciou prostredia

4. **Refaktoring architektúry**
   - Pridaná aplikačná vrstva (Application Layer) so službami
   - Vytvorená abstraktná základná trieda pre kontroléry
   - Odstránená duplicita kódu v kontroléroch
   - Lepšie dodržiavanie princípov hexagonálnej architektúry a DDD
   - Podrobná dokumentácia v `docs/architecture-refactoring.md`

## UUID Value Object

Aplikácia používa UUID Value Object pre prácu s UUID:

```php
// Vytvorenie UUID z reťazca
$uuid = new Uuid('550e8400-e29b-41d4-a716-446655440000');

// Generovanie nového UUID
$uuid = Uuid::generate();

// Získanie nil UUID (00000000-0000-0000-0000-000000000000)
$uuid = Uuid::nil();

// Vytvorenie UUID z reťazca s validáciou
$uuid = Uuid::fromString('550e8400-e29b-41d4-a716-446655440000');
if ($uuid === null) {
    // Neplatné UUID
}

// Získanie hodnoty UUID
$value = $uuid->getValue();

// Porovnanie s iným UUID
if ($uuid->equals($otherUuid)) {
    // UUID sú rovnaké
}

// Konverzia na reťazec
$string = (string) $uuid;
```

## UUID Validator Middleware

Aplikácia používa middleware pre automatickú validáciu UUID v požiadavkách:

```php
// Registrácia middleware v index.php
$app->add($container->get(UuidValidatorMiddleware::class));

// Alebo priamo na konkrétnej route
$app->get('/users/{id}', [UserController::class, 'show'])
    ->add(UuidValidatorMiddleware::class);
```

Middleware automaticky validuje:
- UUID v parametroch URL (napr. `/articles/{id}`)
- UUID v tele požiadavky (napr. `author_id` v POST požiadavkách)

Ak je UUID neplatné, middleware vráti chybovú odpoveď s kódom 400 Bad Request.

## Twig Extension pre UUID

Aplikácia poskytuje Twig extension pre prácu s UUID v šablónach:

```twig
{# Generovanie nového UUID #}
{{ generate_uuid() }}

{# Validácia UUID #}
{% if is_valid_uuid('550e8400-e29b-41d4-a716-446655440000') %}
    UUID je platné
{% else %}
    UUID je neplatné
{% endif %}

{# Skrátenie UUID #}
{{ uuid_short('550e8400-e29b-41d4-a716-446655440000') }} {# Výstup: 550e8400... #}
{{ uuid_short('550e8400-e29b-41d4-a716-446655440000', 12) }} {# Výstup: 550e8400-e29b... #}
```

## Optimalizácia frontendu

Aplikácia používa lazy loading komponentov pre optimalizáciu výkonu:

```javascript
// Lazy loading komponentov
const loadComponent = async (name) => {
  return import(`./components/${name}.js`)
    .then(module => module.default)
    .catch(() => null);
};

// Použitie
document.addEventListener('DOMContentLoaded', async () => {
  const darkMode = await loadComponent('darkMode');
  if (darkMode) darkMode.init();

  const animations = await loadComponent('animations');
  if (animations) animations.init();
});
```

Výhody optimalizácie:
- **Rýchlejšie načítanie stránky** - Menšia počiatočná veľkosť JavaScript súborov
- **Lepšia výkonnosť** - Komponenty sa načítavajú až keď sú potrebné
- **Lepšia organizácia kódu** - Kód je rozdelený do menších, znovupoužiteľných komponentov

Viac informácií nájdete v [dokumentácii optimalizácie frontendu](docs/frontend-optimization.md).

## Tmavý režim

Aplikácia podporuje tmavý režim, ktorý môžete prepnúť pomocou prepínača v pravom hornom rohu:

- **Automatická detekcia** - Aplikácia automaticky detekuje preferovaný režim používateľa
- **Ukladanie preferencie** - Preferencia je uložená v localStorage
- **Plynulé prechody** - Prechody medzi režimami sú animované

## Frontend s TailwindCSS a GSAP

Aplikácia používa moderný frontend stack:

- **TailwindCSS** - Utility-first CSS framework pre rýchle vytváranie responzívnych dizajnov
- **GSAP (GreenSock Animation Platform)** - Profesionálna knižnica pre animácie
- **Vite.js** - Moderný build tool pre frontend assets

### Animácie s GSAP

Aplikácia obsahuje niekoľko typov animácií:

- **Fade In** - Postupné zobrazenie elementu
- **Slide Up** - Posunutie elementu zdola nahor
- **Slide In Left** - Posunutie elementu zľava doprava
- **Slide In Right** - Posunutie elementu sprava doľava

Tieto animácie sú implementované pomocou GSAP ScrollTrigger, ktorý spúšťa animácie pri scrollovaní.

## Composer skripty

Aplikácia poskytuje niekoľko composer skriptov pre bežné úlohy:

```bash
# Spustenie PHP servera
composer start

# Inicializácia databázy
composer init-db

# Spustenie vývojového prostredia
composer dev

# Spustenie testov
composer test

# Spustenie testov s podrobným výpisom
composer test:verbose

# Spustenie unit testov
composer test:unit

# Spustenie integračných testov
composer test:integration

# Generovanie coverage reportu
composer test:coverage

# Spustenie všetkých testov s prehľadným výpisom
composer test:all

# Analýza kódu pomocou PHPStan
composer phpstan

# Kontrola kódového štýlu
composer cs

# Automatická oprava kódového štýlu
composer cs-fix
```

## Inštalácia a spustenie

1. Nainštalujte PHP závislosti:
   ```
   composer install
   ```

2. Nainštalujte Node.js závislosti:
   ```
   pnpm install
   ```

3. Inicializujte databázu:
   ```
   composer init-db
   ```

4. Spustite vývojový server:
   ```
   composer dev
   ```

5. Aplikácia bude dostupná na adrese `http://localhost:8080`

## Produkčné nasadenie

### Nasadenie na shared hosting

1. Vytvorte archív pre nasadenie:
   ```bash
   # Pre Linux používateľov (zachováva Unix oprávnenia)
   ./bin/deploy-shared-hosting.sh

   # Pre Windows používateľov
   php bin/deploy-shared-hosting.php
   ```

2. Rozbaľte archív (`build_shared_hosting.zip` alebo `build_shared_hosting.tar.gz`)

3. Nahrajte všetky súbory na váš hosting pomocou FTP klienta

4. Nastavte práva na zápis pre adresáre `var` a `data` (chmod 755 alebo 777)

5. Navštívte vašu doménu v prehliadači

Podrobný návod nájdete v [dokumentácii nasadenia na shared hosting](docs/shared-hosting-deployment.md).

### Manuálne nasadenie

1. Skompilujte frontend assets:
   ```
   composer build
   ```

2. Optimalizujte autoloader:
   ```
   composer install --no-dev --optimize-autoloader
   ```

3. Nasaďte aplikáciu na produkčný server

## Výhody tejto architektúry

1. **Oddelenie logiky od infraštruktúry**
   - Biznis logika je izolovaná od infraštruktúrnych detailov
   - Jednoduchá na pochopenie a údržbu

2. **Vymeniteľnosť adaptérov**
   - Jednoduché prepínanie medzi rôznymi implementáciami (databáza, API, atď.)
   - Nové adaptéry môžu byť pridané bez zmeny doménovej logiky

3. **Testovateľnosť**
   - Jednoduché mockovanie závislostí pre unit testy
   - Doménová logika môže byť testovaná nezávisle od infraštruktúry

4. **Globálne unikátne identifikátory**
   - UUID zabezpečujú globálnu unikátnosť identifikátorov
   - Nie je potrebná centrálna koordinácia pri generovaní ID
   - Lepšia škálovateľnosť a distribúcia dát

5. **Moderný frontend**
   - TailwindCSS pre rýchly a konzistentný dizajn
   - GSAP pre profesionálne animácie
   - Vite.js pre rýchly development a optimalizovaný build

## Dokumentácia

### Pre vývojárov

- [Nedávne vylepšenia](docs/recent-improvements.md) - Zoznam nedávnych vylepšení
- [Plán optimalizácie](docs/optimization-plan.md) - Plán optimalizácie projektu
- [Proces vytvorenia release](docs/release-process.md) - Proces mergovania a vytvorenia release
- [Návod na spúšťanie testov](docs/testing.md) - Návod na spúšťanie testov
- [Výsledky testov](docs/test-results.md) - Výsledky testov
- [Nástroje pre debugovanie](docs/debugging.md) - Nástroje pre debugovanie

### Pre používateľov

- [Používateľská príručka](docs/user-guide.md) - Návod pre bežných používateľov
- [API referencia](docs/api-reference.md) - Dokumentácia verejného API
- [Inštalačná príručka](docs/installation-guide.md) - Návod na inštaláciu a nasadenie
- [Nasadenie na shared hosting](docs/shared-hosting-deployment.md) - Návod na nasadenie na shared hosting

## Funkcie

### SEO friendly URL

Aplikácia podporuje SEO friendly URL pomocou slugov:

- Automatické generovanie slugov z názvu článku
- URL v tvare `/web/view/{type}/{slug}` namiesto `/web/view/articles/{id}`
- Podpora diakritiky a špeciálnych znakov

### Debugovacie nástroje

Aplikácia poskytuje nástroje pre debugovanie:

- Farebný výpis všetkých dostupných rout (`bin/list-routes.php`)
- Jednoduchý výpis rout pre shared hosting (`bin/list-routes-simple.php`)
- Webový výpis rout (`public/debug/routes.php`)

Viac informácií nájdete v [dokumentácii debugovacích nástrojov](docs/debugging.md).

### Testy

Aplikácia obsahuje 39 testov a 105 asercí, ktoré testujú všetky dôležité časti systému:

- Unit testy pre doménové triedy a služby
- Integračné testy pre repozitáre

Testy môžete spustiť pomocou príkazu `composer test:verbose` alebo `composer test:all`.

Viac informácií nájdete v [dokumentácii testov](docs/testing.md).
