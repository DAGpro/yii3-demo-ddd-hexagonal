<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Web\Component\IdentityAccess\User;

use App\IdentityAccess\User\Application\Service\UserServiceInterface;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use App\Presentation\Infrastructure\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Yiisoft\Http\Method;

class DeleteUserController
{
    private WebControllerService $webService;
    private UserServiceInterface $userService;

    public function __construct(
        WebControllerService $webService,
        UserServiceInterface $userService
    ) {
        $this->webService = $webService;
        $this->userService = $userService;
    }

    public function remove(Request $request, LoggerInterface $logger): ?ResponseInterface
    {
        $body = $request->getParsedBody();

        if (empty($body['user_id']) || $request->getMethod() !== Method::POST) {
            return $this->webService->sessionFlashAndRedirect(
                'The request must be a POST method and have a user_id parameter!',
                'backend/user',
                [],
                'danger'
            );
        }

        try {
            $this->userService->deleteUser((int)$body['user_id']);

            return $this->webService->sessionFlashAndRedirect(
                'User successfully removed',
                'backend/user'
            );
        } catch (IdentityException $e) {
            $logger->error($e);
            return $this->webService->notFound();
        }

    }

    public function clearUsers(Request $request, LoggerInterface $logger): ?ResponseInterface
    {
        if ( $request->getMethod() !== Method::POST) {
            return $this->webService->sessionFlashAndRedirect(
                'The request must be a POST method!',
                'backend/user',
                [],
                'danger'
            );
        }

        try {
            $this->userService->removeAll();

            return $this->webService->sessionFlashAndRedirect(
                'All users successfully removed',
                'backend/user',
            );
        } catch (\Exception $e) {
            $logger->error($e);
            return $this->webService->sessionFlashAndRedirect(
                $e->getMessage(),
                'backend/user',
                [],
                'danger',
            );
        }
    }
}
