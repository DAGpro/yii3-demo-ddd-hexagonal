<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\Data\Paginator\OffsetPaginator $paginator;
 * @var \Yiisoft\Data\Reader\DataReaderInterface|string[][] $archive
 * @var \Yiisoft\Data\Reader\DataReaderInterface|string[][] $tags
 * @var \Yiisoft\Translator\TranslatorInterface $translator
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\View\WebView $this
 * @var \App\Core\Component\Blog\Domain\User\Author|null $author
 */

use App\Core\Component\Blog\Domain\Post;
use App\Presentation\Frontend\Web\View\Widget\PostCard;
use App\Presentation\Infrastructure\Web\Widget\OffsetPagination;
use Yiisoft\Html\Html;

$this->setTitle($translator->translate('layout.blog'));
$pagination = OffsetPagination::widget()
                              ->paginator($paginator)
                              ->urlGenerator(fn ($page) => $urlGenerator->generate('blog/index', ['page' => $page]));
?>
<h1><?= Html::encode($this->getTitle())?></h1>
<div class="row">
    <div class="col-sm-8 col-md-8 col-lg-9">
        <?php
        $pageSize = $paginator->getCurrentPageSize();
        if ($pageSize > 0) {
            echo Html::p(
                $translator->translate('layout.pagination-summary', [
                    'pageSize' => $pageSize,
                    'total' => $paginator->getTotalItems(),
                ]),
                ['class' => 'text-muted']
            );
        } else {
            echo Html::p(
                $translator->translate('layout.no-records')
        );
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
        <?php
        if ($author !== null) {
            echo Html::a(
                $translator->translate('blog.add.post'),
                $url->generate('blog/author/post/add'),
                ['class' => 'btn btn-outline-secondary btn-md-12 mb-3']
            );
        } ?>
        <?= $this->render('_topTags', ['tags' => $tags]) ?>
        <?= $this->render('_archive', ['archive' => $archive]) ?>
    </div>
</div>
