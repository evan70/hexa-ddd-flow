<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * Test, či metóda isValid správne validuje role používateľov
     */
    public function testIsValid(): void
    {
        // Platné role
        $this->assertTrue(User::isValid(User::ADMIN));
        $this->assertTrue(User::isValid(User::EDITOR));
        $this->assertTrue(User::isValid(User::AUTHOR));
        $this->assertTrue(User::isValid(User::SUBSCRIBER));

        // Neplatné role
        $this->assertFalse(User::isValid('invalid_role'));
        $this->assertFalse(User::isValid(''));
    }

    /**
     * Test, či metóda getAll vracia všetky role používateľov
     */
    public function testGetAll(): void
    {
        $roles = User::getAll();

        $this->assertIsArray($roles);
        $this->assertCount(4, $roles);
        $this->assertContains(User::ADMIN, $roles);
        $this->assertContains(User::EDITOR, $roles);
        $this->assertContains(User::AUTHOR, $roles);
        $this->assertContains(User::SUBSCRIBER, $roles);
    }

    /**
     * Test, či konštanty majú správne hodnoty
     */
    public function testConstants(): void
    {
        $this->assertEquals('admin', User::ADMIN);
        $this->assertEquals('editor', User::EDITOR);
        $this->assertEquals('author', User::AUTHOR);
        $this->assertEquals('subscriber', User::SUBSCRIBER);
    }
}
