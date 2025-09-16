<?php

declare(strict_types=1);

namespace App\IdentityAccess\AuthService;

use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Domain\User;
use App\IdentityAccess\User\Infrastructure\Authentication\AuthenticationException;
use App\IdentityAccess\User\Infrastructure\Authentication\Identity;
use App\IdentityAccess\User\Infrastructure\Authentication\IdentityRepositoryInterface;
use Throwable;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\User\CurrentUser;

final readonly class AuthenticationService
{
    public function __construct(
        private CurrentUser $currentUser,
        private UserQueryServiceInterface $userQueryService,
        private IdentityRepositoryInterface $identityRepository,
    ) {
    }

    /**
     * @throws AuthenticationException
     * @throws Throwable
     */
    public function login(string $login, string $password): IdentityInterface
    {
        $user = $this->userQueryService->findByLogin($login);

        if ($user === null || !$user->validatePassword($password)) {
            throw new AuthenticationException('Login or password incorrect!');
        }

        $identity = $this->identityRepository->findOrCreate($user);

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
            $this->identityRepository->save([$identity]);
        }

        return $this->currentUser->logout();
    }

    public function getUser(): ?User
    {
        $identity = $this->currentUser->getIdentity();
        if ($identity instanceof Identity) {
            return $identity->getUser();
        }
        return null;
    }

    public function isGuest(): bool
    {
        return $this->currentUser->isGuest();
    }
}
