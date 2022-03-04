<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\Data\Paginator\OffsetPaginator $paginator;
 * @var \Yiisoft\Translator\TranslatorInterface $translator
 * @var \App\Blog\Domain\Tag $item
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\View\WebView $this
 */

use App\Presentation\Infrastructure\Web\Widget\OffsetPagination;
use Yiisoft\Html\Html;

$this->setTitle($item->getLabel());

$pagination = OffsetPagination::widget()
                              ->paginator($paginator)
                              ->urlGenerator(fn ($page) => $url->generate(
                                  'blog/tag',
                                  ['label' => $item->getLabel(), 'page' => $page]
                              ));
echo Html::tag('h1', Html::encode($item->getLabel()));
echo Html::openTag('ul');
/** @var \App\Blog\Domain\Post $post */
foreach ($paginator->read() as $post) {
    echo Html::openTag('li', ['class' => 'text-muted']);
    echo Html::a(Html::encode($post->getTitle()), $url->generate('blog/post', ['slug' => $post->getSlug()]));
    echo ' by ';
    $userLogin = $post->getAuthor()->getName();
    echo Html::a(Html::encode($userLogin), $url->generate('user/profile', ['login' => $userLogin]));
    echo ' at ';
    echo Html::span($post->getCreatedAt()->format('H:i d.m.Y'));
    echo Html::closeTag('li');
}
echo Html::closeTag('ul');

if ($pagination->isRequired()) {
    echo $pagination;
}
