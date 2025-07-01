<?php

declare(strict_types=1);

use Yiisoft\Form\Widget\Field;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var UrlGeneratorInterface $url
 * @var Field $field
 * @var Translator $translator
 * @var string $csrf
 * @var string|null $currentUrl
 */


function navElement(string $url, string $name): string
{
    return <<<LI
        <li class="nav-item">
            <a class="nav-link" href="{$url}">$name</a>
        </li>
        LI;
}

$roles = navElement($url->generate('backend/access'), $translator->translate('identityAccess.roles'));
$permissions = navElement($url->generate('backend/access/permissions'),
    $translator->translate('identityAccess.permissions'),
);
$assignments = navElement($url->generate('backend/access/assignments'),
    $translator->translate('identityAccess.assignments'),
);

if ($currentUrl !== null) {
    $currentAccess = ucfirst($translator->translate('identityAccess.' . $currentUrl));
    ${$currentUrl} = <<<LI
        <li class="nav-item">
            <a class="nav-link disabled" href="#">{$currentAccess}</a>
        </li>
        LI;
}

?>

<ul class="nav mb-3 border border-2 border-light">
    <?= $roles, $permissions, $assignments ?>
</ul>
