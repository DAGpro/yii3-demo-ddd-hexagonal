<?php

declare(strict_types=1);

/**
 * @var \App\Core\Component\Blog\Domain\Post $post
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Translator\TranslatorInterface $translator
 * @var \Yiisoft\View\WebView $this
 * @var \App\Core\Component\Blog\Domain\User\Author $author
 * @var bool $canAddComment
 * @var string $csrf
 * @var string $slug
 */

use Yiisoft\Html\Html;
use Yiisoft\Yii\Bootstrap5\Alert;

$this->setTitle($post->getTitle());

if (!empty($errors)) {
    foreach ($errors as $field => $error) {
        echo Alert::widget()->options(['class' => 'alert-danger'])->body(Html::encode($field . ':' . $error));
    }
}

?>
    <h1><?= Html::encode($post->getTitle()) ?></h1>
    <div>
        <span class="text-muted"><?= $post->getPublishedAt() === null
                ? $translator->translate('blog.not.published.post')
                : $post->getPublishedAt()->format('H:i:s d.m.Y') ?> by</span>
        <?php
        echo Html::a(
            $post->getAuthor()->getName(),
            $url->generate('user/profile', ['login' => $post->getAuthor()->getName()]),
            ['class' => 'mr-3']
        );

        echo Html::a(
            'Edit',
            $url->generate('blog/author/post/edit', ['slug' => $post->getSlug()]),
            ['class' => 'btn btn-outline-secondary btn-sm ms-2']
        );
        ?>
    </div>
<?php

echo Html::tag('article', $post->getContent(), ['class' => 'text-justify']);

if ($post->getTags()) {
    echo Html::openTag('div', ['class' => 'mt-3 mb-3']);
    foreach ($post->getTags() as $tag) {
        echo Html::a(
            Html::encode($tag->getLabel()),
            $url->generate('blog/tag', ['label' => $tag->getLabel()]),
            ['class' => 'btn btn-outline-secondary btn-sm mb-1 me-2']
        );
    }
    echo Html::closeTag('div');
}
