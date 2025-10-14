<?php

declare(strict_types=1);


use App\Blog\Domain\Post;
use App\Blog\Presentation\Frontend\View\Widget\PostCard;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\P;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;
use Yiisoft\Yii\DataView\Pagination\OffsetPagination;
use Yiisoft\Yii\DataView\Pagination\PaginationContext;

/**
 * @var int $year
 * @var int $month
 * @var OffsetPaginator $paginator
 * @var TranslatorInterface $translator
 * @var UrlGeneratorInterface $url
 * @var WebView $this
 */

$monthName = DateTime::createFromFormat('!m', (string)$month)->format('F');
/** @psalm-scope-this WebView */
$this->setTitle($translator->translate('blog.archive.for') . "<small class='text-muted'>$monthName $year</small>");

$pagination = Div::tag()
    ->content(
        OffsetPagination::create(
            $paginator,
            $url->generate(
                'blog/archive/month',
                ['year' => $year, 'month' => $month],
            ) . '/page/' . PaginationContext::URL_PLACEHOLDER,
            $url->generate('blog/archive/month', ['year' => $year, 'month' => $month]),
        ),
    );
?>
<h1><?= $this->getTitle() ?></h1>
<div class="row">
    <div class="col-sm-8 col-md-8 col-lg-9">
        <?php
        $pageSize = $paginator->getCurrentPageSize();
        if ($pageSize > 0) {
            echo P::tag()
                ->content(
                    $translator->translate('pagination-summary',
                        ['pageSize' => $pageSize, 'total' => $paginator->getTotalItems()],
                    ),
                )
                ->class('text-muted');
        } else {
            echo P::tag()->content($translator->translate('views.no-records'));
        }

        /** @var Post $item */
        foreach ($paginator->read() as $item) {
            echo PostCard::widget(['post' => $item]);
        }

        if ($paginator->getTotalItems() > 0) {
            echo $pagination;
        }
        ?>
    </div>
    <div class="col-sm-4 col-md-4 col-lg-3">
    </div>
</div>
