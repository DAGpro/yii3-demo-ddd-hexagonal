<?php

declare(strict_types=1);

/**
 * @var \App\Blog\Domain\Tag $tag
 * @var \Yiisoft\View\WebView $this
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Translator\Translator $translator
 * @var string $title
 * @var bool $canModerateComment
 */

use Yiisoft\Html\Html;

$this->setTitle($title);

?>

<div>
    <h2 class="tag mb-2"><?=$tag->getLabel()?></h2>
    <span class="text-muted"><?=$translator->translate('blog.created.by')?> <?= $tag->getCreatedAt()->format('H:i:s d.m.Y')?></span>
    <?php
    if ($canModerateComment) {
        echo Html::a(
            'Moderate Tag',
            $url->generate('backend/tag/moderate', ['tag_id' => $tag->getId()]),
            ['class' => 'btn btn-outline-secondary btn-sm ms-2']
        );
    }
    ?>

</div>
