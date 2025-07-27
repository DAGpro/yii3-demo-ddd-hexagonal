<?php

declare(strict_types=1);


use Yiisoft\FormModel\Field;
use Yiisoft\FormModel\FormModelInterface;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var TranslatorInterface $translator
 * @var UrlGeneratorInterface $url
 * @var string $csrf
 * @var FormModelInterface $formModel
 */

$this->setTitle($translator->translate('identityAccess.form.login'));

$error ??= null;
?>

<div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5">
            <div class="card border border-dark shadow-2-strong rounded-3">
                <div class="card-header bg-dark text-white">
                    <h1 class="fw-normal h3 text-center"><?= Html::encode($this->getTitle()) ?></h1>
                </div>
                <div class="card-body p-5 text-center">
                    <?= Form::tag()
                        ->action($url->generate('auth/login'))
                        ->method('post')
                        ->csrf($csrf)
                        ->id('loginForm')
                        ->content(
                            Field::text($formModel, 'login')->autofocus(),
                            Field::password($formModel, 'password'),
                            Field::checkbox($formModel, 'rememberMe')
                                ->containerClass('form-check form-switch text-start mt-2')
                                ->inputClass('form-check-input')
                                ->labelClass('form-check-label'),
                            Field::submitButton()
                                ->addButtonAttributes(['id' => 'login-button'])
                                ->name('login-button')
                                ->content($translator->translate('button.submit')),

                        )
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

