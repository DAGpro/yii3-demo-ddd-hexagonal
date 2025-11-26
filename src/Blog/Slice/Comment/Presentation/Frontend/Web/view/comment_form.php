<?php

declare(strict_types=1);

use App\Blog\Domain\Comment;
use App\Blog\Slice\Comment\Presentation\Frontend\Web\CommentForm;
use Yiisoft\Assets\AssetManager;
use Yiisoft\FormModel\Field;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;

/**
 * @var UrlGeneratorInterface $url
 * @var AssetManager $assetManager
 * @var Translator $translator
 * @var Comment $comment
 * @var WebView $this
 * @var CommentForm $form
 * @var int $commentId
 * @var string $commentText
 * @var string $csrf
 */


$this->setTitle($translator->translate('blog.edit.comment'));

echo "<h1 class='mb-3'>{$this->getTitle()}</h1>";

?>


<?= Form::tag()
    ->action($url->generate('blog/comment/edit', ['comment_id' => $commentId]))
    ->method('post')
    ->addAttributes(['enctype' => 'multipart/form-data'])
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
