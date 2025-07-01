<?php

declare(strict_types=1);

/**
 * @var DataReaderInterface|string[][] $archive
 * @var UrlGeneratorInterface $url
 * @var TranslatorInterface $translator
 * @var WebView $this
 */

use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\Li;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

$this->setTitle($translator->translate('blog.archive'));

?>
<h1><?= Html::encode($this->getTitle()) ?></h1>
<div class="row">
    <div class="col-sm-12">
        <?php
        $currentYear = null;

        if (count($archive)) {
            $monthList = [];
            foreach ($archive->read() as $item) {
                $year = $item['year'];
                $month = $item['month'];
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

            foreach ($monthList as $year => $months) {
                echo Li::tag()
                    ->class('list-group-item d-flex flex-column justify-content-between lh-condensed')
                    ->content(
                        A::tag()
                            ->content((string)$year)
                            ->url($url->generate('blog/archive/year', ['year' => $year]))
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
