<?php

namespace App\Tests\Controller;

use App\Tests\TestTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    use TestTrait;

    /** @var EntityManagerInterface $entityManager */
    protected $entityManager;

    /** @var KernelBrowser */
    protected $client;

    protected static $initialized = false;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects();

        $this->entityManager = self::$container->get("doctrine")->getManager();

        if (!self::$initialized) {
            $this->truncateTable("users");
            $this->truncateTable("auth_logs");
            $this->createNewUserInDatabase("exemple@contact.com", "password", true);
            self::$initialized = true;
        }
    }

    public function testLoginFormWithNotVerifiedAccount(): void
    {
        $this->createNewUserInDatabase("unverified@contact.com", "password", false);

        $formData = [
            "email" => "unverified@contact.com",
            "password" => "password"
        ];

        $this->sendRequestToLogin($formData);
        $this->assertSelectorTextContains(
            'div[class="alert alert-danger"]',
            "Votre compte n'est pas encore activé. Veuillez vérifié vos e-mail pour activer"
        );
    }

    /**
     * @dataProvider provideBadCredentials
     * @param array $formData
     * @@param string $errorMessage
     */
    public function testLoginFormBruteForce(array $formData, string $errorMessage): void
    {
        $this->sendRequestToLogin($formData);
        $this->assertSelectorTextContains('div[class="alert alert-danger"]', $errorMessage);
    }

    private function sendRequestToLogin(array $formData): void
    {
        $crawler = $this->client->request("GET", "/login");
        $this->assertSelectorTextContains("h1", "Accéder à votre espace");

        $form = $crawler->filter('form[method="post"]')->form($formData);
        $this->client->submit($form);
    }


    public function provideBadCredentials()
    {
        yield [
            [
                "email" => "exemple@contact.com",
                "password" => "bad-password"
            ],
            "Identifiants invalides."
        ];

        yield [
            [
                "email" => "exemple@contact.com",
                "password" => "bad-password"
            ],
            "Identifiants invalides."
        ];

        yield [
            [
                "email" => "exemple@contact.com",
                "password" => "bad-password"
            ],
            "Identifiants invalides."
        ];

        yield [
            [
                "email" => "exemple@contact.com",
                "password" => "bad-password"
            ],
            "La vérification anti-spam a échoué. Veuillez réessayez."
        ];

        yield [
            [
                "email" => "exemple@contact.com",
                "password" => "bad-password"
            ],
            "La vérification anti-spam a échoué. Veuillez réessayez."
        ];

        yield [
            [
                "email" => "exemple@contact.com",
                "password" => "bad-password"
            ],
            "Il semblerait que vous avez oubliez votre mot de passe. Par mesure de sécurité vous devez attendre"
        ];
    }
}