<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Form\Widget\Field $field
 * @var \Yiisoft\Translator\Translator $translator
 * @var \App\Core\Component\IdentityAccess\Access\Application\Service\UserAssignmentsDTO $user
 * @var string|null $currentUrl
 * @var string $csrf
 * @var array $roles
 * @var array $permissions
 */

?>
    <div class="main">
        <?= $this->render('../__access_menu', ['currentUrl' => $currentUrl])?>

        <?php
        echo <<<REVOKEASSIGNMENTSIDENTITY
        <h2 class="mb-4 p-2">
            {$translator->translate('identityAccess.user.assignments')}  <a href="{$url->generate('backend/user/profile', ['user_id' => $user->getId()])}">{$user->getLogin()}</a>
            <form id="revokeAssignmentsUser" class="float-end" action="{$url->generate('backend/access/revoke-all')}" method="post" >
                <input type="hidden" name="_csrf" value="{$csrf}">
                <input type="hidden" name="user_id" value="{$user->getId()}">

                <button type="submit" class="btn btn-sm btn-danger">{$translator->translate('identityAccess.revoke.assignments')}</button>
            </form>
        </h2>
        REVOKEASSIGNMENTSIDENTITY;
        ?>

        <h3><?=$translator->translate('identityAccess.user.roles')?></h3>

        <div class="identity-roles m-2">
            <?php if ($user->existRoles()): ?>
            <div class="table-responsive">
                <table class="table mb-5 border border-light border-3">
                    <thead>
                    <tr>
                        <th scope="col"><?=$translator->translate('identityAccess.role')?></th>
                        <th scope="col"><?=$translator->translate('identityAccess.child.roles')?></th>
                        <th scope="col"><?=$translator->translate('identityAccess.nested.roles')?></th>
                        <th scope="col"><?=$translator->translate('identityAccess.nested.permissions')?></th>
                        <th scope="col"><?=$translator->translate('identityAccess.child.permissions')?></th>
                        <th scope="col"><?=$translator->translate('identityAccess.action')?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($user->getRoles() as $role) {
                        echo <<<REVOKEROLE
                        <tr>
                            <td>{$role->getName()}</td>
                            <td>{$role->getChildRolesName()}</td>
                            <td>{$role->getNestedRolesName()}</td>
                            <td>{$role->getNestedPermissionsName()}</td>
                            <td>{$role->getChildPermissionsName()}</td>
                            <td>
                                <form id="revokeIdentityRole" action="{$url->generate('backend/access/revoke-role', ['user_id' => $user->getId(), 'role' => $role->getName()])}" method="post" >
                                    <input type="hidden" name="_csrf" value="{$csrf}">
                                    <input type="hidden" name="role" value="{$role->getName()}">
                                    <input type="hidden" name="user_id" value="{$user->getId()}">

                                    <button type="submit" class="btn btn-sm btn-danger">{$translator->translate('identityAccess.revoke.role')}</button>
                                </form>
                            </td>
                        </tr>
                        REVOKEROLE;
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p><?=$translator->translate('identityAccess.no.roles.assigned')?></p>
            <?php endif;?>
        </div>

        <?php
        echo <<<ASSIGNROLE
        <div class="border border-3 border-light p-3 mb-3">
            <form class="mb-3" id="assignRole" action="{$url->generate('backend/access/assign-role', ['user_id' => $user->getId()])}" method="post" >
                <input type="hidden" name="_csrf" value="{$csrf}">
                <div class="mb-3 form-group">
                    <label for="role" class="form-label required">{$translator->translate('identityAccess.role.name')}</label>
                    <input class="form-control" type="text" name="role" value="">
                </div>
                <input class="" type="hidden" name="user_id" value="{$user->getId()}">

                <button type="submit" class="btn btn-sm btn-success">{$translator->translate('identityAccess.assign.role')}</button>
            </form>
        </div>
        ASSIGNROLE;
        ?>

        <h3><?=$translator->translate('identityAccess.user.permissions')?></h3>
        <div class="identity-permissions m-2">
            <?php if ($user->existPermissions()): ?>
            <table class="table mb-5 border border-light border-3">
                <thead>
                <tr>
                    <th scope="col"><?=$translator->translate('identityAccess.permissions')?></th>
                    <th scope="col"><?=$translator->translate('identityAccess.action')?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                /** @var array $permissions */
                foreach ($user->getPermissions() as $permission) {
                    echo <<<REVOKEPERMISSION
                    <tr>
                        <td>{$permission->getName()}</td>
                        <td>
                            <form id="revokeIdentityPermission" action="{$url->generate('backend/access/revoke-permission', ['user_id' => $user->getId(), 'permission' => $permission->getName()])}" method="post" >
                                <input type="hidden" name="_csrf" value="{$csrf}">
                                <input type="hidden" name="permission" value="{$permission->getName()}">
                                <input type="hidden" name="user_id" value="{$user->getId()}">

                                <button type="submit" class="btn btn-sm btn-danger">{$translator->translate('identityAccess.revoke.permission')}</button>
                            </form>
                        </td>
                    </tr>
                    REVOKEPERMISSION;
                }
                ?>
                </tbody>
            </table>
            <?php else: ?>
                <p><?=$translator->translate('identityAccess.no.permissions.assigned')?></p>
            <?php endif; ?>
        </div>
        <?php
        echo <<<ASSIGNPERMISSION
        <div class="border border-3 border-light p-3">
            <form id="assignPermission" action="{$url->generate('backend/access/assign-permission', ['user_id' => $user->getId()])}" method="post" >
                <input type="hidden" name="_csrf" value="{$csrf}">
                <div class="mb-3 form-group">
                    <label for="permission" class="form-label required">Permission name</label>
                    <input class="form-control" type="text" name="permission" value="">
                </div>
                 <input class="" type="hidden" name="user_id" value="{$user->getId()}">

                <button type="submit" class="btn btn-sm btn-success">{$translator->translate('identityAccess.assign.permission')}</button>
            </form>
        </div>
        ASSIGNPERMISSION;
        ?>
    </div>
<?php
