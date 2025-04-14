<?php

declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Csrf\Guard;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Views\Twig;

class SlimCsrfMiddleware implements MiddlewareInterface
{
    private Guard $csrf;
    private Twig $twig;
    private array $excludedRoutes;

    /**
     * Konštruktor
     *
     * @param Guard $csrf
     * @param Twig $twig
     * @param array $excludedRoutes Cesty, ktoré sú vylúčené z CSRF ochrany
     */
    public function __construct(
        Guard $csrf,
        Twig $twig,
        array $excludedRoutes = []
    ) {
        $this->csrf = $csrf;
        $this->twig = $twig;
        $this->excludedRoutes = $excludedRoutes;
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
        $path = $request->getUri()->getPath();

        // Kontrola, či je cesta vylúčená z CSRF ochrany
        foreach ($this->excludedRoutes as $excludedRoute) {
            if (strpos($path, $excludedRoute) === 0) {
                return $handler->handle($request);
            }
        }

        // Pridanie CSRF tokenov do Twig
        $this->twig->getEnvironment()->addGlobal('csrf', [
            'keys' => [
                'name' => $this->csrf->getTokenNameKey(),
                'value' => $this->csrf->getTokenValueKey()
            ],
            'name' => $this->csrf->getTokenName(),
            'value' => $this->csrf->getTokenValue()
        ]);

        try {
            // Spracovanie požiadavky cez CSRF Guard
            return $this->csrf->process($request, $handler);
        } catch (\Exception $e) {
            // V prípade chyby presmerujeme na predchádzajúcu stránku
            $referer = $request->getHeaderLine('HTTP_REFERER') ?: '/';
            $response = new \Slim\Psr7\Response();
            return $response->withHeader('Location', $referer)->withStatus(302);
        }
    }
}
