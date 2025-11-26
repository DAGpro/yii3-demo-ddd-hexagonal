<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Slice\User\Presentation\Backend\Web;

use App\IdentityAccess\User\Domain\Exception\IdentityException;
use App\IdentityAccess\User\Slice\User\UserServiceInterface;
use App\Infrastructure\Presentation\Web\Service\WebControllerService;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Yiisoft\Http\Method;

final readonly class DeleteUserController
{
    public function __construct(
        private WebControllerService $webService,
        private UserServiceInterface $userService,
    ) {}

    public function remove(Request $request, LoggerInterface $logger): ?ResponseInterface
    {
        $body = $request->getParsedBody();

        if (empty($body['user_id']) || $request->getMethod() !== Method::POST) {
            return $this->webService->sessionFlashAndRedirect(
                'The request must be a POST method and have a user_id parameter!',
                'backend/user',
                [],
                'danger',
            );
        }

        try {
            $this->userService->deleteUser((int) $body['user_id']);

            return $this->webService->sessionFlashAndRedirect(
                'User successfully removed',
                'backend/user',
            );
        } catch (IdentityException $e) {
            $logger->error($e);
            return $this->webService->notFound();
        }
    }

    public function clearUsers(Request $request, LoggerInterface $logger): ?ResponseInterface
    {
        if ($request->getMethod() !== Method::POST) {
            return $this->webService->sessionFlashAndRedirect(
                'The request must be a POST method!',
                'backend/user',
                [],
                'danger',
            );
        }

        try {
            $this->userService->removeAll();

            return $this->webService->sessionFlashAndRedirect(
                'All users successfully removed',
                'backend/user',
            );
        } catch (Exception $e) {
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
