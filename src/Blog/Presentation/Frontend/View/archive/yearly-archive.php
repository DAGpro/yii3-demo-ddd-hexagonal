<?php

declare(strict_types=1);

/**
 * @var int $year
 * @var \App\Blog\Domain\Post[]|\Yiisoft\Data\Reader\DataReaderInterface $items
 * @var \Yiisoft\Translator\TranslatorInterface $translator
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\View\WebView $this
 */

use App\Blog\Domain\Post;
use Yiisoft\Html\Html;

$this->setTitle($translator->translate('blog.archive.for-year', ['year' => $year]));

?>
<h1><?= $translator->translate('blog.archive.for-year', ['year' => '<small class="text-muted">' . $year . '</small>']) ?></h1>
<div class="row">
    <div class="col-sm-8 col-md-8 col-lg-9">
        <?php
        if (count($items) > 0) {
            echo Html::p(
                $translator->translate('blog.total.posts', ['count' => count($items)]),
                ['class' => 'text-muted']
            );
        } else {
            echo Html::p($translator->translate('views.no-records'));
        }
        $currentMonth = null;
        $monthName = '';
        /** @var Post $item */
        foreach ($items as $item) {
            $month = (int)$item->getPublishedAt()->format('m');

            if ($currentMonth !== $month) {
                $currentMonth = $month;
                $monthName = DateTime::createFromFormat('!m', (string) $month)->format('F');
                echo Html::div("{$year} {$monthName}", ['class' => 'lead']);
            }
            echo Html::openTag('div');
            echo Html::a(
                Html::encode($item->getTitle()),
                $url->generate('blog/post', ['slug' => $item->getSlug()])
            );
            echo ' by ';
            $login = $item->getAuthor()->getName();
            echo Html::a(Html::encode($login), $url->generate(
                'user/profile',
                ['login' => $login]
            ));
            echo Html::closeTag('div');
        }
        ?>
    </div>
    <div class="col-sm-4 col-md-4 col-lg-3"></div>
</div>
