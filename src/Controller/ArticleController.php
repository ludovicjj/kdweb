<?php


namespace App\Controller;


use App\Entity\Article;
use App\Entity\Picture;
use App\Repository\ArticleRepository;
use DateTimeImmutable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

class ArticleController
{
    private $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    /**
     * @Route("/get-article-without-doctrine-converter-1/{id}")
     * @param Request $request
     */
    public function getArticleById(Request $request)
    {
        $id = $request->attributes->get('id');
        $article = $this->articleRepository->find($id);

        if ($article === null) {
            throw new NotFoundHttpException("cet article n'existe pas");
        }

        dd($article);
    }

    /**
     * @Route("/get-article-without-doctrine-converter-2/{slug}")
     * @param Request $request
     */
    public function getArticleBySlug(Request $request)
    {
        $slug = $request->attributes->get('slug');
        $article = $this->articleRepository->findOneBy([
            "slug" => $slug
        ]);

        if ($article === null) {
            throw new NotFoundHttpException("cet article n'existe pas");
        }

        dd($article);
    }

    /**
     * @Route("/get-article-with-doctrine-converter-1/{id}")
     * @param Article $article
     */
    public function getArticleWithParamConverter1(Article $article)
    {
        dd($article);
    }

    /**
     * @Route("/get-article-with-doctrine-converter-2/{slug}")
     * @param Article $article
     */
    public function getArticleWithParamConverter2(Article $article)
    {
        dd($article);
    }

    /**
     * @Route("/get-article-with-doctrine-converter-3/{article_slug}")
     * @ParamConverter("article", options={"mapping": {"article_slug" = "slug"}})
     * @param Article $article
     */
    public function getArticleWithParamConverter3(Article $article)
    {
        dd($article);
    }


    /**
     * @Route("/article/{id}/picture/{picture_id}")
     * @Entity("picture", expr="repository.find(picture_id)")
     *
     * @param Article $article
     * @param Picture $picture
     */
    public function getArticleAndPictureWithParamConverter(Article $article, Picture $picture)
    {
        dd($article, $picture);
    }

    /**
     * @Route("/datetime/{start}")
     * @param DateTimeImmutable $start
     */
    public function getDateTimeImmutableWithDateTimeConverter(DateTimeImmutable $start)
    {
        dd($start);
    }
}