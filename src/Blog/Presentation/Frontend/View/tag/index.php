<?php

declare(strict_types=1);


use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\H1;
use Yiisoft\Html\Tag\Li;
use Yiisoft\Html\Tag\Ul;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;
use Yiisoft\Yii\DataView\Pagination\OffsetPagination;
use Yiisoft\Yii\DataView\Pagination\PaginationContext;


/**
 * @var OffsetPaginator $paginator
 * @var TranslatorInterface $translator
 * @var Tag $item
 * @var UrlGeneratorInterface $url
 * @var WebView $this
 */
$this->setTitle($item->getLabel());
$pagination = Div::tag()
    ->content(
        OffsetPagination::create(
            $paginator,
            $url->generate('blog/tag', ['label' => $item->getLabel()]) . '/page/' . PaginationContext::URL_PLACEHOLDER,
            $url->generate('blog/tag', ['label' => $item->getLabel()]),
        ),
    )
    ->class('table-responsive')
    ->encode(false)
    ->render();

echo H1::tag()->content(Html::encode($item->getLabel()));

$liList = [];
/** @var Post $post */
foreach ($paginator->read() as $post) {
    $liList[] = Li::tag()
        ->class('text-muted')
        ->content(
            Html::a(
                Html::encode($post->getTitle()),
                $url->generate('blog/post', ['slug' => $post->getSlug()]),
            ),
            ' by ',
            A::tag()
                ->content(
                    Html::a(
                        Html::encode($post->getAuthor()->getName()),
                        $url->generate('user/profile', ['login' => $post->getAuthor()->getName()]),
                    ),
                ),
            ' at ',
            Html::span($post->getCreatedAt()->format('H:i d.m.Y')),
        );
}

echo Ul::tag()
    ->items(
        ...$liList,
    );

if ($paginator->getTotalItems() > 0) {
    echo $pagination;
}
