<?php

declare(strict_types=1);

/**
 * @var OffsetPaginator $paginator ;
 * @var UrlGeneratorInterface $url
 * @var Translator $translator
 * @var WebView $this
 * @var string $csrf
 * @var bool $canPublicPost
 */

use App\Blog\Domain\Comment;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\P;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;
use Yiisoft\Yii\DataView\Pagination\OffsetPagination;
use Yiisoft\Yii\DataView\Pagination\PaginationContext;

$this->setTitle($translator->translate('backend.title.comments'));
$pagination = Div::tag()
    ->content(
        new OffsetPagination()
            ->withContext(
                new PaginationContext(
                    $url->generate(
                        'blog/comment',
                    ) . '/page/' . PaginationContext::URL_PLACEHOLDER,
                    $url->generate(
                        'blog/comment',
                    ) . '/page/' . PaginationContext::URL_PLACEHOLDER,
                    $url->generate('blog/comment'),
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
    <div class="col-sm-12 col-md-10 col-lg-8">
        <?php
        $pageSize = $paginator->getCurrentPageSize();
        if ($pageSize > 0) {
            echo P::tag()
                ->content(sprintf('Showing %s out of %s comments', $pageSize, $paginator->getTotalItems()))
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
                <div class="comment-list m-2 mb-3 p-2 border border-light border-2">
                    <p>
                        <a href="{$url->generate('backend/comment/view', ['comment_id' => $comment->getId()])}">
                            {$translator->translate('blog.comment.id.№')}{$comment->getId()}
                        </a>
                    </p>
                    <p class="small">{$comment->getContent()}</p>

                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-$colorButton">$status</button>
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
