<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// Načítanie .env súboru
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Cesta k databáze
$dbPath = __DIR__ . '/../var/app.sqlite';
$dbDir = dirname($dbPath);

// Vytvorenie adresára, ak neexistuje
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0777, true);
}

// Odstránenie existujúcej databázy, ak existuje
if (file_exists($dbPath)) {
    unlink($dbPath);
}

// Vytvorenie novej databázy
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vytvorenie tabuľky pre sessions
$pdo->exec('
CREATE TABLE sessions (
    id TEXT PRIMARY KEY,
    user_id TEXT,
    data TEXT,
    created_at TEXT,
    expires_at TEXT
)
');

// Vytvorenie tabuľky pre aplikačné nastavenia
$pdo->exec('
CREATE TABLE settings (
    key TEXT PRIMARY KEY,
    value TEXT,
    updated_at TEXT
)
');

// Vytvorenie indexov
$pdo->exec('CREATE INDEX idx_sessions_user_id ON sessions(user_id)');
$pdo->exec('CREATE INDEX idx_sessions_expires_at ON sessions(expires_at)');

echo "Aplikačná databáza bola úspešne inicializovaná.\n";
