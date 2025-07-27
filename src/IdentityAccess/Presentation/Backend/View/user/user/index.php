<?php

declare(strict_types=1);


use App\IdentityAccess\User\Domain\User;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;
use Yiisoft\Yii\DataView\Pagination\OffsetPagination;
use Yiisoft\Yii\DataView\Pagination\PaginationContext;

/**
 * @var WebView $this
 * @var UrlGeneratorInterface $url
 * @var Translator $translator
 * @var OffsetPaginator $paginator
 * @var array $action
 * @var string $title
 * @var string $csrf
 */

?>
    <div class="main">
        <?php
        echo <<<CLEARUSERS
            <h2 class="mb-4 p-2">
                <span>{$translator->translate('identityAccess.users')}</span>
                <form
                    id="clearUsers" class="float-end"
                    action="{$url->generate('backend/user/clear-users')}"
                    method="post"
                >
                <input type="hidden" name="_csrf" value="{$csrf}">
                <button type="submit" class="btn btn-sm btn-danger">
                    {$translator->translate('identityAccess.remove.users')}
                </button>
                </form>
            </h2>
            CLEARUSERS;
        ?>

        <div class="users m-2">
            <div class="table-responsive">
                <table class="table table-striped mb-4 border">
                    <thead>
                    <tr>
                        <th scope="col"><?= $translator->translate('identityAccess.user.id') ?></th>
                        <th scope="col"><?= $translator->translate('identityAccess.login') ?></th>
                        <th scope="col"><?= $translator->translate('identityAccess.access.rights') ?></th>
                        <th scope="col"><?= $translator->translate('identityAccess.action') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    /** @var  User $user */
                    foreach ($paginator->read() as $user) {
                        $urlProfile = $url->generate('backend/user/profile', ['user_id' => $user->getId()]);
                        $urlAssignments = $url->generate('backend/access/user-assignments',
                            ['user_id' => $user->getId()],
                        );
                        echo <<<REMOVEUSER
                            <tr>
                                <td>
                                    <a href="{$urlProfile}" class="fw-bold">{$user->getId()}</a>
                                </td>
                                <td>{$user->getLogin()}</td>
                                <td>
                                    <a href="{$urlAssignments}" class="fw-bold">
                                        {$translator->translate('identityAccess.user.assignments')}
                                    </a>
                                </td>
                                <td>
                                    <form
                                        id="removeIdentity"
                                        action="{$url->generate('backend/user/delete')}"
                                        method="post"
                                    >
                                        <input type="hidden" name="_csrf" value="$csrf">
                                        <input type="hidden" name="user_id" value="{$user->getId()}">

                                        <button type="submit" class="btn btn-sm btn-danger">
                                            {$translator->translate('identityAccess.remove.user')}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            REMOVEUSER;
                    }
                    ?>
                    </tbody>
                </table>
            </div>

            <div class="mb-4">
                <a href="<?= $url->generate('backend/user/create') ?>" class="btn btn-success">
                    <?= $translator->translate('identityAccess.create.user') ?>
                </a>
            </div>

            <?php
            $pagination = Div::tag()
                ->content(
                    new OffsetPagination()
                        ->withContext(
                            new PaginationContext(
                                $url->generate(
                                    'backend/user',
                                ) . 'page/' . PaginationContext::URL_PLACEHOLDER,
                                $url->generate(
                                    'backend/user',
                                ) . 'page/' . PaginationContext::URL_PLACEHOLDER,
                                $url->generate('backend/user'),
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

            if ($paginator->getTotalItems() > 0) {
                echo $pagination;
            }
            ?>
        </div>
    </div>
<?php
