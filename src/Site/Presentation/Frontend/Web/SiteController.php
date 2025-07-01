<?php

declare(strict_types=1);

namespace App\Site\Presentation\Frontend\Web;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final readonly class SiteController
{
    private ViewRenderer $viewRenderer;

    public function __construct(ViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer
            ->withViewPath('@view')
            ->withControllerName('site');
    }

    public function index(): ResponseInterface
    {
        return $this->viewRenderer->render('index');
    }
}
