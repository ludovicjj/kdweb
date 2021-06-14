<?php

namespace App\Controller;

use App\Handler\RegistrationHandler;
use App\HandlerFactory\HandlerFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    /** @var HandlerFactory $handlerFactory */
    private $handlerFactory;

    public function __construct(
        HandlerFactory $handlerFactory
    ) {
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @Route("/register", name="app_register")
     *
     * @param Request $request
     * @return Response
     */
    public function register(Request $request): Response
    {
        $handler = $this->handlerFactory->createHandler(RegistrationHandler::class);

        if ($handler->handle($request)) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $handler->createView(),
        ]);
    }
}
