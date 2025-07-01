<?php

declare(strict_types=1);

/**
 * @var User $item
 * @var UrlGeneratorInterface $url
 * @var WebView $this
 * @var TranslatorInterface $translator
 */

use App\IdentityAccess\User\Domain\User;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

$this->setTitle($item->getLogin());


?>
<div>
    <div class="border border-3 border-light mb-3 p-3">
        <?= Html::tag('h1', Html::encode($this->getTitle())) ?>
        <span class="text-muted">
            <?= $translator->translate('identityAccess.user.created.at')
            . $item->getCreatedAt()->format('H:i:s d.m.Y')
            ?>
        </span>
        <form class="mb-3 float-end" id="deleteUser" action="<?= $url->generate('user/cabinet/delete') ?>"
              method="post">
            <input type="hidden" name="_csrf" value="{$csrf}">
            <input type="hidden" name="user_id" value="{$item->getId()}">

            <button type="submit" class="btn btn-sm btn-danger">remove</button>
        </form>
    </div>

</div>
