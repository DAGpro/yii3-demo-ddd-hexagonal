<?php

declare(strict_types=1);


use App\Blog\Domain\Post;
use App\Infrastructure\LocaleDateFormatter;
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
 * @var CurrentRoute $currentRoute
 * @var Translator $translator
 * @var WebView $this
 * @var string $csrf
 */
$this->setTitle($translator->translate('backend.title.posts'));
?>

<h1 class="mb-4"><?= Html::encode($this->getTitle()) ?></h1>
<div class="roles">
    <?php
    $columns = [
        new DataColumn(
            property: 'id',
            header: 'Id',
            withSorting: true,
            content: static fn(Post $model): int => (int)$model->getId(),
            filter: TextInputFilter::widget()->addAttributes(['class' => 'form-control form-control-sm']),
            filterEmpty: false,
        ),
        new DataColumn(
            property: 'title',
            header: 'Title',
            withSorting: true,
            content: static fn(Post $model): string => A::tag()
                ->class('fw-bold')
                ->href($url->generate('backend/post/view', ['post_id' => $model->getId()]))
                ->content($model->getTitle())->render(),
            encodeContent: false,
            filter: TextInputFilter::widget()->addAttributes(['class' => 'form-control form-control-sm']),
            filterEmpty: false,
        ),
        new DataColumn(
            property: 'author_name',
            header: 'Author',
            withSorting: true,
            content: static fn(Post $model): string => $model->getAuthor()->getName(),
            filter: TextInputFilter::widget()->addAttributes(['class' => 'form-control form-control-sm']),
            filterEmpty: false,
        ),
        new DataColumn(
            property: 'created_at',
            header: 'Created At',
            withSorting: true,
            content: static fn(Post $model): string
                => LocaleDateFormatter::format(
                $model->getCreatedAt(),
                $translator->getLocale(),
                'd MMMM yyyy HH:mm',
            ),
            filter: TextInputFilter::widget()->addAttributes(['class' => 'form-control form-control-sm']),
            filterEmpty: false,
        ),
        new DataColumn(
            property: 'public',
            header: 'Status',
            withSorting: true,
            content: static fn(Post $model): string
                => $model->isPublic()
                ? "<button class='btn btn-success btn-sm'>{$translator->translate('blog.public')}</button>"
                : "<button class='btn btn-danger btn-sm'>{$translator->translate('blog.draft')}</button>",
            encodeContent: false,
            filter: TextInputFilter::widget()->addAttributes(['class' => 'form-control form-control-sm']),
            filterEmpty: false,
        ),
        new DataColumn(
            header: 'Action',
            withSorting: true,
            content: static fn(Post $model): string
                => Form::tag()
                ->method('post')
                ->action($url->generate('backend/post/delete', ['post_id' => $model->getId()]))
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
                        ->name('post_id')
                        ->value($model->getId())
                        ->render(),
                    Button::tag()
                        ->class('btn btn-sm btn-danger')
                        ->content($translator->translate('blog.delete'))
                        ->render(),
                )
                ->render(),
            encodeContent: false,
            filter: TextInputFilter::widget()->addAttributes(['class' => 'form-control form-control-sm']),
            filterEmpty: false,
        ),
    ];
    /** @var PaginationWidgetInterface<PaginatorInterface> $pagination */
    $pagination = OffsetPagination::create(
        $paginator,
        $url->generate('backend/post') . 'page/' . PaginationContext::URL_PLACEHOLDER,
        $url->generate('backend/post'),
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
