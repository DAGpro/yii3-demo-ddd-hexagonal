<?php

declare(strict_types=1);

/**
 * @var OffsetPaginator $paginator ;
 * @var UrlGeneratorInterface $url
 * @var Translator $translator
 * @var string $csrf
 * @var WebView $this
 */

use App\Blog\Domain\Tag;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\P;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;
use Yiisoft\Yii\DataView\Pagination\OffsetPagination;
use Yiisoft\Yii\DataView\Pagination\PaginationContext;

$this->setTitle($translator->translate('backend.title.tags'));
$pagination = Div::tag()
    ->content(
        new OffsetPagination()
            ->withContext(
                new PaginationContext(
                    $url->generate(
                        'backend/tag',
                    ) . '/page/' . PaginationContext::URL_PLACEHOLDER,
                    $url->generate(
                        'backend/tag',
                    ) . '/page/' . PaginationContext::URL_PLACEHOLDER,
                    $url->generate('backend/tag'),
                ),
            )
            ->listTag('ul')
            ->listAttributes(['class' => 'pagination width-auto'])
            ->itemTag('li')
            ->itemAttributes(['class' => 'page-item'])
            ->linkAttributes(['class' => 'page-link'])
            ->currentItemClass('active')
            ->currentLinkClass('page-link')
            ->disabledItemClass('disabled')
            ->disabledLinkClass('disabled')
            ->withPaginator($paginator),
    )
    ->class('table-responsive')
    ->encode(false)
    ->render();
?>
<h1><?= Html::encode($this->getTitle()) ?></h1>
<div class="row">
    <div class="col-md-12">
        <?php
        $pageSize = $paginator->getCurrentPageSize();
        if ($pageSize > 0) {
            echo P::tag()
                ->content(sprintf('Showing %s out of %s tags', $pageSize, $paginator->getTotalItems()))
                ->class('text-muted')
                ->render();
        } else {
            echo P::tag()
                ->content($translator->translate('no.records'))
                ->render();
        }
        ?>

        <div class="m-2">
            <table class="table mb-5 border border-light border-3">
                <thead>
                <tr>
                    <th scope="col"><?= $translator->translate('blog.id') ?></th>
                    <th scope="col"><?= $translator->translate('blog.tag.label') ?></th>
                    <th scope="col"><?= $translator->translate('blog.action') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                /** @var Tag $tag */
                foreach ($paginator->read() as $tag) {
                    echo <<<ROLE
                        <tr>
                            <td>{$tag->getId()}</td>
                            <td>
                                <a class="btn btn-info"
                                    href="{$url->generate('backend/tag/change', ['tag_id' => $tag->getId()])}">
                                        {$tag->getLabel()}
                                </a>
                            </td>
                            <td>
                                <form id="removeRole"
                                    action="{$url->generate('backend/tag/delete', ['tag_id' => $tag->getId()])}"
                                    method="post"
                                >
                                    <input type="hidden" name="_csrf" value="$csrf">
                                    <input type="hidden" name="tag_id" value="{$tag->getId()}">

                                    <button type="submit" class="btn btn-sm btn-danger">
                                        {$translator->translate('blog.delete')}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        ROLE;
                }
                ?>
                </tbody>
            </table>
        </div>
        <?php
        if ($paginator->getTotalItems() > 0) {
            echo $pagination;
        }
        ?>
    </div>
</div>
