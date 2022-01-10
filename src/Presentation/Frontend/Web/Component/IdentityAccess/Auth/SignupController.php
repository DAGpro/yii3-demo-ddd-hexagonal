<?php

declare(strict_types=1);

namespace App\Presentation\Frontend\Web\Component\IdentityAccess\Auth;

use App\Core\Component\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\Infrastructure\Authentication\AuthenticationService;
use App\Infrastructure\Authentication\AuthenticationException;
use App\Infrastructure\Authentication\SignupUserService;
use App\Presentation\Frontend\Web\Component\IdentityAccess\Auth\Form\SignupForm;
use App\Presentation\Infrastructure\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Http\Method;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\View\ViewRenderer;

final class SignupController
{
    private ViewRenderer $viewRenderer;
    private WebControllerService $webService;

    public function __construct(ViewRenderer $viewRenderer, WebControllerService $webService)
    {
        $this->viewRenderer = $viewRenderer->withControllerName('component/identity-access/auth/signup');
        $this->webService = $webService;
    }

    public function signup(
        AuthenticationService $authenticationService,
        SignupUserService $signupUserService,
        UserQueryServiceInterface $userQueryService,
        ServerRequestInterface $request,
        TranslatorInterface $translator,
        ValidatorInterface $validator
    ): ResponseInterface {
        if (!$authenticationService->isGuest()) {
            return $this->redirectToMain();
        }

        $body = $request->getParsedBody();

        $signupForm = new SignupForm($userQueryService, $translator);

        if (
            $request->getMethod() === Method::POST
            && $signupForm->load(is_array($body) ? $body : [])
            && $validator->validate($signupForm)->isValid()
        ) {
            try {
                $signupUserService->signup($signupForm->getLogin(), $signupForm->getPassword());
                return $this->redirectToMain();
            } catch (AuthenticationException $exception) {
                $signupForm->getFormErrors()->addError('password', $exception->getMessage());
            }
        }

        return $this->viewRenderer->render('signup', ['formModel' => $signupForm]);
    }

    private function redirectToMain(): ResponseInterface
    {
        return $this->webService->getRedirectResponse('site/index');
    }
}
