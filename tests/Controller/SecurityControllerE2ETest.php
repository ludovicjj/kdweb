<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;
use DateInterval;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Symfony\Component\Panther\PantherTestCase;
use Generator;
use Exception;

class SecurityControllerE2ETest extends PantherTestCase
{
    /** @var EntityManagerInterface $entityManger */
    private $entityManger;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManger = $kernel->getContainer()->get('doctrine')->getManager();
        $this->truncateTable("users");
    }

    /**
     * @dataProvider provideLoginFormBadData
     * @param int $attemptCount
     * @param array $formData
     * @param string $screenshotPath
     * @param string $errorMessage
     */
    public function testLoginFail(
        int $attemptCount,
        array $formData,
        string $screenshotPath,
        string $errorMessage
    ): void
    {
        if ($attemptCount === 1) {
            $this->truncateTable("auth_logs");
        }

        $this->createNewUserInDatabase("exemple@contact.fr", "Password-1", false);
        $client = self::createPantherClient();
        $crawler = $client->request("GET", "/login");
        $form = $crawler->selectButton("Se connecter")->form($formData);
        $client->submit($form);

        $client->takeScreenshot($screenshotPath);
        $this->assertSelectorTextContains('div[class="alert alert-danger"]', $errorMessage);

        if ($attemptCount === 4) {
            try {
                $client->waitFor('iframe', 3);
            } catch (NoSuchElementException $error) {
            } catch (TimeoutException $error) {
            }
            $this->assertSelectorAttributeContains(
                "iframe",
                "title",
                "widget containing checkbox for hCaptcha security challenge"
            );
        }
    }

    public function provideLoginFormBadData(): Generator
    {
        yield [
            1,
            [
                "email" => "exemple@contact.fr",
                "password" => "Password-1"
            ],
            "./var/screenshots/unverified-user.png",
            "Votre compte n'est pas encore activé. Veuillez vérifié vos e-mail pour activer votre compte avant le"
        ];

        yield [
            2,
            [
                "email" => "exemple@contact.fr",
                "password" => "Bad-password"
            ],
            "./var/screenshots/invalid-password-1.png",
            "Identifiants invalides."
        ];

        yield [
            3,
            [
                "email" => "exemple@contact.fr",
                "password" => "Bad-password"
            ],
            "./var/screenshots/invalid-password-2.png",
            "Identifiants invalides."
        ];

        yield [
            4,
            [
                "email" => "exemple@contact.fr",
                "password" => "Bad-password"
            ],
            "./var/screenshots/invalid-hcaptcha.png",
            "La vérification anti-spam a échoué. Veuillez réessayez."
        ];
    }

    private function truncateTable(string $table): void
    {
        try {
            $this->entityManger->getConnection()->executeQuery("TRUNCATE TABLE `{$table}`");
            $this->entityManger->getConnection()->close();
        } catch (Exception $exception) {

        }

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