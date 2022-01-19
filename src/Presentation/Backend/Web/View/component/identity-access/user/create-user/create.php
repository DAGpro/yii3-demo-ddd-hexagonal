<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Data\Paginator\PaginatorInterface $paginator
 * @var Field $field
 * @var \Yiisoft\Translator\TranslatorInterface $translator
 * @var string $csrf
 * @var string $action
 * @var string $title
 * @var \App\Presentation\Backend\Web\Component\IdentityAccess\User\Forms\CreateUserForm $form
 */

use Yiisoft\Form\Widget\Field;
use Yiisoft\Form\Widget\Form;
use Yiisoft\Html\Html;

?>

<div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5">
            <div class="card border border-dark shadow-2-strong rounded-3">
                <div class="card-header bg-dark text-white">
                    <h1 class="fw-normal h3 text-center"><?= Html::encode($this->getTitle()) ?></h1>
                </div>
                <div class="card-body p-5 text-center">
                    <?= Form::widget()
                        ->action($url->generate('backend/user/create'))
                        ->attributes(['enctype' => 'multipart/form-data'])
                        ->csrf($csrf)
                        ->id('signupForm')
                        ->begin() ?>

                    <?= Field::widget()->config($form, 'login')->text(['autofocus' => true]) ?>
                    <?= Field::widget()->config($form, 'password')->password() ?>
                    <?= Field::widget()->config($form, 'passwordVerify')->password() ?>
                    <?= Field::widget()->containerClass('d-grid gap-2 form-floating')->submitButton(
                        [
                            'class' => 'btn btn-primary btn-lg mt-3',
                            'id' => 'register-button',
                            'value' => $translator->translate('layout.submit'),
                        ]
                    ) ?>

                    <?= Form::end() ?>
                </div>
            </div>
        </div>
    </div>
</div>
