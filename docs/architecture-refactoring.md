# Refaktoring architektúry aplikácie

Tento dokument popisuje refaktoring architektúry aplikácie, ktorý bol vykonaný s cieľom zlepšiť organizáciu kódu, odstrániť duplicitu a lepšie dodržiavať princípy hexagonálnej architektúry a Domain-Driven Design (DDD).

## Prehľad zmien

Hlavné zmeny zahŕňajú:

1. Vytvorenie aplikačnej vrstvy (Application Layer)
2. Zavedenie abstraktnej základnej triedy pre kontroléry
3. Implementácia služieb (Services) pre doménovú logiku
4. Odstránenie duplicitného kódu v kontroléroch

## Nová štruktúra projektu

```
📂 src
├── 📂 Domain          <- Doménové triedy (bez zmeny)
│   ├── User.php
│   ├── Article.php
│   ├── ...
│
├── 📂 Application     <- NOVÁ VRSTVA: Aplikačná logika
│   └── 📂 Service
│       ├── ArticleService.php    (Služba pre prácu s článkami)
│       └── UserService.php       (Služba pre prácu s používateľmi)
│
├── 📂 Ports           <- Rozhrania pre služby (bez zmeny)
│   ├── UserRepositoryInterface.php
│   └── ArticleRepositoryInterface.php
│
└── 📂 Infrastructure  <- Implementácie portov
    ├── 📂 Persistence (bez zmeny)
    │   ├── DatabaseUserRepository.php
    │   └── DatabaseArticleRepository.php
    ├── 📂 Controller
    │   ├── AbstractController.php  (NOVÉ: Základná trieda pre kontroléry)
    │   ├── UserController.php      (Refaktorovaný)
    │   └── ArticleController.php   (Refaktorovaný)
    ├── ...
```

## Detaily implementácie

### 1. Aplikačná vrstva (Application Layer)

Aplikačná vrstva bola pridaná ako prostredník medzi doménovými entitami a infraštruktúrou. Táto vrstva obsahuje služby, ktoré zapuzdrujú biznis logiku aplikácie.

#### ArticleService

Služba `ArticleService` poskytuje metódy pre prácu s článkami:

```php
class ArticleService
{
    private ArticleRepositoryInterface $articleRepository;

    public function __construct(ArticleRepositoryInterface $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    // Metódy pre získanie článkov
    public function getAllArticles(): array { ... }
    public function getArticleById(string $id, Request $request): array { ... }
    public function getArticlesByType(string $type, Request $request): array { ... }
    public function getArticlesByCategory(string $category): array { ... }
    public function getArticlesByTag(string $tag): array { ... }
    
    // Metódy pre získanie metadát
    public function getAllCategories(): array { ... }
    public function getAllTags(): array { ... }
    
    // Pomocné metódy
    private function decodeCategories(array $articles): array { ... }
}
```

#### UserService

Služba `UserService` poskytuje metódy pre prácu s používateľmi:

```php
class UserService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    // Metódy pre získanie používateľov
    public function getAllUsers(): array { ... }
    public function getUserById(string $id, Request $request): array { ... }
    public function getUsersByRole(string $role): array { ... }
}
```

### 2. Abstraktná základná trieda pre kontroléry

Vytvorili sme abstraktnú triedu `AbstractController`, ktorá obsahuje spoločnú funkcionalitu pre všetky kontroléry:

```php
abstract class AbstractController
{
    protected Twig $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    // Pomocná metóda pre dekódovanie kategórií
    protected function decodeCategories(array $articles): array { ... }

    // Pomocná metóda pre renderovanie šablón
    protected function render(Response $response, string $template, array $data = []): Response { ... }
}
```

### 3. Refaktorované kontroléry

Kontroléry boli refaktorované, aby dedili od `AbstractController` a používali služby namiesto priameho prístupu k repozitárom:

#### ArticleController

