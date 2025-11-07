<?php

declare(strict_types=1);

namespace App\Site\Presentation\Frontend\Web\Contact;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Yiisoft\FormModel\FormHydrator;
use Yiisoft\Http\Header;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\View\Exception\ViewNotFoundException;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final readonly class ContactController
{
    private ViewRenderer $viewRenderer;

    public function __construct(
        private ContactMailer $mailer,
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $url,
        ViewRenderer $viewRenderer,
    ) {
        $this->viewRenderer = $viewRenderer
            ->withControllerName('contact')
            ->withViewPath(__DIR__ . '/views');
    }

    /**
     * @throws Throwable
     * @throws ViewNotFoundException
     */
    public function contact(
        ServerRequestInterface $request,
        FormHydrator $formHydrator,
    ): ResponseInterface {
        $form = new ContactForm();
        if (($request->getMethod() === Method::POST)
            && $formHydrator->populateFromPostAndValidate($form, $request)
        ) {
            $this->mailer->send($form, $request);

            return $this->responseFactory
                ->createResponse(Status::FOUND)
                ->withHeader(Header::LOCATION, $this->url->generate('site/contact'));
        }

        return $this->viewRenderer->render('form', ['form' => $form]);
    }
}
