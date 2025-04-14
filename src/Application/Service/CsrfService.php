<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Ports\SessionRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class CsrfService
{
    private SessionRepositoryInterface $sessionRepository;
    private string $cookieName;
    private string $tokenName;
    private int $tokenExpiration;

    /**
     * Konštruktor
     *
     * @param SessionRepositoryInterface $sessionRepository
     * @param string $cookieName Názov cookie pre session
     * @param string $tokenName Názov CSRF tokenu
     * @param int $tokenExpiration Platnosť tokenu v sekundách
     */
    public function __construct(
        SessionRepositoryInterface $sessionRepository,
        string $cookieName = 'session_id',
        string $tokenName = 'csrf_token',
        int $tokenExpiration = 3600 // 1 hodina
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->cookieName = $cookieName;
        $this->tokenName = $tokenName;
        $this->tokenExpiration = $tokenExpiration;
    }

    /**
     * Generuje nový CSRF token a uloží ho do session
     *
     * @param Request $request
     * @return string Vygenerovaný token
     */
    public function generateToken(Request $request): string
    {
        $cookies = $request->getCookieParams();
        $sessionId = null;
        $sessionData = [];

        // Kontrola, či existuje session cookie
        if (isset($cookies[$this->cookieName])) {
            $sessionId = $cookies[$this->cookieName];
            $session = $this->sessionRepository->get($sessionId);

            if ($session) {
                $sessionData = $session['data'];
            } else {
                // Session ID existuje, ale session nie je v databáze
                $sessionId = null;
            }
        }

        // Ak session neexistuje, vytvoríme novú
        if ($sessionId === null) {
            $sessionId = bin2hex(random_bytes(16));

            // Vytvorenie novej session
            $this->sessionRepository->create($sessionId, null, $sessionData);

            // Vrátime response s cookie
            $response = $request->getAttribute('response');
            if ($response) {
                $response = $response->withHeader('Set-Cookie', $this->cookieName . '=' . $sessionId . '; Path=/; HttpOnly; SameSite=Lax');
                $request = $request->withAttribute('response', $response);
            }
        }

        // Generovanie náhodného tokenu
        $token = bin2hex(random_bytes(32));

        // Uloženie tokenu do session
        $sessionData[$this->tokenName] = [
            'token' => $token,
            'expires' => time() + $this->tokenExpiration
        ];

        $this->sessionRepository->update($sessionId, $sessionData);

        return $token;
    }

    /**
     * Overí platnosť CSRF tokenu
     *
     * @param Request $request
     * @param string $token Token na overenie
     * @return bool True, ak je token platný
     */
    public function validateToken(Request $request, string $token): bool
    {
        $cookies = $request->getCookieParams();

        // Ak nie je nastavená cookie, token nie je platný
        if (!isset($cookies[$this->cookieName])) {
            return false;
        }

        $sessionId = $cookies[$this->cookieName];
        $session = $this->sessionRepository->get($sessionId);

        // Ak session neexistuje alebo neobsahuje CSRF token, token nie je platný
        if (!$session || !isset($session['data'][$this->tokenName])) {
            return false;
        }

        $tokenData = $session['data'][$this->tokenName];

        // Kontrola expirácie tokenu
        if (time() > $tokenData['expires']) {
            return false;
        }

        // Kontrola tokenu
        return hash_equals($tokenData['token'], $token);
    }

    /**
     * Generuje nový CSRF token a uloží ho do session (pre použitie v šablónach)
     *
     * @return string Vygenerovaný token
     */
    public function generate(): string
    {
        $sessionId = null;
        $sessionData = [];

        // Kontrola, či existuje session cookie
        if (isset($_COOKIE[$this->cookieName])) {
            $sessionId = $_COOKIE[$this->cookieName];
            $session = $this->sessionRepository->get($sessionId);

            if ($session) {
                $sessionData = $session['data'];
            } else {
                // Session ID existuje, ale session nie je v databáze
                $sessionId = null;
            }
        }

        // Ak session neexistuje, vytvoríme novú
        if ($sessionId === null) {
            $sessionId = bin2hex(random_bytes(16));

            // Nastavenie cookie
            setcookie(
                $this->cookieName,
                $sessionId,
                [
                    'expires' => time() + 86400,
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );

            // Vytvorenie novej session
            $this->sessionRepository->create($sessionId, null, $sessionData);
        }

        // Generovanie náhodného tokenu
        $token = bin2hex(random_bytes(32));

        // Uloženie tokenu do session
        $sessionData[$this->tokenName] = [
            'token' => $token,
            'expires' => time() + $this->tokenExpiration
        ];

        $this->sessionRepository->update($sessionId, $sessionData);

        return $token;
    }

    /**
     * Overí platnosť CSRF tokenu (pre použitie v middleware)
     *
     * @param string $token Token na overenie
     * @return bool True, ak je token platný
     */
    public function validate(string $token): bool
    {
        // Ak nie je nastavená cookie, token nie je platný
        if (!isset($_COOKIE[$this->cookieName])) {
            return false;
        }

        $sessionId = $_COOKIE[$this->cookieName];
        $session = $this->sessionRepository->get($sessionId);

        // Ak session neexistuje alebo neobsahuje CSRF token, token nie je platný
        if (!$session || !isset($session['data'][$this->tokenName])) {
            return false;
        }

        $tokenData = $session['data'][$this->tokenName];

        // Kontrola expirácie tokenu
        if (time() > $tokenData['expires']) {
            return false;
        }

        // Kontrola tokenu
        return hash_equals($tokenData['token'], $token);
    }
}
