<?php

declare(strict_types=1);

/**
 * @var View $this
 * @var UrlGeneratorInterface $url
 * @var Translator $translator
 * @var PostForm $form
 * @var array $body
 * @var string $csrf
 * @var array $action
 * @var array $tags
 * @var string $title
 */

use App\Blog\Presentation\Frontend\Web\Author\PostForm;
use Yiisoft\FormModel\Field;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Button;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Html\Tag\Input;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\View;

?>

<h1><?= Html::encode($title) ?></h1>

<?php
$tagListInString = implode(
    ', ',
    $form
        ->getValidationResult()
        ->getPropertyErrorMessagesIndexedByPath('tags'),
);

$buttonTags = '';
foreach ($form->getTags() as $tag) {
    $buttonTags .= Button::tag()
        ->class('btn btn-sm btn-info mb-2 me-2 remove-tag')
        ->content(
            Input::tag()
                ->type('hidden')
                ->name('tags[]')
                ->value($tag)
                ->render(),
            $tag,
            '<span class="btn-close ms-1"></span>',
        )
        ->render();
}

?>

<?= Form::tag()
    ->action($url->generate(...$action))
    ->method('post')
    ->attributes(['enctype' => 'multipart/form-data'])
    ->csrf($csrf)
    ->id('form-author-post')
    ->content(
        Field::text($form, 'title'),
        Field::textArea($form, 'content')
            ->addInputAttributes(['rows' => 9, 'style' => 'height: 300px;']),
        "<label for='addTag' class='form-label'>{$translator->translate('blog.add.tag')}</label>",
        '<input type="text" class="form-control mb-3" id="addTag" placeholder="Add tag" value="">',
        "<p class='text-danger'>$tagListInString</p>",
        Button::tag()
            ->content($translator->translate('button.add'))
            ->addAttributes(['class' => 'btn btn-primary mb-3', 'id' => 'addTagButton']),
        Div::tag()->content($buttonTags),
        Field::submitButton()
            ->content($translator->translate('button.submit'))
            ->addButtonAttributes(['class' => 'btn btn-primary btn-lg mt-3', 'id' => 'author-post-button']),
    )
?>
