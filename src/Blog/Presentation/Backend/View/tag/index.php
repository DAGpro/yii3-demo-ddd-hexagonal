<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\Data\Paginator\OffsetPaginator $paginator;
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Form\Widget\Field $field
 * @var \Yiisoft\Translator\Translator $translator
 * @var string $csrf
 * @var \Yiisoft\View\WebView $this
 */

use App\Blog\Domain\Tag;
use App\Presentation\Infrastructure\Web\Widget\OffsetPagination;
use Yiisoft\Html\Html;

$this->setTitle($translator->translate('backend.title.tags'));
$pagination = OffsetPagination::widget()
    ->paginator($paginator)
    ->urlGenerator(fn ($page) => $url->generate('backend/tag', ['page' => $page]));
?>
<h1><?= Html::encode($this->getTitle())?></h1>
<div class="row">
    <div class="col-md-12">
        <?php
        $pageSize = $paginator->getCurrentPageSize();
        if ($pageSize > 0) {
            echo Html::p(
                sprintf('Showing %s out of %s tags', $pageSize, $paginator->getTotalItems()),
                ['class' => 'text-muted']
            );
        } else {
            echo Html::p('No records');
        }
        ?>

        <div class="m-2">
            <table class="table mb-5 border border-light border-3">
                <thead>
                <tr>
                    <th scope="col"><?=$translator->translate('blog.id')?></th>
                    <th scope="col"><?=$translator->translate('blog.tag.label')?></th>
                    <th scope="col"><?=$translator->translate('blog.action')?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                /** @var Tag $tag */
                foreach ($paginator->read() as $tag) {
                    echo <<<ROLE
                        <tr>
                            <td>{$tag->getId()}</td>
                            <td><a class="btn btn-info" href="{$url->generate('backend/tag/change', ['tag_id' => $tag->getId()])}">{$tag->getLabel()}</a></td>
                            <td>
                                <form id="removeRole" action="{$url->generate('backend/tag/delete', ['tag_id' => $tag->getId()])}" method="post" >
                                    <input type="hidden" name="_csrf" value="{$csrf}">
                                    <input type="hidden" name="tag_id" value="{$tag->getId()}">

                                    <button type="submit" class="btn btn-sm btn-danger">{$translator->translate('blog.delete')}</button>
                                </form>
                            </td>
                        </tr>
                    ROLE;
                }
                ?>
                </tbody>
            </table>
        </div>
        <?php
        if ($pagination->isRequired()) {
            echo $pagination;
        }
        ?>
    </div>
</div>
