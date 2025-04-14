<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Service;

use App\Application\Service\UserService;
use App\Ports\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

class UserServiceTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private UserService $userService;
    
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->userService = new UserService($this->userRepository);
    }
    
    /**
     * Test, či metóda getAllUsers volá findAll na repozitári
     */
    public function testGetAllUsers(): void
    {
        $users = [
            ['id' => '1', 'username' => 'user1', 'email' => 'user1@example.com', 'role' => 'admin'],
            ['id' => '2', 'username' => 'user2', 'email' => 'user2@example.com', 'role' => 'editor']
        ];
        
        $this->userRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($users);
        
        $result = $this->userService->getAllUsers();
        
        $this->assertEquals($users, $result);
    }
    
    /**
     * Test, či metóda getUserById vracia používateľa, ak existuje
     */
    public function testGetUserById(): void
    {
        $user = [
            'id' => '1',
            'username' => 'user1',
            'email' => 'user1@example.com',
            'role' => 'admin'
        ];
        
        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with('1')
            ->willReturn($user);
        
        $request = $this->createMock(ServerRequestInterface::class);
        
        $result = $this->userService->getUserById('1', $request);
        
        $this->assertEquals($user, $result);
    }
    
    /**
     * Test, či metóda getUserById vyhodí výnimku, ak používateľ neexistuje
     */
    public function testGetUserByIdThrowsExceptionWhenUserNotFound(): void
    {
        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with('999')
            ->willReturn(null);
        
        $request = $this->createMock(ServerRequestInterface::class);
        
        $this->expectException(HttpNotFoundException::class);
        
        $this->userService->getUserById('999', $request);
    }
    
    /**
     * Test, či metóda getUsersByRole volá findByRole na repozitári
     */
    public function testGetUsersByRole(): void
    {
        $users = [
            ['id' => '1', 'username' => 'user1', 'email' => 'user1@example.com', 'role' => 'admin'],
            ['id' => '3', 'username' => 'user3', 'email' => 'user3@example.com', 'role' => 'admin']
        ];
        
        $this->userRepository->expects($this->once())
            ->method('findByRole')
            ->with('admin')
            ->willReturn($users);
        
        $result = $this->userService->getUsersByRole('admin');
        
        $this->assertEquals($users, $result);
    }
    
    /**
     * Test, či metóda createUser validuje dáta a volá save na repozitári
     */
    public function testCreateUser(): void
    {
        $userData = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'role' => 'editor'
        ];
        
        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($userData)
            ->willReturn('new-user-id');
        
        $result = $this->userService->createUser($userData);
        
        $this->assertEquals('new-user-id', $result);
    }
    
    /**
     * Test, či metóda createUser vyhodí výnimku pri neplatných dátach
     */
    public function testCreateUserThrowsExceptionWithInvalidData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->userService->createUser([
            'username' => 'newuser',
            // Chýba email
            'role' => 'editor'
        ]);
    }
}
