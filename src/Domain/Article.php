<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * Trieda pre články
 */
class Article
{
    /**
     * Typy článkov
     */
    public const TYPE_ARTICLE = 'article';
    public const TYPE_PRODUCT = 'product';
    public const TYPE_PAGE = 'page';

    /**
     * Overí, či je typ článku platný
     *
     * @param string $type
     * @return bool
     */
    public static function isValidType(string $type): bool
    {
        return in_array($type, [
            self::TYPE_ARTICLE,
            self::TYPE_PRODUCT,
            self::TYPE_PAGE
        ]);
    }

    /**
     * Vráti všetky dostupné typy článkov
     *
     * @return array
     */
    public static function getAllTypes(): array
    {
        return [
            self::TYPE_ARTICLE,
            self::TYPE_PRODUCT,
            self::TYPE_PAGE
        ];
    }
}
