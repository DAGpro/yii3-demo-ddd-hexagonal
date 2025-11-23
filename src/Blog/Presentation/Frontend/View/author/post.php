<?php

declare(strict_types=1);


use App\Blog\Domain\Post;
use App\Blog\Domain\User\Author;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\Article;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\H1;
use Yiisoft\Html\Tag\Span;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

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
$this->setTitle($post->getTitle());

echo Article::tag()
    ->class('card mb-3 text-justify')
    ->content(
        Div::tag()
            ->class('card-header')
            ->encode(false)
            ->content(
                H1::tag()->content($post->getTitle()),
                Div::tag()
                    ->class('mb-1')
                    ->content(
                        Span::tag()
                            ->class('text-muted')
                            ->content(
                                $post->getPublishedAt() === null
                                    ? $translator->translate('blog.not.published.post')
                                    : $translator->translate(
                                        'blog.published.post',
                                        ['date' => $post->getPublishedAt()?->format('H:i:s d.m.Y')],
                                    ),
                            ),
                        A::tag()
                            ->class('mr-3')
                            ->content($post->getAuthor()->getName())
                            ->url($url->generate('user/profile', ['login' => $post->getAuthor()->getName()]))
                            ->encode(false)
                            ->render(),
                    )
                    ->encode(false)
                    ->render(),
            ),
        Div::tag()
            ->class('card-body')
            ->content(
                Div::tag()
                    ->class('mb-3')
                    ->content($post->getContent()),
            )
            ->encode(false)
            ->render(),
        Div::tag()
            ->class('card-footer')
            ->encode(false)
            ->content(
                A::tag()
                    ->class('btn btn-outline-secondary')
                    ->content('Edit')
                    ->url($url->generate('blog/author/post/edit', ['slug' => $post->getSlug()]))
                    ->encode(false)
                    ->render(),
            ),
    )
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
        ->class('mb-3')
        ->content($tagLinks)
        ->encode(false)
        ->render();
}
