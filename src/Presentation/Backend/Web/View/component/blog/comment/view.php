<?php

declare(strict_types=1);

/**
 * @var \App\Core\Component\Blog\Domain\Comment $comment
 * @var \Yiisoft\View\WebView $this
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Form\Widget\Field $field
 * @var \Yiisoft\Translator\Translator $translator
 * @var string $title
 * @var string $csrf
 */

use Yiisoft\Html\Html;

$this->setTitle($translator->translate('blog.view.comment') . 'id №' . $comment->getId());

?>

<div>
    <h1><?=$this->getTitle()?></h1>
    <div class="comment mb-2 p-2 border border-light border-2">
        <?=$comment->getContent()?>
    </div>
    <p>
        <span class="text-muted">
            <?= $comment->getPublishedAt() === null
                ? $translator->translate('blog.not.published.comment')
                : $translator->translate('blog.published.comment') . $comment->getPublishedAt()->format('H:i:s d.m.Y') ?>
        </span>
        <?php
        echo $translator->translate('blog.commentator') . Html::a(
            $comment->getCommentator()->getName(),
            $url->generate('backend/user/profile', ['user_id' => $comment->getCommentator()->getId()]),
            ['class' => 'mr-3']
        );
        ?>
    </p>


    <div class="btn-group mb-3" role="group">
        <?php
        if ($comment->isPublic()) {
            echo <<<FORM
                <form id="draftComment" method="POST" action="{$url->generate('backend/comment/draft', ['comment_id' => $comment->getId()])}" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="{$csrf}">
                    <input name="comment_id" type="hidden" value="{$comment->getId()}">
                    <button type="submit" class="btn btn-sm btn-primary">{$translator->translate('blog.draft.comment')}</button>
                </form>
            FORM;
        } else {
            echo <<<FORM
                <form id="publicComment" method="POST" action="{$url->generate('backend/comment/public', ['comment_id' => $comment->getId()])}" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="{$csrf}">
                    <input name="comment_id" type="hidden" value="{$comment->getId()}">
                    <button type="submit" class="btn btn-sm btn-success">{$translator->translate('blog.public.comment')}</button>
                </form>
            FORM;
        }
        echo Html::a(
            $translator->translate('blog.moderate.comment'),
            $url->generate('backend/comment/moderate', ['comment_id' => $comment->getId()]),
            ['class' => 'btn btn-sm btn-warning']
        );
        echo <<<FORM
            <form id="deleteComment" method="POST" action="{$url->generate('backend/comment/delete', ['comment_id' => $comment->getId()])}" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="{$csrf}">
                <input name="comment_id" type="hidden" value="{$comment->getId()}">
                <button type="submit" class="btn btn-sm btn-danger">{$translator->translate('blog.delete.comment')}</button>
            </form>
        FORM;

        ?>
    </div>
</div>
