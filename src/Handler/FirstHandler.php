<?php


namespace App\Handler;

use App\HandlerFactory\HandlerInterface;

class FirstHandler implements HandlerInterface
{
    public function handle(): string
    {
        return "i am the first handler";
    }
}