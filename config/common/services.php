<?php

use App\Core\Component\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
use App\Core\Component\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\Core\Component\IdentityAccess\Access\Application\Service\AppService\AccessManagementService;
use App\Core\Component\IdentityAccess\Access\Application\Service\AppService\AccessRightsService;
use App\Core\Component\IdentityAccess\Access\Application\Service\AppService\AssignAccessService;
use App\Core\Component\IdentityAccess\Access\Application\Service\AppService\AssignmentsService;
use App\Core\Component\IdentityAccess\Access\Application\Service\AssignAccessServiceInterface;
use App\Core\Component\IdentityAccess\Access\Application\Service\AssignmentsServiceInterface;
use App\Core\Component\IdentityAccess\User\Application\Service\AppService\UserQueryService;
use App\Core\Component\IdentityAccess\User\Application\Service\AppService\UserService;
use App\Core\Component\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\Core\Component\IdentityAccess\User\Application\Service\UserServiceInterface;

return [
    UserQueryServiceInterface::class => UserQueryService::class,
    UserServiceInterface::class => UserService::class,

    AccessRightsServiceInterface::class => AccessRightsService::class,
    AccessManagementServiceInterface::class => AccessManagementService::class,
    AssignAccessServiceInterface::class => AssignAccessService::class,
    AssignmentsServiceInterface::class => AssignmentsService::class
];
