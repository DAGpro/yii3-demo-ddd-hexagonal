<?php

declare(strict_types=1);

/**
 * @var \App\Core\Component\Blog\Domain\Post $item
 * @var \Yiisoft\Translator\TranslatorInterface $translator
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\View\WebView $this
 * @var bool $canEdit
 * @var \App\Core\Component\Blog\Domain\User\Commentator $commentator
 * @var string $csrf
 * @var string $slug
 */

use Yiisoft\Html\Html;
use Yiisoft\Yii\Bootstrap5\Alert;

$this->setTitle($item->getTitle());

if (!empty($errors)) {
    foreach ($errors as $field => $error) {
        echo Alert::widget()->options(['class' => 'alert-danger'])->body(Html::encode($field . ':' . $error));
    }
}

?>
    <h1><?= Html::encode($item->getTitle()) ?></h1>
    <div>
        <span class="text-muted"><?= $item->getPublishedAt() === null
                ? 'not published'
                : $translator->translate('blog.published.post', ['date' => $item->getPublishedAt()->format('H:i:s d.m.Y')]) ?> by</span>
        <?php
        echo Html::a(
            $item->getAuthor()->getName(),
            $url->generate('user/profile', ['login' => $item->getAuthor()->getName()]),
            ['class' => 'mr-3']
        );
        if ($canEdit) {
            echo Html::a(
                'Edit',
                $url->generate('blog/author/post/edit', ['slug' => $slug]),
                ['class' => 'btn btn-outline-secondary btn-sm ms-2']
            );
        }
        ?>
    </div>
<?php

echo Html::tag('article', $item->getContent(), ['class' => 'text-justify']);

if ($item->getTags()) {
    echo Html::openTag('div', ['class' => 'mt-3 mb-3']);
    foreach ($item->getTags() as $tag) {
        echo Html::a(
            Html::encode($tag->getLabel()),
            $url->generate('blog/tag', ['label' => $tag->getLabel()]),
            ['class' => 'btn btn-outline-secondary btn-sm mb-1 me-2']
        );
    }
    echo Html::closeTag('div');
}
if ($commentator !== null){
    echo <<<FORM
        <form id="commentAdd" method="POST" action="{$url->generate('blog/comment/add', ['slug' => $item->getSlug()])}" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="{$csrf}">
            <input name="post_id" type="hidden" value="{$item->getId()}">

            <div class="form-group mb-3">
                <label class="form-label" for="comment">{$translator->translate('blog.add.comment')}</label>
                <textarea minlength="3" required class="form-control" name="comment" type="text" rows="5"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">{$translator->translate('button.submit')}</button>
        </form>
    FORM;
}

echo Html::tag('h2', $translator->translate('blog.comments'), ['class' => 'mt-4 text-muted']);
echo Html::openTag('div', ['class' => 'mt-3']);

if ($item->getComments()) {
    foreach ($item->getComments() as $comment) {
        ?>
        <div class="media mt-4 shadow p-3 rounded">
            <div class="media-body">
                <div>
                    <?= Html::a(
                        $comment->getCommentator()->getName(),
                        $url->generate('user/profile', ['login' => $comment->getCommentator()->getName()])
                    ) ?>
                    <span class="text-muted">
                        <i><?=$translator->translate('blog.created.at')?></i> <?= $comment->getCreatedAt()->format('H:i d.m.Y') ?>
                    </span>
                    <?php if ($comment->isPublic()) { ?>
                        <span class="text-muted">
                            <i><?=$translator->translate('blog.updated.at')?></i> <?= $comment->getUpdatedAt()->format('H:i d.m.Y') ?>
                        </span>
                    <?php } ?>
                    <span>
                        <?= $commentator !== null && $commentator->isEqual($comment->getCommentator())
                            ? Html::a(
                                'Edit',
                                $url->generate('blog/comment/edit', ['comment_id' => $comment->getId()]),
                                ['class' => 'border border-info rounded px-2 text-muted']
                            ) : ''
                        ?>
                </span>
                </div>
                <div class="mt-1 text-justify">
                    <?= Html::encode($comment->getContent()) ?>
                </div>
            </div>
        </div>
        <?php
    }
} else {
    echo Html::p('No comments', ['class' => 'lead']);
}
echo Html::closeTag('div');
