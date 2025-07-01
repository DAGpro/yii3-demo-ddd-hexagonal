<?php

declare(strict_types=1);

namespace App\Site\Presentation\Frontend\Web\Contact;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\FormModel\FormModelInterface;
use Yiisoft\Mailer\File;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Session\Flash\FlashInterface;

/**
 * ContactMailer sends an email from the contact form
 */
final readonly class ContactMailer
{
    private MailerInterface $mailer;

    public function __construct(
        private FlashInterface $flash,
        private LoggerInterface $logger,
        MailerInterface $mailer,
        private string $sender,
        private string $to,
    ) {
        $this->mailer = $mailer->withTemplate(new MessageBodyTemplate(__DIR__ . '/mail/'));
    }

    public function send(FormModelInterface $form, ServerRequestInterface $request)
    {
        $message = $this->mailer
            ->compose(
                'contact-email',
                [
                    'content' => $form->getPropertyValue('body'),
                ],
            )
            ->withSubject($form->getPropertyValue('subject'))
            ->withFrom([$form->getPropertyValue('email') => $form->getPropertyValue('name')])
            ->withSender($this->sender)
            ->withTo($this->to);

        $attachFiles = $request->getUploadedFiles();
        foreach ($attachFiles as $attachFile) {
            foreach ($attachFile as $file) {
                foreach ($file as $uploadFile) {
                    if ($uploadFile->getError() === UPLOAD_ERR_OK) {
                        $message = $message->withAttached(
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

        try {
            $this->mailer->send($message);
            $flashMsg = 'Thank you for contacting us, we\'ll get in touch with you as soon as possible.';
        } catch (Exception $e) {
            $flashMsg = $e->getMessage();
            $this->logger->error($flashMsg);
        } finally {
            $this->flash->add(
                isset($e) ? 'danger' : 'success',
                [
                    'body' => $flashMsg,
                ],
                true,
            );
        }
    }
}
