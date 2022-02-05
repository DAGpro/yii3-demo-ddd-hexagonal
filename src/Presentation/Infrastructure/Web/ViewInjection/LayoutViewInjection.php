<?php

declare(strict_types=1);

namespace App\Presentation\Infrastructure\Web\ViewInjection;

use App\Infrastructure\Authentication\AuthenticationService;
use App\Infrastructure\Authorization\AuthorizationService;
use Yiisoft\Yii\View\LayoutParametersInjectionInterface;

final class LayoutViewInjection implements LayoutParametersInjectionInterface
{
    private AuthenticationService $authenticationService;
    private AuthorizationService $authorizationService;

    public function __construct(AuthenticationService $authenticationService, AuthorizationService $authorizationService)
    {
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
    }

    public function getLayoutParameters(): array
    {
        $user = $this->authenticationService->getUser();
        return [
            'brandLabel' => 'Yii Demo',
            'user' => $user,
            'canAddPost' => $user !== null && $this->authorizationService->userHasRole((string)$user->getId(), 'author'),
        ];
    }
}
