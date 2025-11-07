<?php

declare(strict_types=1);

namespace App\Blog\Presentation\Frontend\View\Widget;

use App\Blog\Domain\Post;
use Override;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\A;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\P;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Widget\Widget;

final class AuthorPostCard extends Widget
{
    private array $options = [];

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
        private readonly Post $post,
    ) {
    }

    #[Override]
    public function render(): string
    {
        if (!isset($this->options['id'])) {
            $this->options['id'] = "{$this->post->getId()}-post-card";
        }

        $this->initOptions();

        return Div::tag()
            ->encode(false)
            ->class('card mb-3')
            ->content(
                Div::tag()
                    ->class('card-body d-flex flex-column align-items-start')
                    ->encode(false)
                    ->content(
                        $this->renderHead(),
                        $this->renderBody(),
                        $this->renderTags(),
                        A::tag()
                            ->content('Edit post')
                            ->url(
                                $this->urlGenerator->generate(
                                    'blog/author/post/edit',
                                    ['slug' => $this->post->getSlug()],
                                ),
                            )
                            ->class('btn btn-light')
                            ->render(),
                    ),
            )
            ->render();
    }

    /**
     * The HTML attributes for the widget container tag. The following special options are recognized.
     *
     * {@see Html::renderTagAttributes} for details on how attributes are being rendered.
     */
    public function options(array $value): self
    {
        $this->options = $value;

        return $this;
    }

    protected function renderHead(): string
    {
        return A::tag()
            ->content($this->post->getTitle())
            ->url(
                $this->urlGenerator->generate(
                    'blog/author/post/view',
                    ['slug' => $this->post->getSlug()],
                ),
            )
            ->encode(false)
            ->class('mb-0 h4 text-decoration-none')
            ->render();
    }

    protected function renderBody(): string
    {
        return Div::tag()
            ->class('card-text mb-auto')
            ->content(
                (($publishedAt = $this->post->getPublishedAt()) === null)
                    ? $this->translator->translate('blog.not.published.post')
                    : $this->translator->translate('blog.published.post',
                    ['date' => $publishedAt->format('M, d')],
                ),
                ' by ',
                A::tag()
                    ->class('mb-1 text-muted')
                    ->content($this->post->getAuthor()->getName())
                    ->url(
                        $this->urlGenerator->generate(
                            'user/profile',
                            ['login' => $this->post->getAuthor()->getName()],
                        ),
                    ),
                P::tag()
                    ->class('mt-3')
                    ->content(
                        mb_substr($this->post->getContent(), 0, 400)
                        . (mb_strlen($this->post->getContent()) > 400 ? '…' : ''),
                    ),
            )
            ->encode(false)
            ->render();
    }

    protected function renderTags(): string
    {
        $tags = [];
        foreach ($this->post->getTags() as $tag) {
            $tags[] = A::tag()
                ->content($tag->getLabel())
                ->url($this->urlGenerator->generate('blog/tag', ['label' => $tag->getLabel()]))
                ->class('btn btn-outline-secondary btn-sm mb-1 me-2 mt-1')
                ->encode(false)
                ->render();
        }

        return Div::tag()
            ->class('mt-3 mb-2')
            ->encode(false)
            ->content(
                ...$tags,
            )
            ->render();
    }

    protected function initOptions(): void
    {
        Html::addCssClass($this->options, ['class' => 'card mb-4']);
    }
}
