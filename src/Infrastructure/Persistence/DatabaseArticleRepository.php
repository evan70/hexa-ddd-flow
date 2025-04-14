<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\ArticleType;
use App\Domain\ArticleFactory;
use App\Domain\ValueObject\Uuid;
use App\Ports\ArticleRepositoryInterface;
use PDO;

class DatabaseArticleRepository implements ArticleRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): array
    {
        $statement = $this->pdo->query('SELECT * FROM articles');
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(string|Uuid $id): ?array
    {
        // Konverzia Uuid objektu na string
        if ($id instanceof Uuid) {
            $id = $id->getValue();
        }

        $statement = $this->pdo->prepare('SELECT * FROM articles WHERE id = :id');
        $statement->execute(['id' => $id]);

        $article = $statement->fetch(PDO::FETCH_ASSOC);

        return $article ?: null;
    }

    public function findByType(string $type): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM articles WHERE type = :type');
        $statement->execute(['type' => $type]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByAuthorId(string|Uuid $authorId): array
    {
        // Konverzia Uuid objektu na string
        if ($authorId instanceof Uuid) {
            $authorId = $authorId->getValue();
        }

        $statement = $this->pdo->prepare('SELECT * FROM articles WHERE author_id = :author_id');
        $statement->execute(['author_id' => $authorId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function save(array $articleData): string
    {
        try {
            // Použitie factory na validáciu a doplnenie dát
            $articleData = ArticleFactory::createFromArray($articleData);

            if (isset($articleData['id']) && $this->findById($articleData['id'])) {
                // Update existing article
                $sql = 'UPDATE articles SET 
                        title = :title, 
                        content = :content, 
                        type = :type, 
                        author_id = :author_id, 
                        updated_at = :updated_at 
                        WHERE id = :id';

                $existingArticle = $this->findById($articleData['id']);

                $statement = $this->pdo->prepare($sql);
                $statement->execute([
                    'id' => $articleData['id'],
                    'title' => $articleData['title'] ?? $existingArticle['title'],
                    'content' => $articleData['content'] ?? $existingArticle['content'],
                    'type' => $articleData['type'] ?? $existingArticle['type'],
                    'author_id' => $articleData['author_id'] ?? $existingArticle['author_id'],
                    'updated_at' => $articleData['updated_at']
                ]);

                return $articleData['id'];
            } else {
                // Insert new article
                $sql = 'INSERT INTO articles (id, title, content, type, author_id, created_at, updated_at) 
                        VALUES (:id, :title, :content, :type, :author_id, :created_at, :updated_at)';

                $statement = $this->pdo->prepare($sql);
                $statement->execute([
                    'id' => $articleData['id'],
                    'title' => $articleData['title'],
                    'content' => $articleData['content'],
                    'type' => $articleData['type'],
                    'author_id' => $articleData['author_id'],
                    'created_at' => $articleData['created_at'],
                    'updated_at' => $articleData['updated_at']
                ]);

                return $articleData['id'];
            }
        } catch (\InvalidArgumentException $e) {
            // Zachytenie chyby z factory
            throw new \RuntimeException('Neplatné dáta článku: ' . $e->getMessage());
        }
    }

    public function delete(string|Uuid $id): bool
    {
        // Konverzia Uuid objektu na string
        if ($id instanceof Uuid) {
            $id = $id->getValue();
        }

        $statement = $this->pdo->prepare('DELETE FROM articles WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }
}
