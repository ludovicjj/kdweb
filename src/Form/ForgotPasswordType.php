<?php

namespace App\Form;

use App\DTO\ForgotPasswordDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForgotPasswordType extends AbstractType
{
    /**
     * @param FormBuilderInterface<callable> $builder
     * @param array<mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', RepeatedType::class, [
            'type' => EmailType::class,
            'invalid_message' => "Les adresses e-mail ne correspondent pas.",
            "required" => true,
            'first_options' => [
                "label" => "Saisir votre adresse e-mail",
            ],
            'second_options' => [
                "label" => "Confirmer votre adresse e-mail",
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ForgotPasswordDTO::class,
            'empty_data' => function(FormInterface $form){
                return new ForgotPasswordDTO(
                    $form->get('email')->getData()
                );
            }
        ]);
    }
}