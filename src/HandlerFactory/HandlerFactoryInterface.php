<?php

namespace App\HandlerFactory;


interface HandlerFactoryInterface
{
    public function createHandler(string $handler): HandlerInterface;
}