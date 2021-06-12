<?php

namespace App\HandlerFactory;

use Psr\Container\ContainerInterface;

class HandlerFactory implements HandlerFactoryInterface
{
    /** @var ContainerInterface $container */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function createHandler(string $handler): HandlerInterface
    {
        /** @var HandlerInterface $handler */
        $handler = $this->container->get($handler);
        return $handler;
    }
}