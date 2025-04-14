<?php

/**
 * Skript na výpis všetkých dostupných rout v aplikácii
 *
 * Použitie:
 * php bin/list-routes.php
 *
 * Autor: Augment Agent
 * Dátum: <?= date('Y-m-d') ?>
 */

// Načítanie autoloadera
require __DIR__ . '/../vendor/autoload.php';

// Vytvorenie aplikácie rovnakým spôsobom ako v boot/app.php
$app = require __DIR__ . '/../boot/app.php';

// Získanie rout
$routes = $app->getRouteCollector()->getRoutes();

// Farby pre výstup
$colors = [
    'GET' => "\033[32m", // Zelená
    'POST' => "\033[34m", // Modrá
    'PUT' => "\033[33m", // Žltá
    'DELETE' => "\033[31m", // Červená
    'PATCH' => "\033[35m", // Purpurová
    'OPTIONS' => "\033[36m", // Azúrová
    'reset' => "\033[0m" // Reset
];

// Výpis hlavičky
echo "╔════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                                                  ZOZNAM DOSTUPNÝCH ROUT                                                         ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════╝\n\n";

// Zoradenie rout podľa cesty
$routesByPath = [];
foreach ($routes as $route) {
    $path = $route->getPattern();
    if (!isset($routesByPath[$path])) {
        $routesByPath[$path] = [];
    }
    $routesByPath[$path][] = $route;
}
ksort($routesByPath);

// Výpis rout
echo str_pad("Metóda", 10) . " | " . str_pad("Cesta", 50) . " | " . str_pad("Názov", 20) . " | Handler\n";
echo str_repeat("-", 120) . "\n";

foreach ($routesByPath as $path => $pathRoutes) {
    foreach ($pathRoutes as $route) {
        $methods = $route->getMethods();

        foreach ($methods as $method) {
            $color = $colors[$method] ?? $colors['reset'];

            echo $color . str_pad($method, 10) . $colors['reset'] . " | ";
            echo str_pad($path, 50) . " | ";
            echo str_pad($route->getName() ?: '-', 20) . " | ";

            // Získanie handlera
            $callable = $route->getCallable();
            if (is_string($callable)) {
                echo $callable;
            } elseif (is_array($callable)) {
                if (is_object($callable[0])) {
                    echo get_class($callable[0]) . '::' . $callable[1];
                } else {
                    echo $callable[0] . '::' . $callable[1];
                }
            } elseif ($callable instanceof \Closure) {
                echo 'Closure';
            } else {
                echo 'Unknown';
            }

            echo "\n";
        }
    }
}

// Výpis zhrnutia
$totalRoutes = count($routes);
$totalPaths = count($routesByPath);

echo "\n";
echo "Celkový počet rout: $totalRoutes\n";
echo "Celkový počet unikátnych ciest: $totalPaths\n";

// Výpis rout podľa metódy
$routesByMethod = [];
foreach ($routes as $route) {
    $methods = $route->getMethods();
    foreach ($methods as $method) {
        if (!isset($routesByMethod[$method])) {
            $routesByMethod[$method] = 0;
        }
        $routesByMethod[$method]++;
    }
}

echo "\nPočet rout podľa metódy:\n";
foreach ($routesByMethod as $method => $count) {
    $color = $colors[$method] ?? $colors['reset'];
    echo $color . str_pad($method, 10) . $colors['reset'] . ": $count\n";
}

// Výpis middlewarov
echo "\nMiddlewares:\n";
$middlewares = [];
foreach ($routes as $route) {
    $routeMiddlewares = $route->getMiddleware();
    foreach ($routeMiddlewares as $middleware) {
        $name = $middleware->getCallable();
        if (is_string($name)) {
            if (!isset($middlewares[$name])) {
                $middlewares[$name] = 0;
            }
            $middlewares[$name]++;
        } elseif (is_array($name)) {
            $middlewareName = is_object($name[0]) ? get_class($name[0]) : $name[0];
            $middlewareName .= '::' . $name[1];
            if (!isset($middlewares[$middlewareName])) {
                $middlewares[$middlewareName] = 0;
            }
            $middlewares[$middlewareName]++;
        }
    }
}

ksort($middlewares);
foreach ($middlewares as $name => $count) {
    echo str_pad($name, 50) . ": $count rout\n";
}

// Výpis pätičky
echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                                                  KONIEC ZOZNAMU ROUT                                                            ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════╝\n";
