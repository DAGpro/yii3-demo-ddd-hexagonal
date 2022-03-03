<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\Data\Paginator\OffsetPaginator $paginator;
 * @var \Yiisoft\Data\Reader\DataReaderInterface|string[][] $archive
 * @var \Yiisoft\Data\Reader\DataReaderInterface|string[][] $tags
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Translator\TranslatorInterface $translator
 * @var \Yiisoft\View\WebView $this
 * @var \App\Blog\Domain\User\Author|null $author
 */

use App\Blog\Domain\Post;
use App\Presentation\Frontend\Web\View\Widget\AuthorPostCard;
use App\Presentation\Infrastructure\Web\Widget\OffsetPagination;
use Yiisoft\Html\Html;

$this->setTitle('Posts by author ' . $author->getName());
$pagination = OffsetPagination::widget()
    ->paginator($paginator)
    ->urlGenerator(fn ($page) => $url->generate('blog/author/posts', ['page' => $page, 'author' => $author->getName()]));
?>
<h1><?= Html::encode($this->getTitle())?></h1>
<div class="row">
    <div class="col-sm-8 col-md-8 col-lg-9">
        <?php
        $pageSize = $paginator->getCurrentPageSize();
        if ($pageSize > 0) {
            echo Html::p(
                sprintf('Showing %s out of %s posts', $pageSize, $paginator->getTotalItems()),
                ['class' => 'text-muted']
            );
        } else {
            echo Html::p('You have no posts yet');
        }

        /** @var Post $item */
        foreach ($paginator->read() as $item) {
            echo AuthorPostCard::widget()->post($item);
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
        <?php //$this->render('_topTags', ['tags' => $tags]) ?>
        <?php //$this->render('_archive', ['archive' => $archive]) ?>
    </div>
</div>
