<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Ports\ArticleRepositoryInterface;
use App\Domain\Article;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Služba pre prácu s článkami
 */
class ArticleService
{
    private ArticleRepositoryInterface $articleRepository;

    /**
     * Konštruktor
     *
     * @param ArticleRepositoryInterface $articleRepository
     */
    public function __construct(ArticleRepositoryInterface $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    /**
     * Získa všetky články s dekódovanými kategóriami
     *
     * @return array
     */
    public function getAllArticles(): array
    {
        $articles = $this->articleRepository->findAll();
        return $this->decodeCategories($articles);
    }

    /**
     * Získa článok podľa ID
     *
     * @param string $id
     * @param Request $request
     * @return array
     * @throws HttpNotFoundException
     */
    public function getArticleById(string $id, Request $request): array
    {
        $article = $this->articleRepository->findById($id);

        if (!$article) {
            throw new HttpNotFoundException($request, "Article not found");
        }

        return $this->decodeCategories([$article])[0];
    }

    /**
     * Získa články podľa typu
     *
     * @param string $type
     * @param Request $request
     * @return array
     * @throws HttpNotFoundException
     */
    public function getArticlesByType(string $type, Request $request): array
    {
        if (!Article::isValidType($type)) {
            throw new HttpNotFoundException($request, "Typ článku '{$type}' nebol nájdený");
        }

        $articles = $this->articleRepository->findByType($type);
        return $this->decodeCategories($articles);
    }

    /**
     * Získa články podľa kategórie
     *
     * @param string $category
     * @return array
     */
    public function getArticlesByCategory(string $category): array
    {
        $articles = $this->articleRepository->findByCategory($category);
        return $this->decodeCategories($articles);
    }

    /**
     * Získa články podľa tagu
     *
     * @param string $tag
     * @return array
     */
    public function getArticlesByTag(string $tag): array
    {
        $articles = $this->articleRepository->findByTag($tag);
        return $this->decodeCategories($articles);
    }

    /**
     * Získa všetky kategórie
     *
     * @return array
     */
    public function getAllCategories(): array
    {
        return $this->articleRepository->getAllCategories();
    }

    /**
     * Získa všetky tagy
     *
     * @return array
     */
    public function getAllTags(): array
    {
        return $this->articleRepository->getAllTags();
    }

    /**
     * Dekóduje JSON kategórie pre každý článok v zozname
     *
     * @param array $articles Zoznam článkov
     * @return array Zoznam článkov s dekódovanými kategóriami
     */
    private function decodeCategories(array $articles): array
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
}
