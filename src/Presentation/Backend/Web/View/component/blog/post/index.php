<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\Data\Paginator\OffsetPaginator $paginator;
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Form\Widget\Field $field
 * @var \Yiisoft\Translator\Translator $translator
 * @var \Yiisoft\View\WebView $this
 * @var string $csrf
 */

use App\Core\Component\Blog\Domain\Post;
use App\Presentation\Infrastructure\Web\Widget\OffsetPagination;
use Yiisoft\Html\Html;

$this->setTitle($translator->translate('backend.title.posts'));
$pagination = OffsetPagination::widget()
    ->paginator($paginator)
    ->urlGenerator(fn ($page) => $url->generate('backend/post', ['page' => $page]));
?>
<h1><?= Html::encode($this->getTitle())?></h1>
<div class="roles">
        <?php
        $pageSize = $paginator->getCurrentPageSize();
        if ($pageSize > 0) {
            echo Html::p(
                sprintf('Showing %s out of %s posts', $pageSize, $paginator->getTotalItems()),
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
                    <th scope="col"><?=$translator->translate('blog.title')?></th>
                    <th scope="col"><?=$translator->translate('blog.author')?></th>
                    <th scope="col"><?=$translator->translate('blog.status')?></th>
                    <th scope="col"><?=$translator->translate('blog.action')?></th>
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
                            <td><a href="{$url->generate('backend/post/view', ['post_id' => $post->getId()])}" class="fw-bold">{$post->getTitle()}</a></td>
                            <td>{$post->getAuthor()->getName()}</td>
                            <td>{$status}</td>
                            <td>
                                <form id="removeRole" action="{$url->generate('backend/post/delete', ['post_id' => $post->getId()])}" method="post" >
                                    <input type="hidden" name="_csrf" value="{$csrf}">
                                    <input type="hidden" name="post_id" value="{$post->getId()}">

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
