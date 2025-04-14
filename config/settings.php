<?php

declare(strict_types=1);

return [
    'displayErrorDetails' => true,
    'logErrorDetails' => true,
    'logErrors' => true,
    'database' => [
        'users' => [
            'path' => __DIR__ . '/../data/users.sqlite'
        ],
        'articles' => [
            'path' => __DIR__ . '/../data/articles.sqlite'
        ],
        'app' => [
            'path' => __DIR__ . '/../data/app.sqlite'
        ]
    ]
];
