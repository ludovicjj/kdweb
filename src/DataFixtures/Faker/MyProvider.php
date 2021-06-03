<?php

namespace App\DataFixtures\Faker;

use Faker\Provider\Base as BaseProvider;
use Faker\Generator;

class MyProvider extends BaseProvider
{
    public function __construct(Generator $generator)
    {
        parent::__construct($generator);
    }

    public function customName()
    {
        $key = array_rand($this->names);
        return $this->names[$key];
    }

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