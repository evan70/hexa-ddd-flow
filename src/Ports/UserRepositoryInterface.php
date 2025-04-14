<?php

declare(strict_types=1);

namespace App\Ports;

use App\Domain\ValueObject\Uuid;

interface UserRepositoryInterface
{
    /**
     * Find all users
     *
     * @return array List of all users
     */
    public function findAll(): array;

    /**
     * Find a user by ID
     *
     * @param string|Uuid $id
     * @return array|null User data or null if not found
     */
    public function findById(string|Uuid $id): ?array;

    /**
     * Find users by role
     *
     * @param string $role
     * @return array List of users with the specified role
     */
    public function findByRole(string $role): array;

    /**
     * Save user data
     *
     * @param array $userData
     * @return string ID of the saved user
     */
    public function save(array $userData): string;

    /**
     * Delete a user
     *
     * @param string|Uuid $id
     * @return bool Success status
     */
    public function delete(string|Uuid $id): bool;
}
