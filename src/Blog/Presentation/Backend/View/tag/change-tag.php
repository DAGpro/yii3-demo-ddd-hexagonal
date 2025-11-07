<?php

declare(strict_types=1);


use App\Blog\Domain\Tag;
use App\Blog\Presentation\Backend\Web\Form\TagForm;
use Yiisoft\FormModel\Field;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var UrlGeneratorInterface $url
 * @var Field $field
 * @var Translator $translator
 * @var TagForm $form
 * @var string $csrf
 * @var array $action
 * @var string $title
 * @var Tag $tag
 * @var array $error
 */
$this->setTitle($title);

?>
    <div class="main row">
        <div class="col-md-5">
            <div class="card">
                <h3 class="card-header"> <?= $translator->translate('blog.tag.change') . $form->getLabel() ?></h3>
                <?= Form::tag()
                    ->action($url->generate('backend/tag/change', ['tag_id' => $form->getId()]))
                    ->method('post')
                    ->attributes(['enctype' => 'multipart/form-data'])
                    ->csrf($csrf)
                    ->class('card-body')
                    ->id('form-moderate-tag')
                    ->content(
                        Field::text($form, 'label'),
                        Field::number($form, 'id')->addInputAttributes(['disabled' => 'disabled']),
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
<?php
