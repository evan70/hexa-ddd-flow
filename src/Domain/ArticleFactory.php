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

        return [
            'id' => $id ?? UuidGenerator::generate(),
            'title' => $title,
            'content' => $content,
            'type' => $type,
            'author_id' => $authorId,
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

        return $data;
    }
}
