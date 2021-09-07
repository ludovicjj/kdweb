<?php


namespace App\Voter;


use App\Entity\Article;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ArticleVoter extends Voter
{
    public const EDIT = "edit";

    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [self::EDIT])) {
            return false;
        }

        if (!$subject instanceof Article) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $article = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($article, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit(Article $article, User $user): bool
    {
        return $article->getAuthor() === $user->getAuthor();
    }
}