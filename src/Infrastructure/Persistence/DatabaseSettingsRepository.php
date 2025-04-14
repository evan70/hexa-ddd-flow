<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Ports\SettingsRepositoryInterface;
use PDO;

class DatabaseSettingsRepository implements SettingsRepositoryInterface
{
    private PDO $pdo;

    /**
     * Konštruktor
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Získa všetky nastavenia
     *
     * @return array
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM settings');
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['key']] = $setting['value'];
        }
        
        return $result;
    }

    /**
     * Získa nastavenie podľa kľúča
     *
     * @param string $key
     * @return string|null
     */
    public function get(string $key): ?string
    {
        $stmt = $this->pdo->prepare('SELECT value FROM settings WHERE key = :key');
        $stmt->execute(['key' => $key]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['value'] : null;
    }

    /**
     * Uloží nastavenie
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function set(string $key, string $value): bool
    {
        $now = date('Y-m-d H:i:s');
        
        // Kontrola, či nastavenie už existuje
        $stmt = $this->pdo->prepare('SELECT id FROM settings WHERE key = :key');
        $stmt->execute(['key' => $key]);
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            // Aktualizácia existujúceho nastavenia
            $stmt = $this->pdo->prepare('
                UPDATE settings 
                SET value = :value, updated_at = :updated_at 
                WHERE key = :key
            ');
            
            return $stmt->execute([
                'key' => $key,
                'value' => $value,
                'updated_at' => $now
            ]);
        } else {
            // Vytvorenie nového nastavenia
            $stmt = $this->pdo->prepare('
                INSERT INTO settings (key, value, created_at, updated_at)
                VALUES (:key, :value, :created_at, :updated_at)
            ');
            
            return $stmt->execute([
                'key' => $key,
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }
    }

    /**
     * Odstráni nastavenie
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM settings WHERE key = :key');
        return $stmt->execute(['key' => $key]);
    }
}
