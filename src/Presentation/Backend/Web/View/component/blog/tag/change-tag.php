<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var Field $field
 * @var \Yiisoft\Translator\Translator $translator
 * @var \App\Presentation\Backend\Web\Component\Blog\Form\TagForm $form
 * @var string $csrf
 * @var array $action
 * @var string $title
 * @var \App\Blog\Domain\Tag $tag
 * @var array $error
 */

use Yiisoft\Form\Widget\Field;
use Yiisoft\Form\Widget\Form;

$this->setTitle($title);

?>
    <div class="main row">
        <div class="col-md-5">
            <h3> <?=$translator->translate('blog.tag.change') . $form->getLabel()?></h3>
            <?= Form::widget()
                ->action($url->generate(...$action))
                ->method('post')
                ->attributes(['enctype' => 'multipart/form-data'])
                ->csrf($csrf)
                ->id('form-moderate-tag')
                ->begin() ?>

            <?= Field::widget()->text($form, 'label') ?>
            <?= Field::widget()->text($form, 'id')->attributes(['disabled' => 'disabled']) ?>

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

            <?= Form::end()?>
        </div>
    </div>
<?php
