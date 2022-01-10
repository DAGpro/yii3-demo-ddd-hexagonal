<?php

use App\Core\Component\IdentityAccess\User\Application\Service\AppService\UserQueryService;
use App\Core\Component\IdentityAccess\User\Application\Service\AppService\UserService;
use App\Core\Component\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\Core\Component\IdentityAccess\User\Application\Service\UserServiceInterface;

return [
    UserQueryServiceInterface::class => UserQueryService::class,
    UserServiceInterface::class => UserService::class,
];
