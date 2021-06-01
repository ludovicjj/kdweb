<?php

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTimeImmutable;
use DateTime;
use Nelmio\Alice\Loader\NativeLoader;
use Nelmio\Alice\Throwable\LoadingThrowable;

class ArticleFixtures extends Fixture
{
    const DATA_ENTRY_POINT = __DIR__.'/data/articles.yml';

    public function load(ObjectManager $manager)
    {
        try {
            $objectSet = $this->getNativeLoader()->loadFile(self::DATA_ENTRY_POINT);
            /** @var Article $object */
            foreach ($objectSet->getObjects() as $object) {
                [
                    'dateObject' => $dateObject,
                    'dateString' => $dateString
                ] = $this->generateRandomDateBetweenRange('01-01-2020', '01-06-2021');
                $object->setSlug($object->getSlug() . "-{$dateString}");
                $object->setCreatedAt($dateObject);
                $manager->persist($object);
            }
        } catch (LoadingThrowable $e) {
        }

        $manager->flush();
    }

    private function generateRandomDateBetweenRange(string $start, string $end): array
    {
        $startTimestamp = DateTime::createFromFormat('d-m-Y', $start)->getTimestamp();
        $endTimestamp = DateTime::createFromFormat('d-m-Y', $end)->getTimestamp();
        $randomTimestamp = mt_rand($startTimestamp, $endTimestamp);
        $dateTimeImmutable = (new DateTimeImmutable())->setTimestamp($randomTimestamp);

        return [
            'dateObject' => $dateTimeImmutable,
            'dateString' => $dateTimeImmutable->format('d-m-Y')
        ];
    }

    private function getNativeLoader()
    {
        return new NativeLoader();
    }
}
