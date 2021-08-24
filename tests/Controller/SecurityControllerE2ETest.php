<?php

namespace App\Tests\Controller;

use App\Tests\TestTrait;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Symfony\Component\Panther\PantherTestCase;
use Generator;

class SecurityControllerE2ETest extends PantherTestCase
{
    use TestTrait;

    /** @var EntityManagerInterface $entityManager */
    protected $entityManager;

    protected static $initialized = false;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();

        if (!self::$initialized) {
            $this->truncateTable("users");
            $this->truncateTable("auth_logs");
            $this->createNewUserInDatabase("exemple@contact.com", "password", false);
            self::$initialized = true;
        }
    }

    /**
     * @dataProvider provideLoginFormBadData
     * @param int $attemptCount
     * @param array $formData
     * @param string $screenshotPath
     * @param string $errorMessage
     */
    public function testCaptchaWithThreeFailedAttemptLogin(
        int $attemptCount,
        array $formData,
        string $screenshotPath,
        string $errorMessage
    ): void
    {
        $client = self::createPantherClient();
        $crawler = $client->request("GET", "/login");
        $form = $crawler->selectButton("Se connecter")->form($formData);
        $client->submit($form);

        $client->takeScreenshot($screenshotPath);
        $this->assertSelectorTextContains('div[class="alert alert-danger"]', $errorMessage);

        if ($attemptCount >= 3) {
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
                "email" => "exemple@contact.com",
                "password" => "Bad-password"
            ],
            "./var/screenshots/invalid-password-1.png",
            "Identifiants invalides."
        ];

        yield [
            2,
            [
                "email" => "exemple@contact.com",
                "password" => "Bad-password"
            ],
            "./var/screenshots/invalid-password-2.png",
            "Identifiants invalides."
        ];

        yield [
            3,
            [
                "email" => "exemple@contact.com",
                "password" => "Bad-password"
            ],
            "./var/screenshots/invalid-password-3.png",
            "Identifiants invalides."
        ];

        yield [
            4,
            [
                "email" => "exemple@contact.com",
                "password" => "Bad-password"
            ],
            "./var/screenshots/invalid-hcaptcha.png",
            "La vérification anti-spam a échoué. Veuillez réessayez."
        ];
    }
}