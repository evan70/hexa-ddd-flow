<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\ValueObject\Uuid;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

/**
 * Trieda pre generovanie UUID
 *
 * @deprecated Použite App\Domain\ValueObject\Uuid namiesto tejto triedy
 */
class UuidGenerator
{
    /**
     * Vygeneruje nové UUID v4
     *
     * @return string
     * @deprecated Použite Uuid::generate() namiesto tejto metódy
     */
    public static function generate(): string
    {
        return Uuid::generate()->getValue();
    }

    /**
     * Overí, či je reťazec platným UUID
     *
     * @param string $uuid
     * @return bool
     * @deprecated Použite Uuid::fromString() namiesto tejto metódy
     */
    public static function isValid(string $uuid): bool
    {
        try {
            // Oprava: Použitie výsledku volania metódy
            return RamseyUuid::isValid($uuid);
        } catch (InvalidUuidStringException $e) {
            return false;
        }
    }

    /**
     * Vráti nil UUID (00000000-0000-0000-0000-000000000000)
     *
     * @return string
     * @deprecated Použite Uuid::nil() namiesto tejto metódy
     */
    public static function nil(): string
    {
        return Uuid::nil()->getValue();
    }
}
