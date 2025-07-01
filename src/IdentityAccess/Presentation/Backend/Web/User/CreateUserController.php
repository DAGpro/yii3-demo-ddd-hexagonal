<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Web\User;

use App\IdentityAccess\Presentation\Backend\Web\User\Forms\CreateUserForm;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Application\Service\UserServiceInterface;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use App\Infrastructure\Presentation\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Yiisoft\Http\Method;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final readonly class CreateUserController
{
    private ViewRenderer $viewRenderer;

    public function __construct(
        ViewRenderer $viewRenderer,
        private WebControllerService $webService,
        private UserServiceInterface $userService
    ) {
        $viewRenderer = $viewRenderer->withLayout('@backendLayout/main');
        $viewRenderer = $viewRenderer->withViewPath('@identityBackendView/user');
        $this->viewRenderer = $viewRenderer->withControllerName('create-user');
    }

    public function create(
        Request $request,
        UserQueryServiceInterface $userQueryService,
        TranslatorInterface $translator,
        LoggerInterface $logger,
        ValidatorInterface $validator
    ): ResponseInterface {
        try {
            $form = new CreateUserForm($userQueryService, $translator);
            if (($request->getMethod() === Method::POST)
                && $form->load($request->getParsedBody())
                && $validator->validate($form)->isValid()
            ) {
                $this->userService->createUser($form->getLogin(), $form->getPassword());

                return $this->webService->redirect('backend/user');
            }

            return $this->viewRenderer->render(
                'create',
                [
                    'form' => $form,
                ]
            );
        } catch (IdentityException $e) {
            $logger->error($e);
            return $this->webService->sessionFlashAndRedirect(
                $e->getMessage(),
                'backend/user',
                [],
                'danger'
            );
        }
    }
}
