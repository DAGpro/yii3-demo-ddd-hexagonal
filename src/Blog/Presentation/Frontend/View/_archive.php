<?php

declare(strict_types=1);

use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\H6;
use Yiisoft\Html\Tag\Li;
use Yiisoft\Html\Tag\Span;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

/**
 * @var DataReaderInterface $archive
 * @var TranslatorInterface $translator
 * @var UrlGeneratorInterface $url
 * @var WebView $this
 */
?>
<h4 class="text-muted mb-3"><?= $translator->translate('blog.archive') ?></h4>
<ul class="list-group mb-3">
    <?php
    if (count($archive) > 0) {
        $monthList = [];
        /** @var array{year: string|null, month: string|null, count: int} $item */
        foreach ($archive->read() as $item) {
            /** @var string|null $year */
            $year = $item['year'];
            /** @var string|null $month */
            $month = $item['month'];
            /** @var int $count */
            $count = $item['count'];

            if ($year === null || $month === null) {
                continue;
            }

            $monthList[$year][] =
                Div::tag()
                    ->class('d-flex justify-content-between align-items-center')
                    ->content(
                        A::tag()
                            ->class('text-muted overflow-hidden')
                            ->content(
                                Date('F', mktime(0, 0, 0, (int)$month, 1, (int)$year)),
                            )
                            ->url(
                                $url->generate(
                                    'blog/archive/month',
                                    ['year' => $year, 'month' => $month,],
                                ),
                            ),
                        Span::tag()->content((string)$count)->class('badge rounded-pill bg-secondary'),
                    );
        }
        $monthsPrint = [];
        /**
         * @var int $yearMonths
         * @var array<string> $months
         */
        foreach ($monthList as $yearMonths => $months) {
            $monthsPrint[] = H6::tag()
                ->content((string)$yearMonths)
                ->class('me-0');
            $monthsPrint = [...$monthsPrint, ...$months];
        }

        $monthsPrint[] = A::tag()
            ->content('Open archive')
            ->url($url->generate('blog/archive/index'))
            ->class('mt-2');

        echo Li::tag()
            ->class('list-group-item d-flex flex-column justify-content-between lh-condensed')
            ->content(
                ...$monthsPrint,
            )
            ->encode(false)
            ->render();
    } else {
        echo '<li>', $translator->translate('views.no-records'), '</li>';
    }
    ?>
</ul>
