<?php

declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use App\Domain\ValueObject\Uuid;

/**
 * Middleware pre validáciu UUID v požiadavkách
 */
class UuidValidatorMiddleware implements MiddlewareInterface
{
    /**
     * Spracuje požiadavku a validuje UUID parametre
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $route = $request->getAttribute('route');

        if ($route) {
            // Validácia ID parametra
            $uuid = $route->getArgument('id');
            if ($uuid && Uuid::fromString($uuid) === null) {
                $response = new Response();
                $response->getBody()->write(json_encode([
                    'error' => 'Invalid UUID format',
                    'parameter' => 'id',
                    'value' => $uuid
                ]));

                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
            }

            // Validácia author_id parametra v tele požiadavky
            $params = $request->getParsedBody();
            if (is_array($params) && isset($params['author_id']) && Uuid::fromString($params['author_id']) === null) {
                $response = new Response();
                $response->getBody()->write(json_encode([
                    'error' => 'Invalid UUID format',
                    'parameter' => 'author_id',
                    'value' => $params['author_id']
                ]));

                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
            }
        }

        return $handler->handle($request);
    }
}
