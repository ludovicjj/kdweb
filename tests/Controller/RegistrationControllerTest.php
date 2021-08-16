<?php

namespace App\Tests\Controller;

use App\Tests\TestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
        $client = $this->clientGoesOnPage("GET", "/register");
        $this->purgeDatabaseBeforeTest("users");

        $client->submitForm(
            "Valider",
            [
                "registration_form[email]" => "test@contact.com",
                "registration_form[password][first]" => "John-doe-123",
                "registration_form[password][second]" => "John-doe-123",
                "registration_form[agreeTerms]" => true,
            ]
        );
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame("app_login");
    }
}