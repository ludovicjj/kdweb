<?php


namespace App\Controller;

use App\Factory\DTO\EditArticleDTOFactory;
use App\Form\EditArticleType;
use App\Handler\CreateArticleHandler;
use App\HandlerFactory\HandlerFactory;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use LogicException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /** @var HandlerFactory $handlerFactory */
    private $handlerFactory;

    private $articleRepository;
    private $formFactory;

    public function __construct(
        HandlerFactory $handlerFactory,
        ArticleRepository $articleRepository,
        FormFactoryInterface $formFactory
    )
    {
        $this->handlerFactory = $handlerFactory;
        $this->articleRepository = $articleRepository;
        $this->formFactory = $formFactory;
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

    /**
     * @Route("/articles/edit/{article_id}", name="app_edit_article", methods={"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function edit(Request $request)
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        $articleId = $request->attributes->get("article_id");
        $article = $this->articleRepository->find($articleId);

        $dto = EditArticleDTOFactory::build($article);
        $picture = $article->getPicture();

        $form = $this->formFactory->create(EditArticleType::class, $dto)->handleRequest($request);

        if ($form->isSubmitted()) {
            dd($form->getData());
        }

        return $this->render('article/create_article.html.twig', [
           "article_form" => $form->createView(),
            "pictureName" => $picture->getPictureName(),
            "isEdit" => true
        ]);
    }
}