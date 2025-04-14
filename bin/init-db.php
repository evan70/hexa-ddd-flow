<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Domain\UserFactory;
use App\Domain\ValueObject\Uuid;

// Načítanie nastavení
$settings = require __DIR__ . '/../config/settings.php';

// Vytvorenie adresára pre databázy, ak neexistuje
$dataDir = dirname($settings['database']['users']['path']);
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}

echo "Inicializácia databáz...\n";

// Inicializácia users databázy
$usersPdo = new PDO('sqlite:' . $settings['database']['users']['path']);
$usersPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vymazanie existujúcej tabuľky users, ak existuje
$usersPdo->exec('DROP TABLE IF EXISTS users');

// Vytvorenie tabuľky users
$usersPdo->exec('
    CREATE TABLE users (
        id CHAR(36) PRIMARY KEY,
        username TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        role TEXT NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL
    )
');

echo "Tabuľka users vytvorená.\n";

// Vloženie ukážkových používateľov
$sampleUsers = UserFactory::createSampleUsers();
$stmt = $usersPdo->prepare('
    INSERT INTO users (id, username, email, password, role, created_at, updated_at)
    VALUES (:id, :username, :email, :password, :role, :created_at, :updated_at)
');

foreach ($sampleUsers as $user) {
    $stmt->execute($user);
    echo "Používateľ {$user['username']} pridaný.\n";
}

// Inicializácia articles databázy
$articlesPdo = new PDO('sqlite:' . $settings['database']['articles']['path']);
$articlesPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vymazanie existujúcej tabuľky articles, ak existuje
$articlesPdo->exec('DROP TABLE IF EXISTS articles');

// Vytvorenie tabuľky articles
$articlesPdo->exec('
    CREATE TABLE articles (
        id CHAR(36) PRIMARY KEY,
        title TEXT NOT NULL,
        content TEXT NOT NULL,
        type TEXT NOT NULL,
        author_id CHAR(36) NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (author_id) REFERENCES users(id)
    )
');

echo "Tabuľka articles vytvorená.\n";

// Vytvorenie ukážkových článkov priamo
$now = date('Y-m-d H:i:s');
$sampleArticles = [
    [
        'id' => (string) Uuid::generate(),
        'title' => 'Prvý článok',
        'content' => 'Toto je obsah prvého článku. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        'type' => 'article',
        'author_id' => $sampleUsers[0]['id'],
        'created_at' => $now,
        'updated_at' => $now
    ],
    [
        'id' => (string) Uuid::generate(),
        'title' => 'Nový produkt',
        'content' => 'Predstavujeme nový produkt s množstvom funkcií. Donec auctor, magna eu fringilla tincidunt.',
        'type' => 'product',
        'author_id' => $sampleUsers[1]['id'],
        'created_at' => $now,
        'updated_at' => $now
    ],
    [
        'id' => (string) Uuid::generate(),
        'title' => 'O nás',
        'content' => 'Informácie o našej spoločnosti. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices.',
        'type' => 'page',
        'author_id' => $sampleUsers[0]['id'],
        'created_at' => $now,
        'updated_at' => $now
    ],
    [
        'id' => (string) Uuid::generate(),
        'title' => 'Druhý článok',
        'content' => 'Obsah druhého článku s ďalšími informáciami. Nullam eget felis eget nunc lobortis mattis.',
        'type' => 'article',
        'author_id' => $sampleUsers[2]['id'],
        'created_at' => $now,
        'updated_at' => $now
    ]
];

$stmt = $articlesPdo->prepare('
    INSERT INTO articles (id, title, content, type, author_id, created_at, updated_at)
    VALUES (:id, :title, :content, :type, :author_id, :created_at, :updated_at)
');

foreach ($sampleArticles as $article) {
    $stmt->execute($article);
    echo "Článok '{$article['title']}' pridaný.\n";
}

echo "Inicializácia databáz dokončená.\n";
