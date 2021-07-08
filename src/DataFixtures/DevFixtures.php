<?php

namespace App\DataFixtures;

use App\DataFixtures\Faker\CustomNativeLoader;
use App\Entity\Article;
use App\Entity\Picture;
use App\Service\FileUploader;
use App\Utils\DateTimeImmutableTrait;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\String\Slugger\SluggerInterface;

class DevFixtures extends Fixture
{
    use DateTimeImmutableTrait;

    const DATA_ENTRY_POINT = __DIR__.'/data/dev_data.yml';

    /** @var SluggerInterface $slugger */
    private $slugger;

    /** @var FileUploader $fileUploader */
    private $fileUploader;

    /** @var string $uploadDir */
    private $uploadDir;

    /** @var Filesystem $fileSystem */
    private $fileSystem;

    public function __construct(
        SluggerInterface $slugger,
        FileUploader $fileUploader,
        Filesystem $fileSystem,
        string $uploadDir
    ) {
        $this->slugger = $slugger;
        $this->fileUploader = $fileUploader;
        $this->fileSystem = $fileSystem;
        $this->uploadDir = $uploadDir;
    }

    public function load(ObjectManager $manager): void
    {
        $objectSet = $this->getCustomNativeLoader()->loadFile(self::DATA_ENTRY_POINT);
        $this->RemoveExistingUploadDirAndRecreate();

        foreach ($objectSet->getObjects() as $object) {
            if ($object instanceof Picture) {

                if ($object->getImage() === null) {
                    throw new HttpException(
                        400,
                        sprintf('Invalid Fixture. %s required value to property image', get_class($object))
                    );
                }
                $file = $this->fileUploader->upload($object->getImage());
                $object->setPictureName($file['fileName'])
                       ->setPicturePath($file['filePath']);
            }
            if ($object instanceof Article) {
                $randomDate = $this->generateRandomDateBetweenRange('01-01-2020', '08-07-2021');

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

    private function getCustomNativeLoader(): NativeLoader
    {
        return new CustomNativeLoader();
    }

    /**
     * Remove the uploads directory and recreate it
     * see: services.yaml
     */
    private function RemoveExistingUploadDirAndRecreate(): void
    {
        if ($this->fileSystem->exists($this->uploadDir)) {
            $this->fileSystem->remove($this->uploadDir);
            $this->fileSystem->mkdir($this->uploadDir);
        }
    }
}
