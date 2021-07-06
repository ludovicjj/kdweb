<?php


namespace App\Controller;


use App\Handler\ForgotPasswordHandler;
use App\HandlerFactory\HandlerFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ForgotPasswordController extends AbstractController
{
    /** @var HandlerFactory $handlerFactory */
    private $handlerFactory;

    public function __construct(
        HandlerFactory $handlerFactory
    )
    {
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @Route("/forgot/password", name="app_forgot_password")
     *
     * @param Request $request
     * @return Response
     */
    public function forgotPassword(Request $request): Response
    {
        $handler = $this->handlerFactory->createHandler(ForgotPasswordHandler::class);

        if ($handler->handle($request)) {
            dd('Form is valid');
        }

        return $this->render("forgot_password/forgot_password.html.twig", [
                "forgotPasswordForm" => $handler->createView()
            ]
        );
    }
}