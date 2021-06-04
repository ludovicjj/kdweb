<?php


namespace App\Service;


use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    /** @var SluggerInterface $slugger */
    private $slugger;

    /** @var string $uploadDir */
    private $uploadDir;

    public function __construct(
        SluggerInterface $slugger,
        string $uploadDir
    ) {
        $this->slugger = $slugger;
        $this->uploadDir = $uploadDir;
    }

    /**
     * Upload file and return it's filename and filepath.
     *
     * @param UploadedFile $file
     * @return array{fileName: string, filePath: string}
     */
    public function upload(UploadedFile $file): array
    {
        $fileName = $this->generateUniqueFileName($file);

        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir);
        }

        try {
            $file->move($this->uploadDir, $fileName);
        } catch (FileException $fileException) {
            throw $fileException;
        }

        return [
            'fileName' => $fileName,
            'filePath' => $this->uploadDir . $fileName
        ];
    }

    /**
     * Generate an unique filename.
     *
     * @param UploadedFile $file
     * @return string
     */
    private function generateUniqueFileName(UploadedFile $file): string
    {
        $originFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $originFileNameSlugged = $this->slugger->slug(strtolower($originFileName));
        $randomId = uniqid();
        return "{$originFileNameSlugged}-{$randomId}.{$file->guessExtension()}";
    }
}