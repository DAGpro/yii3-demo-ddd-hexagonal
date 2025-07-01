<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Web\Middleware;

use App\Infrastructure\Authentication\AuthenticationService;
use App\Infrastructure\Authorization\AuthorizationService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Http\Status;

final class AccessRoleChecker implements MiddlewareInterface
{
    private ?string $role = null;

    public function __construct(private ResponseFactoryInterface $responseFactory, private AuthenticationService $authenticationService, private AuthorizationService $authorizationService)
    {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->authenticationService->getUser();
        if ($user === null) {
            throw new \Exception('Log in to the site');
        }

        if ($this->role === null) {
            throw new \InvalidArgumentException('Role not set.');
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
