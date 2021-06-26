<?php


namespace App\EventSubscriber;


use App\Event\ConfirmPasswordEvents;
use App\Security\ConfirmPassword;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ConfirmPasswordSubscriber implements EventSubscriberInterface
{
    /** @var UrlGeneratorInterface $urlGenerator */
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @return array<string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConfirmPasswordEvents::MODAL_DISPLAY => "onModalDisplay",
            ConfirmPasswordEvents::PASSWORD_INVALID => "onPasswordInvalid",
            ConfirmPasswordEvents::SESSION_INVALIDATE => "onSessionInvalidate",
        ];
    }

    public function onModalDisplay(ConfirmPasswordEvents $event): void
    {

    }

    public function onPasswordInvalid(ConfirmPasswordEvents $event): void
    {

    }

    public function onSessionInvalidate(ConfirmPasswordEvents $event): void
    {

    }
}