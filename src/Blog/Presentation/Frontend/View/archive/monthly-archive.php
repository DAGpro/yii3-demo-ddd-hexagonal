<?php

declare(strict_types=1);

/**
 * @var int $year
 * @var int $month
 * @var \Yiisoft\Data\Paginator\OffsetPaginator $paginator
 * @var \Yiisoft\Translator\TranslatorInterface $translator
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\View\WebView $this
 */

use App\Blog\Domain\Post;
use App\Blog\Presentation\Frontend\View\Widget\PostCard;
use App\Presentation\Infrastructure\Web\Widget\OffsetPagination;
use Yiisoft\Html\Html;

$monthName = DateTime::createFromFormat('!m', (string) $month)->format('F');
$this->setTitle($translator->translate('blog.archive.for') . "<small class='text-muted'>$monthName $year</small>");

$pagination = OffsetPagination::widget()
    ->paginator($paginator)
    ->urlGenerator(
        fn ($page) => $url->generate(
            'blog/archive/month',
            ['year' => $year, 'month' => $month, 'page' => $page]
        )
    );
?>
<h1><?=$this->getTitle()?></h1>
<div class="row">
    <div class="col-sm-8 col-md-8 col-lg-9">
        <?php
        $pageSize = $paginator->getCurrentPageSize();
        if ($pageSize > 0) {
            echo Html::p(
                $translator->translate('pagination-summary', ['pageSize' => $pageSize, 'total' => $paginator->getTotalItems()]),
                ['class' => 'text-muted']
            );
        } else {
            echo Html::p($translator->translate('views.no-records'));
        }
        /** @var Post $item */
        foreach ($paginator->read() as $item) {
            echo PostCard::widget()->post($item);
        }
        if ($pagination->isRequired()) {
            echo $pagination;
        }
        ?>
    </div>
    <div class="col-sm-4 col-md-4 col-lg-3">
    </div>
</div>
