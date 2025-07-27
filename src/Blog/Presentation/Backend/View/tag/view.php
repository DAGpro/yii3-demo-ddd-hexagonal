<?php

declare(strict_types=1);


use App\Blog\Domain\Tag;
use Yiisoft\Html\Tag\A;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;

/**
 * @var Tag $tag
 * @var WebView $this
 * @var UrlGeneratorInterface $url
 * @var Translator $translator
 * @var string $title
 * @var bool $canModerateTag
 */

/** @psalm-scope-this Yiisoft\View\WebView */
$this->setTitle($title);

?>

<div>
    <h2 class="tag mb-2"><?= $tag->getLabel() ?></h2>
    <span class="text-muted">
        <?= $translator->translate('blog.created.by') ?> <?= $tag->getCreatedAt()->format('H:i:s d.m.Y') ?>
    </span>
    <?php
    if ($canModerateTag) {
        echo A::tag()
            ->content('Moderate Tag')
            ->url($url->generate('backend/tag/moderate', ['tag_id' => $tag->getId()]))
            ->class('btn btn-outline-secondary btn-sm ms-2')
            ->render();
    }
    ?>

</div>
