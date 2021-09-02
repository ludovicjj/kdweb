<?php

namespace App\EventListener;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use LogicException;
use DateTimeImmutable;

class ArticleListener
{
    /** @var Security $security */
    private $security;

    /** @var SluggerInterface $slugger */
    private $slugger;

    public function __construct(
        Security $security,
        SluggerInterface $slugger
    )
    {
        $this->security = $security;
        $this->slugger = $slugger;
    }

    public function prePersist(Article $article, LifecycleEventArgs $event): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if ($user === null) {
            throw new LogicException("Expect user here.");
        }

        $author = $user->getAuthor();

        if ($author === null) {
            throw new LogicException("Current user is not an author.");
        }

        $article
            ->setAuthor($author)
            ->setCreatedAt(new DateTimeImmutable("now"))
            ->setSlug($this->makeArticleSlug($article))
        ;
    }

    private function makeArticleSlug(Article $article): string
    {
        /** @var string $title */
        $title = $article->getTitle();

        $slug = mb_strtolower($title . "-" . time(), "UTF-8");
        return $this->slugger->slug($slug);
    }
}