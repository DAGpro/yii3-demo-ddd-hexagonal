<?php

declare(strict_types=1);

/**
 * @var OffsetPaginator $paginator
 * @var TranslatorInterface $translator
 * @var Tag $item
 * @var UrlGeneratorInterface $url
 * @var WebView $this
 */

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

$this->setTitle($item->getLabel());
$pagination = Div::tag()
    ->content(
        new OffsetPagination()
            ->withContext(
                new PaginationContext(
                    $url->generate('blog/tag',
                        ['label' => $item->getLabel()],
                    ) . '/page/' . PaginationContext::URL_PLACEHOLDER,
                    $url->generate(
                        'blog/tag',
                        ['label' => $item->getLabel()],
                    ) . '/page/' . PaginationContext::URL_PLACEHOLDER,
                    $url->generate('blog/tag', ['label' => $item->getLabel()]),
                ),
            )
            ->listTag('ul')
            ->listAttributes(['class' => 'pagination width-auto'])
            ->itemTag('li')
            ->itemAttributes(['class' => 'page-item'])
            ->linkAttributes(['class' => 'page-link'])
            ->currentItemClass('active')
            ->currentLinkClass('page-link')
            ->disabledItemClass('disabled')
            ->disabledLinkClass('disabled')
            ->withPaginator($paginator),
    )
    ->class('table-responsive')
    ->encode(false)
    ->render();

H1::tag()->content(Html::encode($item->getLabel()))->render();

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
