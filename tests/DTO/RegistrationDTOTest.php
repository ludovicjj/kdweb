<?php

namespace App\Tests\DTO;

use App\DTO\RegistrationDTO;
use App\Entity\User;
use App\Tests\UnitTestTrait;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RegistrationDTOTest extends KernelTestCase
{
    use UnitTestTrait;

    private const VALID_EMAIL_VALUE = "test@contact.com";
    private const VALID_PASSWORD_VALUE = "Password-test-123";
    private const VALID_AUTHOR_NAME_VALUE = "john";

    /** @var ValidatorInterface $validator */
    private $validator;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::$container->get("validator");
        $this->entityManager = self::$container->get("doctrine")->getManager();
    }

    /**
     * @testdox Test registrationDTO constraints with valid Data
     */
    public function testRegistrationDtoWithValidData(): void
    {
        $dto = new RegistrationDTO(
            self::VALID_EMAIL_VALUE,
            self::VALID_PASSWORD_VALUE,
            self::VALID_AUTHOR_NAME_VALUE
        );

        $this->getValidatorErrors($dto, 0);
    }

    /**
     * @testdox Test registrationDTO constraints with invalid Data
     * @dataProvider provideBadDataForRegistrationDto
     * @param array $data
     * @param string $errorMessage
     */
    public function testRegistrationDtoWithBadData(array $data, string $errorMessage): void
    {
        $dto = new RegistrationDTO(
            $data["email"],
            $data["password"],
            $data["authorName"]
        );

        $errors = $this->getValidatorErrors($dto, 1);
        $this->assertEquals($errorMessage, $errors[0]->getMessage());
    }

    /**
     * @testdox Test registrationDTO custom constraint for unique email
     */
    public function testRegistrationDtoWithAlreadyUsedEmail(): void
    {
        $this->createUserInDatabase(self::VALID_EMAIL_VALUE, self::VALID_PASSWORD_VALUE);

        $dto = new RegistrationDTO(
            self::VALID_EMAIL_VALUE,
            self::VALID_PASSWORD_VALUE,
            self::VALID_AUTHOR_NAME_VALUE
        );

        $errors = $this->getValidatorErrors($dto, 1);
        $this->assertEquals("Cette adresse email ne peut pas être utilisée.", $errors[0]->getMessage());
    }

    /**
     * @return Generator
     */
    public function provideBadDataForRegistrationDto(): Generator
    {
        yield [
            [
                "email" => null,
                "password" => self::VALID_PASSWORD_VALUE,
                "authorName" => self::VALID_AUTHOR_NAME_VALUE
            ],
            "Le champs email ne peut être vide."
        ];

        yield [
            [
                "email" => self::VALID_EMAIL_VALUE,
                "password" => null,
                "authorName" => self::VALID_AUTHOR_NAME_VALUE
            ],
            "Le champs password ne peut être vide."
        ];

        yield [
            [
                "email" => "test@contact",
                "password" => self::VALID_PASSWORD_VALUE,
                "authorName" => self::VALID_AUTHOR_NAME_VALUE
            ],
            "\"test@contact\" n'est pas une adresse email valide."
        ];

        yield [
            [
                "email" => self::VALID_EMAIL_VALUE,
                "password" => "password",
                "authorName" => self::VALID_AUTHOR_NAME_VALUE
            ],
            "Votre mot de passe doit contenir au minimum 8 caractères avec au moins une majuscule, un chiffre et un caractère spécial."
        ];
    }

    private function getValidatorErrors(RegistrationDTO $dto, int $ExceptedCountErrors): ConstraintViolationListInterface
    {
        $errors = $this->validator->validate($dto);
        $this->assertCount($ExceptedCountErrors, $errors);
        return $errors;
    }

    /**
     * Create a new user with the given email and password.
     * Then persist and flush it in Database.
     *
     * @param string $email
     * @param string $password
     */
    private function createUserInDatabase(string $email, string $password): void
    {
        $user = (new User())
            ->setEmail($email)
            ->setPassword($password)
            ->setIsVerified(false);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}