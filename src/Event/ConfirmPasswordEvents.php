<?php


namespace App\Event;


use Symfony\Contracts\EventDispatcher\Event;

class ConfirmPasswordEvents extends Event
{
    public const MODAL_DISPLAY = "confirm_password_events.modal_display";
    public const PASSWORD_INVALID = "confirm_password_events.password_invalid";
    public const SESSION_INVALIDATE = "confirm_password_events.session_invalidate";
}