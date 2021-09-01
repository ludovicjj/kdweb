<?php

namespace App\Factory;

use App\DTO\DTOInterface;
use App\Entity\Author;
use App\Entity\User;
use DateTimeImmutable;
use DateInterval;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class CreateUserFactory
{
    /** @var TokenGeneratorInterface $tokenGenerator */
    private $tokenGenerator;

    public function __construct(TokenGeneratorInterface $tokenGenerator)
    {
        $this->tokenGenerator = $tokenGenerator;
    }

    public function build(DTOInterface $dto): User
    {
        /** @var string $email */
        $email = $dto->getEmail();

        /** @var string $plainPassword */
        $plainPassword = $dto->getPassword();

        /** @var string $authorName */
        $authorName = $dto->getAuthorName();

        $author = (new Author())->setName($authorName);

        return (new User())
            ->setEmail($email)
            ->setPassword($plainPassword) // listener hash plain password
            ->setAuthor($author)
            ->setRegistrationToken($this->tokenGenerator->generateToken())
            ->setAccountMustBeVerifiedBefore((new DateTimeImmutable('now'))->add(new DateInterval("P1D")))
            ->setIsVerified(false)
        ;
    }
}