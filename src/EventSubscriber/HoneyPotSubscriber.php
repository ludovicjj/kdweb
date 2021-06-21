<?php


namespace App\EventSubscriber;


use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HoneyPotSubscriber implements EventSubscriberInterface
{
    /** @var LoggerInterface $honeyPotLogger */
    private $honeyPotLogger;

    /** @var RequestStack $requestStack */
    private $requestStack;

    public function __construct(
        LoggerInterface $honeyPotLogger,
        RequestStack $requestStack
    ) {
        $this->honeyPotLogger = $honeyPotLogger;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => "checkHoneyJar"
        ];
    }

    public function checkHoneyJar(FormEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return;
        }

        $data = $event->getData();

        if (!array_key_exists('phone', $data) || !array_key_exists('faxNumber', $data)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Form has been modified.");
        }

        [
            "phone" => $phone,
            "faxNumber" => $faxNumber
        ] = $data;

        if ($phone !== "" || $faxNumber !== "") {
            $this->honeyPotLogger->info("Potential Bot fill hidden field with data phone:'{$phone}', faxNumber:'{$faxNumber}'. IP:'{$request->getClientIp()}'");
            throw new HttpException(Response::HTTP_FORBIDDEN, "Potential Bot fill hidden field.");
        }
    }
}