<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Ports\SessionRepositoryInterface;
use App\Ports\UserRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthService
{
    private UserRepositoryInterface $userRepository;
    private SessionRepositoryInterface $sessionRepository;
    private string $cookieName;
    private int $sessionLifetime;

    /**
     * Konštruktor
     *
     * @param UserRepositoryInterface $userRepository
     * @param SessionRepositoryInterface $sessionRepository
     * @param string $cookieName Názov cookie pre session
     * @param int $sessionLifetime Životnosť session v sekundách
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        SessionRepositoryInterface $sessionRepository,
        string $cookieName = 'session_id',
        int $sessionLifetime = 86400 // 24 hodín
    ) {
        $this->userRepository = $userRepository;
        $this->sessionRepository = $sessionRepository;
        $this->cookieName = $cookieName;
        $this->sessionLifetime = $sessionLifetime;
    }

    /**
     * Prihlási používateľa
     *
     * @param string $email Email používateľa
     * @param string $password Heslo používateľa
     * @return array|null Dáta používateľa alebo null, ak prihlásenie zlyhalo
     */
    public function login(string $email, string $password): ?array
    {
        // V reálnej aplikácii by sme tu mali hľadať používateľa podľa emailu
        // a overovať heslo pomocou password_verify()
        // Pre účely dema budeme používať jednoduchú implementáciu

        // Hľadáme používateľa podľa emailu
        $users = $this->userRepository->findAll();
        $user = null;

        foreach ($users as $u) {
            if ($u['email'] === $email) {
                $user = $u;
                break;
            }
        }

        if (!$user) {
            return null;
        }

        // V demo implementácii akceptujeme akékoľvek heslo
        // V reálnej aplikácii by sme tu mali použiť password_verify()
        
        // Vytvorenie session
        $sessionId = $this->sessionRepository->create(
            $user['id'],
            ['user' => $user],
            $this->sessionLifetime
        );

        // Nastavenie cookie
        setcookie(
            $this->cookieName,
            $sessionId,
            [
                'expires' => time() + $this->sessionLifetime,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );

        return $user;
    }

    /**
     * Odhlási používateľa
     *
     * @return bool Úspech operácie
     */
    public function logout(): bool
    {
        if (!isset($_COOKIE[$this->cookieName])) {
            return false;
        }

        $sessionId = $_COOKIE[$this->cookieName];
        
        // Vymazanie cookie
        setcookie(
            $this->cookieName,
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );

        // Vymazanie session
        return $this->sessionRepository->delete($sessionId);
    }

    /**
     * Získa aktuálne prihláseného používateľa
     *
     * @param Request $request
     * @return array|null Dáta používateľa alebo null, ak používateľ nie je prihlásený
     */
    public function getCurrentUser(Request $request): ?array
    {
        $cookies = $request->getCookieParams();
        
        if (!isset($cookies[$this->cookieName])) {
            return null;
        }

        $sessionId = $cookies[$this->cookieName];
        $session = $this->sessionRepository->get($sessionId);

        if (!$session || !isset($session['data']['user'])) {
            return null;
        }

        return $session['data']['user'];
    }

    /**
     * Overí, či je používateľ prihlásený
     *
     * @param Request $request
     * @return bool True, ak je používateľ prihlásený
     */
    public function isLoggedIn(Request $request): bool
    {
        return $this->getCurrentUser($request) !== null;
    }

    /**
     * Overí, či má používateľ požadovanú rolu
     *
     * @param Request $request
     * @param string $role Požadovaná rola
     * @return bool True, ak má používateľ požadovanú rolu
     */
    public function hasRole(Request $request, string $role): bool
    {
        $user = $this->getCurrentUser($request);

        if (!$user) {
            return false;
        }

        return $user['role'] === $role;
    }

    /**
     * Overí, či má používateľ aspoň jednu z požadovaných rolí
     *
     * @param Request $request
     * @param array $roles Požadované role
     * @return bool True, ak má používateľ aspoň jednu z požadovaných rolí
     */
    public function hasAnyRole(Request $request, array $roles): bool
    {
        $user = $this->getCurrentUser($request);

        if (!$user) {
            return false;
        }

        return in_array($user['role'], $roles);
    }
}
