<?php

declare(strict_types=1);


use App\Blog\Domain\Tag;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Data\Paginator\PaginatorInterface;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\Button;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Html\Tag\Input;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;
use Yiisoft\Yii\DataView\Filter\Widget\TextInputFilter;
use Yiisoft\Yii\DataView\GridView\Column\DataColumn;
use Yiisoft\Yii\DataView\GridView\GridView;
use Yiisoft\Yii\DataView\Pagination\OffsetPagination;
use Yiisoft\Yii\DataView\Pagination\PaginationContext;
use Yiisoft\Yii\DataView\Pagination\PaginationWidgetInterface;
use Yiisoft\Yii\DataView\YiiRouter\UrlCreator;
use Yiisoft\Yii\DataView\YiiRouter\UrlParameterProvider;

/**
 * @var OffsetPaginator $paginator ;
 * @var UrlGeneratorInterface $url
 * @var Translator $translator
 * @var CurrentRoute $currentRoute
 * @var string $csrf
 * @var WebView $this
 */
$this->setTitle($translator->translate('backend.title.tags'));

?>
<h1><?= Html::encode($this->getTitle()) ?></h1>
<div class="row">
    <div class="col-md-12">
        <?php
        $columns = [
            new DataColumn(
                property: 'id',
                header: $translator->translate('blog.id'),
                withSorting: true,
                content: static fn(Tag $model): int => (int)$model->getId(),
                filter: TextInputFilter::widget()->addAttributes(['class' => 'form-control form-control-sm']),
                filterEmpty: false,
            ),
            new DataColumn(
                property: 'label',
                header: $translator->translate('blog.tag.label'),
                withSorting: true,
                content: static function (Tag $model) use ($url): string {
                    return A::tag()
                        ->class('fw-bold')
                        ->href($url->generate('backend/tag/change', ['tag_id' => $model->getId()]))
                        ->content($model->getLabel())->render();
                },
                encodeContent: false,
                filter: TextInputFilter::widget()->addAttributes(['class' => 'form-control form-control-sm']),
                filterEmpty: false,
            ),
            new DataColumn(
                header: $translator->translate('blog.action'),
                withSorting: true,
                content: static function (Tag $model) use ($translator, $url, $csrf): string {
                    return Form::tag()
                        ->method('post')
                        ->action($url->generate('backend/tag/delete', ['tag_id' => $model->getId()]))
                        ->addAttributes(['id' => 'removeRole'])
                        ->encode(false)
                        ->content(
                            Input::tag()
                                ->type('hidden')
                                ->name('_csrf')
                                ->value($csrf)
                                ->render(),
                            Input::tag()
                                ->type('hidden')
                                ->name('tag_id')
                                ->value($model->getId())
                                ->render(),
                            Button::tag()
                                ->class('btn btn-sm btn-danger')
                                ->content($translator->translate('blog.delete'))
                                ->render(),
                        )
                        ->render();
                },
                encodeContent: false,
                filter: TextInputFilter::widget()->addAttributes(['class' => 'form-control form-control-sm']),
                filterEmpty: false,
            ),
        ];

        /**
         * @var PaginationWidgetInterface<PaginatorInterface> $pagination
         */
        $pagination =
            OffsetPagination::create(
                $paginator,
                $url->generate('backend/tag') . 'page/' . PaginationContext::URL_PLACEHOLDER,
                $url->generate('backend/tag'),
            );

        echo GridView::widget()
            ->dataReader($paginator)
            ->tableAttributes([
                'class' => 'table table-striped text-center h-75',
                'id' => 'table-tariffs-group',
            ])
            ->sortableHeaderPrepend('<div class="text-secondary float-end text-opacity-50 me-1">⭥</div>')
            ->sortableHeaderAscPrepend('<div class="fw-bold float-end me-1">⭡</div>')
            ->sortableHeaderDescPrepend('<div class="fw-bold float-end me-1">⭣</div>')
            ->containerAttributes(['class' => 'table-responsive'])
            ->headerCellAttributes(['class' => 'table-dark'])
            ->headerRowAttributes(['class' => 'card-header bg-dark text-white'])
            ->emptyCellAttributes(['style' => 'color:red'])
            ->summaryAttributes(['class' => 'mt-3 me-3 summary text-end'])
            ->noResultsCellAttributes(['class' => 'card-header bg-danger text-black'])
            ->summaryTemplate($translator->translate('how.many.records.shown'))
            ->noResultsText($translator->translate('views.no-records'))
            ->emptyCell('-')
            ->urlParameterProvider(new UrlParameterProvider($currentRoute))
            ->urlCreator(new UrlCreator($url))
            ->paginationWidget($pagination)
            ->columns(...$columns);
        ?>
    </div>
