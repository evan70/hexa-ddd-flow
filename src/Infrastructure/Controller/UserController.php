<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Application\Service\UserService;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;

/**
 * Controller pre používateľov
 */
class UserController extends AbstractController
{
    private UserService $userService;

    /**
     * Konštruktor
     *
     * @param UserService $userService
     * @param Twig $twig
     */
    public function __construct(
        UserService $userService,
        Twig $twig
    ) {
        parent::__construct($twig);
        $this->userService = $userService;
    }

    /**
     * Zobrazí všetkých používateľov
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function index(Request $request, Response $response): Response
    {
        $users = $this->userService->getAllUsers();

        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Zobrazí používateľa podľa ID
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];

        // UUID validácia je vykonávaná v middleware
        $user = $this->userService->getUserById($id, $request);

        $response->getBody()->write(json_encode($user));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Zobrazí HTML zoznam používateľov
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function viewList(Request $request, Response $response): Response
    {
        $users = $this->userService->getAllUsers();

        return $this->render($response, 'users/list.twig', [
            'users' => $users
        ]);
    }
}
