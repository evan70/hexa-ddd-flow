<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Domain\UserFactory;
use App\Domain\ArticleType;
use App\Domain\User;
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

// Vytvorenie rozšíreného zoznamu používateľov
$sampleUsers = [
    // Administrátori
    UserFactory::create(
        'admin@example.com',
        'admin',
        User::ADMIN,
        password_hash('admin123', PASSWORD_DEFAULT)
    ),
    UserFactory::create(
        'john.admin@example.com',
        'john_admin',
        User::ADMIN,
        password_hash('password123', PASSWORD_DEFAULT)
    ),
    
    // Editori
    UserFactory::create(
        'editor@example.com',
        'editor',
        User::EDITOR,
        password_hash('editor123', PASSWORD_DEFAULT)
    ),
    UserFactory::create(
        'jane.editor@example.com',
        'jane_editor',
        User::EDITOR,
        password_hash('password123', PASSWORD_DEFAULT)
    ),
    UserFactory::create(
        'mark.editor@example.com',
        'mark_editor',
        User::EDITOR,
        password_hash('password123', PASSWORD_DEFAULT)
    ),
    
    // Autori
    UserFactory::create(
        'author@example.com',
        'author',
        User::AUTHOR,
        password_hash('author123', PASSWORD_DEFAULT)
    ),
    UserFactory::create(
        'sarah.author@example.com',
        'sarah_author',
        User::AUTHOR,
        password_hash('password123', PASSWORD_DEFAULT)
    ),
    UserFactory::create(
        'mike.author@example.com',
        'mike_author',
        User::AUTHOR,
        password_hash('password123', PASSWORD_DEFAULT)
    ),
    UserFactory::create(
        'lisa.author@example.com',
        'lisa_author',
        User::AUTHOR,
        password_hash('password123', PASSWORD_DEFAULT)
    ),
    
    // Používatelia
    UserFactory::create(
        'subscriber@example.com',
        'subscriber',
        User::SUBSCRIBER,
        password_hash('subscriber123', PASSWORD_DEFAULT)
    ),
    UserFactory::create(
        'user1@example.com',
        'user1',
        User::SUBSCRIBER,
        password_hash('password123', PASSWORD_DEFAULT)
    ),
    UserFactory::create(
        'user2@example.com',
        'user2',
        User::SUBSCRIBER,
        password_hash('password123', PASSWORD_DEFAULT)
    ),
    UserFactory::create(
        'user3@example.com',
        'user3',
        User::SUBSCRIBER,
        password_hash('password123', PASSWORD_DEFAULT)
    ),
    UserFactory::create(
        'user4@example.com',
        'user4',
        User::SUBSCRIBER,
        password_hash('password123', PASSWORD_DEFAULT)
    ),
];

