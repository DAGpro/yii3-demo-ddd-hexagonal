<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\View\View $this
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Translator\Translator $translator
 * @var \App\Presentation\Frontend\Web\Component\Blog\Author\PostForm $form
 * @var array $body
 * @var string $csrf
 * @var array $action
 * @var array $tags
 * @var string $title
 */

use Yiisoft\Form\Widget\Field;
use Yiisoft\Form\Widget\Form;
use Yiisoft\Html\Html;

?>

<h1><?= Html::encode($title) ?></h1>

<?= Form::widget()
    ->action($url->generate(...$action))
    ->method('post')
    ->attributes(['enctype' => 'multipart/form-data'])
    ->csrf($csrf)
    ->id('form-author-post')
    ->begin() ?>

<?= Field::widget()->text($form, 'title') ?>
<?= Field::widget()->textArea($form, 'content')->attributes(['rows' => 9, 'style' => 'height: 300px;']) ?>

<label for="addTag" class="form-label"><?=$translator->translate('blog.add.tag')?></label>
<input type="text" class="form-control mb-3" id="addTag" placeholder="Add tag" value="">
<p class="text-danger"><?=implode(', ', $form->getFormErrors()->getErrors('tags'))?></p>
<?= Html::button(
    'Add',
    ['class' => 'btn btn-primary mb-3', 'id' => 'addTagButton']
) ?>

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
->attributes(['class' => 'btn btn-primary btn-lg mt-3', 'id' => 'author-post-button']) ?>

<?=Form::end()?>
