<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Slice\Auth;

use App\IdentityAccess\ContextMap\AuthService\AuthenticationService;
use App\IdentityAccess\User\Infrastructure\Authentication\AuthenticationException;
use App\IdentityAccess\User\Slice\Auth\Form\LoginForm;
use App\Infrastructure\Presentation\Web\Service\WebControllerService;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Yiisoft\FormModel\FormHydrator;
use Yiisoft\Http\Method;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\User\Login\Cookie\CookieLogin;
use Yiisoft\User\Login\Cookie\CookieLoginIdentityInterface;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final readonly class AuthController
{
    private ViewRenderer $viewRenderer;

    public function __construct(
        ViewRenderer $viewRenderer,
        private AuthenticationService $authService,
        private WebControllerService $webService,
    ) {
        $this->viewRenderer = $viewRenderer->withViewPath(__DIR__ . '/view/auth');
    }

    /**
     * @throws JsonException
     * @throws Throwable
     */
    public function login(
        ServerRequestInterface $request,
        CookieLogin $cookieLogin,
        TranslatorInterface $translator,
        LoginForm $loginForm,
        FormHydrator $formHydrator,
    ): ResponseInterface {
        if (!$this->authService->isGuest()) {
            return $this->redirectToMain();
        }

        if (
            $request->getMethod() === Method::POST
            && $formHydrator->populateFromPostAndValidate($loginForm, $request)
        ) {
            try {
                $identity = $this->authService->login($loginForm->getLogin(), $loginForm->getPassword());
                if ($identity instanceof CookieLoginIdentityInterface && !$loginForm->getPropertyValue('rememberMe')) {
                    return $cookieLogin->addCookie($identity, $this->redirectToMain());
                }

                return $this->redirectToMain();
            } catch (AuthenticationException $exception) {
                $loginForm->getValidationResult()->addError(
                    $translator->translate($exception->getMessage()),
                    valuePath: ['password'],
                );
            }
        }

        return $this->viewRenderer->render('login', ['formModel' => $loginForm]);
    }

    /**
     * @throws Throwable
     */
    public function logout(): ResponseInterface
    {
        $this->authService->logout();

        return $this->redirectToMain();
    }

    private function redirectToMain(): ResponseInterface
    {
        return $this->webService->redirect('site/index');
    }
}
