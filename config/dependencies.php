<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\DatabaseUserRepository;
use App\Infrastructure\Persistence\DatabaseArticleRepository;
use App\Infrastructure\Helper\ViteAssetHelper;
use App\Infrastructure\Middleware\UuidValidatorMiddleware;
use App\Infrastructure\Controller\UserController;
use App\Infrastructure\Controller\ArticleController;
use App\Infrastructure\Twig\UuidExtension;
use App\Ports\UserRepositoryInterface;
use App\Ports\ArticleRepositoryInterface;
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
                __DIR__ . '/../public/build/manifest.json',
                false, // Nastavené na false, aby sa vždy používal produkčný build
                'http://localhost:5173'
            );
        },
        
        // UUID Validator Middleware
        UuidValidatorMiddleware::class => function (ContainerInterface $c) {
            return new UuidValidatorMiddleware();
        },
        
        // Controllers
        UserController::class => function (ContainerInterface $c) {
            return new UserController(
                $c->get(UserRepositoryInterface::class),
                $c->get(Twig::class)
            );
        },
        
        ArticleController::class => function (ContainerInterface $c) {
            return new ArticleController(
                $c->get(ArticleRepositoryInterface::class),
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
            
            // Pridanie Twig extensions
            $twig->addExtension(new UuidExtension());
            
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
        }
    ]);
};
