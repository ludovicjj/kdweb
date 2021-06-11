<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                "label" => 'Email',
                "required" => true,
                "attr" => [
                    "autofocus" => true
                ]
            ])
            ->add('password', RepeatedType::class, [
                "type" => PasswordType::class,
                "invalid_message" => "Les mot de passe ne correspondent pas.",
                "required" => true,
                "first_options" => [
                    "label" => "Mot de passe",
                    "label_attr" => [
                        "title" => "le label title du first_options"
                    ],
                    "attr" => [
                        "pattern" => "^(?=.*?[A-Z])(?=.*?[0-9])(?=.*[-+_!@#$%^&*., ?]).{8,}$",
                        "tilte" => "Minimum 8 caractères avec au moins une majuscule, un chiffre et un caractère spécial",
                        "max_length" => 255
                    ]
                ],
                "second_options" => [
                    "label" => "Confirmer le mot de passe",
                    "label_attr" => [
                        "title" => "le label title du second_options"
                    ],
                    "attr" => [
                        "pattern" => "^(?=.*?[A-Z])(?=.*?[0-9])(?=.*[-+_!@#$%^&*., ?]).{8,}$",
                        "tilte" => "Confirmer votre mot de passe",
                        "max_length" => 255
                    ]
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                "label" => "J'accepte les conditions d'utilisation de se site",
                'mapped' => false,
                'constraints' => [
                    new isTrue([
                        'message' => "Vous devez accepter les conditions d'utilisation de se site pour vous inscrire.",
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
