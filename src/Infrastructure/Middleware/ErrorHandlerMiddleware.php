<?php

declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;
use Throwable;

class ErrorHandlerMiddleware implements MiddlewareInterface
{
    private Twig $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (HttpNotFoundException $exception) {
            // Spracovanie 404 chyby
            $response = new \Slim\Psr7\Response();
            return $this->twig->render(
                $response->withStatus(404),
                'errors/404.twig',
                [
                    'exception_type' => get_class($exception),
                    'exception_message' => $exception->getMessage(),
                ]
            );
        } catch (Throwable $exception) {
            // Spracovanie ostatných chýb
            $response = new \Slim\Psr7\Response();
            return $this->twig->render(
                $response->withStatus(500),
                'errors/500.twig',
                [
                    'exception_type' => get_class($exception),
                    'exception_message' => $exception->getMessage(),
                ]
            );
        }
    }
}
