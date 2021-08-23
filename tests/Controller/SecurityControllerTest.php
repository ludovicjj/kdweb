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

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects();

        $this->entityManager = self::$container->get("doctrine")->getManager();
        $this->truncateTable("users");
    }

    /**
     * @dataProvider provideBadCredentials
     * @param int $attemptLoginCount
     * @param array $formData
     * @@param string $errorMessage
     */
    public function testLoginFormBruteForce(int $attemptLoginCount, array $formData, string $errorMessage): void
    {
        // create user for test
        $this->createNewUserInDatabase("exemple@contact.com", "password", true);

        // go to login, fill form and send data
        $this->sendRequestToLogin($formData);

        // check error message
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
            1,
            [
                "email" => "exemple@contact.com",
                "password" => "bad-password"
            ],
            "Identifiants invalides."
        ];
    }
}