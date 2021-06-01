<?php

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTimeImmutable;
use DateTime;
use Nelmio\Alice\Loader\NativeLoader;
use Nelmio\Alice\Throwable\LoadingThrowable;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\String\Slugger\SluggerInterface;

class ArticleFixtures extends Fixture
{
    const DATA_ENTRY_POINT = __DIR__.'/data/articles.yml';

    /** @var SluggerInterface $slugger */
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        try {
            $objectSet = $this->getNativeLoader()->loadFile(self::DATA_ENTRY_POINT);
            /** @var Article $object */
            foreach ($objectSet->getObjects() as $object) {
                [
                    'dateObject' => $dateObject,
                    'dateString' => $dateString
                ] = $this->generateRandomDateBetweenRange('01-01-2020', '01-06-2021');
                /** @var string $title */
                $title = $object->getTitle();
                $slug = $this->slugger->slug(strtolower($title)) . '-' . $dateString;
                $object->setSlug($slug);
                $object->setCreatedAt($dateObject);
                $manager->persist($object);
            }
        } catch (LoadingThrowable $e) {
        }

        $manager->flush();
    }

    /**
     * Generate random DateTimeImmutable object and related date string,
     * Between a stat date and an end date.
     *
     * @param string $start
     * @param string $end
     * @return array{dateObject: DateTimeImmutable, dateString: string}
     */
    private function generateRandomDateBetweenRange(string $start, string $end): array
    {
        $startDate = DateTime::createFromFormat('d-m-Y', $start);
        $endDate = DateTime::createFromFormat('d-m-y', $end);
        if (!$startDate || !$endDate) {
            throw new HttpException(400, 'Parameters invalid, expected date with format d-m-Y');
        }
        $randomTimestamp = mt_rand($startDate->getTimestamp(), $endDate->getTimestamp());
        $dateTimeImmutable = (new DateTimeImmutable())->setTimestamp($randomTimestamp);
        return [
            'dateObject' => $dateTimeImmutable,
            'dateString' => $dateTimeImmutable->format('d-m-Y')
        ];
    }

    private function getNativeLoader(): NativeLoader
    {
        return new NativeLoader();
    }
}
