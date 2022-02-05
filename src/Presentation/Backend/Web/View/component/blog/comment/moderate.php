<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var Field $field
 * @var \App\Presentation\Backend\Web\Component\Blog\Form\CommentForm $form
 * @var \Yiisoft\Translator\TranslatorInterface $translator
 * @var \App\Core\Component\Blog\Domain\Comment $comment
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

    <?= Field::widget()->config($form, 'content')->textArea(['rows' => '9', 'class' => 'h-100']) ?>
    <?= Field::widget()->config($form, 'public')->inputClass('form-check-input')->checkbox() ?>
    <?= Field::widget()->config($form, 'comment_id')->text(['disabled' => 'disabled']) ?>

    <?= Field::widget()->submitButton(
        [
            'class' => 'btn btn-primary btn-lg mt-3',
            'id' => 'login-button',
            'value' => $translator->translate('button.submit'),
        ],
    ) ?>
    <?=Form::end()?>
</div>
