<?php

declare(strict_types=1);

namespace App\Infrastructure\External;

use App\Domain\ArticleType;
use App\Domain\ValueObject\Uuid;
use App\Ports\ArticleRepositoryInterface;

/**
 * EXAMPLE CODE - This is an example implementation of ArticleRepositoryInterface
 * that uses an external API instead of a database.
 *
 * This class is not used in the application and serves as a demonstration
 * of how the hexagonal architecture allows for easy swapping of adapters.
 */
class ApiArticleRepository implements ArticleRepositoryInterface
{
    private string $apiBaseUrl;
    private string $apiKey;

    /**
     * Constructor
     *
     * @param string $apiBaseUrl Base URL of the API
     * @param string $apiKey API key for authentication
     */
    public function __construct(string $apiBaseUrl, string $apiKey)
    {
        $this->apiBaseUrl = $apiBaseUrl;
        $this->apiKey = $apiKey;
    }

    /**
     * Find all articles
     *
     * @return array List of all articles
     */
    public function findAll(): array
    {
        $response = $this->makeApiRequest('GET', '/articles');

        if (isset($response['error']) || empty($response)) {
            return [];
        }

        return $response;
    }

    /**
     * Find an article by ID
     *
     * @param string|Uuid $id
     * @return array|null Article data or null if not found
     */
    public function findById(string|Uuid $id): ?array
    {
        // Convert Uuid object to string if needed
        if ($id instanceof Uuid) {
            $id = $id->getValue();
        }

        $response = $this->makeApiRequest('GET', "/articles/{$id}");

        if (isset($response['error']) || empty($response)) {
            return null;
        }

        return $response;
    }

    /**
     * Find articles by type
     *
     * @param string $type
     * @return array List of articles with the specified type
     */
    public function findByType(string $type): array
    {
        $response = $this->makeApiRequest('GET', '/articles', [
            'type' => $type
        ]);

        if (isset($response['error']) || empty($response)) {
            return [];
        }

        return $response;
    }

    /**
     * Find articles by author ID
     *
     * @param string|Uuid $authorId
     * @return array List of articles by the specified author
     */
    public function findByAuthorId(string|Uuid $authorId): array
    {
        // Convert Uuid object to string if needed
        if ($authorId instanceof Uuid) {
            $authorId = $authorId->getValue();
        }

        $response = $this->makeApiRequest('GET', '/articles', [
            'author_id' => $authorId
        ]);

        if (isset($response['error']) || empty($response)) {
            return [];
        }

        return $response;
    }

    /**
     * Save article data
     *
     * @param array $articleData
     * @return string ID of the saved article
     */
    public function save(array $articleData): string
    {
        if (isset($articleData['id'])) {
            // Update existing article
            $response = $this->makeApiRequest('PUT', "/articles/{$articleData['id']}", $articleData);

            if (isset($response['error'])) {
                throw new \RuntimeException('Failed to update article: ' . $response['error']);
            }

            return $articleData['id'];
        } else {
            // Create new article
            $response = $this->makeApiRequest('POST', '/articles', $articleData);

            if (isset($response['error'])) {
                throw new \RuntimeException('Failed to create article: ' . $response['error']);
            }

            return $response['id'];
        }
    }

    /**
     * Delete an article
     *
     * @param string|Uuid $id
     * @return bool Success status
     */
    public function delete(string|Uuid $id): bool
    {
        // Convert Uuid object to string if needed
        if ($id instanceof Uuid) {
            $id = $id->getValue();
        }

        $response = $this->makeApiRequest('DELETE', "/articles/{$id}");

        return !isset($response['error']);
    }

    /**
     * Find articles by category
     *
     * @param string $category
     * @return array List of articles with the specified category
     */
    public function findByCategory(string $category): array
    {
        $response = $this->makeApiRequest('GET', '/articles', [
            'category' => $category
        ]);

        if (isset($response['error']) || empty($response)) {
            return [];
        }

        return $response;
    }

    /**
     * Find articles by tag
     *
     * @param string $tag
     * @return array List of articles with the specified tag
     */
    public function findByTag(string $tag): array
    {
        $response = $this->makeApiRequest('GET', '/articles', [
            'tag' => $tag
        ]);

        if (isset($response['error']) || empty($response)) {
            return [];
        }

        return $response;
    }

    /**
     * Get all unique categories from articles
     *
     * @return array List of all categories
     */
    public function getAllCategories(): array
    {
        $response = $this->makeApiRequest('GET', '/categories');

        if (isset($response['error']) || empty($response)) {
            return [];
        }

        return $response;
    }

    /**
     * Get all unique tags from articles
     *
     * @return array List of all tags
     */
    public function getAllTags(): array
    {
        $response = $this->makeApiRequest('GET', '/tags');

        if (isset($response['error']) || empty($response)) {
            return [];
        }

        return $response;
    }

    /**
     * Make an API request
     *
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return array Response data
     */
    private function makeApiRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->apiBaseUrl . $endpoint;

        $options = [
            'http' => [
                'header' => [
                    'Content-Type: application/json',
                    'X-API-Key: ' . $this->apiKey
                ],
                'method' => $method
            ]
        ];

        if (!empty($data) && in_array($method, ['POST', 'PUT'])) {
            $options['http']['content'] = json_encode($data);
        } elseif (!empty($data) && $method === 'GET') {
            $url .= '?' . http_build_query($data);
        }

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            return ['error' => 'API request failed'];
        }

        return json_decode($result, true) ?? [];
    }
}
