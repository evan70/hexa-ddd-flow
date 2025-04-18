<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Application\Service\ArticleService;
use App\Domain\Article;
use App\Domain\Article as ArticleType;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;

/**
 * Controller pre články
 */
class ArticleController extends AbstractController
{
    private ArticleService $articleService;

    /**
     * Konštruktor
     *
     * @param ArticleService $articleService
     * @param Twig $twig
     */
    public function __construct(
        ArticleService $articleService,
        Twig $twig
    ) {
        parent::__construct($twig);
        $this->articleService = $articleService;
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
        $articles = $this->articleService->getAllArticles();

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
        $article = $this->articleService->getArticleById($id, $request);

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
        try {
            $type = $args['type'];
            $articles = $this->articleService->getArticlesByType($type, $request);

            $response->getBody()->write(json_encode($articles));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (HttpNotFoundException $e) {
            return $response->withStatus(400);
        }
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
        $requiredFields = ['title', 'content', 'type', 'author_id'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            $response->getBody()->write(json_encode([
                'error' => 'Chýbajú povinné polia: ' . implode(', ', $missingFields)
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (!ArticleType::isValidType($data['type'])) {
            $response->getBody()->write(json_encode([
                'error' => 'Neplatný typ článku: ' . $data['type']
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // UUID validácia je vykonávaná v middleware

        try {
            // Toto môže vyhodiť \InvalidArgumentException alebo \RuntimeException
            $id = $this->articleService->createArticle($data);
        } catch (\InvalidArgumentException $e) {
            // Chyba validácie
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\RuntimeException $e) {
            // Chyba pri ukladaní do databázy
            $response->getBody()->write(json_encode([
                'error' => 'Chyba pri ukladaní článku: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

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

        try {
            $article = $this->articleService->getArticleById($id, $request);
        } catch (HttpNotFoundException $e) {
            throw $e;
        }

        // Validácia typu, ak je poskytnutý
        if (isset($data['type']) && !ArticleType::isValidType($data['type'])) {
            return $response->withStatus(400);
        }

        // UUID validácia je vykonávaná v middleware

        try {
            $id = $this->articleService->updateArticle($id, $data, $request);
        } catch (\InvalidArgumentException $e) {
            return $response->withStatus(400);
        }

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

        try {
            $success = $this->articleService->deleteArticle($id, $request);
        } catch (HttpNotFoundException $e) {
            throw $e;
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
        $articles = $this->articleService->getAllArticles();

        return $this->render($response, 'articles/list.twig', [
            'articles' => $articles,
            'title' => 'Zoznam článkov',
            'type' => null,
            'categories' => $this->articleService->getAllCategories(),
            'tags' => $this->articleService->getAllTags()
        ]);
    }

    /**
     * Zobrazí HTML zoznam článkov podľa typu
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function viewByType(Request $request, Response $response, array $args): Response
    {
        $type = $args['type'] ?? '';

        try {
            $articles = $this->articleService->getArticlesByType($type, $request);

            // Nastavenie nadpisu podľa typu
            $titles = [
                Article::TYPE_ARTICLE => 'Zoznam článkov',
                Article::TYPE_PRODUCT => 'Zoznam produktov',
                Article::TYPE_PAGE => 'Zoznam stránok'
            ];

            $title = $titles[$type] ?? 'Zoznam článkov podľa typu: ' . $type;

            return $this->render($response, 'articles/list.twig', [
                'articles' => $articles,
                'title' => $title,
                'type' => $type,
                'categories' => $this->articleService->getAllCategories(),
                'tags' => $this->articleService->getAllTags()
            ]);
        } catch (HttpNotFoundException $e) {
            throw $e;
        }
    }

    /**
     * Zobrazí HTML detail článku podľa ID
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
        $article = $this->articleService->getArticleById($id, $request);

        return $this->render($response, 'articles/detail.twig', [
            'article' => $article
        ]);
    }

    /**
     * Zobrazí HTML detail článku podľa slugu
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function viewBySlug(Request $request, Response $response, array $args): Response
    {
        $slug = $args['slug'];
        $type = $args['type'];

        // Kontrola, či je typ platný
        if (!Article::isValidType($type)) {
            throw new HttpNotFoundException($request, "Typ článku '{$type}' nebol nájdený");
        }

        // Získanie článku podľa slugu
        $article = $this->articleService->getArticleBySlug($slug, $request);

        // Kontrola, či článok má správny typ
        if ($article['type'] !== $type) {
            throw new HttpNotFoundException($request, "Typ článku '{$type}' nebol nájdený");
        }

        return $this->render($response, 'articles/detail.twig', [
            'article' => $article
        ]);
    }

    /**
     * Zobrazí HTML zoznam článkov podľa kategórie
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function viewByCategory(Request $request, Response $response, array $args): Response
    {
        $category = $args['category'] ?? '';

        $articles = $this->articleService->getArticlesByCategory($category);

        return $this->render($response, 'articles/list.twig', [
            'articles' => $articles,
            'title' => 'Články v kategórii: ' . $category,
            'type' => null,
            'filter_type' => 'category',
            'filter_value' => $category,
            'categories' => $this->articleService->getAllCategories(),
            'tags' => $this->articleService->getAllTags()
        ]);
    }

    /**
     * Zobrazí HTML zoznam článkov podľa tagu
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function viewByTag(Request $request, Response $response, array $args): Response
    {
        $tag = $args['tag'] ?? '';

        $articles = $this->articleService->getArticlesByTag($tag);

        return $this->render($response, 'articles/list.twig', [
            'articles' => $articles,
            'title' => 'Články s tagom: ' . $tag,
            'type' => null,
            'filter_type' => 'tag',
            'filter_value' => $tag,
            'categories' => $this->articleService->getAllCategories(),
            'tags' => $this->articleService->getAllTags()
        ]);
    }
}
