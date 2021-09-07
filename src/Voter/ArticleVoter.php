<?php

namespace App\Voter;

use App\Entity\Article;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class ArticleVoter extends Voter
{
    private const CAN_CREATE_ROLE = "ROLE_WRITER";
    public const CREATE = "create";
    public const EDIT = "edit";

    /** @var RoleHierarchyInterface $roleHierarchy */
    private $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [self::EDIT, self::CREATE])) {
            return false;
        }

        if ($subject !== null && !$subject instanceof Article) {
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
            case self::CREATE:
                return $this->canCreate($user);
            case self::EDIT:
                return $this->canEdit($article, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    public function canCreate(User $user): bool
    {
        if (
            !in_array(
            self::CAN_CREATE_ROLE,
            $this->roleHierarchy->getReachableRoleNames($user->getRoles()),
            true)
        ) {
            return false;
        }

        if ($user->getAuthor() === null) {
            return false;
        }

        return true;
    }

    private function canEdit(Article $article, User $user): bool
    {
        return $article->getAuthor() === $user->getAuthor();
    }
}