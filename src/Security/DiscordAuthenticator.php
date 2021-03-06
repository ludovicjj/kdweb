<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class DiscordAuthenticator extends AbstractGuardAuthenticator
{
    /** @var CsrfTokenManagerInterface $csrfTokenManager */
    private $csrfTokenManager;

    /** @var UrlGeneratorInterface $urlGenerator */
    private $urlGenerator;

    /** @var DiscordUserProvider $discordUserProvider */
    private $discordUserProvider;

    /** @var SessionInterface $sessionInterface */
    private $sessionInterface;

    public function __construct(
        CsrfTokenManagerInterface $csrfTokenManager,
        UrlGeneratorInterface $urlGenerator,
        DiscordUserProvider $discordUserProvider,
        SessionInterface $sessionInterface
    )
    {
        $this->csrfTokenManager = $csrfTokenManager;
        $this->urlGenerator = $urlGenerator;
        $this->discordUserProvider = $discordUserProvider;
        $this->sessionInterface = $sessionInterface;
    }

    public function supports(Request $request): bool
    {
        return $request->query->has("discord-oauth-provider");
    }

    /**
     * @param Request $request
     * @return array<string, string|null>
     */
    public function getCredentials(Request $request): array
    {
        $state = $request->query->get("state");

        if (!$state || !$this->csrfTokenManager->isTokenValid(new CsrfToken("mon-super-token", $state))) {
            throw new InvalidCsrfTokenException();
        }

        return [
            "code" => $request->query->get("code")
        ];
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return User|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider): ?User
    {
        if ($credentials["code"] === null)
        {
            return null;
        }

        return $this->discordUserProvider->loadUserFromDiscordOAuth($credentials["code"]);
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $message =  $exception->getMessage();
        $this->sessionInterface->getFlashBag()->add("danger", $message);
        return new RedirectResponse($this->urlGenerator->generate("app_login"));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        return new RedirectResponse($this->urlGenerator->generate("app_user_account_home"));
    }

    public function start(Request $request, AuthenticationException $authException = null): JsonResponse
    {
        return new JsonResponse([
            "message" => "authentification requise"
        ], Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}
