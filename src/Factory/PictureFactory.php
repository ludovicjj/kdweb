<?php


namespace App\Factory;

use App\Entity\Picture;

class PictureFactory
{
    public static function build(string $name, string $path): Picture
    {
        return (new Picture())
            ->setPicturePath($path)
            ->setPictureName($name)
            ;
    }
}