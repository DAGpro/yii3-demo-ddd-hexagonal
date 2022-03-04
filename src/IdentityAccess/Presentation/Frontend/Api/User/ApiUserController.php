<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Frontend\Api\User;

use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use OpenApi\Annotations as OA;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;

/**
 * @OA\Tag(
 *     name="user",
 *     description="User"
 * )
 */
final class ApiUserController
{
    private DataResponseFactoryInterface $responseFactory;

    public function __construct(DataResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     tags={"user"},
     *     @OA\Response(response="200", description="Get users list")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/user/{login}",
     *     tags={"user"},
     *     @OA\Parameter(
     *     @OA\Schema(type="string"),
     *     in="path",
     *     name="login",
     *     parameter="login"
     *     ),
     *     @OA\Response(response="200", description="Get user info")
     * )
     */
    public function profile(UserQueryServiceInterface $userQueryService, CurrentRoute $currentRoute): ResponseInterface
    {
        $login = $currentRoute->getArgument('login');

        /** @var \App\IdentityAccess\User\Domain\User $user */
        $user = $userQueryService->findByLogin($login);
        if ($user === null) {
            return $this->responseFactory->createResponse('Page not found', 404);
        }

        return $this->responseFactory->createResponse(
            ['login' => $user->getLogin(), 'created_at' => $user->getCreatedAt()->format('H:i:s d.m.Y')]
        );
    }
}
