<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Persistence;

use App\Domain\ValueObject\Uuid;
use App\Infrastructure\Persistence\DatabaseUserRepository;
use PDO;
use PHPUnit\Framework\TestCase;

class DatabaseUserRepositoryTest extends TestCase
{
    private PDO $pdo;
    private DatabaseUserRepository $repository;
    
    protected function setUp(): void
    {
        // Vytvorenie in-memory SQLite databázy pre testy
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Vytvorenie tabuľky users
        $this->pdo->exec('
            CREATE TABLE users (
                id CHAR(36) PRIMARY KEY,
                username TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password TEXT,
                role TEXT NOT NULL,
                created_at DATETIME,
                updated_at DATETIME
            )
        ');
        
        // Vytvorenie repozitára
        $this->repository = new DatabaseUserRepository($this->pdo);
        
        // Vloženie testovacích dát
        $this->insertTestData();
    }
    
    /**
     * Vloží testovacie dáta do databázy
     */
    private function insertTestData(): void
    {
        $users = [
            [
                'id' => '550e8400-e29b-41d4-a716-446655440000',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => 'password',
                'role' => 'admin',
                'created_at' => '2023-01-01 00:00:00',
                'updated_at' => '2023-01-01 00:00:00'
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440001',
                'username' => 'editor',
                'email' => 'editor@example.com',
                'password' => 'password',
                'role' => 'editor',
                'created_at' => '2023-01-01 00:00:00',
                'updated_at' => '2023-01-01 00:00:00'
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440002',
                'username' => 'author',
                'email' => 'author@example.com',
                'password' => 'password',
                'role' => 'author',
                'created_at' => '2023-01-01 00:00:00',
                'updated_at' => '2023-01-01 00:00:00'
            ]
        ];
        
        $statement = $this->pdo->prepare('
            INSERT INTO users (id, username, email, password, role, created_at, updated_at)
            VALUES (:id, :username, :email, :password, :role, :created_at, :updated_at)
        ');
        
        foreach ($users as $user) {
            $statement->execute($user);
        }
    }
    
    /**
     * Test, či metóda findAll vracia všetkých používateľov
     */
    public function testFindAll(): void
    {
        $users = $this->repository->findAll();
        
        $this->assertCount(3, $users);
        $this->assertEquals('admin', $users[0]['username']);
        $this->assertEquals('editor', $users[1]['username']);
        $this->assertEquals('author', $users[2]['username']);
    }
    
    /**
     * Test, či metóda findById vracia používateľa podľa ID
     */
    public function testFindById(): void
    {
        $user = $this->repository->findById('550e8400-e29b-41d4-a716-446655440000');
        
        $this->assertNotNull($user);
        $this->assertEquals('admin', $user['username']);
        $this->assertEquals('admin@example.com', $user['email']);
        $this->assertEquals('admin', $user['role']);
    }
    
    /**
     * Test, či metóda findById vracia null, ak používateľ neexistuje
     */
    public function testFindByIdReturnsNullWhenUserNotFound(): void
    {
        $user = $this->repository->findById('non-existent-id');
        
        $this->assertNull($user);
    }
    
    /**
     * Test, či metóda findById akceptuje Uuid objekt
     */
    public function testFindByIdAcceptsUuidObject(): void
    {
        $uuid = new Uuid('550e8400-e29b-41d4-a716-446655440000');
        $user = $this->repository->findById($uuid);
        
        $this->assertNotNull($user);
        $this->assertEquals('admin', $user['username']);
    }
    
    /**
     * Test, či metóda findByRole vracia používateľov podľa role
     */
    public function testFindByRole(): void
    {
        $users = $this->repository->findByRole('admin');
        
        $this->assertCount(1, $users);
        $this->assertEquals('admin', $users[0]['username']);
        
        $users = $this->repository->findByRole('non-existent-role');
        
        $this->assertCount(0, $users);
    }
    
    /**
     * Test, či metóda save ukladá nového používateľa
     */
    public function testSaveCreatesNewUser(): void
    {
        $userData = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'role' => 'subscriber'
        ];
        
        $id = $this->repository->save($userData);
        
        $this->assertNotEmpty($id);
        
        $user = $this->repository->findById($id);
        
        $this->assertNotNull($user);
        $this->assertEquals('newuser', $user['username']);
        $this->assertEquals('newuser@example.com', $user['email']);
        $this->assertEquals('subscriber', $user['role']);
    }
    
    /**
     * Test, či metóda save aktualizuje existujúceho používateľa
     */
    public function testSaveUpdatesExistingUser(): void
    {
        $userData = [
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'username' => 'admin_updated',
            'email' => 'admin_updated@example.com',
            'role' => 'admin'
        ];
        
        $id = $this->repository->save($userData);
        
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $id);
        
        $user = $this->repository->findById($id);
        
        $this->assertNotNull($user);
        $this->assertEquals('admin_updated', $user['username']);
        $this->assertEquals('admin_updated@example.com', $user['email']);
    }
    
    /**
     * Test, či metóda delete vymaže používateľa
     */
    public function testDelete(): void
    {
        $result = $this->repository->delete('550e8400-e29b-41d4-a716-446655440000');
        
        $this->assertTrue($result);
        
        $user = $this->repository->findById('550e8400-e29b-41d4-a716-446655440000');
        
        $this->assertNull($user);
    }
    
    /**
     * Test, či metóda delete vracia false, ak používateľ neexistuje
     */
    public function testDeleteReturnsFalseWhenUserNotFound(): void
    {
        $result = $this->repository->delete('non-existent-id');
        
        $this->assertFalse($result);
    }
    
    /**
     * Test, či metóda delete akceptuje Uuid objekt
     */
    public function testDeleteAcceptsUuidObject(): void
    {
        $uuid = new Uuid('550e8400-e29b-41d4-a716-446655440000');
        $result = $this->repository->delete($uuid);
        
        $this->assertTrue($result);
        
        $user = $this->repository->findById('550e8400-e29b-41d4-a716-446655440000');
        
        $this->assertNull($user);
    }
}
