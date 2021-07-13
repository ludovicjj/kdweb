<?php


namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
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

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->generateUserWithRandomEmail(3);
    }

    private function generateUserWithRandomEmail(int $number)
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
            throw new ServiceUnavailableHttpException("Missing key 'results' in data returned by API");
        }

        return $data['results'];
    }
}