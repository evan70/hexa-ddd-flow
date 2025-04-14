<?php

declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Application\Service\CsrfService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Factory\ResponseFactory;

class CsrfMiddleware implements MiddlewareInterface
{
    private CsrfService $csrfService;
    private array $excludedRoutes;

    /**
     * Konštruktor
     *
     * @param CsrfService $csrfService
     * @param array $excludedRoutes Cesty, ktoré sú vylúčené z CSRF ochrany
     */
    public function __construct(
        CsrfService $csrfService,
        array $excludedRoutes = []
    ) {
        $this->csrfService = $csrfService;
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
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        
        // Kontrola, či je cesta vylúčená z CSRF ochrany
        foreach ($this->excludedRoutes as $excludedRoute) {
            if (strpos($path, $excludedRoute) === 0) {
                return $handler->handle($request);
            }
        }
        
        // Kontrola CSRF tokenu len pre POST, PUT, DELETE a PATCH požiadavky
        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $parsedBody = $request->getParsedBody();
            
            // Kontrola, či je token v požiadavke
            if (!isset($parsedBody['csrf_token'])) {
                return $this->createErrorResponse('CSRF token chýba.');
            }
            
            $token = $parsedBody['csrf_token'];
            
            // Kontrola platnosti tokenu
            if (!$this->csrfService->validate($token)) {
                return $this->createErrorResponse('Neplatný CSRF token.');
            }
        }
        
        return $handler->handle($request);
    }
    
    /**
     * Vytvorí chybovú odpoveď
     *
     * @param string $message Chybová správa
     * @return Response
     */
    private function createErrorResponse(string $message): Response
    {
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse(403);
        $response->getBody()->write(json_encode(['error' => $message]));
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}
