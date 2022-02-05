<?php

use App\Core\Component\Blog\Application\Service\AppService\CommandService\AuthorPostService;
use App\Core\Component\Blog\Application\Service\AppService\CommandService\CommentService;
use App\Core\Component\Blog\Application\Service\AppService\CommandService\ModeratePostService;
use App\Core\Component\Blog\Application\Service\AppService\CommandService\TagService;
use App\Core\Component\Blog\Application\Service\AppService\QueryService\ArchivePostQueryService;
use App\Core\Component\Blog\Application\Service\AppService\QueryService\AuthorPostQueryService;
use App\Core\Component\Blog\Application\Service\AppService\QueryService\CommentQueryService;
use App\Core\Component\Blog\Application\Service\AppService\QueryService\ModeratePostQueryService;
use App\Core\Component\Blog\Application\Service\AppService\QueryService\ReadPostQueryService;
use App\Core\Component\Blog\Application\Service\AppService\QueryService\TagQueryService;
use App\Core\Component\Blog\Application\Service\CommandService\AuthorPostServiceInterface;
use App\Core\Component\Blog\Application\Service\CommandService\CommentServiceInterface;
use App\Core\Component\Blog\Application\Service\CommandService\ModeratePostServiceInterface;
use App\Core\Component\Blog\Application\Service\CommandService\TagServiceInterface;
use App\Core\Component\Blog\Application\Service\QueryService\ArchivePostQueryServiceInterface;
use App\Core\Component\Blog\Application\Service\QueryService\AuthorPostQueryServiceInterface;
use App\Core\Component\Blog\Application\Service\QueryService\CommentQueryServiceInterface;
use App\Core\Component\Blog\Application\Service\QueryService\ModeratePostQueryServiceInterface;
use App\Core\Component\Blog\Application\Service\QueryService\ReadPostQueryServiceInterface;
use App\Core\Component\Blog\Application\Service\QueryService\TagQueryServiceInterface;
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
    AssignmentsServiceInterface::class => AssignmentsService::class,

    AuthorPostServiceInterface::class => AuthorPostService::class,
    CommentServiceInterface::class => CommentService::class,
    ModeratePostServiceInterface::class => ModeratePostService::class,
    TagServiceInterface::class => TagService::class,

    ArchivePostQueryServiceInterface::class => ArchivePostQueryService::class,
    AuthorPostQueryServiceInterface::class => AuthorPostQueryService::class,
    CommentQueryServiceInterface::class => CommentQueryService::class,
    ModeratePostQueryServiceInterface::class => ModeratePostQueryService::class,
    ReadPostQueryServiceInterface::class => ReadPostQueryService::class,
    TagQueryServiceInterface::class => TagQueryService::class,
];
