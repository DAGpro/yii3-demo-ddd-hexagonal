<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Form\Widget\Field $field
 * @var \Yiisoft\Translator\Translator $translator
 * @var \Yiisoft\Data\Paginator\PaginatorInterface $paginator
 * @var array $action
 * @var string $title
 * @var string $csrf
 */

use App\Presentation\Infrastructure\Web\Widget\OffsetPagination;

?>
    <div class="main">
        <?php
        echo <<<CLEARUSERS
        <h2 class="mb-4 p-2">
            <span>{$translator->translate('identityAccess.users')}</span>
            <form id="clearUsers" class="float-end" action="{$url->generate('backend/user/clear-users')}" method="post" >
                <input type="hidden" name="_csrf" value="{$csrf}">
                <button type="submit" class="btn btn-sm btn-danger">{$translator->translate('identityAccess.remove.users')}</button>
            </form>
        </h2>
        CLEARUSERS;
        ?>

        <div class="users m-2">
            <table class="table mb-4 border border-light border-3">
                <thead>
                <tr>
                    <th scope="col"><?=$translator->translate('identityAccess.user.id')?></th>
                    <th scope="col"><?=$translator->translate('identityAccess.login')?></th>
                    <th scope="col"><?=$translator->translate('identityAccess.access.rights')?></th>
                    <th scope="col"><?=$translator->translate('identityAccess.action')?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                /** @var  \App\IdentityAccess\User\Domain\User $user */
                foreach ($paginator->read() as $user) {
                    echo <<<REMOVEUSER
                    <tr>
                        <td><a href="{$url->generate('backend/user/profile', ['user_id' => $user->getId()])}" class="fw-bold">{$user->getId()}</a></td>
                        <td>{$user->getLogin()}</td>
                        <td><a href="{$url->generate('backend/access/user-assignments', ['user_id' => $user->getId()])}" class="fw-bold">{$translator->translate('identityAccess.user.assignments')}</a></td>
                        <td>
                            <form id="removeIdentity" action="{$url->generate('backend/user/delete')}" method="post" >
                                <input type="hidden" name="_csrf" value="{$csrf}">
                                <input type="hidden" name="user_id" value="{$user->getId()}">

                                <button type="submit" class="btn btn-sm btn-danger">{$translator->translate('identityAccess.remove.user')}</button>
                            </form>
                        </td>
                    </tr>
                    REMOVEUSER;
                }
                ?>
                </tbody>
            </table>

            <div class="mb-4">
                <a href="<?=$url->generate('backend/user/create')?>" class="btn btn-success">
                    <?=$translator->translate('identityAccess.create.user')?>
                </a>
            </div>

            <?php
                $pagination = OffsetPagination::widget()
                    ->paginator($paginator)
                    ->urlGenerator(fn ($page) => $url->generate('backend/user', ['page' => $page]));

                if ($pagination->isRequired()) {
                    echo $pagination;
                }
            ?>
        </div>
    </div>
<?php
