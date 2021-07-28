<?php

namespace App\Security;

use App\Entity\User;
use App\Event\DiscordOAuthEvent;
use App\Repository\UserRepository;
use App\Service\PasswordGenerator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DiscordUserProvider implements UserProviderInterface
{
    private const DISCORD_ACCESS_TOKEN_END_POINT = "https://discord.com/api/oauth2/token";
    private const DISCORD_USER_DATA_END_POINT = "https://discordapp.com/api/users/@me";

    /** @var EventDispatcherInterface $eventDispatcher */
    private $eventDispatcher;

    /** @var HttpClientInterface $client */
    private $client;

    /** @var PasswordGenerator $passwordGenerator */
    private $passwordGenerator;

    /** @var string $discordClientId */
    private $discordClientId;

    /** @var string $discordClientSecret */
    private $discordClientSecret;

    /** @var UserRepository $userRepository */
    private $userRepository;

    /** @var UrlGeneratorInterface $urlGenerator */
    private $urlGenerator;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HttpClientInterface $client,
        PasswordGenerator $passwordGenerator,
        UserRepository $userRepository,
        UrlGeneratorInterface $urlGenerator,
        string $discordClientId,
        string $discordClientSecret
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->client = $client;
        $this->passwordGenerator = $passwordGenerator;
        $this->userRepository = $userRepository;
        $this->urlGenerator = $urlGenerator;
        $this->discordClientId = $discordClientId;
        $this->discordClientSecret = $discordClientSecret;
    }

    /**
     * @param string $code
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     *
     * @return User
     */
    public function loadUserFromDiscordOAuth(string $code)
    {
        $accessToken = $this->getAccessToken($code);
        $discordUserData = $this->getUserInfo($accessToken);

        [
            "email" => $email,
            "id" => $discordId,
            "username" => $discordUsername
        ] = $discordUserData;

        $user = $this->userRepository->getUserFromDiscordOAuth($discordId, $discordUsername, $email);

        if (!$user) {
            $randomPassword = $this->passwordGenerator->generatePassword(20);
            $user = $this->userRepository->createUserFromDiscordOAuth(
                $discordId,
                $discordUsername,
                $email,
                $randomPassword
            );

            $this->eventDispatcher->dispatch(
                new DiscordOAuthEvent($email, $randomPassword),
                DiscordOAuthEvent::SEND_EMAIL_WITH_PASSWORD
            );
        }

        return $user;
    }

    /**
     * @param string $code
     * @return string
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getAccessToken(string $code): string
    {
        $url = $this->urlGenerator->generate("app_login",
            [
                "discord-oauth-provider" => 1
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $options = [
            "headers" => [
                "Accept" => "application/json",
                "Content-Type" => "application/x-www-form-urlencoded"
            ],
            "body" => [
                "client_id" => $this->discordClientId,
                "client_secret" => $this->discordClientSecret,
                "code" => $code,
                "grant_type" => "authorization_code",
                "redirect_uri" => $url,
                "scope" => "identify email"
            ]
        ];

        $response = $this->client->request("POST", self::DISCORD_ACCESS_TOKEN_END_POINT, $options);
        $data = $response->toArray();

        if (!array_key_exists("access_token", $data)) {
            throw new ServiceUnavailableHttpException(null, "Authentication from discord failed");
        }

        return $data["access_token"];
    }

    /**
     * @param string $accessToken
     * @return array<mixed>
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getUserInfo(string $accessToken)
    {
        $options = [
            "headers" => [
                "Accept" => "application/json",
                "Authorization" => "Bearer {$accessToken}"
            ]
        ];

        $response = $this->client->request("GET", self::DISCORD_USER_DATA_END_POINT, $options);
        $data = $response->toArray();

        if (!$data["email"] || !$data["id"] || !$data["username"]) {
            throw new ServiceUnavailableHttpException(
                null,
                "Discord API has been modified or missing data"
            );
        } elseif (!$data["verified"]) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "Discord Account is not verified");
        }

        return $data;
    }


    public function refreshUser(UserInterface $user)
    {

    }


    public function supportsClass(string $class)
    {

    }

    public function loadUserByUsername(string $username)
    {
        // TODO: Implement loadUserByUsername() method.
    }
}