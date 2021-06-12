<?php


namespace App\DependencyInjection\Compiler;

use App\HandlerFactory\HandlerFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;

class HandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // always first check if the primary service is defined
        if (!$container->has(HandlerFactory::class)) {
            return;
        }

        $definition = $container->getDefinition(HandlerFactory::class);
        $refMap = [];

        // find all service IDs with the app.handler tag
        $taggedServices = $container->findTaggedServiceIds("app.handler");

        foreach (array_keys($taggedServices) as $serviceId) {
            $refMap[$container->getDefinition($serviceId)->getClass()] = new Reference($serviceId);
        }

        // injected ServiceLocator to constructor
        // ServiceLocator class implements the PSR-11 ContainerInterface
        // see: https://symfony.com/blog/new-in-symfony-3-3-service-locators
        // Use service locator in compiler pass
        // see: https://symfony.com/doc/current/service_container/service_subscribers_locators.html#using-service-locators-in-compiler-passes
        $definition->setArgument(0, ServiceLocatorTagPass::register($container, $refMap));
    }
}