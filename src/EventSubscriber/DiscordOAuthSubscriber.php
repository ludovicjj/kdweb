<?php

namespace App\EventSubscriber;

use App\Event\DiscordOAuthEvent;
use App\Service\SendMail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;

class DiscordOAuthSubscriber implements EventSubscriberInterface
{
    /** @var LoggerInterface $discordLogger */
    private $discordOauthLogger;

    /** @var SendMail $sendMail */
    private $sendMail;

    public function __construct(
        SendMail $sendMail,
       LoggerInterface $discordOauthLogger
    )
    {
        $this->sendMail = $sendMail;
        $this->discordOauthLogger = $discordOauthLogger;
    }

    public static function getSubscribedEvents()
    {
        return [
            DiscordOAuthEvent::SEND_EMAIL_WITH_PASSWORD => "onSendEmailWithPassword",
        ];
    }

    public function onSendEmailWithPassword(DiscordOAuthEvent $event): void
    {
        $email = $event->getEmail();
        $randomPassword = $event->getRandomPassword();

        $this->sendMail->send([
            'recipient_email' => $email,
            'subject' => "Compte utilisateur créer par discord",
            'html_template' => "emails/registration_discord.html.twig",
            'context' => [
                "randomPassword" => $randomPassword
            ]
        ]);

        $this->discordOauthLogger->info(
            "User with email : '{$email}' register by discord OAuth. Mail have been send with a random password"
        );
    }
}