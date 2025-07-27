<?php

declare(strict_types=1);


use App\Blog\Domain\Post;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\P;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

/**
 * @var int $year
 * @var Post[]|DataReaderInterface $items
 * @var TranslatorInterface $translator
 * @var UrlGeneratorInterface $url
 * @var WebView $this
 */

/** @psalm-scope-this WebView */
$this->setTitle($translator->translate('blog.archive.for-year', ['year' => $year]));

?>
<h1><?= $translator->translate('blog.archive.for-year',
        ['year' => '<small class="text-muted">' . $year . '</small>'],
    ) ?></h1>
<div class="row">
    <div class="col-sm-8 col-md-8 col-lg-9">
        <?php
        if (count($items) > 0) {
            echo P::tag()
                ->content(
                    $translator->translate(
                        'blog.total.posts',
                        ['count' => count($items)],
                    ),
                )
                ->class('text-muted');
        } else {
            echo P::tag()->content($translator->translate('views.no-records'))->class('bg-danger');
        }
        $currentMonth = null;
        $monthName = '';
        /** @var Post $item */
        foreach ($items as $item) {
            if ($item->getPublishedAt()) {
                continue;
            }
            $month = (int)$item->getPublishedAt()?->format('m');

            if ($currentMonth !== $month) {
                $currentMonth = $month;
                $monthName = DateTime::createFromFormat('!m', (string)$month)->format('F');
                echo Div::tag()
                    ->content("{$year} {$monthName}")
                    ->class('lead');
            }
            echo Div::tag()
                ->content(
                    A::tag()
                        ->content(Html::encode($item->getTitle()))
                        ->url($url->generate('blog/post', ['slug' => $item->getSlug()])),
                    ' by ',
                    A::tag()
                        ->content(Html::encode($item->getAuthor()->getName()))
                        ->url(
                            $url->generate(
                                'user/profile',
                                ['login' => $item->getAuthor()->getName()],
                            ),
                        ),
                );
        }
        ?>
    </div>
    <div class="col-sm-4 col-md-4 col-lg-3"></div>
</div>
