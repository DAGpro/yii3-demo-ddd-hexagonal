<?php

declare(strict_types=1);

use App\IdentityAccess\ContextMap\Middleware\AccessRoleChecker;
use App\IdentityAccess\Presentation\Backend\Web\Access\AccessManagementController;
use App\IdentityAccess\Presentation\Backend\Web\Access\AccessRightsController;
use App\IdentityAccess\Presentation\Backend\Web\Access\AssignAccessController;
use App\IdentityAccess\Presentation\Backend\Web\Access\AssignmentsController;
use Yiisoft\Auth\Middleware\Authentication;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    // Access routes
    Group::create('/backend')
        ->routes(
            Group::create('/access')
                ->routes(
                    //Access Rights Index
                    Route::get('[page/{page:\d+}]')
                        ->name('backend/access')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AccessRightsController::class, 'index']),
                    //View Role
                    Route::get('/view-role[/{role_name}]')
                        ->name('backend/access/view-role')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AccessRightsController::class, 'viewRole']),
                    //Permissions List
                    Route::get('/permissions[/page-{page:\d+}]')
                        ->name('backend/access/permissions')
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->middleware(Authentication::class)
                        ->action([AccessRightsController::class, 'permissionsList']),
                    //Assignments
                    Route::get('/assignments')
                        ->name('backend/access/assignments')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AssignmentsController::class, 'assignments']),
                    //User assignments
                    Route::get('/user-assignments/{user_id}')
                        ->name('backend/access/user-assignments')
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->middleware(Authentication::class)
                        ->action([AssignmentsController::class, 'userAssignments']),

                    //assign role
                    Route::post('/assign-role')
                        ->name('backend/access/assign-role')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AssignAccessController::class, 'assignRole']),
                    //revoke role
                    Route::post('/revoke-role')
                        ->name('backend/access/revoke-role')
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->middleware(Authentication::class)
                        ->action([AssignAccessController::class, 'revokeRole']),
                    //assign permission
                    Route::post('/assign-permission')
                        ->name('backend/access/assign-permission')
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->middleware(Authentication::class)
                        ->action([AssignAccessController::class, 'assignPermission']),
                    //revoke permission
                    Route::post('/revoke-permission')
                        ->name('backend/access/revoke-permission')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AssignAccessController::class, 'revokePermission']),
                    //revoke All assignments user
                    Route::post('/revoke-all')
                        ->name('backend/access/revoke-all')
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->middleware(Authentication::class)
                        ->action([AssignAccessController::class, 'revokeAll']),
                    //clear assignments
                    Route::post('/clear-assignments')
                        ->name('backend/access/clear-assignments')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AssignAccessController::class, 'clearAssignments']),

                    //Add role
                    Route::post('/add-role')
                        ->name('backend/access/add-role')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AccessManagementController::class, 'addRole']),
                    //Remove role
                    Route::post('/remove-role')
                        ->name('backend/access/remove-role')
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->middleware(Authentication::class)
                        ->action([AccessManagementController::class, 'removeRole']),
                    //Add permission
                    Route::post('/add-permission')
                        ->name('backend/access/add-permission')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AccessManagementController::class, 'addPermission']),
                    //Remove permission
                    Route::post('/remove-permission')
                        ->name('backend/access/remove-permission')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AccessManagementController::class, 'removePermission']),
                    //Add child role
                    Route::post('/add-child-role')
                        ->name('backend/access/add-child-role')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AccessManagementController::class, 'addChildRole']),
                    //Remove child role
                    Route::post('/remove-child-role')
                        ->name('backend/access/remove-child-role')
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->middleware(Authentication::class)
                        ->action([AccessManagementController::class, 'removeChildRole']),
                    //Add child permission
                    Route::post('/add-child-permission')
                        ->name('backend/access/add-child-permission')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AccessManagementController::class, 'addChildPermission']),
                    //Remove child permission
                    Route::post('/remove-child-permission')
                        ->name('backend/access/remove-child-permission')
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->middleware(Authentication::class)
                        ->action([AccessManagementController::class, 'removeChildPermission']),
                    //Remove children
                    Route::post('/remove-children')
                        ->name('backend/access/remove-children')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AccessManagementController::class, 'removeChildren']),
                    //Clear access rights
                    Route::post('/clear-access/')
                        ->name('backend/access/clear-access')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([AccessManagementController::class, 'clearAccessRights']),
                ),
        ),

];
