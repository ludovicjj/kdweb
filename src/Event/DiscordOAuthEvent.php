<?php


namespace App\Event;


use Symfony\Contracts\EventDispatcher\Event;

class DiscordOAuthEvent extends Event
{
    public const SEND_EMAIL_WITH_PASSWORD = "discord_oauth_event.send_email_with_password";

    /** @var string $email */
    private $email;

    /** @var string $randomPassword */
    private $randomPassword;

    public function __construct(string $email, string $randomPassword)
    {
        $this->email = $email;
        $this->randomPassword = $randomPassword;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRandomPassword(): string
    {
        return $this->randomPassword;
    }
}