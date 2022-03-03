<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\Data\Paginator\OffsetPaginator $paginator;
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Form\Widget\Field $field
 * @var \Yiisoft\Translator\Translator $translator
 * @var \Yiisoft\View\WebView $this
 * @var string $csrf
 * @var bool $canPublicPost
 */

use App\Blog\Domain\Comment;
use App\Presentation\Infrastructure\Web\Widget\OffsetPagination;
use Yiisoft\Html\Html;

$this->setTitle($translator->translate('backend.title.comments'));
$pagination = OffsetPagination::widget()
    ->paginator($paginator)
    ->urlGenerator(fn ($page) => $url->generate('backend/comment', ['page' => $page]));
?>
<h1><?= Html::encode($this->getTitle())?></h1>
<div class="row">
    <div class="col-sm-12 col-md-10 col-lg-8">
        <?php
        $pageSize = $paginator->getCurrentPageSize();
        if ($pageSize > 0) {
            echo Html::p(
                sprintf('Showing %s out of %s comments', $pageSize, $paginator->getTotalItems()),
                ['class' => 'text-muted']
            );
        } else {
            echo Html::p($translator->translate('no.records'));
        }

        /** @var Comment $comment */
        foreach ($paginator->read() as $comment) {
            $status = $comment->isPublic() ? $translator->translate('blog.public') : $translator->translate('draft');
            $colorButton = $comment->isPublic() ? 'success' : 'danger';
            echo <<<COMMENT
                <div class="comment-list m-2 mb-3 p-2 border border-light border-2">
                    <p><a href="{$url->generate('backend/comment/view', ['comment_id' => $comment->getId()])}">{$translator->translate('blog.comment.id.№')}{$comment->getId()}</a></p>
                    <p class="small">{$comment->getContent()}</p>

                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-{$colorButton}">$status</button>
                        <form id="removeRole" action="{$url->generate('backend/comment/delete', ['comment_id' => $comment->getId()])}" method="post"  enctype="multipart/form-data">
                            <input type="hidden" name="_csrf" value="{$csrf}">
                            <input type="hidden" name="comment_id" value="{$comment->getId()}">

                            <button type="submit" class="btn btn-sm btn-danger">{$translator->translate('blog.delete')}</button>
                        </form>
                    </div>

                </div>
            COMMENT;

        }
        if ($pagination->isRequired()) {
            echo $pagination;
        }
        ?>
    </div>
</div>
