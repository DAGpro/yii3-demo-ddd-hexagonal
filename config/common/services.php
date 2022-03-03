<?php

use App\Blog\Application\Service\AppService\CommandService\AuthorPostService;
use App\Blog\Application\Service\AppService\CommandService\CommentService;
use App\Blog\Application\Service\AppService\CommandService\ModerateCommentService;
use App\Blog\Application\Service\AppService\CommandService\ModeratePostService;
use App\Blog\Application\Service\AppService\CommandService\TagService;
use App\Blog\Application\Service\AppService\QueryService\ArchivePostQueryService;
use App\Blog\Application\Service\AppService\QueryService\AuthorPostQueryService;
use App\Blog\Application\Service\AppService\QueryService\CommentQueryService;
use App\Blog\Application\Service\AppService\QueryService\ModerateCommentQueryService;
use App\Blog\Application\Service\AppService\QueryService\ModeratePostQueryService;
use App\Blog\Application\Service\AppService\QueryService\ReadPostQueryService;
use App\Blog\Application\Service\AppService\QueryService\TagQueryService;
use App\Blog\Application\Service\CommandService\AuthorPostServiceInterface;
use App\Blog\Application\Service\CommandService\CommentServiceInterface;
use App\Blog\Application\Service\CommandService\ModerateCommentServiceInterface;
use App\Blog\Application\Service\CommandService\ModeratePostServiceInterface;
use App\Blog\Application\Service\CommandService\TagServiceInterface;
use App\Blog\Application\Service\QueryService\ArchivePostQueryServiceInterface;
use App\Blog\Application\Service\QueryService\AuthorPostQueryServiceInterface;
use App\Blog\Application\Service\QueryService\CommentQueryServiceInterface;
use App\Blog\Application\Service\QueryService\ModerateCommentQueryServiceInterface;
use App\Blog\Application\Service\QueryService\ModeratePostQueryServiceInterface;
use App\Blog\Application\Service\QueryService\ReadPostQueryServiceInterface;
use App\Blog\Application\Service\QueryService\TagQueryServiceInterface;
use App\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
use App\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Application\Service\AppService\AccessManagementService;
use App\IdentityAccess\Access\Application\Service\AppService\AccessRightsService;
use App\IdentityAccess\Access\Application\Service\AppService\AssignAccessService;
use App\IdentityAccess\Access\Application\Service\AppService\AssignmentsService;
use App\IdentityAccess\Access\Application\Service\AssignAccessServiceInterface;
use App\IdentityAccess\Access\Application\Service\AssignmentsServiceInterface;
use App\IdentityAccess\User\Application\Service\AppService\UserQueryService;
use App\IdentityAccess\User\Application\Service\AppService\UserService;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Application\Service\UserServiceInterface;

return [
    UserQueryServiceInterface::class => UserQueryService::class,
    UserServiceInterface::class => UserService::class,

    AccessRightsServiceInterface::class => AccessRightsService::class,
    AccessManagementServiceInterface::class => AccessManagementService::class,
    AssignAccessServiceInterface::class => AssignAccessService::class,
    AssignmentsServiceInterface::class => AssignmentsService::class,

    AuthorPostServiceInterface::class => AuthorPostService::class,
    CommentServiceInterface::class => CommentService::class,
    ModerateCommentServiceInterface::class => ModerateCommentService::class,
    ModeratePostServiceInterface::class => ModeratePostService::class,
    TagServiceInterface::class => TagService::class,

    ArchivePostQueryServiceInterface::class => ArchivePostQueryService::class,
    AuthorPostQueryServiceInterface::class => AuthorPostQueryService::class,
    CommentQueryServiceInterface::class => CommentQueryService::class,
    ModerateCommentQueryServiceInterface::class => ModerateCommentQueryService::class,
    ModeratePostQueryServiceInterface::class => ModeratePostQueryService::class,
    ReadPostQueryServiceInterface::class => ReadPostQueryService::class,
    TagQueryServiceInterface::class => TagQueryService::class,
];
