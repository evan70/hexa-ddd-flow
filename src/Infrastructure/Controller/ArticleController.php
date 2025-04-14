<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Ports\ArticleRepositoryInterface;
use App\Domain\ArticleType;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;

/**
 * Controller pre články
 */
class ArticleController
{
    private ArticleRepositoryInterface $articleRepository;
    private Twig $twig;

    /**
     * Konštruktor
     *
     * @param ArticleRepositoryInterface $articleRepository
     * @param Twig $twig
     */
    public function __construct(
        ArticleRepositoryInterface $articleRepository,
        Twig $twig
    ) {
        $this->articleRepository = $articleRepository;
        $this->twig = $twig;
    }

    /**
     * Zobrazí všetky články
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function index(Request $request, Response $response): Response
    {
        $articles = $this->articleRepository->findAll();

        $response->getBody()->write(json_encode($articles));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Zobrazí článok podľa ID
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];

        // UUID validácia je vykonávaná v middleware
        $article = $this->articleRepository->findById($id);

        if (!$article) {
            throw new HttpNotFoundException($request, "Article not found");
        }

        $response->getBody()->write(json_encode($article));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Zobrazí články podľa typu
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function showByType(Request $request, Response $response, array $args): Response
    {
        $type = $args['type'];

        if (!ArticleType::isValid($type)) {
            return $response->withStatus(400);
        }

        $articles = $this->articleRepository->findByType($type);

        $response->getBody()->write(json_encode($articles));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Vytvorí nový článok
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        // Validácia dát
        if (!isset($data['title']) || !isset($data['content']) || !isset($data['type']) || !isset($data['author_id'])) {
            return $response->withStatus(400);
        }

        if (!ArticleType::isValid($data['type'])) {
            return $response->withStatus(400);
        }

        // UUID validácia je vykonávaná v middleware

        $id = $this->articleRepository->save($data);

        $response->getBody()->write(json_encode(['id' => $id]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    /**
     * Aktualizuje článok
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];

        // UUID validácia je vykonávaná v middleware

        $data = $request->getParsedBody();
        $data['id'] = $id;

        $article = $this->articleRepository->findById($id);

        if (!$article) {
            throw new HttpNotFoundException($request, "Article not found");
        }

        // Validácia typu, ak je poskytnutý
        if (isset($data['type']) && !ArticleType::isValid($data['type'])) {
            return $response->withStatus(400);
        }

        // UUID validácia je vykonávaná v middleware

        $id = $this->articleRepository->save($data);

        $response->getBody()->write(json_encode(['id' => $id]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Vymaže článok
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];

        // UUID validácia je vykonávaná v middleware

        $success = $this->articleRepository->delete($id);

        if (!$success) {
            throw new HttpNotFoundException($request, "Article not found");
        }

        return $response->withStatus(204);
    }

    /**
     * Zobrazí HTML zoznam článkov
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function viewList(Request $request, Response $response): Response
    {
        $articles = $this->articleRepository->findAll();

        return $this->twig->render($response, 'articles/list.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * Zobrazí HTML detail článku
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function viewDetail(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];

        // UUID validácia je vykonávaná v middleware
        $article = $this->articleRepository->findById($id);

        if (!$article) {
            throw new HttpNotFoundException($request, "Article not found");
        }

        return $this->twig->render($response, 'articles/detail.twig', [
            'article' => $article
        ]);
    }
}
