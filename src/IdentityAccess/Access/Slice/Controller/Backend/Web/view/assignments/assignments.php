<?php

declare(strict_types=1);


use App\IdentityAccess\Access\Slice\Service\UserAssignmentsDTO;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var UrlGeneratorInterface $url
 * @var Translator $translator
 * @var string $csrf
 * @var string|null $currentUrl
 * @var array $users
 */

?>

    <div class="main">
        <?= $this->render('../__access_menu', ['currentUrl' => $currentUrl]) ?>

        <h2 class="mb-4"><?= $translator->translate('identityAccess.users.assignments') ?></h2>

        <div class="assignments">
            <?php
            if (!empty($users)): ?>
                <div class="table-responsive">
                    <table class="table table-striped mb-5 border border-1">
                        <thead>
                        <tr>
                            <th scope="col"><?= $translator->translate('identityAccess.user') ?></th>
                            <th scope="col"><?= $translator->translate('identityAccess.roles') ?></th>
                            <th scope="col"><?= $translator->translate('identityAccess.permissions') ?></th>
                            <th scope="col"><?= $translator->translate('identityAccess.action') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        /** @var UserAssignmentsDTO $user */
                        foreach ($users as $user) {
                            $urlUserAssignments = $url->generate(
                                'backend/access/user-assignments',
                                ['user_id' => $user->getId()],
                            );
                            echo <<<REVOKEASSIGNMENTSUSER
                                <tr>
                                    <td>
                                    <a href="{$urlUserAssignments}" class="fw-bold">
                                        {$user->getLogin()}
                                    </a>
                                    </td>
                                    <td>{$user->getRolesName()}</td>
                                    <td>{$user->getPermissionsName()}</td>
                                    <td>
                                        <form
                                            id="revokeIdentityAssignments"
                                            action="{$url->generate('backend/access/revoke-all')}"
                                            method="post"
                                        >
                                            <input type="hidden" name="_csrf" value="{$csrf}">
                                            <input type="hidden" name="user_id" value="{$user->getId()}">

                                            <button type="submit" class="btn btn-sm btn-danger">
                                                {$translator->translate('identityAccess.revoke.assignments')}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                REVOKEASSIGNMENTSUSER;
                        }
                        ?>
                        </tbody>
                    </table>
                </div>

            <?php
            endif; ?>
        </div>
        <?php
        echo <<<CLEARASSIGNMENTS
            <div class="border border-1 p-3">
                <form
                    id="clearAssignments"
                    action="{$url->generate('backend/access/clear-assignments')}"
                    method="post"
                >
                    <input type="hidden" name="_csrf" value="{$csrf}">
                    <button type="submit" class="btn btn-sm btn-danger">
                        {$translator->translate('identityAccess.clear.assignments')}
                    </button>
                </form>
            </div>
            CLEARASSIGNMENTS;
        ?>
    </div>
<?php
