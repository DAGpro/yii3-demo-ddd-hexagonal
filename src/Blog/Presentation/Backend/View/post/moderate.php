<?php

declare(strict_types=1);


use App\Blog\Domain\Post;
use App\Blog\Presentation\Backend\Web\Form\PostForm;
use Yiisoft\FormModel\Field;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Button;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var UrlGeneratorInterface $url
 * @var Translator $translator
 * @var Post $post
 * @var PostForm $form
 * @var string $csrf
 * @var array $action
 * @var string $title
 */
$this->setTitle($translator->translate('blog.moderate.post'));

?>
<div class="main">
    <div class="card">
        <h1 class="card-header"><?= Html::encode($this->getTitle()) ?></h1>

        <?php
        $htmlTag = '';
        foreach ($form->getTags() as $tag) {
            $tag = Html::encode($tag);
            $htmlTag .= <<<HTML
                <button type="button" class="btn btn-sm btn-info mb-2 me-2 remove-tag">
                    <input type="hidden" name="tags[]" value="$tag">
                     $tag<span class="btn-close ms-1"></span>
                </button>
                HTML;
        }

        $errors = $form->isValidated()
            ? $form->getValidationResult()->getPropertyErrorMessagesIndexedByPath('tag')
            : [];
        $error = !empty($errors) ? implode(', ', array_merge(...array_values($errors))) : '';

        $buttonTags = Button::tag()
            ->content($translator->translate('blog.add.tag'))
            ->class('btn btn-primary mb-3')
            ->id('addTagButton');

        $tagsList = <<<TAGS
            <div class="form-floating mb-3">
                <input type="text" class="form-control" name="addTag" id="addTag" placeholder="Add tag" value="">
                <label for="addTag" class="floatingInput">{$translator->translate('blog.add.tag')}</label>
                <p class="alert-danger">$error</p>
                $buttonTags
            </div>
            <div id="tags">
                $htmlTag
            </div>
            TAGS;
        ?>

        <div class="card-body">
            <?= Form::tag()
                ->action(
                    $url->generate(
                        'backend/post/moderate',
                        ['post_id' => $form->getId()],
                    ),
                )
                ->method('post')
                ->addAttributes(['enctype' => 'multipart/form-data'])
                ->csrf($csrf)
                ->id('form-moderate-post')
                ->encode(false)
                ->content(
                    Field::text($form, 'title'),
                    Field::textArea($form, 'content')
                        ->addInputAttributes(['rows' => '9', 'style' => 'height: 300px;']),
                    Field::checkbox($form, 'public')
                        ->value(true)
                        ->addInputAttributes(['class' => 'form-check-input'])
                        ->containerAttributes(['class' => 'form-check mb-3']),
                    $tagsList,
                    Field::submitButton()
                        ->content($translator->translate('button.submit'))
                        ->addButtonAttributes(
                            [
                                'class' => 'btn btn-danger btn-lg mt-3',
                                'id' => 'login-button',
                            ],
                        ),
                ) ?>
        </div>
    </div>
</div>

