<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Http\Event\DeauthenticatedEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class AuthenticationSubscriber implements EventSubscriberInterface
{
    /** @var LoggerInterface $securityLogger */
    private $securityLogger;

    /** @var RequestStack $request */
    private $request;

    public function __construct(
        LoggerInterface $securityLogger,
        RequestStack $request
    ) {
        $this->securityLogger = $securityLogger;
        $this->request = $request;

    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onSecurityAuthenticationFailure',
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onSecurityAuthenticationSuccess',
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
            'Symfony\Component\Security\Http\Event\LogoutEvent' => 'onSecurityLogoutEvent',
            'security.logout_on_change' => 'onSecurityLogoutOnChange',
            SecurityEvents::SWITCH_USER => 'onSecuritySwitchUser'
        ];
    }

    public function onSecurityAuthenticationFailure(AuthenticationFailureEvent $event): void
    {
        ['user_ip' => $userIP] = $this->getRouteNameAndUserIP();
        $securityToken = $event->getAuthenticationToken();
        ['email' => $emailEntered] = $securityToken->getCredentials();

        $this->securityLogger->info(
            sprintf(
                "Anonymous user with IP: '%s' fail authentication with the following email : '%s'",
                $userIP,
                $emailEntered
            )
        );
    }

    public function onSecurityAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        [
            'user_ip' => $userIP,
            'route_name' => $routeName
        ] = $this->getRouteNameAndUserIP();

        if (empty($event->getAuthenticationToken()->getRoleNames())) {
            $this->securityLogger->info(
                sprintf("Anonymous user join us with IP : '%s' on route : '%s'.", $userIP, $routeName)
            );
        } else {
            $userEmail = $this->getUserEmail($event->getAuthenticationToken());
            $this->securityLogger->info(
                sprintf("User '%s' authenticated with success. IP: '%s'", $userEmail, $userIP)
            );
        }
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $userEmail = $this->getUserEmail($event->getAuthenticationToken());
        $this->securityLogger->info(
            sprintf("Welcome '%s' !", $userEmail)
        );
    }

    public function onSecurityLogoutEvent(LogoutEvent $event): void
    {
        /** @var RedirectResponse|null $response */
        $response = $event->getResponse();

        /** @var  TokenInterface|null $token */
        $token = $event->getToken();

        if (!$response || !$token) {
            return;
        }

        $userEmail = $this->getUserEmail($token);
        $targetUrl = $response->getTargetUrl();
        $this->securityLogger->info(
            sprintf("'%s' disconnected and redirect to the following url : '%s'", $userEmail, $targetUrl)
        );
    }

    public function onSecurityLogoutOnChange(DeauthenticatedEvent $event): void
    {
        // ...
    }

    public function onSecuritySwitchUser(SwitchUserEvent $event): void
    {
        // ...
    }

    /**
     * Return user IP and the current route name
     *
     * @return array{user_ip: string, route_name: mixed|null}
     */
    private function getRouteNameAndUserIP(): array
    {
        $request = $this->request->getCurrentRequest();
        if (!$request) {
            return [
                'user_ip'    => 'unknown',
                'route_name' => 'unknown'
            ];
        }

        return [
            'user_ip'    => $request->getClientIp() ?? 'unknown',
            'route_name' => $request->attributes->get('_route')
        ];
    }

    private function getUserEmail(TokenInterface $securityToken): string
    {
        /** @var User $user */
        $user = $securityToken->getUser();
        return $user->getUsername();
    }
}
