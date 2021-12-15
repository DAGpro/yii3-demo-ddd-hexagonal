<?php

declare(strict_types=1);

use App\Core\Component\Blog\Application\CommentService;
use App\Core\Component\Blog\Domain\Comment;
use Cycle\ORM\ORMInterface;
use Psr\Container\ContainerInterface;

return [
    CommentService::class => static function (ContainerInterface $container) {
        /**
         * @var \App\Core\Component\Blog\Infrastructure\Persistence\Comment\CommentRepository $repository
         */
        $repository = $container->get(ORMInterface::class)->getRepository(Comment::class);

        return new CommentService($repository);
    },
];
