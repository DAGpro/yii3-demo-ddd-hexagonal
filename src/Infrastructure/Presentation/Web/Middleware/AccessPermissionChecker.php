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

final class AccessPermissionChecker implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;
    private AuthorizationService $authorizationService;
    private AuthenticationService $authenticationService;
    private ?string $permission = null;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService,
    ) {
        $this->responseFactory = $responseFactory;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->authenticationService->getUser();
        if ($user === null) {
            throw new \Exception('Log in to the site');
        }

        if ($this->permission === null) {
            throw new \InvalidArgumentException('Permission not set.');
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
