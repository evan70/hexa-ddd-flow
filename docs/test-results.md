# Výsledky testov

Tento dokument obsahuje výsledky testov, ktoré boli spustené v projekte.

## Výsledky testov z 2024-06-05

```
Runtime:       PHP 8.4.5
Configuration: /home/evan/Desktop/slim4/14/phpunit.xml

.......................................                           39 / 39 (100%)

Time: 00:00.033, Memory: 10.00 MB

There was 1 PHPUnit test runner warning:

1) XDEBUG_MODE=coverage (environment variable) or xdebug.mode=coverage (PHP configuration setting) has to be set

WARNINGS!
Tests: 39, Assertions: 105, Warnings: 1.
```

### Zhrnutie

- **Počet testov**: 39
- **Počet asercií**: 105
- **Výsledok**: Všetky testy prešli
- **Čas**: 0.033 sekundy
- **Pamäť**: 10.00 MB

### Poznámky

- Varovanie o chýbajúcom nastavení `XDEBUG_MODE=coverage` sa týka len generovania coverage reportu a nemá vplyv na výsledky testov.
- Testy bežia veľmi rýchlo (len 0.033 sekundy), čo je výborné.
- Všetky testy prešli, čo znamená, že aplikácia funguje správne.

## Ako spustiť testy

Pre spustenie testov s prehľadným výpisom použite:

```bash
composer test:verbose
```

Pre spustenie testov s kontrolou kódu a prehľadným výpisom použite:

```bash
composer test:all
```

Pre viac informácií o testoch pozrite [dokumentáciu testov](testing.md).
