<?php

declare(strict_types=1);


use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\Li;
use Yiisoft\Html\Tag\Span;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

/**
 * @var DataReaderInterface $tags
 * @var TranslatorInterface $translator
 * @var UrlGeneratorInterface $url
 * @var WebView $this
 */

?>
<h4 class="text-muted mb-3">
    <?= $translator->translate('blog.popular.tags') ?>
</h4>
<ul class="list-group mb-3">
    <?php
    $content = [];

    try {
        /** @var array{label: string, count: int} $tag */
        foreach ($tags->read() as $tag) {
            $content[] = Li::tag()
                ->class('list-group-item d-flex justify-content-between align-items-center')
                ->content(
                    A::tag()
                        ->content(Html::encode($tag['label']))
                        ->url($url->generate('blog/tag', ['label' => $tag['label']]))
                        ->class('text-decoration-none text-muted')
                        ->encode(false)
                        ->render()
                    . ' ' .
                    Span::tag()
                        ->class('badge bg-secondary rounded-pill')
                        ->content((string)$tag['count'])
                        ->render(),
                )
                ->encode(false)
                ->render();
        }
    } catch (Exception) {
        $content[] = Li::tag()
            ->class('list-group-item text-danger')
            ->content('Ошибка загрузки тегов')
            ->render();
    }

    if (empty($content)) {
        $content[] = Li::tag()
            ->class('list-group-item text-muted')
            ->content('Теги не найдены')
            ->render();
    }

    echo implode('', $content);
    ?>
</ul>
