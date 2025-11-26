<?php

declare(strict_types=1);


use App\Site\Presentation\Frontend\Web\Contact\ContactForm;
use Yiisoft\FormModel\Field;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Button;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

/**
 * @var string $csrf
 * @var ContactForm $form
 * @var UrlGeneratorInterface $url
 * @var Field $field
 * @var WebView $this
 * @var TranslatorInterface $translator
 */

$this->setTitle($translator->translate('menu.contact'));
?>

<div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-12 col-md-8 col-lg-6 col-xl-8">
            <div class="card border border-dark shadow-2-strong rounded-3">
                <div class="card-header bg-dark text-white">
                    <h1 class="fw-normal h3 text-center"><?= Html::encode($this->getTitle()) ?></h1>
                </div>
                <div class="card-body p-5 text-center">
                    <?= Form::tag()
                        ->method('POST')
                        ->action($url->generate('site/contact'))
                        ->csrf($csrf)
                        ->id('form-contact')
                        ->content(
                            Field::text($form, 'name')->label($translator->translate('site.form.name')),
                            Field::email($form, 'email')->label($translator->translate('site.form.email')),
                            Field::text($form, 'subject')->label($translator->translate('site.form.subject')),
                            Field::textArea($form, 'body')
                                ->label($translator->translate('site.form.body'))
                                ->addInputAttributes(['style' => 'height: 100px']),
                            Field::file($form, 'attachFiles', ['multiple()' => [true]])
                                ->containerClass('mb-3')
                                ->label($translator->translate('site.form.attachFiles')),
                            Field::buttonGroup()
                                ->buttons(
                                    Button::tag()
                                        ->type('reset')
                                        ->class('btn btn-lg btn-danger')
                                        ->content($translator->translate('button.reset')),
                                    Button::tag()
                                        ->type('submit')
                                        ->class('btn btn-lg btn-primary')
                                        ->addAttributes(['name' => 'contact-button'])
                                        ->content($translator->translate('button.submit')),
                                )
                                ->containerClass('btn-group btn-toolbar float-end'),
                        )
?>
                </div>
            </div>
        </div>
    </div>
</div>
