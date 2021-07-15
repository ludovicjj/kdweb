<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserPasswordEncoderListener
{
    /** @var UserPasswordEncoderInterface $passwordEncoder */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function prePersist(User $user, LifecycleEventArgs $args): void
    {
        $this->encodeUserPassword($user, $user->getPassword());
    }

    /**
     * @param User $user
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(User $user, PreUpdateEventArgs $args): void
    {
        $userChanges = $args->getEntityChangeSet();
        if (array_key_exists('password', $userChanges)) {
            $this->encodeUserPassword($user, $userChanges['password'][1]);
        }

    }

    private function encodeUserPassword(User $user, string $password): void
    {
        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
    }
}