<?php

declare(strict_types=1);

use App\Infrastructure\Middleware\ErrorHandlerMiddleware;
use App\Infrastructure\Middleware\UuidValidatorMiddleware;
use App\Infrastructure\Middleware\CsrfMiddleware;
use App\Infrastructure\Middleware\SessionMiddleware;
use App\Infrastructure\Middleware\SlimCsrfMiddleware;
use Slim\Middleware\Session as SlimSessionMiddleware;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

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

// Pridanie Slim Session middleware
$app->add(new SlimSessionMiddleware([
    'name' => 'slim_session',
    'autorefresh' => true,
    'lifetime' => '1 hour'
]));

// Pridanie CSRF middleware
$app->add($container->get(SlimCsrfMiddleware::class));

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

// Vrátime aplikáciu, aby ju mohol spustiť index.php
return $app;
