<?php

declare(strict_types=1);

namespace App\Site\Presentation\Frontend\Api\Controller\Actions;

use OpenApi\Attributes\Get;
use OpenApi\Attributes\Info;
use OpenApi\Attributes\Response;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;

#[Info(version: '2.0', title: 'Yii demo Api')]
final readonly class ApiInfo implements MiddlewareInterface
{
    public function __construct(private DataResponseFactoryInterface $responseFactory)
    {
    }

    #[Get(
        '/api/info/v2',
        responses: [
            new Response(
                response: '200',
                description: 'Get api version',
            ),
        ],
    )]
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->responseFactory->createResponse(['version' => '2.0', 'author' => 'yiisoft']);
    }
}
