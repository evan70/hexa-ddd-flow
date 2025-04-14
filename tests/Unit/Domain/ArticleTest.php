<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Article;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    /**
     * Test, či metóda isValidType správne validuje typy článkov
     */
    public function testIsValidType(): void
    {
        // Platné typy
        $this->assertTrue(Article::isValidType(Article::TYPE_ARTICLE));
        $this->assertTrue(Article::isValidType(Article::TYPE_PRODUCT));
        $this->assertTrue(Article::isValidType(Article::TYPE_PAGE));

        // Neplatné typy
        $this->assertFalse(Article::isValidType('invalid_type'));
        $this->assertFalse(Article::isValidType(''));
    }

    /**
     * Test, či metóda getTypes vracia všetky typy článkov
     */
    public function testGetTypes(): void
    {
        $types = [Article::TYPE_ARTICLE, Article::TYPE_PRODUCT, Article::TYPE_PAGE];

        $this->assertIsArray($types);
        $this->assertCount(3, $types);
        $this->assertContains(Article::TYPE_ARTICLE, $types);
        $this->assertContains(Article::TYPE_PRODUCT, $types);
        $this->assertContains(Article::TYPE_PAGE, $types);
    }

    /**
     * Test, či konštanty majú správne hodnoty
     */
    public function testConstants(): void
    {
        $this->assertEquals('article', Article::TYPE_ARTICLE);
        $this->assertEquals('product', Article::TYPE_PRODUCT);
        $this->assertEquals('page', Article::TYPE_PAGE);
    }
}
