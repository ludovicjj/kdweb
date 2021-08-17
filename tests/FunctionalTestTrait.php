<?php


namespace App\Tests;


use Doctrine\Common\DataFixtures\Purger\ORMPurger as DoctrineOrmPurger;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpKernel\KernelInterface;

trait FunctionalTestTrait
{
    /**
     * @var KernelBrowser $client
     */
    private $client;

    private function createClientAndFollowRedirect(): KernelBrowser
    {
        $this->client->followRedirects();
        return $this->client;
    }

    private function clientGoesOnPageWithFollowRedirect(string $method, string $url): KernelBrowser
    {
        $client = $this->createClientAndFollowRedirect();
        $client->request($method, $url);
        return $client;
    }

    private function purgeTableBeforeTest(string $table): void
    {
        /** @var KernelInterface $kernel */
        $kernel = self::bootKernel();
        $entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $entityManager->getConnection()->executeQuery("TRUNCATE TABLE `{$table}`");
        $entityManager->getConnection()->close();
    }

    /**
     * @before
     */
    protected function purgeDatabase(): void
    {
        $this->client = static::createClient();
        $entityManager = self::$container->get('doctrine.orm.default_entity_manager');
        $purger = new DoctrineOrmPurger($entityManager);
        $purger->purge();
    }
}