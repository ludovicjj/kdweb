<?php


namespace App\Tests;


use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpKernel\KernelInterface;

trait TestTrait
{
    private function createClientAndFollowRedirect(): KernelBrowser
    {
        $client = static::createClient();
        $client->followRedirects();
        return $client;
    }

    private function clientGoesOnPage(string $method, string $url): KernelBrowser
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
        $connection = $entityManager->getConnection()->executeQuery("TRUNCATE TABLE `{$table}`");
        $entityManager->getConnection()->close();
    }
}