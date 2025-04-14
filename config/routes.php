<?php

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Infrastructure\Controller\UserController;
use App\Infrastructure\Controller\ArticleController;
use App\Infrastructure\Controller\AuthController;
use App\Infrastructure\Controller\MarkController;
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

    // Autentifikácia
    $app->get('/login', [AuthController::class, 'loginPage']);
    $app->post('/login', [AuthController::class, 'login']);
    $app->get('/logout', [AuthController::class, 'logout']);

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

    // Mark CMS routy
    $app->group('/mark', function (RouteCollectorProxy $group) {
        // Dashboard
        $group->get('', [MarkController::class, 'dashboard']);

        // Používatelia
        $group->get('/users', [MarkController::class, 'users']);
        $group->get('/users/create', [MarkController::class, 'createUserForm']);
        $group->get('/users/{id}/edit', [MarkController::class, 'editUserForm'])
            ->add(UuidValidatorMiddleware::class);
        $group->get('/users/{id}', [MarkController::class, 'userDetail'])
            ->add(UuidValidatorMiddleware::class);

        // Články
        $group->get('/articles', [MarkController::class, 'articles']);
        $group->get('/articles/create', [MarkController::class, 'createArticleForm']);
        $group->get('/articles/{id}/edit', [MarkController::class, 'editArticleForm'])
            ->add(UuidValidatorMiddleware::class);
        $group->get('/articles/{id}', [MarkController::class, 'articleDetail'])
            ->add(UuidValidatorMiddleware::class);

        // Nastavenia
        $group->get('/settings', [MarkController::class, 'settings']);

        // API pre používateľov
        $group->post('/api/users', [UserController::class, 'create']);
        $group->put('/api/users/{id}', [UserController::class, 'update'])
            ->add(UuidValidatorMiddleware::class);
        $group->delete('/api/users/{id}', [UserController::class, 'delete'])
            ->add(UuidValidatorMiddleware::class);

        // API pre články
        $group->post('/api/articles', [ArticleController::class, 'create']);
        $group->put('/api/articles/{id}', [ArticleController::class, 'update'])
            ->add(UuidValidatorMiddleware::class);
        $group->delete('/api/articles/{id}', [ArticleController::class, 'delete'])
            ->add(UuidValidatorMiddleware::class);
    });
};
