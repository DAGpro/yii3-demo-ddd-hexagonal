<?php

declare(strict_types=1);

use App\IdentityAccess\User\Domain\User;
use App\Infrastructure\Presentation\Web\Asset\AppAsset;
use App\Infrastructure\Presentation\Web\Widget\FlashMessage;
use App\Infrastructure\Presentation\Web\Widget\PerformanceMetrics;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Bootstrap5\Dropdown;
use Yiisoft\Bootstrap5\DropdownAlignment;
use Yiisoft\Bootstrap5\DropdownItem;
use Yiisoft\Bootstrap5\Nav;
use Yiisoft\Bootstrap5\NavBar;
use Yiisoft\Bootstrap5\NavLink;
use Yiisoft\FormModel\Field;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Strings\StringHelper;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

/**
 * @var UrlGeneratorInterface $url
 * @var CurrentRoute $currentRoute
 * @var WebView $this
 * @var AssetManager $assetManager
 * @var TranslatorInterface $translator
 * @var string $content
 *
 * @see App\Infrastructure\Presentation\Web\ViewInjection\
 * @var User|null $user
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
        <?php
        $this->head() ?>
    </head>
    <body class="cover-container-fluid d-flex w-100 h-100 mx-auto flex-column">
    <header class="mb-auto">
        <?php
        $this->beginBody() ?>

        <?= NavBar::widget()
            ->brandText($brandLabel)
            ->brandUrl($url->generate('site/index'))
            ->addAttributes(['class' => 'navbar navbar-light bg-light navbar-expand-sm text-white'])
            ->begin() ?>

        <?= Nav::widget()
            ->currentPath($currentRoute->getUri()?->getPath() ?? '/')
            ->addAttributes(['class' => 'navbar-nav mx-auto'])
            ->items(
                NavLink::to(
                    $translator->translate('menu.blog'),
                    $url->generate('blog/index'),
                    StringHelper::startsWith($currentRouteName, 'blog/') && $currentRouteName !== 'blog/comment/index',
                ),
                NavLink::to(
                    $translator->translate('menu.comments-feed'),
                    $url->generate('blog/comment/index'),
                ),
                NavLink::to(
                    $translator->translate('menu.users'),
                    $url->generate('user/index'),
                    StringHelper::startsWith($currentRouteName, 'user/'),
                ),
                NavLink::to(
                    $translator->translate('menu.contact'),
                    $url->generate('site/contact'),
                ),
                NavLink::to(
                    $translator->translate('menu.swagger'),
                    $url->generate('swagger/index'),
                ),
                NavLink::to(
                    $translator->translate('menu.backend'),
                    $url->generate('backend/dashboard'),
                ),
            ) ?>

        <?= Nav::widget()
            ->currentPath($currentRoute->getUri()?->getPath() ?? '/')
            ->addAttributes(['class' => 'navbar-nav'])
            ->items(
                Dropdown::widget()
                    ->togglerContent($translator->translate('menu.language'))
                    ->items(
                        DropdownItem::link(
                            $translator->translate('layout.language.english'),
                            $url->generateFromCurrent(['_language' => 'en']),
                        ),
                        DropdownItem::link(
                            $translator->translate('layout.language.russian'),
                            $url->generateFromCurrent(['_language' => 'ru']),
                        ),
                        DropdownItem::link(
                            $translator->translate('layout.language.chinese'),
                            $url->generateFromCurrent(['_language' => 'zh']),
                        ),
                        DropdownItem::link(
                            $translator->translate('layout.language.spanish'),
                            $url->generateFromCurrent(['_language' => 'es']),
                        ),
                        DropdownItem::link(
                            $translator->translate('layout.language.hindi'),
                            $url->generateFromCurrent(['_language' => 'hi']),
                        ),
                        DropdownItem::link(
                            $translator->translate('layout.language.arabic'),
                            $url->generateFromCurrent(['_language' => 'ar']),
                        ),
                        DropdownItem::link(
                            $translator->translate('layout.language.portuguese'),
                            $url->generateFromCurrent(['_language' => 'pt']),
                        ),
                    ),
                ...$user === null
                ?
                [
                    NavLink::to(
                        $translator->translate('menu.login'),
                        $url->generate('auth/login'),
                        visible: $isGuest,
                    ),
                    NavLink::to(
                        $translator->translate('menu.signup'),
                        $url->generate('auth/signup'),
                        visible: $isGuest,
                    ),
                ]
                :
                [
                    Dropdown::widget()
                        ->alignment(DropdownAlignment::END)
                        ->togglerContent(Html::encode($user->getLogin()))
                        ->items(
                            DropdownItem::text(
                                Form::tag()
                                    ->action($url->generate('auth/logout'))
                                    ->method('post')
                                    ->csrf($csrf)
                                    ->content(
                                        Field::submitButton()
                                            ->addButtonAttributes(['class' => 'btn btn-sm btn-outline-danger'])
                                            ->containerClass('mb-1')
                                            ->content(
                                                $translator->translate(
                                                    'menu.logout',
                                                    ['login' => Html::encode($user->getLogin())],
                                                ),
                                            )
                                            ->encodeContent(false),
                                    ),
                            ),
                            ...$canAddPost ?
                            [
                                DropdownItem::link(
                                    'Posts',
                                    $url->generate('blog/author/posts', ['author' => $user->getLogin()]),
                                ),
                                DropdownItem::link('Cabinet', $url->generate('user/cabinet')),
                            ] : [],
                        ),
                ],
            ) ?>
        <?= NavBar::end() ?>
    </header>

    <main class="container py-3">
        <?= FlashMessage::widget() ?>
        <?= $content ?>
    </main>

    <footer class='mt-auto bg-dark py-3'>
        <div class='d-flex flex-fill align-items-center container-fluid'>
            <div class='d-flex flex-fill float-start'>
                <i class=''></i>
                <a class='text-decoration-none' href='https://www.yiiframework.com/' target='_blank' rel='noopener'>
                    Yii Framework - <?= date('Y') ?> -
                </a>
                <div class="ms-2 text-white">
                    <?= PerformanceMetrics::widget() ?>
                </div>
            </div>

            <div class='float-end'>
                <a class='text-decoration-none px-1' href='https://github.com/yiisoft' target='_blank' rel='noopener'>
                    <i class="bi bi-github text-white"></i>
                </a>
                <a class='text-decoration-none px-1'
                   href='https://join.slack.com/t/yii/shared_invite/enQtMzQ4MDExMDcyNTk2LTc0NDQ2ZTZhNjkzZDgwYjE4YjZlNGQxZjFmZDBjZTU3NjViMDE4ZTMxNDRkZjVlNmM1ZTA1ODVmZGUwY2U3NDA'
                   target='_blank' rel='noopener'>
                    <i class="bi bi-slack text-white"></i>
                </a>
                <a class='text-decoration-none px-1' href='https://www.facebook.com/groups/yiitalk' target='_blank'
                   rel='noopener'>
                    <i class="bi bi-facebook text-white"></i>
                </a>
                <a class='text-decoration-none px-1' href='https://twitter.com/yiiframework' target='_blank'
                   rel='noopener'>
                    <i class="bi bi-twitter text-white"></i>
                </a>
                <a class='text-decoration-none px-1' href='https://t.me/yii3ru' target='_blank' rel='noopener'>
                    <i class="bi bi-telegram text-white"></i>
                </a>
            </div>
        </div>
    </footer>

    <?php
    $this->endBody() ?>
    </body>
    </html>
<?php
$this->endPage(true);
