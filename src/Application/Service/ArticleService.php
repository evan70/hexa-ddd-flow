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
     * Vytvorí nový článok
     *
     * @param array $articleData Dáta článku
     * @return string ID vytvoreného článku
     * @throws \InvalidArgumentException Ak sú dáta neplatné
     */
    public function createArticle(array $articleData): string
    {
        // Validácia povinných polí
        $this->validateArticleData($articleData);

        // Konverzia kategórií na JSON, ak existujú
        if (isset($articleData['categories']) && is_array($articleData['categories'])) {
            $articleData['categories'] = json_encode($articleData['categories']);
        }

        // Uloženie článku
        return $this->articleRepository->save($articleData);
    }

    /**
     * Aktualizuje existujúci článok
     *
     * @param string $id ID článku
     * @param array $articleData Dáta článku
     * @param Request $request
     * @return string ID aktualizovaného článku
     * @throws HttpNotFoundException Ak článok neexistuje
     * @throws \InvalidArgumentException Ak sú dáta neplatné
     */
    public function updateArticle(string $id, array $articleData, Request $request): string
    {
        // Kontrola, či článok existuje
        $existingArticle = $this->articleRepository->findById($id);
        if (!$existingArticle) {
            throw new HttpNotFoundException($request, "Article not found");
        }

        // Pridanie ID do dát
        $articleData['id'] = $id;

        // Validácia dát
        $this->validateArticleData($articleData, false);

        // Konverzia kategórií na JSON, ak existujú
        if (isset($articleData['categories']) && is_array($articleData['categories'])) {
            $articleData['categories'] = json_encode($articleData['categories']);
        }

        // Aktualizovanie článku
        return $this->articleRepository->save($articleData);
    }

    /**
     * Vymaže článok
     *
     * @param string $id ID článku
     * @param Request $request
     * @return bool Úspech operácie
     * @throws HttpNotFoundException Ak článok neexistuje
     */
    public function deleteArticle(string $id, Request $request): bool
    {
        // Kontrola, či článok existuje
        $existingArticle = $this->articleRepository->findById($id);
        if (!$existingArticle) {
            throw new HttpNotFoundException($request, "Article not found");
        }

        // Vymazanie článku
        return $this->articleRepository->delete($id);
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

    /**
     * Validuje dáta článku
     *
     * @param array $articleData Dáta článku
     * @param bool $isNew Či ide o nový článok (true) alebo aktualizáciu (false)
     * @throws \InvalidArgumentException Ak sú dáta neplatné
     */
    private function validateArticleData(array $articleData, bool $isNew = true): void
    {
        // Kontrola povinných polí pre nový článok
        if ($isNew) {
            $requiredFields = ['title', 'content', 'type', 'author_id'];
            foreach ($requiredFields as $field) {
                if (!isset($articleData[$field]) || empty($articleData[$field])) {
                    throw new \InvalidArgumentException("Chýba povinné pole: {$field}");
                }
            }
        }

        // Kontrola typu článku, ak je zadaný
        if (isset($articleData['type']) && !Article::isValidType($articleData['type'])) {
            throw new \InvalidArgumentException("Neplatný typ článku: {$articleData['type']}");
        }

        // Kontrola kategórií, ak sú zadané
        if (isset($articleData['categories'])) {
            if (!is_array($articleData['categories']) && !is_string($articleData['categories'])) {
                throw new \InvalidArgumentException("Kategórie musia byť pole alebo JSON reťazec");
            }

            // Ak je to reťazec, skúsime ho dekódovať ako JSON
            if (is_string($articleData['categories'])) {
                $decoded = json_decode($articleData['categories'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \InvalidArgumentException("Neplatný JSON formát pre kategórie");
                }
            }
        }
    }
}
