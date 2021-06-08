<?php

namespace App\DataFixtures;

use App\DataFixtures\Faker\CustomNativeLoader;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Nelmio\Alice\Loader\NativeLoader;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    const DATA_ENTRY_POINT = __DIR__.'/data/createUsers.yml';

    public function load(ObjectManager $manager): void
    {
        $objectSet = $this->getCustomNativeLoader()->loadFile(self::DATA_ENTRY_POINT);
        foreach ($objectSet->getObjects() as $object) {
            $manager->persist($object);
        }

        $manager->flush();
    }

    private function getCustomNativeLoader(): NativeLoader
    {
        return new CustomNativeLoader();
    }

    public static function getGroups(): array
    {
        return ['users', 'all'];
    }
}
