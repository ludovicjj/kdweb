<?php

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTimeImmutable;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $article = new Article();
        $article->setTitle('Article 2')
            ->setSlug('article-2')
            ->setContent('Hello world')
            ->setCreatedAt(new DateTimeImmutable('2021-05-01T11:27:00'))
            ->setIsPublished(false);
        $manager->persist($article);

        $manager->flush();
    }
}
