<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;
use DateInterval;
use Symfony\Component\Panther\PantherTestCase;

class SecurityControllerTest extends PantherTestCase
{
    /** @var EntityManagerInterface $entityManger */
    private $entityManger;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManger = $kernel->getContainer()->get('doctrine')->getManager();
        $this->truncateTable("users");
    }


    public function testLoginFailWithUserNotVerifiedAccount()
    {
        $this->createNewUserInDatabase("paul@contact.fr", "Password-1", false);
        $client = self::createPantherClient();
        $crawler = $client->request("GET", "/login");
        $form = $crawler->selectButton("Se connecter")->form([
            "email" => "paul@contact.fr",
            "password" => "Password-1"
        ]);
        $client->submit($form);

        $client->takeScreenshot("./var/tests/screenshots/unverified-user-01.png");
        $this->assertSelectorTextContains(
            'div[class="alert alert-danger"]',
            "Vous devez confirmer votre compte avant de vous connecter."
        );
    }

    private function truncateTable(string $table): void
    {
        $this->entityManger->getConnection()->executeQuery("TRUNCATE TABLE `{$table}`");
        $this->entityManger->getConnection()->close();
    }

    private function createNewUserInDatabase(string $email, string $password, bool $isVerified): void
    {
        $user = (new User())
            ->setEmail($email)
            ->setPassword($password)
            ->setIsVerified($isVerified);

        if (!$isVerified) {
            $user->setAccountMustBeVerifiedBefore((new DateTimeImmutable())->add(new DateInterval("P1D")));
        }

        $this->entityManger->persist($user);
        $this->entityManger->flush();
    }
}