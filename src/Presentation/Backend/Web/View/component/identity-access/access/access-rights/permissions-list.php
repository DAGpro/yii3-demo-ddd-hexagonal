<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var \Yiisoft\Router\UrlGeneratorInterface $url
 * @var \Yiisoft\Form\Widget\Field $field
 * @var \Yiisoft\Translator\Translator $translator
 * @var string $csrf
 * @var string|null $currentUrl
 * @var array $permissions
 */

?>
    <div class="main">
        <?= $this->render('../__access_menu', ['currentUrl' => $currentUrl])?>

        <h2><?=$translator->translate('identityAccess.permissions')?></h2>

        <div class="permissions-list m-2">
            <table class="table mb-5 border border-light border-3">
                <thead>
                <tr>
                    <th scope="col"><?=$translator->translate('identityAccess.permission')?> </th>
                    <th scope="col"><?=$translator->translate('identityAccess.action')?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                /** @var \App\Core\Component\IdentityAccess\Access\Application\Service\PermissionDTO $permission */
                foreach ($permissions as $permission) {
                    echo <<<PERMISSION
                    <tr>
                        <td>{$permission->getName()}</td>
                        <td>
                            <form id="removePermission" action="{$url->generate('backend/access/remove-permission')}" method="post" >
                                <input type="hidden" name="_csrf" value="{$csrf}">
                                <input type="hidden" name="permission" value="{$permission->getName()}">

                                <button type="submit" class="btn btn-sm btn-danger">{$translator->translate('identityAccess.remove')}</button>
                            </form>
                        </td>
                    </tr>
                    PERMISSION;
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
                <div class="border border-3 border-light p-3">
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
            ?>
        </div>

    </div>
<?php
