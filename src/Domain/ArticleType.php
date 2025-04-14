<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * Trieda pre typy článkov
 */
class ArticleType
{
    public const ARTICLE = 'article';
    public const PRODUCT = 'product';
    public const PAGE = 'page';
    
    /**
     * Vráti všetky dostupné typy
     *
     * @return array
     */
    public static function getAll(): array
    {
        return [
            self::ARTICLE,
            self::PRODUCT,
            self::PAGE
        ];
    }
    
    /**
     * Overí, či je typ platný
     *
     * @param string $type
     * @return bool
     */
    public static function isValid(string $type): bool
    {
        return in_array($type, self::getAll());
    }
}
