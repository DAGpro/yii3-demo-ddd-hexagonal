<?php

declare(strict_types=1);

/**
 * @var WebView $this
 * @var UrlGeneratorInterface $url
 * @var PaginatorInterface $paginator
 * @var Field $field
 * @var TranslatorInterface $translator
 * @var string $csrf
 * @var string $action
 * @var string $title
 * @var CreateUserForm $form
 */


use App\IdentityAccess\Presentation\Backend\Web\User\Forms\CreateUserForm;
use Yiisoft\Data\Paginator\PaginatorInterface;
use Yiisoft\FormModel\Field;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

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
                        ->action($url->generate('backend/user/create'))
                        ->attributes(['enctype' => 'multipart/form-data'])
                        ->csrf($csrf)
                        ->id('signupForm')
                        ->content(
                            Field::text($form, 'login')
                                ->addInputAttributes(['autofocus' => true]),
                            Field::password($form, 'password'),
                            Field::password($form, 'passwordVerify'),
                            Field::submitButton()
                                ->content($translator->translate('button.submit'))
                                ->addButtonAttributes(
                                    [
                                        'class' => 'btn btn-primary btn-lg mt-3',
                                        'id' => 'register-button',
                                    ],
                                ),
                        ) ?>
                </div>
            </div>
        </div>
    </div>
</div>
