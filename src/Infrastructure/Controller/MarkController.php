<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Application\Service\ArticleService;
use App\Application\Service\UserService;
use App\Application\Service\AuthService;
use App\Application\Service\SettingsService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class MarkController extends AbstractController
{
    private ArticleService $articleService;
    private UserService $userService;
    private AuthService $authService;
    private SettingsService $settingsService;

    /**
     * Konštruktor
     *
     * @param ArticleService $articleService
     * @param UserService $userService
     * @param AuthService $authService
     * @param SettingsService $settingsService
     * @param Twig $twig
     */
    public function __construct(
        ArticleService $articleService,
        UserService $userService,
        AuthService $authService,
        SettingsService $settingsService,
        Twig $twig
    ) {
        parent::__construct($twig);
        $this->articleService = $articleService;
        $this->userService = $userService;
        $this->authService = $authService;
        $this->settingsService = $settingsService;
    }

    /**
     * Zobrazí dashboard
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function dashboard(Request $request, Response $response): Response
    {
        // Získanie aktuálneho používateľa (už vieme, že je prihlásený a má rolu admin vďaka AuthMiddleware)
        $user = $this->authService->getCurrentUser($request);

        $articles = $this->articleService->getAllArticles();
        $users = $this->userService->getAllUsers();

        return $this->render($response, 'mark/dashboard.twig', [
            'articles' => $articles,
            'users' => $users,
            'articlesCount' => count($articles),
            'usersCount' => count($users)
        ]);
    }

    /**
     * Zobrazí zoznam používateľov
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function users(Request $request, Response $response): Response
    {
        $users = $this->userService->getAllUsers();

        return $this->render($response, 'mark/users.twig', [
            'users' => $users
        ]);
    }

    /**
     * Zobrazí detail používateľa
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function userDetail(Request $request, Response $response, array $args): Response
    {
        $user = $this->userService->getUserById($args['id'], $request);

        return $this->render($response, 'mark/user-detail.twig', [
            'user' => $user
        ]);
    }

    /**
     * Zobrazí formulár pre vytvorenie používateľa
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function createUserForm(Request $request, Response $response): Response
    {
        return $this->render($response, 'mark/user-form.twig');
    }

    /**
     * Zobrazí formulár pre úpravu používateľa
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function editUserForm(Request $request, Response $response, array $args): Response
    {
        $user = $this->userService->getUserById($args['id'], $request);

        return $this->render($response, 'mark/user-form.twig', [
            'user' => $user
        ]);
    }

    /**
     * Zobrazí zoznam článkov
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function articles(Request $request, Response $response): Response
    {
        $articles = $this->articleService->getAllArticles();

        return $this->render($response, 'mark/articles.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * Zobrazí detail článku
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function articleDetail(Request $request, Response $response, array $args): Response
    {
        $article = $this->articleService->getArticleById($args['id'], $request);

        return $this->render($response, 'mark/article-detail.twig', [
            'article' => $article
        ]);
    }

    /**
     * Zobrazí formulár pre vytvorenie článku
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function createArticleForm(Request $request, Response $response): Response
    {
        return $this->render($response, 'mark/article-form.twig');
    }

    /**
     * Zobrazí formulár pre úpravu článku
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function editArticleForm(Request $request, Response $response, array $args): Response
    {
        $article = $this->articleService->getArticleById($args['id'], $request);

        return $this->render($response, 'mark/article-form.twig', [
            'article' => $article
        ]);
    }

    /**
     * Zobrazí nastavenia
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function settings(Request $request, Response $response): Response
    {
        // Získanie všetkých nastavení
        $settings = $this->settingsService->getAll();

        // Spracovanie formulára
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            // Uloženie nastavení
            $this->settingsService->setMultiple($data);

            // Presmerovanie späť na nastavenia
            return $response->withHeader('Location', '/mark/settings')->withStatus(302);
        }

        return $this->render($response, 'mark/settings.twig', [
            'settings' => $settings
        ]);
    }
}
