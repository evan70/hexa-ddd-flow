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
     * @return string|null Vygenerovaný token alebo null, ak používateľ nie je prihlásený
     */
    public function generateToken(Request $request): ?string
    {
        $cookies = $request->getCookieParams();
        
        if (!isset($cookies[$this->cookieName])) {
            return null;
        }

        $sessionId = $cookies[$this->cookieName];
        $session = $this->sessionRepository->get($sessionId);

        if (!$session) {
            return null;
        }

        // Generovanie náhodného tokenu
        $token = bin2hex(random_bytes(32));
        
        // Uloženie tokenu do session
        $sessionData = $session['data'];
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
        
        if (!isset($cookies[$this->cookieName])) {
            return false;
        }

        $sessionId = $cookies[$this->cookieName];
        $session = $this->sessionRepository->get($sessionId);

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
     * @return string|null Vygenerovaný token alebo null, ak používateľ nie je prihlásený
     */
    public function generate(): ?string
    {
        if (!isset($_COOKIE[$this->cookieName])) {
            return null;
        }

        $sessionId = $_COOKIE[$this->cookieName];
        $session = $this->sessionRepository->get($sessionId);

        if (!$session) {
            return null;
        }

        // Generovanie náhodného tokenu
        $token = bin2hex(random_bytes(32));
        
        // Uloženie tokenu do session
        $sessionData = $session['data'];
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
        if (!isset($_COOKIE[$this->cookieName])) {
            return false;
        }

        $sessionId = $_COOKIE[$this->cookieName];
        $session = $this->sessionRepository->get($sessionId);

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
