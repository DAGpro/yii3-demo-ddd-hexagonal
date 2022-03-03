<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var Field $field
 * @var \App\Presentation\Backend\Web\Component\Blog\Form\CommentForm $form
 * @var \Yiisoft\Translator\TranslatorInterface $translator
 * @var \App\Blog\Domain\Comment $comment
 * @var string $csrf
 * @var array $action
 * @var string $title
 */

use Yiisoft\Form\Widget\Field;
use Yiisoft\Form\Widget\Form;

$this->setTitle($translator->translate('blog.moderate.comment') . $form->getCommentId());

?>
<div class="main">
    <h1><?= $this->getTitle()?></h1>
    <?= Form::widget()
        ->action($url->generate(...$action))
        ->method('post')
        ->attributes(['enctype' => 'multipart/form-data'])
        ->csrf($csrf)
        ->id('form-moderate-comment')
        ->begin() ?>

    <?= Field::widget()->textArea($form, 'content')->attributes(['rows' => '9', 'style' => 'height: 250px;']) ?>
    <?= Field::widget()->checkbox($form, 'public')
        ->value(true)
        ->attributes(['class' => 'form-check-input'])
        ->containerAttributes(['class' => 'form-check'])
    ?>
    <?= Field::widget()->number($form, 'comment_id')->attributes(['disabled' => 'disabled']) ?>

    <?= Field::widget()
        ->submitButton()
        ->value($translator->translate('button.submit'))
        ->attributes(
                [
                    'class' => 'btn btn-primary btn-lg mt-3',
                    'id' => 'login-button',
                ]
        )
    ?>
    <?=Form::end()?>
</div>
