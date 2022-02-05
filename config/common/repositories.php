<?php

declare(strict_types=1);


use App\Core\Component\Blog\Domain\Comment;
use App\Core\Component\Blog\Domain\Port\CommentRepositoryInterface;
use App\Core\Component\Blog\Domain\Port\PostRepositoryInterface;
use App\Core\Component\Blog\Domain\Port\TagRepositoryInterface;
use App\Core\Component\Blog\Domain\Post;
use App\Core\Component\Blog\Domain\Tag;
use App\Core\Component\IdentityAccess\User\Domain\Port\UserRepositoryInterface;
use App\Core\Component\IdentityAccess\User\Domain\User;
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
