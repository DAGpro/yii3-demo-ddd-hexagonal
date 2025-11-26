<?php

declare(strict_types=1);


use App\Blog\Domain\Post;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\Button;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Html\Tag\Input;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;

/**
 * @var Post $post
 * @var UrlGeneratorInterface $url
 * @var Translator $translator
 * @var string $csrf
 * @var string $slug
 * @psalm-scope-this WebView
 */

$this->setTitle(
    $translator->translate('blog.view.post')
    . ($post->getId() ?: '<span class="text-danger">Not id</span>'),
);

?>

<div>
    <div class="post mb-4 card">
        <h1 class="mb-3 card-header"><?= $this->getTitle() ?></h1>
        <div class="article p-3 card-body">
            <h2><?= $post->getTitle() ?></h2>

            <p>
                <small><?= $translator->translate('blog.author') ?></small>
                <?= A::tag()
                    ->content($post->getAuthor()->getName())
                    ->url(
                        $url->generate(
                            'backend/user/profile',
                            ['user_id' => $post->getAuthor()->getId()],
                        ),
                    )
                    ->render()
                ?>
            </p>

            <div class="content mb-4">
                <?= $post->getContent() ?>
            </div>

            <div id="tags">
                <?php
                foreach ($post->getTags() as $tag) : ?>
                    <a href="<?= $url->generate('backend/tag/change', ['tag_id' => $tag->getId()]) ?>"
                       class="btn btn-sm btn-info mb-2 me-2">
                        <?= Html::encode($tag->getLabel()) ?>
                    </a>
                <?php
                endforeach; ?>
            </div>

            <p>
                <span class="text-muted">
                    <?= $post->isPublic() === false
                        ? $translator->translate('blog.draft.post')
                        : $translator->translate(
                            'blog.published.post',
                            ['date' => $post->getPublishedAt()?->format('d.m.Y')],
                        )
                    ?>
                </span>
            </p>
        </div>

        <div class="card-footer p-3">
            <div class="btn-group" role="group">
                <?php
                if ($post->isPublic()) {
                    echo Form::tag()
                        ->action($url->generate('backend/post/draft', ['post_id' => $post->getId()]))
                        ->method('POST')
                        ->addAttributes(['id' => 'draftPost'])
                        ->enctype('multipart/form-data')
                        ->encode(false)
                        ->content(
                            Input::tag()
                                ->type('hidden')
                                ->name('_csrf')
                                ->value($csrf),
                            Input::tag()
                                ->type('hidden')
                                ->name('post_id')
                                ->value($post->getId()),
                            Button::tag()
                                ->type('submit')
                                ->class('btn btn-sm btn-primary')
                                ->content($translator->translate('blog.draft.post')),
                        );
                } else {
                    echo Form::tag()
                        ->action($url->generate('backend/post/public', ['post_id' => $post->getId()]))
                        ->method('POST')
                        ->addAttributes(['id' => 'publicPost'])
                        ->enctype('multipart/form-data')
                        ->encode(false)
                        ->content(
                            Input::tag()
                                ->type('hidden')
                                ->name('_csrf')
                                ->value($csrf),
                            Input::tag()
                                ->type('hidden')
                                ->name('post_id')
                                ->value($post->getId()),
                            Button::tag()
                                ->type('submit')
                                ->class('btn btn-sm btn-success')
                                ->content($translator->translate('blog.public.post')),
                        );
                }
                echo A::tag()
                    ->content($translator->translate('blog.moderate.post'))
                    ->url($url->generate('backend/post/moderate', ['post_id' => $post->getId()]))
                    ->class('btn btn-sm btn-warning')
                    ->render();

                echo Form::tag()
                    ->action($url->generate('backend/post/delete', ['post_id' => $post->getId()]))
                    ->method('POST')
                    ->addAttributes(['id' => 'deletePost'])
                    ->enctype('multipart/form-data')
                    ->encode(false)
                    ->content(
                        Input::tag()
                            ->type('hidden')
                            ->name('_csrf')
                            ->value($csrf),
                        Input::tag()
                            ->type('hidden')
                            ->name('post_id')
                            ->value($post->getId()),
                        Button::tag()
                            ->type('submit')
                            ->class('btn btn-sm btn-danger')
                            ->content($translator->translate('blog.delete.post')),
                    );
                ?>
            </div>
        </div>

    </div>
</div>
