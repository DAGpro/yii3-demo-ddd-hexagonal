<?php

declare(strict_types=1);

namespace App\Presentation\Infrastructure\Web\ViewInjection;

use App\Infrastructure\Authentication\AuthenticationService;
use Yiisoft\Yii\View\LayoutParametersInjectionInterface;

final class LayoutViewInjection implements LayoutParametersInjectionInterface
{
    private AuthenticationService $authenticationService;

    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    public function getLayoutParameters(): array
    {
        return [
            'brandLabel' => 'Yii Demo',
            'user' => $this->authenticationService->getUser(),
        ];
    }
}
