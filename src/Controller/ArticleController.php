<?php


namespace App\Controller;

use App\Entity\User;
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
use Symfony\Contracts\Translation\TranslatorInterface;

class ArticleController extends AbstractController
{
    /** @var HandlerFactory $handlerFactory */
    private $handlerFactory;

    /** @var ArticleRepository $articleRepository */
    private $articleRepository;

    /** @var TranslatorInterface $translator */
    private $translator;

    public function __construct(
        HandlerFactory $handlerFactory,
        ArticleRepository $articleRepository,
        TranslatorInterface $translator
    )
    {
        $this->handlerFactory = $handlerFactory;
        $this->articleRepository = $articleRepository;
        $this->translator = $translator;
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
     * @param Request $request
     */
    public function read(Request $request)
    {
        $this->denyAccessUnlessGranted(ArticleVoter::READ);

        /** @var User $user */
        $user = $this->getUser();

        if ($user === null) {
            throw new LogicException("User cannot be null");
        }

        $article = $this->articleRepository->find($request->attributes->get("id"));
        $username = $user->getAuthor()->getName();

        if ($article === null) {
            throw new NotFoundHttpException("Not found article");
        }

        $flashInfo = $this->translator->trans(
            "flash.read.info",
            [
                "%username%" => $username
            ],
            "flash_messages"
        );

        $flashSuccess = $this->translator->trans(
            "flash.read.success",
            [],
            "flash_messages"
        );

        $this->addFlash("info", $flashInfo);
        $this->addFlash("success", $flashSuccess);

        return $this->render("article/read_article.html.twig", [
            "article" => $article,
            "username" => $username
        ]);
    }
}