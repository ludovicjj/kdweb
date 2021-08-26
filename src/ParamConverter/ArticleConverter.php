<?php


namespace App\ParamConverter;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\NonUniqueResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArticleConverter implements ParamConverterInterface
{
    /** @var ArticleRepository $articleRepository */
    private $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $datetime = $request->attributes->get("datetime");

        $datetimeObject = \DateTime::createFromFormat("d-m-Y", $datetime);

        if ($datetimeObject === false) {
            throw new NotFoundHttpException("not found");
        }

        try {
            $article = $this->articleRepository
                ->createQueryBuilder("a")
                ->where("a.createdAt BETWEEN :start AND :end")
                ->setParameters([
                    "start" => $datetimeObject->format("Y-m-d 00:00:00"),
                    "end" => $datetimeObject->format("Y-m-d 23:59:59")
                ])
                ->orderBy("a.createdAt", "DESC")
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return false;
        }

        if ($article === null) {
            throw new NotFoundHttpException();
        }

        $request->attributes->set($configuration->getName(), $article);
        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        // check if configuration has converter and if converter is equal to "my_custom_converter"
        // else ArticleConverter has higher priority to "doctrine.orm" and "datetime" converter

        return ($configuration->getClass() === Article::class &&
            $configuration->getConverter() === "my_custom_converter");
    }
}