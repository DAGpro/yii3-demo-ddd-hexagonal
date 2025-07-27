<?php

declare(strict_types=1);

namespace App\Site\Presentation\Backend\Console\Fixture;

use App\Blog\Domain\Comment;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use App\Blog\Domain\User\Author;
use App\Blog\Domain\User\Commentator;
use App\Blog\Infrastructure\Persistence\Tag\TagRepository;
use App\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Application\Service\AssignAccessServiceInterface;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\IdentityAccess\User\Application\Service\UserServiceInterface;
use App\IdentityAccess\User\Domain\Port\UserRepositoryInterface;
use App\IdentityAccess\User\Domain\User;
use Cycle\Database\DatabaseManager;
use Cycle\ORM\Select\Repository;
use DateMalformedStringException;
use DateTimeImmutable;
use Faker\Factory;
use Faker\Generator;
use Override;
use Random\RandomException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;

#[AsCommand(
    'fixture:add',
    'Add fixtures',
    help: 'This command adds random content',
)]
final class AddCommand extends Command
{
    private const int DEFAULT_COUNT = 10;

    /** @psalm-suppress PropertyNotSetInConstructor */
    private Generator $faker;
    /** @var User[] */
    private iterable $users = [];
    /** @var Tag[] */
    private array $tags = [];
    /** @var Author[] */
    private iterable $authors = [];

    public function __construct(
        private readonly CycleDependencyProxy $promise,
        private readonly AccessRightsServiceInterface $accessRightsService,
        private readonly AssignAccessServiceInterface $assignAccessService,
        private readonly UserServiceInterface $userService,
        private readonly DatabaseManager $databaseManager,
    ) {
        parent::__construct();
    }

