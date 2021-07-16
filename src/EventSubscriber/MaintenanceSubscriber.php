<?php

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MaintenanceSubscriber implements EventSubscriberInterface
{
    /** @var Environment $twig */
    private $twig;

    /** @var ContainerBagInterface $containerBag */
    private $containerBag;

    public function __construct(
        Environment $twig,
        ContainerBagInterface $containerBag
    )
    {
        $this->twig = $twig;
        $this->containerBag = $containerBag;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }

    /**
     * @param RequestEvent $event
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$this->isMaintenanceMode() || $this->isAllowedIp($event)) {
            return;
        }

        $template = $this->twig->render('maintenance/site_under_maintenance.html.twig');
        $response = new Response($template, Response::HTTP_SERVICE_UNAVAILABLE);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    private function isMaintenanceMode(): bool
    {
        return $this->containerBag->get('app.maintenance_mode');
    }

    private function isAllowedIp(RequestEvent $event): bool
    {
        $clientIP = $event->getRequest()->getClientIp();

        if ($clientIP === null) {
            return false;
        }

        return in_array(
            $event->getRequest()->getClientIp(),
            $this->containerBag->get('app.maintenance_supported_ip'),
            true
        );
    }
}