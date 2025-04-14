<?php

/**
 * Webová verzia skriptu na výpis všetkých dostupných rout v aplikácii
 * Prístupná cez URL: /debug/routes.php
 *
 * UPOZORNENIE: Tento súbor by mal byť dostupný len počas vývoja a debugovania.
 * Pred nasadením na produkciu ho odstráňte alebo zabezpečte prístup heslom.
 *
 * Autor: Augment Agent
 * Dátum: <?= date('Y-m-d') ?>
 */

// Kontrola, či je skript spustený v produkčnom prostredí
$isProduction = false;
if (file_exists(__DIR__ . '/../../config/settings.php')) {
    $settings = require __DIR__ . '/../../config/settings.php';
    $isProduction = isset($settings['displayErrorDetails']) && $settings['displayErrorDetails'] === false;
}

// Ak je skript spustený v produkčnom prostredí, vyžadujeme heslo
if ($isProduction) {
    $debugPassword = $_ENV['DEBUG_PASSWORD'] ?? 'debug123'; // Predvolené heslo, zmeňte ho!

    if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_PW'] !== $debugPassword) {
        header('WWW-Authenticate: Basic realm="Debug Area"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Prístup zamietnutý';
        exit;
    }
}

// Načítanie autoloadera
require __DIR__ . '/../../vendor/autoload.php';

// Vytvorenie aplikácie rovnakým spôsobom ako v boot/app.php
$app = require __DIR__ . '/../../boot/app.php';

// Získanie rout
$routes = $app->getRouteCollector()->getRoutes();

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

// Výpis HTML hlavičky
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zoznam dostupných rout</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .method {
            font-weight: bold;
            border-radius: 4px;
            padding: 3px 6px;
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }
        .get {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .post {
            background-color: #d9edf7;
            color: #31708f;
        }
        .put {
            background-color: #fcf8e3;
            color: #8a6d3b;
        }
        .delete {
            background-color: #f2dede;
            color: #a94442;
        }
        .patch {
            background-color: #e8eaf6;
            color: #3f51b5;
        }
        .options {
            background-color: #f3e5f5;
            color: #9c27b0;
        }
        .summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .summary h2 {
            margin-top: 0;
            color: #2c3e50;
        }
        .summary ul {
            list-style-type: none;
            padding: 0;
        }
        .summary li {
            margin-bottom: 5px;
        }
        .search {
            margin-bottom: 20px;
        }
        .search input {
            padding: 8px;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        @media (max-width: 768px) {
            table {
                font-size: 14px;
            }
            th, td {
                padding: 6px 8px;
            }
            .method {
                min-width: 50px;
                padding: 2px 4px;
            }
        }
    </style>
</head>
<body>
    <h1>Zoznam dostupných rout</h1>

    <div class="search">
        <input type="text" id="searchInput" placeholder="Vyhľadávanie rout..." onkeyup="filterRoutes()">
    </div>

    <table id="routesTable">
        <thead>
            <tr>
                <th>Metóda</th>
                <th>Cesta</th>
                <th>Názov</th>
                <th>Handler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($routesByPath as $path => $pathRoutes): ?>
                <?php foreach ($pathRoutes as $route): ?>
                    <?php $methods = $route->getMethods(); ?>
                    <?php foreach ($methods as $method): ?>
                        <tr>
                            <td>
                                <span class="method <?= strtolower($method) ?>"><?= $method ?></span>
                            </td>
                            <td><?= htmlspecialchars($path) ?></td>
                            <td><?= htmlspecialchars($route->getName() ?: '-') ?></td>
                            <td>
                                <?php
                                $callable = $route->getCallable();
                                if (is_string($callable)) {
                                    echo htmlspecialchars($callable);
                                } elseif (is_array($callable)) {
                                    if (is_object($callable[0])) {
                                        echo htmlspecialchars(get_class($callable[0]) . '::' . $callable[1]);
                                    } else {
                                        echo htmlspecialchars($callable[0] . '::' . $callable[1]);
                                    }
                                } elseif ($callable instanceof \Closure) {
                                    echo 'Closure';
                                } else {
                                    echo 'Unknown';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="summary">
        <h2>Súhrn</h2>

        <p><strong>Celkový počet rout:</strong> <?= count($routes) ?></p>
        <p><strong>Celkový počet unikátnych ciest:</strong> <?= count($routesByPath) ?></p>

        <h3>Počet rout podľa metódy:</h3>
        <ul>
            <?php
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

            foreach ($routesByMethod as $method => $count):
            ?>
                <li>
                    <span class="method <?= strtolower($method) ?>"><?= $method ?></span>: <?= $count ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <script>
        function filterRoutes() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('routesTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length; j++) {
                    const cellText = cells[j].textContent || cells[j].innerText;

                    if (cellText.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }

                rows[i].style.display = found ? '' : 'none';
            }
        }
    </script>
</body>
</html>
<?php
// Koniec skriptu
?>
