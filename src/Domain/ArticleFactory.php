<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\UuidGenerator;

/**
 * Factory trieda pre vytváranie článkov
 */
class ArticleFactory
{
    /**
     * Vytvorí nový článok
     *
     * @param string $title Názov článku
     * @param string $content Obsah článku
     * @param string $type Typ článku
     * @param string $authorId UUID autora
     * @param string|null $id UUID článku (voliteľné)
     * @return array Dáta článku
     */
    public static function create(
        string $title,
        string $content,
        string $type,
        string $authorId,
        ?string $id = null
    ): array {
        if (!ArticleType::isValid($type)) {
            throw new \InvalidArgumentException('Neplatný typ článku: ' . $type);
        }

        if (!UuidGenerator::isValid($authorId)) {
            throw new \InvalidArgumentException('Neplatné UUID autora: ' . $authorId);
        }

        // Generovanie slugu z názvu článku
        $slug = self::createSlug($title);

        return [
            'id' => $id ?? UuidGenerator::generate(),
            'title' => $title,
            'content' => $content,
            'type' => $type,
            'author_id' => $authorId,
            'slug' => $slug,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Vytvorí článok z existujúcich dát
     *
     * @param array $data Dáta článku
     * @return array Validované dáta článku
     */
    public static function createFromArray(array $data): array
    {
        // Validácia typu
        if (isset($data['type']) && !ArticleType::isValid($data['type'])) {
            throw new \InvalidArgumentException('Neplatný typ článku: ' . $data['type']);
        }

        // Validácia UUID autora
        if (isset($data['author_id']) && !UuidGenerator::isValid($data['author_id'])) {
            throw new \InvalidArgumentException('Neplatné UUID autora: ' . $data['author_id']);
        }

        // Nastavenie povinných polí, ak chýbajú
        if (!isset($data['id'])) {
            $data['id'] = UuidGenerator::generate();
        }

        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        // Generovanie slugu, ak chýba a máme názov
        if (!isset($data['slug']) && isset($data['title'])) {
            $data['slug'] = self::createSlug($data['title']);
        }

        return $data;
    }

    /**
     * Vytvorí slug z reťazca
     *
     * @param string $text
     * @return string
     */
    public static function createSlug(string $text): string {
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
}