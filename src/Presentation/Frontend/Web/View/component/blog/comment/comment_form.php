<?php

declare(strict_types=1);

use Yiisoft\Assets\AssetManager;
use Yiisoft\Form\Widget\Field;
use Yiisoft\Form\Widget\Form;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\View\WebView;
use Yiisoft\Yii\Bootstrap5\Alert;

/**
 * @var UrlGeneratorInterface $url
 * @var AssetManager $assetManager
 * @var Field $field
 * @var \Yiisoft\Translator\Translator $translator
 * @var \App\Core\Component\Blog\Domain\Comment $comment
 * @var WebView $this
 * @var \App\Presentation\Frontend\Web\Component\Blog\Comment\CommentForm $form
 * @var array $action
 * @var string $commentText
 * @var string $csrf
 */

if (!empty($errors)) {
    foreach ($errors as $field => $error) {
        echo Alert::widget()->options(['class' => 'alert-danger'])->body(Html::encode($field) . ': ' . Html::encode(...$error));
    }
}

$this->setTitle('Edit Comment');

echo "<h1 class='mb-3'>{$this->getTitle()}</h1>";

//$commentText = $commentText ?? $comment->getContent();

//echo <<<FORM
//<form id="draftPost" method="POST" action="{$url->generate(...$action)}" enctype="multipart/form-data">
//    <input type="hidden" name="_csrf" value="{$csrf}">
//    <input name="comment_id" type="hidden" value="{$comment->getId()}">
//
//    <div class="form-group mb-3">
//        <label class="form-label" for="comment">Change Comment</label>
//        <textarea minlength="3" required class="form-control" name="comment" type="text" rows="5">{$commentText}</textarea>
//    </div>
//
//    <button type="submit" class="btn btn-primary">Submit</button>
//</form>
//FORM;
?>


<?= Form::widget()
    ->action($url->generate(...$action))
    ->method('post')
    ->attributes(['enctype' => 'multipart/form-data'])
    ->csrf($csrf)
    ->id('form-comment')
    ->begin() ?>

<?= Field::widget()->textArea($form, 'comment')->attributes(['rows' => '6']) ?>

<?= Field::widget()
    ->submitButton($translator->translate('button.submit'))
    ->attributes(
        [
            'class' => 'btn btn-primary btn-lg mt-3',
            'id' => 'comment-button',
        ]
    )
?>

<?=Form::end()?>
