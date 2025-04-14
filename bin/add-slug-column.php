<?php

declare(strict_types=1);

// Načítanie nastavení
$settings = require __DIR__ . '/../config/settings.php';

// Pripojenie k databáze
$articlesPdo = new PDO('sqlite:' . $settings['database']['articles']['path']);
$articlesPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Kontrola, či stĺpec slug už existuje
$columns = $articlesPdo->query("PRAGMA table_info(articles)")->fetchAll(PDO::FETCH_ASSOC);
$slugColumnExists = false;

foreach ($columns as $column) {
    if ($column['name'] === 'slug') {
        $slugColumnExists = true;
        break;
    }
}

// Ak stĺpec slug neexistuje, pridáme ho
if (!$slugColumnExists) {
    echo "Pridávam stĺpec 'slug' do tabuľky 'articles'...\n";
    $articlesPdo->exec('ALTER TABLE articles ADD COLUMN slug TEXT');
    
    // Generovanie slugov pre existujúce články
    $articles = $articlesPdo->query('SELECT id, title FROM articles')->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($articles as $article) {
        $slug = createSlug($article['title']);
        
        // Kontrola, či slug už existuje
        $count = 1;
        $originalSlug = $slug;
        
        while (true) {
            $stmt = $articlesPdo->prepare('SELECT COUNT(*) FROM articles WHERE slug = :slug');
            $stmt->execute(['slug' => $slug]);
            $exists = (int) $stmt->fetchColumn();
            
            if ($exists === 0) {
                break;
            }
            
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        
        // Aktualizácia článku so slugom
        $stmt = $articlesPdo->prepare('UPDATE articles SET slug = :slug WHERE id = :id');
        $stmt->execute([
            'slug' => $slug,
            'id' => $article['id']
        ]);
        
        echo "Článok '{$article['title']}' má slug: {$slug}\n";
    }
    
    echo "Stĺpec 'slug' bol úspešne pridaný a všetky články majú vygenerované slugy.\n";
} else {
    echo "Stĺpec 'slug' už existuje v tabuľke 'articles'.\n";
}

/**
 * Vytvorí slug z reťazca
 *
 * @param string $text
 * @return string
 */
function createSlug(string $text): string {
    // Konverzia na malé písmená
    $text = mb_strtolower($text, 'UTF-8');
    
    // Nahradenie diakritiky
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    
    // Odstránenie všetkých znakov okrem písmen, číslic a pomlčiek
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    
    // Nahradenie viacerých pomlčiek jednou
    $text = preg_replace('/-+/', '-', $text);
    
    // Odstránenie pomlčiek na začiatku a konci
    $text = trim($text, '-');
    
    return $text;
}
