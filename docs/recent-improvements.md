# Nedávne vylepšenia aplikácie

Tento dokument obsahuje prehľad nedávnych vylepšení aplikácie, ktoré boli implementované v commite `55e38aa9d35e9aafc45a1a87631e08641b46c038` vo vetve `develop`.

## 1. Presun bootstrapu z public/index.php do boot/app.php

### Popis
Presunuli sme väčšinu logiky z `public/index.php` do nového súboru `boot/app.php`, čo zlepšuje organizáciu kódu a bezpečnosť aplikácie.

### Implementované zmeny
- Vytvorený nový adresár `/boot`
- Presunuta väčšina logiky z `public/index.php` do `boot/app.php`
- Upravený `public/index.php` tak, aby len načítal a spustil aplikáciu z `boot/app.php`

### Výhody
- Lepšia organizácia kódu
- Zvýšená bezpečnosť (väčšina logiky je mimo verejne dostupného adresára)
- Jednoduchšie testovanie aplikácie bez potreby simulovať HTTP požiadavky

## 2. Oprava CSRF ochrany - použitie slim/csrf a bryanjhv/slim-session

### Popis
Odstránili sme vlastnú implementáciu CSRF ochrany a nahradili ju oficiálnymi balíkmi `slim/csrf` a `bryanjhv/slim-session`.

### Implementované zmeny
- Odstránené súbory `src/Application/Service/CsrfService.php` a `src/Infrastructure/Middleware/CsrfMiddleware.php`
- Upravená konfigurácia v `config/dependencies.php` tak, aby používala len balíky `slim/csrf` a `bryanjhv/slim-session`
- Upravené šablóny tak, aby používali CSRF tokeny z `slim/csrf`

### Výhody
- Spoľahlivejšia CSRF ochrana
- Menej kódu na údržbu
- Využitie dobre otestovaných a udržiavaných balíkov

## 3. Vylepšenie prepínača témy (dark mode)

### Popis
Vylepšili sme prepínač témy (dark mode) tak, aby bol viditeľnejší a používal reverzné farby pre každú tému.

### Implementované zmeny
- Zväčšené ikony slnka a mesiaca (z h-3 w-3 na h-5 w-5)
- Pridané reverzné farby pre každú tému (text-yellow-500 dark:text-gray-400 pre slnko, text-gray-600 dark:text-yellow-400 pre mesiac)
- Upravené CSS pre prepínač témy (zväčšený prepínač, pridané focus ring)

### Výhody
- Lepšia viditeľnosť prepínača témy
- Intuitívnejšie používanie (ikony majú farby zodpovedajúce téme)
- Lepšia prístupnosť (väčšie ikony, focus ring)

## 4. Oprava problému s card-footer v sekcii Hlavné funkcie

### Popis
Opravili sme problém s `card-footer` v sekcii "Hlavné funkcie", kde bol footer posunutý vyššie, ako by mal byť.

### Implementované zmeny
- Pridané špecifické štýly pre karty v sekcii "Hlavné funkcie"
- Nastavená minimálna výška pre `card-body` (200px)
- Pridané flexbox layout pre karty

### Výhody
- Konzistentný vzhľad kariet bez ohľadu na množstvo textu
- Footer je vždy na spodku karty

## 5. Úprava prístupových práv - /login pre bežných používateľov, /mark pre adminov

### Popis
Upravili sme prístupové práva tak, aby bežní používatelia mali prístup len k `/login` a po prihlásení boli presmerovaní na `/`, zatiaľ čo administrátori majú prístup k `/mark` a po prihlásení sú presmerovaní na `/mark`.

### Implementované zmeny
- Upravený `AuthMiddleware` tak, aby vracal chybu 403 pre používateľov bez role 'admin'
- Vytvorená nová šablóna `error/403.twig` pre chybu 403
- Upravený `AuthController` tak, aby presmeroval používateľov podľa ich role

### Výhody
- Lepšia bezpečnosť (bežní používatelia nemajú prístup k admin rozhraniu)
- Lepšia používateľská skúsenosť (používatelia sú presmerovaní na správne miesto po prihlásení)
- Jasná spätná väzba pri pokuse o prístup k neoprávneným stránkam

## 6. Oprava formulára pre vytváranie článkov - pridanie author_id

### Popis
Opravili sme formulár pre vytváranie článkov, kde chýbalo povinné pole `author_id`.

### Implementované zmeny
- Pridané skryté pole `author_id` do formulára pre vytváranie článkov
- Upravený JavaScript kód pre lepšie spracovanie chýb
- Upravený `ArticleController` tak, aby vracal lepšie chybové hlásenia

### Výhody
- Formulár pre vytváranie článkov funguje správne
- Lepšie chybové hlásenia pri neúspešnom vytvorení článku

## 7. Implementácia slug pre články - SEO friendly URL

### Popis
Implementovali sme slug pre články, čo umožňuje používať SEO friendly URL namiesto ID článkov.

### Implementované zmeny
- Pridaný stĺpec `slug` do tabuľky `articles`
- Vytvorený migračný skript `bin/add-slug-column.php`
- Upravený `ArticleFactory` tak, aby generoval slug z názvu článku
- Pridaná metóda `findBySlug` do `DatabaseArticleRepository`
- Pridaná metóda `getArticleBySlug` do `ArticleService`
- Pridaná nová routa `/web/view/{type}/{slug}` pre zobrazenie článku podľa slugu
- Upravené šablóny tak, aby používali slug namiesto ID

### Výhody
- SEO friendly URL pre články
- Lepšia čitateľnosť URL
- Lepšia používateľská skúsenosť

## Záver

Tieto vylepšenia výrazne zlepšujú kvalitu kódu, bezpečnosť a používateľskú skúsenosť aplikácie. Všetky zmeny boli implementované s ohľadom na hexagonálnu architektúru a princípy Domain-Driven Design.

Ďalšie plánované vylepšenia:
- Implementácia automatického generovania slugu pri úprave názvu článku
- Pridanie validácie slugu (unikátnosť, platné znaky)
- Implementácia prekladov URL (slug v rôznych jazykoch)
