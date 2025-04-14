<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

class UuidTest extends TestCase
{
    /**
     * Test, či konštruktor správne validuje UUID
     */
    public function testConstructorWithValidUuid(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $uuid = new Uuid($uuidString);
        
        $this->assertEquals($uuidString, $uuid->getValue());
    }
    
    /**
     * Test, či konštruktor vyhodí výnimku pri neplatnom UUID
     */
    public function testConstructorWithInvalidUuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        new Uuid('invalid-uuid');
    }
    
    /**
     * Test, či metóda generate vytvára platné UUID
     */
    public function testGenerate(): void
    {
        $uuid = Uuid::generate();
        
        $this->assertInstanceOf(Uuid::class, $uuid);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid->getValue());
    }
    
    /**
     * Test, či metóda __toString vracia hodnotu UUID
     */
    public function testToString(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $uuid = new Uuid($uuidString);
        
        $this->assertEquals($uuidString, (string) $uuid);
    }
    
    /**
     * Test, či metóda equals správne porovnáva UUID
     */
    public function testEquals(): void
    {
        $uuid1 = new Uuid('550e8400-e29b-41d4-a716-446655440000');
        $uuid2 = new Uuid('550e8400-e29b-41d4-a716-446655440000');
        $uuid3 = new Uuid('650e8400-e29b-41d4-a716-446655440000');
        
        $this->assertTrue($uuid1->equals($uuid2));
        $this->assertFalse($uuid1->equals($uuid3));
    }
}
