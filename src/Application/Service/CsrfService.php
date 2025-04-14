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
        error_log('Overujem token: ' . $token);
        $cookies = $request->getCookieParams();

        // Ak nie je nastavená cookie, token nie je platný
        if (!isset($cookies[$this->cookieName])) {
            error_log('Session cookie nie je nastavená');
            return false;
        }

        $sessionId = $cookies[$this->cookieName];
        error_log('Session ID: ' . $sessionId);
        $session = $this->sessionRepository->get($sessionId);

        // Ak session neexistuje alebo neobsahuje CSRF token, token nie je platný
        if (!$session) {
            error_log('Session neexistuje');
            return false;
        }

        if (!isset($session['data'][$this->tokenName])) {
            error_log('Session neobsahuje CSRF token');
            return false;
        }

        $tokenData = $session['data'][$this->tokenName];
        error_log('Token v session: ' . $tokenData['token']);

        // Kontrola expirácie tokenu
        if (time() > $tokenData['expires']) {
            error_log('Token vypršal');
            return false;
        }

        // Kontrola tokenu
        $result = hash_equals($tokenData['token'], $token);
        error_log('Výsledok porovnania: ' . ($result ? 'true' : 'false'));
        return $result;
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

                // Ak už existuje token a je platný, vrátime ho
                if (isset($sessionData[$this->tokenName]) &&
                    isset($sessionData[$this->tokenName]['token']) &&
                    isset($sessionData[$this->tokenName]['expires']) &&
                    time() < $sessionData[$this->tokenName]['expires']) {

                    error_log('Používam existujúci token: ' . $sessionData[$this->tokenName]['token']);
                    return $sessionData[$this->tokenName]['token'];
                }
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
            $this->sessionRepository->create($sessionId, $sessionData);
        }

        // Generovanie náhodného tokenu
        $token = bin2hex(random_bytes(32));
        error_log('Generujem nový token: ' . $token);

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
        error_log('Overujem token (middleware): ' . $token);

        // Ak nie je nastavená cookie, token nie je platný
        if (!isset($_COOKIE[$this->cookieName])) {
            error_log('Session cookie nie je nastavená (middleware)');
            return false;
        }

        $sessionId = $_COOKIE[$this->cookieName];
        error_log('Session ID (middleware): ' . $sessionId);
        $session = $this->sessionRepository->get($sessionId);

        // Ak session neexistuje alebo neobsahuje CSRF token, token nie je platný
        if (!$session) {
            error_log('Session neexistuje (middleware)');
            return false;
        }

        if (!isset($session['data'][$this->tokenName])) {
            error_log('Session neobsahuje CSRF token (middleware)');
            return false;
        }

        $tokenData = $session['data'][$this->tokenName];
        error_log('Token v session (middleware): ' . $tokenData['token']);

        // Kontrola expirácie tokenu
        if (time() > $tokenData['expires']) {
            error_log('Token vypršal (middleware)');
            return false;
        }

        // Kontrola tokenu
        $result = hash_equals($tokenData['token'], $token);
        error_log('Výsledok porovnania (middleware): ' . ($result ? 'true' : 'false'));
        return $result;
    }

    /**
     * Získa session podľa ID
     *
     * @param string $sessionId ID session
     * @return array|null Session alebo null, ak session neexistuje
     */
    public function getSession(string $sessionId): ?array
    {
        return $this->sessionRepository->get($sessionId);
    }
}
