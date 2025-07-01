<?php

declare(strict_types=1);

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

use App\Blog\Domain\Post;
use App\Blog\Presentation\Backend\Web\Form\PostForm;
use Yiisoft\FormModel\Field;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Button;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;

$this->setTitle($translator->translate('blog.moderate.post'));

?>
<div class="main">
    <h1><?= Html::encode($this->getTitle()) ?></h1>

    <<<'HTML'
    <div class="form-floating mb-3">
        <input type="text" class="form-control" name="addTag" id="addTag" placeholder="Add tag" value="">
        <label for="addTag" class="floatingInput"><?= $translator->translate('blog.add.tag') ?></label>
        <p class="alert-danger">
            <?= implode(', ', $form->getValidationResult()->getErrorMessagesIndexedByPath('tag')) ?>
        </p>
        <?= Button::tag()
            ->content($translator->translate('blog.add.tag'))
            ->class('btn btn-primary mb-3')
            ->id('addTagButton')
        ?>
    </div>
    <div id="tags">
        <?php
        foreach ($form->getTags() as $tag) : ?>
            <button type="button" class="btn btn-sm btn-info mb-2 me-2 remove-tag">
                <input type="hidden" name="tags[]" value="<?= Html::encode($tag) ?>">
                <?= Html::encode($tag) ?><span class="btn-close ms-1"></span>
            </button>
        <?php
        endforeach; ?>
    </div>
    HTML,

    <?= Form::tag()
        ->action($url->generate(...$action))
        ->method('post')
        ->attributes(['enctype' => 'multipart/form-data'])
        ->csrf($csrf)
        ->id('form-moderate-post')
        ->content(
            Field::text($form, 'title'),
            Field::textArea($form, 'content')
                ->addInputAttributes(['rows' => '9', 'style' => 'height: 300px;']),
            Field::checkbox($form, 'public')
                ->value(true)
                ->addInputAttributes(['class' => 'form-check-input'])
                ->containerAttributes(['class' => 'form-check']),


            Field::submitButton()
                ->content($translator->translate('button.submit'))
                ->addButtonAttributes(
                    [
                        'class' => 'btn btn-primary btn-lg mt-3',
                        'id' => 'login-button',
                    ],
                ),
        ) ?>


</div>

