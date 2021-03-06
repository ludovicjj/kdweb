<?php


namespace App\Tests;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class PublicUrlSmokeTest extends WebTestCase
{
    use FunctionalTestTrait;

    public function testPublicUri(): void
    {
        $client = $this->createClientAndFollowRedirect();
        $publicUri = $this->getPublicUri();
        $countPublicUri = count($publicUri);
        $countUriSuccessfulLoaded = 0;
        $uriFailureLoaded = [];


        foreach ($publicUri as $uri)
        {
            $client->request("GET", $uri);
            if ($client->getResponse()->getStatusCode() === Response::HTTP_OK) {
                $countUriSuccessfulLoaded++;
            } else {
                $uriFailureLoaded[] = $uri;
            }
        }

        $this->assertSame($countPublicUri, $countUriSuccessfulLoaded);
        $this->assertEmpty($uriFailureLoaded);
    }

    private function getPublicUri(): array
    {
        /** @var RouterInterface $router */
        $router = self::$container->get("router");
        $routes = $router->getRouteCollection()->all();
        return array_map(
            function (Route $publicRoutes) {
                return $publicRoutes->getPath();
            },
            array_filter($routes, function(Route $route) {
                return $route->getDefault("_public_access") === true;
            })
        );
    }
}