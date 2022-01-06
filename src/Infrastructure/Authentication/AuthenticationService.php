<?php

declare(strict_types=1);

namespace App\Infrastructure\Authentication;

use App\Core\Component\IdentityAccess\User\Application\UserService;
use App\Core\Component\IdentityAccess\User\Domain\User;
use App\Core\Component\IdentityAccess\User\Infrastructure\Persistence\UserRepository;
use Spiral\Database\Database;
use Spiral\Database\DatabaseManager;
use Throwable;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\User\CurrentUser;
use Yiisoft\User\Guest\GuestIdentity;

final class AuthenticationService
{
    private CurrentUser $currentUser;
    private UserRepository $userRepository;
    private UserService $userService;
    private IdentityRepository $identityRepository;
    private Database $db;

    public function __construct(
        CurrentUser $currentUser,
        DatabaseManager $databaseManager,
        UserRepository $userRepository,
        IdentityRepository $identityRepository,
        UserService $userService,
    ) {
        $this->currentUser = $currentUser;
        $this->userRepository = $userRepository;
        $this->userService = $userService;
        $this->identityRepository = $identityRepository;
        $this->db = $databaseManager->database();
    }

    public function login(string $login, string $password): IdentityInterface
    {
        $user = $this->userRepository->findByLogin($login);

        if ($user === null || !$user->validatePassword($password)) {
            throw new AuthenticationException('Login or password incorrect!');
        }

        $identity = $this->identityRepository->findByUserId($user->getId());
        if (!$this->currentUser->login($identity)) {
            throw new AuthenticationException('Login failed, please try again!');
        }

        return $identity;
    }

    /**
     * @throws Throwable
     */
    public function logout(): bool
    {
        $identity = $this->currentUser->getIdentity();

        if ($identity instanceof Identity) {
            $identity->regenerateCookieLoginKey();
            $this->identityRepository->save($identity);
        }

        return $this->currentUser->logout();
    }

    /**
     * @throws Throwable
     */
    public function signup(string $login, string $password): void
    {
        try {
            $this->db->begin();
            $this->userService->createUser($login, $password);
            $user = $this->userRepository->findByLogin($login);
            $identity = new Identity($user);
            $this->identityRepository->save($identity);
            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollback();
            throw new AuthenticationException('Failed to register user, please try again!');
        }
    }

    public function getUser(): ?User
    {
        $identity = $this->currentUser->getIdentity();
        if ($identity instanceof GuestIdentity) {
            return null;
        }
        return $identity->getUser();
    }

    public function isGuest(): bool
    {
        return $this->currentUser->isGuest();
    }
}
