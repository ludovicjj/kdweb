<?php

namespace App\Handler;

use App\HandlerFactory\HandlerInterface;

class SecondHandler implements HandlerInterface
{
    public function handle(): string
    {
        return "i am the second handler";
    }
}