<?php

declare(strict_types=1);

namespace App\Site\Presentation\Frontend\Web\Contact;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Yiisoft\FormModel\FormModelInterface;
use Yiisoft\Mailer\File;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Mailer\Message;
use Yiisoft\Session\Flash\FlashInterface;
use Yiisoft\View\Exception\ViewNotFoundException;
use Yiisoft\View\View;

/**
 * ContactMailer sends an email from the contact form
 */
final class ContactMailer
{
    public function __construct(
        private readonly FlashInterface $flash,
        private readonly LoggerInterface $logger,
        private readonly MailerInterface $mailer,
        private View $view,
        private readonly string $to,
    ) {
        $this->view = $view->withBasePath(dirname(__DIR__) . '/Contact/mail');
    }

    /**
     * @throws Throwable
     * @throws ViewNotFoundException
     */
    public function send(FormModelInterface $form, ServerRequestInterface $request): void
    {
        $messageContent = $this->view->render(dirname(__DIR__) . '/Contact/mail/contact-email', [
            'content' => $form->getPropertyValue('body'),
        ]);

        $content = $this->view->render(dirname(__DIR__) . '/Contact/mail/layouts/html',
            ['content' => $messageContent],
        );

        $message = new Message(
            from: [(string)$form->getPropertyValue('email') => (string)$form->getPropertyValue('name')],
            to: [$this->to],
            subject: (string)$form->getPropertyValue('subject') ?: null,
            textBody: $content,
        );

        $attachFiles = $request->getUploadedFiles();
        foreach ($attachFiles as $attachFile) {
            foreach ($attachFile as $file) {
                /** @var UploadedFileInterface $uploadFile */
                foreach ($file as $uploadFile) {
                    if ($uploadFile->getError() === UPLOAD_ERR_OK) {
                        $message = $message->withAddedAttachments(
                            File::fromContent(
                                (string)$uploadFile->getStream(),
                                $uploadFile->getClientFilename(),
                                $uploadFile->getClientMediaType(),
                            ),
                        );
                    }
                }
            }
        }

        $flashType = 'success';
        $flashMsg = 'Thank you for contacting us, we\'ll get in touch with you as soon as possible.';

        try {
            $this->mailer->send($message);
        } catch (Exception $e) {
            $flashType = 'danger';
            $flashMsg = $e->getMessage();
            $this->logger->error($flashMsg);
        } finally {
            $this->flash->add(
                $flashType,
                ['body' => $flashMsg],
                true,
            );
        }
    }
}
