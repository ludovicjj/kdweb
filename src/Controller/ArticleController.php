<?php


namespace App\Controller;

use App\Factory\DTO\EditArticleDTOFactory;
use App\Handler\CreateArticleHandler;
use App\Handler\EditArticleHandler;
use App\HandlerFactory\HandlerFactory;
use App\Repository\ArticleRepository;
use App\Voter\ArticleVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use LogicException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /** @var HandlerFactory $handlerFactory */
    private $handlerFactory;

    /** @var ArticleRepository $articleRepository */
    private $articleRepository;

    public function __construct(
        HandlerFactory $handlerFactory,
        ArticleRepository $articleRepository
    )
    {
        $this->handlerFactory = $handlerFactory;
        $this->articleRepository = $articleRepository;
    }

    /**
     * @Route("/articles/create", name="app_create_article", methods={"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted(ArticleVoter::CREATE);
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
        $articleId = $request->attributes->get("article_id");
        $article = $this->articleRepository->find($articleId);

        if ($article === null) {
            throw new NotFoundHttpException("Not found article");
        }


        $this->denyAccessUnlessGranted(ArticleVoter::EDIT, $article);

        $dto = EditArticleDTOFactory::build($article);

        $handler = $this->handlerFactory->createHandler(EditArticleHandler::class);

        if ($handler->handle($request, $dto, $article)) {
            return $this->redirectToRoute("app_user_account_home");
        }

        return $this->render('article/create_article.html.twig', [
            "article_form" => $handler->createView(),
            "pictureName" => $article->getPicture()->getPictureName(),
            "isEdit" => true
        ]);
    }

    /**
     * @Route(
     *     {
     *          "fr": "/articles/lire/{id}",
     *          "en": "/articles/read/{id}"
     *     },
     *     name="app_read_article",
     *     methods={"GET"}
     * )
     */
    public function read()
    {
        dd('hello world');
    }
}