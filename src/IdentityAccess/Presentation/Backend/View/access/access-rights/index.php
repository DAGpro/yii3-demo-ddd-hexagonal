<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Form\Widget\Field $field
 * @var \Yiisoft\Translator\Translator $translator
 * @var string $csrf
 * @var string|null $currentUrl
 * @var array $roles
 */


?>
<div class="main">
    <?= $this->render('../__access_menu', ['currentUrl' => $currentUrl])?>

    <?php
    echo <<<CLEARACCESS
        <h2 class="mb-4 p-2">
            <span>{$translator->translate('identityAccess.roles')}</span>
            <form id="clearAccess" class="float-end" action="{$url->generate('backend/access/clear-access')}" method="post" >
                <input type="hidden" name="_csrf" value="{$csrf}">

                <button type="submit" class="btn btn-sm btn-danger">{$translator->translate('identityAccess.remove.all.access.rights')}</button>
            </form>
        </h2>
        CLEARACCESS;
    ?>

    <div class="role-list m-2">
        <table class="table mb-5 border border-light border-3">
            <thead>
            <tr>
                <th scope="col"><?=$translator->translate('identityAccess.role')?> </th>
                <th scope="col"><?=$translator->translate('identityAccess.child.roles')?></th>
                <th scope="col"><?=$translator->translate('identityAccess.nested.roles')?></th>
                <th scope="col"><?=$translator->translate('identityAccess.child.permissions')?></th>
                <th scope="col"><?=$translator->translate('identityAccess.nested.permissions')?></th>
                <th scope="col"><?=$translator->translate('identityAccess.action')?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            /** @var \App\IdentityAccess\Access\Application\Service\RoleDTO $role */
            foreach ($roles as $role) {
                echo <<<ROLE
                    <tr>
                        <td><a href="{$url->generate('backend/access/view-role', ['role_name' => $role->getName()])}" class="fw-bold">{$role->getName()}</a></td>
                        <td>{$role->getChildRolesName()}</td>
                        <td>{$role->getNestedRolesName()}</td>
                        <td>{$role->getChildPermissionsName()}</td>
                        <td>{$role->getNestedPermissionsName()}</td>
                        <td>
                            <form id="removeRole" action="{$url->generate('backend/access/remove-role')}" method="post" >
                                <input type="hidden" name="_csrf" value="{$csrf}">
                                <input type="hidden" name="role" value="{$role->getName()}">

                                <button type="submit" class="btn btn-sm btn-danger">{$translator->translate('identityAccess.remove.role')}</button>
                            </form>
                        </td>
                    </tr>
                ROLE;
            }
            ?>
            </tbody>
        </table>
    </div>

    <div class="row">
        <?php
        echo <<<ADDPERMISSION
        <div class="col-md-6">
            <div class="border border-3 border-light p-3 mb-3">
                <form id="addPermission" action="{$url->generate('backend/access/add-permission')}" method="post" >
                    <input type="hidden" name="_csrf" value="{$csrf}">
                    <div class="mb-3 form-group">
                        <label for="permission" class="form-label required">{$translator->translate('identityAccess.permission.name')}</label>
                        <input class="form-control" type="text" name="permission" value="">
                    </div>

                    <button type="submit" class="btn btn-sm btn-success">{$translator->translate('identityAccess.add.permission')}</button>
                </form>
            </div>
        </div>
        ADDPERMISSION;

        echo <<<ADDROLE
        <div class="col-md-6">
            <div class="border border-3 border-light p-3 mb-3">
                <form id="addRole" action="{$url->generate('backend/access/add-role')}" method="post" >
                    <input type="hidden" name="_csrf" value="{$csrf}">
                    <div class="mb-3 form-group">
                        <label for="role" class="form-label required">{$translator->translate('identityAccess.role.name')}</label>
                        <input class="form-control" type="text" name="role" value="">
                    </div>

                    <button type="submit" class="btn btn-sm btn-success">{$translator->translate('identityAccess.add.role')}</button>
                </form>
            </div>
        </div>
        ADDROLE;

        echo <<<CLEARACCESS
        <div class="col-md-12">
            <div class="border border-3 border-light p-3 mb-3">
                <form id="clearAccess" action="{$url->generate('backend/access/clear-access')}" method="post" >
                    <input type="hidden" name="_csrf" value="{$csrf}">
                    <button type="submit" class="btn btn-danger">{$translator->translate('identityAccess.remove.all.access.rights')}</button>
                </form>
            </div>
        </div>
        CLEARACCESS;
        ?>
    </div>

</div>
<?php
