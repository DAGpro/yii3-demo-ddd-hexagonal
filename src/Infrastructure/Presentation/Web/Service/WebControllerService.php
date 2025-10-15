<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Web\Service;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;

final readonly class WebControllerService
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private FlashInterface $flash,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @param array<string, string|int|float|bool|null> $routeArguments
     */
    public function redirect(string $route, array $routeArguments = []): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(Status::FOUND)
            ->withHeader(
                Header::LOCATION,
                $this->urlGenerator->generate($route, $routeArguments),
            );
    }

    public function notFound(): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(Status::NOT_FOUND);
    }

    /**
     * @param array<string, string|int|float|bool|null> $routeArguments
     */
    public function sessionFlashAndRedirect(
        string $message,
        string $route,
        array $routeArguments = [],
        string $key = 'success',
        bool $removeFlashAfterAccess = true,
    ): ResponseInterface {
        $this->flash->add(
            $key,
            ['body' => $message],
            $removeFlashAfterAccess,
        );
        return $this->responseFactory
            ->createResponse(Status::FOUND)
            ->withHeader(
                Header::LOCATION,
                $this->urlGenerator->generate($route, $routeArguments),
            );
    }

    public function accessDenied(string $reasonPhrase = ''): ResponseInterface
    {
        return $this->responseFactory->createResponse(
            Status::FORBIDDEN,
            $reasonPhrase,
        );
    }
}
