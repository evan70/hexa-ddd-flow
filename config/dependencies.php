<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\DatabaseUserRepository;
use App\Infrastructure\Persistence\DatabaseArticleRepository;
use App\Infrastructure\Helper\ViteAssetHelper;
use App\Infrastructure\Middleware\UuidValidatorMiddleware;
use App\Infrastructure\Controller\UserController;
use App\Infrastructure\Controller\ArticleController;
use App\Infrastructure\Controller\AuthController;
use App\Infrastructure\Controller\MarkController;
use App\Infrastructure\Controller\AbstractController;
use App\Infrastructure\Twig\UuidExtension;
use App\Infrastructure\Middleware\AuthMiddleware;
// Odstránené: use App\Infrastructure\Middleware\CsrfMiddleware;
use App\Infrastructure\Middleware\SlimCsrfMiddleware;
use App\Infrastructure\Persistence\DatabaseSessionRepository;
use App\Infrastructure\Persistence\DatabaseSettingsRepository;
use App\Twig\ViteExtension;
use App\Ports\UserRepositoryInterface;
use App\Ports\ArticleRepositoryInterface;
use App\Ports\SessionRepositoryInterface;
use App\Ports\SettingsRepositoryInterface;
use App\Application\Service\ArticleService;
use App\Application\Service\UserService;
use App\Application\Service\AuthService;
// Odstránené: use App\Application\Service\CsrfService;
use App\Application\Service\SettingsService;
use Slim\Csrf\Guard;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use Slim\Exception\HttpNotFoundException;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // Vite Asset Helper
        ViteAssetHelper::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            return new ViteAssetHelper(
                __DIR__ . '/../public/build/.vite/manifest.json',
                false, // Nastavené na false, aby sa vždy používal produkčný build
                'http://localhost:5173'
            );
        },

        // UUID Validator Middleware
        UuidValidatorMiddleware::class => function (ContainerInterface $c) {
            return new UuidValidatorMiddleware();
        },

        // Controllers

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

        // Twig konfigurácia
        Twig::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $twig = Twig::create(__DIR__ . '/../resources/views', [
                'cache' => $settings['displayErrorDetails'] ? false : __DIR__ . '/../var/cache/twig',
                'auto_reload' => $settings['displayErrorDetails'],
                'debug' => $settings['displayErrorDetails'],
            ]);

            // Pridanie ViteAssetHelper do Twig
            $twig->getEnvironment()->addGlobal('vite', $c->get(ViteAssetHelper::class));

            // Pridanie AuthService do Twig
            $twig->getEnvironment()->addGlobal('auth', $c->get(AuthService::class));

            // Pridanie Twig extensions
            $twig->addExtension(new UuidExtension());
            $twig->addExtension(new ViteExtension());

            return $twig;
        },

        PDO::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');

            // Vytvorenie adresára pre databázy, ak neexistuje
            $dataDir = dirname($settings['database']['users']['path']);
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0777, true);
            }

            // Vytvorenie PDO inštancie pre users databázu
            $usersPdo = new PDO('sqlite:' . $settings['database']['users']['path']);
            $usersPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $usersPdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Inicializácia users databázy, ak je prázdna
            $usersPdo->exec('
                CREATE TABLE IF NOT EXISTS users (
                    id CHAR(36) PRIMARY KEY,
                    username TEXT NOT NULL,
                    email TEXT NOT NULL UNIQUE,
                    password TEXT,
                    role TEXT NOT NULL,
                    created_at DATETIME,
                    updated_at DATETIME
                )
            ');

            return $usersPdo;
        },

        'articles_pdo' => function (ContainerInterface $c) {
            $settings = $c->get('settings');

            // Vytvorenie PDO inštancie pre articles databázu
            $articlesPdo = new PDO('sqlite:' . $settings['database']['articles']['path']);
            $articlesPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $articlesPdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Inicializácia articles databázy, ak je prázdna
            $articlesPdo->exec('
                CREATE TABLE IF NOT EXISTS articles (
                    id CHAR(36) PRIMARY KEY,
                    title TEXT NOT NULL,
                    content TEXT NOT NULL,
                    type TEXT NOT NULL,
                    author_id CHAR(36) NOT NULL,
                    created_at DATETIME,
                    updated_at DATETIME,
                    FOREIGN KEY (author_id) REFERENCES users(id)
                )
            ');

            return $articlesPdo;
        },

        UserRepositoryInterface::class => function (ContainerInterface $c) {
            return new DatabaseUserRepository($c->get(PDO::class));
        },

        ArticleRepositoryInterface::class => function (ContainerInterface $c) {
            return new DatabaseArticleRepository($c->get('articles_pdo'));
        },

        'app_pdo' => function (ContainerInterface $c) {
            $settings = $c->get('settings');

            // Vytvorenie adresára pre databázy, ak neexistuje
            $dataDir = dirname($settings['database']['app']['path']);
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0777, true);
            }

            // Vytvorenie PDO inštancie pre app databázu
            $appPdo = new PDO('sqlite:' . $settings['database']['app']['path']);
            $appPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $appPdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Inicializácia app databázy, ak je prázdna
            $appPdo->exec('
                CREATE TABLE IF NOT EXISTS sessions (
                    id TEXT PRIMARY KEY,
                    user_id TEXT,
                    data TEXT,
                    created_at TEXT,
                    expires_at TEXT
                )
            ');

            $appPdo->exec('
                CREATE TABLE IF NOT EXISTS settings (
                    key TEXT PRIMARY KEY,
                    value TEXT,
                    updated_at TEXT
                )
            ');

            // Vytvorenie indexov
            $appPdo->exec('CREATE INDEX IF NOT EXISTS idx_sessions_user_id ON sessions(user_id)');
            $appPdo->exec('CREATE INDEX IF NOT EXISTS idx_sessions_expires_at ON sessions(expires_at)');

            return $appPdo;
        },

        SessionRepositoryInterface::class => function (ContainerInterface $c) {
            return new DatabaseSessionRepository($c->get('app_pdo'));
        },

        SettingsRepositoryInterface::class => function (ContainerInterface $c) {
            return new DatabaseSettingsRepository($c->get('app_pdo'));
        },

        SettingsService::class => function (ContainerInterface $c) {
            return new SettingsService($c->get(SettingsRepositoryInterface::class));
        },

        AuthService::class => function (ContainerInterface $c) {
            return new AuthService(
                $c->get(UserRepositoryInterface::class),
                $c->get(SessionRepositoryInterface::class),
                'session_id',
                86400 // 24 hodín
            );
        },

        // Odstránené: CsrfService

        AuthController::class => function (ContainerInterface $c) {
            return new AuthController(
                $c->get(AuthService::class),
                $c->get(Twig::class)
            );
        },

        MarkController::class => function (ContainerInterface $c) {
            return new MarkController(
                $c->get(ArticleService::class),
                $c->get(UserService::class),
                $c->get(AuthService::class),
                $c->get(SettingsService::class),
                $c->get(Twig::class)
            );
        },

        AuthMiddleware::class => function (ContainerInterface $c) {
            return new AuthMiddleware(
                $c->get(AuthService::class),
                $c->get(Twig::class),
                ['admin'], // Len používatelia s rolou admin majú prístup k MarkCMS
                '/login'
            );
        },

        // Odstránené: CsrfMiddleware

        Guard::class => function (ContainerInterface $c) {
            // Spustenie session, ak ešte nie je spustená
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
            $guard = new Guard($responseFactory);

            // Nastavenie storage handler pre persistenciu tokenov
            $guard->setPersistentTokenMode(true);

            // Nastavenie error handlera
            $guard->setFailureHandler(function (\Psr\Http\Message\ServerRequestInterface $request, $handler) {
                // Vytvorenie chybovej odpovede
                $response = new \Slim\Psr7\Response();
                $response->getBody()->write(json_encode([
                    'error' => 'Neplatný CSRF token.'
                ]));

                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(403);
            });

            return $guard;
        },

        SlimCsrfMiddleware::class => function (ContainerInterface $c) {
            return new SlimCsrfMiddleware(
                $c->get(Guard::class),
                $c->get(Twig::class),
                ['/api', '/login'] // Cesty vylúčené z CSRF ochrany
            );
        },



    ]);
};
