<?php

/**
 * Jednoduchý skript na výpis všetkých dostupných rout v aplikácii
 * Vhodný pre shared hosting (bez farebného výstupu)
 *
 * Použitie:
 * php bin/list-routes-simple.php
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

// Výpis hlavičky
echo "=================================================================\n";
echo "                    ZOZNAM DOSTUPNÝCH ROUT                       \n";
echo "=================================================================\n\n";

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
            echo str_pad($method, 10) . " | ";
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
    echo str_pad($method, 10) . ": $count\n";
}

// Výpis pätičky
echo "\n";
echo "=================================================================\n";
echo "                    KONIEC ZOZNAMU ROUT                          \n";
echo "=================================================================\n";
