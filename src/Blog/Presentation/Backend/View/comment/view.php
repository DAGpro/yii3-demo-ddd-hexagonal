<?php

declare(strict_types=1);


use App\Blog\Domain\Comment;
use Yiisoft\Html\Tag\A;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;

/**
 * @var Comment $comment
 * @var WebView $this
 * @var UrlGeneratorInterface $url
 * @var Translator $translator
 * @var string $title
 * @var string $csrf
 */
$this->setTitle(
    $translator->translate('blog.view.comment')
    . ($comment->getId() ?? '<span class="text-danger">Not id</span>'),
);

?>

<div class="main">
    <div class="card">
        <h1 class="card-header"><?= $this->getTitle() ?></h1>
        <div class="comment mb-2 p-2 border border-light border-2 card-body">
            <div class="mb-5">
                <?= $comment->getContent() ?>
            </div>
            <p>
                <span class="text-muted">
                    <?= ($publishedAt = $comment->getPublishedAt()) === null
                        ? $translator->translate('blog.not.published.comment')
                        : $translator->translate('blog.published.comment')
                        . $publishedAt->format('H:i:s d.m.Y')
                    ?>
                </span>
                <?php
                echo $translator->translate('blog.commentator')
                    . A::tag()
                        ->content($comment->getCommentator()->getName())
                        ->url(
                            $url->generate(
                                'backend/user/profile',
                                ['user_id' => $comment->getCommentator()->getId()],
                            ),
                        )
                        ->class('mr-3')
                        ->render();
                ?>
            </p>
        </div>
        <div class="card-footer">
            <div class="btn-group mb-3" role="group">
                <?php
                if ($comment->isPublic()) {
                    echo <<<FORM
                        <form id="draftComment"
                            method="POST"
                            action="{$url->generate('backend/comment/draft', ['comment_id' => $comment->getId()])}"
                            enctype="multipart/form-data"
                        >
                            <input type="hidden" name="_csrf" value="$csrf">
                            <input name="comment_id" type="hidden" value="{$comment->getId()}">
                            <button type="submit" class="btn btn-sm btn-primary">
                                {$translator->translate('blog.draft.comment')}
                            </button>
                        </form>
                        FORM;
                } else {
                    echo <<<FORM
                        <form id="publicComment"
                            method="POST"
                            action="{$url->generate('backend/comment/public', ['comment_id' => $comment->getId()])}"
                            enctype="multipart/form-data"
                        >
                            <input type="hidden" name="_csrf" value="$csrf">
                            <input name="comment_id" type="hidden" value="{$comment->getId()}">
                            <button type="submit" class="btn btn-sm btn-success">
                                {$translator->translate('blog.public.comment')}
                            </button>
                        </form>
                        FORM;
                }
                echo A::tag()
                    ->content($translator->translate('blog.moderate.comment'))
                    ->url($url->generate('backend/comment/moderate', ['comment_id' => $comment->getId()]))
                    ->class('btn btn-sm btn-warning')
                    ->render();
                echo <<<FORM
                    <form id="deleteComment"
                        method="POST"
                        action="{$url->generate('backend/comment/delete', ['comment_id' => $comment->getId()])}"
                        enctype="multipart/form-data"
                    >
                        <input type="hidden" name="_csrf" value="$csrf">
                        <input name="comment_id" type="hidden" value="{$comment->getId()}">
                        <button type="submit" class="btn btn-sm btn-danger">
                            {$translator->translate('blog.delete.comment')}
                        </button>
                    </form>
                    FORM;

                ?>
            </div>
        </div>

    </div>
</div>
