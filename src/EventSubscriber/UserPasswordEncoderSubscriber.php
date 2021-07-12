<?php


namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UserPasswordEncoderSubscriber implements EventSubscriber
{
    /** @var UserPasswordEncoderInterface $passwordEncoder */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->encodeUserPassword($args);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->encodeUserPassword($args);
    }

    private function encodeUserPassword(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof User) {
            return;
        }

        $entity->setPassword($this->passwordEncoder->encodePassword($entity, $entity->getPassword()));
    }
}