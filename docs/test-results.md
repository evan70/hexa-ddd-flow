# Výsledky testov

Tento dokument obsahuje výsledky testov, ktoré boli spustené v projekte.

## Výsledky testov z 2024-06-05

```
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

Auth Service (Tests\Unit\Application\Service\AuthService)
 ✔ Get current user returns null when no cookie
 ✔ Get current user returns null when session not found
 ✔ Get current user returns user when session exists
 ✔ Is logged in returns true when user is logged in
 ✔ Is logged in returns false when user is not logged in
 ✔ Has role returns true when user has role
 ✔ Has role returns false when user does not have role

Database User Repository (Tests\Integration\Infrastructure\Persistence\DatabaseUserRepository)
 ✔ Find all
 ✔ Find by id
 ✔ Find by id returns null when user not found
 ✔ Find by id accepts uuid object
 ✔ Find by role
 ✔ Save creates new user
 ✔ Save updates existing user
 ✔ Delete
 ✔ Delete returns false when user not found
 ✔ Delete accepts uuid object

User (Tests\Unit\Domain\User)
 ✔ Is valid
 ✔ Get all
 ✔ Constants

User Service (Tests\Unit\Application\Service\UserService)
 ✔ Get all users
 ✔ Get user by id
 ✔ Get user by id throws exception when user not found
 ✔ Get users by role
 ✔ Create user
 ✔ Create user throws exception with invalid data

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
