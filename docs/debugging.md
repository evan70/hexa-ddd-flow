# Nástroje pre debugovanie

Tento dokument popisuje nástroje pre debugovanie aplikácie, ktoré sú k dispozícii v projekte.

## Výpis dostupných rout

Pre debugovanie a vývoj aplikácie je často užitočné mať prehľad o všetkých dostupných routách. Projekt poskytuje niekoľko nástrojov na výpis rout:

### Konzolové skripty

#### 1. Farebný výpis rout v konzole

```bash
php bin/list-routes.php
```

Tento skript vypíše všetky dostupné routy v aplikácii s farebným zvýraznením metód (GET, POST, PUT, DELETE, atď.). Je vhodný pre lokálne vývojové prostredie.

Výstup obsahuje:
- Metódu (GET, POST, PUT, DELETE, atď.)
- Cestu (pattern)
- Názov routy (ak je definovaný)
- Handler (controller a metóda)
- Súhrn počtu rout podľa metódy
- Zoznam použitých middlewarov

#### 2. Jednoduchý výpis rout v konzole

```bash
php bin/list-routes-simple.php
```

Tento skript je podobný ako predchádzajúci, ale bez farebného zvýraznenia. Je vhodný pre shared hosting alebo prostredia, kde farebný výstup nie je podporovaný.

### Webový výpis rout

Pre výpis rout priamo cez webový prehliadač môžete použiť:

```
http://vasa-domena.sk/debug/routes.php
```

Táto stránka poskytuje interaktívny výpis všetkých rout s možnosťou vyhľadávania. Je to užitočné, keď nemáte prístup k príkazovému riadku alebo potrebujete rýchlo skontrolovať dostupné routy na produkčnom serveri.

#### Zabezpečenie webového výpisu

V produkčnom prostredí by mal byť webový výpis rout zabezpečený. Môžete použiť:

1. **Basic Authentication**:
   - Skript automaticky vyžaduje heslo v produkčnom prostredí
   - Predvolené heslo je `debug123` (zmeňte ho v súbore `public/debug/routes.php`)
   - Môžete nastaviť vlastné heslo pomocou premennej prostredia `DEBUG_PASSWORD`

2. **Obmedzenie prístupu pomocou .htaccess**:
   - V súbore `public/debug/.htaccess` sú pripravené komentované pravidlá
   - Môžete povoliť prístup len z určitých IP adries
   - Môžete použiť Basic Auth na úrovni webového servera

3. **Odstránenie súborov**:
   - Pred nasadením na produkciu môžete jednoducho odstrániť adresár `public/debug`

## Príklady použitia

### Hľadanie konkrétnej routy

```bash
php bin/list-routes.php | grep "articles"
```

### Výpis len GET rout

```bash
php bin/list-routes.php | grep "GET"
```

### Kontrola middlewarov

Skript `bin/list-routes.php` zobrazuje aj zoznam middlewarov použitých v aplikácii, čo je užitočné pre kontrolu zabezpečenia a autentifikácie.

## Riešenie problémov na shared hostingu

Pri debugovaní na shared hostingu môžete naraziť na rôzne obmedzenia. Tu sú tipy, ako ich obísť:

1. **Použite jednoduchý skript bez farebného výstupu**:
   ```bash
   php bin/list-routes-simple.php
   ```

2. **Použite webový výpis rout**:
   - Nahrajte súbor `public/debug/routes.php` na server
   - Navštívte URL `http://vasa-domena.sk/debug/routes.php`

3. **Presmerovanie výstupu do súboru**:
   ```bash
   php bin/list-routes-simple.php > routes.txt
   ```

4. **Kontrola konkrétnej routy**:
   - Ak máte problém s konkrétnou routou, môžete ju vyhľadať vo výpise
   - Skontrolujte, či je správne definovaná a či má správny handler

## Bezpečnostné odporúčania

1. **Nikdy nenechávajte debugovacie nástroje dostupné na produkčnom serveri bez zabezpečenia**
2. **Zmeňte predvolené heslo pre prístup k webovému výpisu rout**
3. **Obmedzte prístup k debugovacím nástrojom len na určité IP adresy**
4. **Po dokončení debugovania odstráňte alebo zabezpečte všetky debugovacie súbory**
