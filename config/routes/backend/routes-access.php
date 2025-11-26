<?php

declare(strict_types=1);

use App\IdentityAccess\Access\Slice\Controller\Backend\Web\AccessManagementController;
use App\IdentityAccess\Access\Slice\Controller\Backend\Web\AccessRightsController;
use App\IdentityAccess\Access\Slice\Controller\Backend\Web\AssignAccessController;
use App\IdentityAccess\Access\Slice\Controller\Backend\Web\AssignmentsController;
use App\IdentityAccess\ContextMap\Middleware\AccessRoleChecker;
use Yiisoft\Auth\Middleware\Authentication;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    Group::create('/backend')
        ->routes(
            Group::create('/access')
                ->routes(
                    Route::get('[page/{page:\d+}]')
                        ->name('backend/access')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AccessRightsController::class, 'index']),
                    Route::get('/view-role[/{role_name}]')
                        ->name('backend/access/view-role')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AccessRightsController::class, 'viewRole']),
                    Route::get('/permissions[/page-{page:\d+}]')
                        ->name('backend/access/permissions')
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->middleware(Authentication::class)
                        ->action([AccessRightsController::class, 'permissionsList']),
                    Route::get('/assignments')
                        ->name('backend/access/assignments')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AssignmentsController::class, 'assignments']),
                    Route::get('/user-assignments/{user_id}')
                        ->name('backend/access/user-assignments')
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->middleware(Authentication::class)
                        ->action([AssignmentsController::class, 'userAssignments']),
                    Route::post('/assign-role')
                        ->name('backend/access/assign-role')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AssignAccessController::class, 'assignRole']),
                    Route::post('/revoke-role')
                        ->name('backend/access/revoke-role')
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->middleware(Authentication::class)
                        ->action([AssignAccessController::class, 'revokeRole']),
                    Route::post('/assign-permission')
                        ->name('backend/access/assign-permission')
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->middleware(Authentication::class)
                        ->action([AssignAccessController::class, 'assignPermission']),
                    Route::post('/revoke-permission')
                        ->name('backend/access/revoke-permission')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AssignAccessController::class, 'revokePermission']),
                    Route::post('/revoke-all')
                        ->name('backend/access/revoke-all')
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->middleware(Authentication::class)
                        ->action([AssignAccessController::class, 'revokeAll']),
                    Route::post('/clear-assignments')
                        ->name('backend/access/clear-assignments')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AssignAccessController::class, 'clearAssignments']),
                    Route::post('/add-role')
                        ->name('backend/access/add-role')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AccessManagementController::class, 'addRole']),
                    Route::post('/remove-role')
                        ->name('backend/access/remove-role')
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->middleware(Authentication::class)
                        ->action([AccessManagementController::class, 'removeRole']),
                    Route::post('/add-permission')
                        ->name('backend/access/add-permission')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AccessManagementController::class, 'addPermission']),
                    Route::post('/remove-permission')
                        ->name('backend/access/remove-permission')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AccessManagementController::class, 'removePermission']),
                    Route::post('/add-child-role')
                        ->name('backend/access/add-child-role')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AccessManagementController::class, 'addChildRole']),
                    Route::post('/remove-child-role')
                        ->name('backend/access/remove-child-role')
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->middleware(Authentication::class)
                        ->action([AccessManagementController::class, 'removeChildRole']),
                    Route::post('/add-child-permission')
                        ->name('backend/access/add-child-permission')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AccessManagementController::class, 'addChildPermission']),
                    Route::post('/remove-child-permission')
                        ->name('backend/access/remove-child-permission')
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->middleware(Authentication::class)
                        ->action([AccessManagementController::class, 'removeChildPermission']),
                    Route::post('/remove-children')
                        ->name('backend/access/remove-children')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AccessManagementController::class, 'removeChildren']),
                    Route::post('/clear-access/')
                        ->name('backend/access/clear-access')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AccessManagementController::class, 'clearAccessRights']),
                ),
        ),

];
