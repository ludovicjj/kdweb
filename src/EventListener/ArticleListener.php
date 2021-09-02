<?php

namespace App\EventListener;

use App\Entity\Article;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ArticleListener
{
    public function prePersist(Article $article, LifecycleEventArgs $event): void
    {
        // ... do something to notify the changes
    }
}