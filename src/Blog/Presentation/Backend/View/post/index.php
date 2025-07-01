<?php

declare(strict_types=1);

/**
 * @var OffsetPaginator $paginator ;
 * @var UrlGeneratorInterface $url
 * @var Translator $translator
 * @var WebView $this
 * @var string $csrf
 */

use App\Blog\Domain\Post;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Html\Tag\P;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;
use Yiisoft\Yii\DataView\Pagination\OffsetPagination;
use Yiisoft\Yii\DataView\Pagination\PaginationContext;

$this->setTitle($translator->translate('backend.title.posts'));
$pagination = Div::tag()
    ->content(
        new OffsetPagination()
            ->withContext(
                new PaginationContext(
                    $url->generate(
                        'blog/post',
                    ) . '/page/' . PaginationContext::URL_PLACEHOLDER,
                    $url->generate(
                        'blog/post',
                    ) . '/page/' . PaginationContext::URL_PLACEHOLDER,
                    $url->generate('blog/comment'),
                ),
            )
            ->listTag('ul')
            ->listAttributes(['class' => 'pagination width-auto'])
            ->itemTag('li')
            ->itemAttributes(['class' => 'page-item'])
            ->linkAttributes(['class' => 'page-link'])
            ->currentItemClass('active')
            ->currentLinkClass('page-link')
            ->disabledItemClass('disabled')
            ->disabledLinkClass('disabled')
            ->withPaginator($paginator),
    )
    ->class('table-responsive')
    ->encode(false)
    ->render();
?>
<h1><?= Html::encode($this->getTitle()) ?></h1>
<div class="roles">
    <?php
    $pageSize = $paginator->getCurrentPageSize();
    if ($pageSize > 0) {
        echo P::tag()
            ->content(sprintf('Showing %s out of %s posts', $pageSize, $paginator->getTotalItems()))
            ->class('text-muted')
            ->render();
    } else {
        echo P::tag()
            ->content($translator->translate('no.records'))
            ->class('text-muted')
            ->render();
    }
    ?>
    <div class="m-2">
        <table class="table mb-5 border border-light border-3">
            <thead>
            <tr>
                <th scope="col"><?= $translator->translate('blog.id') ?></th>
                <th scope="col"><?= $translator->translate('blog.title') ?></th>
                <th scope="col"><?= $translator->translate('blog.author') ?></th>
                <th scope="col"><?= $translator->translate('blog.status') ?></th>
                <th scope="col"><?= $translator->translate('blog.action') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            /** @var Post $post */
            foreach ($paginator->read() as $post) {
                $status = $post->isPublic()
                    ? "<button class='btn btn-success btn-sm'>{$translator->translate('blog.public')}</button>"
                    : "<button class='btn btn-danger btn-sm'>{$translator->translate('blog.draft')}</button>";
                echo <<<ROLE
                    <tr>
                        <td>{$post->getId()}</td>
                        <td>
                            <a href="{$url->generate('backend/post/view', ['post_id' => $post->getId()])}"
                                class="fw-bold"
                            >
                                {$post->getTitle()}
                            </a>
                        </td>
                        <td>{$post->getAuthor()->getName()}</td>
                        <td>$status</td>
                        <td>
                            <form
                                id="removeRole"
                                action="{$url->generate('backend/post/delete', ['post_id' => $post->getId()])}"
                                method="post"
                            >
                                <input type="hidden" name="_csrf" value="$csrf">
                                <input type="hidden" name="post_id" value="{$post->getId()}">

                                <button type="submit"
                                 class="btn btn-sm btn-danger">
                                    {$translator->translate('blog.delete')}
                                </button>
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
    if ($paginator->getTotalItems() > 0) {
        echo $pagination;
    }
    ?>
</div>
