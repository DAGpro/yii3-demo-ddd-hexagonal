<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Form\Widget\Field $field
 * @var \Yiisoft\Translator\Translator $translator
 * @var \App\Core\Component\IdentityAccess\Access\Application\Service\RoleDTO $role
 * @var string|null $currentUrl
 * @var string $title
 * @var string $csrf
 */


?>
    <div class="main">
        <?= $this->render('../__access_menu', ['currentUrl' => $currentUrl])?>
        <div class="role m-2">
            <?php
            echo <<<REMOVEROLE
            <h2 class="mb-4">
                <span>{$translator->translate('identityAccess.role')} : {$role->getName()}</span>
                <form id="removeRole" class="float-end" action="{$url->generate('backend/access/remove-role')}" method="post" >
                    <input type="hidden" name="_csrf" value="{$csrf}">
                    <input type="hidden" name="role" value="{$role->getName()}">

                    <button type="submit" class="btn btn-danger">{$translator->translate('identityAccess.remove.role')}</button>
                </form>
            </h2>
            REMOVEROLE;
            ?>
        <?php if(!empty($role->getChildRoles())): ?>
            <h4><?=$translator->translate('identityAccess.child.roles')?></h4>
            <div class="child-roles m-2">
                <table class="table mb-5 border border-light border-3">
                    <thead>
                    <tr>
                        <th scope="col"><?=$translator->translate('identityAccess.child.role')?></th>
                        <th scope="col"><?=$translator->translate('identityAccess.action')?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    /** @var \App\Core\Component\IdentityAccess\Access\Application\Service\RoleDTO $role */
                    foreach ($role->getChildRoles() as $childRole) {
                        echo <<<REMOVECHILDROLE
                            <tr>
                                <td><a href="{$url->generate('backend/access/view-role', ['role_name' => $childRole->getName()])}" class="fw-bold">{$childRole->getName()}</a></td>
                                <td>
                                    <form id="removeChildRole" action="{$url->generate('backend/access/remove-child-role')}" method="post" >
                                        <input type="hidden" name="_csrf" value="{$csrf}">
                                        <input type="hidden" name="parent_role" value="{$role->getName()}">
                                        <input type="hidden" name="child_role" value="{$childRole->getName()}">

                                        <button type="submit" class="btn btn-sm btn-danger">{$translator->translate('identityAccess.remove.role')}</button>
                                    </form>
                                </td>
                            </tr>
                        REMOVECHILDROLE;
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        <?php else:?>
            <h4><?=$translator->translate('identityAccess.no.add.roles')?></h4>
        <?php endif;?>
        <?php
        echo <<<ADDCHILDROLE
            <div class="border border-3 border-light mb-4 p-3">
                <form id="addChildRole" action="{$url->generate('backend/access/add-child-role')}" method="post" >
                    <input type="hidden" name="_csrf" value="{$csrf}">
                    <div class="mb-3 form-group">
                        <label for="child-role" class="form-label required">{$translator->translate('identityAccess.child.role.name')}</label>
                        <input class="form-control" type="text" name="child_role" value="">
                    </div>
                    <input class="form-control" type="hidden" name="parent_role" value="{$role->getName()}">
                    <button type="submit" class="btn btn-sm btn-success">{$translator->translate('identityAccess.add.child.role')}</button>
                </form>
            </div>
        ADDCHILDROLE;
        ?>
        <?php if(!empty($role->getChildPermissions())): ?>
            <h4><?=$translator->translate('identityAccess.child.permissions')?></h4>
            <div class="child-permissions m-2">
                <table class="table mb-5 border border-light border-3">
                    <thead>
                    <tr>
                        <th scope="col"><?=$translator->translate('identityAccess.child.permission')?></th>
                        <th scope="col"><?=$translator->translate('identityAccess.action')?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    /** @var \App\Core\Component\IdentityAccess\Access\Application\Service\PermissionDTO $childPermission */
                    foreach ($role->getChildPermissions() as $childPermission) {
                        echo <<<REMOVECHILDPERMISSION
                        <tr>
                            <td>{$childPermission->getName()}</td>
                            <td>
                                <form id="removeChildPermission" action="{$url->generate('backend/access/remove-child-permission')}" method="post" >
                                    <input type="hidden" name="_csrf" value="{$csrf}">
                                    <input type="hidden" name="parent_role" value="{$role->getName()}">
                                    <input type="hidden" name="child_permission" value="{$childPermission->getName()}">

                                    <button type="submit" class="btn btn-sm btn-danger">{$translator->translate('identityAccess.remove')}</button>
                                </form>
                            </td>
                        </tr>
                        REMOVECHILDPERMISSION;
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <h4><?=$translator->translate('identityAccess.no.add.permissions')?></h4>
        <?php endif;?>
        <?php
        echo <<<ADDCHILDPERMISSION
            <div class="border border-3 border-light mb-4 p-3">
                <form id="addChildPermission" action="{$url->generate('backend/access/add-child-permission')}" method="post" >
                    <input type="hidden" name="_csrf" value="{$csrf}">
                    <div class="mb-3 form-group">
                        <label for="child-permission" class="form-label required">{$translator->translate('identityAccess.child.permission.name')}</label>
                        <input class="form-control" type="text" name="child_permission" value="">
                    </div>
                    <input class="form-control" type="hidden" name="parent_role" value="{$role->getName()}">
                    <button type="submit" class="btn btn-sm btn-success">{$translator->translate('identityAccess.add.child.permission')}</button>
                </form>
            </div>
            ADDCHILDPERMISSION;
        ?>
        <?php
        echo <<<REMOVECHILDREN
            <div class="border border-3 border-light mb-4 p-3">
                <form id="removeRoleChildren" action="{$url->generate('backend/access/remove-children')}" method="post" >
                    <input type="hidden" name="_csrf" value="{$csrf}">
                    <input class="form-control" type="hidden" name="parent_role" value="{$role->getName()}">
                    <button type="submit" class="btn btn-sm btn-danger">{$translator->translate('identityAccess.remove.children')}</button>
                </form>
            </div>
            REMOVECHILDREN;
        ?>
        </div>
    </div>
<?php
