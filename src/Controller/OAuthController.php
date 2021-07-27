<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class OAuthController extends AbstractController
{
    private const DISCORD_END_POINT = "https://discord.com/api/oauth2/authorize";

    /** @var CsrfTokenManagerInterface $csrfTokenManager */
    private $csrfTokenManager;

    /** @var UrlGeneratorInterface $urlGenerator */
    private $urlGenerator;

    public function __construct(
        CsrfTokenManagerInterface $csrfTokenManager,
        UrlGeneratorInterface $urlGenerator
    )
    {
        $this->csrfTokenManager = $csrfTokenManager;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @Route("/oauth/discord", name="app_oauth_discord", methods={"GET"})
     */
    public function loginWithDiscord(): RedirectResponse
    {
        $url = $this->urlGenerator->generate("app_login",
            [
                "discord-oauth-provider" => 1
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $queryParams = http_build_query([
            "client_id"     => $this->getParameter("app.discord_client_id"),
            "prompt"        => "consent",
            "redirect_url"  => $url,
            "response_type" => "code",
            "scope"         => "identify email",
            "state"         => $this->csrfTokenManager->getToken("mon-super-token")->getValue(),
        ]);

        return new RedirectResponse(self::DISCORD_END_POINT . "?" . $queryParams);
    }
}