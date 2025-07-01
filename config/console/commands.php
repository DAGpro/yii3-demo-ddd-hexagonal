<?php

declare(strict_types=1);

use App\IdentityAccess\Presentation\Backend\Console\Access\AccessListCommand;
use App\IdentityAccess\Presentation\Backend\Console\Access\Assign\AssignAllPermissionsCommand;
use App\IdentityAccess\Presentation\Backend\Console\Access\Assign\AssignPermissionCommand;
use App\IdentityAccess\Presentation\Backend\Console\Access\Assign\AssignRoleCommand;
use App\IdentityAccess\Presentation\Backend\Console\Access\Assign\RevokePermissionCommand;
use App\IdentityAccess\Presentation\Backend\Console\Access\Assign\RevokeRoleCommand;
use App\IdentityAccess\Presentation\Backend\Console\Access\AssignmentsListCommand;
use App\IdentityAccess\Presentation\Backend\Console\Access\Management\AddAllChildPermissionsCommand;
use App\IdentityAccess\Presentation\Backend\Console\Access\Management\AddChildPermissionCommand;
use App\IdentityAccess\Presentation\Backend\Console\Access\Management\AddChildRoleCommand;
use App\IdentityAccess\Presentation\Backend\Console\Access\Management\AddPermissionCommand;
use App\IdentityAccess\Presentation\Backend\Console\Access\Management\AddRoleCommand;
use App\IdentityAccess\Presentation\Backend\Console\Access\Management\RemoveAllAccessRightsCommand;
use App\IdentityAccess\Presentation\Backend\Console\Access\Management\RemoveChildPermissionsCommand;
use App\IdentityAccess\Presentation\Backend\Console\Access\Management\RemoveChildRoleCommand;
use App\IdentityAccess\Presentation\Backend\Console\Access\Management\RemovePermissionCommand;
use App\IdentityAccess\Presentation\Backend\Console\Access\Management\RemoveRoleCommand;
use App\IdentityAccess\Presentation\Backend\Console\Access\UserAssignmentsCommand;
use App\IdentityAccess\Presentation\Backend\Console\Access\ViewRoleCommand;
use App\IdentityAccess\Presentation\Backend\Console\User\CreateUserCommand;
use App\IdentityAccess\Presentation\Backend\Console\User\DeleteUserCommand;
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
