<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function getCountArticlesCreatedByUser(User $user): int
    {
        return $this
            ->createQueryBuilder('article')
            ->select('COUNT(article)')
            ->where('article.author = :author')
            ->setParameter('author', $user->getAuthor())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getCountArticlesPublishedByUser(User $user): int
    {
        return $this
            ->createQueryBuilder('article')
            ->select('COUNT(article)')
            ->where('article.author = :author')
            ->andWhere('article.isPublished = true')
            ->setParameter('author', $user->getAuthor())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
