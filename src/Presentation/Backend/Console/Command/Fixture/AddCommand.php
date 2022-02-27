<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Console\Command\Fixture;

use App\Core\Component\Blog\Domain\Comment;
use App\Core\Component\Blog\Domain\Port\PostRepositoryInterface;
use App\Core\Component\Blog\Domain\Post;
use App\Core\Component\Blog\Domain\Tag;
use App\Core\Component\Blog\Domain\User\Author;
use App\Core\Component\Blog\Domain\User\Commentator;
use App\Core\Component\Blog\Infrastructure\Persistence\Tag\TagRepository;
use App\Core\Component\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\Core\Component\IdentityAccess\Access\Application\Service\AssignAccessServiceInterface;
use App\Core\Component\IdentityAccess\Access\Application\Service\RoleDTO;
use App\Core\Component\IdentityAccess\User\Domain\User;
use App\Core\Component\IdentityAccess\User\Infrastructure\Persistence\UserRepository;
use App\Infrastructure\Authentication\SignupUserService;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyProxy;

final class AddCommand extends Command
{
    protected static $defaultName = 'fixture/add';

    private CycleDependencyProxy $promise;
    private Generator $faker;
    /** @var User[] */
    private iterable $users = [];
    /** @var Tag[] */
    private array $tags = [];
    /** @var Author[] */
    private iterable $authors;
    private AssignAccessServiceInterface $assignAccessService;
    private AccessRightsServiceInterface $accessRightsService;

    private const DEFAULT_COUNT = 10;
    private SignupUserService $signupUserService;

    public function __construct(
        CycleDependencyProxy $promise,
        AccessRightsServiceInterface $accessRightsService,
        AssignAccessServiceInterface $assignAccessService,
        SignupUserService $signupUserService,
    ) {
        $this->promise = $promise;
        $this->assignAccessService = $assignAccessService;
        $this->accessRightsService = $accessRightsService;
        $this->signupUserService = $signupUserService;
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Add fixtures')
            ->setHelp('This command adds random content')
            ->addArgument('count', InputArgument::OPTIONAL, 'Count', self::DEFAULT_COUNT);
    }

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

        try {
            if (!$this->accessRightsService->existRole('author')) {
                $io->error('Add access rights before creating fixtures!');
                return ExitCode::OK;
            }

            $this->addUsers($count);
            $this->addAccessRightsToUsers((int)round($count / 3));
            $this->addTags($count);
            $this->addPosts($count);

        } catch (\Throwable $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }
        $io->success('Done');
        return ExitCode::OK;
    }

    private function addUsers(int $count): void
    {
        for ($i = 0; $i < $count; ++$i) {
            $login = $this->faker->firstName . rand(0, 9999);

            $this->signupUserService->signup($login, $login);
        }
        /**
         * @var $userRepository UserRepository
         */
        $userRepository = $this->promise->getORM()->getRepository(User::class);
        $this->users = $userRepository->select()->orderBy('id', 'DESC')->limit($count)->fetchAll();
    }

    public function addAccessRightsToUsers(int $countUsers): void
    {
        $usersKeys = array_rand($this->users, $countUsers);

        /** @var User $user */
        foreach ($usersKeys as $key) {
            $user = $this->users[$key];
            $this->assignAccessService->assignRole(new RoleDTO('author'), (string)$user->getId());
            $this->authors[] = new Author($user->getId(), $user->getLogin());
        }
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

    private function addPosts(int $count): void
    {
        if (count($this->authors) === 0) {
            throw new \Exception('No users');
        }
        $posts = [];
        for ($i = 0; $i < $count; ++$i) {

            /** @var Author $postAuthor */
            $postAuthor = $this->authors[array_rand($this->authors)];

            $posts[] = $post = new Post(
                $this->faker->text(64),
                $this->faker->realText(random_int(1000, 4000)),
                clone $postAuthor
            );

            $public = rand(0, 2) > 0;
            $public ? $post->publish() : $post->toDraft();
            if ($public) {
                $post->setPublishedAt(new \DateTimeImmutable(date('r', rand(time(), strtotime('-2 years')))));
            }
            // link tags
            $postTags = (array)array_rand($this->tags, rand(1, count($this->tags)));
            foreach ($postTags as $tagId) {
                $tag = $this->tags[$tagId];
                $post->addTag($tag);
                // todo: uncomment when issue is resolved https://github.com/cycle/orm/issues/70
                // $tag->addPost($post);
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
        $commentCount = (int)round($count/2.5);
        foreach ($postsSaveds as $post) {
            for ($j = 0; $j <= $commentCount; ++$j) {

                $commentUser = $this->users[array_rand($this->users)];
                $commentator = new Commentator($commentUser->getId(), $commentUser->getLogin());

                $comment = $post->createComment($this->faker->realText(random_int(100, 500)), clone $commentator);
                $commentPublic = rand(0, 3) > 0;
                $comment->setPublic($commentPublic);
                if ($commentPublic) {
                    $comment->setPublishedAt(new \DateTimeImmutable(date('r', rand(time(), strtotime('-1 years')))));
                }

                $comments[] = $comment;
            }
        }

        $this->promise->getORM()->getRepository(Comment::class)->save($comments);
    }
}
