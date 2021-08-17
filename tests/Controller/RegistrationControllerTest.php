<?php

namespace App\Tests\Controller;

use App\Tests\FunctionalTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Generator;

class RegistrationControllerTest extends WebTestCase
{
    use FunctionalTestTrait;

    public function testGetRequestToRegistrationPage(): void
    {
        $this->clientGoesOnPageWithFollowRedirect("GET", "/register");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un compte utilisateur');
    }

    /**
     * @testdox Test Honey Pot.
     */
    public function testHoneyPot(): void
    {
        $client = $this->clientGoesOnPageWithFollowRedirect("GET", "/register");
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

    /**
     * @testdox Test registration form with valid Data
     */
    public function testRegistrationFormWithSuccess()
    {
        $client = $this->clientGoesOnPageWithFollowRedirect("GET", "/register");
        $client->submitForm("Valider", [
            "registration_form[email]" => "test@contact.com",
            "registration_form[password][first]" => "John-doe-123",
            "registration_form[password][second]" => "John-doe-123",
            "registration_form[agreeTerms]" => true
        ]);
        $this->assertRouteSame("app_login");
    }

    /**
     * @testdox Test registration form with invalid Data
     *
     * @param array $formData
     * @param string $errorMessage
     * @dataProvider provideBadRequestsForRegistrationForm
     */
    public function testRegistrationFormWithBadData(
        array $formData,
        string $errorMessage
    ): void
    {
        $client = $this->clientGoesOnPageWithFollowRedirect("GET", "/register");
        $client->submitForm("Valider", $formData);
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