$stmt = $usersPdo->prepare('
    INSERT INTO users (id, username, email, password, role, created_at, updated_at)
    VALUES (:id, :username, :email, :password, :role, :created_at, :updated_at)
');

foreach ($sampleUsers as $user) {
    $stmt->execute($user);
    echo "Používateľ {$user['username']} ({$user['role']}) pridaný.\n";
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
        categories TEXT NOT NULL DEFAULT "[]",  -- JSON pole kategórií
        tag TEXT,                              -- Jeden tag
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (author_id) REFERENCES users(id)
    )
');

echo "Tabuľka articles vytvorená.\n";

// Funkcia pre generovanie náhodného dátumu v posledných 30 dňoch
function randomDate() {
    $timestamp = time() - rand(0, 30 * 24 * 60 * 60);
    return date('Y-m-d H:i:s', $timestamp);
}

// Zoznam dostupných kategórií
$categories = [
    'PHP',
    'JavaScript',
    'HTML',
    'CSS',
    'Database',
    'Security',
    'Performance',
    'Architecture',
    'Testing',
    'DevOps',
    'Frontend',
    'Backend',
    'Mobile',
    'API',
    'Framework',
    'Library',
    'Tutorial',
    'Best Practices',
    'Case Study',
    'News'
];

// Zoznam dostupných tagov
$tags = [
    'beginner',
    'intermediate',
    'advanced',
    'expert',
    'trending',
    'popular',
    'featured',
    'recommended',
    'new',
    'updated',
    'free',
    'premium',
    'exclusive',
    'limited',
    'hot',
    'cool',
    'essential',
    'must-read',
    'top-rated',
    'editor-choice'
];

// Funkcia pre výber náhodných kategórií
function getRandomCategories($categories, $min = 1, $max = 3) {
    $count = rand($min, $max);
    $selectedCategories = [];
    
    // Zabezpečenie, že počet kategórií neprekročí počet dostupných kategórií
    $count = min($count, count($categories));
    
    $keys = array_rand($categories, $count);
    
    // Ak je vybraná len jedna kategória, array_rand vráti int namiesto poľa
    if (!is_array($keys)) {
        $keys = [$keys];
    }
    
    foreach ($keys as $key) {
        $selectedCategories[] = $categories[$key];
    }
    
    return $selectedCategories;
}

// Funkcia pre výber náhodného tagu
function getRandomTag($tags) {
    return $tags[array_rand($tags)];
}

// Funkcia pre generovanie náhodného obsahu článku
function generateContent($paragraphs = 3) {
    $loremIpsum = [
        "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam auctor, magna eu fringilla tincidunt, nisl nunc aliquet nunc, vitae aliquam nisl nunc eu nisl. Nullam auctor, magna eu fringilla tincidunt, nisl nunc aliquet nunc, vitae aliquam nisl nunc eu nisl.",
        "Donec auctor, magna eu fringilla tincidunt, nisl nunc aliquet nunc, vitae aliquam nisl nunc eu nisl. Nullam auctor, magna eu fringilla tincidunt, nisl nunc aliquet nunc, vitae aliquam nisl nunc eu nisl.",
        "Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Donec velit neque, auctor sit amet aliquam vel, ullamcorper sit amet ligula. Proin eget tortor risus. Vivamus magna justo, lacinia eget consectetur sed, convallis at tellus.",
        "Curabitur aliquet quam id dui posuere blandit. Vivamus suscipit tortor eget felis porttitor volutpat. Curabitur non nulla sit amet nisl tempus convallis quis ac lectus.",
        "Praesent sapien massa, convallis a pellentesque nec, egestas non nisi. Cras ultricies ligula sed magna dictum porta. Pellentesque in ipsum id orci porta dapibus.",
        "Nulla quis lorem ut libero malesuada feugiat. Curabitur non nulla sit amet nisl tempus convallis quis ac lectus. Sed porttitor lectus nibh.",
        "Vivamus magna justo, lacinia eget consectetur sed, convallis at tellus. Nulla porttitor accumsan tincidunt. Curabitur aliquet quam id dui posuere blandit."
    ];
    
    $content = '';
    for ($i = 0; $i < $paragraphs; $i++) {
        $content .= $loremIpsum[array_rand($loremIpsum)] . "\n\n";
    }
    
    return trim($content);
}

// Vytvorenie rozšíreného zoznamu článkov
$sampleArticles = [];

// Funkcia pre priradenie vhodných kategórií podľa typu článku
function getCategoriesByType($type, $categories) {
    switch ($type) {
        case 'article':
            // Články môžu mať všetky kategórie
            return getRandomCategories($categories, 1, 4);
        case 'product':
            // Produkty majú obmedzený výber kategórií
            $productCategories = array_filter($categories, function($category) {
                return in_array($category, [
                    'PHP', 'JavaScript', 'Framework', 'Library', 'Tutorial', 
                    'Best Practices', 'Performance', 'Security', 'Architecture', 'Testing'
                ]);
            });
            return getRandomCategories(array_values($productCategories), 1, 3);
        case 'page':
            // Stránky majú špecifické kategórie
            $pageCategories = ['Company', 'Information', 'Legal', 'Support', 'About'];
            return getRandomCategories($pageCategories, 1, 2);
        default:
            return [];
    }
}

// Funkcia pre priradenie vhodného tagu podľa typu článku
function getTagByType($type, $tags) {
    switch ($type) {
        case 'article':
            // Články môžu mať všetky tagy
            return getRandomTag($tags);
        case 'product':
            // Produkty majú obmedzený výber tagov
            $productTags = array_filter($tags, function($tag) {
                return in_array($tag, [
                    'premium', 'exclusive', 'featured', 'recommended', 'new', 
                    'updated', 'hot', 'trending', 'popular', 'essential'
                ]);
            });
            return getRandomTag(array_values($productTags));
        case 'page':
            // Stránky nemusia mať tag
            return rand(0, 1) ? null : 'important';
        default:
            return null;
    }
}

// Články typu 'article'
$articleTitles = [
    'Ako začať s programovaním v PHP',
    'Najlepšie praktiky pre vývoj webových aplikácií',
    'Úvod do objektovo orientovaného programovania',
    'Čo je nové v PHP 8.3',
    'Ako optimalizovať výkon vašej webovej aplikácie',
    'Bezpečnostné tipy pre PHP vývojárov',
    'Práca s databázami v PHP',
    'Moderné PHP frameworky v roku 2023',
    'Ako implementovať REST API v PHP',
    'Testovanie PHP aplikácií: Unit testy a TDD'
];

foreach ($articleTitles as $index => $title) {
    $date = randomDate();
    $authorIndex = array_rand(array_filter($sampleUsers, function($user) {
        return $user['role'] === User::AUTHOR || $user['role'] === User::EDITOR;
    }));
    
    // Získanie kategórií a tagu pre článok
    $articleCategories = getCategoriesByType('article', $categories);
    $articleTag = getTagByType('article', $tags);
    
    $sampleArticles[] = [
        'id' => (string) Uuid::generate(),
        'title' => $title,
        'content' => generateContent(rand(3, 6)),
        'type' => 'article',
        'author_id' => $sampleUsers[$authorIndex]['id'],
        'categories' => json_encode($articleCategories),
        'tag' => $articleTag,
        'created_at' => $date,
        'updated_at' => $date
    ];
}

// Produkty
$productTitles = [
    'Profesionálny PHP kurz',
    'Kniha: Moderné PHP aplikácie',
    'Online workshop: Vývoj webových aplikácií',
    'Konzultácie pre PHP vývojárov',
    'Balík nástrojov pre PHP vývojárov',
    'Šablóny pre PHP projekty',
    'Kurz: Hexagonálna architektúra v praxi',
    'Kniha: Domain-Driven Design v PHP',
    'Prémiové pluginy pre PHP aplikácie',
    'Vývojárske nástroje pre PHP'
];

foreach ($productTitles as $index => $title) {
    $date = randomDate();
    $authorIndex = array_rand(array_filter($sampleUsers, function($user) {
        return $user['role'] === User::EDITOR || $user['role'] === User::ADMIN;
    }));
    
    // Získanie kategórií a tagu pre produkt
    $productCategories = getCategoriesByType('product', $categories);
    $productTag = getTagByType('product', $tags);
    
    $sampleArticles[] = [
        'id' => (string) Uuid::generate(),
        'title' => $title,
        'content' => generateContent(rand(2, 4)),
        'type' => 'product',
        'author_id' => $sampleUsers[$authorIndex]['id'],
        'categories' => json_encode($productCategories),
        'tag' => $productTag,
        'created_at' => $date,
        'updated_at' => $date
    ];
}

// Stránky
$pageTitles = [
    'O nás',
    'Kontakt',
    'Služby',
    'Referencie',
    'Kariéra',
    'FAQ',
    'Podmienky používania',
    'Ochrana osobných údajov',
    'História spoločnosti',
    'Náš tím'
];

foreach ($pageTitles as $index => $title) {
    $date = randomDate();
    $authorIndex = array_rand(array_filter($sampleUsers, function($user) {
        return $user['role'] === User::ADMIN;
    }));
    
    // Získanie kategórií a tagu pre stránku
    $pageCategories = getCategoriesByType('page', $categories);
    $pageTag = getTagByType('page', $tags);
    
    $sampleArticles[] = [
        'id' => (string) Uuid::generate(),
        'title' => $title,
        'content' => generateContent(rand(2, 5)),
        'type' => 'page',
        'author_id' => $sampleUsers[$authorIndex]['id'],
        'categories' => json_encode($pageCategories),
        'tag' => $pageTag,
        'created_at' => $date,
        'updated_at' => $date
    ];
}

$stmt = $articlesPdo->prepare('
    INSERT INTO articles (id, title, content, type, author_id, categories, tag, created_at, updated_at)
    VALUES (:id, :title, :content, :type, :author_id, :categories, :tag, :created_at, :updated_at)
');

foreach ($sampleArticles as $article) {
    $stmt->execute($article);
    echo "Článok '{$article['title']}' (typ: {$article['type']}) pridaný.\n";
}

// Výpis štatistík
$userStats = $usersPdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role")->fetchAll(PDO::FETCH_ASSOC);
$articleStats = $articlesPdo->query("SELECT type, COUNT(*) as count FROM articles GROUP BY type")->fetchAll(PDO::FETCH_ASSOC);

// Získanie štatistík o kategóriách a tagoch
$categoryStats = [];
$tagStats = [];

$articles = $articlesPdo->query("SELECT categories, tag FROM articles")->fetchAll(PDO::FETCH_ASSOC);
foreach ($articles as $article) {
    $articleCategories = json_decode($article['categories'], true);
    foreach ($articleCategories as $category) {
        if (!isset($categoryStats[$category])) {
            $categoryStats[$category] = 0;
        }
        $categoryStats[$category]++;
    }
    
    if (!empty($article['tag'])) {
        if (!isset($tagStats[$article['tag']])) {
            $tagStats[$article['tag']] = 0;
        }
        $tagStats[$article['tag']]++;
    }
}

// Zoradenie štatistík podľa počtu výskytov
arsort($categoryStats);
arsort($tagStats);

echo "\n=== Štatistiky ===\n";
echo "Používatelia:\n";
foreach ($userStats as $stat) {
    echo "  - {$stat['role']}: {$stat['count']}\n";
}

echo "Články:\n";
foreach ($articleStats as $stat) {
    echo "  - {$stat['type']}: {$stat['count']}\n";
}

echo "\nTop 5 kategórií:\n";
$i = 0;
foreach ($categoryStats as $category => $count) {
    echo "  - {$category}: {$count}\n";
    $i++;
    if ($i >= 5) break;
}

echo "\nTop 5 tagov:\n";
$i = 0;
foreach ($tagStats as $tag => $count) {
    echo "  - {$tag}: {$count}\n";
    $i++;
    if ($i >= 5) break;
}

echo "\nInicializácia databáz dokončená.\n";
