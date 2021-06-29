<?php


namespace App\EventSubscriber;


use App\Event\ConfirmPasswordEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
        $this->sendJsonResponse();
    }

    public function onPasswordInvalid(ConfirmPasswordEvents $event): void
    {
        $this->sendJsonResponse();
    }

    public function onSessionInvalidate(ConfirmPasswordEvents $event): void
    {
        $loginUrl = $this->urlGenerator->generate("app_login");
        $this->sendJsonResponse($loginUrl);
    }

    /**
     * Send JSON response and exit the parent request.
     *
     * @param string|null $loginUrl
     */
    private function sendJsonResponse(?string $loginUrl = null): void
    {
        $data = [
            "is_password_confirmed" => false,
            "status_code" => Response::HTTP_OK
        ];
        $status = Response::HTTP_OK;

        if ($loginUrl) {
            $data["login_url"] = $loginUrl;
            $data["status_code"] = Response::HTTP_FOUND;
            $status = Response::HTTP_FOUND;
        }


        $response = new JsonResponse($data, $status);
        $response->send();
        exit();
    }
}