<?php

declare(strict_types=1);

namespace App\Presentation\Frontend\Web\Component\IdentityAccess\Auth;

use App\Core\Component\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\Core\Component\IdentityAccess\User\Application\Service\UserServiceInterface;
use App\Core\Component\IdentityAccess\User\Domain\Exception\IdentityException;
use App\Infrastructure\Authentication\AuthenticationService;
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
        UserServiceInterface $userService,
        UserQueryServiceInterface $userQueryService,
        ServerRequestInterface $request,
        TranslatorInterface $translator,
        ValidatorInterface $validator
    ): ResponseInterface {
        if (!$authenticationService->isGuest()) {
            return $this->webService->redirect('site/index');
        }

        $body = $request->getParsedBody();

        $signupForm = new SignupForm($userQueryService, $translator);

        if (
            $request->getMethod() === Method::POST
            && $signupForm->load(is_array($body) ? $body : [])
            && $validator->validate($signupForm)->isValid()
        ) {
            try {
                $userService->createUser($signupForm->getLogin(), $signupForm->getPassword());
                return $this->webService->sessionFlashAndRedirect(
                    $translator->translate('IdentityAccess.user.registered'),
                    'site/index'
                );
            } catch (IdentityException $exception) {
                $signupForm->getFormErrors()->addError('password', $exception->getMessage());
            }
        }

        return $this->viewRenderer->render('signup', ['formModel' => $signupForm]);
    }
}
