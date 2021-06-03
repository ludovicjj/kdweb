<?php

namespace App\DataFixtures\Faker;

use Faker\Provider\Base as BaseProvider;
use Faker\Generator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CustomProvider extends BaseProvider
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
        if (!file_exists(self::PATH_DATA_PICTURE)) {
            mkdir(self::PATH_DATA_PICTURE);
        }

        $imageId = imagecreate(200, 150);

        if ($imageId === false) {
            throw new HttpException(400, "Cannot Initialize new GD image stream");
        }

        $colorId = imagecolorallocate($imageId, 255, 128, 0);

        if ($colorId === false) {
            throw new HttpException(400, "Color allocation failed");
        }

        $filename = self::PATH_DATA_PICTURE . '/' . $name . '.png';
        imagepng($imageId, $filename);
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