```php
class ArticleController extends AbstractController
{
    private ArticleService $articleService;

    public function __construct(ArticleService $articleService, Twig $twig)
    {
        parent::__construct($twig);
        $this->articleService = $articleService;
    }

    // Metódy kontroléra používajú službu namiesto repozitára
    public function index(Request $request, Response $response): Response { ... }
    public function show(Request $request, Response $response, array $args): Response { ... }
    // ...
}
```

#### UserController

```php
class UserController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService, Twig $twig)
    {
        parent::__construct($twig);
        $this->userService = $userService;
    }

    // Metódy kontroléra používajú službu namiesto repozitára
    public function index(Request $request, Response $response): Response { ... }
    public function show(Request $request, Response $response, array $args): Response { ... }
    // ...
}
```

### 4. Aktualizácia konfigurácie závislostí

Konfigurácia závislostí bola aktualizovaná, aby zaregistrovala nové služby a upravila závislosti kontrolérov:

```php
// User Service
UserService::class => function (ContainerInterface $c) {
    return new UserService(
        $c->get(UserRepositoryInterface::class)
    );
},

UserController::class => function (ContainerInterface $c) {
    return new UserController(
        $c->get(UserService::class),
        $c->get(Twig::class)
    );
},

// Article Service
ArticleService::class => function (ContainerInterface $c) {
    return new ArticleService(
        $c->get(ArticleRepositoryInterface::class)
    );
},

ArticleController::class => function (ContainerInterface $c) {
    return new ArticleController(
        $c->get(ArticleService::class),
        $c->get(Twig::class)
    );
},
```

## Výhody refaktoringu

1. **Odstránenie duplicity kódu**
   - Spoločná funkcionalita je teraz v abstraktnej triede a službách
   - Kód pre dekódovanie kategórií je na jednom mieste
   - Renderovanie šablón je zjednotené

2. **Lepšia organizácia kódu podľa princípov DDD**
   - Aplikačná vrstva jasne oddeľuje doménovú logiku od infraštruktúry
   - Služby zapuzdrujú biznis pravidlá a operácie

3. **Jednoduchšie testovanie**
   - Služby je možné testovať nezávisle od kontrolérov
   - Kontroléry majú minimálnu logiku a sú ľahko testovateľné

4. **Lepšia škálovateľnosť**
   - Nové funkcie je možné pridávať do služieb bez zmeny kontrolérov
   - Nové kontroléry môžu jednoducho dediť od abstraktnej triedy

5. **Zachovanie hexagonálnej architektúry**
   - Jasné oddelenie domény, aplikácie a infraštruktúry
   - Porty a adaptéry sú stále jasne definované

## Príklad použitia

Pred refaktoringom:

```php
// V kontroléri
$articles = $this->articleRepository->findAll();

// Dekódovanie kategórií pre každý článok
foreach ($articles as &$article) {
    if (isset($article['categories'])) {
        $article['categories'] = json_decode($article['categories'], true) ?: [];
    } else {
        $article['categories'] = [];
    }
}

return $this->twig->render($response, 'articles/list.twig', [
    'articles' => $articles,
    'title' => 'Zoznam článkov',
    'type' => null,
    'categories' => $this->articleRepository->getAllCategories(),
    'tags' => $this->articleRepository->getAllTags()
]);
```

Po refaktoringu:

```php
// V kontroléri
$articles = $this->articleService->getAllArticles();

return $this->render($response, 'articles/list.twig', [
    'articles' => $articles,
    'title' => 'Zoznam článkov',
    'type' => null,
    'categories' => $this->articleService->getAllCategories(),
    'tags' => $this->articleService->getAllTags()
]);
```

## Záver

Tento refaktoring výrazne zlepšil kvalitu kódu a uľahčí budúce rozširovanie aplikácie. Aplikácia teraz lepšie dodržiava princípy hexagonálnej architektúry a Domain-Driven Design, čo vedie k lepšej udržateľnosti a rozšíriteľnosti kódu.
