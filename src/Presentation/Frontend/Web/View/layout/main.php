<?php

declare(strict_types=1);

use App\Presentation\Infrastructure\Web\Asset\AppAsset;
use App\Presentation\Infrastructure\Web\Widget\FlashMessage;
use App\Presentation\Infrastructure\Web\Widget\PerformanceMetrics;
use Yiisoft\Form\Widget\Form;
use Yiisoft\Html\Html;
use Yiisoft\Strings\StringHelper;
use Yiisoft\Yii\Bootstrap5\Nav;
use Yiisoft\Yii\Bootstrap5\NavBar;
use Yiisoft\Form\Widget\Field;

/**
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Router\CurrentRoute $currentRoute
 * @var \Yiisoft\View\WebView $this
 * @var \Yiisoft\Assets\AssetManager $assetManager
 * @var \Yiisoft\Translator\TranslatorInterface $translator
 * @var string $content
 *
 * @see \App\ApplicationViewInjection
 * @var \App\IdentityAccess\User\Domain\User|null $user
 * @var string $csrf
 * @var string $brandLabel
 * @var bool $canAddPost
 */

$assetManager->register(AppAsset::class);

$this->addCssFiles($assetManager->getCssFiles());
$this->addCssStrings($assetManager->getCssStrings());
$this->addJsFiles($assetManager->getJsFiles());
$this->addJsStrings($assetManager->getJsStrings());
$this->addJsVars($assetManager->getJsVars());

$currentRouteName = $currentRoute->getName() ?? '';
$isGuest = $user === null || $user->getId() === null;

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
                ->brandUrl($url->generate('site/index'))
                ->options(['class' => 'navbar navbar-light bg-light navbar-expand-sm text-white'])
                ->begin() ?>

            <?= Nav::widget()
                ->currentPath($currentRoute->getUri()->getPath())
                ->options(['class' => 'navbar-nav mx-auto'])
                ->items(
                    [
                        [
                            'label' => $translator->translate('menu.blog'),
                            'url' => $url->generate('blog/index'),
                            'active' => StringHelper::startsWith(
                                $currentRouteName,
                                'blog/'
                            ) && $currentRouteName !== 'blog/comment/index',
                        ],
                        [
                            'label' => $translator->translate('menu.comments-feed'),
                            'url' => $url->generate('blog/comment/index'),
                        ],
                        [
                            'label' => $translator->translate('menu.users'),
                            'url' => $url->generate('user/index'),
                            'active' => StringHelper::startsWith($currentRouteName, 'user/'),
                        ],
                        [
                            'label' => $translator->translate('menu.contact'),
                            'url' => $url->generate('site/contact'),
                        ],
                        [
                            'label' => $translator->translate('menu.swagger'),
                            'url' => $url->generate('swagger/index'),
                        ],
                        ['label' => $translator->translate('menu.backend'), 'url' => $url->generate('backend/dashboard'), ],
                    ]
                ) ?>

            <?= Nav::widget()
                ->currentPath($currentRoute->getUri()->getPath())
                ->options(['class' => 'navbar-nav'])
                ->items(
                    [
                        [
                            'label' => $translator->translate('menu.language'),
                            'url' => '#',
                            'items' => [
                                [
                                    'label' => $translator->translate('layout.language.english'),
                                    'url' => $url->generateFromCurrent(['_language' => 'en'], 'site/index'),
                                ],
                                [
                                    'label' => $translator->translate('layout.language.russian'),
                                    'url' => $url->generateFromCurrent(['_language' => 'ru'], 'site/index'),
                                ],
                            ],
                        ],
                        [
                            'label' => $translator->translate('menu.login'),
                            'url' => $url->generate('auth/login'),
                            'visible' => $isGuest,
                        ],
                        [
                            'label' => $translator->translate('menu.signup'),
                            'url' => $url->generate('auth/signup'),
                            'visible' => $isGuest,
                        ],
                        $user === null
                            ? ''
                            :
                            ['label' => Html::encode($user->getLogin()),
                                'url' => '',
                                'items' =>
                                    [
                                        $canAddPost ? ['label' => 'Posts', 'url' => $url->generate('blog/author/posts', ['author' => $user->getLogin()])] : '',
                                        ['label' => 'Cabinet', 'url' => $url->generate('user/cabinet')],
                                        Form::widget()
                                            ->action($url->generate('auth/logout'))
                                            ->csrf($csrf)
                                            ->begin()
                                        . Field::widget()
                                            ->attributes(['class' => 'dropdown-item'])
                                            ->containerClass('mb-1')
                                            ->submitButton()
                                            ->value($translator->translate('menu.logout', ['login' => Html::encode($user->getLogin())]))
                                        . Form::end(),
                                    ],
                            ],
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
                        <i class="bi bi-telegram text-white"></i>
                    </a>
                </div>
            </div>
        </footer>

        <?php $this->endBody() ?>
    </body>
</html>
<?php
$this->endPage(true);
