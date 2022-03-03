<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Form\Widget\Field $field
 * @var \Yiisoft\Translator\Translator $translator
 * @var string $csrf
 * @var string|null $currentUrl
 * @var array $users
 */


?>
<div class="main">
    <?= $this->render('../__access_menu', ['currentUrl' => $currentUrl])?>

    <h2><?=$translator->translate('identityAccess.users.assignments')?></h2>

    <div class="assignments m-2">
        <?php if (!empty($users)): ?>
        <div class="table-responsive">
            <table class="table table-responsive mb-5 border border-light border-3">
                <thead>
                <tr>
                    <th scope="col"><?=$translator->translate('identityAccess.user')?></th>
                    <th scope="col"><?=$translator->translate('identityAccess.roles')?></th>
                    <th scope="col"><?=$translator->translate('identityAccess.permissions')?></th>
                    <th scope="col"><?=$translator->translate('identityAccess.action')?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                /** @var \App\IdentityAccess\Access\Application\Service\UserAssignmentsDTO $user */
                foreach ($users as $user) {
                    echo <<<REVOKEASSIGNMENTSUSER
                    <tr>
                        <td><a href="{$url->generate('backend/access/user-assignments', ['user_id' => $user->getId()])}" class="fw-bold">{$user->getLogin()}</a></td>
                        <td>{$user->getRolesName()}</td>
                        <td>{$user->getPermissionsName()}</td>
                        <td>
                            <form id="revokeIdentityAssignments" action="{$url->generate('backend/access/revoke-all')}" method="post" >
                                <input type="hidden" name="_csrf" value="{$csrf}">
                                <input type="hidden" name="user_id" value="{$user->getId()}">

                                <button type="submit" class="btn btn-sm btn-danger">{$translator->translate('identityAccess.revoke.assignments')}</button>
                            </form>
                        </td>
                    </tr>
                    REVOKEASSIGNMENTSUSER;
                }
                ?>
                </tbody>
            </table>
        </div>

        <?php endif; ?>
    </div>
    <?php
    echo <<<CLEARASSIGNMENTS
    <div class="border border-3 border-light p-3">
        <form id="clearAssignments" action="{$url->generate('backend/access/clear-assignments')}" method="post" >
            <input type="hidden" name="_csrf" value="{$csrf}">
            <button type="submit" class="btn btn-sm btn-danger">{$translator->translate('identityAccess.clear.assignments')}</button>
        </form>
    </div>
    CLEARASSIGNMENTS;
    ?>
</div>
<?php
