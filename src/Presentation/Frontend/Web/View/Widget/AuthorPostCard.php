<?php

declare(strict_types=1);

namespace App\Presentation\Frontend\Web\View\Widget;

use App\Blog\Domain\Post;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\Bootstrap5\Widget;

final class AuthorPostCard extends Widget
{
    private ?Post $post = null;

    private array $options = [];

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    protected function run(): string
    {
        if (!isset($this->options['id'])) {
            $this->options['id'] = "{$this->getId()}-post-card";
        }

        $this->initOptions();

        return implode("\n", [
            Html::openTag('div', $this->options),
            Html::openTag('div', ['class' => 'card-body d-flex flex-column align-items-start']),
            $this->renderHead(),
            $this->renderBody(),
            $this->renderTags(),
            Html::a('Edit post',
                $this->urlGenerator->generate(
                    'blog/author/post/edit',
                    ['slug' => $this->post->getSlug()]
                ),
                ['class' => 'btn btn-light'],
            ),
            Html::closeTag('div'),
            Html::closeTag('div'),
        ]);
    }

    protected function renderHead(): string
    {
        return Html::a(
            $this->post->getTitle(),
            $this->urlGenerator->generate('blog/author/post/view', ['slug' => $this->post->getSlug()]),
            ['class' => 'mb-0 h4 text-decoration-none'] // stretched-link
        )
        ->render();
    }

    protected function renderBody(): string
    {
        $return = Html::openTag('div', ['class' => 'card-text mb-auto']);
        $return .= $this->post->getPublishedAt() === null
            ? 'not published'
            : $this->post->getPublishedAt()->format('M, d');
        $return .= ' by ';
        $return .= Html::a(
            $this->post->getAuthor()->getName(),
            $this->urlGenerator->generate('user/profile', ['login' => $this->post->getAuthor()->getName()])
        )->class('mb-1 text-muted');

        $return .= Html::p(
            mb_substr($this->post->getContent(), 0, 400)
            . (mb_strlen($this->post->getContent()) > 400 ? '…' : ''),
        );
        return $return . Html::closeTag('div');
    }

    protected function renderTags(): string
    {
        $return = Html::openTag('div', ['class' => 'mt-3 mb-2']);
        foreach ($this->post->getTags() as $tag) {
            $return .= Html::a(
                $tag->getLabel(),
                $this->urlGenerator->generate('blog/tag', ['label' => $tag->getLabel()]),
                ['class' => 'btn btn-outline-secondary btn-sm mb-1 me-2 mt-1']
            );
        }
        return $return . Html::closeTag('div');
    }

    protected function initOptions(): void
    {
        Html::addCssClass($this->options, ['widget' => 'card mb-4']);
    }

    public function post(?Post $post): self
    {
        $this->post = $post;

        if ($post !== null) {
            $this->options['data']['post-slug'] = $post->getSlug();
        }

        return $this;
    }

    /**
     * The HTML attributes for the widget container tag. The following special options are recognized.
     *
     * {@see \Yiisoft\Html\Html::renderTagAttributes()} for details on how attributes are being rendered.
     */
    public function options(array $value): self
    {
        $this->options = $value;

        return $this;
    }
}
