<?php

namespace App\DataFixtures\Faker;

use Nelmio\Alice\Loader\NativeLoader;
use Faker\Generator as FakerGenerator;

class CustomNativeLoader extends NativeLoader
{
    protected function createFakerGenerator(): FakerGenerator
    {
        $generator = parent::createFakerGenerator();
        $generator->addProvider(new MyProvider($generator));
        return $generator;
    }
}