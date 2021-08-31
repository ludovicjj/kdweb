<?php


namespace App\EventSubscriber;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CreateArticleSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => "onPreSetData"
        ];
    }

    public function onPreSetData(FormEvent $event): void
    {
        $form = $event->getForm();

        if (!$form->getConfig()->hasOption("user_role")) {
            return;
        }

        $role = $form->getConfig()->getOption("user_role");

        if (in_array("ROLE_ADMIN", $role, true)) {
            $form->add("save", SubmitType::class, [
                "label" => "Sauvegarder cette article comme brouillon",
                "attr" => [
                    "class" => "btn btn-warning"
                ]
            ]);
        }
    }
}