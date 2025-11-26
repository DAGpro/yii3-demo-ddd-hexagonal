<?php

declare(strict_types=1);


use App\Blog\Domain\Comment;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\Button;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Html\Tag\Input;
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
                    echo Form::tag()
                        ->action($url->generate('backend/comment/draft', ['comment_id' => $comment->getId()]))
                        ->method('POST')
                        ->addAttributes(['id' => 'draftComment'])
                        ->enctype('multipart/form-data')
                        ->encode(false)
                        ->content(
                            Input::tag()
                                ->type('hidden')
                                ->name('_csrf')
                                ->value($csrf),
                            Input::tag()
                                ->type('hidden')
                                ->name('comment_id')
                                ->value($comment->getId()),
                            Button::tag()
                                ->type('submit')
                                ->class('btn btn-sm btn-primary')
                                ->content($translator->translate('blog.draft.comment')),
                        );
                } else {
                    echo Form::tag()
                        ->action($url->generate('backend/comment/public', ['comment_id' => $comment->getId()]))
                        ->method('POST')
                        ->addAttributes(['id' => 'publicComment'])
                        ->enctype('multipart/form-data')
                        ->encode(false)
                        ->content(
                            Input::tag()
                                ->type('hidden')
                                ->name('_csrf')
                                ->value($csrf),
                            Input::tag()
                                ->type('hidden')
                                ->name('comment_id')
                                ->value($comment->getId()),
                            Button::tag()
                                ->type('submit')
                                ->class('btn btn-sm btn-success')
                                ->content($translator->translate('blog.public.comment')),
                        );
                }
                echo A::tag()
                    ->content($translator->translate('blog.moderate.comment', ['itemId' => $comment->getId()]))
                    ->url($url->generate('backend/comment/moderate', ['comment_id' => $comment->getId()]))
                    ->class('btn btn-sm btn-warning')
                    ->render();
                echo Form::tag()
                    ->action($url->generate('backend/comment/delete', ['comment_id' => $comment->getId()]))
                    ->method('POST')
                    ->addAttributes(['id' => 'deleteComment'])
                    ->enctype('multipart/form-data')
                    ->encode(false)
                    ->content(
                        Input::tag()
                            ->type('hidden')
                            ->name('_csrf')
                            ->value($csrf),
                        Input::tag()
                            ->type('hidden')
                            ->name('comment_id')
                            ->value($comment->getId()),
                        Button::tag()
                            ->type('submit')
                            ->class('btn btn-sm btn-danger')
                            ->content($translator->translate('blog.delete.comment')),
                    );
                ?>
            </div>
        </div>
    </div>
</div>
