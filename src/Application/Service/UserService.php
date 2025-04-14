<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Ports\UserRepositoryInterface;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Služba pre prácu s používateľmi
 */
class UserService
{
    private UserRepositoryInterface $userRepository;

    /**
     * Konštruktor
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Získa všetkých používateľov
     *
     * @return array
     */
    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }

    /**
     * Získa používateľa podľa ID
     *
     * @param string $id
     * @param Request $request
     * @return array
     * @throws HttpNotFoundException
     */
    public function getUserById(string $id, Request $request): array
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw new HttpNotFoundException($request, "User not found");
        }

        return $user;
    }

    /**
     * Získa používateľov podľa role
     *
     * @param string $role
     * @return array
     */
    public function getUsersByRole(string $role): array
    {
        return $this->userRepository->findByRole($role);
    }

    /**
     * Vytvorí nového používateľa
     *
     * @param array $userData Dáta používateľa
     * @return string ID vytvoreného používateľa
     * @throws \InvalidArgumentException Ak sú dáta neplatné
     */
    public function createUser(array $userData): string
    {
        // Validácia povinných polí
        $this->validateUserData($userData);

        // Uloženie používateľa
        return $this->userRepository->save($userData);
    }

    /**
     * Aktualizuje existujúceho používateľa
     *
     * @param string $id ID používateľa
     * @param array $userData Dáta používateľa
     * @param Request $request
     * @return string ID aktualizovaného používateľa
     * @throws HttpNotFoundException Ak používateľ neexistuje
     * @throws \InvalidArgumentException Ak sú dáta neplatné
     */
    public function updateUser(string $id, array $userData, Request $request): string
    {
        // Kontrola, či používateľ existuje
        $existingUser = $this->userRepository->findById($id);
        if (!$existingUser) {
            throw new HttpNotFoundException($request, "User not found");
        }

        // Pridanie ID do dát
        $userData['id'] = $id;

        // Validácia dát
        $this->validateUserData($userData, false);

        // Aktualizovanie používateľa
        return $this->userRepository->save($userData);
    }

    /**
     * Vymaže používateľa
     *
     * @param string $id ID používateľa
     * @param Request $request
     * @return bool Úspech operácie
     * @throws HttpNotFoundException Ak používateľ neexistuje
     */
    public function deleteUser(string $id, Request $request): bool
    {
        // Kontrola, či používateľ existuje
        $existingUser = $this->userRepository->findById($id);
        if (!$existingUser) {
            throw new HttpNotFoundException($request, "User not found");
        }

        // Vymazanie používateľa
        return $this->userRepository->delete($id);
    }

    /**
     * Validuje dáta používateľa
     *
     * @param array $userData Dáta používateľa
     * @param bool $isNew Či ide o nového používateľa (true) alebo aktualizáciu (false)
     * @throws \InvalidArgumentException Ak sú dáta neplatné
     */
    private function validateUserData(array $userData, bool $isNew = true): void
    {
        // Kontrola povinných polí pre nového používateľa
        if ($isNew) {
            $requiredFields = ['username', 'email', 'role'];
            foreach ($requiredFields as $field) {
                if (!isset($userData[$field]) || empty($userData[$field])) {
                    throw new \InvalidArgumentException("Chýba povinné pole: {$field}");
                }
            }
        }

        // Kontrola emailu, ak je zadaný
        if (isset($userData['email']) && !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Neplatný email: {$userData['email']}");
        }

        // Kontrola role, ak je zadaná
        if (isset($userData['role']) && !\App\Domain\User::isValid($userData['role'])) {
            throw new \InvalidArgumentException("Neplatná rola: {$userData['role']}");
        }
    }
}
