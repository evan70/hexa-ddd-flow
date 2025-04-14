<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Service;

use App\Application\Service\AuthService;
use App\Ports\SessionRepositoryInterface;
use App\Ports\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class AuthServiceTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private SessionRepositoryInterface $sessionRepository;
    private AuthService $authService;
    
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->sessionRepository = $this->createMock(SessionRepositoryInterface::class);
        
        $this->authService = new AuthService(
            $this->userRepository,
            $this->sessionRepository,
            'test_session_id',
            3600
        );
    }
    
    /**
     * Test, či metóda getCurrentUser vracia null, ak nie je nastavená cookie
     */
    public function testGetCurrentUserReturnsNullWhenNoCookie(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        
        $request->expects($this->once())
            ->method('getCookieParams')
            ->willReturn([]);
        
        $result = $this->authService->getCurrentUser($request);
        
        $this->assertNull($result);
    }
    
    /**
     * Test, či metóda getCurrentUser vracia null, ak session neexistuje
     */
    public function testGetCurrentUserReturnsNullWhenSessionNotFound(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        
        $request->expects($this->once())
            ->method('getCookieParams')
            ->willReturn(['test_session_id' => 'session123']);
        
        $this->sessionRepository->expects($this->once())
            ->method('get')
            ->with('session123')
            ->willReturn(null);
        
        $result = $this->authService->getCurrentUser($request);
        
        $this->assertNull($result);
    }
    
    /**
     * Test, či metóda getCurrentUser vracia používateľa, ak session existuje
     */
    public function testGetCurrentUserReturnsUserWhenSessionExists(): void
    {
        $user = [
            'id' => '1',
            'username' => 'user1',
            'email' => 'user1@example.com',
            'role' => 'admin'
        ];
        
        $session = [
            'id' => 'session123',
            'user_id' => '1',
            'data' => ['user' => $user],
            'created_at' => '2023-01-01 00:00:00',
            'expires_at' => '2023-01-02 00:00:00'
        ];
        
        $request = $this->createMock(ServerRequestInterface::class);
        
        $request->expects($this->once())
            ->method('getCookieParams')
            ->willReturn(['test_session_id' => 'session123']);
        
        $this->sessionRepository->expects($this->once())
            ->method('get')
            ->with('session123')
            ->willReturn($session);
        
        $result = $this->authService->getCurrentUser($request);
        
        $this->assertEquals($user, $result);
    }
    
    /**
     * Test, či metóda isLoggedIn vracia true, ak je používateľ prihlásený
     */
    public function testIsLoggedInReturnsTrueWhenUserIsLoggedIn(): void
    {
        $user = [
            'id' => '1',
            'username' => 'user1',
            'email' => 'user1@example.com',
            'role' => 'admin'
        ];
        
        $request = $this->createMock(ServerRequestInterface::class);
        
        // Mock AuthService::getCurrentUser
        $authService = $this->getMockBuilder(AuthService::class)
            ->setConstructorArgs([
                $this->userRepository,
                $this->sessionRepository,
                'test_session_id',
                3600
            ])
            ->onlyMethods(['getCurrentUser'])
            ->getMock();
        
        $authService->expects($this->once())
            ->method('getCurrentUser')
            ->with($request)
            ->willReturn($user);
        
        $result = $authService->isLoggedIn($request);
        
        $this->assertTrue($result);
    }
    
    /**
     * Test, či metóda isLoggedIn vracia false, ak používateľ nie je prihlásený
     */
    public function testIsLoggedInReturnsFalseWhenUserIsNotLoggedIn(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        
        // Mock AuthService::getCurrentUser
        $authService = $this->getMockBuilder(AuthService::class)
            ->setConstructorArgs([
                $this->userRepository,
                $this->sessionRepository,
                'test_session_id',
                3600
            ])
            ->onlyMethods(['getCurrentUser'])
            ->getMock();
        
        $authService->expects($this->once())
            ->method('getCurrentUser')
            ->with($request)
            ->willReturn(null);
        
        $result = $authService->isLoggedIn($request);
        
        $this->assertFalse($result);
    }
    
    /**
     * Test, či metóda hasRole vracia true, ak má používateľ požadovanú rolu
     */
    public function testHasRoleReturnsTrueWhenUserHasRole(): void
    {
        $user = [
            'id' => '1',
            'username' => 'user1',
            'email' => 'user1@example.com',
            'role' => 'admin'
        ];
        
        $request = $this->createMock(ServerRequestInterface::class);
        
        // Mock AuthService::getCurrentUser
        $authService = $this->getMockBuilder(AuthService::class)
            ->setConstructorArgs([
                $this->userRepository,
                $this->sessionRepository,
                'test_session_id',
                3600
            ])
            ->onlyMethods(['getCurrentUser'])
            ->getMock();
        
        $authService->expects($this->once())
            ->method('getCurrentUser')
            ->with($request)
            ->willReturn($user);
        
        $result = $authService->hasRole($request, 'admin');
        
        $this->assertTrue($result);
    }
    
    /**
     * Test, či metóda hasRole vracia false, ak používateľ nemá požadovanú rolu
     */
    public function testHasRoleReturnsFalseWhenUserDoesNotHaveRole(): void
    {
        $user = [
            'id' => '1',
            'username' => 'user1',
            'email' => 'user1@example.com',
            'role' => 'editor'
        ];
        
        $request = $this->createMock(ServerRequestInterface::class);
        
        // Mock AuthService::getCurrentUser
        $authService = $this->getMockBuilder(AuthService::class)
            ->setConstructorArgs([
                $this->userRepository,
                $this->sessionRepository,
                'test_session_id',
                3600
            ])
            ->onlyMethods(['getCurrentUser'])
            ->getMock();
        
        $authService->expects($this->once())
            ->method('getCurrentUser')
            ->with($request)
            ->willReturn($user);
        
        $result = $authService->hasRole($request, 'admin');
        
        $this->assertFalse($result);
    }
}
