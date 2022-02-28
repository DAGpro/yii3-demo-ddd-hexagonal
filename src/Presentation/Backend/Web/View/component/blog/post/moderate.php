<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Form\Widget\Field $field
 * @var \Yiisoft\Translator\Translator $translator
 * @var \App\Core\Component\Blog\Domain\Post $post
 * @var \App\Presentation\Backend\Web\Component\Blog\Form\PostForm $form
 * @var string $csrf
 * @var array $action
 * @var string $title
 */

use Yiisoft\Form\Widget\Form;
use Yiisoft\Html\Html;
use Yiisoft\Form\Widget\Field;

$this->setTitle($translator->translate('blog.moderate.post'));

?>
<div class="main">
    <h1><?= Html::encode($this->getTitle()) ?></h1>

    <?= Form::widget()
        ->action($url->generate(...$action))
        ->method('post')
        ->attributes(['enctype' => 'multipart/form-data'])
        ->csrf($csrf)
        ->id('form-moderate-post')
        ->begin() ?>

    <?= Field::widget()->text($form, 'title') ?>
    <?= Field::widget()->textArea($form, 'content')->attributes(['rows' => '9', 'style' => 'height: 300px;']) ?>
    <?= Field::widget()->checkbox($form, 'public') ?>


    <div class="form-floating mb-3">
        <input type="text" class="form-control" name="addTag" id="addTag" placeholder="Add tag" value="">
        <label for="addTag" class="floatingInput"><?=$translator->translate('blog.add.tag')?></label>
        <p class="alert-danger"><?=implode(', ', $form->getFormErrors()->getErrors('tag'))?></p>
        <?= Html::button(
            $translator->translate('blog.add.tag'),
            ['class' => 'btn btn-primary mb-3', 'id' => 'addTagButton']
        ) ?>
    </div>

    <div id="tags">
        <?php foreach ($form->getTags()as $tag) : ?>
            <button type="button" class="btn btn-sm btn-info mb-2 me-2 remove-tag">
                <input type="hidden" name="tags[]" value="<?= Html::encode($tag) ?>">
                <?= Html::encode($tag) ?><span class="btn-close ms-1"></span>
            </button>
        <?php endforeach; ?>
    </div>

    <?= Field::widget()
        ->submitButton()
        ->value($translator->translate('button.submit'))
        ->attributes(
            [
                'class' => 'btn btn-primary btn-lg mt-3',
                'id' => 'login-button'
            ]
        )
    ?>

    <?=Form::end()?>
</div>

