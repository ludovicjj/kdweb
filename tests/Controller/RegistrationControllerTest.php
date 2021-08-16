<?php

namespace App\Tests\Controller;

use App\Tests\TestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Generator;

class RegistrationControllerTest extends WebTestCase
{
    use TestTrait;

    public function testGetRequestToRegistrationPage(): void
    {
        $this->clientGoesOnPage("GET", "/register");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un compte utilisateur');
    }

    public function testHoneyPot(): void
    {
        $client = $this->clientGoesOnPage("GET", "/register");
        $client->submitForm(
            "Valider",
            [
                "registration_form[email]" => "test@contact.com",
                "registration_form[password][first]" => "mypassword",
                "registration_form[password][second]" => "mypassword",
                "registration_form[agreeTerms]" => true,
                "registration_form[phone]" => "my phone number",
                "registration_form[faxNumber]" => "my fax number",
            ]
        );

        $this->assertResponseStatusCodeSame(403, "Potential Bot fill hidden field.");
        $this->assertRouteSame("app_register");
    }

    public function testRegistrationFormWithSuccess()
    {
        $client = static::createClient();
        $crawler = $client->request("GET", "/register");
        $this->purgeTableBeforeTest("users");

        $form = $crawler->selectButton('Valider')->form([
            "registration_form[email]" => "test@contact.com",
            "registration_form[password][first]" => "John-doe-123",
            "registration_form[password][second]" => "John-doe-123",
            "registration_form[agreeTerms]" => true
        ]);
        $client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $client->followRedirect();
        $this->assertRouteSame("app_login");
    }

    /**
     * @param array $formData
     * @param string $errorMessage
     * @dataProvider provideBadRequestsForRegistrationForm
     */
    public function testRegistrationFormWithBadData(
        array $formData,
        string $errorMessage
    ): void
    {
        $client = static::createClient();
        $crawler = $client->request("GET", "/register");
        $this->purgeTableBeforeTest("users");

        $form = $crawler->selectButton('Valider')->form($formData);
        $client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('span.form-error-message', $errorMessage);
    }

    /**
     * @return Generator
     */
    public function provideBadRequestsForRegistrationForm(): Generator
    {
        yield [
            [
                "registration_form[email]" => "",
                "registration_form[password][first]" => "John-doe-123",
                "registration_form[password][second]" => "John-doe-123",
                "registration_form[agreeTerms]" => true
            ],
            "Le champs email ne peut être vide."
        ];

        yield [
            [
                "registration_form[email]" => "test@contact.com",
                "registration_form[password][first]" => "",
                "registration_form[password][second]" => "",
                "registration_form[agreeTerms]" => true
            ],
            "Le champs password ne peut être vide."
        ];

        yield [
            [
                "registration_form[email]" => "test@contact.com",
                "registration_form[password][first]" => "John-doe-123",
                "registration_form[password][second]" => "",
                "registration_form[agreeTerms]" => true
            ],
            "Les mot de passe ne correspondent pas."
        ];

        yield [
            [
                "registration_form[email]" => "test@contact.com",
                "registration_form[password][first]" => "John-doe-123",
                "registration_form[password][second]" => "John-doe-123",
                "registration_form[agreeTerms]" => false
            ],
            "Vous devez accepter les conditions d'utilisation de se site pour vous inscrire."
        ];

        yield [
            [
                "registration_form[email]" => "test@contact",
                "registration_form[password][first]" => "John-doe-123",
                "registration_form[password][second]" => "John-doe-123",
                "registration_form[agreeTerms]" => true
            ],
            "\"test@contact\" n'est pas une adresse email valide."
        ];
    }
}