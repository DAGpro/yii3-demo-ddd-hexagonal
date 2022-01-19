<?php

declare(strict_types=1);

namespace App\Presentation\Frontend\Api\Component\IdentityAccess\User;

use App\Core\Component\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\Core\Component\IdentityAccess\User\Infrastructure\Persistence\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;

final class ApiUserController
{
    private DataResponseFactoryInterface $responseFactory;

    public function __construct(DataResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function index(UserQueryServiceInterface $userQueryService): ResponseInterface
    {
        $dataReader = $userQueryService->findAllPreloaded()
            ->withSort(
                Sort::only(['login'])
                    ->withOrderString('login')
            );
        $users = $dataReader->read();

        $items = [];
        foreach ($users as $user) {
            $items[] = ['login' => $user->getLogin(), 'created_at' => $user->getCreatedAt()->format('H:i:s d.m.Y')];
        }

        return $this->responseFactory->createResponse($items);
    }

    public function profile(UserQueryServiceInterface $userQueryService, CurrentRoute $currentRoute): ResponseInterface
    {
        $login = $currentRoute->getArgument('login');

        /** @var \App\Core\Component\IdentityAccess\User\Domain\User $user */
        $user = $userQueryService->findByLogin($login);
        if ($user === null) {
            return $this->responseFactory->createResponse('Page not found', 404);
        }

        return $this->responseFactory->createResponse(
            ['login' => $user->getLogin(), 'created_at' => $user->getCreatedAt()->format('H:i:s d.m.Y')]
        );
    }
}