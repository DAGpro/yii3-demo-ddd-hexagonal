<?php

declare(strict_types=1);


use App\Blog\Slice\Post\Controller\Frontend\Web\Author\PostForm;
use Yiisoft\FormModel\Field;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Button;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Html\Tag\Input;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;

/**
 * @var UrlGeneratorInterface $url
 * @var Translator $translator
 * @var PostForm $form
 * @var array $body
 * @var string $csrf
 * @var array{route:string, arguments?:array<string, string>} $action
 * @var array $tags
 * @var string $title
 * @psalm-scope-this WebView
 */
?>

<h1><?= Html::encode($title) ?></h1>

<?php
$tagsErrorMessages = $form->isValidated()
    ? $form->getValidationResult()->getPropertyErrorMessagesIndexedByPath('tags')
    : null;
$tagsErrorsWithString = !empty($tagsErrorMessages) ? implode(
    ', ',
    /** @return string[] */
    array_merge(...array_values($tagsErrorMessages)),
) : '';

$buttonTags = '';
foreach ($form->getTags() as $tag) {
    $buttonTags .= Button::tag()
        ->type('button')
        ->class('btn btn-sm btn-info mt-3 me-2 remove-tag')
        ->encode(false)
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
    ->action($url->generate($action['route'], $action['arguments'] ?? []))
    ->method('post')
    ->addAttributes(['enctype' => 'multipart/form-data'])
    ->csrf($csrf)
    ->id('form-author-post')
    ->encode(false)
    ->content(
        Field::text($form, 'title'),
        Field::textArea($form, 'content')
            ->addInputAttributes(['rows' => 9, 'style' => 'height: 300px;']),
        "<label for='addTag' class='form-label'>{$translator->translate('blog.add.tag')}</label>",
        '<input type="text" class="form-control mb-3" id="addTag" placeholder="Add tag" value="">',
        "<p class='text-danger'>$tagsErrorsWithString</p>",
        Button::tag()
            ->type('button')
            ->content($translator->translate('blog.add.tag'))
            ->addAttributes(['class' => 'btn btn-primary mb-3', 'id' => 'addTagButton']),
        Div::tag()
            ->id('tags')
            ->encode(false)
            ->content($buttonTags),
        Field::submitButton()
            ->content($translator->translate('button.submit'))
            ->addButtonAttributes(['class' => 'btn btn-primary btn-lg mt-3', 'id' => 'author-post-button']),
    )
?>
