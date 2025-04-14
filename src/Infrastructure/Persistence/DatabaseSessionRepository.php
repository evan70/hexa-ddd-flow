<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Ports\SessionRepositoryInterface;
use PDO;
use Ramsey\Uuid\Uuid;

class DatabaseSessionRepository implements SessionRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Vytvorí novú session
     *
     * @param string $userId ID používateľa
     * @param array $data Dáta session
     * @param int $expiresIn Platnosť session v sekundách
     * @return string ID vytvorenej session
     */
    public function create(string $userId, array $data = [], int $expiresIn = 3600): string
    {
        $sessionId = Uuid::uuid4()->toString();
        $now = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);
        $jsonData = json_encode($data);

        $statement = $this->pdo->prepare('
            INSERT INTO sessions (id, user_id, data, created_at, expires_at)
            VALUES (:id, :user_id, :data, :created_at, :expires_at)
        ');

        $statement->execute([
            'id' => $sessionId,
            'user_id' => $userId,
            'data' => $jsonData,
            'created_at' => $now,
            'expires_at' => $expiresAt
        ]);

        return $sessionId;
    }

    /**
     * Získa session podľa ID
     *
     * @param string $sessionId ID session
     * @return array|null Dáta session alebo null, ak session neexistuje alebo vypršala
     */
    public function get(string $sessionId): ?array
    {
        $statement = $this->pdo->prepare('
            SELECT * FROM sessions
            WHERE id = :id AND expires_at > :now
        ');

        $statement->execute([
            'id' => $sessionId,
            'now' => date('Y-m-d H:i:s')
        ]);

        $session = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            return null;
        }

        // Dekódovanie JSON dát
        $session['data'] = json_decode($session['data'], true) ?: [];

        return $session;
    }

    /**
     * Aktualizuje dáta session
     *
     * @param string $sessionId ID session
     * @param array $data Nové dáta session
     * @return bool Úspech operácie
     */
    public function update(string $sessionId, array $data): bool
    {
        $jsonData = json_encode($data);

        $statement = $this->pdo->prepare('
            UPDATE sessions
            SET data = :data
            WHERE id = :id AND expires_at > :now
        ');

        $statement->execute([
            'id' => $sessionId,
            'data' => $jsonData,
            'now' => date('Y-m-d H:i:s')
        ]);

        return $statement->rowCount() > 0;
    }

    /**
     * Vymaže session
     *
     * @param string $sessionId ID session
     * @return bool Úspech operácie
     */
    public function delete(string $sessionId): bool
    {
        $statement = $this->pdo->prepare('
            DELETE FROM sessions
            WHERE id = :id
        ');

        $statement->execute(['id' => $sessionId]);

        return $statement->rowCount() > 0;
    }

    /**
     * Vymaže všetky sessions používateľa
     *
     * @param string $userId ID používateľa
     * @return bool Úspech operácie
     */
    public function deleteAllForUser(string $userId): bool
    {
        $statement = $this->pdo->prepare('
            DELETE FROM sessions
            WHERE user_id = :user_id
        ');

        $statement->execute(['user_id' => $userId]);

        return $statement->rowCount() > 0;
    }

    /**
     * Vymaže všetky vypršané sessions
     *
     * @return int Počet vymazaných sessions
     */
    public function deleteExpired(): int
    {
        $statement = $this->pdo->prepare('
            DELETE FROM sessions
            WHERE expires_at <= :now
        ');

        $statement->execute(['now' => date('Y-m-d H:i:s')]);

        return $statement->rowCount();
    }

    /**
     * Overí, či je session platná
     *
     * @param string $sessionId ID session
     * @return bool True, ak je session platná
     */
    public function isValid(string $sessionId): bool
    {
        $statement = $this->pdo->prepare('
            SELECT COUNT(*) FROM sessions
            WHERE id = :id AND expires_at > :now
        ');

        $statement->execute([
            'id' => $sessionId,
            'now' => date('Y-m-d H:i:s')
        ]);

        return (int) $statement->fetchColumn() > 0;
    }
}
