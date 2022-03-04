<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Web\User;

use App\IdentityAccess\Presentation\Backend\Web\User\Forms\CreateUserForm;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Application\Service\UserServiceInterface;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use App\Presentation\Infrastructure\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Yiisoft\Http\Method;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\View\ViewRenderer;

final class CreateUserController
{
    private ViewRenderer $viewRenderer;
    private WebControllerService $webService;
    private UserServiceInterface $userService;

    public function __construct(
        ViewRenderer $viewRenderer,
        WebControllerService $webService,
        UserServiceInterface $userService
    ) {
        $viewRenderer = $viewRenderer->withLayout('@backendLayout/main');
        $viewRenderer = $viewRenderer->withViewPath('@identityBackendView/user');
        $this->viewRenderer = $viewRenderer->withControllerName('create-user');
        $this->webService = $webService;
        $this->userService = $userService;
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
