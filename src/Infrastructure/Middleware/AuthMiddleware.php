<?php

declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Application\Service\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

class AuthMiddleware implements MiddlewareInterface
{
    private AuthService $authService;
    private array $roles;
    private string $redirectUrl;
    private Twig $twig;

    /**
     * Konštruktor
     *
     * @param AuthService $authService Služba pre autentifikáciu
     * @param Twig $twig Twig engine pre renderovanie šablón
     * @param array $roles Povolené role (prázdne pole znamená, že stačí byť prihlásený)
     * @param string $redirectUrl URL, na ktorú sa presmeruje neprihlásený používateľ
     */
    public function __construct(
        AuthService $authService,
        Twig $twig,
        array $roles = [],
        string $redirectUrl = '/login'
    ) {
        $this->authService = $authService;
        $this->roles = $roles;
        $this->redirectUrl = $redirectUrl;
        $this->twig = $twig;
    }

    /**
     * Spracuje požiadavku
     *
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Kontrola, či je používateľ prihlásený
        if (!$this->authService->isLoggedIn($request)) {
            // Používateľ nie je prihlásený, presmerujeme ho na prihlasovaciu stránku
            $routeContext = RouteContext::fromRequest($request);
            $routeParser = $routeContext->getRouteParser();

            $redirectUrl = $this->redirectUrl;
            if (strpos($redirectUrl, '/') !== 0) {
                $redirectUrl = $routeParser->urlFor($redirectUrl);
            }

            $response = new \Slim\Psr7\Response();
            return $response
                ->withHeader('Location', $redirectUrl)
                ->withStatus(302);
        }

        // Ak sú definované role, kontrolujeme, či má používateľ požadovanú rolu
        if (!empty($this->roles) && !$this->authService->hasAnyRole($request, $this->roles)) {
            // Používateľ nemá požadovanú rolu
            // V tomto bode už vieme, že používateľ je prihlásený (z podmienky vyššie)
            // Zobrazíme mu peknú chybovú stránku 403
            return $this->twig->render(new \Slim\Psr7\Response(403), 'error/403.twig');
        }

        // Používateľ je prihlásený a má požadovanú rolu, pokračujeme
        return $handler->handle($request);
    }
}