    #[Override]
    public function configure(): void
    {
        $this->addArgument('count', InputArgument::OPTIONAL, 'Count', self::DEFAULT_COUNT);
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = (int)$input->getArgument('count');
        // get faker
        if (!class_exists(Factory::class)) {
            $io->error('Faker should be installed. Run `composer install --dev`');
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $this->faker = Factory::create();

        $db = $this->databaseManager->database();

        try {
            if (!$this->accessRightsService->existRole('author')) {
                $io->error('Add access rights before creating fixtures!');
                return ExitCode::OK;
            }

            $db->begin();
            $this->addUsers($count);
            $this->addAccessRightsToUsers((int)round($count / 3));
            $this->addTags($count);
            $this->addPosts($count);
            $db->commit();
        } catch (Throwable $t) {
            $db->rollback();
            $io->error($t->getMessage());
            $code = $t->getCode();
            return is_int($code) && $code > 0 ? $code : ExitCode::UNSPECIFIED_ERROR;
        }
        $io->success('Done');
        return ExitCode::OK;
    }

    /**
     * @throws AssignedItemException
     */
    private function addAccessRightsToUsers(int $countUsers): void
    {
        if (empty($this->users)) {
            return;
        }

        // Приводим массив пользователей к индексированному массиву
        $usersArray = array_values($this->users);
        $totalUsers = count($usersArray);

        // Выбираем случайных пользователей
        $selectedIndices = [];
        $countToSelect = min($countUsers, $totalUsers);

        if ($countToSelect === 0) {
            return;
        }

        // Выбираем случайные индексы
        $selectedIndices = array_rand(
            array_flip(range(0, $totalUsers - 1)),
            $countToSelect,
        );

        if (!is_array($selectedIndices)) {
            $selectedIndices = [$selectedIndices];
        }

        // Обрабатываем выбранных пользователей
        foreach ($selectedIndices as $index) {
            $user = $usersArray[$index];
            $userId = $user->getId();
            $userLogin = $user->getLogin();

            if ($userId === null) {
                continue;
            }

            $this->assignAccessService->assignRole(new RoleDTO('author'), $userId);
            $this->authors[] = new Author($userId, $userLogin);
        }
    }

    /**
     * @throws RandomException
     */
    private function addUsers(int $count): void
    {
        for ($i = 0; $i < $count; ++$i) {
            $login = $this->faker->firstName . random_int(0, 9999);

            $this->userService->createUser($login, $login);
        }

        /** @var UserRepositoryInterface&Repository $userRepository */
        $userRepository = $this->promise->getORM()->getRepository(User::class);
        $select = $userRepository
            ->select()
            ->orderBy('id', 'DESC')
            ->limit($count);

        /** @var array<array-key, User> $users */
        $users = $select->fetchAll();
        $this->users = $users;
    }

    private function addTags(int $count): void
    {
        /** @var TagRepository $tagRepository */
        $tagRepository = $this->promise->getORM()->getRepository(Tag::class);
        $this->tags = [];
        $tagWords = [];
        for ($i = 0, $fails = 0; $i < $count; ++$i) {
            $word = $this->faker->word();
            if (in_array($word, $tagWords, true)) {
                --$i;
                ++$fails;
                if ($fails >= $count) {
                    break;
                }
                continue;
            }
            $tagWords[] = $word;
            $tag = $tagRepository->getOrCreate($word);
            $this->tags[] = $tag;
        }
    }

    /**
     * @throws RandomException
     * @throws DateMalformedStringException
     */
    private function addPosts(int $count): void
    {
        if (count($this->authors) === 0) {
            throw new RuntimeException('No users');
        }
        $posts = [];
        for ($i = 0; $i < $count; ++$i) {
            /** @var Author $postAuthor */
            $postAuthor = $this->authors[array_rand($this->authors)];

            $posts[] = $post = new Post(
                $this->faker->text(64),
                $this->faker->realText(random_int(1000, 4000)),
                clone $postAuthor,
            );

            $public = random_int(0, 2) > 0;
            $public ? $post->publish() : $post->toDraft();
            if ($public) {
                $twoYearsAgo = strtotime('-2 years');
                $post->setPublishedAt(new DateTimeImmutable(date('r',
                    random_int(
                        (int)min(time(), $twoYearsAgo),
                        (int)max(time(), $twoYearsAgo),
                    ),
                ),
                ),
                );
            }
            // link tags
            $tagCount = count($this->tags);
            if ($tagCount === 0) {
                continue;
            }
            $tagsToSelect = random_int(1, $tagCount);
            $postTags = [];
            if (!empty($this->tags)) {
                $selectedKeys = array_rand($this->tags, $tagsToSelect);

                $postTags = is_array($selectedKeys) ? $selectedKeys : [$selectedKeys];
            }

            // Добавляем теги к посту
            foreach ($postTags as $tagId) {
                if (isset($this->tags[$tagId])) {
                    $tag = $this->tags[$tagId];
                    $post->addTag($tag);
                    // todo: uncomment when issue is resolved https://github.com/cycle/orm/issues/70
                    // $tag->addPost($post);
                }
            }
        }

        /** @var PostRepositoryInterface $postRepository */
        $postRepository = $this->promise->getORM()->getRepository(Post::class);
        $postRepository->save($posts);
        $postsSaveds = $postRepository
            ->select()
            ->limit($count)
            ->orderBy('id', 'DESC')
            ->fetchAll();

        // add comments

        $comments = [];
        $commentCount = (int)round((float)$count / 2.5);
        /** @var Post $post */
        foreach ($postsSaveds as $post) {
            for ($j = 0; $j <= $commentCount; ++$j) {
                $commentUser = $this->users[array_rand($this->users)];
                $userId = $commentUser->getId();
                $login = $commentUser->getLogin();
                if ($userId === null) {
                    continue;
                }
                $commentator = new Commentator($userId, $login);

                /** @var Comment $comment */
                $comment = $post->createComment($this->faker->realText(random_int(100, 500)), clone $commentator);
                $commentPublic = random_int(0, 3) > 0;
                $commentPublic ? $comment->publish() : $comment->toDraft();
                if ($commentPublic) {
                    $oneYearAgo = strtotime('-1 year');
                    $comment->setPublishedAt(new DateTimeImmutable(date('r',
                        random_int(
                            (int)min(time(), $oneYearAgo),
                            (int)max(time(), $oneYearAgo),
                        ),
                    ),
                    ),
                    );
                }

                $comments[] = $comment;
            }
        }

        /** @var CommentRepositoryInterface $commentRepository */
        $commentRepository = $this->promise->getORM()->getRepository(Comment::class);
        $commentRepository->save($comments);
    }
}
