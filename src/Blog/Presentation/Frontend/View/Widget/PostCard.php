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
use Yiisoft\Widget\Widget;

final class PostCard extends Widget
{
    private array $options = [];

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Post $post,
    ) {}

    #[Override]
    public function render(): string
    {
        if (!isset($this->options['id'])) {
            $this->options['id'] = "{$this->post->getId()}-post-card";
        }

        $this->initOptions();

        return Div::tag()
            ->content(
                Div::tag()
                    ->content(
                        $this->renderHead(),
                        $this->renderBody(),
                        $this->renderTags(),
                    )
                    ->encode(false)
                    ->class('card-body d-flex flex-column align-items-start'),
            )
            ->addAttributes($this->options)
            ->encode(false)
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
                    'blog/post',
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
            ->content(
                (($publishedAt = $this->post->getPublishedAt()) === null)
                    ? 'not published'
                    : $publishedAt->format('M, d'),
                ' by ',
                A::tag()
                    ->content($this->post->getAuthor()->getName())
                    ->url(
                        $this->urlGenerator->generate(
                            'user/profile',
                            ['login' => $this->post->getAuthor()->getName()],
                        ),
                    ),
                P::tag()
                    ->content(
                        mb_substr($this->post->getContent(), 0, 400)
                        . (mb_strlen($this->post->getContent()) > 400 ? '…' : ''),
                    ),
            )
            ->class('card-text mb-auto')
            ->encode(false)
            ->render();
    }

    protected function renderTags(): string
    {
        $links = [];
        foreach ($this->post->getTags() as $tag) {
            $links[] = A::tag()
                ->content(
                    $tag->getLabel(),
                )
                ->url(
                    $this->urlGenerator->generate(
                        'blog/tag',
                        ['label' => $tag->getLabel()],
                    ),
                )
                ->class('btn btn-outline-secondary btn-sm mb-1 me-2 mt-1')
                ->encode(false);
        }

        return Div::tag()
            ->content(
                ...$links,
            )
            ->class('mt-3')
            ->encode(false)
            ->render();
    }

    protected function initOptions(): void
    {
        Html::addCssClass($this->options, ['class' => 'post-preview card mb-4']);
    }
}
