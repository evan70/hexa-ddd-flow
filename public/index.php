<?php

declare(strict_types=1);

// Nastavenie správnych MIME typov pre statické súbory
$path = $_SERVER['REQUEST_URI'];
if (preg_match('/\.(js|css|jpg|jpeg|png|gif|svg|webp)$/i', $path, $matches)) {
    $extension = strtolower($matches[1]);
    $mimeTypes = [
        'js' => 'application/javascript',
        'css' => 'text/css',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp'
    ];

    if (isset($mimeTypes[$extension])) {
        $filePath = __DIR__ . $path;
        if (file_exists($filePath)) {
            header('Content-Type: ' . $mimeTypes[$extension]);
            readfile($filePath);
            exit;
        }
    }
}

use App\Infrastructure\Middleware\ErrorHandlerMiddleware;
use App\Infrastructure\Middleware\UuidValidatorMiddleware;
use App\Infrastructure\Middleware\CsrfMiddleware;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Inicializácia Container Builder
$containerBuilder = new ContainerBuilder();

// Nastavenie definícií
$settings = require __DIR__ . '/../config/settings.php';
$containerBuilder->addDefinitions(['settings' => $settings]);

// Pridanie závislostí
$dependencies = require __DIR__ . '/../config/dependencies.php';
$dependencies($containerBuilder);

// Vytvorenie kontajnera
$container = $containerBuilder->build();

// Vytvorenie aplikácie
AppFactory::setContainer($container);
$app = AppFactory::create();

// Registrácia middleware
$app->addBodyParsingMiddleware();

// Pridanie Twig middleware
$twig = $container->get(Twig::class);
$app->add(TwigMiddleware::createFromContainer($app, Twig::class));

// Pridanie UUID validator middleware
$app->add($container->get(UuidValidatorMiddleware::class));

// Pridanie CSRF middleware
$app->add($container->get(CsrfMiddleware::class));

// Pridanie vlastného error handler middleware
$app->add(new ErrorHandlerMiddleware($twig));

// Štandardný error middleware (bude použitý len ak náš vlastný middleware nepokryje chybu)
$app->addErrorMiddleware(
    $settings['displayErrorDetails'],
    $settings['logErrors'],
    $settings['logErrorDetails']
);

// Registrácia rout
$routes = require __DIR__ . '/../config/routes.php';
$routes($app);

// Pridanie 404 handlera pre neexistujúce routy
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    throw new \Slim\Exception\HttpNotFoundException($request);
});

// Spustenie aplikácie
$app->run();
