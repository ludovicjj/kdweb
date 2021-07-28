<?php


namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiUserFixtures extends Fixture
{
    /** @var HttpClientInterface $client */
    private $client;

    /** @var ObjectManager $manager */
    private $manager;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->generateUserWithRandomEmail(3);
    }

    /**
     * @param int $number
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function generateUserWithRandomEmail(int $number): void
    {
        $data = $this->fetchRandomUserEmail($number);

        for ($i = 0; $i < $number; $i++) {
            $user = new User();
            $user
                ->setPassword("secret")
                ->setEmail($data[$i]['email'])
                ->setIsVerified(true);

            $this->manager->persist($user);
            $this->manager->flush();
        }
    }

    /**
     * @param int $numberOfResults
     * @param string $nationality
     *
     * @return array<mixed>
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function fetchRandomUserEmail(int $numberOfResults = 1, string $nationality = "fr"): array
    {
        $response = $this->client->request("GET", "https://randomuser.me/api/", [
            "headers" => [
                "Accept" => "application/json",
                "Content-Type" => "application/json"
            ],
            "query" => [
                "format" => "json",
                "inc" => "email",
                "nat" => $nationality,
                "results" => $numberOfResults
            ]
        ]);

        $data = $response->toArray();

        if (!array_key_exists("results", $data)) {
            throw new ServiceUnavailableHttpException(
                null,
                "Missing key 'results' in data returned by API"
            );
        }

        return $data['results'];
    }
}