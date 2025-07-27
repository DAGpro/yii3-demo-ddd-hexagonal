<?php

declare(strict_types=1);

use App\IdentityAccess\User\Domain\User;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\I;
use Yiisoft\Html\Tag\Input;
use Yiisoft\Html\Tag\Input\Checkbox;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;
use Yiisoft\Yii\DataView\Column\Base\DataContext;
use Yiisoft\Yii\DataView\Column\CheckboxColumn;
use Yiisoft\Yii\DataView\Column\DataColumn;
use Yiisoft\Yii\DataView\Column\SerialColumn;
use Yiisoft\Yii\DataView\Filter\Widget\TextInputFilter;
use Yiisoft\Yii\DataView\GridView;
use Yiisoft\Yii\DataView\Pagination\OffsetPagination;
use Yiisoft\Yii\DataView\YiiRouter\UrlCreator;
use Yiisoft\Yii\DataView\YiiRouter\UrlParameterProvider;

/**
 * @var OffsetPaginator $paginator ;
 * @var TranslatorInterface $translator
 * @var UrlGeneratorInterface $url
 * @var WebView $this
 * @var CurrentRoute $currentRoute
 */

$this->setTitle($translator->translate('menu.users'));
?>

<div class="container">
    <div class="text-end">
        <?= A::tag()
            ->content('API v1 Info')
            ->url($url->generate('api/info/v1'))
            ->class('btn btn-link')
        ?>
        <?= A::tag()
            ->content('API v2 Info')
            ->url($url->generate('api/info/v2'))
            ->class('btn btn-link')
        ?>
        <?= A::tag()
            ->content('API Users List Data')
            ->url($url->generate('api/user/index'))
            ->class('btn btn-link')
        ?>
    </div>

    <div class="card shadow">
        <h5 class="card-header bg-primary text-white">
            <i class="bi bi-people"></i> List users
        </h5>
        <?php
        $columns = [
            new SerialColumn(),
            new CheckboxColumn(
                multiple: true,
                content: static function (Checkbox $input, DataContext $context): string {
                    $data = $context->data;

                    return Input::tag()
                        ->type('checkbox')
                        ->addAttributes([
                            'id' => "",
                            'name' => 'checkbox[]',
                            'data-bs-toggle' => 'tooltip',
                        ])
                        ->value('value')
                        ->render();
                },
            ),
            new DataColumn(
                property: 'id',
                header: 'Id',
                withSorting: true,
                content: static fn(User $model): int => (int)$model->getId(),
                filter: TextInputFilter::widget()->addAttributes(['class' => 'form-control form-control-sm']),
                filterEmpty: false,
            ),
            new DataColumn(
                property: 'login',
                header: 'Login',
                withSorting: true,
                content: static fn(User $model): string => $model->getLogin(),
                filter: TextInputFilter::widget()->addAttributes(['class' => 'form-control form-control-sm']),
                filterEmpty: false,
            ),
            new DataColumn(
                property: 'created_at',
                header: 'Created At',
                withSorting: true,
                content: static fn(User $model): string => $model->getCreatedAt()->format('r'),
                filter: TextInputFilter::widget()->addAttributes(['class' => 'form-control form-control-sm']),
                filterEmpty: false,
            ),
            new DataColumn(
                property: 'api',
                header: 'Api',
                withSorting: true,
                content: static fn(User $model): string => Html::a(
                    'API User Data',
                    $url->generate('api/user/profile', ['login' => $model->getLogin()]),
                    ['class' => 'btn btn-link', 'target' => '_blank'],
                )->render(),
                encodeContent: false,
                filter: TextInputFilter::widget()->addAttributes(['class' => 'form-control form-control-sm']),
                filterEmpty: false,
            ),
            new DataColumn(
                property: 'profile',
                header: 'Profile',
                withSorting: true,
                content: static fn(User $model): string
                    => A::tag()
                    ->content(I::tag()
                        ->class('bi bi-person-fill ms-1')
                        ->addAttributes(['style' => 'font-size: 1.5em'])->render(),
                    )
                    ->url($url->generate('user/profile', ['login' => $model->getLogin()]))
                    ->class('btn btn-link')
                    ->encode(false)
                    ->render(),
                encodeContent: false,
                filter: TextInputFilter::widget()->addAttributes(['class' => 'form-control form-control-sm']),
                filterEmpty: false,
            ),
        ];
        echo GridView::widget()
            ->dataReader($paginator)
            ->tableAttributes([
                'class' => 'table table-striped text-center h-75',
                'id' => 'table-tariffs-group',
            ])
            ->sortableHeaderPrepend('<div class="float-start text-secondary text-opacity-50 me-1">⭥</div>')
            ->sortableHeaderAscPrepend('<div class="float-start fw-bold me-1">⭡</div>')
            ->sortableHeaderDescPrepend('<div class="float-start fw-bold me-1">⭣</div>')
            ->enableHeader(true)
            ->containerAttributes(['class' => 'table-responsive'])
            ->headerCellAttributes(['class' => 'table-dark'])
            ->headerRowAttributes(['class' => 'card-header bg-dark text-white'])
            ->emptyCellAttributes(['style' => 'color:red'])
            ->summaryAttributes(['class' => 'mt-3 me-3 summary text-end'])
            ->emptyTextAttributes(['class' => 'card-header bg-danger text-black'])
            ->summaryTemplate('Показано {begin}-{end} из {totalCount} записей')
            ->emptyText('Записи не найдены')
            ->enableFooter(true)
            ->emptyCell('-')
            ->urlParameterProvider(new UrlParameterProvider($currentRoute))
            ->urlCreator(new UrlCreator($url))
            ->paginationWidget(
                OffsetPagination::widget()
                    ->listTag('ul')
                    ->listAttributes(['class' => 'pagination width-auto'])
                    ->itemTag('li')
                    ->itemAttributes(['class' => 'page-item'])
                    ->linkAttributes(['class' => 'page-link'])
                    ->currentItemClass('active')
                    ->currentLinkClass('page-link')
                    ->disabledItemClass('disabled')
                    ->disabledLinkClass('disabled'),
            )
            ->columns(...$columns); ?>
    </div>
</div>
