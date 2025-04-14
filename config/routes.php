<?php

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Infrastructure\Controller\UserController;
use App\Infrastructure\Controller\ArticleController;
use App\Infrastructure\Middleware\UuidValidatorMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Slim\Exception\HttpNotFoundException;

return function (App $app) {
    // Domovská stránka
    $app->get('/', function (Request $request, Response $response) {
        $view = $this->get(Twig::class);
        return $view->render($response, 'home.twig');
    });

    // Test 404 stránky
    $app->get('/test-404', function (Request $request, Response $response) {
        throw new HttpNotFoundException($request, "Testovacia 404 chyba");
    });

    // API routy
    $app->group('/api', function (RouteCollectorProxy $group) {
        // Users API
        $group->get('/users', [UserController::class, 'index']);
        $group->get('/users/{id}', [UserController::class, 'show'])
            ->add(UuidValidatorMiddleware::class);

        // Articles API
        $group->get('/articles', [ArticleController::class, 'index']);
        $group->get('/articles/{id}', [ArticleController::class, 'show'])
            ->add(UuidValidatorMiddleware::class);
        $group->get('/articles/type/{type}', [ArticleController::class, 'showByType']);
        $group->post('/articles', [ArticleController::class, 'create'])
            ->add(UuidValidatorMiddleware::class);
        $group->put('/articles/{id}', [ArticleController::class, 'update'])
            ->add(UuidValidatorMiddleware::class);
        $group->delete('/articles/{id}', [ArticleController::class, 'delete'])
            ->add(UuidValidatorMiddleware::class);
    });

    // Web routy
    $app->group('/web', function (RouteCollectorProxy $group) {
        // Získanie všetkých článkov (JSON)
        $group->get('/articles', [ArticleController::class, 'index']);

        // Získanie článku podľa ID (JSON)
        $group->get('/articles/{id}', [ArticleController::class, 'show'])
            ->add(UuidValidatorMiddleware::class);

        // Získanie článkov podľa typu (JSON)
        $group->get('/articles/type/{type}', [ArticleController::class, 'showByType']);

        // HTML zobrazenie článkov
        $group->get('/view/articles', [ArticleController::class, 'viewList']);

        // HTML zobrazenie článku
        $group->get('/view/articles/{id}', [ArticleController::class, 'viewDetail'])
            ->add(UuidValidatorMiddleware::class);

        // HTML zobrazenie článkov podľa typu
        $group->get('/view/{type}', [ArticleController::class, 'viewByType']);

        // HTML zobrazenie článkov podľa kategórie
        $group->get('/view/category/{category}', [ArticleController::class, 'viewByCategory']);

        // HTML zobrazenie článkov podľa tagu
        $group->get('/view/tag/{tag}', [ArticleController::class, 'viewByTag']);
    });

    // Mark routy
    $app->group('/mark', function (RouteCollectorProxy $group) {
        // Získanie všetkých používateľov (JSON)
        $group->get('/users', [UserController::class, 'index']);

        // Získanie používateľa podľa ID (JSON)
        $group->get('/users/{id}', [UserController::class, 'show'])
            ->add(UuidValidatorMiddleware::class);

        // Vytvorenie nového článku
        $group->post('/articles', [ArticleController::class, 'create'])
            ->add(UuidValidatorMiddleware::class);

        // Aktualizácia článku
        $group->put('/articles/{id}', [ArticleController::class, 'update'])
            ->add(UuidValidatorMiddleware::class);

        // Vymazanie článku
        $group->delete('/articles/{id}', [ArticleController::class, 'delete'])
            ->add(UuidValidatorMiddleware::class);

        // HTML zobrazenie používateľov
        $group->get('/view/users', [UserController::class, 'viewList']);
    });
};
