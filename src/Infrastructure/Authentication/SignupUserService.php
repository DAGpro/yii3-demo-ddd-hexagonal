<?php

namespace App\Infrastructure\Authentication;

use App\Core\Component\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\Core\Component\IdentityAccess\User\Application\Service\UserServiceInterface;
use Spiral\Database\Database;
use Spiral\Database\DatabaseManager;
use Throwable;
use Yiisoft\Auth\IdentityRepositoryInterface;

class SignupUserService
{
    private UserQueryServiceInterface $userQueryService;
    private UserServiceInterface $userService;
    private IdentityRepositoryInterface $identityRepository;
    private Database $db;

    public function __construct(
        DatabaseManager $databaseManager,
        UserQueryServiceInterface $userQueryService,
        IdentityRepositoryInterface $identityRepository,
        UserServiceInterface $userService,
    ) {
        $this->userQueryService = $userQueryService;
        $this->userService = $userService;
        $this->identityRepository = $identityRepository;
        $this->db = $databaseManager->database();
    }
    /**
     * @throws Throwable
     */
    public function signup(string $login, string $password): void
    {
        try {
            $this->db->begin();
            $this->userService->createUser($login, $password);
            $user = $this->userQueryService->findByLogin($login);
            $identity = new Identity($user);
            $this->identityRepository->save($identity);
            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollback();
            throw new AuthenticationException('Failed to register user, please try again!');
        }
    }
}
