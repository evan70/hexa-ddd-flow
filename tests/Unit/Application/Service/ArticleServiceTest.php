<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Service;

use App\Application\Service\ArticleService;
use App\Ports\ArticleRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

class ArticleServiceTest extends TestCase
{
    private ArticleRepositoryInterface $articleRepository;
    private ArticleService $articleService;
    
    protected function setUp(): void
    {
        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $this->articleService = new ArticleService($this->articleRepository);
    }
    
    /**
     * Test, či metóda getAllArticles volá findAll na repozitári
     */
    public function testGetAllArticles(): void
    {
        $articles = [
            ['id' => '1', 'title' => 'Article 1', 'content' => 'Content 1', 'categories' => '["category1", "category2"]'],
            ['id' => '2', 'title' => 'Article 2', 'content' => 'Content 2', 'categories' => '["category2", "category3"]']
        ];
        
        $this->articleRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($articles);
        
        $result = $this->articleService->getAllArticles();
        
        $this->assertCount(2, $result);
        $this->assertEquals(['category1', 'category2'], $result[0]['categories']);
        $this->assertEquals(['category2', 'category3'], $result[1]['categories']);
    }
    
    /**
     * Test, či metóda getArticleById vracia článok, ak existuje
     */
    public function testGetArticleById(): void
    {
        $article = [
            'id' => '1',
            'title' => 'Article 1',
            'content' => 'Content 1',
            'categories' => '["category1", "category2"]'
        ];
        
        $this->articleRepository->expects($this->once())
            ->method('findById')
            ->with('1')
            ->willReturn($article);
        
        $request = $this->createMock(ServerRequestInterface::class);
        
        $result = $this->articleService->getArticleById('1', $request);
        
        $this->assertEquals('1', $result['id']);
        $this->assertEquals('Article 1', $result['title']);
        $this->assertEquals(['category1', 'category2'], $result['categories']);
    }
    
    /**
     * Test, či metóda getArticleById vyhodí výnimku, ak článok neexistuje
     */
    public function testGetArticleByIdThrowsExceptionWhenArticleNotFound(): void
    {
        $this->articleRepository->expects($this->once())
            ->method('findById')
            ->with('999')
            ->willReturn(null);
        
        $request = $this->createMock(ServerRequestInterface::class);
        
        $this->expectException(HttpNotFoundException::class);
        
        $this->articleService->getArticleById('999', $request);
    }
    
    /**
     * Test, či metóda getAllCategories volá getAllCategories na repozitári
     */
    public function testGetAllCategories(): void
    {
        $categories = ['category1', 'category2', 'category3'];
        
        $this->articleRepository->expects($this->once())
            ->method('getAllCategories')
            ->willReturn($categories);
        
        $result = $this->articleService->getAllCategories();
        
        $this->assertEquals($categories, $result);
    }
    
    /**
     * Test, či metóda getAllTags volá getAllTags na repozitári
     */
    public function testGetAllTags(): void
    {
        $tags = ['tag1', 'tag2', 'tag3'];
        
        $this->articleRepository->expects($this->once())
            ->method('getAllTags')
            ->willReturn($tags);
        
        $result = $this->articleService->getAllTags();
        
        $this->assertEquals($tags, $result);
    }
}
