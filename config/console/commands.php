<?php

declare(strict_types=1);

use App\IdentityAccess\Access\Slice\Controller\Backend\Console\AccessListCommand;
use App\IdentityAccess\Access\Slice\Controller\Backend\Console\Assign\AssignAllPermissionsCommand;
use App\IdentityAccess\Access\Slice\Controller\Backend\Console\Assign\AssignPermissionCommand;
use App\IdentityAccess\Access\Slice\Controller\Backend\Console\Assign\AssignRoleCommand;
use App\IdentityAccess\Access\Slice\Controller\Backend\Console\Assign\RevokePermissionCommand;
use App\IdentityAccess\Access\Slice\Controller\Backend\Console\Assign\RevokeRoleCommand;
use App\IdentityAccess\Access\Slice\Controller\Backend\Console\AssignmentsListCommand;
use App\IdentityAccess\Access\Slice\Controller\Backend\Console\Management\AddAllChildPermissionsCommand;
use App\IdentityAccess\Access\Slice\Controller\Backend\Console\Management\AddChildPermissionCommand;
use App\IdentityAccess\Access\Slice\Controller\Backend\Console\Management\AddChildRoleCommand;
use App\IdentityAccess\Access\Slice\Controller\Backend\Console\Management\AddPermissionCommand;
use App\IdentityAccess\Access\Slice\Controller\Backend\Console\Management\AddRoleCommand;
use App\IdentityAccess\Access\Slice\Controller\Backend\Console\Management\RemoveAllAccessRightsCommand;
use App\IdentityAccess\Access\Slice\Controller\Backend\Console\Management\RemoveChildPermissionsCommand;
use App\IdentityAccess\Access\Slice\Controller\Backend\Console\Management\RemoveChildRoleCommand;
use App\IdentityAccess\Access\Slice\Controller\Backend\Console\Management\RemovePermissionCommand;
use App\IdentityAccess\Access\Slice\Controller\Backend\Console\Management\RemoveRoleCommand;
use App\IdentityAccess\Access\Slice\Controller\Backend\Console\UserAssignmentsCommand;
use App\IdentityAccess\Access\Slice\Controller\Backend\Console\ViewRoleCommand;
use App\IdentityAccess\User\Slice\User\Presentation\Backend\Console\CreateUserCommand;
use App\IdentityAccess\User\Slice\User\Presentation\Backend\Console\DeleteUserCommand;
use App\Site\Presentation\Backend\Console\Fixture\AddCommand;
use App\Site\Presentation\Backend\Console\Fixture\CreateAccessRights;
use App\Site\Presentation\Backend\Console\Router\ListCommand;
use App\Site\Presentation\Backend\Console\Translation\TranslateCommand;

return [
    'access:addAllChildPermissions' => AddAllChildPermissionsCommand::class,
    'access:addChildPermission' => AddChildPermissionCommand::class,
    'access:addChildRole' => AddChildRoleCommand::class,
    'access:addPermission' => AddPermissionCommand::class,
    'access:addRole' => AddRoleCommand::class,
    'access:removeAll' => RemoveAllAccessRightsCommand::class,
    'access:removeChildPermissions' => RemoveChildPermissionsCommand::class,
    'access:removeChildRole' => RemoveChildRoleCommand::class,
    'access:removePermission' => RemovePermissionCommand::class,
    'access:removeRole' => RemoveRoleCommand::class,

    'assign:allPermission' => AssignAllPermissionsCommand::class,
    'assign:permission' => AssignPermissionCommand::class,
    'assign:revokePermission' => RevokePermissionCommand::class,
    'assign:revokeRole' => RevokeRoleCommand::class,
    'assign:role' => AssignRoleCommand::class,

    'access:list' => AccessListCommand::class,
    'assignments:list' => AssignmentsListCommand::class,
    'assignments:user' => UserAssignmentsCommand::class,
    'access:viewRole' => ViewRoleCommand::class,

    'fixture:add' => AddCommand::class,
    'fixture:addAccess' => CreateAccessRights::class,

    'router:list' => ListCommand::class,

    'translator:translate' => TranslateCommand::class,

    'user:create' => CreateUserCommand::class,
    'user:delete' => DeleteUserCommand::class,
];
