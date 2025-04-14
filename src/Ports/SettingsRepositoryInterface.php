<?php

declare(strict_types=1);

namespace App\Ports;

interface SettingsRepositoryInterface
{
    /**
     * Získa všetky nastavenia
     *
     * @return array
     */
    public function getAll(): array;

    /**
     * Získa nastavenie podľa kľúča
     *
     * @param string $key
     * @return string|null
     */
    public function get(string $key): ?string;

    /**
     * Uloží nastavenie
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function set(string $key, string $value): bool;

    /**
     * Odstráni nastavenie
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;
}
