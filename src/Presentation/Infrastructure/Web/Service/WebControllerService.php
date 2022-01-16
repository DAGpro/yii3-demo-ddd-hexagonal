<?php

declare(strict_types=1);

namespace App\Presentation\Infrastructure\Web\Service;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;

final class WebControllerService
{
    private ResponseFactoryInterface $responseFactory;
    private UrlGeneratorInterface $urlGenerator;
    private FlashInterface $flash;

    public function __construct(ResponseFactoryInterface $responseFactory, FlashInterface $flash, UrlGeneratorInterface $urlGenerator)
    {
        $this->responseFactory = $responseFactory;
        $this->urlGenerator = $urlGenerator;
        $this->flash = $flash;
    }

    public function redirect(string $url, array $urlArguments = []): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(Status::FOUND)
            ->withHeader(Header::LOCATION, $this->urlGenerator->generate($url, $urlArguments));
    }

    public function notFound(): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(Status::NOT_FOUND);
    }

    public function sessionFlashAndRedirect(
        string $url,
        string $message,
        string $key = 'success',
        bool $removeAfterAccess = true
    ): ResponseInterface {
        $this->flash->add(
            $key,
            ['body' => $message],
            $removeAfterAccess
        );
        return $this->responseFactory
            ->createResponse(Status::FOUND)
            ->withHeader(Header::LOCATION, $url);
    }
}
