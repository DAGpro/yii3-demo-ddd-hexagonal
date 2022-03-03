<?php

declare(strict_types=1);


use App\Blog\Domain\Comment;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Port\TagRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use App\IdentityAccess\User\Domain\Port\UserRepositoryInterface;
use App\IdentityAccess\User\Domain\User;
use App\Infrastructure\Authentication\Identity;
use Cycle\ORM\ORMInterface;
use Psr\Container\ContainerInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;

return [
    TagRepositoryInterface::class => static function (ContainerInterface $container) {
        return $container->get(ORMInterface::class)->getRepository(Tag::class);
    },
    PostRepositoryInterface::class => static function (ContainerInterface $container) {
        return $container->get(ORMInterface::class)->getRepository(Post::class);
    },
    CommentRepositoryInterface::class => static function (ContainerInterface $container) {
        return $container->get(ORMInterface::class)->getRepository(Comment::class);
    },

    UserRepositoryInterface::class => static function (ContainerInterface $container) {
        return $container->get(ORMInterface::class)->getRepository(User::class);
    },

    IdentityRepositoryInterface::class => static function (ContainerInterface $container): IdentityRepositoryInterface {
        return $container->get(ORMInterface::class)->getRepository(Identity::class);
    },
];
