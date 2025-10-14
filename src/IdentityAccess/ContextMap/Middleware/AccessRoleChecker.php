<?php

declare(strict_types=1);

namespace App\IdentityAccess\ContextMap\Middleware;

use App\IdentityAccess\ContextMap\AuthService\AuthenticationService;
use App\IdentityAccess\ContextMap\AuthService\AuthorizationService;
use InvalidArgumentException;
use Override;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Yiisoft\Http\Status;

final class AccessRoleChecker implements MiddlewareInterface
{
    private ?string $role = null;

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

        if ($this->role === null) {
            throw new InvalidArgumentException('Role not set.');
        }

        if (!$this->authorizationService->userHasRole((string)$user->getId(), $this->role)) {
            return $this->responseFactory->createResponse(Status::FORBIDDEN);
        }

        return $handler->handle($request);
    }

    public function withRole(string $role): self
    {
        $new = clone $this;
        $new->role = $role;
        return $new;
    }
}
