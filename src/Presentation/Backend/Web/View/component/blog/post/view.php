<?php

declare(strict_types=1);

/**
 * @var \App\Core\Component\Blog\Domain\Post $post
 * @var \Yiisoft\View\WebView $this
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Form\Widget\Field $field
 * @var \Yiisoft\Translator\Translator $translator
 * @var string $csrf
 * @var string $slug
 */

use Yiisoft\Html\Html;

$this->setTitle($translator->translate('blog.view.post'). ' : Id №' . $post->getId());

?>

<div>
    <div class="post mb-4">
        <h1 class="mb-3"><?=$this->getTitle()?></h1>
        <div class="article p-3 border border-light border-2">
            <h2><?=$post->getTitle()?></h2>
            <p><small><?=$translator->translate('blog.author')?></small> <?=Html::a($post->getAuthor()->getName(),
                    $url->generate(
                        'backend/user/profile',
                        ['user_id' => $post->getAuthor()->getId()]
                    )
                )?>
            </p>
            <div class="content mb-4">
                <?=$post->getContent()?>
            </div>

            <div id="tags">
                <?php foreach ($post->getTags()as $tag) : ?>
                    <a href="<?=$url->generate('backend/tag/change', ['tag_id' => $tag->getId()])?>" class="btn btn-sm btn-info mb-2 me-2">
                        <?= Html::encode($tag->getLabel()) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <p>
                <span class="text-muted">
                    <?= $post->isPublic() === false
                        ? $translator->translate('blog.draft.post')
                        : $translator->translate('blog.published.post')
                    ?>
                </span>
            </p>
        </div>
    </div>

    <div class="btn-group" role="group">
        <?php
        if ($post->isPublic()) {
            echo <<<FORM
                <form id="draftPost" method="POST" action="{$url->generate('backend/post/draft', ['post_id' => $post->getId()])}" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="{$csrf}">
                    <input name="post_id" type="hidden" value="{$post->getId()}">
                    <button type="submit" class="btn btn-sm btn-primary">{$translator->translate('blog.draft.post')}</button>
                </form>
            FORM;
        } else {
            echo <<<FORM
                <form id="publicPost" method="POST" action="{$url->generate('backend/post/public', ['post_id' => $post->getId()])}" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="{$csrf}">
                    <input name="post_id" type="hidden" value="{$post->getId()}">
                    <button type="submit" class="btn btn-sm btn-success">{$translator->translate('blog.public.post')}</button>
                </form>
            FORM;
        }
        echo Html::a(
            $translator->translate('blog.moderate.post'),
            $url->generate('backend/post/moderate', ['post_id' => $post->getId()]),
            ['class' => 'btn btn-sm btn-warning']
        );
        echo <<<FORM
            <form id="deletePost" method="POST" action="{$url->generate('backend/post/delete', ['post_id' => $post->getId()])}" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="{$csrf}">
                <input name="post_id" type="hidden" value="{$post->getId()}">
                <button type="submit" class="btn btn-sm btn-danger">{$translator->translate('blog.delete.post')}</button>
            </form>
        FORM;
        ?>
    </div>
</div>
