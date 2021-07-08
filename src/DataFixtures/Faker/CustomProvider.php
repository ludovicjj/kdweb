<?php

namespace App\DataFixtures\Faker;

use App\Utils\DateTimeImmutableTrait;
use Faker\Provider\Base as BaseProvider;
use Faker\Generator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use DateTimeImmutable;

class CustomProvider extends BaseProvider
{
    use DateTimeImmutableTrait;
    const PATH_DATA_PICTURE = __DIR__.'/../DataPicture';

    public function __construct(Generator $generator)
    {
        parent::__construct($generator);
    }

    /**
     * Return a random character name
     *
     * @return string
     */
    public function customName(): string
    {
        $key = array_rand($this->names);
        return $this->names[$key];
    }

    /**
     * Return a random color
     *
     * @return string
     */
    public function customColor(): string
    {
        $key = array_rand($this->color);
        return $this->color[$key];
    }

    public function generateExpiredDatetime(string $start = "01-01-2020", string $end = "05-10-2020"): DateTimeImmutable
    {
        ["dateObject" => $dateObject] = $this->generateRandomDateBetweenRange($start, $end);
        return $dateObject;
    }

    /**
     * Create and save a PNG image into DataPicture directory.
     * Return an UploadedFile from PNG image.
     *
     * @param string $name
     * @param string $color
     * @return UploadedFile
     */
    public function customImage(string $name, string $color): UploadedFile
    {
        if (!file_exists(self::PATH_DATA_PICTURE)) {
            mkdir(self::PATH_DATA_PICTURE);
        }

        $imageId = imagecreate(200, 150);

        if ($imageId === false) {
            throw new HttpException(400, "Cannot Initialize new GD image stream");
        }

        $this->setColorAllocation($color, $imageId);

        $filename = self::PATH_DATA_PICTURE . '/' . $name . '.png';
        imagepng($imageId, $filename);
        return new UploadedFile($filename, $name, null, null, true);
    }

    /**
     * @param string $color
     * @param resource $imageId
     */
    private function setColorAllocation(string $color, $imageId): void
    {
        switch ($color) {
            case 'orange':
                imagecolorallocate($imageId, 255, 128, 0);
                break;
            case 'blue':
                imagecolorallocate($imageId, 0, 128, 255);
                break;
            case 'lightblue':
                imagecolorallocate($imageId, 156, 227, 254);
                break;
            case 'black':
                imagecolorallocate($imageId, 0, 0, 0);
                break;
            case 'yellow':
                imagecolorallocate($imageId, 240, 247, 9);
                break;
            case 'green':
                imagecolorallocate($imageId, 1, 126, 11);
                break;
            default:
                imagecolorallocate($imageId, 255, 255, 255);
        }
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

    /** @var string[]  */
    private $color = [
        'orange',
        'blue',
        'lightblue',
        'black',
        'yellow',
        'green',
    ];
}