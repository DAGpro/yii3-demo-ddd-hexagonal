<?php

declare(strict_types=1);

use App\Blog\Domain\Comment;
use App\Blog\Presentation\Frontend\Web\Comment\CommentForm;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Bootstrap5\Alert;
use Yiisoft\FormModel\Field;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;

/**
 * @var UrlGeneratorInterface $url
 * @var AssetManager $assetManager
 * @var Field $field
 * @var Translator $translator
 * @var Comment $comment
 * @var WebView $this
 * @var CommentForm $form
 * @var array $action
 * @var string $commentText
 * @var string $csrf
 */

if (!empty($errors)) {
    foreach ($errors as $field => $error) {
        echo Alert::widget()
            ->addAttributes(['class' => 'alert-danger'])->body(Html::encode($field)
                . ': ' . Html::encode(...$error),
            );
    }
}

$this->setTitle('Edit Comment');

echo "<h1 class='mb-3'>{$this->getTitle()}</h1>";

?>


<?= Form::tag()
    ->action($url->generate(...$action))
    ->method('post')
    ->attributes(['enctype' => 'multipart/form-data'])
    ->csrf($csrf)
    ->id('form-comment')
    ->content(
        Field::textarea($form, 'comment')
            ->addInputAttributes(['rows' => 6,]),
        Field::submitButton()
            ->content($translator->translate('button.submit'))
            ->addButtonAttributes(['class' => 'btn btn-primary btn-lg mt-3', 'id' => 'comment-button']),
    )
?>
