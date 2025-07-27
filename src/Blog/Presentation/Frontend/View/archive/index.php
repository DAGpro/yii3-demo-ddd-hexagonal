<?php

declare(strict_types=1);


use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\Li;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

/**
 * @var DataReaderInterface $archive
 * @var UrlGeneratorInterface $url
 * @var TranslatorInterface $translator
 * @var WebView $this
 */

/** @psalm-scope-this WebView */
$this->setTitle($translator->translate('blog.archive'));

?>
<h1><?= Html::encode($this->getTitle()) ?></h1>
<div class="row">
    <div class="col-sm-12">
        <?php
        $currentYear = null;

        if (count($archive)) {
            $monthList = [];
            /**
             * @var array{year: int|null, month: int|null, count: int} $item
             */
            foreach ($archive->read() as $item) {
                /**
                 * @var string|null $year
                 **/
                $year = $item['year'];
                /**
                 * @var string|null $month
                 **/
                $month = $item['month'];
                /**
                 * @var int $count
                 **/
                $count = $item['count'];

                if ($year === null || $month === null) {
                    continue;
                }

                $monthList[$year][] = Div::tag()
                    ->class('d-flex flex-wrap')
                    ->content(
                        Div::tag()
                            ->class('mx-2 my-1')
                            ->content(
                                A::tag()
                                    ->class('text-muted')
                                    ->content(
                                        Date('F', mktime(0, 0, 0, (int)$month, 1, (int)$year)),
                                    )
                                    ->url(
                                        $url->generate(
                                            'blog/archive/year',
                                            ['year' => $year, 'month' => $month],
                                        ),
                                    )
                                    ->url(
                                        $url->generate(
                                            'blog/archive/month',
                                            ['year' => $year, 'month' => $month,],
                                        ),
                                    ),
                                Html::tag('sup', (string)$count),
                            ),
                    )
                    ->encode(false);
            }

            /**
             * @var int $yearMonths
             * @var array $months
             */
            foreach ($monthList as $yearMonths => $months) {
                echo Li::tag()
                    ->class('list-group-item d-flex flex-column justify-content-between lh-condensed')
                    ->content(
                        A::tag()
                            ->content((string)$yearMonths)
                            ->url($url->generate('blog/archive/year', ['year' => $yearMonths]))
                            ->class('h5'),
                        ...$months,
                    )
                    ->encode(false)
                    ->render();
            }
        } else {
            echo $translator->translate('layout.no.records');
        }
        ?>
    </div>
</div>
