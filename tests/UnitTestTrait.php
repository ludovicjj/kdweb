<?php


namespace App\Tests;

use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger as DoctrineOrmPurger;

trait UnitTestTrait
{
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
        self::bootKernel();
        $entityManager = self::$container->get('doctrine.orm.default_entity_manager');
        $purger = new DoctrineOrmPurger($entityManager);
        $purger->purge();
    }
}