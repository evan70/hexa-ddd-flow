<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\UuidGenerator;

/**
 * Factory trieda pre vytváranie používateľov
 */
class UserFactory
{
    /**
     * Vytvorí nového používateľa
     *
     * @param string $email Email používateľa
     * @param string $username Používateľské meno
     * @param string $passwordHash Hash hesla (voliteľné)
     * @param string $role Rola používateľa
     * @param string|null $id UUID používateľa (voliteľné)
     * @return array Dáta používateľa
     */
    public static function create(
        string $email,
        string $username,
        string $role,
        ?string $passwordHash = null,
        ?string $id = null
    ): array {
        if (!User::isValid($role)) {
            throw new \InvalidArgumentException('Neplatná rola používateľa: ' . $role);
        }

        return [
            'id' => $id ?? UuidGenerator::generate(),
            'username' => $username,
            'email' => $email,
            'password' => $passwordHash,
            'role' => $role,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Vytvorí používateľa z existujúcich dát
     *
     * @param array $data Dáta používateľa
     * @return array Validované dáta používateľa
     */
    public static function createFromArray(array $data): array
    {
        // Validácia role
        if (isset($data['role']) && !User::isValid($data['role'])) {
            throw new \InvalidArgumentException('Neplatná rola používateľa: ' . $data['role']);
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

    /**
     * Vytvorí ukážkových používateľov
     *
     * @return array Pole ukážkových používateľov
     */
    public static function createSampleUsers(): array
    {
        return [
            self::create(
                'admin@example.com',
                'admin',
                User::ADMIN,
                password_hash('admin123', PASSWORD_DEFAULT)
            ),
            self::create(
                'editor@example.com',
                'editor',
                User::EDITOR,
                password_hash('editor123', PASSWORD_DEFAULT)
            ),
            self::create(
                'author@example.com',
                'author',
                User::AUTHOR,
                password_hash('author123', PASSWORD_DEFAULT)
            ),
            self::create(
                'subscriber@example.com',
                'subscriber',
                User::SUBSCRIBER,
                password_hash('subscriber123', PASSWORD_DEFAULT)
            ),
        ];
    }
}
