# Spúšťanie testov

Tento dokument popisuje, ako spúšťať testy v projekte.

## Dostupné príkazy

V projekte sú k dispozícii nasledujúce príkazy pre spúšťanie testov:

### Základné príkazy

- **Spustenie všetkých testov**:
  ```bash
  composer test
  ```

- **Spustenie testov s podrobným výpisom**:
  ```bash
  composer test:verbose
  ```

- **Spustenie unit testov**:
  ```bash
  composer test:unit
  ```

- **Spustenie integračných testov**:
  ```bash
  composer test:integration
  ```

- **Generovanie coverage reportu**:
  ```bash
  composer test:coverage
  ```
  Report bude vygenerovaný v adresári `coverage`.

  > **Poznámka**: Pre generovanie coverage reportu je potrebné mať nainštalovaný a nakonfigurovaný Xdebug.
  > Skript automaticky nastaví `XDEBUG_MODE=coverage`, ale Xdebug musí byť nainštalovaný.
  >
  > Inštalácia Xdebug:
  > ```bash
  > sudo apt-get install php-xdebug  # Pre Ubuntu/Debian
  > sudo pecl install xdebug         # Pre ostatné systémy
  > ```
  >
  > Po inštalácii je potrebné pridať nasledujúce riadky do php.ini:
  > ```ini
  > [xdebug]
  > zend_extension=xdebug.so
  > xdebug.mode=debug,coverage
  > ```

### Pokročilé príkazy

- **Spustenie všetkých testov s prehľadným výpisom**:
  ```bash
  composer test:all
  ```
  Tento príkaz spustí:
  - Statickú analýzu kódu (PHPStan)
  - Kontrolu štýlu kódu (PHP_CodeSniffer)
  - Všetky testy s podrobným výpisom

  Výsledky budú zobrazené s farebnými ikonami (✓, ✗, ⚠) a prehľadným súhrnom.

- **Statická analýza kódu**:
  ```bash
  composer phpstan
  ```

- **Kontrola štýlu kódu**:
  ```bash
  composer cs
  ```

- **Automatická oprava štýlu kódu**:
  ```bash
  composer cs-fix
  ```

## Ukážka výstupu

Pri spustení príkazu `composer test:all` uvidíte podobný výstup:

```
╔════════════════════════════════════════════════════════════════╗
║                     SPUSTENIE TESTOV                           ║
╚════════════════════════════════════════════════════════════════╝

=== STATICKÁ ANALÝZA KÓDU (PHPSTAN) ===

 31/31 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%

✓ PHPStan: Kód prešiel statickou analýzou

=== KONTROLA ŠTÝLU KÓDU (PHP_CODESNIFFER) ===

✓ PHP_CodeSniffer: Kód spĺňa štandardy PSR-12

=== TESTY ===

Time: 00:00.032, Memory: 10.00 MB

Article (Tests\Unit\Domain\Article)
 ✔ Is valid type
 ✔ Get types
 ✔ Constants

Article Service (Tests\Unit\Application\Service\ArticleService)
 ✔ Get all articles
 ✔ Get article by id
 ✔ Get article by id throws exception when article not found
 ✔ Get all categories
 ✔ Get all tags

... (skrátené pre prehľadnosť) ...

Uuid (Tests\Unit\Domain\ValueObject\Uuid)
 ✔ Constructor with valid uuid
 ✔ Constructor with invalid uuid
 ✔ Generate
 ✔ To string
 ✔ Equals

OK (39 tests, 105 assertions)

✓ Testy: Všetky testy prešli

=== SÚHRN TESTOV ===

✓ Statická analýza kódu: OK
✓ Kontrola štýlu kódu: OK
✓ Testy: OK

╔════════════════════════════════════════════════════════════════╗
║                     VŠETKY TESTY PREŠLI                         ║
╚════════════════════════════════════════════════════════════════╝
```

## Pridávanie nových testov

### Unit testy

Unit testy by mali byť umiestnené v adresári `tests/Unit` a testovať jednotlivé komponenty aplikácie izolovane od ostatných komponentov.

Príklad unit testu:

```php
<?php

namespace Tests\Unit;

use App\Domain\Article;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    public function testCreateArticle(): void
    {
        $article = new Article('Test Title', 'Test Content');

        $this->assertEquals('Test Title', $article->getTitle());
        $this->assertEquals('Test Content', $article->getContent());
    }
}
```

### Integračné testy

Integračné testy by mali byť umiestnené v adresári `tests/Integration` a testovať interakciu medzi komponentami aplikácie.

Príklad integračného testu:

```php
<?php

namespace Tests\Integration;

use App\Domain\Article;
use App\Infrastructure\Persistence\DatabaseArticleRepository;
use PHPUnit\Framework\TestCase;

class DatabaseArticleRepositoryTest extends TestCase
{
    private DatabaseArticleRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new DatabaseArticleRepository(/* ... */);
    }

    public function testSaveAndFindArticle(): void
    {
        $article = new Article('Test Title', 'Test Content');

        $id = $this->repository->save($article);
        $foundArticle = $this->repository->findById($id);

        $this->assertEquals('Test Title', $foundArticle->getTitle());
        $this->assertEquals('Test Content', $foundArticle->getContent());
    }
}
```

## Konfigurácia PHPUnit

Konfigurácia PHPUnit je uložená v súbore `phpunit.xml` v koreňovom adresári projektu. Tu môžete upraviť nastavenia PHPUnit podľa potreby.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.5/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         cacheDirectory=".phpunit.cache">
  <testsuites>
    <testsuite name="Unit">
      <directory>tests/Unit</directory>
    </testsuite>
    <testsuite name="Integration">
      <directory>tests/Integration</directory>
    </testsuite>
  </testsuites>
  <php>
    <env name="APP_ENV" value="testing"/>
    <env name="CACHE_DRIVER" value="array"/>
    <env name="SESSION_DRIVER" value="array"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
  </php>
  <source>
    <include>
      <directory>src</directory>
    </include>
  </source>
</phpunit>
```
