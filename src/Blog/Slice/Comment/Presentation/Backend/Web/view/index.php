<?php

declare(strict_types=1);


use App\Blog\Domain\Comment;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\P;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;
use Yiisoft\Yii\DataView\Pagination\OffsetPagination;
use Yiisoft\Yii\DataView\Pagination\PaginationContext;

/**
 * @var OffsetPaginator $paginator ;
 * @var UrlGeneratorInterface $url
 * @var Translator $translator
 * @var CurrentRoute $currentRoute
 * @var WebView $this
 * @var string $csrf
 * @var bool $canPublicPost
 */
$this->setTitle($translator->translate('backend.title.comments'));

$queryUrl = $currentRoute->getUri()?->getQuery() ? '?' . $currentRoute->getUri()->getQuery() : '';
$pagination = Div::tag()
    ->content(
        OffsetPagination::create(
            $paginator,
            $url->generate('backend/comment') . 'page/' . PaginationContext::URL_PLACEHOLDER . $queryUrl,
            $url->generate('backend/comment') . $queryUrl,
        ),
    )
    ->class('table-responsive')
    ->encode(false)
    ->render();
?>
<h1><?= Html::encode($this->getTitle()) ?></h1>

<?php
$sort = $paginator->getSort();
$currentSort = $sort?->getOrder();
?>

<div class="mb-3">
    <form method="get" action="<?= $url->generate('backend/comment') ?>" class="row g-3">
        <div class="col-md-6">
            <label for="sort" class="form-label"><?= $translator->translate('Sort by') ?>:</label>
            <select name="sort" id="sort" class="form-select" onchange="this.form.submit()">
                <option value="id" <?= ($currentSort === ['id' => 'asc']) ? 'selected' : '' ?>>
                    <?= $translator->translate('Sort by id ⬆️') ?>
                </option>
                <option value="-id" <?= ($currentSort === ['id' => 'desc']) ? 'selected' : '' ?>>
                    <?= $translator->translate('Sort by id ⬇️') ?>
                </option>
                <option value="public" <?= ($currentSort === ['public' => 'asc']) ? 'selected' : '' ?>>
                    <?= $translator->translate('Draft first') ?>
                </option>
                <option value="-public" <?= ($currentSort === ['public' => 'desc']) ? 'selected' : '' ?>>
                    <?= $translator->translate('Public first ') ?>
                </option>
            </select>
        </div>
    </form>
</div>

<div class="row">
    <div class="col-sm-12 col-md-10 col-lg-8">
        <?php
        $pageSize = $paginator->getCurrentPageSize();
        if ($pageSize > 0) {
            echo P::tag()
                ->content(
                    $translator->translate(
                        'pagination-summary',
                        [
                            'pageSize' => $pageSize,
                            'total' => $paginator->getTotalItems(),
                        ],
                    ),
                )
                ->class('text-muted')
                ->render();
        } else {
            echo P::tag()
                ->content($translator->translate('no.records'))
                ->class('text-muted')
                ->render();
        }

        /** @var Comment $comment */
        foreach ($paginator->read() as $comment) {
            $status = $comment->isPublic() ? $translator->translate('blog.public') : $translator->translate('draft');
            $colorButton = $comment->isPublic() ? 'success' : 'danger';
            echo <<<COMMENT
                <div class="comment-list m-2 mb-4 border border-2 card">
                    <p class="card-header">
                        <a href="{$url->generate('backend/comment/view', ['comment_id' => $comment->getId()])}">
                            {$translator->translate('blog.comment.id.№')}{$comment->getId()}
                        </a>
                    </p>
                    <p class="small card-body">{$comment->getContent()}</p>

                    <div class="card-footer">
                        <button type="button" class="btn btn-sm btn-$colorButton me-1">$status</button>
                        <form
                            id="removeRole"
                            class="d-inline"
                            action="{$url->generate('backend/comment/delete', ['comment_id' => $comment->getId()])}"
                            method="post"
                            enctype="multipart/form-data"
                        >
                            <input type="hidden" name="_csrf" value="$csrf">
                            <input type="hidden" name="comment_id" value="{$comment->getId()}">

                            <button type="submit" class="btn btn-sm btn-danger">
                                {$translator->translate('blog.delete')}
                            </button>
                        </form>
                    </div>

                </div>
                COMMENT;
        }

        if ($paginator->getTotalItems() > 0) {
            echo $pagination;
        }
        ?>
    </div>
</div>
