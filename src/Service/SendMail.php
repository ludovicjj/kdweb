<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class SendMail
{
    /** @var MailerInterface $mailer */
    private $mailer;

    /** @var string $senderEmail */
    private $senderEmail;

    /** @var string $senderName */
    private $senderName;

    public function __construct(
        MailerInterface $mailer,
        string $senderEmail,
        string $senderName
    ) {
        $this->mailer = $mailer;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
    }

    /**
     * @param array<mixed> $arguments
     */
    public function send(array $arguments): void
    {
        [
            'recipient_email' => $recipientEmail,
            'subject' => $subject,
            'html_template' => $htmlTemplate,
            'context' => $context
        ] = $arguments;

        $email = new TemplatedEmail();
        $email
            ->from(new Address($this->senderEmail, $this->senderName))
            ->to($recipientEmail)
            ->subject($subject)
            ->htmlTemplate($htmlTemplate)
            ->context($context);
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $mailerException) {

        }
    }
}