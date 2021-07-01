<?php

namespace App\Entity;

use App\Repository\AuthLogRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

/**
 * @ORM\Entity(repositoryClass=AuthLogRepository::class)
 * @ORM\Table(name="auth_logs")
 */
class AuthLog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var int $id
     */
    private $id;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @var DateTimeImmutable $authAttemptAt
     */
    private $authAttemptAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string|null $userIp
     */
    private $userIp;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string $emailEntered
     */
    private $emailEntered;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean $isSuccessfulAuth
     */
    private $isSuccessfulAuth;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null $startBlackListingAt
     */
    private $startBlackListingAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null $endBlackListingAt
     */
    private $endBlackListingAt;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean $isRememberMeAuth
     */
    private $isRememberMeAuth;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null $deauthenticatedAt
     */
    private $deauthenticatedAt;

    public function __construct(
        string $emailEntered,
        ?string $userIp
    ) {
        $this->authAttemptAt = new DateTimeImmutable("now");
        $this->emailEntered = $emailEntered;
        $this->userIp = $userIp;
        $this->isRememberMeAuth = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthAttemptAt(): DateTimeImmutable
    {
        return $this->authAttemptAt;
    }

    public function setAuthAttemptAt(DateTimeImmutable $authAttemptAt): self
    {
        $this->authAttemptAt = $authAttemptAt;

        return $this;
    }

    public function getUserIp(): ?string
    {
        return $this->userIp;
    }

    public function setUserIp(?string $userIp): self
    {
        $this->userIp = $userIp;

        return $this;
    }

    public function getEmailEntered(): string
    {
        return $this->emailEntered;
    }

    public function setEmailEntered(string $emailEntered): self
    {
        $this->emailEntered = $emailEntered;

        return $this;
    }

    public function getIsSuccessfulAuth(): bool
    {
        return $this->isSuccessfulAuth;
    }

    public function setIsSuccessfulAuth(bool $isSuccessfulAuth): self
    {
        $this->isSuccessfulAuth = $isSuccessfulAuth;

        return $this;
    }

    public function getStartBlackListingAt(): ?DateTimeImmutable
    {
        return $this->startBlackListingAt;
    }

    public function setStartBlackListingAt(DateTimeImmutable $startBlackListingAt): self
    {
        $this->startBlackListingAt = $startBlackListingAt;

        return $this;
    }

    public function getEndBlackListingAt(): ?DateTimeImmutable
    {
        return $this->endBlackListingAt;
    }

    public function setEndBlackListingAt(DateTimeImmutable $endBlackListingAt): self
    {
        $this->endBlackListingAt = $endBlackListingAt;

        return $this;
    }

    public function getIsRememberMeAuth(): bool
    {
        return $this->isRememberMeAuth;
    }

    public function setIsRememberMeAuth(bool $isRememberMeAuth): self
    {
        $this->isRememberMeAuth = $isRememberMeAuth;

        return $this;
    }

    public function getDeauthenticatedAt(): ?DateTimeImmutable
    {
        return $this->deauthenticatedAt;
    }

    public function setDeauthenticatedAt(DateTimeImmutable $deauthenticatedAt): self
    {
        $this->deauthenticatedAt = $deauthenticatedAt;

        return $this;
    }
}
