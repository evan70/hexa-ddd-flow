# Proces mergovania a vytvorenia release

Tento dokument popisuje proces mergovania vetvy `develop` do hlavnej vetvy (`master`) a vytvorenia nového release.

## 1. Príprava na release

Pred vytvorením release je potrebné:

1. **Skontrolovať, či všetky zmeny sú commitnuté a pushnuté**:
   ```bash
   git status
   ```

2. **Spustiť testy a statickú analýzu kódu**:
   ```bash
   composer test
   composer phpstan
   ```

3. **Aktualizovať dokumentáciu** (ak je to potrebné)

## 2. Mergovanie vetvy `develop` do `master`

1. **Prejsť na vetvu `master` a stiahnuť najnovšie zmeny**:
   ```bash
   git checkout master
   git pull origin master
   ```

2. **Mergovať vetvu `develop` do `master`**:
   ```bash
   git merge develop --no-ff -m "Merge branch 'develop' do master - Release vX.Y.Z"
   ```
   
   Parameter `--no-ff` zabezpečí, že sa vytvorí merge commit, aj keď by bolo možné použiť fast-forward.

3. **Vyriešiť prípadné konflikty**:
   Ak sa vyskytnú konflikty, je potrebné ich vyriešiť a potom dokončiť merge:
   ```bash
   git add .
   git commit
   ```

## 3. Vytvorenie tagu pre release

1. **Vytvoriť anotovaný tag**:
   ```bash
   git tag -a vX.Y.Z -m "Release vX.Y.Z"
   ```
   
   Kde `X.Y.Z` je verzia podľa [Semantic Versioning](https://semver.org/):
   - `X` - major verzia (zmeny, ktoré nie sú spätne kompatibilné)
   - `Y` - minor verzia (nové funkcie, ktoré sú spätne kompatibilné)
   - `Z` - patch verzia (opravy chýb, ktoré sú spätne kompatibilné)

2. **Pushnúť zmeny a tag na GitHub**:
   ```bash
   git push origin master
   git push origin vX.Y.Z
   ```

## 4. Vytvorenie release na GitHub

1. **Vytvoriť súbor s poznámkami k vydaniu** (release notes):
   ```bash
   touch release-notes-vX.Y.Z.md
   ```
   
   Do tohto súboru napísať podrobný popis všetkých zmien v release.

2. **Vytvoriť release na GitHub**:
   - Otvoriť https://github.com/evan70/hexa-ddd-flow/releases/new
   - Vybrať tag `vX.Y.Z`
   - Zadať názov release: `Release vX.Y.Z`
   - Skopírovať obsah súboru `release-notes-vX.Y.Z.md` do poľa pre popis
   - Kliknúť na tlačidlo `Publish release`

## 5. Pokračovanie vo vývoji

1. **Vrátiť sa na vetvu `develop`**:
   ```bash
   git checkout develop
   ```

2. **Začať pracovať na ďalších funkciách** pre budúci release.

## Príklad: Vytvorenie release v1.1.0

```bash
# 1. Príprava na release
git checkout develop
git pull origin develop
composer phpstan

# 2. Mergovanie vetvy develop do master
git checkout master
git pull origin master
git merge develop --no-ff -m "Merge branch 'develop' do master - Release v1.1.0"

# 3. Vytvorenie tagu pre release
git tag -a v1.1.0 -m "Release v1.1.0"

# 4. Pushnúť zmeny a tag na GitHub
git push origin master
git push origin v1.1.0

# 5. Vytvorenie release na GitHub
# (použiť webové rozhranie GitHub)

# 6. Pokračovanie vo vývoji
git checkout develop
```

## Konvencie pre commit správy

Pre lepšiu čitateľnosť histórie commitov odporúčame používať nasledujúce konvencie pre commit správy:

- `feat:` - nová funkcia
- `fix:` - oprava chyby
- `docs:` - zmeny v dokumentácii
- `style:` - zmeny, ktoré nemenia funkcionalitu (formátovanie, medzery, atď.)
- `refactor:` - refaktorovanie kódu
- `test:` - pridanie alebo úprava testov
- `chore:` - zmeny v build procese alebo pomocných nástrojoch

Príklad:
```
feat: pridaná podpora pre slug v článkoch
```

## Konvencie pre tagy

Tagy by mali byť vytvorené podľa [Semantic Versioning](https://semver.org/):

- `vX.Y.Z` - stabilný release
- `vX.Y.Z-alpha.N` - alpha release
- `vX.Y.Z-beta.N` - beta release
- `vX.Y.Z-rc.N` - release candidate

Príklad:
```
v1.0.0
v1.1.0-beta.1
```
