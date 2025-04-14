<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

/**
 * Abstraktná základná trieda pre kontroléry
 */
abstract class AbstractController
{
    protected Twig $twig;

    /**
     * Konštruktor
     *
     * @param Twig $twig
     */
    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Dekóduje JSON kategórie pre každý článok v zozname
     *
     * @param array $articles Zoznam článkov
     * @return array Zoznam článkov s dekódovanými kategóriami
     */
    protected function decodeCategories(array $articles): array
    {
        foreach ($articles as &$article) {
            if (isset($article['categories'])) {
                $article['categories'] = json_decode($article['categories'], true) ?: [];
            } else {
                $article['categories'] = [];
            }
        }
        
        return $articles;
    }

    /**
     * Renderuje šablónu s danými dátami
     *
     * @param Response $response
     * @param string $template Cesta k šablóne
     * @param array $data Dáta pre šablónu
     * @return Response
     */
    protected function render(Response $response, string $template, array $data = []): Response
    {
        return $this->twig->render($response, $template, $data);
    }
}
