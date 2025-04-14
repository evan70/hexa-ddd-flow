<?php

declare(strict_types=1);

namespace App\Ports;

use App\Domain\ValueObject\Uuid;

interface ArticleRepositoryInterface
{
    /**
     * Find all articles
     *
     * @return array List of all articles
     */
    public function findAll(): array;

    /**
     * Find an article by ID
     *
     * @param string|Uuid $id
     * @return array|null Article data or null if not found
     */
    public function findById(string|Uuid $id): ?array;

    /**
     * Find articles by type
     *
     * @param string $type
     * @return array List of articles with the specified type
     */
    public function findByType(string $type): array;

    /**
     * Find articles by author ID
     *
     * @param string|Uuid $authorId
     * @return array List of articles by the specified author
     */
    public function findByAuthorId(string|Uuid $authorId): array;

    /**
     * Save article data
     *
     * @param array $articleData
     * @return string ID of the saved article
     */
    public function save(array $articleData): string;

    /**
     * Delete an article
     *
     * @param string|Uuid $id
     * @return bool Success status
     */
    public function delete(string|Uuid $id): bool;
}
