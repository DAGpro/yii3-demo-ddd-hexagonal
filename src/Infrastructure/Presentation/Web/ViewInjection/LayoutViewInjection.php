<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Web\ViewInjection;

use App\IdentityAccess\ContextMap\AuthService\AuthenticationService;
use App\IdentityAccess\ContextMap\AuthService\AuthorizationService;
use Override;
use Yiisoft\Yii\View\Renderer\LayoutParametersInjectionInterface;

final readonly class LayoutViewInjection implements LayoutParametersInjectionInterface
{
    public function __construct(
        private AuthenticationService $authenticationService,
        private AuthorizationService $authorizationService,
    ) {}

    #[Override]
    public function getLayoutParameters(): array
    {
        $user = $this->authenticationService->getUser();
        return [
            'brandLabel' => 'Yii Demo',
            'user' => $user,
            'canAddPost' => $user !== null && $this->authorizationService->userHasRole(
                (string) $user->getId(),
                'author',
            ),
        ];
    }
}
