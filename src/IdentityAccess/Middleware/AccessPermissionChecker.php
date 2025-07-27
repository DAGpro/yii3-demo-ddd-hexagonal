<?php

declare(strict_types=1);

namespace App\IdentityAccess\Middleware;

use App\IdentityAccess\AuthService\AuthenticationService;
use App\IdentityAccess\AuthService\AuthorizationService;
use InvalidArgumentException;
use Override;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Yiisoft\Http\Status;

final class AccessPermissionChecker implements MiddlewareInterface
{
    private ?string $permission = null;

    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly AuthenticationService $authenticationService,
        private readonly AuthorizationService $authorizationService,
    ) {
    }

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->authenticationService->getUser();
        if ($user === null) {
            throw new RuntimeException('Log in to the site');
        }

        if ($this->permission === null) {
            throw new InvalidArgumentException('Permission not set.');
        }

        if (!$this->authorizationService->userHasPermission((string)$user->getId(), $this->permission)) {
            return $this->responseFactory->createResponse(Status::FORBIDDEN);
        }

        return $handler->handle($request);
    }

    public function withPermission(string $permission): self
    {
        $new = clone $this;
        $new->permission = $permission;
        return $new;
    }
}
