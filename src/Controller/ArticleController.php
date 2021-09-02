<?php


namespace App\Controller;

use App\Handler\CreateArticleHandler;
use App\HandlerFactory\HandlerFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use LogicException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /** @var HandlerFactory $handlerFactory */
    private $handlerFactory;

    public function __construct(HandlerFactory $handlerFactory)
    {
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @Route("/articles/create", name="app_create_article", methods={"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $user = $this->getUser();

        if ($user === null) {
            throw new LogicException("User cannot be null");
        }

        $handler = $this->handlerFactory->createHandler(CreateArticleHandler::class);
        $formOptions = ["form_options" => ["user_role" => $user->getRoles()]];
        if ($handler->handle($request, null, null, $formOptions)) {
            return $this->redirectToRoute("app_user_account_home");
        }

        return $this->render("article/create_article.html.twig", [
            "article_form" => $handler->createView()
        ]);
    }
}