<?php

declare(strict_types=1);

use App\Presentation\Infrastructure\Web\Asset\AppAsset;
use App\Presentation\Infrastructure\Web\Widget\FlashMessage;
use App\Presentation\Infrastructure\Web\Widget\PerformanceMetrics;
use Yiisoft\Form\Widget\Field;
use Yiisoft\Form\Widget\Form;
use Yiisoft\Html\Html;
use Yiisoft\Strings\StringHelper;
use Yiisoft\Yii\Bootstrap5\Nav;
use Yiisoft\Yii\Bootstrap5\NavBar;

/**
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Router\CurrentRoute $currentRoute
 * @var \Yiisoft\View\WebView $this
 * @var \Yiisoft\Assets\AssetManager $assetManager
 * @var \Yiisoft\Translator\TranslatorInterface $translator
 * @var string $content
 *
 * @see \App\ApplicationViewInjection
 * @var \App\Core\Component\IdentityAccess\User\Domain\User|null $user
 * @var string $csrf
 * @var string $brandLabel
 */

$assetManager->register(AppAsset::class);

$this->addCssFiles($assetManager->getCssFiles());
$this->addCssStrings($assetManager->getCssStrings());
$this->addJsFiles($assetManager->getJsFiles());
$this->addJsStrings($assetManager->getJsStrings());
$this->addJsVars($assetManager->getJsVars());

$currentRouteName = $currentRoute->getName() ?? '';

$this->beginPage();
?>
    <!DOCTYPE html>
    <html class="h-100" lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Yii Demo<?= $this->getTitle() ? ' - ' . Html::encode($this->getTitle()) : '' ?></title>
        <?php $this->head() ?>
    </head>
    <body class="cover-container-fluid d-flex w-100 h-100 mx-auto flex-column">
    <header class="mb-auto">
        <?php $this->beginBody() ?>

        <?= NavBar::widget()
            ->brandText($brandLabel)
            ->brandUrl($url->generate('backend/dashboard'))
            ->options(['class' => 'navbar navbar-light bg-light navbar-expand-sm text-white'])
            ->begin() ?>

        <?= Nav::widget()
            ->currentPath($currentRoute->getUri()->getPath())
            ->options(['class' => 'navbar-nav mx-auto'])
            ->items(
                [
                    ['label' => $translator->translate('backend_menu.blog'), 'url' => '',
                        'items' => [
                            ['label' => $translator->translate('backend_menu.posts'), 'url' => $url->generate('backend/post'), 'active' => StringHelper::startsWith($currentRouteName, 'backend/post')],
                            ['label' => $translator->translate('backend_menu.comments'), 'url' => $url->generate('backend/comment'), 'active' => StringHelper::startsWith($currentRouteName, 'backend/comment')],
                            ['label' => $translator->translate('backend_menu.tags'), 'url' => $url->generate('backend/tag'), 'active' => StringHelper::startsWith($currentRouteName, 'backend/tag')],
                        ]
                    ],
                    ['label' => $translator->translate('backend_menu.identityAccess'), 'url' => '',
                        'items' => [
                            ['label' => $translator->translate('backend_menu.accessRights'), 'url' => $url->generate('backend/access'), 'active' => StringHelper::startsWith($currentRouteName, 'backend/access')],
                            ['label' => $translator->translate('backend_menu.user'), 'url' => $url->generate('backend/user'), 'active' => StringHelper::startsWith($currentRouteName, 'backend/user')],
                        ]
                    ],
                    ['label' => $translator->translate('menu.frontend'), 'url' => $url->generate('site/index'), ],
                ]
            ) ?>

        <?= Nav::widget()
            ->currentPath($currentRoute->getUri()->getPath())
            ->options(['class' => 'navbar-nav'])
            ->items(
                $user === null || $user->getId() === null
                    ? [
                        [
                            'label' => $translator->translate('menu.login'),
                            'url' => $url->generate('auth/login'),
                        ],
                        [
                            'label' => $translator->translate('menu.signup'),
                            'url' => $url->generate('auth/signup'),
                        ],
                        [
                            'label' => $translator->translate('menu.language'),
                            'url' => '#',
                            'items' => [
                                [
                                    'label' => $translator->translate('layout.language.english'),
                                    'url' => $url->generate($currentRouteName,  array_merge($currentRoute->getArguments(), ['_language' => 'en'])),
                                ],
                                [
                                    'label' => $translator->translate('layout.language.russian'),
                                    'url' => $url->generate($currentRouteName, array_merge($currentRoute->getArguments(), ['_language' => 'ru'])),
                                ],
                            ]
                        ]
                    ]
                    : [
                        [
                            'label' => $translator->translate('menu.language'),
                            'url' => '#',
                            'items' => [
                                [
                                    'label' => $translator->translate('layout.language.english'),
                                    'url' => $url->generate($currentRouteName,  array_merge($currentRoute->getArguments(), ['_language' => 'en'])),
                                ],
                                [
                                    'label' => $translator->translate('layout.language.russian'),
                                    'url' => $url->generate($currentRouteName, array_merge($currentRoute->getArguments(), ['_language' => 'ru'])),
                                ],
                            ]
                        ],
                        Form::widget()
                            ->action($url->generate('auth/logout'))
                            ->csrf($csrf)
                            ->begin()
                        . Field::widget()
                            ->containerClass('mb-1')
                            ->submitButton(
                                [
                                    'class' => 'btn btn-primary',
                                    'value' => $translator->translate(
                                        'menu.logout',
                                        ['login' => Html::encode($user->getLogin())],
                                    ),
                                ],
                            )
                        . Form::end()
                    ],
            ) ?>
        <?= NavBar::end() ?>
    </header>

    <main class="container py-3">
        <?= FlashMessage::widget()?>
        <?= $content ?>
    </main>

    <footer class='mt-auto bg-dark py-3'>
        <div class = 'd-flex flex-fill align-items-center container-fluid'>
            <div class = 'd-flex flex-fill float-start'>
                <i class=''></i>
                <a class='text-decoration-none' href='https://www.yiiframework.com/' target='_blank' rel='noopener'>
                    Yii Framework - <?= date('Y') ?> -
                </a>
                <div class="ms-2 text-white">
                    <?= PerformanceMetrics::widget() ?>
                </div>
            </div>

            <div class='float-end'>
                <a class='text-decoration-none px-1' href='https://github.com/yiisoft' target='_blank' rel='noopener' >
                    <i class="bi bi-github text-white"></i>
                </a>
                <a class='text-decoration-none px-1' href='https://join.slack.com/t/yii/shared_invite/enQtMzQ4MDExMDcyNTk2LTc0NDQ2ZTZhNjkzZDgwYjE4YjZlNGQxZjFmZDBjZTU3NjViMDE4ZTMxNDRkZjVlNmM1ZTA1ODVmZGUwY2U3NDA' target='_blank' rel='noopener'>
                    <i class="bi bi-slack text-white"></i>
                </a>
                <a class='text-decoration-none px-1' href='https://www.facebook.com/groups/yiitalk' target='_blank' rel='noopener'>
                    <i class="bi bi-facebook text-white"></i>
                </a>
                <a class='text-decoration-none px-1' href='https://twitter.com/yiiframework' target='_blank' rel='noopener'>
                    <i class="bi bi-twitter text-white"></i>
                </a>
                <a class='text-decoration-none px-1' href='https://t.me/yii3ru' target='_blank' rel='noopener'>
                    <i class="bi bi-twitter text-white"></i>
                </a>
            </div>
        </div>
    </footer>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php
$this->endPage(true);
