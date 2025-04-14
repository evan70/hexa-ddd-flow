<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Ports\UserRepositoryInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;

/**
 * Controller pre používateľov
 */
class UserController
{
    private UserRepositoryInterface $userRepository;
    private Twig $twig;

    /**
     * Konštruktor
     *
     * @param UserRepositoryInterface $userRepository
     * @param Twig $twig
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        Twig $twig
    ) {
        $this->userRepository = $userRepository;
        $this->twig = $twig;
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
        $users = $this->userRepository->findAll();

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
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw new HttpNotFoundException($request, "User not found");
        }

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
        $users = $this->userRepository->findAll();

        return $this->twig->render($response, 'users/list.twig', [
            'users' => $users
        ]);
    }
}
