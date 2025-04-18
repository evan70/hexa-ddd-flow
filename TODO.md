# TODO: Ďalšie kroky pre rozvoj aplikácie

## 1. Dokončenie implementácie služieb
- [x] Dokončiť `ArticleService`:
  - [x] Pridať metódy pre vytváranie článkov (`createArticle`)
  - [x] Pridať metódy pre aktualizáciu článkov (`updateArticle`)
  - [x] Pridať metódy pre mazanie článkov (`deleteArticle`)
  - [x] Implementovať validáciu vstupných dát

- [x] Dokončiť `UserService`:
  - [x] Pridať metódy pre vytváranie používateľov (`createUser`)
  - [x] Pridať metódy pre aktualizáciu používateľov (`updateUser`)
  - [x] Pridať metódy pre mazanie používateľov (`deleteUser`)
  - [x] Implementovať validáciu vstupných dát

## 2. Implementácia autentifikácie a autorizácie
- [x] Vytvoriť `AuthService` pre správu autentifikácie
- [x] Implementovať prihlasovanie a odhlasovanie používateľov
- [x] Vytvoriť middleware pre kontrolu autentifikácie
- [x] Implementovať autorizáciu na základe rolí používateľov
- [x] Pridať ochranu proti CSRF útokom
- [x] Vytvoriť stránky pre prihlásenie a registráciu

## 3. Zlepšenie doménovej vrstvy
- [ ] Nahradiť triedy s konštantami skutočnými entitami
- [ ] Implementovať Value Objects pre všetky doménové koncepty
- [ ] Pridať doménové udalosti (Domain Events)
- [ ] Vytvoriť agregáty pre komplexné doménové operácie

## 4. Rozšírenie testovania
- [x] Nastaviť PHPUnit pre testovanie
- [x] Implementovať unit testy pre doménové triedy
- [x] Implementovať unit testy pre služby
- [x] Vytvoriť integračné testy pre repozitáre
- [ ] Implementovať end-to-end testy pre API

## 5. Vylepšenie používateľského rozhrania
- [ ] Implementovať dynamické filtrovanie článkov na strane klienta
- [ ] Pridať vyhľadávanie v článkoch
- [ ] Vylepšiť responzívny dizajn
- [ ] Implementovať lazy loading pre obrázky a obsah
- [ ] Pridať pagination pre zoznamy článkov a používateľov

## 6. Optimalizácia výkonu
- [ ] Implementovať cachovanie na úrovni služieb
- [ ] Optimalizovať SQL dotazy v repozitároch
- [ ] Pridať indexy do databázových tabuliek
- [ ] Implementovať lazy loading pre vzťahy medzi entitami

## 7. Dokumentácia a monitoring
- [ ] Vytvoriť API dokumentáciu (Swagger/OpenAPI)
- [ ] Implementovať logovanie
- [ ] Pridať health checks pre API
- [ ] Rozšíriť dokumentáciu o návody pre vývojárov

## 8. Refaktoring šablón
- [ ] Vytvoriť znovupoužiteľné komponenty (partial templates)
- [ ] Implementovať dedičnosť šablón pre rôzne typy stránok
- [ ] Oddeliť logiku od prezentácie pomocou view modelov

## 9. Migrácia na lepšiu databázu
- [ ] Zvážiť migráciu na MySQL alebo PostgreSQL
- [ ] Implementovať migračný systém
- [ ] Pridať indexy pre optimalizáciu vyhľadávania

## 10. Kontinuálna integrácia a nasadenie
- [ ] Nastaviť CI/CD pipeline
- [ ] Automatizovať testy a statickú analýzu kódu
- [ ] Implementovať automatické nasadenie

## Prioritné úlohy pre najbližšie obdobie
1. ~~Dokončiť implementáciu služieb~~ ✅
2. ~~Implementovať autentifikáciu a autorizáciu~~ ✅
3. ~~Rozšíriť testovanie~~ ✅
4. Zlepšiť doménovú vrstvu
5. Vylepšiť používateľské rozhranie
