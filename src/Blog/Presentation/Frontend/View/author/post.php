<?php

declare(strict_types=1);

/**
 * @var Post $post
 * @var UrlGeneratorInterface $url
 * @var TranslatorInterface $translator
 * @var WebView $this
 * @var Author $author
 * @var bool $canAddComment
 * @var string $csrf
 * @var string $slug
 */

use App\Blog\Domain\Post;
use App\Blog\Domain\User\Author;
use Yiisoft\Bootstrap5\Alert;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\Article;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

$this->setTitle($post->getTitle());

if (!empty($errors)) {
    foreach ($errors as $field => $error) {
        echo Alert::widget()->addAttributes(['class' => 'alert-danger'])->body(Html::encode($field . ':' . $error));
    }
}

?>
    <h1><?= Html::encode($post->getTitle()) ?></h1>
    <div>
        <span class="text-muted"><?= $post->getPublishedAt() === null
                ? $translator->translate('blog.not.published.post')
                : $translator->translate('blog.published.post',
                    ['date' => $post->getPublishedAt()->format('H:i:s d.m.Y')],
                ) ?> by</span>
        <?php
        echo A::tag()
            ->class('mr-3')
            ->content($post->getAuthor()->getName())
            ->url($url->generate('user/profile', ['login' => $post->getAuthor()->getName()]))
            ->encode(false)
            ->render();

        echo A::tag()
            ->class('btn btn-outline-secondary btn-sm ms-2')
            ->content('Edit')
            ->url($url->generate('blog/author/post/edit', ['slug' => $post->getSlug()]))
            ->encode(false)
            ->render();
        ?>
    </div>
<?php

echo Article::tag()
    ->content($post->getContent())
    ->class('text-justify')
    ->encode(false)
    ->render();

if ($post->getTags()) {
    $tagLinks = '';
    foreach ($post->getTags() as $tag) {
        $tagLinks .= A::tag()
            ->class('btn btn-outline-secondary btn-sm mb-1 me-2')
            ->content(Html::encode($tag->getLabel()))
            ->url($url->generate('blog/tag', ['label' => $tag->getLabel()]))
            ->encode(false)
            ->render();
    }

    echo Div::tag()
        ->class('mt-3 mb-3')
        ->content($tagLinks)
        ->encode(false)
        ->render();
}
