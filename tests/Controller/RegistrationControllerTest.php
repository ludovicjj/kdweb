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
}