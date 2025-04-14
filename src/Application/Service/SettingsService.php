<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Ports\SettingsRepositoryInterface;

class SettingsService
{
    private SettingsRepositoryInterface $settingsRepository;

    /**
     * Konštruktor
     *
     * @param SettingsRepositoryInterface $settingsRepository
     */
    public function __construct(SettingsRepositoryInterface $settingsRepository)
    {
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * Získa všetky nastavenia
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->settingsRepository->getAll();
    }

    /**
     * Získa nastavenie podľa kľúča
     *
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function get(string $key, ?string $default = null): ?string
    {
        $value = $this->settingsRepository->get($key);
        return $value !== null ? $value : $default;
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
        return $this->settingsRepository->set($key, $value);
    }

    /**
     * Uloží viacero nastavení naraz
     *
     * @param array $settings
     * @return bool
     */
    public function setMultiple(array $settings): bool
    {
        $success = true;

        foreach ($settings as $key => $value) {
            $result = $this->settingsRepository->set($key, $value);
            if (!$result) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Odstráni nastavenie
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        return $this->settingsRepository->delete($key);
    }
}
