<?php

declare(strict_types=1);

namespace App\Infrastructure\Authentication;

use App\Core\Component\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\Core\Component\IdentityAccess\User\Domain\User;
use Throwable;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\User\CurrentUser;

final class AuthenticationService
{
    private CurrentUser $currentUser;
    private UserQueryServiceInterface $userQueryService;
    private IdentityRepositoryInterface $identityRepository;

    public function __construct(
        CurrentUser $currentUser,
        UserQueryServiceInterface $userQueryService,
        IdentityRepositoryInterface $identityRepository,
    ) {
        $this->currentUser = $currentUser;
        $this->userQueryService = $userQueryService;
        $this->identityRepository = $identityRepository;
    }

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
            $this->identityRepository->save($identity);
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
