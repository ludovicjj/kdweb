<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\AuthLogRepository;
use App\Security\BruteForceChecker;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Http\Event\DeauthenticatedEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class AuthenticationSubscriber implements EventSubscriberInterface
{
    /** @var LoggerInterface $securityLogger */
    private $securityLogger;

    /** @var RequestStack $requestStack */
    private $requestStack;

    /** @var BruteForceChecker $forceChecker */
    private $forceChecker;

    /** @var AuthLogRepository $authLogRepository */
    private $authLogRepository;

    public function __construct(
        LoggerInterface $securityLogger,
        RequestStack $requestStack,
        BruteForceChecker $forceChecker,
        AuthLogRepository $authLogRepository
    ) {
        $this->securityLogger = $securityLogger;
        $this->requestStack = $requestStack;
        $this->forceChecker = $forceChecker;
        $this->authLogRepository = $authLogRepository;
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

    /**
     * Authentication failure.
     * Add new info message for security log with authentication failure detail.
     * Add failed authentication attempt ONLY IF account exist.
     *
     * @param AuthenticationFailureEvent $event
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function onSecurityAuthenticationFailure(AuthenticationFailureEvent $event): void
    {
        $userIP = $this->getUserIP();
        $securityToken = $event->getAuthenticationToken();
        $credentials = $securityToken->getCredentials();

        if (is_array($credentials) && array_key_exists("email", $credentials)) {
            $emailEntered = $credentials["email"];

            $this->securityLogger->info(
                sprintf(
                    "Anonymous user with IP: '%s' fail authentication with the following email : '%s'",
                    $userIP,
                    $emailEntered
                )
            );

            // Add Failed Authentication Attempt only if account exist.
            if (!$event->getAuthenticationException() instanceof UsernameNotFoundException) {
                $this->forceChecker->addFailedAuthAttempt($emailEntered, $userIP);
            }
        }
    }

    /**
     * Authentication success
     *
     * @param AuthenticationSuccessEvent $event
     */
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
            $securityToken = $event->getAuthenticationToken();
            $userEmail = $this->getUserEmail($securityToken);
            $this->securityLogger->info(
                sprintf("Anonymous user is now authenticated as '%s' with IP: '%s'", $userEmail, $userIP)
            );
        }
    }

    /**
     * Authentication successfully by login form
     *
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $userEmail = $this->getUserEmail($event->getAuthenticationToken());
        $userIP = $this->getUserIP();
        $request = $this->requestStack->getCurrentRequest();

        if ($request && $request->cookies->get("REMEMBERME")) {
            $this->securityLogger->info(
                sprintf("User '%s' authenticated by remember me cookie", $userEmail)
            );
            $this->authLogRepository->addSuccessfulAttempt($userEmail, $userIP, true);
        } else {
            $this->securityLogger->info(
                sprintf("User '%s' authenticated successfully by form login", $userEmail)
            );
            $this->authLogRepository->addSuccessfulAttempt($userEmail, $userIP);
        }
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
        $request = $this->requestStack->getCurrentRequest();
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

    /**
     * Return UserIP or null
     * @return string|null
     */
    public function getUserIP(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return null;
        }
        return $request->getClientIp();
    }

    private function getUserEmail(TokenInterface $securityToken): string
    {
        /** @var User $user */
        $user = $securityToken->getUser();
        return $user->getUsername();
    }
}
