<?php

namespace App\Form;

use App\DTO\RegistrationDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class RegistrationFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface<callable> $builder
     * @param array<mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegistrationDTO::class,
            'empty_data' => function(FormInterface $form){
                return new RegistrationDTO(
                    $form->get('email')->getData(),
                    $form->get('password')->getData()
                );
            }
        ]);
    }
}
