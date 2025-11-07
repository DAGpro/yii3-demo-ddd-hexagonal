<?php

declare(strict_types=1);


use App\IdentityAccess\Access\Application\Service\RoleDTO;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var UrlGeneratorInterface $url
 * @var Translator $translator
 * @var string $csrf
 * @var array $roles
 */


?>
    <div class="main">
        <?= $this->render('../__access_menu', ['currentUrl' => 'roles']) ?>

        <?php
        echo <<<CLEARACCESS
            <h2 class="mb-4 p-2">
                <span>{$translator->translate('identityAccess.roles')}</span>
                <form
                    id="clearAccess" class="float-end"
                    action="{$url->generate('backend/access/clear-access')}" method="post"
                >
                    <input type="hidden" name="_csrf" value="{$csrf}">

                    <button type="submit" class="btn btn-sm btn-danger">
                        {$translator->translate('identityAccess.remove.all.access.rights')}
                    </button>
                </form>
            </h2>
            CLEARACCESS;
        ?>

        <div class="role-list m-2 mb-5 table-responsive">
            <table class="table table-striped border">
                <thead>
                <tr>
                    <th scope="col"><?= $translator->translate('identityAccess.role') ?> </th>
                    <th scope="col"><?= $translator->translate('identityAccess.child.roles') ?></th>
                    <th scope="col"><?= $translator->translate('identityAccess.nested.roles') ?></th>
                    <th scope="col"><?= $translator->translate('identityAccess.child.permissions') ?></th>
                    <th scope="col"><?= $translator->translate('identityAccess.nested.permissions') ?></th>
                    <th scope="col"><?= $translator->translate('identityAccess.action') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                /** @var RoleDTO $role */
                foreach ($roles as $role) {
                    $urlViewRole = $url->generate('backend/access/view-role', ['role_name' => $role->getName()]);
                    echo <<<ROLE
                        <tr>
                            <td>
                                <a href="$urlViewRole" class="fw-bold">
                                    {$role->getName()}
                                </a>
                            </td>
                            <td>{$role->getChildRolesName()}</td>
                            <td>{$role->getNestedRolesName()}</td>
                            <td>{$role->getChildPermissionsName()}</td>
                            <td>{$role->getNestedPermissionsName()}</td>
                            <td>
                                <form
                                    id="removeRole"
                                    action="{$url->generate('backend/access/remove-role')}"
                                    method="post"
                                >
                                    <input type="hidden" name="_csrf" value="{$csrf}">
                                    <input type="hidden" name="role" value="{$role->getName()}">

                                    <button type="submit" class="btn btn-sm btn-danger">
                                        {$translator->translate('identityAccess.remove.role')}
                                    </button>
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
                    <div class="card p-3 mb-3">
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

            echo <<<CLEARACCESS
                <div class="col-md-12">
                    <div class="card p-3 mb-3">
                        <form
                            id="clearAccess"
                            action="{$url->generate('backend/access/clear-access')}"
                            method="post"
                            class="card-body"
                        >
                            <input type="hidden" name="_csrf" value="{$csrf}">
                            <button type="submit" class="btn btn-danger">
                                {$translator->translate('identityAccess.remove.all.access.rights')}
                            </button>
                        </form>
                    </div>
                </div>
                CLEARACCESS;
            ?>
        </div>
    </div>
<?php
