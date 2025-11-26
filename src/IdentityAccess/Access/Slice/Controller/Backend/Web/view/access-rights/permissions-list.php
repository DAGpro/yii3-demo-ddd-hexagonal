<?php

declare(strict_types=1);


use App\IdentityAccess\Access\Slice\Service\PermissionDTO;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var UrlGeneratorInterface $url
 * @var Translator $translator
 * @var string $csrf
 * @var array $permissions
 */
?>
    <div class="main">
        <?= $this->render('../__access_menu', ['currentUrl' => 'permissions']) ?>

        <h2><?= $translator->translate('identityAccess.permissions') ?></h2>

        <div class="permissions-list m-2 mb-5 table-responsive">
            <table class="table table-striped border border-1">
                <thead>
                <tr>
                    <th scope="col"><?= $translator->translate('identityAccess.permission') ?> </th>
                    <th scope="col"><?= $translator->translate('identityAccess.action') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                /** @var PermissionDTO $permission */
                foreach ($permissions as $permission) {
                    echo <<<PERMISSION
                        <tr>
                            <td>{$permission->getName()}</td>
                            <td>
                                <form
                                    id="removePermission"
                                    action="{$url->generate('backend/access/remove-permission')}"
                                    method="post"
                                 >
                                    <input type="hidden" name="_csrf" value="{$csrf}">
                                    <input type="hidden" name="permission" value="{$permission->getName()}">

                                    <button type="submit" class="btn btn-sm btn-danger">
                                        {$translator->translate('identityAccess.remove')}
                                    </button>
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
                    <div class="card p-3 mb-3">
                        <form
                            id="addPermission"
                            action="{$url->generate('backend/access/add-permission')}"
                            method="post"
                            class="card-body"
                        >
                            <input type="hidden" name="_csrf" value="{$csrf}">
                            <div class="mb-3 form-group">
                                <label for="permission" class="form-label required">
                                    {$translator->translate('identityAccess.permission.name')}
                                </label>
                                <input class="form-control" type="text" name="permission" value="">
                            </div>

                            <button type="submit" class="btn btn-sm btn-success">
                                {$translator->translate('identityAccess.add.permission')}
                            </button>
                        </form>
                    </div>
                </div>
                ADDPERMISSION;

            echo <<<ADDROLE
                <div class="col-md-6">
                    <div class="card p-3">
                        <form
                            id="addRole"
                            action="{$url->generate('backend/access/add-role')}"
                            method="post"
                            class="card-body"
                        >
                            <input type="hidden" name="_csrf" value="{$csrf}">
                            <div class="mb-3 form-group">
                                <label for="role" class="form-label required">
                                    {$translator->translate('identityAccess.role.name')}
                                </label>
                                <input class="form-control" type="text" name="role" value="">
                            </div>

                            <button type="submit" class="btn btn-sm btn-success">
                                {$translator->translate('identityAccess.add.role')}
                            </button>
                        </form>
                    </div>
                </div>
                ADDROLE;
            ?>
        </div>

    </div>
<?php
