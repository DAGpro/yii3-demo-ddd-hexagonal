<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\Span;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

/**
 * @var TranslatorInterface $translator
 * @var UrlGeneratorInterface $url
 * @var CurrentRoute $currentRoute
 * @var WebView $this
 */

$this->setTitle($translator->translate('view-404.not-found'));
?>

<div class="card shadow p-5 my-5 mx-5 bg-white rounded">
    <div class="card-body text-center ">
        <h1 class="card-title display-1 fw-bold">404</h1>
        <p class="card-text">
            <?= $translator->translate('view-404.page.not-found', [
                'url' => Span::tag()
                    ->content(Html::encode($currentRoute->getUri()->getPath()))
                    ->class('text-muted')
                    ->render(),
            ])
            ?>
        </p>
        <p>
            <?= A::tag()
                ->content($translator->translate('view-404.go.home'))
                ->url($url->generate('site/index'))
                ->class('btn btn-outline-primary mt-5')
                ->render();
            ?>
        </p>
    </div>
</div>
