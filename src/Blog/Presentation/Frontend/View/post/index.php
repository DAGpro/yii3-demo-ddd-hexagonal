<?php

declare(strict_types=1);


use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use App\Blog\Domain\User\Commentator;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\P;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

/**
 * @var Post $item
 * @var TranslatorInterface $translator
 * @var UrlGeneratorInterface $url
 * @var WebView $this
 * @var bool $canEdit
 * @var ?Commentator $commentator
 * @var string $csrf
 * @var string $slug
 */
$this->setTitle($item->getTitle());

?>
    <h1><?= Html::encode($item->getTitle()) ?></h1>
    <div>
        <span class="text-muted"><?= $item->getPublishedAt() === null
                ? $translator->translate('blog.not.published.post')
                : $translator->translate(
                    'blog.published.post',
                    ['date' => $item->getPublishedAt()?->format('H:i:s d.m.Y')],
                ) ?> by</span>
        <?php
        echo A::tag()
            ->class('mr-3')
            ->content($item->getAuthor()->getName())
            ->url($url->generate('user/profile', ['login' => $item->getAuthor()->getName()]))
            ->render();
        if ($canEdit) {
            A::tag()
                ->class('btn btn-outline-secondary btn-sm ms-2')
                ->content('Edit')
                ->url($url->generate('blog/author/post/edit', ['slug' => $slug]))
                ->render();
        }
        ?>
    </div>
<?php

echo Html::tag('article', $item->getContent(), ['class' => 'text-justify']);

if ($item->getTags()) {
    echo Div::tag()
        ->class('mt-3 mb-3')
        ->content(
            implode(
                '',
                array_map(
                    static fn(Tag $tag)
                        => A::tag()
                        ->class('btn btn-outline-secondary btn-sm mb-1 me-2')
                        ->content(Html::encode($tag->getLabel()))
                        ->url($url->generate('blog/tag', ['label' => $tag->getLabel()]))
                        ->encode(false)
                        ->render(),
                    $item->getTags(),
                ),
            ),
        )
        ->encode(false)
        ->render();
}
if ($commentator !== null) {
    echo <<<FORM
        <form id="commentAdd" method="POST"
            action="{$url->generate('blog/comment/add', ['slug' => $item->getSlug()])}"
             enctype="multipart/form-data"
        >
            <input type="hidden" name="_csrf" value="$csrf">
            <input name="post_id" type="hidden" value="{$item->getId()}">

            <div class="form-group mb-3">
                <label class="form-label" for="comment">{$translator->translate('blog.add.comment')}</label>
                <textarea minlength="3" required class="form-control" name="comment" type="text" rows="5"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">{$translator->translate('button.submit')}</button>
        </form>
        FORM;
}

echo Div::tag()->class('mt-4 text-muted')->content($translator->translate('blog.comments'))->render();
echo Div::tag()->class('mt-3')->open();

if ($item->getComments()) {
    foreach ($item->getComments() as $comment) {
        ?>
        <div class="media mt-4 shadow p-3 rounded">
            <div class="media-body">
                <div>
                    <?= A::tag()->content($comment->getCommentator()->getName())
                        ->url($url->generate('user/profile', ['login' => $comment->getCommentator()->getName()]))
                        ->render()
                    ?>
                    <span class="text-muted">
                        <i><?= $translator->translate('blog.created.at') ?></i>
                        <?= $comment->getCreatedAt()->format('H:i d.m.Y') ?>
                    </span>
                    <?php
                    if ($comment->isPublic()) { ?>
                        <span class="text-muted">
                            <i><?= $translator->translate('blog.updated.at') ?></i>
                            <?= $comment->getUpdatedAt()->format('H:i d.m.Y') ?>
                        </span>
                        <?php
                    } ?>
                    <span>
                        <?= $commentator !== null && $commentator->isEqual($comment->getCommentator())
                            ? A::tag()->content('Edit')
                                ->url($url->generate('blog/comment/edit', ['comment_id' => $comment->getId()]))
                                ->addAttributes(['class' => 'border border-info rounded px-2 text-muted'])
                                ->render()
                            : ''
                        ?>
                </span>
                </div>
                <div class="mt-1 text-justify">
                    <?= P::tag()->content(Html::encode($comment->getContent()))->render() ?>
                </div>
            </div>
        </div>
        <?php
    }
} else {
    echo P::tag()->class('lead')->content('No comments')->render();
}
echo Div::tag()->close();
