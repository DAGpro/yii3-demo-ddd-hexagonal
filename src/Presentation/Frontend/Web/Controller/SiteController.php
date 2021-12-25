<?php

declare(strict_types=1);

namespace App\Presentation\Frontend\Web\Controller;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\Yii\View\ViewRenderer;

final class SiteController
{
    private ViewRenderer $viewRenderer;

    public function __construct(ViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer->withControllerName('controller/site');
    }

    public function index(): ResponseInterface
    {
        return $this->viewRenderer->render('index');
    }
}
