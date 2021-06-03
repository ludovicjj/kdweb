<?php

namespace App\DataFixtures\Faker;

use Faker\Provider\Base as BaseProvider;
use Faker\Generator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MyProvider extends BaseProvider
{
    const PATH_DATA_PICTURE = __DIR__.'/../DataPicture';

    public function __construct(Generator $generator)
    {
        parent::__construct($generator);
    }

    public function customName(): string
    {
        $key = array_rand($this->names);
        return $this->names[$key];
    }

    public function customImage(string $name): UploadedFile
    {
        // Check if directory exist
        if (!file_exists(self::PATH_DATA_PICTURE)) {
            mkdir(self::PATH_DATA_PICTURE);
        }

        // create image and define size
        $image = imagecreate(200, 150);
        // define color image
        imagecolorallocate($image, 255, 128, 0);
        $filename = self::PATH_DATA_PICTURE . '/' . $name . '.png';
        // Save image into dir
        imagepng($image, $filename);

        // return uploadedFile
        return new UploadedFile($filename, $name, null, null, true);
    }

    /** @var string[]  */
    private $names = [
        'Mario',
        'Luigi',
        'Sonic',
        'Pikachu',
        'Link',
        'Lara Croft',
        'Zelda',
        'Pac-Man',
    ];
}