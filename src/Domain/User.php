<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * Trieda pre role používateľov
 */
class User
{
    public const ADMIN = 'admin';
    public const EDITOR = 'editor';
    public const AUTHOR = 'author';
    public const SUBSCRIBER = 'subscriber';

    /**
     * Vráti všetky dostupné role
     *
     * @return array
     */
    public static function getAll(): array
    {
        return [
            self::ADMIN,
            self::EDITOR,
            self::AUTHOR,
            self::SUBSCRIBER
        ];
    }

    /**
     * Overí, či je rola platná
     *
     * @param string $role
     * @return bool
     */
    public static function isValid(string $role): bool
    {
        return in_array($role, self::getAll());
    }
}
