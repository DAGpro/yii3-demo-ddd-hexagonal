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

$this->setTitle($translator->translate('Signup'));
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
                        ->action($url->generate('auth/signup'))
                        ->method('POST')
                        ->csrf($csrf)
                        ->id('signupForm')
                        ->content(
                            Field::text($formModel, 'login')->autofocus(),
                            Field::password($formModel, 'password'),
                            Field::password($formModel, 'passwordVerify'),
                            Field::submitButton()
                                ->addButtonAttributes(['id' => 'register-button'])
                                ->name('register-button')
                                ->content($translator->translate('button.submit')),
                        ) ?>
                </div>
            </div>
        </div>
    </div>
</div>
