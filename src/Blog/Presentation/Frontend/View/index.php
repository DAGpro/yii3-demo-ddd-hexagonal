<?php

declare(strict_types=1);

/**
 * @var OffsetPaginator $paginator ;
 * @var DataReaderInterface|string[][] $archive
 * @var DataReaderInterface $tags
 * @var TranslatorInterface $translator
 * @var UrlGeneratorInterface $url
 * @var WebView $this
 * @var Author|null $author
 */

use App\Blog\Domain\Post;
use App\Blog\Domain\User\Author;
use App\Blog\Presentation\Frontend\View\Widget\PostCard;
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

$this->setTitle($translator->translate('view-blog.blog'));

$pagination = Div::tag()
    ->content(
        new OffsetPagination()
            ->withContext(
                new PaginationContext(
                    $url->generate('blog/index') . '/page/' . PaginationContext::URL_PLACEHOLDER,
                    $url->generate('blog/index') . '/page/' . PaginationContext::URL_PLACEHOLDER,
                    $url->generate('blog/index'),
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
?>
<h1><?= Html::encode($this->getTitle()) ?></h1>
<div class="row">
    <div class="col-sm-8 col-md-8 col-lg-9">
        <?php
        $pageSize = $paginator->getCurrentPageSize();
        if ($pageSize > 0) {
            echo P::tag()
                ->content(
                    $translator->translate('pagination-summary', [
                        'pageSize' => $pageSize,
                        'total' => $paginator->getTotalItems(),
                    ]),
                )
                ->class('text-muted')
                ->encode(false)
                ->render();
        } else {
            echo P::tag()
                ->content($translator->translate('views.no-records'));
        }
        /** @var Post $item */
        foreach ($paginator->read() as $item) {
            echo PostCard::widget()->post($item);
        }
        if ($paginator->getTotalItems() > 0) {
            echo $pagination;
        }
        ?>
    </div>
    <div class="col-sm-4 col-md-4 col-lg-3">
        <?php
        if ($author !== null) {
            echo A::tag()
                ->content($translator->translate('blog.add.post'))
                ->url($url->generate('blog/author/post/add'))
                ->class('btn btn-outline-secondary btn-md-12 mb-3')
                ->encode(false)
                ->render();
        } ?>

        <?= $this->render('_topTags', ['tags' => $tags]) ?>
        <?= $this->render('_archive', ['archive' => $archive]) ?>
    </div>
</div>
