<?php

namespace App\Form\FormExtension;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepeatedPasswordType extends AbstractType
{
    public function getParent(): string
    {
        return RepeatedType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "type" => PasswordType::class,
            "invalid_message" => "Les mot de passe ne correspondent pas.",
            "required" => true,
            "first_options" => [
                "label" => "Mot de passe",
                "label_attr" => [
                    "title" => "Pour des raison de sécurité, votre mot de passe doit contenir au moins 8 caractères 
                        dont 1 caractère en majuscule, 1 nombre et un caractères special."
                ],
                "attr" => [
                    "pattern" => "^(?=.*?[A-Z])(?=.*?[0-9])(?=.*[-+_!@#$%^&*., ?]).{8,}$",
                    "title" => "Minimum 8 caractères avec au moins une majuscule, un chiffre et un caractère spécial",
                    "maxlength" => 255
                ]
            ],
            "second_options" => [
                "label" => "Confirmer votre mot de passe",
                "label_attr" => [
                    "title" => "Confirmer votre mot de passe"
                ],
                "attr" => [
                    "pattern" => "^(?=.*?[A-Z])(?=.*?[0-9])(?=.*[-+_!@#$%^&*., ?]).{8,}$",
                    "title" => "Confirmer votre mot de passe",
                    "maxlength" => 255
                ]
            ]
        ]);
    }
}