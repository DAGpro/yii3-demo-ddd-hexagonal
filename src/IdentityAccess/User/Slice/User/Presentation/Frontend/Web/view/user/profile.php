<?php

declare(strict_types=1);

use App\IdentityAccess\User\Domain\User;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\H1;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\View\WebView;

/**
 * @var User $item
 * @var UrlGeneratorInterface $url
 * @var WebView $this
 */

$this->setTitle($item->getLogin());

echo H1::tag()
    ->content(Html::encode($this->getTitle()))
    ->render();
?>
<div>
    <span class="text-muted">Created at <?= $item->getCreatedAt()->format('H:i:s d.m.Y') ?></span>
</div>
