<?php

declare(strict_types=1);


use App\Blog\Domain\Comment;
use App\Blog\Presentation\Backend\Web\Form\CommentForm;
use Yiisoft\FormModel\Field;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var UrlGeneratorInterface $url
 * @var Field $field
 * @var CommentForm $form
 * @var TranslatorInterface $translator
 * @var Comment $comment
 * @var string $csrf
 * @var array<string, array<string, string|int|array>> $action
 * @var string $title
 */
$this->setTitle(
    $translator->translate('blog.moderate.comment') . $form->getCommentId(),
);

?>
<div class="main">
    <div class="card">
        <h1 class="card-header"><?= $this->getTitle() ?></h1>
        <div class="card-body">
            <?= Form::tag()
                ->action(
                    $url->generate(
                        'backend/comment/moderate',
                        ['comment_id' => $form->getCommentId()],
                    ),
                )
                ->method('post')
                ->attributes(['enctype' => 'multipart/form-data'])
                ->csrf($csrf)
                ->id('form-moderate-comment')
                ->content(
                    Field::textArea($form, 'content')
                        ->addInputAttributes(['rows' => '9', 'style' => 'height: 250px;']),

                    Field::checkbox($form, 'public')
                        ->value(true)
                        ->addInputAttributes(['class' => 'form-check-input'])
                        ->containerAttributes(['class' => 'form-check']),

                    Field::number($form, 'comment_id')
                        ->addInputAttributes(['disabled' => 'disabled']),

                    Field::submitButton()
                        ->content($translator->translate('button.submit'))
                        ->addButtonAttributes(
                            [
                                'class' => 'btn btn-primary btn-lg mt-3',
                                'id' => 'login-button',
                            ],
                        ),
                )
            ?>
        </div>

    </div>

</div>
