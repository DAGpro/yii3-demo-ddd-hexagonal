<?php

declare(strict_types=1);

use App\Infrastructure\Presentation\Web\Middleware\LocaleMiddleware;
use App\Infrastructure\Presentation\Web\ViewInjection\CommonViewInjection;
use App\Infrastructure\Presentation\Web\ViewInjection\LayoutViewInjection;
use App\Infrastructure\Presentation\Web\ViewInjection\LinkTagsViewInjection;
use App\Infrastructure\Presentation\Web\ViewInjection\MetaTagsViewInjection;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Cookies\CookieMiddleware;
use Yiisoft\Definitions\Reference;
use Yiisoft\ErrorHandler\Middleware\ErrorCatcher;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\Middleware\Router;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\SessionMiddleware;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\User\Login\Cookie\CookieLoginMiddleware;
use Yiisoft\Yii\Console\Application;
use Yiisoft\Yii\Console\Command\Serve;
use Yiisoft\Yii\Cycle\Schema\Conveyor\AttributedSchemaConveyor;
use Yiisoft\Yii\View\CsrfViewInjection;

return [
    'locales' => ['en' => 'en-US', 'ru' => 'ru-RU'],
    'mailer' => [
        'adminEmail' => 'admin@example.com',
        'senderEmail' => 'sender@example.com',
    ],
    'middlewares' => [
        ErrorCatcher::class,
        SessionMiddleware::class,
        CookieMiddleware::class,
        CookieLoginMiddleware::class,
        LocaleMiddleware::class,
        Router::class,
    ],

    'yiisoft/aliases' => [
        'aliases' => [
            '@root' => dirname(__DIR__),
            '@assets' => '@root/public/assets',
            '@assetsUrl' => '@baseUrl/assets',
            '@baseUrl' => '/',
            '@messages' => '@resources/messages',
            '@npm' => '@root/node_modules',
            '@public' => '@root/public',
            '@resources' => '@root/resources',
            '@runtime' => '@root/runtime',
            '@src' => '@root/src',
            '@vendor' => '@root/vendor',
            '@layout' => '@src/Site/Presentation/Frontend/View/layout',
            '@view' => '@src/Site/Presentation/Frontend/View',
            '@backendLayout' => '@src/Site/Presentation/Backend/View/layout',
            '@backendView' => '@src/Site/Presentation/Backend/View',
            '@blogView' => '@src/Blog/Presentation/Frontend/View',
            '@blogBackendView' => '@src/Blog/Presentation/Backend/View',
            '@identityView' => '@src/IdentityAccess/Presentation/Frontend/View',
            '@identityBackendView' => '@src/IdentityAccess/Presentation/Backend/View',
        ],
    ],

    'yiisoft/forms' => [
        'field' => [
            'ariaDescribedBy' => [true],
            'containerClass' => ['form-floating mb-3'],
            'errorClass' => ['fw-bold fst-italic invalid-feedback'],
            'hintClass' => ['form-text'],
            'inputClass' => ['form-control'],
            'invalidClass' => ['is-invalid'],
            'labelClass' => ['floatingInput'],
            'template' => ['{input}{label}{hint}{error}'],
            'validClass' => ['is-valid'],
            'defaultValues' => [
                [
                    'submit' => [
                        'definitions' => [
                            'class()' => ['btn btn-primary btn-lg mt-3'],
                        ],
                        'containerClass' => 'd-grid gap-2 form-floating',
                    ],
                ],
            ],
        ],
        'form' => [
            'attributes' => [['enctype' => 'multipart/form-data']],
        ],
    ],

    'yiisoft/rbac-rules-container' => [
        'rules' => require __DIR__ . '/rbac-rules.php',
    ],

    'yiisoft/router-fastroute' => [
        'enableCache' => false,
    ],

    'yiisoft/translator' => [
        'locale' => 'en',
        'fallbackLocale' => 'en',
        'defaultCategory' => 'app',
        'categorySources' => [
            // You can add categories from your application and additional modules using `Reference::to` below
            // Reference::to(ApplicationCategorySource::class),
            Reference::to('translation.app'),
        ],
    ],

    'yiisoft/view' => [
        'basePath' => '@view',
        'parameters' => [
            'assetManager' => Reference::to(AssetManager::class),
            'urlGenerator' => Reference::to(UrlGeneratorInterface::class),
            'currentRoute' => Reference::to(CurrentRoute::class),
            'translator' => Reference::to(TranslatorInterface::class),
        ],
    ],

    'yiisoft/cookies' => [
        'secretKey' => '53136271c432a1af377c3806c3112ddf',
    ],

    'yiisoft/yii-view' => [
        'viewPath' => '@view',
        'layout' => '@view/layout/main',
        'injections' => [
            Reference::to(CommonViewInjection::class),
            Reference::to(CsrfViewInjection::class),
            Reference::to(LayoutViewInjection::class),
            Reference::to(LinkTagsViewInjection::class),
            Reference::to(MetaTagsViewInjection::class),
        ],
    ],

    'yiisoft/yii-console' => [
        'name' => Application::NAME,
        'version' => Application::VERSION,
        'autoExit' => false,
        'commands' => [
            'serve' => Serve::class,

            'user/delete' => \App\IdentityAccess\Presentation\Backend\Console\User\DeleteUserCommand::class,
            'user/create' => \App\IdentityAccess\Presentation\Backend\Console\User\CreateUserCommand::class,

            'assign/assignRole' => \App\IdentityAccess\Presentation\Backend\Console\Access\Assign\AssignRoleCommand::class,
            'assign/revokeRole' => \App\IdentityAccess\Presentation\Backend\Console\Access\Assign\RevokeRoleCommand::class,
            'assign/assignPermission' => \App\IdentityAccess\Presentation\Backend\Console\Access\Assign\AssignPermissionCommand::class,
            'assign/assignAllPermissions' => \App\IdentityAccess\Presentation\Backend\Console\Access\Assign\AssignAllPermissionsCommand::class,
            'assign/revokePermission' => \App\IdentityAccess\Presentation\Backend\Console\Access\Assign\RevokePermissionCommand::class,

            'access/addRole' => \App\IdentityAccess\Presentation\Backend\Console\Access\Management\AddRoleCommand::class,
            'access/removeRole' => \App\IdentityAccess\Presentation\Backend\Console\Access\Management\RemoveRoleCommand::class,
            'access/addPermission' => \App\IdentityAccess\Presentation\Backend\Console\Access\Management\AddPermissionCommand::class,
            'access/removePermission' => \App\IdentityAccess\Presentation\Backend\Console\Access\Management\RemovePermissionCommand::class,
            'access/addChildPermission' => \App\IdentityAccess\Presentation\Backend\Console\Access\Management\AddChildPermissionCommand::class,
            'access/addAllChildPermission' => \App\IdentityAccess\Presentation\Backend\Console\Access\Management\AddAllChildPermissionsCommand::class,
            'access/addChildRole' => \App\IdentityAccess\Presentation\Backend\Console\Access\Management\AddChildRoleCommand::class,
            'access/removeChildPermission' => \App\IdentityAccess\Presentation\Backend\Console\Access\Management\RemoveChildPermissionCommand::class,
            'access/removeChildRole' => \App\IdentityAccess\Presentation\Backend\Console\Access\Management\RemoveChildRoleCommand::class,
            'access/removeAll' => \App\IdentityAccess\Presentation\Backend\Console\Access\Management\RemoveAllAccessRightsCommand::class,

            'access/list' => \App\IdentityAccess\Presentation\Backend\Console\Access\AccessListCommand::class,
            'access/assignmentsList' => \App\IdentityAccess\Presentation\Backend\Console\Access\AssignmentsListCommand::class,
            'access/viewRole' => \App\IdentityAccess\Presentation\Backend\Console\Access\ViewRoleCommand::class,
            'access/userAssignments' => \App\IdentityAccess\Presentation\Backend\Console\Access\UserAssignmentsCommand::class,

            'fixture/add' => \App\Site\Presentation\Backend\Console\Fixture\AddCommand::class,
            'fixture/addAccess' => \App\Site\Presentation\Backend\Console\Fixture\CreateAccessRights::class,
            'router/list' => \App\Site\Presentation\Backend\Console\Router\ListCommand::class,
            'translator/translate' => \App\Site\Presentation\Backend\Console\Translation\TranslateCommand::class,
        ],
    ],

    'yiisoft/yii-cycle' => [
        // DBAL config
        'dbal' => [
            // SQL query logger. Definition of Psr\Log\LoggerInterface
            // For example, \Yiisoft\Yii\Cycle\Logger\StdoutQueryLogger::class
            'query-logger' => null,
            // Default database
            'default' => 'default',
            'aliases' => [],
            'databases' => [
                'default' => ['connection' => 'sqlite'],
            ],
            'connections' => [
                'sqlite' => new \Cycle\Database\Config\SQLiteDriverConfig(
                    connection: new \Cycle\Database\Config\SQLite\FileConnectionConfig(
                        database: 'runtime/database.db'
                    )
                ),
            ],
        ],

        // Cycle migration config
        'migrations' => [
            'directory' => '@root/migrations',
            'namespace' => 'App\\Migration',
            'table' => 'migration',
            'safe' => false,
        ],

        /**
         * SchemaProvider list for {@see \Yiisoft\Yii\Cycle\Schema\Provider\Support\SchemaProviderPipeline}
         * Array of classname and {@see SchemaProviderInterface} object.
         * You can configure providers if you pass classname as key and parameters as array:
         * [
         *     SimpleCacheSchemaProvider::class => [
         *         'key' => 'my-custom-cache-key'
         *     ],
         *     FromFilesSchemaProvider::class => [
         *         'files' => ['@runtime/cycle-schema.php']
         *     ],
         *     FromConveyorSchemaProvider::class => [
         *         'generators' => [
         *              Generator\SyncTables::class, // sync table changes to database
         *          ]
         *     ],
         * ]
         */
        'schema-providers' => [
            // Uncomment next line to enable a Schema caching in the common cache
            // \Yiisoft\Yii\Cycle\Schema\Provider\SimpleCacheSchemaProvider::class => ['key' => 'cycle-orm-cache-key'],

            // Store generated Schema in the file
            \Yiisoft\Yii\Cycle\Schema\Provider\PhpFileSchemaProvider::class => [
                'mode' => \Yiisoft\Yii\Cycle\Schema\Provider\PhpFileSchemaProvider::MODE_WRITE_ONLY,
                'file' => 'runtime/schema.php',
            ],

            \Yiisoft\Yii\Cycle\Schema\Provider\FromConveyorSchemaProvider::class => [
                'generators' => [
                    Cycle\Schema\Generator\SyncTables::class, // sync table changes to database
                ],
            ],
        ],

        /**
         * Config for {@see \Yiisoft\Yii\Cycle\Schema\Conveyor\AnnotatedSchemaConveyor}
         * Annotated entity directories list.
         * {@see \Yiisoft\Aliases\Aliases} are also supported.
         */
        'entity-paths' => [
            '@src',
        ],
        'conveyor' => AttributedSchemaConveyor::class,
    ],
    'yiisoft/yii-swagger' => [
        'annotation-paths' => [
            '@src/Blog/Presentation/Backend/Api',
            '@src/Blog/Presentation/Frontend/Api',
            '@src/IdentityAccess/Presentation/Backend/Api',
            '@src/IdentityAccess/Presentation/Frontend/Api',
            '@src/Site/Presentation/Backend/Api',
            '@src/Site/Presentation/Frontend/Api',
        ],
    ],
];
