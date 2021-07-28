<?php


namespace App\Security;


use App\Service\PasswordGenerator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DiscordUserProvider implements UserProviderInterface
{
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

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HttpClientInterface $client,
        PasswordGenerator $passwordGenerator,
        string $discordClientId,
        string $discordClientSecret
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->client = $client;
        $this->passwordGenerator = $passwordGenerator;
        $this->discordClientId = $discordClientId;
        $this->discordClientSecret = $discordClientSecret;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername(string $username): UserInterface
    {

    }

    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException  if the user is not supported
     * @throws UsernameNotFoundException if the user is not found
     */
    public function refreshUser(UserInterface $user): UserInterface
    {

    }

    /**
     * Whether this provider supports the given user class.
     *
     * @return bool
     */
    public function supportsClass(string $class): bool
    {

    }
}