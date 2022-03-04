<?php

declare(strict_types=1);

namespace App\Site\Presentation\Backend\Web;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\Yii\View\ViewRenderer;

final class SiteController
{
    private ViewRenderer $viewRenderer;

    public function __construct(ViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer
            ->withController($this)
            ->withLayout('@backendLayout/main')
            ->withViewPath('@backendView');
    }

    public function index(): ResponseInterface
    {
        return $this->viewRenderer->render('index');
    }
}
