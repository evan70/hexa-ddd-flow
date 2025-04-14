<?php

declare(strict_types=1);

namespace App\Ports;

interface SessionRepositoryInterface
{
    /**
     * Vytvorí novú session
     *
     * @param string $userId ID používateľa
     * @param array $data Dáta session
     * @param int $expiresIn Platnosť session v sekundách
     * @return string ID vytvorenej session
     */
    public function create(string $userId, array $data = [], int $expiresIn = 3600): string;

    /**
     * Získa session podľa ID
     *
     * @param string $sessionId ID session
     * @return array|null Dáta session alebo null, ak session neexistuje alebo vypršala
     */
    public function get(string $sessionId): ?array;

    /**
     * Aktualizuje dáta session
     *
     * @param string $sessionId ID session
     * @param array $data Nové dáta session
     * @return bool Úspech operácie
     */
    public function update(string $sessionId, array $data): bool;

    /**
     * Vymaže session
     *
     * @param string $sessionId ID session
     * @return bool Úspech operácie
     */
    public function delete(string $sessionId): bool;

    /**
     * Vymaže všetky sessions používateľa
     *
     * @param string $userId ID používateľa
     * @return bool Úspech operácie
     */
    public function deleteAllForUser(string $userId): bool;

    /**
     * Vymaže všetky vypršané sessions
     *
     * @return int Počet vymazaných sessions
     */
    public function deleteExpired(): int;

    /**
     * Overí, či je session platná
     *
     * @param string $sessionId ID session
     * @return bool True, ak je session platná
     */
    public function isValid(string $sessionId): bool;
}
