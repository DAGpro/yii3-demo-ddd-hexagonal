<?php

declare(strict_types=1);


use App\IdentityAccess\User\Domain\User;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;


/**
 * @var User $item
 * @var Translator $translator
 * @var UrlGeneratorInterface $url
 * @var WebView $this
 * @var string $csrf
 */
$this->setTitle("User: " . Html::encode($item->getLogin()));

echo Html::tag('h1');
echo <<<DELETEUSER
    <div class="border border-3 border-light p-3 mb-3">
        <h1>
            {$this->getTitle()}
            <form
                class="mb-3 float-end" id="deleteApiToken"
                action="{$url->generate('backend/user/delete')}"
                method="post"
            >
                <input type="hidden" name="_csrf" value="{$csrf}">
                <input type="hidden" name="user_id" value="{$item->getId()}">

                <button type="submit" class="btn btn-sm btn-danger">
                    {$translator->translate('identityAccess.remove')}
                </button>
            </form>
        </h1>
    </div>
    DELETEUSER;

?>
<div class="main">
    <p class="text-muted">
        <?= $translator->translate('identityAccess.created.at') ?>
        <?= $item->getCreatedAt()->format('H:i:s d.m.Y') ?>
    </p>

    <p>
        <a href="<?= $url->generate('backend/access/user-assignments', ['user_id' => $item->getId()]) ?>"
           class="fw-bold">
            <?= $translator->translate('identityAccess.user.assignments') ?>
        </a>
    </p>

</div>
