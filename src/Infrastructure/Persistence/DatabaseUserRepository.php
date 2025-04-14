<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\User;
use App\Domain\UserFactory;
use App\Domain\ValueObject\Uuid;
use App\Ports\UserRepositoryInterface;
use PDO;

class DatabaseUserRepository implements UserRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): array
    {
        $statement = $this->pdo->query('SELECT * FROM users');
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(string|Uuid $id): ?array
    {
        // Konverzia Uuid objektu na string
        if ($id instanceof Uuid) {
            $id = $id->getValue();
        }

        $statement = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $statement->execute(['id' => $id]);

        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findByRole(string $role): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE role = :role');
        $statement->execute(['role' => $role]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function save(array $userData): string
    {
        try {
            // Použitie factory na validáciu a doplnenie dát
            $userData = UserFactory::createFromArray($userData);

            if (isset($userData['id']) && $this->findById($userData['id'])) {
                // Update existing user
                $sql = 'UPDATE users SET 
                        username = :username, 
                        email = :email, 
                        password = :password,
                        role = :role, 
                        updated_at = :updated_at 
                        WHERE id = :id';

                $existingUser = $this->findById($userData['id']);

                $statement = $this->pdo->prepare($sql);
                $statement->execute([
                    'id' => $userData['id'],
                    'username' => $userData['username'] ?? $existingUser['username'],
                    'email' => $userData['email'] ?? $existingUser['email'],
                    'password' => $userData['password'] ?? $existingUser['password'],
                    'role' => $userData['role'] ?? $existingUser['role'],
                    'updated_at' => $userData['updated_at']
                ]);

                return $userData['id'];
            } else {
                // Insert new user
                $sql = 'INSERT INTO users (id, username, email, password, role, created_at, updated_at) 
                        VALUES (:id, :username, :email, :password, :role, :created_at, :updated_at)';

                $statement = $this->pdo->prepare($sql);
                $statement->execute([
                    'id' => $userData['id'],
                    'username' => $userData['username'],
                    'email' => $userData['email'],
                    'password' => $userData['password'] ?? null,
                    'role' => $userData['role'],
                    'created_at' => $userData['created_at'],
                    'updated_at' => $userData['updated_at']
                ]);

                return $userData['id'];
            }
        } catch (\InvalidArgumentException $e) {
            // Zachytenie chyby z factory
            throw new \RuntimeException('Neplatné dáta používateľa: ' . $e->getMessage());
        }
    }

    public function delete(string|Uuid $id): bool
    {
        // Konverzia Uuid objektu na string
        if ($id instanceof Uuid) {
            $id = $id->getValue();
        }

        $statement = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }
}
