<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Slice\Auth;

use App\IdentityAccess\ContextMap\AuthService\AuthenticationService;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use App\IdentityAccess\User\Slice\Auth\Form\SignupForm;
use App\IdentityAccess\User\Slice\User\UserServiceInterface;
use App\Infrastructure\Presentation\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\FormModel\FormHydrator;
use Yiisoft\Http\Method;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final readonly class SignupController
{
    private ViewRenderer $viewRenderer;

    public function __construct(
        ViewRenderer $viewRenderer,
        private WebControllerService $webService,
    ) {
        $this->viewRenderer = $viewRenderer->withViewPath(__DIR__ . '/view/signup');
    }

    public function signup(
        AuthenticationService $authenticationService,
        UserServiceInterface $userService,
        ServerRequestInterface $request,
        TranslatorInterface $translator,
        SignupForm $signupForm,
        FormHydrator $formHydrator,
    ): ResponseInterface {
        if (!$authenticationService->isGuest()) {
            return $this->webService->redirect('site/index');
        }

        if (
            $request->getMethod() === Method::POST
            && $formHydrator->populateFromPostAndValidate($signupForm, $request)
        ) {
            try {
                $userService->createUser($signupForm->login, $signupForm->password);
                return $this->webService->sessionFlashAndRedirect(
                    $translator->translate('IdentityAccess.user.registered'),
                    'site/index',
                );
            } catch (IdentityException $exception) {
                $signupForm
                    ->getValidationResult()
                    ->addError(
                        $exception->getMessage(),
                        valuePath: ['password'],
                    );
            }
        }

        return $this->viewRenderer->render('signup', ['formModel' => $signupForm]);
    }
}
