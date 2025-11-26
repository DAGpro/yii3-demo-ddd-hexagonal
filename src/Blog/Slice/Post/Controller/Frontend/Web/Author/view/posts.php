<?php

declare(strict_types=1);


use App\Blog\Domain\Post;
use App\Blog\Domain\User\Author;
use App\Blog\Slice\Post\Controller\Frontend\Web\Widget\AuthorPostCard;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\P;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;
use Yiisoft\Yii\DataView\Pagination\OffsetPagination;
use Yiisoft\Yii\DataView\Pagination\PaginationContext;

/**
 * @var OffsetPaginator $paginator ;
 * @var DataReaderInterface|string[][] $archive
 * @var DataReaderInterface|string[][] $tags
 * @var UrlGeneratorInterface $url
 * @var TranslatorInterface $translator
 * @var WebView $this
 * @var Author $author
 */
$this->setTitle('Posts by author ' . $author->getName());
$pagination = Div::tag()
    ->content(
        OffsetPagination::create(
            $paginator,
            $url->generate('blog/author/posts', ['author' => $author->getName()]),
            $url->generate('blog/author/posts', ['author' => $author->getName()])
            . 'page/' . PaginationContext::URL_PLACEHOLDER,
        ),
    )
    ->class('table-responsive')
    ->encode(false)
    ->render();
?>
<h1><?= Html::encode($this->getTitle()) ?></h1>
<div class="row">
    <div class="col-sm-8 col-md-8 col-lg-9">
        <?php
        $pageSize = $paginator->getCurrentPageSize();
        if ($pageSize > 0) {
            echo P::tag()
                ->class('text-muted')
                ->content(
                    sprintf(
                        'Showing %s out of %s posts',
                        $pageSize,
                        $paginator->getTotalItems(),
                    ),
                )
                ->encode(false)
                ->render();
        } else {
            echo P::tag()->content('You have no posts yet');
        }

        /** @var Post $item */
        foreach ($paginator->read() as $item) {
            echo AuthorPostCard::widget(['post' => $item]);
        }

        if ($paginator->getTotalItems() > 0) {
            echo $pagination;
        }
        ?>
    </div>
    <div class="col-sm-4 col-md-4 col-lg-3">
        <?= A::tag()
            ->class('btn btn-outline-secondary btn-md-12 mb-3')
            ->content($translator->translate('blog.add.post'))
            ->url($url->generate('blog/author/post/add'))
            ->encode(false)
            ->render()
        ?>
    </div>
</div>
