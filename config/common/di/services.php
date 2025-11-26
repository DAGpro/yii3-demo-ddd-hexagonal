<?php

use App\Blog\Slice\Comment\Application\Service\CommandService\CommentService;
use App\Blog\Slice\Comment\Application\Service\CommandService\CommentServiceInterface;
use App\Blog\Slice\Comment\Application\Service\CommandService\ModerateCommentService;
use App\Blog\Slice\Comment\Application\Service\CommandService\ModerateCommentServiceInterface;
use App\Blog\Slice\Comment\Application\Service\QueryService\CommentQueryService;
use App\Blog\Slice\Comment\Application\Service\QueryService\CommentQueryServiceInterface;
use App\Blog\Slice\Comment\Application\Service\QueryService\ModerateCommentQueryService;
use App\Blog\Slice\Comment\Application\Service\QueryService\ModerateCommentQueryServiceInterface;
use App\Blog\Slice\Post\Service\CommandService\AuthorPostService;
use App\Blog\Slice\Post\Service\CommandService\AuthorPostServiceInterface;
use App\Blog\Slice\Post\Service\CommandService\ModeratePostService;
use App\Blog\Slice\Post\Service\CommandService\ModeratePostServiceInterface;
use App\Blog\Slice\Post\Service\QueryService\ArchivePostQueryService;
use App\Blog\Slice\Post\Service\QueryService\ArchivePostQueryServiceInterface;
use App\Blog\Slice\Post\Service\QueryService\AuthorPostQueryService;
use App\Blog\Slice\Post\Service\QueryService\AuthorPostQueryServiceInterface;
use App\Blog\Slice\Post\Service\QueryService\ModeratePostQueryService;
use App\Blog\Slice\Post\Service\QueryService\ModeratePostQueryServiceInterface;
use App\Blog\Slice\Post\Service\QueryService\ReadPostQueryService;
use App\Blog\Slice\Post\Service\QueryService\ReadPostQueryServiceInterface;
use App\Blog\Slice\Tag\Service\CommandService\TagService;
use App\Blog\Slice\Tag\Service\CommandService\TagServiceInterface;
use App\Blog\Slice\Tag\Service\QueryService\TagQueryService;
use App\Blog\Slice\Tag\Service\QueryService\TagQueryServiceInterface;
use App\IdentityAccess\Access\Slice\Service\AccessManagementServiceInterface;
use App\IdentityAccess\Access\Slice\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Slice\Service\AppService\AccessManagementService;
use App\IdentityAccess\Access\Slice\Service\AppService\AccessRightsService;
use App\IdentityAccess\Access\Slice\Service\AppService\AssignAccessService;
use App\IdentityAccess\Access\Slice\Service\AppService\AssignmentsService;
use App\IdentityAccess\Access\Slice\Service\AssignAccessServiceInterface;
use App\IdentityAccess\Access\Slice\Service\AssignmentsServiceInterface;
use App\IdentityAccess\User\Slice\User\UserQueryService;
use App\IdentityAccess\User\Slice\User\UserQueryServiceInterface;
use App\IdentityAccess\User\Slice\User\UserService;
use App\IdentityAccess\User\Slice\User\UserServiceInterface;

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
