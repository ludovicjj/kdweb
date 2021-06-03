<?php

namespace App\DataFixtures;

use App\DataFixtures\Faker\CustomNativeLoader;
use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTimeImmutable;
use DateTime;
use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\String\Slugger\SluggerInterface;

class ArticleFixtures extends Fixture
{
    const DATA_ENTRY_POINT = __DIR__.'/data/createArticles.yml';

    /** @var SluggerInterface $slugger */
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        $objectSet = $this->getCustomNativeLoader()->loadFile(self::DATA_ENTRY_POINT);

        foreach ($objectSet->getObjects() as $object) {
            if ($object instanceof Article) {
                $randomDate = $this->generateRandomDateBetweenRange('01-01-2020', '01-06-2021');

                $title = $object->getTitle();
                if ($title === null) {
                    throw new HttpException(
                        400,
                        sprintf('Invalid Fixture. %s required value to property title', get_class($object))
                    );
                }
                $slug = $this->slugger->slug(strtolower($title)) . '-' . $randomDate['dateString'];
                $object->setCreatedAt($randomDate['dateObject']);
                $object->setSlug($slug);
            }

            $manager->persist($object);
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
        $endDate = DateTime::createFromFormat('d-m-Y', $end);

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

    private function getCustomNativeLoader(): NativeLoader
    {
        return new CustomNativeLoader();
    }
}
