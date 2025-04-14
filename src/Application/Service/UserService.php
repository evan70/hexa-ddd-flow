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
}
