<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Application\Service\AuthService;
use App\Application\Service\CsrfService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class AuthController extends AbstractController
{
    private AuthService $authService;
    private CsrfService $csrfService;

    /**
     * Konštruktor
     *
     * @param AuthService $authService
     * @param CsrfService $csrfService
     * @param Twig $twig
     */
    public function __construct(
        AuthService $authService,
        CsrfService $csrfService,
        Twig $twig
    ) {
        parent::__construct($twig);
        $this->authService = $authService;
        $this->csrfService = $csrfService;
    }

    /**
     * Zobrazí prihlasovaciu stránku
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function loginPage(Request $request, Response $response): Response
    {
        // Ak je používateľ už prihlásený, presmerujeme ho na domovskú stránku
        if ($this->authService->isLoggedIn($request)) {
            return $response
                ->withHeader('Location', '/')
                ->withStatus(302);
        }

        return $this->render($response, 'auth/login.twig');
    }

    /**
     * Spracuje prihlásenie
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        // Kontrola, či sú vyplnené všetky povinné polia
        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->render($response, 'auth/login.twig', [
                'error' => 'Prosím, vyplňte email a heslo.'
            ]);
        }

        // Kontrola CSRF tokenu
        if (!isset($data['csrf_token']) || !$this->csrfService->validateToken($request, $data['csrf_token'])) {
            return $this->render($response, 'auth/login.twig', [
                'error' => 'Neplatný bezpečnostný token. Skúste to znova.'
            ]);
        }

        $user = $this->authService->login($data['email'], $data['password']);

        if (!$user) {
            return $this->render($response, 'auth/login.twig', [
                'error' => 'Nesprávny email alebo heslo.'
            ]);
        }

        // Prihlásenie úspešné, presmerujeme používateľa na domovskú stránku
        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }

    /**
     * Spracuje odhlásenie
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function logout(Request $request, Response $response): Response
    {
        $this->authService->logout();

        // Presmerujeme používateľa na prihlasovaciu stránku
        return $response
            ->withHeader('Location', '/login')
            ->withStatus(302);
    }
}
